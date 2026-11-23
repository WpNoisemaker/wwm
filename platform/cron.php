<?php
/**
 * Background jobs, for hosts that can run a cron job (cPanel: Cron Jobs). Every five minutes:
 *
 *   php /home/YOURCPUSER/public_html/cron.php
 *
 * It sends livestream reminders that are due and guests' photos waiting for Google Drive. The
 * site also does both on its own as people use it, so cron only makes them more punctual.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Run this from the command line or a cron job.\n");
}
$root = __DIR__;
require $root . '/app/Core/App.php';
App::boot($root);
require $root . '/app/Core/View.php';
require $root . '/app/Views/partials/icons.php';
require $root . '/app/Views/partials/ui.php';

$r = Reminder::run(500);
$d = Drive::sync(60);
$s = Settle::run(100);   // instant settlement: anything that could not be paid out straight away
echo date('c'), " reminders sent: $r, photos sent to Drive: $d, payouts sent: $s\n";
