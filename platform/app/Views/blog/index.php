<?php /** Stories: wedding tips and how-tos. */
$mins = static fn (array $p): int => max(1, (int) round(str_word_count(strip_tags((string) $p['body'])) / 200)); ?>
<div class="page wrap">
	<header class="page-head">
		<span class="eyebrow">Written for our weddings, not borrowed</span>
		<h1>Planning, honestly</h1>
		<p class="lede">Tips and how-tos for Nigerian and African weddings.</p>
	</header>
	<?php if ($cats): ?>
		<nav class="chips chips--center" aria-label="Topics">
			<a class="chip<?= $cat === '' ? ' is-on' : '' ?>" href="<?= e(App::url('/blog')) ?>">Everything</a>
			<?php foreach ($cats as $c): ?>
				<a class="chip<?= $cat === $c['category'] ? ' is-on' : '' ?>" href="<?= e(App::url('/blog?category=' . urlencode((string) $c['category']))) ?>"><?= e($c['category']) ?></a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>
	<?php if (!$rows): ?>
		<div class="glass glass--plain empty gap-t"><span class="cbtn"><?= icon('book') ?></span><p>Nothing published here yet.</p></div>
	<?php else: ?>
		<div class="stories gap-t">
			<?php foreach ($rows as $po): ?>
				<a class="story-card" href="<?= e(App::url('/blog/' . $po['slug'])) ?>">
					<?= sq((string) $po['cover'], '', 'sq--wide') ?>
					<span class="eyebrow"><?= e((string) $po['category']) ?> · <?= $mins($po) ?> min read</span>
					<span class="story-card__t"><?= e((string) $po['title']) ?></span>
					<span class="story-card__x"><?= e(mb_strimwidth((string) $po['excerpt'], 0, 140, '…')) ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
