<?php
/** The top of every HQ screen. */
$now  = $now ?? '';
$tabs = [['hq', 'Overview', '/hq', 'grid'], ['weddings', 'Weddings', '/hq/weddings', 'ring'], ['people', 'People', '/hq/people', 'people'],
         ['money', 'Money', '/hq/money', 'wallet'], ['settings', 'Settings', '/hq/settings', 'gear']];
?>
<div class="page wrap">
	<header class="page-head"><span class="eyebrow"><?= e($sub ?? 'Read straight from the database') ?></span><h1><?= e($h) ?></h1></header>
	<nav class="subnav" aria-label="HQ">
		<?php foreach ($tabs as [$k, $label, $href, $ic]): ?>
			<a href="<?= e(App::url($href)) ?>"<?= $now === $k ? ' class="is-on" aria-current="page"' : '' ?>><?= icon($ic) ?><?= e($label) ?></a>
		<?php endforeach; ?>
	</nav>
	<?= flashes() ?>
