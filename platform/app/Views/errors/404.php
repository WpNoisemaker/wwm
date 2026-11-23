<div class="page wrap">
	<div class="glass empty error-card">
		<div class="error-code"><?= dots('404') ?></div>
		<h1 class="h2">That page is not here</h1>
		<p>It may have moved, or the link may have been mistyped. Try searching, or start again from the homepage.</p>
		<a class="pill" href="<?= e(App::url('/')) ?>">Go home</a>
	</div>
</div>
