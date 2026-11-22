<?php
/** The top bar. On a phone it is just brand and two icons, with the tab bar doing navigation.
 *  From 900px it becomes the site header a visitor expects on the web: brand, the sections across
 *  the middle, then search, account and the call to action. */
$now = $tab ?? '';
$nav = require __DIR__ . '/nav.php';
?>
<header class="bar" data-bar>
	<?= View::partial('partials/brand') ?>

	<nav class="bar__nav" aria-label="Sections">
		<?php foreach ($nav as [$i, $t, $h, $k]): ?>
			<a href="<?= e(App::url($h)) ?>"<?= $now === $k ? ' class="is-on" aria-current="page"' : '' ?>><?= e($t) ?></a>
		<?php endforeach; ?>
	</nav>

	<span class="bar__sp"></span>
	<a class="ico" href="<?= e(App::url('/search')) ?>" aria-label="Search"><?= icon('search', 20) ?></a>
	<?php if (Auth::check()): ?>
		<a class="ico" href="<?= e(App::url('/account')) ?>" aria-label="Your account"><?= icon('user', 20) ?></a>
	<?php else: ?>
		<a class="bar__in" href="<?= e(App::url('/sign-in')) ?>">Sign in</a>
		<a class="ico" href="<?= e(App::url('/account')) ?>" aria-label="Your account"><?= icon('user', 20) ?></a>
	<?php endif; ?>
	<a class="btn btn--acid btn--sm bar__cta" href="<?= e(App::url('/join')) ?>"><?= Auth::check() ? 'New page' : 'Create free' ?></a>
</header>
