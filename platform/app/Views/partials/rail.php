<?php
/** The desktop nav rail. Hidden on a phone, where the tab bar does this job. */
$now  = $tab ?? '';
$nav  = [
	['home',      'Home',       '/',          'home'],
	['rings',     'Weddings',   '/weddings',  'weddings'],
	['talk',      'Community',  '/community', 'community'],
	['store',     'Market',     '/market',    'market'],
	['chart',     'Rankings',   '/rankings',  'rankings'],
];
$mine = [
	['user',  'Your page',  '/account',       'account'],
	['cash',  'Money',      '/account/money', 'money'],
	['cal',   'Guests',     '/account/guests','guests'],
];
?>
<nav class="rail-nav" aria-label="Sections">
	<?php foreach ($nav as [$i, $t, $h, $k]): ?>
		<a href="<?= e(App::url($h)) ?>"<?= $now === $k ? ' class="is-on" aria-current="page"' : '' ?>><?= icon($i, 21) ?><span><?= e($t) ?></span></a>
	<?php endforeach; ?>
	<hr>
	<?php foreach ($mine as [$i, $t, $h, $k]): ?>
		<a href="<?= e(App::url($h)) ?>"<?= $now === $k ? ' class="is-on" aria-current="page"' : '' ?>><?= icon($i, 21) ?><span><?= e($t) ?></span></a>
	<?php endforeach; ?>
	<a class="btn btn--acid rail-nav__cta" href="<?= e(App::url('/account/new')) ?>"><?= icon('plus', 19) ?>New page</a>
</nav>
