<?php
/** The site footer. A landing page has to end somewhere a visitor can keep moving. */
$nav = require __DIR__ . '/nav.php';
?>
<footer class="foot">
	<div class="wrap foot__in">
		<div class="foot__brand">
			<a class="bar__brand" href="<?= e(App::url('/')) ?>">
				<span class="bar__mark">&amp;</span>
				<span class="bar__name">Wedding Websites<span class="muted"> &amp; More</span></span>
			</a>
			<p class="foot__tag">Where Nigerian and African weddings everywhere come to be seen, celebrated and ranked.</p>
			<a class="btn btn--ghost btn--sm" href="<?= e(App::url('/help')) ?>"><?= icon('talk', 16) ?> Talk to us</a>
		</div>

		<nav class="foot__col" aria-label="Weddings">
			<b>Weddings</b>
			<a href="<?= e(App::url('/weddings')) ?>">Explore weddings</a>
			<a href="<?= e(App::url('/rankings')) ?>">The Buzz Chart</a>
			<a href="<?= e(App::url('/join')) ?>">Create a wedding page</a>
			<a href="<?= e(App::url('/pricing')) ?>">Pricing</a>
		</nav>

		<nav class="foot__col" aria-label="Marketplace">
			<b>Marketplace</b>
			<a href="<?= e(App::url('/market')) ?>">Stays &amp; apartments</a>
			<a href="<?= e(App::url('/market?kind=ride')) ?>">Rides &amp; convoys</a>
			<a href="<?= e(App::url('/market?kind=honeymoon')) ?>">Honeymoons</a>
			<a href="<?= e(App::url('/vendors')) ?>">Vendors</a>
			<a href="<?= e(App::url('/sell')) ?>">Become a seller</a>
		</nav>

		<nav class="foot__col" aria-label="More">
			<b>More</b>
			<a href="<?= e(App::url('/community')) ?>">Anonymous community</a>
			<a href="<?= e(App::url('/blog')) ?>">Wedding tips</a>
			<a href="<?= e(App::url('/about')) ?>">About us</a>
			<a href="<?= e(App::url('/help')) ?>">Help</a>
		</nav>

		<?php if (!empty($footCities)): ?>
			<nav class="foot__col foot__col--wide" aria-label="Weddings by city">
				<b>Weddings in</b>
				<span class="foot__cities">
					<?php foreach ($footCities as $fc): ?>
						<a href="<?= e(App::url('/weddings?city=' . urlencode((string) $fc['city']))) ?>"><?= e($fc['city']) ?></a>
					<?php endforeach; ?>
				</span>
			</nav>
		<?php endif; ?>
	</div>

	<div class="wrap foot__bottom">
		<span>&copy; <?= e(date('Y')) ?> Wedding Websites &amp; More</span>
		<span class="foot__legal">
			<a href="<?= e(App::url('/terms')) ?>">Terms</a>
			<a href="<?= e(App::url('/privacy')) ?>">Privacy</a>
		</span>
	</div>
</footer>
