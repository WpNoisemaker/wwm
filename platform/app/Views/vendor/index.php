<?php
/** Vendors: search, trades and cities as chips, then squircle cards. */
$q = static function (array $o) use ($f): string {
	$all = array_filter(array_merge($f, $o), static fn ($v) => $v !== '' && $v !== null);
	return App::url('/vendors') . ($all ? '?' . http_build_query($all) : '');
};
?>
<div class="page wrap">
	<header class="page-head">
		<span class="eyebrow"><?= t('vendors.eyebrow.reviewed-by-couples-who-booked', 'Reviewed by couples who booked them') ?></span>
		<h1><?= t('vendors.title.the-people-behind-the-day', 'Wedding vendors') ?></h1>
		<p class="lede"><?= e(number_format($total)) ?> vendor<?= $total === 1 ? '' : 's' ?><?= $f['city'] !== '' ? ' in ' . e($f['city']) : '' ?></p>
	</header>
	<form class="searchbox narrow" method="get" action="<?= e(App::url('/vendors')) ?>" role="search">
		<?= icon('search') ?>
		<input class="input" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="A name, a trade or a city" aria-label="Search vendors">
		<?php foreach (['category', 'city'] as $k): if ($f[$k] !== ''): ?><input type="hidden" name="<?= $k ?>" value="<?= e($f[$k]) ?>"><?php endif; endforeach; ?>
	</form>
	<nav class="chips chips--center gap-t-sm" aria-label="Trades">
		<a class="chip<?= $f['category'] === '' ? ' is-on' : '' ?>" href="<?= e($q(['category' => null, 'page' => null])) ?>">Everyone</a>
		<?php foreach ($cats as $c): ?>
			<a class="chip<?= $f['category'] === $c['category'] ? ' is-on' : '' ?>" href="<?= e($q(['category' => $f['category'] === $c['category'] ? null : $c['category'], 'page' => null])) ?>"><?= e($c['category']) ?></a>
		<?php endforeach; ?>
	</nav>
	<?php if ($cities): ?>
		<nav class="chips chips--center chips--quiet" aria-label="Cities">
			<?php foreach ($cities as $c): ?>
				<a class="chip<?= $f['city'] === $c['city'] ? ' is-on' : '' ?>" href="<?= e($q(['city' => $f['city'] === $c['city'] ? null : $c['city'], 'page' => null])) ?>"><?= e($c['city']) ?> <span class="chip__n"><?= (int) $c['n'] ?></span></a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>

	<?php if (!$rows): ?>
		<div class="glass glass--plain empty gap-t"><span class="cbtn"><?= icon('store') ?></span><p><?= t('vendors.text.no-vendors-match-that-yet', 'No vendors match that yet.') ?></p><a class="pill pill--glass" href="<?= e(App::url('/vendors')) ?>"><?= t('vendors.button.show-everyone', 'Show everyone') ?></a></div>
	<?php else: ?>
		<div class="cards gap-t">
			<?php foreach ($rows as $v): ?>
				<a class="card" href="<?= e(App::url('/vendors/' . $v['slug'])) ?>">
					<span class="sq"><?= photo((string) $v['logo'], (string) $v['name']) ?><?php if ((int) $v['verified']): ?><span class="sq__tag">Verified</span><?php endif; ?></span>
					<span class="card__cap"><span class="card__t"><?= e($v['name']) ?></span><span class="card__s"><?= e(trim($v['category'] . ' · ' . $v['city'], ' ·')) ?><?= (float) $v['rating'] > 0 ? ' · ' . e(number_format((float) $v['rating'], 1)) . ' ★' : '' ?></span></span>
				</a>
			<?php endforeach; ?>
		</div>
		<?= pager($page, $pages, $f) ?>
	<?php endif; ?>
</div>
