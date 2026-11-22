<h1 class="gate__h">New password.</h1>
<p class="sub gate__sub">Choose one you have not used anywhere else.</p>
<form method="post" action="<?= e(App::url('/reset/' . $token)) ?>" class="gate__form">
	<?= Guard::field() ?>
	<label for="pass">New password</label>
	<input id="pass" name="pass" type="password" required minlength="8" autofocus autocomplete="new-password">
	<small class="gate__hint">At least 8 characters. Every other device will be signed out.</small>
	<button class="btn btn--acid btn--block" type="submit">Save it</button>
</form>
