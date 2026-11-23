<?php /** The footer: compact on a phone, a proper site footer on desktop. */ ?>
<footer class="site-foot wrap">
	<div class="site-foot__grid">
		<div class="site-foot__brand">
			<a class="brand" href="<?= e(App::url('/')) ?>"><span class="cbtn"><span class="logo">&amp;</span></span>Wedding Websites &amp; More</a>
			<p><?= tl('site.text.wedding-pages-for-african-couples', 'Wedding pages for African couples: invite, RSVP, gifts, spraying and livestream in one link, ranked every week.') ?></p>
			<div class="install" data-install>
				<span class="cbtn cbtn--sm"><span class="logo" style="font-size:20px">&amp;</span></span>
				<div><b>Get the app</b><span data-install-hint>Add it to your home screen</span></div>
				<button class="pill pill--sm" type="button" data-install-go>Install</button>
			</div>
		</div>
		<div class="site-foot__cols">
			<div><h2><?= t('site.heading.platform', 'Platform') ?></h2><ul>
				<li><a href="<?= e(App::url('/weddings')) ?>">Wedding pages</a></li>
				<li><a href="<?= e(App::url('/rankings')) ?>">Buzz Chart</a></li>
				<li><a href="<?= e(App::url('/community')) ?>">Community</a></li>
				<li><a href="<?= e(App::url('/blog')) ?>">Blog</a></li>
			</ul></div>
			<div><h2><?= t('site.heading.shop', 'Shop') ?></h2><ul>
				<li><a href="<?= e(App::url('/vendors')) ?>">Vendors</a></li>
				<li><a href="<?= e(App::url('/market')) ?>">Marketplace</a></li>
				<li><a href="<?= e(App::url('/pricing')) ?>">Pricing</a></li>
			</ul></div>
			<div><h2><?= t('site.heading.samples', 'Samples') ?></h2><ul>
				<li><a href="<?= e(App::url('/w/ibrahim-ummi')) ?>">Premium page</a></li>
				<li><a href="<?= e(App::url('/templates')) ?>">All designs</a></li>
				<li><a href="<?= e(App::url('/w/kola-lara')) ?>">Free page</a></li>
			</ul></div>
			<div><h2><?= t('site.heading.help', 'Help') ?></h2><ul>
				<li><a href="<?= e(App::url('/help')) ?>">Help</a></li>
				<li><a href="<?= e(App::url('/about')) ?>">About</a></li>
				<li><a href="<?= e(App::url('/terms')) ?>">Terms &amp; conditions</a></li>
				<li><a href="<?= e(App::url('/privacy')) ?>">Privacy policy</a></li>
				<li><a href="<?= e(App::url('/refunds')) ?>">Refund policy</a></li>
				<li><a href="<?= e(App::url('/community-guidelines')) ?>">Community guidelines</a></li>
				<li><a href="<?= e(App::url('/cookies')) ?>">Cookies</a></li>
			</ul></div>
		</div>
	</div>
	<nav class="site-foot__places" aria-label="Weddings by country">
		<h2><?= t('site.heading.weddings-in', 'Weddings in') ?></h2>
		<?php foreach (Wedding::PLACES as $slug => $pl): ?><a href="<?= e(App::url('/weddings/' . $slug)) ?>"><?= e(ucfirst(preg_replace('/^the /', '', explode(' and ', $pl[1])[0]))) ?></a><?php endforeach; ?>
	</nav>
	<div class="site-foot__base"><span>&copy; <?= date('Y') ?> Wedding Websites &amp; More</span><span><?= t('footer.made-in', 'Made in Abuja') ?></span></div>
</footer>
