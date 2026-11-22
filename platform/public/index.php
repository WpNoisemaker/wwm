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

$r = new Router();
require $root . '/app/routes.php';
$r->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
