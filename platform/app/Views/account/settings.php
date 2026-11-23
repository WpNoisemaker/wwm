<?= View::partial('account/_head', ['h' => 'Settings', 'w' => $w, 'sub' => 'Your account', 'now' => 'settings']) ?>
	<div class="split-2">
		<form method="post" class="form glass glass--plain glass--tight acct-card">
			<?= Guard::field() ?>
			<h2 class="h3">You</h2>
			<label class="field"><span>Name</span><input class="input" name="name" value="<?= e($u['name']) ?>" autocomplete="name"></label>
			<label class="field"><span>Email</span><input class="input" name="email" type="email" value="<?= e($u['email']) ?>" autocomplete="email"></label>
			<p class="help">Changing it? Type your current password below too. You sign in with the new one from then on.</p>
			<label class="field"><span>Phone</span><input class="input" name="phone" value="<?= e($u['phone']) ?>" autocomplete="tel" inputmode="tel"></label>
			<h2 class="h3 gap-t-sm">Password</h2>
			<label class="field"><span>Current password</span><input class="input" name="current" type="password" autocomplete="current-password"></label>
			<label class="field"><span>New password</span><input class="input" name="pass" type="password" minlength="8" autocomplete="new-password"></label>
			<p class="help">Leave the new password blank to keep the one you have. Changing it signs out every other device.</p>
			<button class="pill pill--block" type="submit">Save</button>
		</form>
		<aside class="form glass glass--plain glass--tight">
			<h2 class="h3">How we reach you</h2>
			<label class="check"><span>Email: sign-in, receipts, RSVP alerts</span><input type="checkbox" checked disabled></label>
			<label class="check"><span>WhatsApp: not switched on</span><input type="checkbox" disabled></label>
			<label class="check"><span>SMS: not switched on</span><input type="checkbox" disabled></label>
			<p class="help">WhatsApp and SMS stay off until an administrator connects a provider. Until then nothing is sent to a phone.</p>
			<form method="post" action="<?= e(App::url('/sign-out')) ?>"><?= Guard::field() ?><button class="pill pill--glass pill--block" type="submit"><?= icon('out') ?>Sign out</button></form>
		</aside>
	</div>
</div>
