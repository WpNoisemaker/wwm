<?php
/** The wedding directory: search, a row of sorts and styles, the cities, then the pages. */
$q = static function (array $over) use ($f): string {
	$all = array_filter(array_merge(['q' => $f['q'], 'city' => $f['city'], 'style' => $f['style'], 'sort' => $f['sort']], $over), static fn ($v) => $v !== '' && $v !== null);
	return App::url('/weddings') . ($all ? '?' . http_build_query($all) : '');
};
?>
<div class="page wrap">
	<header class="page-head">
		<span class="eyebrow">Real pages, real guests</span>
		<h1><?= $f['q'] !== '' ? 'Weddings matching “' . e($f['q']) . '”' : 'Wedding pages' ?></h1>
		<p class="lede"><?= e(number_format($total)) ?> page<?= $total === 1 ? '' : 's' ?><?= $f['city'] !== '' ? ' in ' . e($f['city']) : '' ?><?= $f['style'] !== '' ? ' · ' . e($f['style']) : '' ?></p>
	</header>

	<form class="searchbox narrow" method="get" action="<?= e(App::url('/weddings')) ?>" role="search">
		<?= icon('search') ?>
		<input class="input" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="A couple, a city or a hashtag" aria-label="Search weddings">
		<?php foreach (['city', 'style', 'sort'] as $k): if ($f[$k] !== '' && !($k === 'sort' && $f[$k] === 'soon')): ?><input type="hidden" name="<?= $k ?>" value="<?= e($f[$k]) ?>"><?php endif; endforeach; ?>
	</form>

	<nav class="chips chips--center gap-t-sm" aria-label="Sort and style">
		<?php foreach ([['soon', 'Coming up'], ['new', 'Just added'], ['buzz', 'Most buzz']] as [$k, $label]): ?>
			<a class="chip<?= $f['sort'] === $k ? ' is-on' : '' ?>" href="<?= e($q(['sort' => $k === 'soon' ? null : $k, 'page' => null])) ?>"<?= $f['sort'] === $k ? ' aria-current="true"' : '' ?>><?= e($label) ?></a>
		<?php endforeach; ?>
		<?php foreach ($styles as $st): ?>
			<a class="chip<?= $f['style'] === $st ? ' is-on' : '' ?>" href="<?= e($q(['style' => $f['style'] === $st ? null : $st, 'page' => null])) ?>"><?= e($st) ?></a>
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
		<div class="glass glass--plain empty gap-t">
			<span class="cbtn"><?= icon('search') ?></span>
			<p>Nothing matches that yet. Try a different spelling, or clear the filters.</p>
			<a class="pill pill--glass" href="<?= e(App::url('/weddings')) ?>">Show every page</a>
		</div>
	<?php else: ?>
		<div class="cards gap-t">
			<?php foreach ($rows as $w): ?>
				<?= wedding_card($w, trim(Wedding::short($w) . ' · ' . (string) $w['city'], ' ·'), Site::tier((string) $w['plan'])) ?>
			<?php endforeach; ?>
		</div>
		<?= pager($page, $pages, ['q' => $f['q'], 'city' => $f['city'], 'style' => $f['style'], 'sort' => $f['sort'] === 'soon' ? '' : $f['sort']]) ?>
	<?php endif; ?>
</div>
