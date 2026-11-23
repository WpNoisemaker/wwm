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
require $root . '/app/Views/partials/ui.php';

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

/*
 * A file upload sent from the page's script (so it can show a progress bar) asks for the
 * redirect back as a header instead. The page then goes there itself, keeping any #section, and
 * the "Saved" message waits for that page instead of being used up by the upload request.
 */
if (($_SERVER['HTTP_X_WWM_UPLOAD'] ?? '') === '1') {
    header_register_callback(static function (): void {
        foreach (headers_list() as $h) {
            if (stripos($h, 'Location:') === 0) {
                header_remove('Location');
                http_response_code(200);
                header('X-WWM-Go: ' . trim(substr($h, 9)));
                header('Cache-Control: no-store');
                return;
            }
        }
    });
}

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

// Content changes a site already installed should also get, applied once each.
try {
    Upgrade::run();
} catch (Throwable $e) {
    error_log('[wwm] upgrades: ' . $e->getMessage());
}

$r = new Router();
require $root . '/app/routes.php';
// A site installed in a folder (example.com/weddings/) routes the same as one at the root.
$uri  = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$base = rtrim((string) parse_url((string) App::config('url', ''), PHP_URL_PATH), '/');
if ($base !== '' && str_starts_with($uri, $base . '/')) {
    $uri = substr($uri, strlen($base));
} elseif ($base !== '' && $uri === $base) {
    $uri = '/';
}
$r->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $uri);
