<h1 class="gate__h">Welcome back.</h1>
<p class="sub gate__sub">Sign in to your wedding page.</p>
<form method="post" action="<?= e(App::url('/sign-in')) ?>" class="gate__form">
	<?= Guard::field() ?>
	<input type="hidden" name="to" value="<?= e($to) ?>">

	<label for="email">Email</label>
	<input id="email" name="email" type="email" autocomplete="email" required autofocus value="<?= e(Guard::old('email')) ?>">

	<label for="pass">Password</label>
	<input id="pass" name="pass" type="password" autocomplete="current-password" required>

	<button class="btn btn--acid btn--block" type="submit">Sign in</button>
	<a class="gate__small" href="<?= e(App::url('/forgot')) ?>">Forgot your password?</a>
</form>
