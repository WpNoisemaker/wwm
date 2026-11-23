<?php /** A vendor replying to the reviews guests left them on one wedding page. @var array $t @var array $w @var ?array $v @var array $rows */ ?>
<div class="page wrap">
	<header class="page-head"><span class="eyebrow"><?= e((string) ($v['name'] ?? 'Your business')) ?></span><h1>Reviews from <?= e(Wedding::names($w)) ?>&rsquo;s wedding</h1></header>
	<?= flashes() ?>
	<?php if (!$rows): ?>
		<div class="glass glass--plain empty"><span class="cbtn"><?= icon('star') ?></span><p>No guest has reviewed you on this wedding page yet. Reviews appear here as soon as they are posted.</p></div>
	<?php else: ?>
		<div class="acct-list">
			<?php foreach ($rows as $r): ?>
				<form method="post" class="form glass glass--plain glass--tight acct-card">
					<?= Guard::field() ?><input type="hidden" name="review_id" value="<?= (int) $r['id'] ?>">
					<p class="stars" aria-label="<?= (int) $r['stars'] ?> out of 5"><?= str_repeat('★', (int) $r['stars']) ?><span class="stars__off"><?= str_repeat('★', 5 - (int) $r['stars']) ?></span></p>
					<blockquote class="team-quote"><?= e((string) $r['body']) ?></blockquote>
					<p class="help"><?= e((string) $r['name']) ?>, a guest · <?= e(ago((string) $r['created_at'])) ?></p>
					<label class="field"><span>Your reply (shown under the review)</span><textarea class="input" name="reply" rows="2" maxlength="1000" placeholder="Thank you for the kind words!"><?= e((string) $r['reply']) ?></textarea></label>
					<button class="pill pill--sm" type="submit"><?= trim((string) $r['reply']) !== '' ? 'Update my reply' : 'Post my reply' ?></button>
				</form>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
