<section class="sec wrap">
	<div class="sec__head">
		<div>
			<h2>Stays, rides, honeymoons &amp; aso-ebi</h2>
			<p class="sub"><?= e(number_format($total)) ?> listing<?= $total === 1 ? '' : 's' ?>. Your money is held until you confirm the booking.</p>
		</div>
	</div>

	<form class="hero__search wfind" method="get" role="search">
		<span class="hero__search-ico"><?= icon('search', 19) ?></span>
		<input type="search" name="q" value="<?= e($f['q']) ?>" placeholder="An apartment, a convoy, a honeymoon" aria-label="Search the marketplace">
		<button class="btn btn--sm" type="submit">Search</button>
	</form>

	<?php $q = static function (array $o) use ($f): string {
		$all = array_filter(array_merge($f, $o), static fn($v) => $v !== '' && $v !== null);
		return App::url('/market') . ($all ? '?' . http_build_query($all) : '');
	}; ?>
	<div class="rail chips">
		<?php foreach ($kinds as $k): ?>
			<a class="chip<?= $f['kind'] === $k['kind'] ? ' is-on' : '' ?>" href="<?= e($q(['kind' => $f['kind'] === $k['kind'] ? null : $k['kind'], 'page' => null])) ?>">
				<?= e(Listing::kindName((string) $k['kind'])) ?><em><?= e($k['n']) ?></em></a>
		<?php endforeach; ?>
	</div>
	<div class="rail chips">
		<?php foreach ($cities as $c): ?>
			<a class="chip<?= $f['city'] === $c['city'] ? ' is-on' : '' ?>" href="<?= e($q(['city' => $f['city'] === $c['city'] ? null : $c['city'], 'page' => null])) ?>">
				<?= e($c['city']) ?><em><?= e($c['n']) ?></em></a>
		<?php endforeach; ?>
	</div>

	<?php if (!$rows): ?>
		<div class="blank"><p class="blank__h">Nothing listed for that yet.</p><a class="btn btn--ghost" href="<?= e(App::url('/market')) ?>">Show all</a></div>
	<?php else: ?>
		<div class="vgrid">
			<?php foreach ($rows as $i => $l): ?>
				<a class="mcard mcard--page" href="<?= e(App::url('/market/' . $l['slug'])) ?>">
					<span class="mcard__art blob blob--<?= e(['orange','green','blue','pink'][$i % 4]) ?>"><?= photo(first_photo((string) $l['photos']), '') ?></span>
					<span class="mcard__k"><?= e(Listing::kindName((string) $l['kind'])) ?> &middot; <?= e($l['city']) ?></span>
					<b class="mcard__t"><?= e($l['title']) ?></b>
					<span class="mcard__p"><?= $l['on_ask'] ? 'On request' : money(short_money((int) $l['price'])) ?> <small><?= e($l['price_unit']) ?></small></span>
				</a>
			<?php endforeach; ?>
		</div>
		<?php if ($pages > 1): ?>
			<nav class="pager" aria-label="Pages">
				<?php for ($i = 1; $i <= $pages; $i++): ?><a class="pager__n<?= $i === $page ? ' is-on' : '' ?>" href="<?= e($q(['page' => $i === 1 ? null : $i])) ?>"><?= e($i) ?></a><?php endfor; ?>
			</nav>
		<?php endif; ?>
	<?php endif; ?>
</section>
