<?php /** One post and its replies. Anyone can reply, no account. */ ?>
<div class="page wrap">
	<div class="thread">
		<div class="thread__post">
			<div class="post-solo">
				<article class="post <?= e(talk_colour((string) $t['bg'])) ?>">
					<span class="post__topic"><?= e(Talk::topic((string) $t['topic'])) ?></span>
					<div class="post__body"><p class="post__text<?= mb_strlen((string) $t['body']) > 150 ? ' post__text--long' : '' ?>"><?= nl2br(e((string) $t['body'])) ?></p><span class="post__mark"><i aria-hidden="true">&amp;</i><?= e(Share::host()) ?></span></div>
					<span class="post__meta"><?= e($t['pen_name'] ?: 'Someone') ?> · <?= e(ago((string) $t['created_at'])) ?></span>
				</article>
			</div>
			<div class="actions">
				<form method="post" action="<?= e(App::url('/community/' . $t['id'] . '/like')) ?>" class="act">
					<?= Guard::field() ?>
					<button class="cbtn cbtn--lg<?= $liked ? ' is-on' : '' ?>" type="submit" aria-label="Love this post" aria-pressed="<?= $liked ? 'true' : 'false' ?>"><?= icon('heart') ?><span class="act__n"><?= (int) $t['likes'] ?></span></button><span class="act__l">Love</span>
				</form>
				<div class="act"><a class="cbtn cbtn--lg" href="#reply" aria-label="Write a reply"><?= icon('reply') ?><span class="act__n"><?= (int) $t['replies'] ?></span></a><span class="act__l">Reply</span></div>
				<div class="act"><button class="cbtn cbtn--lg" type="button" data-share aria-label="Share this post"><?= icon('share') ?></button><span class="act__l">Share</span></div>
			</div>
		</div>
		<div class="thread__replies">
			<?= flashes() ?>
			<div class="block-head"><h2 class="h2"><?= (int) $t['replies'] ?> repl<?= (int) $t['replies'] === 1 ? 'y' : 'ies' ?></h2></div>
			<?php if (!$replies): ?>
				<p class="sub">Nobody has answered yet. Say something kind.</p>
			<?php else: ?>
				<div class="replies">
					<?php foreach ($replies as $r): ?>
						<div class="reply"><p><?= nl2br(e((string) $r['body'])) ?></p><span><?= e((string) ($r['pen_name'] ?: 'Someone')) ?> · <?= e(ago((string) $r['created_at'])) ?></span></div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<form id="reply" method="post" action="<?= e(App::url('/community/' . $t['id'] . '/reply')) ?>" class="form glass glass--plain glass--tight gap-t">
				<?= Guard::field() ?>
				<label class="field"><span>Say something back</span><textarea class="input" name="body" rows="3" required maxlength="1500"></textarea></label>
				<label class="field"><span>Pen name: anything you like</span><input class="input" name="pen_name" maxlength="40" placeholder="Someone" autocomplete="off"></label>
				<button class="pill pill--block" type="submit">Reply</button>
				<p class="after-pill">No account needed. Only your pen name appears.</p>
			</form>
		</div>
	</div>
</div>
