<div class="auth__head"><span class="cbtn cbtn--lg auth__mark"><span class="logo">&amp;</span></span><h1>Make your page</h1><p>It is free, and it stays free. In two minutes you will have a link to share.</p></div>
<form method="post" action="<?= e(App::url('/join')) ?>" class="form">
	<?= Guard::field() ?>
	<div class="form-grid form-grid--2">
		<label class="field"><span>Your first name</span><input class="input" id="a" name="a" required autofocus value="<?= e(Guard::old('a')) ?>" placeholder="Ibrahim"></label>
		<label class="field"><span>Their first name</span><input class="input" id="b" name="b" required value="<?= e(Guard::old('b')) ?>" placeholder="Ummi"></label>
		<label class="field"><span>Wedding date (optional)</span><input class="input" id="date" name="date" type="date" value="<?= e(Guard::old('date')) ?>"></label>
		<label class="field"><span>City (optional)</span><input class="input" id="city" name="city" value="<?= e(Guard::old('city')) ?>" placeholder="Ilorin"></label>
	</div>
	<hr class="rule">
	<label class="field"><span>Your name</span><input class="input" id="name" name="name" required autocomplete="name" value="<?= e(Guard::old('name')) ?>"></label>
	<label class="field"><span>Email</span><input class="input" id="email" name="email" type="email" required autocomplete="email" value="<?= e(Guard::old('email')) ?>"></label>
	<label class="field"><span>Phone (optional)</span><input class="input" id="phone" name="phone" autocomplete="tel" inputmode="tel" value="<?= e(Guard::old('phone')) ?>" placeholder="0803 000 0000"></label>
	<label class="field"><span>Password, at least 8 characters</span><input class="input" id="pass" name="pass" type="password" required minlength="8" autocomplete="new-password"></label>
	<button class="pill pill--block" type="submit">Create my page</button>
	<p class="after-pill">Your page starts as private. Nobody can see it until you say so.</p>
	<p class="after-pill">By creating a page you agree to our <a class="linkish" href="<?= e(App::url('/terms')) ?>">terms</a>, <a class="linkish" href="<?= e(App::url('/privacy')) ?>">privacy policy</a> and <a class="linkish" href="<?= e(App::url('/refunds')) ?>">refund policy</a>. Free pages can be removed at any time; paid pages stay live for good.</p>
</form>
