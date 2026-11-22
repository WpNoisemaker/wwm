<div class="auth__head"><span class="cbtn cbtn--lg auth__mark"><span class="logo">&amp;</span></span><h1>Welcome back</h1><p>Sign in to your wedding page.</p></div>
<form method="post" action="<?= e(App::url('/sign-in')) ?>" class="form">
	<?= Guard::field() ?>
	<input type="hidden" name="to" value="<?= e($to) ?>">
	<label class="field"><span>Email</span><input class="input" id="email" name="email" type="email" autocomplete="email" required autofocus value="<?= e(Guard::old('email')) ?>"></label>
	<label class="field"><span>Password</span><input class="input" id="pass" name="pass" type="password" autocomplete="current-password" required></label>
	<button class="pill pill--block" type="submit">Sign in</button>
	<p class="center"><a class="linkish" href="<?= e(App::url('/forgot')) ?>">Forgot your password?</a></p>
</form>
