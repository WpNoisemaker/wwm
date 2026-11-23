<?php
/** A vendor: squircle photo, the facts as tiles, how to reach them, then what couples said. */
$stars = static fn (int $n): string => str_repeat('★', max(0, min(5, $n))) . '<span class="stars__off">' . str_repeat('★', 5 - max(0, min(5, $n))) . '</span>';
?>
<div class="page wrap">
	<div class="profile">
		<?= sq((string) $v['logo'], (string) $v['name'], 'profile__pic', true) ?>
		<div class="profile__body">
			<span class="eyebrow"><?= e($v['category'] ?: 'Vendor') ?><?= $v['city'] ? ' · ' . e($v['city']) : '' ?></span>
			<h1 class="profile__h"><?= e($v['name']) ?> <?php if ((int) $v['verified']): ?><span class="badge badge--solid"><?= icon('check') ?>Verified</span><?php endif; ?></h1>
			<div class="mosaic profile__facts">
				<div class="tile tile--w2"><span class="k">Rating</span><span class="v"><?= (float) $v['rating'] > 0 ? e(number_format((float) $v['rating'], 1)) . ' <small>of 5</small>' : 'New' ?></span></div>
				<div class="tile tile--w2"><span class="k">Reviews</span><span class="v"><?= (int) $v['reviews'] ?></span></div>
			</div>
			<?php if (trim((string) $v['about']) !== ''): ?><p class="profile__about"><?= nl2br(e((string) $v['about'])) ?></p><?php endif; ?>
			<div class="form-actions">
				<?php if ((string) $v['phone'] !== ''): ?><a class="pill" href="tel:<?= e(preg_replace('/[^0-9+]/', '', (string) $v['phone'])) ?>"><?= icon('phone') ?>Call <?= e($v['phone']) ?></a><?php endif; ?>
				<?php if ($v['instagram']): ?><a class="pill pill--glass" href="https://instagram.com/<?= e(ltrim((string) $v['instagram'], '@')) ?>" rel="noopener nofollow" target="_blank">Instagram</a><?php endif; ?>
				<?php if ($v['website']): ?><a class="pill pill--glass" href="<?= e($v['website']) ?>" rel="noopener nofollow" target="_blank">Website</a><?php endif; ?>
				<button class="cbtn" type="button" data-share aria-label="Share"><?= icon('share') ?></button>
			</div>
		</div>
	</div>

	<?php if (!empty($sells)): ?>
	<section class="sec">
		<div class="sec-head"><span class="eyebrow">Book on the marketplace</span><h2>What <?= e($v['name']) ?> offers</h2></div>
		<div class="rail">
			<?php foreach ($sells as $m): ?>
				<a class="card" href="<?= e(App::url('/market/' . $m['slug'])) ?>">
					<?= sq(first_photo((string) $m['photos']), (string) $m['title']) ?>
					<span class="card__cap"><span class="card__t"><?= e($m['title']) ?></span><span class="card__s"><?= (int) $m['on_ask'] ? 'Price on request' : money(short_money((int) $m['price'])) . ((string) $m['price_unit'] !== '' ? ' ' . e($m['price_unit']) : '') ?><?= (string) $m['city'] !== '' ? ' · ' . e($m['city']) : '' ?></span></span>
				</a>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<section class="sec">
		<div class="sec-head"><span class="eyebrow"><?= count($reviews) ?> review<?= count($reviews) === 1 ? '' : 's' ?></span><h2>What couples said</h2></div>
		<?php if (!$reviews): ?>
			<div class="glass glass--plain empty"><span class="cbtn"><?= icon('star') ?></span><p>No reviews yet. Couples who book them can leave one after the wedding.</p></div>
		<?php else: ?>
			<div class="reviews">
				<?php foreach ($reviews as $r): ?>
					<figure class="review">
						<p class="stars" aria-label="<?= (int) $r['stars'] ?> out of 5"><?= $stars((int) $r['stars']) ?></p>
						<blockquote><?= e((string) $r['body']) ?></blockquote>
						<figcaption><?= e((string) ($r['name'] ?: 'A couple')) ?> · <?= e(ago((string) $r['created_at'])) ?></figcaption>
						<?php if (trim((string) $r['reply']) !== ''): ?><p class="review__reply"><b><?= e($v['name']) ?> replied</b><?= e((string) $r['reply']) ?></p><?php endif; ?>
					</figure>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>
</div>
