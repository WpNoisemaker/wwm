<h1 class="gate__h">Make your page.</h1>
<p class="sub gate__sub">Free, and it stays free. Two minutes and you have a link to share.</p>
<form method="post" action="<?= e(App::url('/join')) ?>" class="gate__form">
	<?= Guard::field() ?>

	<div class="gate__pair">
		<span>
			<label for="a">Your first name</label>
			<input id="a" name="a" required autofocus value="<?= e(Guard::old('a')) ?>" placeholder="Ibrahim">
		</span>
		<span>
			<label for="b">Their first name</label>
			<input id="b" name="b" required value="<?= e(Guard::old('b')) ?>" placeholder="Ummi">
		</span>
	</div>

	<div class="gate__pair">
		<span>
			<label for="date">Wedding date <em>optional</em></label>
			<input id="date" name="date" type="date" value="<?= e(Guard::old('date')) ?>">
		</span>
		<span>
			<label for="city">City <em>optional</em></label>
			<input id="city" name="city" value="<?= e(Guard::old('city')) ?>" placeholder="Kano">
		</span>
	</div>

	<hr class="gate__rule">

	<label for="name">Your name</label>
	<input id="name" name="name" required autocomplete="name" value="<?= e(Guard::old('name')) ?>">

	<label for="email">Email</label>
	<input id="email" name="email" type="email" required autocomplete="email" value="<?= e(Guard::old('email')) ?>">

	<label for="phone">Phone <em>optional</em></label>
	<input id="phone" name="phone" autocomplete="tel" value="<?= e(Guard::old('phone')) ?>" placeholder="0803 000 0000">

	<label for="pass">Password</label>
	<input id="pass" name="pass" type="password" required minlength="8" autocomplete="new-password">
	<small class="gate__hint">At least 8 characters.</small>

	<button class="btn btn--acid btn--block" type="submit">Create my page</button>
	<small class="gate__hint">Your page starts private. Nobody sees it until you say so.</small>
</form>
