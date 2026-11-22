<?php
/**
 * Templates are plain PHP. No engine, no compile step, nothing to warm up on a shared host.
 */
declare(strict_types=1);

final class View
{
    private static array $shared = [];

    public static function share(string $k, mixed $v): void { self::$shared[$k] = $v; }

    public static function render(string $tpl, array $data = [], ?string $layout = 'layout'): void
    {
        echo self::capture($tpl, $data, $layout);
    }

    public static function capture(string $tpl, array $data = [], ?string $layout = 'layout'): string
    {
        $content = self::partial($tpl, $data);
        if ($layout === null) {
            return $content;
        }
        return self::partial($layout, $data + ['content' => $content]);
    }

    public static function partial(string $tpl, array $data = []): string
    {
        $file = App::root() . '/app/Views/' . $tpl . '.php';
        if (!is_readable($file)) {
            throw new RuntimeException("View not found: $tpl");
        }
        extract(self::$shared + $data, EXTR_SKIP);
        ob_start();
        require $file;
        return (string) ob_get_clean();
    }

    /** Escape for HTML. Short name because it is used on every line of every template. */
    public static function e(mixed $v): string
    {
        return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

function e(mixed $v): string { return View::e($v); }
