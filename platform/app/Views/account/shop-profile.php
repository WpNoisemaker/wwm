<?php
/** A seller's business profile: what couples see on the public page, their best work, and replies to reviews. @var ?array $v @var array $reviews */
$projects = $v ? Market::projects($v) : [];
?>
<?= View::partial('account/_head', ['h' => 'Business profile', 'sub' => $v ? (string) $v['name'] : 'Your seller shop', 'now' => 'shop-profile']) ?>
	<div class="split-2">
		<form method="post" action="<?= e(App::url('/account/shop')) ?>" class="form glass glass--plain glass--tight acct-card" enctype="multipart/form-data">
			<?= Guard::field() ?><input type="hidden" name="do" value="profile">
			<div class="row row--between"><h2 class="h3">What couples see</h2><?php if ($v && (int) $v['verified']): ?><span class="badge badge--solid"><?= icon('check') ?>Verified</span><?php endif; ?></div>
			<div class="form-grid form-grid--2">
				<label class="field span-2"><span>Business name</span><input class="input" name="name" value="<?= e((string) ($v['name'] ?? '')) ?>" required></label>
				<label class="field span-2"><span>What you do</span><input class="input" name="category" value="<?= e((string) ($v['category'] ?? '')) ?>" placeholder="Car hire, stays, aso-ebi, home organising"></label>
				<label class="field"><span>City</span><input class="input" name="city" value="<?= e((string) ($v['city'] ?? '')) ?>"></label>
				<label class="field"><span>State</span><input class="input" name="state" value="<?= e((string) ($v['state'] ?? '')) ?>"></label>
				<label class="field"><span>Phone</span><input class="input" name="phone" type="tel" value="<?= e((string) ($v['phone'] ?? '')) ?>"></label>
				<label class="field"><span>Instagram</span><input class="input" name="instagram" value="<?= e((string) ($v['instagram'] ?? '')) ?>" placeholder="yourhandle"></label>
				<label class="field span-2"><span>Website</span><input class="input" name="website" value="<?= e((string) ($v['website'] ?? '')) ?>"></label>
				<label class="field span-2"><span>About your business</span><textarea class="input" name="about" rows="6"><?= e((string) ($v['about'] ?? '')) ?></textarea></label>
				<label class="photo-pick span-2"><input type="file" name="logo" accept="image/jpeg,image/png,image/webp" data-photo-input><?= icon('camera') ?><span data-photo-name><?= $v && (string) $v['logo'] !== '' ? 'Change your logo or photo' : 'Add a logo or photo' ?></span></label>
			</div>
			<h2 class="h3 gap-t-sm">Your three best projects</h2>
			<p class="help">They sit at the top of your public profile: a photo, a title and a line about each.</p>
			<?php for ($i = 0; $i < 3; $i++): $pj = $projects[$i] ?? ['', '', '']; ?>
				<div class="proj-edit">
					<?php if ($pj[2] !== ''): ?><div class="proj-edit__pic"><?= sq($pj[2], '', '') ?><label class="squad-edit__off"><input type="checkbox" name="proj_off[<?= $i ?>]" value="1"> Remove</label></div><?php endif; ?>
					<div class="form-grid form-grid--2">
						<label class="field"><span>Project <?= $i + 1 ?></span><input class="input" name="proj_title[<?= $i ?>]" maxlength="120" value="<?= e($pj[0]) ?>" placeholder="Aso-ebi for 200 guests"></label>
						<label class="field"><span>A line about it</span><input class="input" name="proj_about[<?= $i ?>]" maxlength="300" value="<?= e($pj[1]) ?>"></label>
						<label class="photo-pick span-2"><input type="file" name="proj_photo[<?= $i ?>]" accept="image/jpeg,image/png,image/webp" data-photo-input><?= icon('camera') ?><span data-photo-name><?= $pj[2] !== '' ? 'Change the photo' : 'Add a photo' ?></span></label>
					</div>
				</div>
			<?php endfor; ?>
			<button class="pill pill--block" type="submit">Save profile</button>
		</form>
		<aside class="form glass glass--plain glass--tight">
			<h2 class="h3">Your public page</h2>
			<?php if ($v): ?>
				<?= sq((string) $v['logo'], (string) $v['name'], 'sq--wide') ?>
				<p class="help">Couples find you in Vendors and on every listing you post.</p>
				<a class="pill pill--glass pill--block" href="<?= e(App::url('/vendors/' . $v['slug'])) ?>">Open it</a>
			<?php else: ?>
				<p class="help">Save your profile and it appears in Vendors, with your listings.</p>
			<?php endif; ?>
			<p class="help"><?= $v && (int) $v['verified'] ? 'Verified by Wedding Websites &amp; More.' : 'HQ verifies sellers once they have checked the business.' ?></p>
			<?php if ($v): $prem = Market::premium($v); $lk = Market::look($v); if (!$prem) { $lk = array_merge($lk, ['primary' => '#0F5C4D', 'accent' => '#E8B04B', 'font' => 'playfair', 'on' => '#FFFFFF']); }
				$gf = array_values(array_filter(array_column(Market::FONTS, 2))); $logo = $lk['logo'] !== '' ? $lk['logo'] : (string) $v['logo']; $price = (int) Site::setting('seller_premium_price', '0'); $wa = preg_replace('/\D/', '', Site::setting('whatsapp', '')); ?>
			<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=<?= e(implode('&family=', $gf)) ?>&display=swap">
			<div class="form glass glass--plain glass--tight inv-look" id="look">
				<div class="lock-row"><h2 class="h3">Your invoice look</h2><?php if ($prem): ?><span class="promo__tag"><?= icon('s-crown') ?>Premium</span><?php endif; ?></div>
				<div class="inv-mini" data-inv-mini style="--ip: <?= e($lk['primary']) ?>; --ia: <?= e($lk['accent']) ?>; --io: <?= e($lk['on']) ?>; --if: <?= e(Market::FONTS[$lk['font']][1]) ?>">
					<div class="inv-mini__band"><?= $logo !== '' ? sq($logo, '', 'inv-mini__logo') : '' ?><b><?= e((string) $v['name']) ?></b><small>Invoice #A1B2C3</small></div>
					<div class="inv-mini__body"><p><span>Your service</span><b>₦150,000</b></p><p class="inv-mini__total"><span>Total</span><b>₦150,000</b></p><span class="inv-mini__btn">Pay now</span></div>
				</div>
				<?php if ($prem): ?>
					<form method="post" action="<?= e(App::url('/account/shop')) ?>" enctype="multipart/form-data" class="form">
						<?= Guard::field() ?><input type="hidden" name="do" value="look">
						<p class="help">Every invoice, payment link and receipt you send in the chat carries this look.</p>
						<div class="form-grid form-grid--2">
							<label class="field"><span>Main colour</span><input type="color" name="primary" value="<?= e($lk['primary']) ?>" data-inv="primary"></label>
							<label class="field"><span>Second colour</span><input type="color" name="accent" value="<?= e($lk['accent']) ?>" data-inv="accent"></label>
							<label class="field span-2"><span>Font</span><select class="input" name="font" data-inv="font"><?php foreach (Market::FONTS as $fk => [$fn, $fc]): ?><option value="<?= e($fk) ?>" data-css="<?= e($fc) ?>"<?= $lk['font'] === $fk ? ' selected' : '' ?>><?= e($fn) ?></option><?php endforeach; ?></select></label>
							<label class="photo-pick span-2"><input type="file" name="inv_logo" accept="image/jpeg,image/png,image/webp" data-photo-input><?= icon('camera') ?><span data-photo-name>Logo for invoices (empty uses your profile logo)</span></label>
							<?php if ($lk['logo'] !== ''): ?><label class="check span-2"><span>Use my profile logo instead</span><input type="checkbox" name="logo_off" value="1"></label><?php endif; ?>
						</div>
						<div class="form-actions"><button class="pill pill--sm" type="submit">Save invoice look</button><button class="pill pill--sm pill--glass" type="submit" name="reset" value="1">Back to our standard look</button></div>
					</form>
				<?php else: ?>
					<h3 class="h3">Send invoices in your own colours</h3>
					<p class="help">Premium sellers choose their colours and font and add their logo. Every invoice, payment link and receipt they send then looks like it came from their own shop.</p>
					<?php if ($price): ?><p><b><?= money(number_format($price)) ?></b> a month</p><?php endif; ?>
					<?php if ($wa !== ''): ?><a class="pill pill--block" href="https://wa.me/<?= e($wa) ?>?text=<?= rawurlencode('Hello, I sell on your marketplace as ' . $v['name'] . '. I would like Premium for my invoices.') ?>" target="_blank" rel="noopener"><?= icon('chat') ?>Ask us about Premium</a>
					<?php else: ?><a class="pill pill--block" href="mailto:<?= e(Site::setting('email', '')) ?>?subject=<?= rawurlencode('Premium for ' . $v['name']) ?>"><?= icon('mail') ?>Ask us about Premium</a><?php endif; ?>
				<?php endif; ?>
			</div>
			<?php endif; ?>
			<?php if (!empty($reviews)): ?>
				<h2 class="h3 gap-t-sm" id="reviews">Reviews</h2>
				<?php foreach ($reviews as $r): ?>
					<div class="tile review-mini">
						<p><b><?= str_repeat('★', (int) $r['stars']) ?></b> <?= e((string) $r['name']) ?></p>
						<p class="help"><?= e((string) $r['body']) ?></p>
						<?php if (trim((string) $r['reply']) !== ''): ?>
							<p class="help"><b>You replied:</b> <?= e((string) $r['reply']) ?></p>
						<?php else: ?>
							<form method="post" action="<?= e(App::url('/account/shop')) ?>" class="form">
								<?= Guard::field() ?><input type="hidden" name="do" value="reply"><input type="hidden" name="review_id" value="<?= (int) $r['id'] ?>">
								<label class="sr" for="rp<?= (int) $r['id'] ?>">Your reply</label>
								<textarea class="input" id="rp<?= (int) $r['id'] ?>" name="reply" rows="2" maxlength="1000" required placeholder="Reply once, publicly"></textarea>
								<button class="pill pill--sm" type="submit">Reply</button>
							</form>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</aside>
	</div>
</div>
