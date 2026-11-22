<section class="sec wrap" id="wall">
	<h2 class="wed__h">Wall of love</h2>
	<form class="wallform" method="post" action="<?= e(App::url('/w/' . $w['slug'] . '/note')) ?>">
		<?= Guard::field() ?>
		<label for="n-name" class="sr-only">Your name</label>
		<input id="n-name" name="name" placeholder="Your name" required>
		<label for="n-body" class="sr-only">Your note</label>
		<textarea id="n-body" name="body" rows="2" placeholder="Say something they will keep&hellip;" required maxlength="1000"></textarea>
		<button class="btn btn--acid" type="submit">Leave it</button>
	</form>

	<?php if (!$notes): ?>
		<p class="sub">Nobody has written yet. Be the first.</p>
	<?php else: ?>
		<div class="wall">
			<?php foreach ($notes as $n): $isPhoto = $n['kind'] === 'photo' && $n['image'] !== '' && !str_starts_with((string) $n['image'], 'wp:'); ?>
				<div class="wnote<?= $isPhoto ? ' wnote--photo' : '' ?>">
					<?php if ($isPhoto): ?><span class="wnote__art"><?= photo((string) $n['image'], '') ?></span><?php endif; ?>
					<span class="wnote__body"><?= e((string) $n['body']) ?></span>
					<span class="wnote__foot">
						<span class="wnote__who"><?= e((string) $n['name']) ?> &middot; <?= e(ago((string) $n['created_at'])) ?></span>
						<span class="wnote__likes"><?= icon('heart', 13) ?> <?= e((int) $n['likes']) ?></span>
					</span>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
