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
}
