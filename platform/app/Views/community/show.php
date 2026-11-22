<section class="sec wrap talk__one">
	<a class="pill pill--quiet" href="<?= e(App::url('/community')) ?>"><?= icon('back', 13) ?> All posts</a>
	<article class="post post--<?= e($t['bg']) ?> post--open">
		<span class="post__mark" aria-hidden="true">Wedding Websites &amp; More · Wedding Websites &amp; More · Wedding Websites &amp; More</span>
		<header class="post__top">
			<em class="post__topic"><?= e(Talk::topic((string) $t['topic'])) ?></em>
			<span class="post__ago"><?= e(ago((string) $t['created_at'])) ?></span>
		</header>
		<p class="post__says"><?= e((string) $t['body']) ?></p>
		<footer class="post__foot">
			<span><?= e((int) $t['replies']) ?> repl<?= (int) $t['replies'] === 1 ? 'y' : 'ies' ?></span>
			<form method="post" action="<?= e(App::url('/community/' . $t['id'] . '/like')) ?>">
				<?= Guard::field() ?>
				<button class="post__like" type="submit"><?= icon('heart', 14) ?> <?= e((int) $t['likes']) ?></button>
			</form>
		</footer>
	</article>

	<h3 class="form__g"><?= e((int) $t['replies']) ?> repl<?= (int) $t['replies'] === 1 ? 'y' : 'ies' ?></h3>
	<?php foreach ($replies as $r): ?>
		<div class="reply">
			<p><?= e((string) $r['body']) ?></p>
			<small><?= e((string) ($r['pen_name'] ?: 'Someone')) ?> &middot; <?= e(ago((string) $r['created_at'])) ?></small>
		</div>
	<?php endforeach; ?>

	<form method="post" action="<?= e(App::url('/community/' . $t['id'] . '/reply')) ?>" class="form form--card">
		<?= Guard::field() ?>
		<label for="r-body">Say something back</label>
		<textarea id="r-body" name="body" rows="3" required></textarea>
		<label for="r-pen">A name to post under</label>
		<input id="r-pen" name="pen_name" maxlength="60" placeholder="Anything you like">
		<button class="btn btn--acid" type="submit">Reply</button>
	</form>
</section>
