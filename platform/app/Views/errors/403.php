<div class="page wrap">
	<div class="glass empty error-card">
		<div class="error-code"><?= dots('403') ?></div>
		<h1 class="h2">This part is for staff</h1>
		<p>You are signed in, but this area is for the people who run the site. If that looks wrong, ask them to change your role.</p>
		<a class="pill" href="<?= e(App::url('/account')) ?>">Back to your page</a>
	</div>
</div>
