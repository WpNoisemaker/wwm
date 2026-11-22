<?php
/** A listing: the photo, the price on a glass card, how booking works, then more like it. */
$photos = json_decode((string) $l['photos'], true);
$photos = is_array($photos) ? array_values($photos) : [];
?>
<div class="page wrap">
	<div class="profile">
		<?= sq($photos[0] ?? '', (string) $l['title'], 'profile__pic', true) ?>
		<div class="profile__body">
			<span class="eyebrow"><?= e(Listing::kindName((string) $l['kind'])) ?> · <?= e($l['city']) ?></span>
			<h1 class="profile__h"><?= e($l['title']) ?></h1>
			<div class="glass glass--tight listing-price">
				<span class="k">Price</span>
				<span class="total__v"><?= (int) $l['on_ask'] ? 'On request' : money(number_format((int) $l['price'])) ?></span>
				<?php if ((string) $l['price_unit'] !== ''): ?><span class="sub"><?= e($l['price_unit']) ?></span><?php endif; ?>
			</div>
			<?php if (trim((string) $l['about']) !== ''): ?><p class="profile__about"><?= nl2br(e((string) $l['about'])) ?></p><?php endif; ?>
			<div class="tile listing-safe"><?= icon('s-shield') ?><span><b>Booking is protected.</b> Agree the dates with the seller, then pay on the site. Your money is held until you confirm the booking happened.</span></div>
			<div class="form-actions">
				<a class="pill" href="<?= e(App::url(Auth::check() ? '/account' : '/sign-in?to=' . urlencode('/market/' . $l['slug']))) ?>"><?= Auth::check() ? 'Ask the seller' : 'Sign in to book' ?></a>
				<button class="cbtn" type="button" data-share aria-label="Share"><?= icon('share') ?></button>
			</div>
		</div>
	</div>
	<?php if (count($photos) > 1): ?>
		<div class="gallery gap-t"><?php foreach (array_slice($photos, 1, 4) as $p): ?><?= sq((string) $p, '') ?><?php endforeach; ?></div>
	<?php endif; ?>

	<?php $others = array_values(array_filter($more, static fn ($m) => $m['id'] !== $l['id'])); if ($others): ?>
	<section class="sec">
		<div class="sec-head"><span class="eyebrow">Also in <?= e(Listing::kindName((string) $l['kind'])) ?></span><h2>More like this</h2></div>
		<div class="rail">
			<?php foreach (array_slice($others, 0, 4) as $m): ?>
				<a class="card" href="<?= e(App::url('/market/' . $m['slug'])) ?>">
					<?= sq(first_photo((string) $m['photos']), (string) $m['title']) ?>
					<span class="card__cap"><span class="card__t"><?= e($m['title']) ?></span><span class="card__s"><?= (int) $m['on_ask'] ? 'Price on request' : money(short_money((int) $m['price'])) ?> · <?= e($m['city']) ?></span></span>
				</a>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>
</div>
