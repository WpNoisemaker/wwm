<?php
/** A customer account: just enough to message sellers. @var string $to */
ob_start(); ?>
<div class="auth__head"><span class="cbtn cbtn--lg auth__mark"><span class="logo">&amp;</span></span><h1>Create a customer account</h1><p>It is free, and it lets you message sellers on the marketplace, get their invoices and keep your receipts.</p></div>
<form method="post" action="<?= e(App::url('/join/customer')) ?>" class="form">
	<?= Guard::field() ?><input type="hidden" name="to" value="<?= e($to) ?>">
	<label class="field"><span>Your name</span><input class="input" name="name" required autocomplete="name" value="<?= e(Guard::old('name')) ?>"></label>
	<label class="field"><span>Email</span><input class="input" name="email" type="email" required autocomplete="email" value="<?= e(Guard::old('email')) ?>"></label>
	<label class="field"><span>Phone (optional)</span><input class="input" name="phone" autocomplete="tel" inputmode="tel" value="<?= e(Guard::old('phone')) ?>"></label>
	<label class="field"><span>Password, at least 8 characters</span><input class="input" name="pass" type="password" required minlength="8" autocomplete="new-password"></label>
	<button class="pill pill--block" type="submit">Create my account</button>
	<p class="after-pill">By creating an account you agree to our <a class="linkish" href="<?= e(App::url('/terms')) ?>">terms</a> and <a class="linkish" href="<?= e(App::url('/privacy')) ?>">privacy policy</a>. We never hold money for sellers: you pay them directly.</p>
</form>
<?php $slot = (string) ob_get_clean();
echo View::partial('auth/_shell', ['slot' => $slot, 'foot' => 'Already have an account? <a href="' . e(App::url('/sign-in?to=' . urlencode($to))) . '">Sign in</a>. Planning a wedding? <a href="' . e(App::url('/join')) . '">Make your page</a>.']);
