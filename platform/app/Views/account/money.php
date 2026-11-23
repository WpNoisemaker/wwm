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
			<?php if ((string) $w['va_number'] !== '' || Bachs::on()): $settled = Settle::forWedding((int) $w['id'], 8); ?>
			<div class="glass glass--plain glass--tight bank-card" id="settle">
				<h2 class="h3">Where your money goes</h2>
				<?php if ((string) $w['payout_dest'] !== ''): ?>
					<p class="bank-card__to"><?= icon('check') ?><span><b><?= e($w['payout_bank_name']) ?> ••<?= e(substr((string) $w['payout_account'], -4)) ?></b> · <?= e($w['payout_name']) ?></span></p>
					<p class="help">Every gift and spray is paid on to this account the moment it arrives<?= in_array((string) $w['plan'], ['premium', 'society'], true) ? ', in full' : ', less our ' . e(Site::setting('fee_gift', '4')) . '% fee' ?>.</p>
				<?php else: ?>
					<p class="help">Add your own bank account. Every gift and spray is then paid on to it the moment it arrives<?= in_array((string) $w['plan'], ['premium', 'society'], true) ? ', in full' : '' ?>.</p>
				<?php endif; ?>
				<?php if (!Managed::managing()): $banks = Bachs::banks(); ?>
				<details class="bank-card__edit"<?= (string) $w['payout_dest'] === '' ? ' open' : '' ?>><summary><?= (string) $w['payout_dest'] !== '' ? 'Change the account' : 'Add your bank account' ?></summary>
					<form method="post" action="<?= e(App::url('/account/bank/save')) ?>" class="form" data-bank-form data-check="<?= e(App::url('/account/bank/check')) ?>">
						<?= Guard::field() ?>
						<label class="field"><span>Bank</span><select class="input" name="bank_code" required><option value="">Choose your bank</option><?php foreach ($banks as $b): ?><option value="<?= e($b['code']) ?>"<?= (string) $w['payout_bank_code'] === $b['code'] ? ' selected' : '' ?>><?= e($b['name']) ?></option><?php endforeach; ?></select></label>
						<label class="field"><span>Account number</span><input class="input" name="account_number" inputmode="numeric" pattern="\d{10}" maxlength="10" required value="<?= e((string) $w['payout_account']) ?>"></label>
						<p class="bank-card__name" data-bank-name aria-live="polite"></p>
						<button class="pill pill--block" type="submit">Save my account</button>
					</form>
				</details>
				<?php endif; ?>
				<?php if ($settled): ?>
					<div class="rows gap-t-sm">
						<?php foreach ($settled as $st): $done = $st['status'] === 'paid' || $st['status'] === 'sent'; ?>
							<div class="row-item"><span class="row-item__ico"><?= icon($st['status'] === 'failed' ? 'info' : ($done ? 'check' : 'clock')) ?></span>
								<span><span class="row-item__t"><?= money(number_format(intdiv((int) $st['net_kobo'], 100))) ?> <?= $st['status'] === 'paid' ? 'paid to your bank' : ($st['status'] === 'sent' ? 'on its way to your bank' : ($st['status'] === 'failed' ? 'could not be paid out yet' : 'waiting to go out')) ?></span>
								<span class="row-item__s"><?= e((string) ($st['payer_name'] ?: 'A guest')) ?> · <?= $st['purpose'] === 'spray' ? 'spray' : 'gift' ?><?= (int) $st['fee_kobo'] ? ' · fee ' . money(number_format(intdiv((int) $st['fee_kobo'], 100))) : '' ?> · <?= e(ago((string) $st['created_at'])) ?></span></span></div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
			<?php endif; ?>
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
			<?php if (Bachs::on()): ?>
			<div class="form glass glass--plain glass--tight">
				<h2 class="h3">Payouts</h2>
				<p class="help">You do not need to ask. Every gift and spray is paid to your own bank account the moment it arrives. Add or change the account under <a class="linkish" href="#settle">Where your money goes</a>.</p>
			</div>
			<?php else: ?>
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
			<?php endif; ?>
		</aside>
	</div>
</div>
