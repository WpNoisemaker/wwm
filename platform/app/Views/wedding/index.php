<?php /** The directory. */ ?>
<section class="sec wrap">
	<div class="sec__head">
		<div>
			<h2><?= $f['q'] !== '' ? 'Weddings matching &ldquo;' . e($f['q']) . '&rdquo;' : 'Wedding pages' ?></h2>
			<p class="sub"><?= e(number_format($total)) ?> page<?= $total === 1 ? '' : 's' ?><?= $f['city'] !== '' ? ' in ' . e($f['city']) : '' ?><?= $f['style'] !== '' ? ' &middot; ' . e($f['style']) : '' ?></p>
		</div>
	</div>

	<form class="hero__search wfind" method="get" action="<?= e(App::url('/weddings')) ?>" role="search">
		<span class="hero__search-ico"><?= icon('search', 19) ?></span>
		<input type="search" name="q" value="<?= e($f['q']) ?>" placeholder="A couple, a city or a hashtag" aria-label="Search weddings">
		<?php if ($f['city']) : ?><input type="hidden" name="city" value="<?= e($f['city']) ?>"><?php endif; ?>
		<?php if ($f['style']): ?><input type="hidden" name="style" value="<?= e($f['style']) ?>"><?php endif; ?>
		<button class="btn btn--sm" type="submit">Search</button>
	</form>

	<div class="wfilters">
		<div class="rail chips">
			<?php
			$q = static function (array $over) use ($f): string {
				$base = array_filter(['q' => $f['q'], 'city' => $f['city'], 'style' => $f['style'], 'sort' => $f['sort']]);
				$all  = array_filter(array_merge($base, $over), static fn($v) => $v !== '' && $v !== null);
				return App::url('/weddings') . ($all ? '?' . http_build_query($all) : '');
			};
			?>
			<?php foreach ([['soon', 'Coming up'], ['new', 'Just added'], ['buzz', 'Most buzz']] as [$k, $label]): ?>
				<a class="chip<?= $f['sort'] === $k ? ' is-on' : '' ?>" href="<?= e($q(['sort' => $k, 'page' => null])) ?>"><?= e($label) ?></a>
			<?php endforeach; ?>
			<span class="wfilters__gap"></span>
			<?php foreach ($styles as $st): ?>
				<a class="chip<?= $f['style'] === $st ? ' is-on' : '' ?>" href="<?= e($q(['style' => $f['style'] === $st ? null : $st, 'page' => null])) ?>"><?= e($st) ?></a>
			<?php endforeach; ?>
		</div>
		<div class="rail chips">
			<?php foreach ($cities as $c): ?>
				<a class="chip<?= $f['city'] === $c['city'] ? ' is-on' : '' ?>" href="<?= e($q(['city' => $f['city'] === $c['city'] ? null : $c['city'], 'page' => null])) ?>">
					<?= e($c['city']) ?><em><?= e($c['n']) ?></em>
				</a>
			<?php endforeach; ?>
		</div>
	</div>

	<?php if (!$rows): ?>
		<div class="blank">
			<p class="blank__h">Nothing here yet.</p>
			<p class="sub">Try a different spelling, or clear the filters.</p>
			<a class="btn btn--ghost" href="<?= e(App::url('/weddings')) ?>">Show everything</a>
		</div>
	<?php else: ?>
		<div class="wgrid wgrid--page">
			<?php foreach ($rows as $w): $d = Wedding::palette($w); ?>
				<a class="wcard" href="<?= e(App::url('/w/' . $w['slug'])) ?>" style="--p:<?= e($d['p']) ?>;--a:<?= e($d['a']) ?>">
					<span class="wcard__art"><?= photo((string) $w['cover'], '') ?></span>
					<span class="wcard__tier"><?= e(Site::tier((string) $w['plan'])) ?></span>
					<span class="wcard__body">
						<b><?= e(Wedding::names($w)) ?></b>
						<small><?= e(Wedding::when($w)) ?><?= $w['city'] ? ' &middot; ' . e($w['city']) : '' ?></small>
						<small class="wcard__meta"><?= e((int) $w['guests']) ?> coming &middot; <?= e((int) $w['notes']) ?> notes</small>
					</span>
				</a>
			<?php endforeach; ?>
		</div>

		<?php if ($pages > 1): ?>
			<nav class="pager" aria-label="Pages">
				<?php for ($i = 1; $i <= $pages; $i++): ?>
					<a class="pager__n<?= $i === $page ? ' is-on' : '' ?>" href="<?= e($q(['page' => $i === 1 ? null : $i])) ?>"><?= e($i) ?></a>
				<?php endfor; ?>
			</nav>
		<?php endif; ?>
	<?php endif; ?>
</section>
