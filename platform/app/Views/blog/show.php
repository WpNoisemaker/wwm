<?php /** One story. */ ?>
<article class="page wrap">
	<header class="page-head page-head--show">
		<span class="eyebrow"><?= e((string) $p['category']) ?> · <?= max(1, (int) round(str_word_count(strip_tags((string) $p['body'])) / 200)) ?> min read</span>
		<h1><?= e((string) $p['title']) ?></h1>
		<?php if ((string) $p['excerpt'] !== ''): ?><p class="lede"><?= e((string) $p['excerpt']) ?></p><?php endif; ?>
	</header>
	<?php if ($p['cover']): ?><div class="narrow" style="width:min(100%,880px)"><?= sq((string) $p['cover'], '', 'sq--wide', true) ?></div><?php endif; ?>
	<div class="prose gap-t"><?= $p['body'] /* trusted: written by the site owner in HQ */ ?></div>
	<p class="prose sub gap-t">Published <?= e($p['published_at'] ? date('j F Y', (int) strtotime((string) $p['published_at'])) : 'recently') ?></p>
</article>
<?php if ($more): ?>
<section class="sec wrap">
	<div class="sec-head"><span class="eyebrow">Keep reading</span><h2>Read next</h2></div>
	<div class="stories">
		<?php foreach ($more as $m): ?>
			<a class="story-card" href="<?= e(App::url('/blog/' . $m['slug'])) ?>">
				<?= sq((string) $m['cover'], '', 'sq--wide') ?>
				<span class="eyebrow"><?= e((string) $m['category']) ?></span>
				<span class="story-card__t"><?= e((string) $m['title']) ?></span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>
