<?php
/**
 * A country's own wedding listing: /weddings/uk, /weddings/ghana and the rest. Its own headline,
 * its own numbers, its cities and plans, the pages, the vendors who work there, then the other
 * countries.
 *
 * @var string $place @var string $country @var string $in @var string $line @var array $rows
 * @var int $total @var int $all @var int $pages @var int $page @var array $f @var array $cities
 * @var array $plans @var int $soon @var string $cur @var array $others @var array $vendors
 */
$base = App::url('/weddings/' . $place);
$q = static function (array $over) use ($f, $base): string {
	$all = array_filter(array_merge(['q' => $f['q'], 'plan' => $f['plan'], 'city' => $f['city'], 'sort' => $f['sort'] === 'trending' ? '' : $f['sort']], $over), static fn ($v) => $v !== '' && $v !== null);
	return $base . ($all ? '?' . http_build_query($all) : '');
};
$filtered = $f['q'] !== '' || $f['plan'] !== '' || $f['city'] !== '';
?>
<div class="page wrap place">
	<header class="page-head">
		<span class="eyebrow">African weddings in <?= e($in) ?></span>
		<h1>Weddings in <?= e($in) ?></h1>
		<p class="lede"><?= e($line) ?></p>
	</header>

	<section class="stats stats--place" aria-label="<?= e('Weddings in ' . $in . ' today') ?>">
		<div class="tile"><?= dots((string) $all, 'dotnum--md') ?><span class="k">Wedding page<?= $all === 1 ? '' : 's' ?></span></div>
		<div class="tile"><?= dots((string) count($cities), 'dotnum--md') ?><span class="k">Cit<?= count($cities) === 1 ? 'y' : 'ies' ?></span></div>
		<div class="tile"><?= dots((string) $soon, 'dotnum--md') ?><span class="k">In the next 90 days</span></div>
		<div class="tile"><span class="place__cur"><?= e(trim(cur_sign($cur))) ?></span><span class="k">Gifts in <?= e(Fx::many($cur)) ?></span></div>
	</section>

	<form class="searchbox narrow gap-t" method="get" action="<?= e($base) ?>" role="search">
		<?= icon('search') ?>
		<input class="input" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="<?= e('A couple, a city or a hashtag in ' . $in) ?>" aria-label="<?= e('Search weddings in ' . $in) ?>">
		<?php foreach (['plan', 'city', 'sort'] as $k): if ($f[$k] !== '' && !($k === 'sort' && $f[$k] === 'trending')): ?><input type="hidden" name="<?= $k ?>" value="<?= e($f[$k]) ?>"><?php endif; endforeach; ?>
	</form>

	<?php if ($all > 0): ?>
	<nav class="seg seg--center seg--plans gap-t-sm" aria-label="Plan">
		<a class="seg__a<?= $f['plan'] === '' ? ' is-on' : '' ?>" href="<?= e($q(['plan' => null, 'page' => null])) ?>">All plans</a>
		<?php foreach ($plans as $pk => $pn): if (!$pn) { continue; } ?>
			<a class="seg__a<?= $f['plan'] === $pk ? ' is-on' : '' ?>" href="<?= e($q(['plan' => $f['plan'] === $pk ? null : $pk, 'page' => null])) ?>"><?= e(Site::tier($pk)) ?> <span class="chip__n"><?= (int) $pn ?></span></a>
		<?php endforeach; ?>
	</nav>
	<nav class="chips chips--center gap-t-sm" aria-label="Sort and city">
		<?php foreach (Wedding::SORTS as $k => $label): ?>
			<a class="chip<?= $f['sort'] === $k ? ' is-on' : '' ?>" href="<?= e($q(['sort' => $k === 'trending' ? null : $k, 'page' => null])) ?>"<?= $f['sort'] === $k ? ' aria-current="true"' : '' ?>><?= e($label) ?></a>
		<?php endforeach; ?>
		<?php if (count($cities) > 1): foreach ($cities as $c): ?>
			<a class="chip<?= $f['city'] === $c['city'] ? ' is-on' : '' ?>" href="<?= e($q(['city' => $f['city'] === $c['city'] ? null : $c['city'], 'page' => null])) ?>"><?= e($c['city']) ?> <span class="chip__n"><?= (int) $c['n'] ?></span></a>
		<?php endforeach; endif; ?>
	</nav>
	<?php endif; ?>

	<?php if (!$rows): ?>
		<div class="glass glass--plain empty gap-t">
			<span class="cbtn"><?= icon('heart') ?></span>
			<?php if ($filtered): ?>
				<p>Nothing in <?= e($in) ?> matches that yet. Try a different spelling, or clear the filters.</p>
				<a class="pill pill--glass" href="<?= e($base) ?>">Every page in <?= e($in) ?></a>
			<?php else: ?>
				<p>No public wedding pages in <?= e($in) ?> yet. Getting married there? Yours can be the first, with gifts in <?= e(Fx::many($cur)) ?>.</p>
				<a class="pill" href="<?= e(App::url('/join')) ?>">Make your page free</a>
			<?php endif; ?>
		</div>
	<?php else: ?>
		<p class="center sub gap-t"><?= e(number_format($total)) ?><?= $f['plan'] !== '' ? ' ' . e(Site::tier($f['plan'])) : '' ?> page<?= $total === 1 ? '' : 's' ?><?= $f['city'] !== '' ? ' in ' . e($f['city']) : '' ?></p>
		<?php $ranks = Wedding::ranks($country); ?>
		<div class="cards gap-t-sm">
			<?php foreach ($rows as $w): ?>
				<?= wedding_card($w, trim(Wedding::short($w) . ' · ' . (string) $w['city'], ' ·'), Site::tier((string) $w['plan']), '', $ranks[(int) $w['id']] ?? 0) ?>
			<?php endforeach; ?>
		</div>
		<?= pager($page, $pages, ['q' => $f['q'], 'plan' => $f['plan'], 'city' => $f['city'], 'sort' => $f['sort'] === 'trending' ? '' : $f['sort']]) ?>
	<?php endif; ?>
