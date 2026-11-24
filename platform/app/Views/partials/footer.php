<?php /** The footer: compact on a phone, a proper site footer on desktop. */ ?>
<footer class="site-foot wrap">
	<div class="site-foot__grid">
		<div class="site-foot__brand">
			<a class="brand" href="<?= e(App::url('/')) ?>"><span class="cbtn"><span class="logo">&amp;</span></span>Wedding Websites &amp; More</a>
			<p><?= tl('site.text.wedding-pages-for-african-couples', 'We make wedding pages for African couples. Your invitation, RSVP, gifts, spraying and livestream sit on one link, and pages are ranked every week.') ?></p>
			<div class="install" data-install>
				<span class="cbtn cbtn--sm"><span class="logo" style="font-size:20px">&amp;</span></span>
				<div><b>Get the app</b><span data-install-hint>Add it to your home screen</span></div>
				<button class="pill pill--sm" type="button" data-install-go>Install</button>
			</div>
		</div>
		<div class="site-foot__cols">
			<?php foreach (Menu::footer() as [$head, $links]): ?>
				<div><h2><?= e($head) ?></h2><ul>
					<?php foreach ($links as [$label, $url]): ?><li><a href="<?= e(Menu::href($url)) ?>"><?= e($label) ?></a></li><?php endforeach; ?>
				</ul></div>
			<?php endforeach; ?>
		</div>
	</div>
	<nav class="site-foot__places" aria-label="Weddings by country">
		<h2><?= t('site.heading.weddings-in', 'Weddings in') ?></h2>
		<?php foreach (Wedding::PLACES as $slug => $pl): ?><a href="<?= e(App::url('/weddings/' . $slug)) ?>"><?= e(ucfirst(preg_replace('/^the /', '', explode(' and ', $pl[1])[0]))) ?></a><?php endforeach; ?>
	</nav>
	<div class="site-foot__base"><span>&copy; <?= date('Y') ?> Wedding Websites &amp; More</span><span><?= t('footer.made-in', 'Made in Abuja') ?></span></div>
</footer>
