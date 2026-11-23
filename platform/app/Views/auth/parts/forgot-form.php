<div class="auth__head"><span class="cbtn cbtn--lg auth__mark"><?= icon('lock') ?></span><h1>Forgot it?</h1><p>Enter the email address on your account and we will send you a link to set a new password.</p></div>
<?php if ($link !== ''): ?>
	<p class="note"><?= icon('info') ?><span>Debug mode is on, so here is the link instead of an email: <a href="<?= e($link) ?>"><?= e($link) ?></a></span></p>
<?php endif; ?>
<form method="post" action="<?= e(App::url('/forgot')) ?>" class="form">
	<?= Guard::field() ?>
	<label class="field"><span>Email</span><input class="input" id="email" name="email" type="email" required autofocus autocomplete="email"></label>
	<button class="pill pill--block" type="submit">Send the link</button>
</form>
