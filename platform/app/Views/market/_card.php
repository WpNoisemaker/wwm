<?php
/**
 * One listing, the way the original marketplace showed it: who is selling (logo, name, verified,
 * city) on top, then every photo, swipeable, then the title and price.
 * @var array $l  a listing row with the seller columns   @var bool $who  show the seller row
 */
$who    = $who ?? true;
$href   = App::url('/market/' . $l['slug']);
$photos = Listing::photos($l) ?: [''];
$n      = count($photos);
$priceL = (int) $l['on_ask'] ? 'Price on request' : money(short_money((int) $l['price'])) . ((string) $l['price_unit'] !== '' ? ' ' . e($l['price_unit']) : '');
?>
<article class="card lcard">
	<?php if ($who && !empty($l['seller'])): ?>
		<a class="lcard__who" href="<?= e(App::url('/vendors/' . $l['seller_slug'])) ?>">
			<span class="lcard__av"><?php if (!empty($l['seller_logo'])): ?><?= photo((string) $l['seller_logo'], '') ?><?php else: ?><?= e(mb_strtoupper(mb_substr((string) $l['seller'], 0, 1))) ?><?php endif; ?></span>
			<span class="lcard__id"><b><?= e((string) $l['seller']) ?><?php if ((int) $l['seller_ok']): ?><?= icon('s-shield', 0, 'card__ok') ?><span class="sr"> (verified)</span><?php endif; ?></b><small><?= e((string) ($l['seller_city'] ?: $l['city'])) ?></small></span>
		</a>
	<?php endif; ?>
	<div class="sq lcard__media"<?= $n > 1 ? ' data-cgal' : '' ?>>
		<div class="lcard__track" data-cgal-track>
			<?php foreach ($photos as $i => $p): ?><a class="lcard__slide" href="<?= e($href) ?>"<?= $i ? ' tabindex="-1" aria-hidden="true"' : '' ?>><?= photo((string) $p, $i === 0 ? (string) $l['title'] : '') ?></a><?php endforeach; ?>
		</div>
		<span class="sq__tag"><?= e(Market::catName((string) $l['kind'])) ?></span>
		<?php if ($n > 1): ?>
			<span class="lcard__n" data-cgal-n aria-hidden="true">1/<?= $n ?></span>
			<span class="lcard__dots" aria-hidden="true"><?php for ($i = 0; $i < $n; $i++): ?><i<?= $i === 0 ? ' class="is-on"' : '' ?>></i><?php endfor; ?></span>
			<button class="lcard__go lcard__go--prev" type="button" data-cgal-go="-1" aria-label="Previous photo" hidden><?= icon('back') ?></button>
			<button class="lcard__go lcard__go--next" type="button" data-cgal-go="1" aria-label="Next photo"><?= icon('next') ?></button>
		<?php endif; ?>
	</div>
	<a class="card__cap" href="<?= e($href) ?>"><span class="card__t"><?= e($l['title']) ?></span><span class="card__s"><?= $priceL ?><?= $n > 1 ? ' · ' . $n . ' photos' : '' ?></span></a>
</article>
