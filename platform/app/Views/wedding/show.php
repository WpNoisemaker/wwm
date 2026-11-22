<?php
/**
 * The page a couple shares. One template, four plans. What the plan buys is not a nag bar at the
 * bottom — it is the cover, the colours, the type scale and which sections exist at all.
 */
$grand  = Wedding::has($w, 'grand');
$gold   = Wedding::has($w, 'gold');
$names  = Wedding::names($w);
?>
<article class="wed<?= $grand ? ' wed--grand' : '' ?><?= $gold ? ' wed--gold' : '' ?>"
         style="--p:<?= e($pal['p']) ?>;--a:<?= e($pal['a']) ?>">

	<?php foreach (Guard::takeFlash() as $fl): ?>
		<div class="wrap"><p class="note note--<?= e($fl['kind']) ?>"><?= e($fl['msg']) ?></p></div>
	<?php endforeach; ?>

	<?= View::partial('wedding/parts/cover', compact('w', 'pal', 'names', 'next', 'left', 'gold', 'grand')) ?>

	<?php if ($mine): ?>
		<div class="wrap wed__owner">
			<p><?= icon('lock', 15) ?> You are looking at your own page<?= (string) $w['visibility'] !== 'public' ? ', and it is still private' : '' ?>.
				<a href="<?= e(App::url('/account/page')) ?>">Edit it</a></p>
		</div>
	<?php endif; ?>

	<?= View::partial('wedding/parts/story',    compact('w', 'tally')) ?>
	<?= View::partial('wedding/parts/programs', compact('w', 'programs')) ?>
	<?= View::partial('wedding/parts/rsvp',     compact('w')) ?>
	<?php if ($squad): ?><?= View::partial('wedding/parts/squad', compact('squad')) ?><?php endif; ?>
	<?php if ($gifts): ?><?= View::partial('wedding/parts/gifts', compact('w', 'gifts', 'tally')) ?><?php endif; ?>
	<?php if (Wedding::has($w, 'live')): ?><?= View::partial('wedding/parts/live', compact('w')) ?><?php endif; ?>
	<?php if ($photos): ?><?= View::partial('wedding/parts/photos', compact('photos')) ?><?php endif; ?>
	<?= View::partial('wedding/parts/wall', compact('w', 'notes')) ?>

	<?php if ($sponsors): ?>
		<section class="sec wrap wed__ads">
			<p class="cap">Sponsored</p>
			<div class="rows">
				<?php foreach ($sponsors as $s): ?>
					<a class="row" href="<?= e(App::url('/vendors/' . $s['slug'])) ?>">
						<span class="row__ico"><?= photo((string) $s['logo'], '') ?: icon('store', 20) ?></span>
						<span class="row__t"><?= e($s['name']) ?> <span class="sub">&middot; <?= e($s['category']) ?>, <?= e($s['city']) ?></span></span>
						<span class="row__v"><?= icon('arrow', 14) ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ($locked): ?>
		<section class="sec wrap">
			<div class="uplift">
				<p class="cap">Only you can see this</p>
				<h2 class="uplift__h">There is more your page could do.</h2>
				<div class="uplift__grid">
					<?php foreach ($locked as $plan => $items): ?>
						<div class="uplift__col">
							<b><?= e(Site::tier((string) $plan)) ?></b>
							<ul><?php foreach ($items as $i): ?><li><?= icon('check', 13) ?><?= e($i) ?></li><?php endforeach; ?></ul>
						</div>
					<?php endforeach; ?>
				</div>
				<a class="btn btn--acid" href="<?= e(App::url('/pricing')) ?>">See what each plan adds</a>
			</div>
		</section>
	<?php endif; ?>

	<footer class="wed__foot wrap">
		<p><?= e($names) ?> &middot; <?= e(Wedding::when($w)) ?></p>
		<p class="sub">Made on <a href="<?= e(App::url('/')) ?>">Wedding Websites &amp; More</a>.
			<a href="<?= e(App::url('/join')) ?>">Make yours free</a>.</p>
	</footer>
</article>
