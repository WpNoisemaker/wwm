<header class="bar" data-bar>
	<?= View::partial('partials/brand') ?>
	<span class="bar__sp"></span>
	<a class="ico" href="<?= e(App::url('/search')) ?>" aria-label="Search"><?= icon('search', 20) ?></a>
	<a class="ico" href="<?= e(App::url('/account')) ?>" aria-label="Your account"><?= icon('user', 20) ?></a>
</header>
