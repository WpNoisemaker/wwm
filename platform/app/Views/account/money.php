<?php $bal = $in - $out; ?>
<?= View::partial('account/_head', ['h' => 'Money', 'w' => $w, 'sub' => 'Gifts, spraying and payouts', 'now' => 'money']) ?>
	<div class="kpis kpis--3">
		<?= kv('Gifts received', money(short_money($in), (string) $w['currency']), '', true) ?>
		<?= kv('Paid out', money(short_money($out), (string) $w['currency']), '', true) ?>
		<?= kv('Available', money(short_money(max(0, $bal)), (string) $w['currency']), 'is-on', true) ?>
	</div>
	<p class="sub gap-t-sm"><?= $fee > 0 ? 'Your plan carries a ' . e(rtrim(rtrim(number_format($fee, 1), '0'), '.')) . '% service fee on cash gifts, which guests can choose to cover. Premium carries none.' : 'Your plan carries no service fee on gifts or spraying.' ?></p>
	<div class="split-2 gap-t">
		<div>
			<div class="glass glass--plain glass--tight bank-card" id="bank">
				<h2 class="h3">Your account for gifts and spraying</h2>
				<?php if ((string) $w['va_number'] !== ''): ?>
					<p class="bank-card__n" data-copy="<?= e($w['va_number']) ?>"><?= e($w['va_number']) ?> <?= icon('copy') ?></p>
					<p class="help"><?= e($w['va_bank']) ?> · <?= e($w['va_name']) ?></p>
					<p class="help">Guests send gifts and spray by transfer to this account. Each one shows on your page as soon as it arrives.</p>
				<?php elseif (Bachs::on()): ?>
					<p class="help">Get your own account number. Guests transfer to it, and every gift and spray shows on your page as soon as it lands.</p>
					<form method="post" action="<?= e(App::url('/account/bank')) ?>"><?= Guard::field() ?><button class="pill" type="submit">Get my account number</button></form>
					<?php if ((string) $w['va_error'] !== ''): ?><p class="help">Last try: <?= e($w['va_error']) ?></p><?php endif; ?>
				<?php else: ?>
					<p class="help">Bank transfers switch on soon. We will give you your own account number for gifts and spraying.</p>
				<?php endif; ?>
			</div>
			<div class="block-head gap-t"><h2 class="h2">Gifts in</h2></div>
			<?php if (!$payments): ?><p class="sub">Nothing yet.</p><?php else: ?>
				<div class="rows">
					<?php foreach ($payments as $p): ?>
						<div class="row-item">
							<span class="row-item__ico"><?= icon($p['purpose'] === 'spray' ? 's-wallet' : 's-gift') ?></span>
							<span><span class="row-item__t"><?= e($p['payer_name'] ?: 'A guest') ?></span><span class="row-item__s"><?= e(ago((string) $p['created_at'])) ?><?= $p['message'] ? ' · “' . e(mb_strimwidth((string) $p['message'], 0, 48, '…')) . '”' : '' ?></span></span>
							<span class="row-item__end"><?= money(number_format((int) $p['amount']), (string) $p['currency']) ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<div class="block-head gap-t"><h2 class="h2">Payouts</h2></div>
			<?php if (!$payouts): ?><p class="sub">None requested.</p><?php else: ?>
				<div class="rows">
					<?php foreach ($payouts as $p): ?>
						<div class="row-item">
							<span class="row-item__ico"><?= icon('wallet') ?></span>
							<span><span class="row-item__t"><?= e($p['bank']) ?></span><span class="row-item__s"><?= e(ago((string) $p['created_at'])) ?></span></span>
							<span class="row-item__end"><?= money(number_format((int) $p['amount']), (string) $p['currency']) ?><span class="sub"><?= e(ucfirst((string) $p['status'])) ?></span></span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<aside>
			<form method="post" action="<?= e(App::url('/account/payout')) ?>" class="form glass glass--tight">
				<?= Guard::field() ?>
				<h2 class="h3">Ask for a payout</h2>
				<label class="field"><span>Amount</span><input class="input" name="amount" type="number" min="1" max="<?= max(0, $bal) ?>" inputmode="numeric" required></label>
				<label class="field"><span>Bank</span><input class="input" name="bank" required placeholder="GTBank"></label>
				<label class="field"><span>Account number</span><input class="input" name="acct" required inputmode="numeric"></label>
				<label class="field"><span>Account name</span><input class="input" name="acct_name" required></label>
				<button class="pill pill--block" type="submit"<?= $bal <= 0 ? ' disabled' : '' ?>>Request it</button>
				<?php if ($bal <= 0): ?><p class="after-pill">Nothing available to pay out yet.</p><?php endif; ?>
			</form>
		</aside>
	</div>
</div>
