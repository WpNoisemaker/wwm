<?php
/** The desktop header: brand, the sections in a glass pill, then search, theme and account. */
$now = $tab ?? '';
$nav = require __DIR__ . '/nav.php';
?>
<header class="site-head">
	<a class="brand" href="<?= e(App::url('/')) ?>"><span class="cbtn"><span class="logo">&amp;</span></span>Wedding Websites &amp; More</a>
	<nav class="site-nav" aria-label="Sections">
		<?php foreach ($nav as [$i, $t, $h, $k]): ?>
			<a href="<?= e(App::url($h)) ?>"<?= $now === $k ? ' class="is-here" aria-current="page"' : '' ?>><?= e($t) ?></a>
		<?php endforeach; ?>
	</nav>
	<div class="site-tools">
		<a class="cbtn" href="<?= e(App::url('/search')) ?>" aria-label="Search"><?= icon('search') ?></a>
		<button class="cbtn" type="button" data-theme-toggle aria-label="Switch between day and night"><?= icon('auto') ?></button>
		<?php if (Auth::check()): ?>
			<a class="pill pill--sm" href="<?= e(App::url('/account')) ?>">Your page</a>
		<?php else: ?>
			<a class="link" href="<?= e(App::url('/sign-in')) ?>">Sign in</a>
			<a class="pill pill--sm" href="<?= e(App::url('/join')) ?>">Create your page</a>
		<?php endif; ?>
	</div>
</header>
