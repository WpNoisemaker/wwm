<?php /** A private page: one glass card asking for the couple's code. */ ?>
<div class="page wrap">
	<div class="auth">
		<div class="glass">
			<?= flashes() ?>
			<div class="auth__head"><span class="cbtn cbtn--lg auth__mark"><?= icon('lock') ?></span><h1>A private page</h1><p>This one is not public. If the couple gave you a code, put it in.</p></div>
			<form method="post" class="form">
				<?= Guard::field() ?>
				<label class="field"><span>Code</span><input class="input input--code" id="code" name="code" required autofocus autocomplete="off" autocapitalize="characters"></label>
				<button class="pill pill--block" type="submit">Open it</button>
			</form>
		</div>
		<p class="auth__foot"><a href="<?= e(App::url('/weddings')) ?>">Browse the public pages instead</a></p>
	</div>
</div>
