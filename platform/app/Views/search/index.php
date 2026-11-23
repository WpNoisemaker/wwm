<?php /** Search across couples, vendors and the marketplace. */ ?>
<div class="page wrap">
	<header class="page-head">
		<span class="eyebrow">Couples, vendors and the marketplace</span>
		<h1>Search</h1>
		<?php if ($q !== ''): ?><p class="lede"><?= e(number_format($total)) ?> result<?= $total === 1 ? '' : 's' ?> for “<?= e($q) ?>”</p><?php endif; ?>
	</header>
	<form class="searchbox narrow" method="get" action="<?= e(App::url('/search')) ?>" role="search">
		<?= icon('search') ?>
		<input class="input" type="search" name="q" value="<?= e($q) ?>"<?= $q === '' ? ' autofocus' : '' ?> placeholder="A couple, a vendor, a city, an apartment" aria-label="Search the site">
	</form>
	<?php if ($q === ''): ?>
		<div class="glass glass--plain empty gap-t"><span class="cbtn"><?= icon('search') ?></span><p>Search for couples and hashtags, vendors and their trades, or anything on the marketplace.</p></div>
	<?php elseif ($total === 0): ?>
		<div class="glass glass--plain empty gap-t"><span class="cbtn"><?= icon('search') ?></span><p>Nothing matched that. Try a shorter word, or a city.</p></div>
	<?php else: ?>
		<?php if ($weddings): ?>
			<section class="sec"><div class="block-head"><h2 class="h2">Weddings</h2></div>
				<div class="cards"><?php foreach ($weddings as $w): ?><?= wedding_card($w) ?><?php endforeach; ?></div></section>
		<?php endif; ?>
		<?php if ($vendors): ?>
			<section class="sec"><div class="block-head"><h2 class="h2">Vendors</h2></div>
				<div class="rows">
					<?php foreach ($vendors as $v): ?>
						<a class="row-item" href="<?= e(App::url('/vendors/' . $v['slug'])) ?>">
							<?= sq((string) $v['logo'], '', 'row-item__pic') ?>
							<span><span class="row-item__t"><?= e($v['name']) ?></span><span class="row-item__s"><?= e(trim($v['category'] . ' · ' . $v['city'], ' ·')) ?></span></span>
							<span class="row-item__end"><?= (float) $v['rating'] > 0 ? e(number_format((float) $v['rating'], 1)) . ' ★' : '' ?></span>
						</a>
					<?php endforeach; ?>
				</div></section>
		<?php endif; ?>
		<?php if ($listings): ?>
			<section class="sec"><div class="block-head"><h2 class="h2">Marketplace</h2></div>
				<div class="cards">
					<?php foreach ($listings as $l): ?>
						<a class="card" href="<?= e(App::url('/market/' . $l['slug'])) ?>"><?= sq(first_photo((string) $l['photos']), (string) $l['title']) ?><span class="card__cap"><span class="card__t"><?= e($l['title']) ?></span><span class="card__s"><?= e(Listing::kindName((string) $l['kind'])) ?> · <?= e($l['city']) ?></span></span></a>
					<?php endforeach; ?>
				</div></section>
		<?php endif; ?>
	<?php endif; ?>
</div>
