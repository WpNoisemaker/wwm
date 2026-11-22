<?php
/**
 * Front controller.
 */
declare(strict_types=1);

// PHP's built-in server sends every request through this file, assets included. On a real host
// Apache serves those directly (see .htaccess), so this only ever runs locally.
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (is_file($file)) {
        return false;
    }
}

$root = dirname(__DIR__);
require $root . '/app/Core/App.php';
App::boot($root);
require $root . '/app/Core/View.php';
require $root . '/app/Views/partials/icons.php';

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

/**
 * Anything that escapes a controller lands here. Without this an uncaught error still returned
 * HTTP 200 with a stack trace in the body — which is both a lie to the browser and a leak of the
 * server's paths. Debug shows the detail; a live site shows the 500 page and logs the rest.
 */
set_exception_handler(static function (Throwable $e): void {
    error_log('[wwm] ' . $e::class . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) {
        http_response_code(500);
    }
    if (App::config('debug')) {
        echo '<pre style="padding:20px;font:13px/1.5 ui-monospace,monospace;white-space:pre-wrap">'
           . htmlspecialchars($e::class . ': ' . $e->getMessage() . "\n\n" . $e->getTraceAsString(), ENT_QUOTES)
           . '</pre>';
        return;
    }
    View::render('errors/500', ['title' => 'Something broke']);
});

// A fatal (a missing method, say) is not an exception, so it needs its own net.
register_shutdown_function(static function (): void {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log('[wwm] fatal: ' . $e['message'] . ' @ ' . $e['file'] . ':' . $e['line']);
        if (!headers_sent()) {
            http_response_code(500);
        }
    }
});

$r = new Router();
require $root . '/app/routes.php';
$r->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
