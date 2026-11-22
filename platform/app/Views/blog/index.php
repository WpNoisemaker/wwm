<section class="sec wrap">
	<div class="sec__head">
		<div><h2>Wedding tips &amp; how-tos</h2><p class="sub">Written for Nigerian and African weddings, not borrowed from somewhere else.</p></div>
	</div>
	<div class="rail chips">
		<a class="chip<?= $cat === '' ? ' is-on' : '' ?>" href="<?= e(App::url('/blog')) ?>">Everything</a>
		<?php foreach ($cats as $c): ?>
			<a class="chip<?= $cat === $c['category'] ? ' is-on' : '' ?>" href="<?= e(App::url('/blog?category=' . urlencode((string) $c['category']))) ?>"><?= e($c['category']) ?><em><?= e($c['n']) ?></em></a>
		<?php endforeach; ?>
	</div>
	<?php if (!$rows): ?>
		<div class="blank"><p class="blank__h">Nothing published here yet.</p></div>
	<?php else: ?>
		<div class="pgrid">
			<?php foreach ($rows as $po): ?>
				<a class="pcard" href="<?= e(App::url('/blog/' . $po['slug'])) ?>">
					<span class="pcard__art blob blob--orange"><?= photo((string) $po['cover'], '') ?></span>
					<span class="pcard__k"><?= e((string) $po['category']) ?> &middot; <?= e(max(1, (int) round(str_word_count(strip_tags((string) $po['body'])) / 200))) ?> min read</span>
					<b class="pcard__t"><?= e((string) $po['title']) ?></b>
					<span class="pcard__x"><?= e(mb_strimwidth((string) $po['excerpt'], 0, 130, '…')) ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
