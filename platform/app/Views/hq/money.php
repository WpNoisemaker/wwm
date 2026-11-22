<?= View::partial('account/_head', ['h' => 'Money']) ?>
<section class="sec wrap">
	<div class="tally">
		<span class="tally__i"><b class="fig"><?= money(short_money($in)) ?></b><em>through the site</em></span>
		<span class="tally__i"><b class="fig"><?= money(short_money($fees)) ?></b><em>service fees</em></span>
	</div>
	<h3 class="form__g">Payments</h3>
	<div class="rows">
		<?php foreach ($payments as $p): ?>
			<div class="row"><span class="row__ico"><?= icon('gift', 18) ?></span>
				<span class="row__t"><?= e($p['payer_name'] ?: 'A guest') ?> <span class="sub">&middot; <?= e($p['purpose']) ?> &middot; <?= e(ago((string) $p['created_at'])) ?></span></span>
				<span class="row__v"><em class="pill"><?= e($p['status']) ?></em> <?= money(number_format((int) $p['amount']), (string) $p['currency']) ?></span></div>
		<?php endforeach; ?>
	</div>
	<h3 class="form__g">Payouts</h3>
	<div class="rows">
		<?php foreach ($payouts as $p): ?>
			<div class="row"><span class="row__ico"><?= icon('cash', 18) ?></span>
				<span class="row__t"><?= e($p['name']) ?> <span class="sub">&middot; <?= e($p['bank']) ?> &middot; <?= e(ago((string) $p['created_at'])) ?></span></span>
				<span class="row__v"><em class="pill"><?= e($p['status']) ?></em> <?= money(number_format((int) $p['amount']), (string) $p['currency']) ?></span></div>
		<?php endforeach; ?>
	</div>
</section>
