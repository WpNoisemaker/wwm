<section class="sec wrap">
	<div class="sec__head">
		<div>
			<h2>The people who make the day</h2>
			<p class="sub"><?= e(number_format($total)) ?> vendor<?= $total === 1 ? '' : 's' ?>, reviewed by couples who actually booked them.</p>
		</div>
	</div>

	<form class="hero__search wfind" method="get" role="search">
		<span class="hero__search-ico"><?= icon('search', 19) ?></span>
		<input type="search" name="q" value="<?= e($f['q']) ?>" placeholder="A name, a trade or a city" aria-label="Search vendors">
		<button class="btn btn--sm" type="submit">Search</button>
	</form>

	<?php $q = static function (array $o) use ($f): string {
		$all = array_filter(array_merge($f, $o), static fn($v) => $v !== '' && $v !== null);
		return App::url('/vendors') . ($all ? '?' . http_build_query($all) : '');
	}; ?>
	<div class="rail chips">
		<?php foreach ($cats as $c): ?>
			<a class="chip<?= $f['category'] === $c['category'] ? ' is-on' : '' ?>" href="<?= e($q(['category' => $f['category'] === $c['category'] ? null : $c['category'], 'page' => null])) ?>">
				<?= e($c['category']) ?><em><?= e($c['n']) ?></em></a>
		<?php endforeach; ?>
	</div>
	<div class="rail chips">
		<?php foreach ($cities as $c): ?>
			<a class="chip<?= $f['city'] === $c['city'] ? ' is-on' : '' ?>" href="<?= e($q(['city' => $f['city'] === $c['city'] ? null : $c['city'], 'page' => null])) ?>">
				<?= e($c['city']) ?><em><?= e($c['n']) ?></em></a>
		<?php endforeach; ?>
	</div>

	<?php if (!$rows): ?>
		<div class="blank"><p class="blank__h">No vendors match that.</p><a class="btn btn--ghost" href="<?= e(App::url('/vendors')) ?>">Show all</a></div>
	<?php else: ?>
		<div class="vgrid">
			<?php foreach ($rows as $v): ?>
				<a class="vcard" href="<?= e(App::url('/vendors/' . $v['slug'])) ?>">
					<span class="vcard__art blob blob--pink"><?= photo((string) $v['logo'], '') ?></span>
					<span class="vcard__body">
						<b><?= e($v['name']) ?></b>
						<small><?= e(trim(($v['category'] ? $v['category'] . ', ' : '') . $v['city'], ', ')) ?></small>
						<span class="vcard__foot">
							<?php if ($v['verified']): ?><em class="pill">Verified</em><?php endif; ?>
							<span class="vcard__rate"><?= e(number_format((float) $v['rating'], 1)) ?> &#9733; <small>(<?= e((int) $v['reviews']) ?>)</small></span>
						</span>
					</span>
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
