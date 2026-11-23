<?php
/**
 * The community. Posts are words on colour; swipe left and right to move between them, the
 * buttons underneath like and save. Anyone can post, no account.
 *
 * @var array $rows @var string $topic @var string $sort @var array $topics @var array $liked
 */
$topicUrl = static fn (string $t, string $s): string => App::url('/community?' . http_build_query(array_filter(['topic' => $t, 'sort' => $s === 'top' ? '' : $s])));
?>
<div class="page wrap">
	<header class="page-head">
		<span class="eyebrow"><?= t('community.eyebrow.no-account-any-pen-name', 'Anonymous Community') ?></span>
		<h1><?= t('community.title.say-it-here-instead', 'Ask, share and get advice, without your name') ?></h1>
		<p class="lede"><?= t('community.intro.love-family-money-faith-inlaws', 'This is a safe place to voice your concerns and ask questions about your relationship, your wedding, marriage and family life. You do not need an account. Only the pen name you choose is shown, and nobody can see who you are.') ?></p>
	</header>
	<div class="wrap-narrow-flash"><?= flashes() ?></div>

	<nav class="chips chips--center" aria-label="Topics">
		<a class="chip<?= $topic === '' ? ' is-on' : '' ?>" href="<?= e($topicUrl('', $sort)) ?>">Everything</a>
		<?php foreach ($topics as $k => $label): ?>
			<a class="chip<?= $topic === $k ? ' is-on' : '' ?>" href="<?= e($topicUrl($k, $sort)) ?>"><?= e($label) ?></a>
		<?php endforeach; ?>
	</nav>
	<nav class="seg seg--center gap-t-sm" aria-label="Order">
		<a class="seg__a<?= $sort === 'top' ? ' is-on' : '' ?>" href="<?= e($topicUrl($topic, 'top')) ?>">Most felt</a>
		<a class="seg__a<?= $sort === 'new' ? ' is-on' : '' ?>" href="<?= e($topicUrl($topic, 'new')) ?>">Newest</a>
	</nav>

	<?php if (!$rows): ?>
		<div class="glass glass--plain empty gap-t"><span class="cbtn"><?= icon('chat') ?></span><p><?= t('community.text.nothing-here-yet-be-the', 'Nothing has been posted here yet. Be the first to say something.') ?></p><button class="pill" type="button" data-open="compose">Write a post</button></div>
	<?php else: ?>
	<div class="talk gap-t">
		<div class="talk__main">
			<div class="deck" data-deck tabindex="0" aria-label="Posts. Swipe, or use the arrow keys, to move between them.">
				<?php foreach ($rows as $k => $t): $long = mb_strlen((string) $t['body']) > 150; ?>
					<?php if ($k > 0 && $k % 8 === 4): ?><?= Promo::html('deck', intdiv($k, 8)) ?><?php endif; ?>
					<article class="post <?= e(talk_colour((string) $t['bg'])) ?>" data-id="<?= (int) $t['id'] ?>" data-href="<?= e(App::url('/community/' . $t['id'] . ($sort === 'new' ? '?s=new' : ''))) ?>"
					         data-like="<?= e(App::url('/community/' . $t['id'] . '/like')) ?>" data-likes="<?= (int) $t['likes'] ?>" data-replies="<?= (int) $t['replies'] ?>" data-liked="<?= isset($liked[(int) $t['id']]) ? '1' : '0' ?>">
						<span class="post__topic"><?= e(Talk::topic((string) $t['topic'])) ?></span>
						<div class="post__body"><p class="post__text<?= $long ? ' post__text--long' : '' ?>"><?= e(mb_strimwidth((string) $t['body'], 0, 320, '…')) ?></p><span class="post__mark"><i aria-hidden="true">&amp;</i><?= e(Share::host()) ?></span></div>
						<span class="post__meta"><?= e($t['pen_name'] ?: 'Someone') ?> · <?= e(ago((string) $t['created_at'])) ?></span>
					</article>
				<?php endforeach; ?>
			</div>
			<p class="deck-pos" data-deck-pos aria-live="polite">1 of <?= count($rows) ?></p>
			<div class="actions">
				<div class="act"><button class="cbtn cbtn--lg" type="button" data-deck-go="-1" aria-label="Previous post"><?= icon('back') ?></button><span class="act__l">Back</span></div>
				<div class="act"><button class="cbtn cbtn--lg" type="button" data-post-like aria-label="Love this post" aria-pressed="false"><?= icon('heart') ?><span class="act__n" data-count="likes"><?= (int) $rows[0]['likes'] ?></span></button><span class="act__l">Love</span></div>
				<div class="act"><a class="cbtn cbtn--lg" href="<?= e(App::url('/community/' . $rows[0]['id'])) ?>" data-post-open aria-label="Read and write replies"><?= icon('chat') ?><span class="act__n" data-count="replies"><?= (int) $rows[0]['replies'] ?></span></a><span class="act__l">Reply</span></div>
				<div class="act"><button class="cbtn cbtn--lg" type="button" data-share="<?= e(App::url('/community/' . $rows[0]['id'])) ?>" data-post-share aria-label="Share this post"><?= icon('share') ?></button><span class="act__l">Share</span></div>
				<div class="act"><button class="cbtn cbtn--lg" type="button" data-post-save aria-label="Save on this phone" aria-pressed="false"><?= icon('bookmark') ?></button><span class="act__l">Save</span></div>
				<div class="act"><button class="cbtn cbtn--lg" type="button" data-deck-go="1" aria-label="Next post"><?= icon('next') ?></button><span class="act__l">Next</span></div>
			</div>
			<button class="pill pill--block gap-t-sm" type="button" data-open="compose">Write a post</button>
			<p class="after-pill"><?= t('community.text.no-account-needed-only-your', 'You do not need an account, and only your pen name appears.') ?></p>
		</div>
		<aside class="talk__side">
			<div class="block-head"><h2 class="h2"><?= $sort === 'new' ? 'Just posted' : 'Most felt' ?></h2></div>
			<div class="rows">
				<?php foreach (array_slice($rows, 0, 6) as $t): ?>
					<a class="row-item row-item--plain talk__row" href="<?= e(App::url('/community/' . $t['id'])) ?>">
						<span><span class="talk__dot <?= e(talk_colour((string) $t['bg'])) ?>" aria-hidden="true"></span><span class="row-item__t talk__ex"><?= e(mb_strimwidth((string) $t['body'], 0, 90, '…')) ?></span><span class="row-item__s"><?= e(Talk::topic((string) $t['topic'])) ?> · <?= (int) $t['replies'] ?> replies</span></span>
						<span class="row-item__end"><?= (int) $t['likes'] ?><span class="sub">likes</span></span>
					</a>
				<?php endforeach; ?>
			</div>
		</aside>
	</div>
	<?php endif; ?>
