<?php
/** The phone menu: every section the desktop header has, plus search, help and day/night. */
$now = $tab ?? '';
$nav = require __DIR__ . '/nav.php';
$in  = Auth::check();
$more = [
	['search', 'Search',      '/search',  'search'],
	['store',  'Sell with us', '/sell',   'sell'],
	['info',   'About us',    '/about',   'about'],
	['shield', 'Help',        '/help',    'help'],
];
?>
<div class="sheet" id="menu" role="dialog" aria-modal="true" aria-labelledby="menu-t">
	<div class="sheet__grab" aria-hidden="true"></div>
	<div class="sheet__head">
		<a class="cbtn cbtn--sm" href="#" data-close aria-label="Close"><?= icon('close') ?></a>
		<h2 id="menu-t">Menu</h2>
		<button class="cbtn cbtn--sm" type="button" data-theme-toggle aria-label="Switch between day and night"><?= icon('moon') ?></button>
	</div>
	<nav class="mnu" aria-label="All sections">
		<?php foreach (array_merge($nav, $more) as [$i, $t, $h, $k]): ?>
			<a class="mnu__i<?= $now === $k ? ' is-here' : '' ?>" href="<?= e(App::url($h)) ?>"<?= $now === $k ? ' aria-current="page"' : '' ?>><?= icon($i) ?><span><?= e($t) ?></span></a>
		<?php endforeach; ?>
	</nav>
	<div class="mnu__foot">
		<?php if ($in): ?>
			<a class="pill pill--block" href="<?= e(App::url('/account')) ?>">Your page</a>
		<?php else: ?>
			<a class="pill pill--block" href="<?= e(App::url('/join')) ?>">Create your page</a>
			<a class="pill pill--block pill--glass" href="<?= e(App::url('/sign-in')) ?>">Sign in</a>
		<?php endif; ?>
	</div>
</div>
