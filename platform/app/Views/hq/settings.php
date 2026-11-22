<?= View::partial('account/_head', ['h' => 'HQ settings', 'sub' => 'These drive the pricing page and the fee on every gift.']) ?>
<section class="sec wrap">
	<form method="post" class="form form--card">
		<?= Guard::field() ?>
		<h3 class="form__g">Plans</h3>
		<div class="form__pair">
			<span><label for="pro">Pro price</label><input id="pro" name="pro_price" type="number" min="0" value="<?= e($v['pro_price']) ?>"></span>
			<span><label for="prem">Premium price</label><input id="prem" name="premium_price" type="number" min="0" value="<?= e($v['premium_price']) ?>"></span>
		</div>
		<label for="fee">Service fee on cash gifts (%)</label>
		<input id="fee" name="fee_gift" type="number" min="0" max="20" step="0.1" value="<?= e($v['fee_gift']) ?>">
		<small class="gate__hint">Applied to Free and Pro pages. Premium and above pay none.</small>

		<h3 class="form__g">Contact</h3>
		<label for="email">Contact email</label>
		<input id="email" name="email" type="email" value="<?= e($v['email']) ?>">
		<label for="wa">WhatsApp number</label>
		<input id="wa" name="whatsapp" value="<?= e($v['whatsapp']) ?>" placeholder="2348000000000">
		<small class="gate__hint">Used for the High Society quote link. Setting a number here does <b>not</b> switch on WhatsApp notifications — those stay off until a provider is configured.</small>

		<h3 class="form__g">Email confirmation</h3>
		<label class="check"><input type="checkbox" name="email_verify" value="1" <?= $verify['on'] ? 'checked' : '' ?>> <span><b>Ask new sign-ups to confirm their email</b></span></label>
		<small class="gate__hint">
			Off until you decide. When it is on, anyone who signs up from that moment has to follow a link we email them before they can
			make their page public or ask for a payout. They can still sign in and build the page. Everyone who already has an account,
			including everyone imported from WordPress, is never asked.
			<?php if ($verify['on'] && $verify['since'] !== ''): ?><b>On since <?= e(date('j M Y, g:ia', (int) strtotime($verify['since']))) ?>.</b><?php endif; ?>
			Send yourself the test email below first.
		</small>

		<button class="btn btn--acid" type="submit">Save</button>
	</form>

	<form method="post" action="<?= e(App::url('/hq/test-mail')) ?>" class="form form--card">
		<?= Guard::field() ?>
		<h3 class="form__g">Check that email works first</h3>
		<p class="sub">Sends a test message to <?= e((string) Auth::user()['email']) ?>. If it does not arrive, leave confirmation off &mdash; people who sign up would be waiting for a link that never comes.</p>
		<button class="btn btn--ghost" type="submit">Send me a test email</button>
	</form>

	<div class="form form--card">
		<h3 class="form__g">Demo content</h3>
		<?php if ($demo['present']): ?>
			<p class="sub">Demo content is installed:
				<?= e(number_format($demo['counts']['weddings'] ?? 0)) ?> wedding pages,
				<?= e(number_format($demo['counts']['guests'] ?? 0)) ?> guests,
				<?= e(number_format($demo['counts']['vendors'] ?? 0)) ?> vendors,
				<?= e(number_format($demo['counts']['listings'] ?? 0)) ?> listings,
				<?= e(number_format($demo['counts']['talks'] ?? 0)) ?> community posts and
				<?= e(number_format($demo['counts']['users'] ?? 0)) ?> accounts.</p>
			<p class="locked"><?= icon('lock', 14) ?> <span>The demo accounts share a published password. <b>Remove the demo content before you launch.</b></span></p>
			<p class="sub">Every demo account uses the password <b><?= e($demo['password']) ?></b>.</p>
			<div class="rows">
				<?php foreach ($demo['logins'] as $l): ?>
					<div class="row"><span class="row__ico"><?= icon('user', 18) ?></span>
						<span class="row__t"><?= e($l['email']) ?> <span class="sub">&middot; <?= e($l['who']) ?></span></span></div>
				<?php endforeach; ?>
			</div>
			<?php if (Auth::is('owner')): ?>
				<form method="post" action="<?= e(App::url('/hq/demo/remove')) ?>"
				      onsubmit="return confirm('Delete all the demo content? This removes the demo wedding pages, vendors, listings, community posts and every demo account. Anything real is not touched.');">
					<?= Guard::field() ?>
					<button class="btn btn--ghost" type="submit">Remove all demo content</button>
				</form>
			<?php else: ?>
				<p class="sub">Only the owner can remove it.</p>
			<?php endif; ?>
		<?php else: ?>
			<p class="sub">There is no demo content on this site. If you want to look around a fully-populated one, you can add it: ten wedding pages across all four plans, vendors, marketplace listings, the community, and a few accounts to sign in with.</p>
			<?php if (Auth::is('owner')): ?>
				<form method="post" action="<?= e(App::url('/hq/demo/add')) ?>">
					<?= Guard::field() ?>
					<button class="btn btn--ghost" type="submit">Add demo content</button>
				</form>
			<?php else: ?>
				<p class="sub">Only the owner can add it.</p>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</section>
