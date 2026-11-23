<?php
/**
 * A seller's public profile: who they are, their three best projects, what they sell, a message
 * box, reviews from customers who talked to them here (and a form to add one), and a way to report
 * them. A seller paused while HQ checks them shows only that.
 *
 * @var array $v @var array $reviews @var array $sells @var array $projects @var int $me @var bool $canReview @var bool $mine @var ?array $myReview
 */
$stars = static fn (int $n): string => str_repeat('★', max(0, min(5, $n))) . '<span class="stars__off">' . str_repeat('★', 5 - max(0, min(5, $n))) . '</span>';
$paused = (string) ($v['status'] ?? 'active') !== 'active';
$here = '/vendors/' . $v['slug'];
?>
<div class="page wrap">
	<div class="profile">
		<?= sq((string) $v['logo'], (string) $v['name'], 'profile__pic', true, '<span class="fill-art" aria-hidden="true">' . e(mb_strtoupper(mb_substr((string) $v['name'], 0, 1))) . '</span>') ?>
		<div class="profile__body">
			<span class="eyebrow"><?= e($v['category'] ?: 'Vendor') ?><?= $v['city'] ? ' · ' . e($v['city']) : '' ?></span>
			<h1 class="profile__h"><?= e($v['name']) ?> <?php if ((int) $v['verified'] && !$paused): ?><span class="badge badge--solid"><?= icon('check') ?>Verified</span><?php endif; ?></h1>
			<?php if ($paused): ?>
				<p class="note"><?= icon('lock') ?><span>This seller is paused while we check their business. Their listings will return if everything checks out.</span></p>
			<?php else: ?>
				<div class="mosaic profile__facts">
					<div class="tile tile--w2"><span class="k">Rating</span><span class="v"><?= (float) $v['rating'] > 0 ? e(number_format((float) $v['rating'], 1)) . ' <small>of 5</small>' : 'New' ?></span></div>
					<div class="tile tile--w2"><span class="k">Reviews</span><span class="v"><?= (int) $v['reviews'] ?></span></div>
				</div>
				<?php if (trim((string) $v['about']) !== ''): ?><p class="profile__about"><?= nl2br(e((string) $v['about'])) ?></p><?php endif; ?>
				<div class="form-actions">
					<?php if ($v['user_id'] && !$mine): ?><a class="pill" href="#chat"><?= icon('chat') ?>Message</a><?php endif; ?>
					<?php if ($v['instagram']): ?><a class="pill pill--glass" href="https://instagram.com/<?= e(ltrim((string) $v['instagram'], '@')) ?>" rel="noopener nofollow" target="_blank">Instagram</a><?php endif; ?>
					<?php if ($v['website']): ?><a class="pill pill--glass" href="<?= e($v['website']) ?>" rel="noopener nofollow" target="_blank">Website</a><?php endif; ?>
					<button class="cbtn" type="button" data-share aria-label="Share"><?= icon('share') ?></button>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<?php if (!$paused): ?>
	<?php if ($projects): ?>
	<section class="sec" id="work">
		<div class="sec-head"><span class="eyebrow">Best work</span><h2>Projects by <?= e($v['name']) ?></h2></div>
		<div class="projects">
			<?php foreach ($projects as [$pt, $pa, $pp]): ?>
				<figure class="project">
					<?= sq($pp, $pt, 'sq--wide') ?>
					<figcaption><?php if ($pt !== ''): ?><span class="project__t"><?= e($pt) ?></span><?php endif; ?><?php if ($pa !== ''): ?><span class="project__s"><?= e($pa) ?></span><?php endif; ?></figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php if (!empty($sells)): ?>
	<section class="sec">
		<div class="sec-head"><span class="eyebrow">On the marketplace</span><h2>What <?= e($v['name']) ?> offers</h2></div>
		<div class="rail">
			<?php foreach ($sells as $m): ?>
				<?= View::partial('market/_card', ['l' => $m, 'who' => false]) ?>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php if ($v['user_id'] && !$mine): ?>
	<section class="sec" id="chat">
		<div class="chat-start glass glass--plain glass--tight narrow">
			<h2 class="h3">Message <?= e($v['name']) ?></h2>
			<?php if (!$me): ?>
				<p class="help">Ask about your date and what you need. You need a free customer account to message sellers.</p>
				<div class="form-actions">
					<a class="pill" href="<?= e(App::url('/join/customer?to=' . urlencode($here . '#chat'))) ?>"><?= icon('chat') ?>Create a customer account</a>
					<a class="pill pill--glass" href="<?= e(App::url('/sign-in?to=' . urlencode($here . '#chat'))) ?>">Sign in</a>
				</div>
			<?php else: ?>
				<form method="post" action="<?= e(App::url($here . '/message')) ?>" class="form">
					<?= Guard::field() ?>
					<label class="sr" for="v-chat">Your message</label>
					<textarea class="input" id="v-chat" name="body" rows="3" maxlength="4000" required placeholder="Hello! We are getting married on 14 December and would love your help."></textarea>
					<button class="pill pill--block" type="submit"><?= icon('chat') ?>Send</button>
				</form>
			<?php endif; ?>
			<?= View::partial('partials/pay-warning', ['short' => true]) ?>
		</div>
	</section>
	<?php endif; ?>

	<section class="sec" id="reviews">
		<div class="sec-head"><span class="eyebrow"><?= count($reviews) ?> review<?= count($reviews) === 1 ? '' : 's' ?></span><h2>What customers said</h2></div>
		<?php if ($canReview): ?>
			<form method="post" action="<?= e(App::url($here . '/review')) ?>" class="form glass glass--plain glass--tight review-form narrow">
				<?= Guard::field() ?>
				<h3 class="h3"><?= $myReview ? 'Change your review' : 'Leave a review' ?></h3>
				<fieldset class="star-pick" aria-label="Your rating">
					<?php for ($i = 5; $i >= 1; $i--): ?><input type="radio" id="st<?= $i ?>" name="stars" value="<?= $i ?>"<?= (int) ($myReview['stars'] ?? 5) === $i ? ' checked' : '' ?>><label for="st<?= $i ?>" title="<?= $i ?> star<?= $i > 1 ? 's' : '' ?>">★</label><?php endfor; ?>
				</fieldset>
				<label class="sr" for="rv-body">Your review</label>
				<textarea class="input" id="rv-body" name="body" rows="3" maxlength="2000" required placeholder="How did it go? What should other couples know?"><?= e((string) ($myReview['body'] ?? '')) ?></textarea>
				<button class="pill" type="submit"><?= $myReview ? 'Update my review' : 'Post my review' ?></button>
			</form>
		<?php elseif (!$mine): ?>
			<p class="help center">Reviews come from customers who have messaged <?= e($v['name']) ?> here.</p>
		<?php endif; ?>
		<?php if (!$reviews): ?>
			<div class="glass glass--plain empty"><span class="cbtn"><?= icon('star') ?></span><p>No reviews yet.</p></div>
		<?php else: ?>
			<div class="reviews">
				<?php foreach ($reviews as $r): ?>
					<figure class="review">
						<p class="stars" aria-label="<?= (int) $r['stars'] ?> out of 5"><?= $stars((int) $r['stars']) ?></p>
						<blockquote><?= e((string) $r['body']) ?></blockquote>
						<figcaption><?= e((string) ($r['name'] ?: 'A customer')) ?><?php if (!empty($r['guest']) && !empty($r['wslug'])): ?>, guest at <a href="<?= e(App::url('/w/' . $r['wslug'])) ?>"><?= e(Wedding::names($r)) ?></a>&rsquo;s wedding<?php endif; ?> · <?= e(ago((string) $r['created_at'])) ?></figcaption>
						<?php if (trim((string) $r['reply']) !== ''): ?><p class="review__reply"><b><?= e($v['name']) ?> replied</b><?= e((string) $r['reply']) ?></p><?php endif; ?>
					</figure>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>
	<?php if (!$mine): ?><div class="center gap-t"><?= View::partial('partials/report', ['v' => $v, 'back' => $here]) ?></div><?php endif; ?>
	<?php endif; ?>
</div>
