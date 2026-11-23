<?php
/**
 * Hits every GET route and reports what came back. Run after a change:  php smoke.php
 * Exits non-zero if anything is not a 200, so it can gate a release.
 */
declare(strict_types=1);

$base = $argv[1] ?? 'http://127.0.0.1:8080';

// Addresses that need a real row behind them get one picked from the database.
$cfg  = require __DIR__ . '/config/config.php';
$pdo  = new PDO("mysql:host={$cfg['db']['host']};dbname={$cfg['db']['name']};charset=utf8mb4", $cfg['db']['user'], $cfg['db']['pass']);
$one  = static fn(string $sql): string => (string) ($pdo->query($sql)->fetchColumn() ?: '');

$wed    = $one("SELECT slug FROM weddings WHERE visibility='public' LIMIT 1");
$vendor = $one('SELECT slug FROM vendors LIMIT 1');
$list   = $one("SELECT slug FROM listings WHERE status='live' LIMIT 1");
$post   = $one("SELECT slug FROM posts WHERE status='live' LIMIT 1");
$talk   = $one('SELECT id FROM talks LIMIT 1');

$routes = array_filter([
    '/', '/weddings', '/weddings?q=yusuf', '/weddings?sort=buzz', '/rankings',
    '/vendors', '/market', '/blog', '/search', '/search?q=lagos',
    '/pricing', '/about', '/help', '/terms', '/privacy',
    '/community', '/community?sort=new',
    '/sign-in', '/join', '/join/customer', '/sell', '/forgot', '/account/messages', '/hq/market', '/hq/emails', '/pay/NOPE1234', '/sitemap.xml', '/robots.txt',
    '/account', '/hq',
    '/templates', '/offline', '/manifest.webmanifest', '/sw.js', '/w/ibrahim-ummi', '/w/tolu-ayo', '/rankings?city=Lagos', '/vendors?category=Photography', '/market?kind=asoebi', '/nope-not-a-page',
    $wed    ? "/w/$wed"            : null,
    $vendor ? "/vendors/$vendor"   : null,
    $list   ? "/market/$list"      : null,
    $post   ? "/blog/$post"        : null,
    $talk   ? "/community/$talk"   : null,
]);

$bad = 0;
foreach ($routes as $r) {
    $ch = curl_init($base . $r);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 20]);
    $body = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // A 302 to sign-in is the right answer for the account and HQ areas when signed out.
    $gated = in_array($code, [302, 303], true) && str_starts_with($r, '/account') === false ? false : true;
    $ok    = $code === 200 || (in_array($code, [302, 303], true) && (str_starts_with($r, '/account') || str_starts_with($r, '/hq') || str_starts_with($r, '/pay/')))
          || ($r === '/w/tolu-ayo' && $code === 401) || ($r === '/nope-not-a-page' && $code === 404);

    // A page that returns 200 but printed a PHP error is not actually fine.
    $err = '';
    if (preg_match('/(Fatal error|Parse error|Warning:|Notice:|Deprecated:|Uncaught)/', $body, $m)) {
        $ok = false;
        $err = ' <- ' . $m[1] . ' in output';
    }
    if ($code === 200 && strlen($body) < 400) {
        $err = ' <- suspiciously short (' . strlen($body) . ' bytes)';
    }

    printf("%-34s %d%s%s\n", $r, $code, $ok ? '' : '   FAIL', $err);
    if (!$ok) {
        $bad++;
    }
}

echo $bad ? "\n$bad route(s) need attention.\n" : "\nAll " . count($routes) . " routes healthy.\n";
exit($bad ? 1 : 0);
