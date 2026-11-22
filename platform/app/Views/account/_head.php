<?php /** Shared top of every account screen: flashes and a title. */ ?>
<section class="sec wrap acct__top">
	<?php foreach (Guard::takeFlash() as $f): ?><p class="note note--<?= e($f['kind']) ?>"><?= e($f['msg']) ?></p><?php endforeach; ?>
	<div class="sec__head acct__head">
		<div><h2><?= e($h) ?></h2><?php if (!empty($sub)): ?><p class="sub"><?= e($sub) ?></p><?php endif; ?></div>
		<?php if (!empty($w)): ?><a class="pill pill--quiet" href="<?= e(App::url('/w/' . $w['slug'])) ?>">View the page <?= icon('arrow', 13) ?></a><?php endif; ?>
	</div>
</section>
