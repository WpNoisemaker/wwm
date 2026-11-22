<?php /** The footer: compact on a phone, a proper site footer on desktop. */ ?>
<footer class="site-foot wrap">
	<div class="site-foot__grid">
		<div class="site-foot__brand">
			<a class="brand" href="<?= e(App::url('/')) ?>"><span class="cbtn"><span class="logo">&amp;</span></span>Wedding Websites &amp; More</a>
			<p>Wedding pages for African couples: invite, RSVP, gifts, spraying and livestream in one link, ranked every week.</p>
			<div class="install" data-install>
				<span class="cbtn cbtn--sm"><span class="logo" style="font-size:20px">&amp;</span></span>
				<div><b>Get the app</b><span data-install-hint>Add it to your home screen</span></div>
				<button class="pill pill--sm" type="button" data-install-go>Install</button>
			</div>
		</div>
		<div class="site-foot__cols">
			<div><h2>Platform</h2><ul>
				<li><a href="<?= e(App::url('/weddings')) ?>">Wedding pages</a></li>
				<li><a href="<?= e(App::url('/rankings')) ?>">Buzz Chart</a></li>
				<li><a href="<?= e(App::url('/community')) ?>">Community</a></li>
				<li><a href="<?= e(App::url('/blog')) ?>">Stories</a></li>
			</ul></div>
			<div><h2>Shop</h2><ul>
				<li><a href="<?= e(App::url('/vendors')) ?>">Vendors</a></li>
				<li><a href="<?= e(App::url('/market')) ?>">Marketplace</a></li>
				<li><a href="<?= e(App::url('/pricing')) ?>">Pricing</a></li>
			</ul></div>
			<div><h2>Samples</h2><ul>
				<li><a href="<?= e(App::url('/w/ibrahim-ummi')) ?>">Premium page</a></li>
				<li><a href="<?= e(App::url('/templates')) ?>">All designs</a></li>
				<li><a href="<?= e(App::url('/w/kola-lara')) ?>">Free page</a></li>
			</ul></div>
			<div><h2>Help</h2><ul>
				<li><a href="<?= e(App::url('/help')) ?>">Help</a></li>
				<li><a href="<?= e(App::url('/about')) ?>">About</a></li>
				<li><a href="<?= e(App::url('/terms')) ?>">Terms</a></li>
				<li><a href="<?= e(App::url('/privacy')) ?>">Privacy</a></li>
			</ul></div>
		</div>
	</div>
	<div class="site-foot__base"><span>&copy; <?= date('Y') ?> Wedding Websites &amp; More</span><span>Made in Lagos</span></div>
</footer>
