<div class="auth__head"><span class="cbtn cbtn--lg auth__mark"><?= icon('lock') ?></span><h1>New password</h1><p>Choose one you have not used anywhere else.</p></div>
<form method="post" action="<?= e(App::url('/reset/' . $token)) ?>" class="form">
	<?= Guard::field() ?>
	<label class="field"><span>New password, at least 8 characters</span><input class="input" id="pass" name="pass" type="password" required minlength="8" autofocus autocomplete="new-password"></label>
	<p class="help">Every other device will be signed out.</p>
	<button class="pill pill--block" type="submit">Save it</button>
</form>
