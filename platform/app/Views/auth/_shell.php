<?php /** The frame every sign-in style page sits in: brand, card, flashes. */ ?>
<div class="gate">
	<a class="gate__brand" href="<?= e(App::url('/')) ?>">
		<span class="bar__mark">&amp;</span>
		<span>Wedding Websites<span class="muted"> &amp; More</span></span>
	</a>
	<div class="gate__card">
		<?php foreach (Guard::takeFlash() as $f): ?>
			<p class="note note--<?= e($f['kind']) ?>"><?= e($f['msg']) ?></p>
		<?php endforeach; ?>
		<?= $slot ?>
	</div>
	<p class="gate__foot"><?= $foot ?? '' ?></p>
</div>
