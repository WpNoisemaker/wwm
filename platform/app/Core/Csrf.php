<?php
declare(strict_types=1);

final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(16));
        }
        return $_SESSION['csrf'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_t" value="' . e(self::token()) . '">';
    }

    public static function check(): void
    {
        $sent = (string) ($_POST['_t'] ?? '');
        if ($sent === '' || !hash_equals(self::token(), $sent)) {
            http_response_code(419);
            exit('That form expired. Go back and try again.');
        }
    }
}
