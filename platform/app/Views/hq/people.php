<?= View::partial('hq/_head', ['h' => 'People', 'now' => 'people']) ?>
	<div class="rows">
		<?php foreach ($rows as $u): ?>
			<div class="row-item"><span class="row-item__ico"><?= e(mb_strtoupper(mb_substr((string) ($u['name'] ?: $u['email']), 0, 1))) ?></span>
				<span><span class="row-item__t"><?= e($u['name'] ?: '—') ?></span><span class="row-item__s"><?= e($u['email']) ?></span></span>
				<span class="row-item__end"><span class="badge"><?= e($u['role']) ?></span><span class="sub"><?= e(ago((string) $u['created_at'])) ?></span></span></div>
		<?php endforeach; ?>
	</div>
</div>
