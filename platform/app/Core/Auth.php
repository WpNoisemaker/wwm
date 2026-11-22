<?php
declare(strict_types=1);

/**
 * Who is signed in. Sessions live in the database so a shared host's session garbage collector
 * cannot quietly sign everybody out, and so a password change can end every other session.
 */
final class Auth
{
    private static ?array $user = null;
    // Separate from $user, because "not signed in" and "not looked up yet" are different states
    // and null cannot carry both.
    private static bool $looked = false;
    private const COOKIE = 'wwm';

    public static function id(): ?int
    {
        $u = self::user();
        return $u ? (int) $u['id'] : null;
    }

    public static function user(): ?array
    {
        if (self::$looked) {
            return self::$user;
        }
        self::$looked = true;
        $sid = $_COOKIE[self::COOKIE] ?? '';
        if (!is_string($sid) || strlen($sid) !== 64) {
            return null;
        }
        self::$user = Db::one(
            'SELECT u.* FROM sessions s JOIN users u ON u.id = s.user_id
             WHERE s.id = ? AND s.expires_at > NOW() LIMIT 1',
            [$sid]
        );
        return self::$user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function is(string $role): bool
    {
        $u = self::user();
        return $u && $u['role'] === $role;
    }

    /** Verify an email and password. Returns the user, or null — never says which half was wrong. */
    public static function attempt(string $email, string $pass): ?array
    {
        $u = Db::one('SELECT * FROM users WHERE email = ? LIMIT 1', [strtolower(trim($email))]);
        if (!$u || !password_verify($pass, (string) $u['pass'])) {
            return null;
        }
        // Rehash if the cost has moved on since they last signed in.
        if (password_needs_rehash((string) $u['pass'], PASSWORD_DEFAULT)) {
            Db::update('users', ['pass' => password_hash($pass, PASSWORD_DEFAULT)], 'id = ?', [$u['id']]);
        }
        return $u;
    }

    /** Start a session for a user and set the cookie. */
    public static function login(array $user, bool $remember = true): void
    {
        $sid  = bin2hex(random_bytes(32));
        $days = $remember ? 30 : 1;
        Db::run(
            'INSERT INTO sessions (id, user_id, ip, agent, expires_at) VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? DAY))',
            [$sid, $user['id'], @inet_pton((string) ($_SERVER['REMOTE_ADDR'] ?? '')) ?: null, substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 190), $days]
        );
        Db::run('UPDATE users SET last_seen_at = NOW() WHERE id = ?', [$user['id']]);
        setcookie(self::COOKIE, $sid, [
            'expires'  => time() + $days * 86400,
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        self::$user = $user;
        self::$looked = true;
    }

    /** End this session only, so signing out on one device leaves the others alone. */
    public static function logout(): void
    {
        $sid = $_COOKIE[self::COOKIE] ?? '';
        if (is_string($sid) && $sid !== '') {
            Db::run('DELETE FROM sessions WHERE id = ?', [$sid]);
        }
        setcookie(self::COOKIE, '', ['expires' => time() - 3600, 'path' => '/']);
        self::$user = null;
        self::$looked = true;
    }

    /** Create an account. Returns the new user, or null when the email is already taken. */
    public static function register(string $email, string $pass, string $name, string $phone = ''): ?array
    {
        $email = strtolower(trim($email));
        if (Db::val('SELECT id FROM users WHERE email = ?', [$email])) {
            return null;
        }
        $id = Db::insert('users', [
            'email' => $email,
            'pass'  => password_hash($pass, PASSWORD_DEFAULT),
            'name'  => trim($name),
            'phone' => trim($phone),
            'role'  => 'member',
        ]);
        return Db::one('SELECT * FROM users WHERE id = ?', [$id]);
    }

    /** Send a visitor to sign in, remembering where they were going. */
    public static function wall(): void
    {
        if (self::check()) {
            return;
        }
        $to = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        header('Location: ' . App::url('/sign-in?to=' . urlencode($to)));
        exit;
    }

    /** Clear sessions that have run out, so the table does not grow forever on a shared host. */
    public static function sweep(): void
    {
        Db::run('DELETE FROM sessions WHERE expires_at < NOW()');
    }
}
