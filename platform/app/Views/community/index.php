<section class="sec wrap talk__top">
	<?php foreach (Guard::takeFlash() as $f): ?><p class="note note--<?= e($f['kind']) ?>"><?= e($f['msg']) ?></p><?php endforeach; ?>
	<h1 class="talk__h">Say the thing you<br>cannot say out loud.</h1>
	<p class="lede talk__lede">Love, family, money, faith, in-laws, fertility, starting over. Post under any name you like &mdash; there is no account, and no real name is attached to anything here.</p>

	<div class="rail chips">
		<a class="chip<?= $topic === '' ? ' is-on' : '' ?>" href="<?= e(App::url('/community?sort=' . $sort)) ?>">Everything</a>
		<?php foreach ($topics as $k => $label): ?>
			<a class="chip<?= $topic === $k ? ' is-on' : '' ?>" href="<?= e(App::url('/community?topic=' . urlencode($k) . '&sort=' . $sort)) ?>"><?= e($label) ?></a>
		<?php endforeach; ?>
	</div>
	<div class="rail chips">
		<?php foreach (['top' => 'Most felt', 'new' => 'Newest'] as $k => $label): ?>
			<a class="chip<?= $sort === $k ? ' is-on' : '' ?>" href="<?= e(App::url('/community?sort=' . $k . ($topic !== '' ? '&topic=' . urlencode($topic) : ''))) ?>"><?= e($label) ?></a>
		<?php endforeach; ?>
	</div>
</section>

<?php if (!$rows): ?>
	<section class="sec wrap"><div class="blank"><p class="blank__h">Nothing here yet.</p><p class="sub">Be the first to say it.</p></div></section>
<?php else: ?>
<section class="sec wrap">
	<!-- With JavaScript off this is a plain list; deck.js turns it into a swipeable deck. -->
	<div class="deck" data-deck>
		<?php foreach ($rows as $i => $t): ?>
			<article class="post post--<?= e($t['bg']) ?>" data-card data-i="<?= e($i) ?>" data-id="<?= e($t['id']) ?>">
				<span class="post__mark" aria-hidden="true">Wedding Websites &amp; More · Wedding Websites &amp; More · Wedding Websites &amp; More</span>
				<header class="post__top">
					<em class="post__topic"><?= e(Talk::topic((string) $t['topic'])) ?></em>
					<span class="post__ago"><?= e(ago((string) $t['created_at'])) ?></span>
				</header>
				<p class="post__says"><?= e((string) $t['body']) ?></p>
				<footer class="post__foot">
					<span><?= e((int) $t['replies']) ?> repl<?= (int) $t['replies'] === 1 ? 'y' : 'ies' ?></span>
					<span><?= icon('heart', 13) ?> <?= e((int) $t['likes']) ?></span>
				</footer>
			</article>
		<?php endforeach; ?>
	</div>

	<!-- The buttons do the liking and saving. Swiping only moves between posts. -->
	<div class="deck__bar" data-bar hidden>
		<button class="deck__b" type="button" data-prev aria-label="Previous post"><?= icon('back', 20) ?></button>
		<form method="post" class="deck__like" data-likeform action="<?= e(App::url('/community/0/like')) ?>">
			<?= Guard::field() ?>
			<button class="deck__b deck__b--like" type="submit" aria-label="Like this post"><?= icon('heart', 22) ?></button>
		</form>
		<button class="deck__b deck__b--save" type="button" data-save aria-label="Save this post"><?= icon('lock', 20) ?></button>
		<a class="deck__b" data-open href="#" aria-label="Open replies"><?= icon('talk', 20) ?></a>
		<button class="deck__b" type="button" data-next aria-label="Next post"><?= icon('arrow', 20) ?></button>
	</div>
	<p class="deck__hint" data-hint hidden>Swipe to move between posts. The buttons like and save.</p>
</section>
<?php endif; ?>

<section class="sec wrap">
	<form method="post" action="<?= e(App::url('/community/post')) ?>" class="form form--card say">
		<?= Guard::field() ?>
		<h3 class="form__g">Say yours</h3>
		<label for="t-topic">What is it about</label>
		<select id="t-topic" name="topic">
			<?php foreach ($topics as $k => $label): ?><option value="<?= e($k) ?>"<?= $topic === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
		</select>
		<label for="t-body">Say it</label>
		<textarea id="t-body" name="body" rows="5" required minlength="12" placeholder="Nobody will know it was you."></textarea>
		<label for="t-pen">A name to post under</label>
		<input id="t-pen" name="pen_name" maxlength="60" placeholder="Anything you like">
		<small class="gate__hint">Any name at all. It is a label on the thread, not your identity, and it is never shown on the post itself.</small>
		<button class="btn btn--acid" type="submit">Post it</button>
	</form>
</section>
