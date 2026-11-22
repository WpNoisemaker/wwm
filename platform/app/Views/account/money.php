<?= View::partial('account/_head', ['h' => 'Money', 'w' => $w]) ?>
<section class="sec wrap">
	<?php $bal = $in - $out; ?>
	<div class="tally">
		<span class="tally__i"><b class="fig"><?= money(short_money($in), (string) $w['currency']) ?></b><em>gifts received</em></span>
		<span class="tally__i"><b class="fig"><?= money(short_money($out), (string) $w['currency']) ?></b><em>paid out</em></span>
		<span class="tally__i"><b class="fig"><?= money(short_money(max(0, $bal)), (string) $w['currency']) ?></b><em>available</em></span>
	</div>
	<p class="sub"><?= $fee > 0
		? 'Your plan carries a ' . e(rtrim(rtrim(number_format($fee, 1), '0'), '.')) . '% service fee on cash gifts, which guests can choose to cover. Premium carries none.'
		: 'Your plan carries no service fee on gifts or spraying.' ?></p>

	<h3 class="form__g">Ask for a payout</h3>
	<form method="post" action="<?= e(App::url('/account/payout')) ?>" class="form form--card">
		<?= Guard::field() ?>
		<div class="form__pair">
			<span><label for="amount">Amount</label><input id="amount" name="amount" type="number" min="1" max="<?= e(max(0, $bal)) ?>" required></span>
			<span><label for="bank">Bank</label><input id="bank" name="bank" required placeholder="GTBank"></span>
		</div>
		<div class="form__pair">
			<span><label for="acct">Account number</label><input id="acct" name="acct" required inputmode="numeric"></span>
			<span><label for="acct_name">Account name</label><input id="acct_name" name="acct_name" required></span>
		</div>
		<button class="btn btn--acid" type="submit"<?= $bal <= 0 ? ' disabled' : '' ?>>Request it</button>
		<?php if ($bal <= 0): ?><small class="gate__hint">Nothing available to pay out yet.</small><?php endif; ?>
	</form>

	<h3 class="form__g">Gifts in</h3>
	<?php if (!$payments): ?><p class="sub">Nothing yet.</p><?php else: ?>
		<div class="rows">
			<?php foreach ($payments as $p): ?>
				<div class="row">
					<span class="row__ico"><?= icon('gift', 18) ?></span>
					<span class="row__t"><?= e($p['payer_name'] ?: 'A guest') ?> <span class="sub">&middot; <?= e(ago((string) $p['created_at'])) ?><?= $p['message'] ? ' · “' . e(mb_strimwidth((string) $p['message'], 0, 48, '…')) . '”' : '' ?></span></span>
					<span class="row__v"><?= money(number_format((int) $p['amount']), (string) $p['currency']) ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<h3 class="form__g">Payouts</h3>
	<?php if (!$payouts): ?><p class="sub">None requested.</p><?php else: ?>
		<div class="rows">
			<?php foreach ($payouts as $p): ?>
				<div class="row">
					<span class="row__ico"><?= icon('cash', 18) ?></span>
					<span class="row__t"><?= e($p['bank']) ?> <span class="sub">&middot; <?= e(ago((string) $p['created_at'])) ?></span></span>
					<span class="row__v"><em class="pill"><?= e(ucfirst((string) $p['status'])) ?></em> <?= money(number_format((int) $p['amount']), (string) $p['currency']) ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
