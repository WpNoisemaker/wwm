<?= View::partial('hq/_head', ['h' => 'HQ', 'now' => 'hq']) ?>
	<div class="kpis">
		<?php foreach ([[$nums['people'], 'People'], [$nums['weddings'], 'Wedding pages'], [$nums['public'], 'Public'], [$nums['guests'], 'Guests'], [$nums['vendors'], 'Vendors']] as [$n, $l]): ?>
			<div class="tile"><span class="k"><?= e($l) ?></span><?= dots((string) (int) $n, 'dotnum--sm kpi-dots') ?></div>
		<?php endforeach; ?>
		<?= kv('Gifts received', money(short_money((int) $nums['raised'])), '', true) ?>
		<?= kv('Payouts waiting', money(short_money((int) $nums['owed'])), (int) $nums['owed'] > 0 ? 'is-on' : '', true) ?>
		<a class="tile tile--glyph" href="<?= e(App::url('/hq/settings')) ?>"><?= icon('s-spark') ?><span>Settings</span></a>
	</div>
	<?php foreach (Pack::ALL as $pk => $pn): if (!Pack::present($pk)): ?>
		<div class="note gap-t"><?= icon('info') ?><span><?= e(Pack::ABOUT[$pk][0] ?? $pn) ?>: not here yet. <a href="<?= e(App::url('/hq/settings#packs')) ?>">Restore in Settings</a>.</span></div>
	<?php endif; endforeach; ?>
	<div class="split-2 gap-t">
		<div>
			<?php if ($asks): ?>
				<div class="block-head"><h2 class="h2">Payouts waiting</h2></div>
				<div class="rows">
					<?php foreach ($asks as $a): ?>
						<div class="row-item"><span class="row-item__ico"><?= icon('wallet') ?></span>
							<span><span class="row-item__t"><?= e($a['name'] ?: $a['email']) ?></span><span class="row-item__s"><?= e($a['bank']) ?> <?= e($a['account']) ?> · <?= e(ago((string) $a['created_at'])) ?></span></span>
							<span class="row-item__end"><?= money(number_format((int) $a['amount']), (string) $a['currency']) ?></span></div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<div class="block-head<?= $asks ? ' gap-t' : '' ?>"><h2 class="h2">Newest accounts</h2></div>
			<div class="rows">
				<?php foreach ($recent as $u): ?>
					<div class="row-item"><span class="row-item__ico"><?= icon('user') ?></span>
						<span><span class="row-item__t"><?= e($u['name'] ?: $u['email']) ?></span><span class="row-item__s"><?= e($u['email']) ?> · <?= e(ago((string) $u['created_at'])) ?></span></span>
						<span class="row-item__end"><span class="badge"><?= e($u['role']) ?></span></span></div>
				<?php endforeach; ?>
			</div>
		</div>
		<aside>
			<div class="block-head"><h2 class="h2">Pages by plan</h2></div>
			<div class="rows">
				<?php foreach ($plans as $p): ?>
					<div class="row-item row-item--plain"><span class="row-item__t"><?= e(Site::tier((string) $p['plan'])) ?></span><span class="row-item__end"><?= e(number_format((int) $p['n'])) ?></span></div>
				<?php endforeach; ?>
			</div>
		</aside>
	</div>
</div>
