<h1 class="gate__h">Forgot it?</h1>
<p class="sub gate__sub">Give us the email on the account and we will send a link to set a new password.</p>
<?php if ($link !== ''): ?>
	<p class="note note--ok">Debug mode is on, so here is the link instead of an email:<br>
		<a href="<?= e($link) ?>"><?= e($link) ?></a></p>
<?php endif; ?>
<form method="post" action="<?= e(App::url('/forgot')) ?>" class="gate__form">
	<?= Guard::field() ?>
	<label for="email">Email</label>
	<input id="email" name="email" type="email" required autofocus>
	<button class="btn btn--acid btn--block" type="submit">Send the link</button>
</form>
