<section class="sec wrap">
	<div class="lshow">
		<span class="lshow__art blob blob--orange"><?= photo(first_photo((string) $l['photos']), '', true) ?></span>
		<div class="lshow__body">
			<p class="cap"><?= e(Listing::kindName((string) $l['kind'])) ?> &middot; <?= e($l['city']) ?></p>
			<h1 class="lshow__h"><?= e($l['title']) ?></h1>
			<p class="lshow__p"><?= $l['on_ask'] ? 'Price on request' : money(number_format((int) $l['price'])) ?> <small><?= e($l['price_unit']) ?></small></p>
			<?php if (trim((string) $l['about']) !== ''): ?><div class="wed__prose"><?= nl2br(e((string) $l['about'])) ?></div><?php endif; ?>
			<div class="book">
				<b>Booking is protected</b>
				<p class="sub">Chat with the seller, agree the dates, then pay on the site. Your money is held until you confirm the booking has happened.</p>
				<a class="btn btn--acid btn--block" href="<?= e(App::url(Auth::check() ? '/account' : '/sign-in?to=' . urlencode('/market/' . $l['slug']))) ?>">
					<?= Auth::check() ? 'Message the seller' : 'Sign in to book' ?>
				</a>
			</div>
		</div>
	</div>
</section>

<?php if (count($more) > 1): ?>
<section class="sec wrap">
	<div class="sec__head"><div><h2>More <?= e(strtolower(Listing::kindName((string) $l['kind']))) ?>s</h2></div></div>
	<div class="rail">
		<?php foreach ($more as $i => $m): if ($m['id'] === $l['id']) { continue; } ?>
			<a class="mcard" href="<?= e(App::url('/market/' . $m['slug'])) ?>">
				<span class="mcard__art blob blob--<?= e(['green','blue','pink','orange'][$i % 4]) ?>"><?= photo(first_photo((string) $m['photos']), '') ?></span>
				<span class="mcard__k"><?= e($m['city']) ?></span>
				<b class="mcard__t"><?= e($m['title']) ?></b>
				<span class="mcard__p"><?= $m['on_ask'] ? 'On request' : money(short_money((int) $m['price'])) ?> <small><?= e($m['price_unit']) ?></small></span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>