</div>
<?= Guard::field() ?>

<div class="sheet" id="compose" role="dialog" aria-modal="true" aria-labelledby="compose-t">
	<div class="sheet__grab" aria-hidden="true"></div>
	<div class="sheet__head">
		<a class="cbtn cbtn--sm" href="#" data-close aria-label="Close"><?= icon('close') ?></a>
		<h2 id="compose-t"><?= t('community.heading.new-post', 'New post') ?></h2><span></span>
	</div>
	<form class="compose-form" method="post" action="<?= e(App::url('/community/post')) ?>">
		<?= Guard::field() ?>
		<div class="compose">
			<label class="sr" for="c-body">Your post</label>
			<textarea id="c-body" name="body" rows="4" maxlength="600" minlength="12" required placeholder="What's on your mind?"></textarea>
			<span class="compose__count" data-compose-count>0 / 600</span>
		</div>
		<div class="swatches" role="radiogroup" aria-label="Colour">
			<?php $first = true; foreach (talk_colours() as $k => $label): ?>
				<label class="<?= e(talk_colour($k)) ?>"><input type="radio" name="bg" value="<?= e($k) ?>"<?= $first ? ' checked' : '' ?>><span class="sr"><?= e($label) ?></span></label>
			<?php $first = false; endforeach; ?>
		</div>
		<div class="chips chips--center chips--radio" role="radiogroup" aria-label="Topic">
			<?php foreach ($topics as $k => $label): ?>
				<label class="chip"><input type="radio" name="topic" value="<?= e($k) ?>"<?= ($topic !== '' ? $topic === $k : $k === 'love') ? ' checked' : '' ?>><?= e($label) ?></label>
			<?php endforeach; ?>
		</div>
		<label class="field"><span>Pen name: anything you like</span><input class="input" name="pen_name" maxlength="40" placeholder="Quiet Storm" autocomplete="off"></label>
		<button class="pill pill--block" type="submit">Post</button>
		<p class="after-pill"><?= t('community.text.only-your-pen-name-appears', 'Only your pen name appears on the post.') ?></p>
	</form>
</div>
<script>
	(function () {
		var t = document.getElementById('c-body'), c = document.querySelector('[data-compose-count]'); if (!t) { return; }
		t.addEventListener('input', function () { c.textContent = t.value.length + ' / 600'; t.style.height = 'auto'; t.style.height = Math.min(t.scrollHeight, 260) + 'px'; });
	})();
</script>
