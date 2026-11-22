<?php
declare(strict_types=1);

/**
 * Email confirmation: a switch the admin turns on when ready, and off until then.
 *
 * Off — the default — nothing here does anything, and nobody is ever asked to confirm.
 *
 * On, a person who signs up from that moment has to follow a link we email them before they can
 * make their wedding page public or ask for a payout. They can still sign in and build the page.
 *
 * It applies only to accounts created AFTER the switch was turned on. Everyone who already had an
 * account, including everyone imported from WordPress, is never asked, so flipping the switch can
 * never lock an existing person out. That is done by remembering WHEN it was turned on, not by
 * stamping old accounts as confirmed, which would record a confirmation that never happened.
 */
final class Verify
{
    /** Is the switch on? */
    public static function enabled(): bool
    {
        return (string) Db::val("SELECT v FROM settings WHERE k = 'email_verify'", [], '0') === '1';
    }

    /** True when this person must still confirm before the gated actions. */
    public static function needed(?array $user): bool
    {
        if (!$user || !empty($user['email_verified_at']) || !self::enabled()) {
            return false;
        }
        $since = (string) Db::val("SELECT v FROM settings WHERE k = 'email_verify_since'", [], '');
        return $since !== '' && (string) $user['created_at'] >= $since;
    }

    /** Turn it on or off. Turning it on from off records the moment, which is what spares older accounts. */
    public static function set(bool $on): void
    {
        if ($on && !self::enabled()) {
            // The database's own clock, the same one that stamps users.created_at, so the two compare cleanly.
            Db::run("INSERT INTO settings (k, v) VALUES ('email_verify_since', NOW()) ON DUPLICATE KEY UPDATE v = NOW()");
        }
        Db::run("INSERT INTO settings (k, v) VALUES ('email_verify', ?) ON DUPLICATE KEY UPDATE v = VALUES(v)", [$on ? '1' : '0']);
    }

    /** Email them a fresh link. Any earlier unused link stops working. */
    public static function send(array $user): bool
    {
        Db::run("UPDATE tokens SET used_at = NOW() WHERE user_id = ? AND kind = 'verify' AND used_at IS NULL", [$user['id']]);
        $tok = bin2hex(random_bytes(32));
        Db::run(
            "INSERT INTO tokens (user_id, kind, token, expires_at) VALUES (?, 'verify', ?, DATE_ADD(NOW(), INTERVAL 3 DAY))",
            [$user['id'], $tok]
        );
        $first = trim(strtok((string) ($user['name'] ?? ''), ' ') ?: '');
        return Mailer::send(
            (string) $user['email'],
            'Confirm your email',
            'Hello' . ($first !== '' ? ' ' . $first : '') . ",\n\n"
            . "Please confirm this is your email address by opening this link:\n\n"
            . App::url('/verify/' . $tok) . "\n\n"
            . "It works once and expires in 3 days. If you did not make an account, ignore this email.\n\n"
            . "— Wedding Websites & More\n"
        );
    }

    /** Use a link. True when it was valid; it is spent either way a second time. */
    public static function confirm(string $token): bool
    {
        $row = Db::one("SELECT id, user_id FROM tokens WHERE token = ? AND kind = 'verify' AND used_at IS NULL AND expires_at > NOW()", [$token]);
        if (!$row) {
            return false;
        }
        Db::run('UPDATE users SET email_verified_at = NOW() WHERE id = ? AND email_verified_at IS NULL', [$row['user_id']]);
        Db::run('UPDATE tokens SET used_at = NOW() WHERE id = ?', [$row['id']]);
        return true;
    }
}
