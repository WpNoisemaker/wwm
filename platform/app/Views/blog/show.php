<article class="sec wrap post">
	<p class="cap"><?= e((string) $p['category']) ?> &middot; <?= e(max(1, (int) round(str_word_count(strip_tags((string) $p['body'])) / 200))) ?> min read</p>
	<h1 class="post__h"><?= e((string) $p['title']) ?></h1>
	<p class="lede post__x"><?= e((string) $p['excerpt']) ?></p>
	<?php if ($p['cover']): ?><span class="post__art"><?= photo((string) $p['cover'], '', true) ?></span><?php endif; ?>
	<div class="post__body"><?= $p['body'] /* trusted: written by the site owner in HQ */ ?></div>
	<p class="post__when">Published <?= e($p['published_at'] ? date('j F Y', (int) strtotime((string) $p['published_at'])) : 'recently') ?></p>
</article>

<?php if ($more): ?>
<section class="sec wrap">
	<div class="sec__head"><div><h2>Read next</h2></div></div>
	<div class="pgrid">
		<?php foreach ($more as $m): ?>
			<a class="pcard" href="<?= e(App::url('/blog/' . $m['slug'])) ?>">
				<span class="pcard__art blob blob--green"><?= photo((string) $m['cover'], '') ?></span>
				<span class="pcard__k"><?= e((string) $m['category']) ?></span>
				<b class="pcard__t"><?= e((string) $m['title']) ?></b>
			</a>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>
