<?= View::partial('account/_head', ['h' => 'Weddings']) ?>
<section class="sec wrap">
	<div class="rows">
		<?php foreach ($rows as $w): ?>
			<a class="row" href="<?= e(App::url('/w/' . $w['slug'])) ?>">
				<span class="row__ico"><?= icon('rings', 18) ?></span>
				<span class="row__t"><?= e(Wedding::names($w)) ?> <span class="sub">&middot; <?= e($w['city'] ?: 'no city') ?> &middot; <?= e($w['email'] ?: 'no owner') ?></span></span>
				<span class="row__v"><em class="pill"><?= e(Site::tier((string) $w['plan'])) ?></em> <?= e($w['visibility']) ?></span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
