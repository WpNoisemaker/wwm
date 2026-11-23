<?php
/** Sell with us: how it works, what can be sold, and a seller account in one form. @var array $cats @var int $me */
?>
<div class="page wrap">
	<header class="page-head">
		<span class="eyebrow">For businesses</span>
		<h1>Sell to couples planning their wedding</h1>
		<p class="lede">Couples come here to plan the day and everything around it. List what you offer, answer their messages, and send your own invoice or payment link.</p>
	</header>
	<div class="sell-steps">
		<div class="glass glass--plain sell-step"><span class="sell-step__n">1</span><h2 class="h3">Open your shop</h2><p>Add your business name, what you do, a logo and your three best projects.</p></div>
		<div class="glass glass--plain sell-step"><span class="sell-step__n">2</span><h2 class="h3">List what you sell</h2><p>Add as many photos as you like and a price, or &ldquo;on request&rdquo;, in any open category.</p></div>
		<div class="glass glass--plain sell-step"><span class="sell-step__n">3</span><h2 class="h3">Talk and get paid</h2><p>Customers message you. Send an invoice or a payment link in the chat; they pay you directly.</p></div>
	</div>
	<p class="help center gap-t-sm"><?= icon('s-crown') ?> <b>Premium sellers</b> send every invoice, payment link and receipt in their own colours, font and logo<?= ($sp = (int) Site::setting('seller_premium_price', '0')) ? ', for ' . money(number_format($sp)) . ' a month' : '' ?>.</p>
	<section class="sec">
		<div class="sec-head"><span class="eyebrow">Open now</span><h2>What you can sell here</h2><p>We keep the marketplace to what couples need around a wedding. New categories open from time to time.</p></div>
		<div class="cat-list">
			<?php foreach ($cats as $c): ?><div class="cat"><b><?= e((string) $c['name']) ?></b><span><?= e((string) $c['about']) ?></span></div><?php endforeach; ?>
		</div>
	</section>
	<section class="sec" id="join">
		<form method="post" action="<?= e(App::url('/sell')) ?>" class="form glass glass--tight narrow sell-form">
			<?= flashes() ?>
			<?= Guard::field() ?>
			<h2 class="h3"><?= $me ? 'Add a seller shop to your account' : 'Create your seller account' ?></h2>
			<div class="form-grid form-grid--2">
				<label class="field span-2"><span>Business name</span><input class="input" name="business" required maxlength="190" value="<?= e(Guard::old('business')) ?>"></label>
				<label class="field"><span>What you sell</span><select class="input" name="cat" required><option value="">Choose one</option><?php foreach ($cats as $c): ?><option value="<?= e((string) $c['slug']) ?>"<?= Guard::old('cat') === $c['slug'] ? ' selected' : '' ?>><?= e((string) $c['name']) ?></option><?php endforeach; ?></select></label>
				<label class="field"><span>City</span><input class="input" name="city" maxlength="120" value="<?= e(Guard::old('city')) ?>"></label>
				<?php if (!$me): ?>
					<label class="field span-2"><span>Your name</span><input class="input" name="name" required autocomplete="name" value="<?= e(Guard::old('name')) ?>"></label>
					<label class="field"><span>Email</span><input class="input" name="email" type="email" required autocomplete="email" value="<?= e(Guard::old('email')) ?>"></label>
					<label class="field"><span>Phone</span><input class="input" name="phone" autocomplete="tel" inputmode="tel" value="<?= e(Guard::old('phone')) ?>"></label>
					<label class="field span-2"><span>Password, at least 8 characters</span><input class="input" name="pass" type="password" required minlength="8" autocomplete="new-password"></label>
				<?php endif; ?>
			</div>
			<button class="pill pill--block" type="submit"><?= $me ? 'Open my shop' : 'Create my seller account' ?></button>
			<p class="after-pill">Sell only in the categories above. By opening a shop you agree to our <a class="linkish" href="<?= e(App::url('/terms')) ?>">terms</a>: describe what you sell honestly and honour your prices. Verified badges come after we check your business.</p>
			<?php if (!$me): ?><p class="after-pill">Already have an account? <a class="linkish" href="<?= e(App::url('/sign-in?to=' . urlencode('/sell'))) ?>">Sign in</a> first.</p><?php endif; ?>
		</form>
	</section>
</div>
