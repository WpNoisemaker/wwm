<?php
declare(strict_types=1);

/**
 * Checks the password hashes WordPress wrote, so people moved across from the old site can sign in
 * with the password they already have.
 *
 * PHP's own password_verify() cannot read them. Two formats matter:
 *   $P$ / $H$   "phpass", portable MD5 stretching. Every WordPress up to 6.7, and any account that
 *               has not signed in since.
 *   $wp$2y$     WordPress 6.8 and later: bcrypt over a base64 SHA-384 HMAC of the password.
 *
 * It only ever *checks*. On a correct password Auth immediately replaces the stored hash with a
 * modern one, so each account goes through here at most once.
 */
final class LegacyHash
{
    private const ITOA64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    /** True for a hash this class knows how to check. */
    public static function handles(string $hash): bool
    {
        return str_starts_with($hash, '$P$') || str_starts_with($hash, '$H$') || str_starts_with($hash, '$wp$');
    }

    public static function verify(string $password, string $hash): bool
    {
        // WordPress itself refuses anything this long, and it bounds the work an attacker can ask for.
        if (strlen($password) > 4096) {
            return false;
        }
        if (str_starts_with($hash, '$wp$')) {
            $pre = base64_encode(hash_hmac('sha384', $password, 'wp-sha384', true));
            return password_verify($pre, substr($hash, 3));
        }
        $calc = self::phpass($password, $hash);
        return $calc !== '' && hash_equals($hash, $calc);
    }

    /** The phpass computation, given the stored hash as its own settings (id, cost, salt). */
    public static function phpass(string $password, string $setting): string
    {
        if (strlen($setting) < 12 || ($setting[0] . $setting[1] . $setting[2] !== '$P$' && $setting[0] . $setting[1] . $setting[2] !== '$H$')) {
            return '';
        }
        $log2 = strpos(self::ITOA64, $setting[3]);
        // WordPress uses 8. Anything above 16 is not something it ever wrote, and 2^30 rounds of MD5
        // is a way to hang the server, so it is treated as unverifiable rather than attempted.
        if ($log2 === false || $log2 < 7 || $log2 > 16) {
            return '';
        }
        $salt = substr($setting, 4, 8);
        if (strlen($salt) !== 8) {
            return '';
        }
        $count = 1 << $log2;
        $hash  = md5($salt . $password, true);
        do {
            $hash = md5($hash . $password, true);
        } while (--$count);

        return substr($setting, 0, 12) . self::encode64($hash, 16);
    }

    private static function encode64(string $input, int $count): string
    {
        $out = '';
        $i = 0;
        do {
            $value = ord($input[$i++]);
            $out  .= self::ITOA64[$value & 0x3f];
            if ($i < $count) {
                $value |= ord($input[$i]) << 8;
            }
            $out .= self::ITOA64[($value >> 6) & 0x3f];
            if ($i++ >= $count) {
                break;
            }
            if ($i < $count) {
                $value |= ord($input[$i]) << 16;
            }
            $out .= self::ITOA64[($value >> 12) & 0x3f];
            if ($i++ >= $count) {
                break;
            }
            $out .= self::ITOA64[($value >> 18) & 0x3f];
        } while ($i < $count);

        return $out;
    }
}
