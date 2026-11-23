<?php /** One post and its replies. Anyone can reply, no account. */ ?>
<div class="page wrap">
	<div class="thread">
		<div class="thread__post">
			<div class="post-solo" data-post-swipe data-next="<?= e($nav['next']) ?>" data-prev="<?= e($nav['prev']) ?>">
				<article class="post <?= e(talk_colour((string) $t['bg'])) ?>">
					<span class="post__topic"><?= e(Talk::topic((string) $t['topic'])) ?></span>
					<div class="post__body"><p class="post__text<?= mb_strlen((string) $t['body']) > 150 ? ' post__text--long' : '' ?>"><?= nl2br(e((string) $t['body'])) ?></p><span class="post__mark"><i aria-hidden="true">&amp;</i><?= e(Share::host()) ?></span></div>
					<span class="post__meta"><?= e($t['pen_name'] ?: 'Someone') ?> · <?= e(ago((string) $t['created_at'])) ?></span>
				</article>
			</div>
			<div class="actions">
				<div class="act"><?php if ($nav['prev'] !== ''): ?><a class="cbtn cbtn--lg" href="<?= e($nav['prev']) ?>" aria-label="Previous post" data-post-prev><?= icon('back') ?></a><?php else: ?><a class="cbtn cbtn--lg" href="<?= e(App::url('/community')) ?>" aria-label="Back to the community"><?= icon('back') ?></a><?php endif; ?><span class="act__l">Back</span></div>
				<form method="post" action="<?= e(App::url('/community/' . $t['id'] . '/like')) ?>" class="act">
					<?= Guard::field() ?>
					<button class="cbtn cbtn--lg<?= $liked ? ' is-on' : '' ?>" type="submit" aria-label="Love this post" aria-pressed="<?= $liked ? 'true' : 'false' ?>"><?= icon('heart') ?><span class="act__n"><?= (int) $t['likes'] ?></span></button><span class="act__l">Love</span>
				</form>
				<div class="act"><a class="cbtn cbtn--lg" href="#reply" aria-label="Write a reply"><?= icon('reply') ?><span class="act__n"><?= (int) $t['replies'] ?></span></a><span class="act__l">Reply</span></div>
				<div class="act"><button class="cbtn cbtn--lg" type="button" data-share aria-label="Share this post"><?= icon('share') ?></button><span class="act__l">Share</span></div>
				<?php if ($nav['next'] !== ''): ?><div class="act"><a class="cbtn cbtn--lg" href="<?= e($nav['next']) ?>" aria-label="Next post" data-post-next><?= icon('next') ?></a><span class="act__l">Next</span></div><?php endif; ?>
			</div>
		</div>
		<div class="thread__replies">
			<?= flashes() ?>
			<div class="block-head"><h2 class="h2"><?= (int) $t['replies'] ?> repl<?= (int) $t['replies'] === 1 ? 'y' : 'ies' ?></h2></div>
			<?php if (!$replies): ?>
				<p class="sub">Nobody has answered yet. Say something kind.</p>
			<?php else: ?>
				<?php
				// Replies as a thread: each answer sits under the reply it answers.
				$kids = [];
				foreach ($replies as $r) { $kids[(int) ($r['parent_id'] ?? 0)][] = $r; }
				$tree = static function (int $parent, int $depth) use (&$tree, $kids, $t): string {
					if (empty($kids[$parent])) { return ''; }
					$o = '<div class="replies' . ($depth ? ' replies--sub' : '') . '">';
					foreach ($kids[$parent] as $r) {
						$rid = (int) $r['id'];
						$o .= '<div class="reply" id="r' . $rid . '"><p>' . nl2br(e((string) $r['body'])) . '</p><span>' . e((string) ($r['pen_name'] ?: 'Someone')) . ' · ' . e(ago((string) $r['created_at'])) . '</span>'
							. '<details class="reply__answer"><summary>' . icon('reply') . 'Reply</summary>'
							. '<form method="post" action="' . e(App::url('/community/' . $t['id'] . '/reply')) . '" class="form">' . Guard::field()
							. '<input type="hidden" name="parent_id" value="' . $rid . '">'
							. '<label class="sr" for="rb' . $rid . '">Your reply</label><textarea class="input" id="rb' . $rid . '" name="body" rows="2" required maxlength="1500" placeholder="Reply to ' . e((string) ($r['pen_name'] ?: 'Someone')) . '"></textarea>'
							. '<div class="reply__row"><label class="sr" for="rn' . $rid . '">Pen name</label><input class="input" id="rn' . $rid . '" name="pen_name" maxlength="40" placeholder="Pen name" autocomplete="off"><button class="pill pill--sm" type="submit">Reply</button></div>'
							. '</form></details>'
							. $tree($rid, min($depth + 1, 4)) . '</div>';
					}
					return $o . '</div>';
				};
				echo $tree(0, 0);
				?>
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
