<?php
/**
 * A listing: every photo in a swipeable gallery, the price, the seller in brief (with their full
 * profile a tap away), a message box to talk to them, the plain warning that we never hold money,
 * then more like it.
 */
$photos = Listing::photos($l);
$v = !empty($l['vendor_id']) ? Db::one('SELECT * FROM vendors WHERE id = ?', [(int) $l['vendor_id']]) : Market::seller((int) $l['partner_id']);
$me = (int) Auth::id();
$mine = $me && $me === (int) $l['partner_id'];
$here = '/market/' . $l['slug'];
?>
<div class="page wrap">
	<div class="listing">
		<div class="listing__media">
			<?php if (!empty($l['seller'])): ?>
				<a class="lcard__who listing__who" href="<?= e(App::url('/vendors/' . $l['seller_slug'])) ?>">
					<span class="lcard__av"><?php if (!empty($l['seller_logo'])): ?><?= photo((string) $l['seller_logo'], '') ?><?php else: ?><?= e(mb_strtoupper(mb_substr((string) $l['seller'], 0, 1))) ?><?php endif; ?></span>
					<span class="lcard__id"><b><?= e((string) $l['seller']) ?><?php if ((int) $l['seller_ok']): ?><?= icon('s-shield', 0, 'card__ok') ?><span class="sr"> (verified)</span><?php endif; ?></b><small><?= e(trim(Market::catName((string) $l['kind']) . ' · ' . ($l['seller_city'] ?: $l['city']), ' ·')) ?></small></span>
				</a>
			<?php endif; ?>
			<?php if ($photos): ?>
				<div class="lgal" data-lgal>
					<div class="lgal__track" data-lgal-track tabindex="0" aria-label="Photos of <?= e($l['title']) ?>. Swipe for more.">
						<?php foreach ($photos as $i => $p): ?><div class="lgal__slide"><?= sq((string) $p, $i === 0 ? (string) $l['title'] : '', 'sq--wide lgal__pic', $i === 0) ?></div><?php endforeach; ?>
					</div>
					<?php if (count($photos) > 1): ?>
						<span class="lgal__n" data-lgal-n>1 / <?= count($photos) ?></span>
						<div class="lgal__thumbs" role="tablist" aria-label="Choose a photo">
							<?php foreach ($photos as $i => $p): ?><button type="button" class="lgal__thumb<?= $i === 0 ? ' is-on' : '' ?>" data-lgal-go="<?= $i ?>" aria-label="Photo <?= $i + 1 ?>"><?= photo((string) $p, '') ?></button><?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php else: ?>
				<?= sq('', (string) $l['title'], 'sq--wide') ?>
			<?php endif; ?>
		</div>

		<div class="listing__body">
			<span class="eyebrow"><?= e(Market::catName((string) $l['kind'])) ?><?= (string) $l['city'] !== '' ? ' · ' . e($l['city']) : '' ?></span>
			<h1 class="profile__h"><?= e($l['title']) ?></h1>
			<div class="glass glass--tight listing-price">
				<span class="k">Price</span>
				<span class="total__v"><?= (int) $l['on_ask'] ? 'On request' : money(number_format((int) $l['price'])) ?></span>
				<?php if ((string) $l['price_unit'] !== '' && !(int) $l['on_ask']): ?><span class="sub">per <?= e(preg_replace('/^per\s+/i', '', (string) $l['price_unit'])) ?></span><?php endif; ?>
			</div>

			<?php if ($v): ?>
				<a class="seller-card glass glass--plain" href="<?= e(App::url('/vendors/' . $v['slug'])) ?>">
					<?= sq((string) $v['logo'], (string) $v['name'], 'seller-card__pic', false, '<span class="fill-art" aria-hidden="true">' . e(mb_strtoupper(mb_substr((string) $v['name'], 0, 1))) . '</span>') ?>
					<span class="seller-card__in">
						<span class="seller-card__k">Sold by</span>
						<span class="seller-card__n"><?= e((string) $v['name']) ?><?php if ((int) $v['verified']): ?> <span class="badge"><?= icon('check') ?>Verified</span><?php endif; ?></span>
						<span class="seller-card__s"><?= e(trim((string) $v['category'] . ((string) $v['city'] !== '' ? ' · ' . $v['city'] : ''), ' ·')) ?><?= (float) $v['rating'] > 0 ? ' · ★ ' . e(number_format((float) $v['rating'], 1)) . ' (' . (int) $v['reviews'] . ')' : '' ?></span>
						<?php if (trim((string) $v['about']) !== ''): ?><span class="seller-card__x"><?= e(mb_strimwidth(trim((string) $v['about']), 0, 150, '…')) ?></span><?php endif; ?>
						<span class="seller-card__go">Profile, best work &amp; reviews<?= icon('next') ?></span>
					</span>
				</a>
			<?php endif; ?>

			<section class="chat-start glass glass--plain glass--tight" id="chat">
				<?php if ($mine): ?>
					<h2 class="h3">This is your listing</h2>
					<p class="help">Customers message you from here. Their messages arrive in <a class="linkish" href="<?= e(App::url('/account/messages')) ?>">Messages</a>.</p>
				<?php elseif (!$me): ?>
					<h2 class="h3">Talk to <?= e((string) ($v['name'] ?? 'the seller')) ?></h2>
					<p class="help">Ask about dates and what you need. They send you an invoice or a payment link in the chat. You need a free customer account to message sellers.</p>
					<div class="form-actions">
						<a class="pill" href="<?= e(App::url('/join/customer?to=' . urlencode($here . '#chat'))) ?>"><?= icon('chat') ?>Create a customer account</a>
						<a class="pill pill--glass" href="<?= e(App::url('/sign-in?to=' . urlencode($here . '#chat'))) ?>">Sign in</a>
					</div>
				<?php else: ?>
					<h2 class="h3">Message <?= e((string) ($v['name'] ?? 'the seller')) ?></h2>
					<form method="post" action="<?= e(App::url($here . '/message')) ?>" class="form">
						<?= Guard::field() ?>
						<label class="sr" for="chat-body">Your message</label>
						<textarea class="input" id="chat-body" name="body" rows="3" maxlength="4000" required placeholder="Hello! Is this free on 14 December? There are 8 of us."></textarea>
						<button class="pill pill--block" type="submit"><?= icon('chat') ?>Send</button>
					</form>
					<p class="help">Their replies arrive in your Messages, and we email you when they reply. They can send you an invoice or a payment link there.</p>
				<?php endif; ?>
			</section>

			<?= View::partial('partials/pay-warning') ?>

			<?php if (trim((string) $l['about']) !== ''): ?><div class="profile__about listing__about"><?= nl2br(e((string) $l['about'])) ?></div><?php endif; ?>

			<div class="listing__foot">
				<button class="pill pill--sm pill--glass" type="button" data-share><?= icon('share') ?>Share</button>
				<?php if ($v && !$mine): ?><?= View::partial('partials/report', ['v' => $v, 'back' => $here, 'listing' => (int) $l['id']]) ?><?php endif; ?>
			</div>
		</div>
	</div>

	<?php $others = array_values(array_filter($more, static fn ($m) => $m['id'] !== $l['id'])); if ($others): ?>
	<section class="sec">
		<div class="sec-head"><span class="eyebrow">Also in <?= e(Market::catName((string) $l['kind'])) ?></span><h2>More like this</h2></div>
		<div class="rail">
			<?php foreach (array_slice($others, 0, 4) as $m): ?>
				<?= View::partial('market/_card', ['l' => $m]) ?>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>
</div>
