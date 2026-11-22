<?php
/**
 * Config, autoloading and the few globals the app needs.
 */
declare(strict_types=1);

final class App
{
    private static array $cfg = [];

    public static function boot(string $root): void
    {
        $file = $root . '/config/config.php';
        if (!is_readable($file)) {
            http_response_code(500);
            exit('Copy config/config.sample.php to config/config.php and fill it in.');
        }
        self::$cfg = require $file;
        self::$cfg['root'] = $root;

        $debug = !empty(self::$cfg['debug']);
        ini_set('display_errors', $debug ? '1' : '0');
        error_reporting($debug ? E_ALL : E_ALL & ~E_DEPRECATED);
        date_default_timezone_set(self::$cfg['tz'] ?? 'Africa/Lagos');

        spl_autoload_register(static function (string $class) use ($root): void {
            foreach (['Core', 'Controllers', 'Models'] as $dir) {
                $f = "$root/app/$dir/$class.php";
                if (is_readable($f)) {
                    require_once $f;
                    return;
                }
            }
        });
    }

    public static function config(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return self::$cfg;
        }
        return self::$cfg[$key] ?? $default;
    }

    public static function root(): string
    {
        return self::$cfg['root'];
    }

    public static function url(string $path = ''): string
    {
        return rtrim((string) self::config('url'), '/') . '/' . ltrim($path, '/');
    }

    /** Cache-busted asset URL, so a year-long cache header is safe. */
    public static function asset(string $path): string
    {
        $file = self::root() . '/public/' . ltrim($path, '/');
        $v    = is_readable($file) ? substr((string) filemtime($file), -6) : '1';
        return self::url($path) . '?v=' . $v;
    }
}
