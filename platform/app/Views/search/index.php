<section class="sec wrap">
	<div class="sec__head"><div><h2>Search</h2><?php if ($q !== ''): ?><p class="sub"><?= e(number_format($total)) ?> result<?= $total === 1 ? '' : 's' ?> for &ldquo;<?= e($q) ?>&rdquo;</p><?php endif; ?></div></div>
	<form class="hero__search wfind" method="get" role="search">
		<span class="hero__search-ico"><?= icon('search', 19) ?></span>
		<input type="search" name="q" value="<?= e($q) ?>" autofocus placeholder="A couple, a vendor, a city, an apartment" aria-label="Search the site">
		<button class="btn btn--sm" type="submit">Search</button>
	</form>

	<?php if ($q === ''): ?>
		<div class="blank">
			<p class="blank__h">What are you looking for?</p>
			<p class="sub">Couples and hashtags, vendors and their trades, or anything on the marketplace.</p>
		</div>
	<?php elseif ($total === 0): ?>
		<div class="blank"><p class="blank__h">Nothing matched that.</p><p class="sub">Try a shorter word, or a city.</p></div>
	<?php else: ?>
		<?php if ($weddings): ?>
			<h3 class="srch__h">Weddings</h3>
			<div class="wgrid wgrid--page">
				<?php foreach ($weddings as $w): $pl = Wedding::palette($w); ?>
					<a class="wcard" href="<?= e(App::url('/w/' . $w['slug'])) ?>" style="--p:<?= e($pl['p']) ?>;--a:<?= e($pl['a']) ?>">
						<span class="wcard__art"><?= photo((string) $w['cover'], '') ?></span>
						<span class="wcard__body"><b><?= e(Wedding::names($w)) ?></b><small><?= e(Wedding::when($w)) ?></small></span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php if ($vendors): ?>
			<h3 class="srch__h">Vendors</h3>
			<div class="rows">
				<?php foreach ($vendors as $v): ?>
					<a class="row" href="<?= e(App::url('/vendors/' . $v['slug'])) ?>">
						<span class="row__ico"><?= photo((string) $v['logo'], '') ?: icon('store', 20) ?></span>
						<span class="row__t"><?= e($v['name']) ?> <span class="sub">&middot; <?= e(trim(($v['category'] ? $v['category'] . ', ' : '') . $v['city'], ', ')) ?></span></span>
						<span class="row__v"><?= e(number_format((float) $v['rating'], 1)) ?> &#9733;</span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php if ($listings): ?>
			<h3 class="srch__h">Marketplace</h3>
			<div class="rail">
				<?php foreach ($listings as $i => $l): ?>
					<a class="mcard" href="<?= e(App::url('/market/' . $l['slug'])) ?>">
						<span class="mcard__art blob blob--<?= e(['orange','green','blue','pink'][$i % 4]) ?>"><?= photo(first_photo((string) $l['photos']), '') ?></span>
						<span class="mcard__k"><?= e(Listing::kindName((string) $l['kind'])) ?> &middot; <?= e($l['city']) ?></span>
						<b class="mcard__t"><?= e($l['title']) ?></b>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</section>
