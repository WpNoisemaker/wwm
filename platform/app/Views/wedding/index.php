<?php
/** The wedding directory: search, a row of sorts and styles, the cities, then the pages. */
$q = static function (array $over) use ($f): string {
	$all = array_filter(array_merge(['q' => $f['q'], 'plan' => $f['plan'], 'country' => $f['country'], 'city' => $f['city'], 'style' => $f['style'], 'sort' => $f['sort'] === 'trending' ? null : $f['sort']], $over), static fn ($v) => $v !== '' && $v !== null);
	return App::url('/weddings') . ($all ? '?' . http_build_query($all) : '');
};
?>
<div class="page wrap">
	<header class="page-head">
		<span class="eyebrow"><?= t('weddings.eyebrow.real-pages-real-guests', 'Real pages, real guests') ?></span>
		<h1><?= $f['q'] !== '' ? 'Weddings matching “' . e($f['q']) . '”' : 'Wedding pages' ?></h1>
		<p class="lede"><?= e(number_format($total)) ?><?= $f['plan'] !== '' ? ' ' . e(Site::tier($f['plan'])) : '' ?> page<?= $total === 1 ? '' : 's' ?><?= $f['city'] !== '' ? ' in ' . e($f['city']) : ($f['country'] !== '' ? ' in ' . e($f['country']) : '') ?><?= $f['style'] !== '' ? ' · ' . e($f['style']) : '' ?></p>
	</header>

	<form class="searchbox narrow" method="get" action="<?= e(App::url('/weddings')) ?>" role="search">
		<?= icon('search') ?>
		<input class="input" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="A couple, a city or a hashtag" aria-label="Search weddings">
		<?php foreach (['plan', 'country', 'city', 'style', 'sort'] as $k): if ($f[$k] !== '' && !($k === 'sort' && $f[$k] === 'trending')): ?><input type="hidden" name="<?= $k ?>" value="<?= e($f[$k]) ?>"><?php endif; endforeach; ?>
	</form>

	<nav class="seg seg--center seg--plans gap-t-sm" aria-label="Plan">
		<a class="seg__a<?= $f['plan'] === '' ? ' is-on' : '' ?>" href="<?= e($q(['plan' => null, 'page' => null])) ?>">All plans</a>
		<?php foreach ($plans as $pk => $pn): if (!$pn) { continue; } ?>
			<a class="seg__a<?= $f['plan'] === $pk ? ' is-on' : '' ?>" href="<?= e($q(['plan' => $f['plan'] === $pk ? null : $pk, 'page' => null])) ?>"><?= e(Site::tier($pk)) ?> <span class="chip__n"><?= (int) $pn ?></span></a>
		<?php endforeach; ?>
	</nav>
	<nav class="chips chips--center gap-t-sm" aria-label="Sort and style">
		<?php foreach (Wedding::SORTS as $k => $label): ?>
			<a class="chip<?= $f['sort'] === $k ? ' is-on' : '' ?>" href="<?= e($q(['sort' => $k === 'trending' ? null : $k, 'page' => null])) ?>"<?= $f['sort'] === $k ? ' aria-current="true"' : '' ?>><?= e($label) ?></a>
		<?php endforeach; ?>
		<?php foreach ($styles as $st): ?>
			<a class="chip<?= $f['style'] === $st ? ' is-on' : '' ?>" href="<?= e($q(['style' => $f['style'] === $st ? null : $st, 'page' => null])) ?>"><?= e($st) ?></a>
		<?php endforeach; ?>
	</nav>
	<?php if (count($countries) > 1): ?>
		<nav class="chips chips--center chips--quiet" aria-label="Countries">
			<a class="chip<?= $f['country'] === '' ? ' is-on' : '' ?>" href="<?= e($q(['country' => null, 'city' => null, 'page' => null])) ?>">Everywhere</a>
			<?php foreach ($countries as $c): ?>
				<?php $slug = Wedding::placeSlug((string) $c['country']); ?>
				<a class="chip<?= $f['country'] === $c['country'] ? ' is-on' : '' ?>" href="<?= e($slug !== '' ? App::url('/weddings/' . $slug) : $q(['country' => $f['country'] === $c['country'] ? null : $c['country'], 'city' => null, 'page' => null])) ?>"><?= e($c['country']) ?> <span class="chip__n"><?= (int) $c['n'] ?></span></a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>
	<?php if ($cities && $f['country'] === ''): ?>
		<nav class="chips chips--center chips--quiet" aria-label="Cities">
			<?php foreach ($cities as $c): ?>
				<a class="chip<?= $f['city'] === $c['city'] ? ' is-on' : '' ?>" href="<?= e($q(['city' => $f['city'] === $c['city'] ? null : $c['city'], 'page' => null])) ?>"><?= e($c['city']) ?> <span class="chip__n"><?= (int) $c['n'] ?></span></a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>

	<?php if (!$rows): ?>
		<div class="glass glass--plain empty gap-t">
			<span class="cbtn"><?= icon('search') ?></span>
			<p><?= t('weddings.text.nothing-matches-that-yet-try', 'Nothing matches that yet. Try a different spelling, or clear the filters.') ?></p>
			<a class="pill pill--glass" href="<?= e(App::url('/weddings')) ?>"><?= t('weddings.button.show-every-page', 'Show every page') ?></a>
		</div>
	<?php else: ?>
		<?php $ranks = Wedding::ranks(); ?>
		<div class="cards gap-t">
			<?php foreach ($rows as $ci => $w): ?>
				<?php if ($ci === 6): ?><?= Promo::html('card', 2) ?><?php endif; ?>
				<?= wedding_card($w, trim(Wedding::short($w) . ' · ' . Wedding::place($w), ' ·'), Site::tier((string) $w['plan']), '', $ranks[(int) $w['id']] ?? 0) ?>
			<?php endforeach; ?>
		</div>
		<?= pager($page, $pages, ['q' => $f['q'], 'plan' => $f['plan'], 'country' => $f['country'], 'city' => $f['city'], 'style' => $f['style'], 'sort' => $f['sort'] === 'trending' ? '' : $f['sort']]) ?>
	<?php endif; ?>
</div>
<?php if ($page === 1 && $f['q'] === '' && $f['city'] === '' && $f['country'] === ''): ?>
<?= View::partial('partials/near', ['eyebrow' => 'Near you', 'heading' => 'Weddings in your city']) ?>
<?php endif; ?>
