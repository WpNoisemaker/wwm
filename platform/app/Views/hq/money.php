<?= View::partial('hq/_head', ['h' => 'Money', 'now' => 'money']) ?>
	<div class="kpis kpis--3">
		<?= kv('Through the site', money(short_money((int) $in)), '', true) ?>
		<?= kv('Service fees', money(short_money((int) $fees)), '', true) ?>
		<?= kv('Payouts', (string) count($payouts)) ?>
	</div>
	<div class="split-2 gap-t">
		<div>
			<div class="block-head"><h2 class="h2">Payments</h2></div>
			<div class="rows">
				<?php foreach ($payments as $p): ?>
					<div class="row-item"><span class="row-item__ico"><?= icon($p['purpose'] === 'spray' ? 's-wallet' : 's-gift') ?></span>
						<span><span class="row-item__t"><?= e($p['payer_name'] ?: 'A guest') ?></span><span class="row-item__s"><?= e($p['purpose']) ?> · <?= e(ago((string) $p['created_at'])) ?></span></span>
						<span class="row-item__end"><?= money(number_format((int) $p['amount']), (string) $p['currency']) ?><span class="sub"><?= e($p['status']) ?></span></span></div>
				<?php endforeach; ?>
			</div>
		</div>
		<aside>
			<div class="block-head"><h2 class="h2">Payouts</h2></div>
			<div class="rows">
				<?php foreach ($payouts as $p): ?>
					<div class="row-item"><span class="row-item__ico"><?= icon('wallet') ?></span>
						<span><span class="row-item__t"><?= e($p['name']) ?></span><span class="row-item__s"><?= e($p['bank']) ?> · <?= e(ago((string) $p['created_at'])) ?></span></span>
						<span class="row-item__end"><?= money(number_format((int) $p['amount']), (string) $p['currency']) ?><span class="sub"><?= e($p['status']) ?></span></span></div>
				<?php endforeach; ?>
			</div>
		</aside>
	</div>
</div>
