<section class="sec wrap" id="gallery">
	<h2 class="wed__h">Guest gallery</h2>
	<div class="gal">
		<?php foreach ($photos as $ph): ?>
			<figure class="gal__i">
				<?= photo((string) $ph['image'], (string) ($ph['body'] ?: 'A guest photo')) ?>
				<?php if ($ph['body']): ?><figcaption><?= e($ph['body']) ?></figcaption><?php endif; ?>
			</figure>
		<?php endforeach; ?>
	</div>
</section>