</div>

<?php if ($vendors): ?>
<section class="sec wrap">
	<div class="split split--list">
		<div class="sec-head sec-head--side reveal">
			<span class="eyebrow">Booked by couples in <?= e($in) ?></span>
			<h2>Vendors who work here</h2>
			<p>Reviewed by the couples who booked them.</p>
			<a class="pill pill--glass pill--sm" href="<?= e(App::url('/vendors')) ?>">Every vendor <?= icon('arrow') ?></a>
		</div>
		<div class="rows reveal">
			<?php foreach ($vendors as $v): ?>
				<a class="row-item" href="<?= e(App::url('/vendors/' . $v['slug'])) ?>">
					<?= sq((string) $v['logo'], '', 'row-item__pic') ?>
					<span><span class="row-item__t"><?= e($v['name']) ?><?php if ((int) $v['verified']): ?> <span class="badge"><?= icon('check') ?>Verified</span><?php endif; ?></span><span class="row-item__s"><?= e($v['category'] . ' · ' . $v['city']) ?></span></span>
					<span class="row-item__end"><?= (float) $v['rating'] > 0 ? e(number_format((float) $v['rating'], 1)) : '' ?><span class="sub"><?= (int) $v['reviews'] ?> reviews</span></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="sec wrap">
	<div class="glass place__cta reveal">
		<div>
			<span class="eyebrow">Getting married in <?= e($in) ?>?</span>
			<h2>Your page, your currency</h2>
			<p class="sub">RSVPs, the programme, a registry and gifts in <?= e(Fx::many($cur)) ?>, and family back home can give and watch from anywhere.</p>
		</div>
		<a class="pill" href="<?= e(App::url('/join')) ?>">Make your page free</a>
	</div>
</section>

<?php if ($others): ?>
<section class="sec wrap">
	<div class="sec-head reveal"><span class="eyebrow">Wherever the family is</span><h2>More countries</h2></div>
	<div class="mosaic world reveal">
		<?php foreach ($others as $slug => $o): ?>
			<a class="tile world__i" href="<?= e(App::url('/weddings/' . $slug)) ?>"><span class="k"><?= (int) $o['n'] ?> page<?= (int) $o['n'] === 1 ? '' : 's' ?></span><span class="v"><?= e(ucfirst(preg_replace('/^the /', '', $o['name']))) ?></span></a>
		<?php endforeach; ?>
		<a class="tile world__i" href="<?= e(App::url('/weddings')) ?>"><span class="k">Every page</span><span class="v">Everywhere</span></a>
	</div>
</section>
<?php endif; ?>
