<?php
/**
 * The Premium ad, in the shape of the slot it sits in. Always labelled as an ad.
 *
 * @var string $as @var string $title @var string $line @var string $price @var string $img @var string $href
 */
?>
<?php if ($as === 'post'): ?>
	<article class="post post--ad" data-ad="1" data-id="ad" data-href="<?= e($href) ?>" data-likes="0" data-replies="0" aria-label="Ad: <?= e($title) ?>">
		<span class="post__topic promo__lab">Ad · Wedding Websites &amp; More</span>
		<div class="post__body promo__body">
			<?php if ($img !== ''): ?><?= sq($img, '', 'promo__pic') ?><?php endif; ?>
			<span class="promo__tag"><?= icon('s-crown') ?>Premium</span>
			<p class="promo__t"><?= e($title) ?></p>
			<p class="promo__s"><?= e($line) ?></p>
		</div>
		<span class="promo__foot"><a class="pill promo__go" href="<?= e($href) ?>">See Premium</a><span class="promo__p">From <?= $price ?>, once</span></span>
	</article>
<?php elseif ($as === 'card'): ?>
	<a class="card promo-card" href="<?= e($href) ?>" aria-label="Ad: <?= e($title) ?>">
		<span class="sq"><?= $img !== '' ? photo($img, '') : '<span class="fill-art" aria-hidden="true">&amp;</span>' ?><span class="sq__tag promo__ad">Ad</span><span class="promo-card__over"><span class="promo__tag"><?= icon('s-crown') ?>Premium</span><span class="promo-card__t"><?= e($title) ?></span></span></span>
		<span class="card__cap"><span class="card__t">Make yours Premium</span><span class="card__s">From <?= $price ?>, once</span></span>
	</a>
<?php elseif ($as === 'story'): ?>
	<a class="story-card promo-story" href="<?= e($href) ?>" aria-label="Ad: <?= e($title) ?>">
		<?php if ($img !== ''): ?><?= sq($img, '', 'sq--wide') ?><?php endif; ?>
		<span class="eyebrow">Ad · Wedding Websites &amp; More</span>
		<span class="story-card__t"><?= e($title) ?></span>
		<span class="story-card__x"><?= e($line) ?></span>
		<span class="story-card__m promo__cta"><?= icon('s-crown') ?>See Premium · from <?= $price ?></span>
	</a>
<?php else: ?>
	<section class="sec wrap promo-band">
		<a class="promo promo--band" href="<?= e($href) ?>" aria-label="Ad: <?= e($title) ?>">
			<?php if ($img !== ''): ?><?= sq($img, '', 'promo__pic') ?><?php endif; ?>
			<span class="promo__in">
				<span class="promo__lab">Ad · Wedding Websites &amp; More</span>
				<span class="promo__tag"><?= icon('s-crown') ?>Premium</span>
				<span class="promo__t"><?= e($title) ?></span>
				<span class="promo__s"><?= e($line) ?></span>
				<span class="promo__foot"><span class="pill pill--sm promo__go">See Premium</span><span class="promo__p">From <?= $price ?>, once</span></span>
			</span>
		</a>
	</section>
<?php endif; ?>
