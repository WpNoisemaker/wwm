<?php
/** The desktop nav rail. A visitor sees the places to browse; the account links only appear
 *  once somebody is signed in. Hidden on a phone, where the tab bar does this job. */
$now  = $tab ?? '';
$nav  = [
	['home',  'Home',       '/',          'home'],
	['rings', 'Weddings',   '/weddings',  'weddings'],
	['chart', 'Buzz Chart', '/rankings',  'rankings'],
	['store', 'Vendors',    '/vendors',   'vendors'],
	['cash',  'Market',     '/market',    'market'],
	['talk',  'Community',  '/community', 'community'],
	['book',  'Stories',    '/blog',      'blog'],
];
$mine = Auth::check() ? [
	['user', 'Your page', '/account',        'account'],
	['gift', 'Money',     '/account/money',  'money'],
	['cal',  'Guests',    '/account/guests', 'guests'],
] : [];
?>
<nav class="rail-nav" aria-label="Sections">
	<?php foreach ($nav as [$i, $t, $h, $k]): ?>
		<a href="<?= e(App::url($h)) ?>"<?= $now === $k ? ' class="is-on" aria-current="page"' : '' ?>><?= icon($i, 21) ?><span><?= e($t) ?></span></a>
	<?php endforeach; ?>
	<?php if ($mine): ?>
		<hr>
		<?php foreach ($mine as [$i, $t, $h, $k]): ?>
			<a href="<?= e(App::url($h)) ?>"<?= $now === $k ? ' class="is-on" aria-current="page"' : '' ?>><?= icon($i, 21) ?><span><?= e($t) ?></span></a>
		<?php endforeach; ?>
	<?php endif; ?>
	<a class="btn btn--acid rail-nav__cta" href="<?= e(App::url('/join')) ?>"><?= icon('plus', 19) ?><?= Auth::check() ? 'New page' : 'Create free' ?></a>
</nav>
