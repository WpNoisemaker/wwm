<?php
/** The top of every HQ screen. */
$now  = $now ?? '';
$tabs = array_values(array_filter([
	['hq', 'Overview', '/hq', 'grid', ''], ['weddings', 'Weddings', '/hq/weddings', 'ring', 'weddings'], ['requests', 'Requests' . (($rq = Managed::openCount()) ? ' (' . $rq . ')' : ''), '/hq/requests', 'chat', 'weddings'], ['people', 'People', '/hq/people', 'people', 'people'],
	['money', 'Money', '/hq/money', 'wallet', 'money'], ['market', 'Marketplace', '/hq/market', 'store', 'market'], ['blog', 'Blog', '/hq/blog', 'book', 'blog'], ['content', 'Site content', '/hq/content', 'pen', 'content'], ['ads', 'Ads', '/hq/ads', 'spark', 'content'],
	['staff', 'Staff', '/hq/staff', 'shield', 'owner'], ['settings', 'Settings', '/hq/settings', 'gear', 'settings'],
], static fn ($t) => $t[4] === '' || ($t[4] === 'owner' ? Auth::is('owner') : Staff::can($t[4]))));
?>
<div class="page wrap">
	<header class="page-head"><span class="eyebrow"><?= e($sub ?? 'Read straight from the database') ?></span><h1><?= e($h) ?></h1></header>
	<nav class="subnav" aria-label="HQ">
		<?php foreach ($tabs as [$k, $label, $href, $ic]): ?>
			<a href="<?= e(App::url($href)) ?>"<?= $now === $k ? ' class="is-on" aria-current="page"' : '' ?>><?= icon($ic) ?><?= e($label) ?></a>
		<?php endforeach; ?>
	</nav>
	<?= flashes() ?>
