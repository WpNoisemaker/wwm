<?= View::partial('account/_head', ['h' => 'HQ', 'sub' => 'Everything on this screen is read straight from the database.']) ?>
<section class="sec wrap">
	<div class="tally">
		<?php foreach ([
			[number_format($nums['people']), 'people'], [number_format($nums['weddings']), 'wedding pages'],
			[number_format($nums['public']), 'public'], [number_format($nums['guests']), 'guests'],
			[number_format($nums['vendors']), 'vendors'],
		] as [$n, $l]): ?>
			<span class="tally__i"><b class="dot"><?= e($n) ?></b><em><?= e($l) ?></em></span>
		<?php endforeach; ?>
		<span class="tally__i"><b class="fig"><?= money(short_money($nums['raised'])) ?></b><em>gifts received</em></span>
		<span class="tally__i"><b class="fig"><?= money(short_money($nums['owed'])) ?></b><em>payouts waiting</em></span>
	</div>

	<h3 class="form__g">Pages by plan</h3>
	<div class="rows">
		<?php foreach ($plans as $p): ?>
			<div class="row"><span class="row__ico"><?= icon('rings', 18) ?></span>
				<span class="row__t"><?= e(Site::tier((string) $p['plan'])) ?></span>
				<span class="row__v"><?= e(number_format((int) $p['n'])) ?></span></div>
		<?php endforeach; ?>
	</div>

	<?php if ($asks): ?>
		<h3 class="form__g">Payouts waiting</h3>
		<div class="rows">
			<?php foreach ($asks as $a): ?>
				<div class="row"><span class="row__ico"><?= icon('cash', 18) ?></span>
					<span class="row__t"><?= e($a['name'] ?: $a['email']) ?> <span class="sub">&middot; <?= e($a['bank']) ?> <?= e($a['account']) ?> &middot; <?= e(ago((string) $a['created_at'])) ?></span></span>
					<span class="row__v"><?= money(number_format((int) $a['amount']), (string) $a['currency']) ?></span></div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<h3 class="form__g">Newest accounts</h3>
	<div class="rows">
		<?php foreach ($recent as $u): ?>
			<div class="row"><span class="row__ico"><?= icon('user', 18) ?></span>
				<span class="row__t"><?= e($u['name'] ?: $u['email']) ?> <span class="sub">&middot; <?= e($u['email']) ?> &middot; <?= e(ago((string) $u['created_at'])) ?></span></span>
				<span class="row__v"><em class="pill"><?= e($u['role']) ?></em></span></div>
		<?php endforeach; ?>
	</div>
</section>
