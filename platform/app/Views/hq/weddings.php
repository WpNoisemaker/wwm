<?= View::partial('hq/_head', ['h' => 'Weddings', 'now' => 'weddings']) ?>
	<div class="rows">
		<?php foreach ($rows as $w): ?>
			<a class="row-item" href="<?= e(App::url('/w/' . $w['slug'])) ?>">
				<?= sq((string) $w['cover'], '', 'row-item__pic', false, initials($w)) ?>
				<span><span class="row-item__t"><?= e(Wedding::names($w)) ?></span><span class="row-item__s"><?= e($w['city'] ?: 'No city') ?> · <?= e($w['email'] ?: 'No owner') ?> · <?= e(Design::forWedding($w)['name']) ?></span></span>
				<span class="row-item__end"><span class="badge"><?= e(Site::tier((string) $w['plan'])) ?></span><span class="sub"><?= e($w['visibility']) ?></span></span>
			</a>
		<?php endforeach; ?>
	</div>
</div>
