<?= View::partial('account/_head', ['h' => 'Settings', 'w' => $w]) ?>
<section class="sec wrap">
	<form method="post" class="form form--card">
		<?= Guard::field() ?>
		<h3 class="form__g">You</h3>
		<label for="name">Name</label>
		<input id="name" name="name" value="<?= e($u['name']) ?>">
		<label for="email">Email</label>
		<input id="email" value="<?= e($u['email']) ?>" disabled>
		<small class="gate__hint">Ask us if you need to change the email on the account.</small>
		<label for="phone">Phone</label>
		<input id="phone" name="phone" value="<?= e($u['phone']) ?>">

		<h3 class="form__g">Password</h3>
		<label for="current">Current password</label>
		<input id="current" name="current" type="password" autocomplete="current-password">
		<label for="pass">New password</label>
		<input id="pass" name="pass" type="password" minlength="8" autocomplete="new-password">
		<small class="gate__hint">Leave both blank to keep the one you have. Changing it signs out every other device.</small>

		<button class="btn btn--acid" type="submit">Save</button>
	</form>

	<div class="form form--card">
		<h3 class="form__g">How we reach you</h3>
		<label class="check"><input type="checkbox" checked disabled> <span><b>Email</b> — sign-in, resets, receipts and RSVP alerts. Always on.</span></label>
		<label class="check is-off"><input type="checkbox" disabled> <span><b>WhatsApp</b> — not available yet</span></label>
		<label class="check is-off"><input type="checkbox" disabled> <span><b>SMS</b> — not available yet</span></label>
		<p class="sub">WhatsApp and SMS stay switched off until an administrator sets up a provider. Until then nothing is sent to a phone by anybody, including us.</p>
	</div>
</section>
