<?php
declare(strict_types=1);

/**
 * Instant settlement. Every gift or spray that lands in a couple's Bachs account is paid straight on
 * to their own bank account: in full on Premium and High Society, less our fee on Free and Pro. Each
 * one is a row here. If Bachs cannot pay it yet (money still settling), it is tried again every
 * minute for an hour, then flagged for HQ. Bachs confirms with payout.paid or payout.failed.
 */
final class Settle
{
    public static function table(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        Db::run("CREATE TABLE IF NOT EXISTS settlements (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT, payment_id INT UNSIGNED NOT NULL, wedding_id INT UNSIGNED NOT NULL,
            gross_kobo BIGINT NOT NULL, fee_kobo BIGINT NOT NULL DEFAULT 0, net_kobo BIGINT NOT NULL,
            withdrawal_id VARCHAR(80) NOT NULL DEFAULT '', status ENUM('waiting','sent','paid','failed') NOT NULL DEFAULT 'waiting',
            tries SMALLINT NOT NULL DEFAULT 0, error VARCHAR(255) NOT NULL DEFAULT '', next_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, done_at DATETIME NULL,
            PRIMARY KEY (id), UNIQUE KEY uq_pay (payment_id), KEY ix_due (status, next_at), KEY ix_wed (wedding_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    /** A transfer to settle: the naira that arrived, and our fee on Free and Pro. */
    public static function add(array $w, int $paymentId, int $kobo): void
    {
        self::table();
        $free = in_array((string) $w['plan'], ['premium', 'society'], true);
        $fee = $free ? 0 : (int) round($kobo * ((float) Site::setting('fee_gift', '4')) / 100);
        Db::run('INSERT IGNORE INTO settlements (payment_id, wedding_id, gross_kobo, fee_kobo, net_kobo, next_at) VALUES (?, ?, ?, ?, ?, NOW())',
            [$paymentId, $w['id'], $kobo, $fee, $kobo - $fee]);
    }

    /** Pay out whatever is due. Runs after each transfer, and from cron. */
    public static function run(int $limit = 50): int
    {
        if (!Bachs::on()) {
            return 0;
        }
        self::table();
        $n = 0;
        foreach (Db::all("SELECT s.*, w.* , s.id sid, s.status sstatus FROM settlements s JOIN weddings w ON w.id = s.wedding_id
                          WHERE s.status = 'waiting' AND (s.next_at IS NULL OR s.next_at <= NOW()) ORDER BY s.id LIMIT ?", [$limit]) as $r) {
            if ((string) $r['payout_dest'] === '') {
                // No bank account yet: it waits, and goes out as soon as the couple adds one.
                Db::run("UPDATE settlements SET error = 'No bank account added yet', next_at = NOW() + INTERVAL 10 MINUTE WHERE id = ?", [$r['sid']]);
                continue;
            }
            $email = (string) Db::val('SELECT email FROM users WHERE id = ?', [$r['owner_id']], '');
            $err = '';
            $wid = Bachs::withdraw($r, (int) $r['net_kobo'], 'WWM-S' . $r['sid'], $email, $err);
            if ($wid !== '') {
                Db::run("UPDATE settlements SET status = 'sent', withdrawal_id = ?, error = '', tries = tries + 1 WHERE id = ?", [$wid, $r['sid']]);
                $n++;
            } else {
                $tries = (int) $r['tries'] + 1;
                Db::run('UPDATE settlements SET tries = ?, error = ?, status = ?, next_at = NOW() + INTERVAL 1 MINUTE WHERE id = ?',
                    [$tries, mb_substr($err, 0, 250), $tries >= 60 ? 'failed' : 'waiting', $r['sid']]);
            }
        }
        return $n;
    }

    /** Bachs says a payout went through, or did not. */
    public static function update(string $withdrawalId, bool $paid, string $reason): void
    {
        if ($withdrawalId === '') {
            return;
        }
        self::table();
        Db::run("UPDATE settlements SET status = ?, error = ?, done_at = NOW() WHERE withdrawal_id = ?", [$paid ? 'paid' : 'failed', $paid ? '' : mb_substr($reason, 0, 250), $withdrawalId]);
    }

    /** A couple's recent settlements, newest first. */
    public static function forWedding(int $wid, int $n = 20): array
    {
        self::table();
        return Db::all('SELECT s.*, p.payer_name, p.purpose FROM settlements s LEFT JOIN payments p ON p.id = s.payment_id WHERE s.wedding_id = ? ORDER BY s.id DESC LIMIT ?', [$wid, $n]);
    }

    /** Try failed ones again (HQ). */
    public static function retry(int $wid): void
    {
        self::table();
        Db::run("UPDATE settlements SET status = 'waiting', tries = 0, next_at = NOW() WHERE wedding_id = ? AND status = 'failed'", [$wid]);
        self::run();
    }
}
