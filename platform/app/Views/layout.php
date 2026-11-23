<?php
/**
 * The one page frame.
 *
 * Phone: an app. A round button and a centred light title at the top, a glass tab bar at the
 * bottom, nothing else. Desktop: a website, with the sections across a top header and a full
 * footer. A wedding page brings its own header and action bar ($wed) and wears its own palette.
 *
 * @var string $content
 */
$tab      = $tab ?? '';
$wed      = !empty($wed);
$bare     = !empty($bare);
$tabTitles = ['home' => 'Wedding Pages', 'weddings' => 'Weddings', 'rankings' => 'Buzz Chart', 'vendors' => 'Vendors', 'market' => 'Market',
              'community' => 'Community', 'blog' => 'Blog', 'pricing' => 'Pricing', 'account' => 'Your Page', 'hq' => 'HQ', 'search' => 'Search'];
$appTitle = $appTitle ?? ($tabTitles[$tab] ?? mb_strimwidth((string) ($title ?? 'Wedding Websites & More'), 0, 22, '…'));
$back     = $back ?? null;           // a URL turns the left button into a back arrow
$action   = $action ?? null;         // HTML for the right-hand button
$fixTheme = $fixTheme ?? null;       // a wedding template that is always day or always night
$tone     = $tone ?? '';             // 'mono' for a Free page
$ink      = $ink ?? '';              // 'dark' on the light, paper-like templates
$htmlAttr = '';
if ($fixTheme) { $htmlAttr .= ' data-theme="' . e($fixTheme) . '" data-fixed'; } else { $htmlAttr .= ' data-theme="night"'; }
if ($tone)     { $htmlAttr .= ' data-tone="' . e($tone) . '"'; }
if ($ink)      { $htmlAttr .= ' data-ink="' . e($ink) . '"'; }
if (!empty($wtpl)) { $htmlAttr .= ' data-tpl="' . e($wtpl) . '"'; }
$home  = App::url('/');
$base  = (string) parse_url($home, PHP_URL_PATH);
$og    = $ogImage ?? img_url(timg('site.image.share', 'brand/og.jpg'));
// The page's own address, for link previews: the site's origin plus the path asked for.
$origin = (string) preg_replace('#^(https?://[^/]+).*$#', '$1', (string) App::config('url'));
$canon  = $canonical ?? ($origin . strtok((string) ($_SERVER['REQUEST_URI'] ?? '/'), '?'));
$ogTitle = $ogTitle ?? ($title ?? 'Wedding Websites & More');
$ogDesc  = $ogDesc ?? ($desc ?? 'Wedding pages for African couples: invite, RSVP, gifts, spraying and livestream in one link.');
?>
<!doctype html>
<html lang="en"<?= $htmlAttr ?>>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($title ?? 'Wedding Websites & More') ?></title>
<?php if (!empty($desc)): ?><meta name="description" content="<?= e($desc) ?>"><?php endif; ?>
<meta name="theme-color" content="<?= e($fixTheme ? ($themeColor ?? '#6F8590') : ($themeColorNight ?? '#1E242E')) ?>" data-day="<?= e($themeColor ?? '#6F8590') ?>" data-night="<?= e($themeColorNight ?? '#1E242E') ?>">
<link rel="manifest" href="<?= e(App::url('manifest.webmanifest')) ?>">
<link rel="icon" href="<?= e(App::url('assets/img/brand/favicon.ico')) ?>" sizes="48x48">
<link rel="icon" href="<?= e(App::url('assets/img/brand/icon-192.png')) ?>" type="image/png" sizes="192x192">
<link rel="apple-touch-icon" href="<?= e(App::url('assets/img/brand/apple-touch-icon.png')) ?>">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Weddings">
<link rel="canonical" href="<?= e($canon) ?>">
<meta property="og:site_name" content="Wedding Websites &amp; More">
<meta property="og:type" content="<?= e($ogType ?? 'website') ?>">
<meta property="og:url" content="<?= e($canon) ?>">
<meta property="og:title" content="<?= e($ogTitle) ?>">
<meta property="og:description" content="<?= e($ogDesc) ?>">
<meta property="og:image" content="<?= e($og) ?>">
<meta property="og:image:secure_url" content="<?= e($og) ?>">
<meta property="og:image:type" content="image/jpeg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="<?= e($ogAlt ?? $ogTitle) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($ogTitle) ?>">
<meta name="twitter:description" content="<?= e($ogDesc) ?>">
<meta name="twitter:image" content="<?= e($og) ?>">
<link rel="preload" as="font" type="font/woff2" href="<?= e(App::url('assets/fonts/urbanist-latin.woff2')) ?>" crossorigin>
<link rel="stylesheet" href="<?= e(App::asset('assets/css/wwm.css')) ?>">
<?php if (!$fixTheme): ?>
<script>(function(){var t='night',q=new URLSearchParams(location.search).get('theme');try{if(localStorage.getItem('wwm-theme')==='day')t='day'}catch(e){}if(q==='day'||q==='night')t=q;document.documentElement.setAttribute('data-theme',t)})()</script>
<?php endif; ?>
<script type="speculationrules"><?= json_encode(['prefetch' => [[
	'where' => ['and' => [
		['href_matches' => rtrim($base, '/') . '/*'],
		['not' => ['href_matches' => [rtrim($base, '/') . '/sign-out', rtrim($base, '/') . '/hq/demo/*']]],
		['not' => ['selector_matches' => '[data-no-prefetch], [download], [target=_blank]']],
	]],
	'eagerness' => 'moderate',
]]], JSON_UNESCAPED_SLASHES) ?></script>
</head>
<body class="<?= e(trim(($bodyClass ?? '') . ($wed ? ' is-wed' : ''))) ?>">
<?= View::partial('partials/sprite') ?>
<?php if (!$bare && !$wed): ?>
	<?= View::partial('partials/site-head', ['tab' => $tab]) ?>
	<header class="appbar">
		<?php if ($back): ?>
			<a class="cbtn" href="<?= e($back) ?>" aria-label="Back" data-back><?= icon('back') ?></a>
		<?php else: ?>
			<a class="cbtn" href="<?= e($home) ?>" aria-label="Wedding Websites &amp; More, home"><span class="logo">&amp;</span></a>
		<?php endif; ?>
		<p class="appbar__title<?= mb_strlen($appTitle) > 16 ? ' appbar__title--long' : '' ?>"><?= e($appTitle) ?></p>
		<?= $action ?? '<button class="cbtn" type="button" data-theme-toggle aria-label="Switch between day and night">' . icon('moon') . '</button>' ?>
	</header>
<?php endif; ?>
<main id="main"<?= !empty($mainClass) ? ' class="' . e($mainClass) . '"' : '' ?>><?= $content ?></main>
<?php if (!$bare && !$wed): ?>
	<?= View::partial('partials/footer') ?>
	<?= View::partial('partials/tabs', ['tab' => $tab]) ?>
<?php endif; ?>
<script src="<?= e(App::asset('assets/js/wwm.js')) ?>" defer></script>
</body>
</html>
