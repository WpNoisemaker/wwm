<?php
/**
 * One of the site's own ads, in the shape of the slot it sits in. Always labelled as an ad. While
 * an admin edits the page, it carries a link to change it in HQ → Ads.
 *
 * @var string $as @var int $id @var string $title @var string $line @var string $button @var string $price @var string $img @var string $href
 */
$edit = LiveEdit::editing() ? '<a class="promo__edit" href="' . e(App::url('/hq/ads#ad' . $id)) . '" data-cms-skip>' . icon('pen') . 'Edit this ad</a>' : '';
$from = $price !== '' ? 'From ' . $price . ', once' : '';
?>
<?php if ($as === 'post'): ?>
	<article class="post post--ad" data-ad="1" data-id="ad" data-href="<?= e($href) ?>" data-likes="0" data-replies="0" aria-label="Ad: <?= e($title) ?>" data-cms-skip>
		<span class="post__topic promo__lab">Ad · Wedding Websites &amp; More</span>
		<div class="post__body promo__body">
			<?php if ($img !== ''): ?><?= sq($img, '', 'promo__pic') ?><?php endif; ?>
			<span class="promo__tag"><?= icon('s-crown') ?>Premium</span>
			<p class="promo__t"><?= e($title) ?></p>
			<?php if ($line !== ''): ?><p class="promo__s"><?= e($line) ?></p><?php endif; ?>
		</div>
		<span class="promo__foot"><a class="pill promo__go" href="<?= e($href) ?>"><?= e($button) ?></a><?php if ($from !== ''): ?><span class="promo__p"><?= $from ?></span><?php endif; ?><?= $edit ?></span>
	</article>
<?php elseif ($as === 'card'): ?>
	<a class="card promo-card" href="<?= e($href) ?>" aria-label="Ad: <?= e($title) ?>" data-cms-skip>
		<span class="sq"><?= $img !== '' ? photo($img, '') : '<span class="fill-art" aria-hidden="true">&amp;</span>' ?><span class="sq__tag promo__ad">Ad</span><span class="promo-card__over"><span class="promo__tag"><?= icon('s-crown') ?>Premium</span><span class="promo-card__t"><?= e($title) ?></span></span></span>
		<span class="card__cap"><span class="card__t"><?= e($button) ?></span><?php if ($from !== ''): ?><span class="card__s"><?= $from ?></span><?php endif; ?></span>
	</a>
<?php elseif ($as === 'story'): ?>
	<a class="story-card promo-story" href="<?= e($href) ?>" aria-label="Ad: <?= e($title) ?>" data-cms-skip>
		<?php if ($img !== ''): ?><?= sq($img, '', 'sq--wide') ?><?php endif; ?>
		<span class="eyebrow">Ad · Wedding Websites &amp; More</span>
		<span class="story-card__t"><?= e($title) ?></span>
		<?php if ($line !== ''): ?><span class="story-card__x"><?= e($line) ?></span><?php endif; ?>
		<span class="story-card__m promo__cta"><?= icon('s-crown') ?><?= e($button) ?><?= $price !== '' ? ' · from ' . $price : '' ?></span>
	</a>
<?php else: ?>
	<section class="sec wrap promo-band" data-cms-skip>
		<a class="promo promo--band" href="<?= e($href) ?>" aria-label="Ad: <?= e($title) ?>">
			<?php if ($img !== ''): ?><?= sq($img, '', 'promo__pic') ?><?php endif; ?>
			<span class="promo__in">
				<span class="promo__lab">Ad · Wedding Websites &amp; More</span>
				<span class="promo__tag"><?= icon('s-crown') ?>Premium</span>
				<span class="promo__t"><?= e($title) ?></span>
				<?php if ($line !== ''): ?><span class="promo__s"><?= e($line) ?></span><?php endif; ?>
				<span class="promo__foot"><span class="pill pill--sm promo__go"><?= e($button) ?></span><?php if ($from !== ''): ?><span class="promo__p"><?= $from ?></span><?php endif; ?></span>
			</span>
		</a>
		<?= $edit ?>
	</section>
<?php endif; ?>
