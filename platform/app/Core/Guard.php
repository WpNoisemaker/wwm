<?php
declare(strict_types=1);

/**
 * Form protection and one-shot messages. Both ride on the PHP session, which is only started when
 * something actually needs it — a visitor who only reads pages never gets a session cookie.
 */
final class Guard
{
    public static function boot(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_httponly' => true,
                'cookie_samesite' => 'Lax',
                'cookie_secure'   => !empty($_SERVER['HTTPS']),
            ]);
        }
    }

    /** The token to put in every form that changes something. */
    public static function token(): string
    {
        self::boot();
        if (empty($_SESSION['_t'])) {
            $_SESSION['_t'] = bin2hex(random_bytes(32));
        }
        return (string) $_SESSION['_t'];
    }

    /** A ready-made hidden field. */
    public static function field(): string
    {
        return '<input type="hidden" name="_t" value="' . htmlspecialchars(self::token(), ENT_QUOTES) . '">';
    }

    /** True when the posted token matches. Compared in constant time. */
    public static function ok(): bool
    {
        self::boot();
        $sent = (string) ($_POST['_t'] ?? '');
        return $sent !== '' && !empty($_SESSION['_t']) && hash_equals((string) $_SESSION['_t'], $sent);
    }

    /** Refuse the request outright when the token is missing or stale. */
    public static function must(): void
    {
        if (!self::ok()) {
            http_response_code(419);
            exit('Your form has expired. Go back, reload the page and try again.');
        }
    }

    /** Leave a message for the next page load. */
    public static function flash(string $kind, string $msg): void
    {
        self::boot();
        $_SESSION['_f'][] = ['kind' => $kind, 'msg' => $msg];
    }

    /** Take every pending message, clearing them. */
    public static function takeFlash(): array
    {
        self::boot();
        $f = $_SESSION['_f'] ?? [];
        unset($_SESSION['_f']);
        return is_array($f) ? $f : [];
    }

    /** Keep a form's values so a failed submit does not wipe what the visitor typed. */
    public static function keep(array $values): void
    {
        self::boot();
        $_SESSION['_old'] = $values;
    }

    public static function old(string $key, string $fallback = ''): string
    {
        self::boot();
        return (string) ($_SESSION['_old'][$key] ?? $fallback);
    }

    public static function dropOld(): void
    {
        self::boot();
        unset($_SESSION['_old']);
    }

    /** This visitor's address, for keying a throttle. */
    public static function ip(): string
    {
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    /**
     * Has this key used up its allowance? Only reads: asking does not count as an attempt.
     * Pair it with hit(), called only for the attempts that should count (a failed sign-in, say).
     * tooMany() below counts every call, which is right for things that cost something each time
     * (sending an email) and wrong for a sign-in, where a success is not a reason to slow anyone down.
     */
    public static function blocked(string $key, int $limit): bool
    {
        Db::run('DELETE FROM throttle WHERE expires_at < NOW()');
        return (int) Db::val('SELECT hits FROM throttle WHERE bucket = ?', [substr($key, 0, 120)], 0) >= $limit;
    }

    /** Count one attempt against a key, for $minutes from the first. */
    public static function hit(string $key, int $minutes): void
    {
        Db::run(
            'INSERT INTO throttle (bucket, hits, expires_at) VALUES (?, 1, DATE_ADD(NOW(), INTERVAL ? MINUTE))
             ON DUPLICATE KEY UPDATE hits = hits + 1',
            [substr($key, 0, 120), $minutes]
        );
    }

    /** Forget a key, once whatever it was guarding has succeeded. */
    public static function forget(string $key): void
    {
        Db::run('DELETE FROM throttle WHERE bucket = ?', [substr($key, 0, 120)]);
    }

    /** Simple per-action throttle, so a shared host is not a free brute-force target. */
    public static function tooMany(string $action, int $limit = 8, int $minutes = 15): bool
    {
        // The address is only ever kept scrambled, and only until the window runs out.
        $key = substr($action . ':' . hash('sha256', 'wwm|' . (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0')), 0, 120);
        Db::run('DELETE FROM throttle WHERE expires_at < NOW()');
        Db::run(
            'INSERT INTO throttle (bucket, hits, expires_at) VALUES (?, 1, DATE_ADD(NOW(), INTERVAL ? MINUTE))
             ON DUPLICATE KEY UPDATE hits = hits + 1',
            [$key, $minutes]
        );
        return (int) Db::val('SELECT hits FROM throttle WHERE bucket = ?', [$key], 0) > $limit;
    }
}
