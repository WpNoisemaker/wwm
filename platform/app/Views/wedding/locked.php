<div class="gate">
	<a class="gate__brand" href="<?= e(App::url('/')) ?>"><span class="bar__mark">&amp;</span><span>Wedding Websites<span class="muted"> &amp; More</span></span></a>
	<div class="gate__card">
		<?php foreach (Guard::takeFlash() as $f): ?><p class="note note--<?= e($f['kind']) ?>"><?= e($f['msg']) ?></p><?php endforeach; ?>
		<h1 class="gate__h">A private page.</h1>
		<p class="sub gate__sub">This one is not public. If the couple gave you a code, put it in.</p>
		<form method="post" class="gate__form">
			<?= Guard::field() ?>
			<label for="code">Code</label>
			<input id="code" name="code" required autofocus autocomplete="off">
			<button class="btn btn--acid btn--block" type="submit">Open it</button>
		</form>
	</div>
	<p class="gate__foot"><a href="<?= e(App::url('/weddings')) ?>">Browse the public pages instead</a></p>
</div>
