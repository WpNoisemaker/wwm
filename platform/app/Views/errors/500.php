<div class="page wrap">
	<div class="glass empty error-card">
		<div class="error-code"><?= dots('500') ?></div>
		<h1 class="h2">Something broke on our side</h1>
		<p>It has been logged. Try again in a moment.</p>
		<a class="pill" href="<?= e(App::url('/')) ?>">Go home</a>
	</div>
</div>
