<?php
/** The marketplace: aso-ebi, stays, rides and honeymoons. */
$q = static function (array $o) use ($f): string {
	$all = array_filter(array_merge($f, $o), static fn ($v) => $v !== '' && $v !== null);
	return App::url('/market') . ($all ? '?' . http_build_query($all) : '');
};
$price = static fn (array $l): string => (int) $l['on_ask'] ? 'Price on request' : money(short_money((int) $l['price'])) . ((string) $l['price_unit'] !== '' ? ' ' . e($l['price_unit']) : '');
?>
<div class="page wrap">
	<header class="page-head">
		<span class="eyebrow"><?= t('market.eyebrow.message-the-sellers', 'Message the sellers and pay them directly') ?></span>
		<h1><?= t('market.title.asoebi-stays-rides-honeymoons', 'Aso-ebi, shortlets, rides & honeymoons') ?></h1>
		<p class="lede"><?= e(number_format($total)) ?> listing<?= $total === 1 ? '' : 's' ?><?= $f['city'] !== '' ? ' in ' . e($f['city']) : '' ?></p>
	</header>
	<a class="sell-cta glass" href="<?= e(App::url('/sell')) ?>">
		<span class="sell-cta__ico"><?= icon('s-store') ?></span>
		<span class="sell-cta__in">
			<span class="sell-cta__t"><?= t('market.sell.title', 'Sell to couples planning their wedding') ?></span>
			<span class="sell-cta__s"><?= t('market.sell.text', 'List shortlets, rides, aso-ebi, honeymoons and services. Customers message you, and you send your own invoice or payment link.') ?></span>
		</span>
		<span class="pill pill--sm sell-cta__go"><?= t('market.sell.button', 'Sell with us') ?></span>
	</a>
	<form class="searchbox narrow" method="get" action="<?= e(App::url('/market')) ?>" role="search">
		<?= icon('search') ?>
		<input class="input" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="An apartment, a convoy, a honeymoon" aria-label="Search the marketplace">
		<?php foreach (['kind', 'city'] as $k): if (($f[$k] ?? '') !== ''): ?><input type="hidden" name="<?= $k ?>" value="<?= e($f[$k]) ?>"><?php endif; endforeach; ?>
	</form>
	<nav class="chips chips--center gap-t-sm" aria-label="Kinds">
		<a class="chip<?= $f['kind'] === '' ? ' is-on' : '' ?>" href="<?= e($q(['kind' => null, 'page' => null])) ?>">Everything</a>
		<?php foreach ($kinds as $k): ?>
			<a class="chip<?= $f['kind'] === $k['kind'] ? ' is-on' : '' ?>" href="<?= e($q(['kind' => $f['kind'] === $k['kind'] ? null : $k['kind'], 'page' => null])) ?>"><?= e(Market::catName((string) $k['kind'])) ?></a>
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
		<div class="glass glass--plain empty gap-t"><span class="cbtn"><?= icon('bag') ?></span><p><?= t('market.text.nothing-listed-for-that-yet', 'Nothing is listed for that yet.') ?></p><a class="pill pill--glass" href="<?= e(App::url('/market')) ?>"><?= t('market.button.show-everything', 'Show everything') ?></a></div>
	<?php else: ?>
		<div class="cards gap-t">
			<?php foreach ($rows as $l): ?>
				<?= View::partial('market/_card', ['l' => $l]) ?>
			<?php endforeach; ?>
		</div>
		<?= pager($page, $pages, $f) ?>
	<?php endif; ?>
</div>
