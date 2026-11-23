<?php
/** The phone tab bar. Four places and the one thing people come here to do: make a page. */
$now  = $tab ?? '';
$in   = Auth::check();
$tabs = [
	['home', 'Home', '/', 'home'],
	['ring', 'Weddings', '/weddings', 'weddings'],
	null,
	['chat', 'Anonymous Community', '/community', 'community'],
	['user', $in ? 'You' : 'Sign in', $in ? '/account' : '/sign-in', 'account'],
];
?>
<nav class="tabbar" aria-label="App">
	<?php foreach ($tabs as $t): ?>
		<?php if ($t === null): ?>
			<a class="tab-plus" href="<?= e(App::url($in ? '/account/new' : '/join')) ?>" aria-label="Create a wedding page"><span><?= icon('plus') ?></span></a>
		<?php else: [$i, $label, $h, $k] = $t; ?>
			<a href="<?= e(App::url($h)) ?>"<?= $now === $k ? ' class="is-here" aria-current="page"' : '' ?>><?= icon($i) ?><?= e($label) ?></a>
		<?php endif; ?>
	<?php endforeach; ?>
</nav>
