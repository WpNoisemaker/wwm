<?= View::partial('account/_head', ['h' => 'People']) ?>
<section class="sec wrap">
	<div class="rows">
		<?php foreach ($rows as $u): ?>
			<div class="row"><span class="row__ico"><?= icon('user', 18) ?></span>
				<span class="row__t"><?= e($u['name'] ?: '—') ?> <span class="sub">&middot; <?= e($u['email']) ?></span></span>
				<span class="row__v"><em class="pill"><?= e($u['role']) ?></em> <?= e(ago((string) $u['created_at'])) ?></span></div>
		<?php endforeach; ?>
	</div>
</section>
