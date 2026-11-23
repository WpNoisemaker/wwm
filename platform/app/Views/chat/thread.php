<?php
/**
 * One chat. The customer and the seller talk; the seller can send an invoice (lines, total and how
 * to pay them: their payment link or bank details) or just a payment link. It polls for replies.
 *
 * @var array $rows @var string $role @var array $seller @var ?array $customer @var int $pid @var int $uid @var array $listings
 */
$base = '/account/messages/' . $pid . '-' . $uid;
$canBill = in_array($role, ['partner', 'staff'], true) && (bool) array_filter($rows, static fn ($m) => (string) $m['sender'] === 'user');
$last = $rows ? (int) end($rows)['id'] : 0;
$curSelect = static function (): string {
	$o = '';
	foreach (Fx::NAMES as $k => $n) { $o .= '<option value="' . e($k) . '">' . e(trim(cur_sign($k)) . ' ' . $n) . '</option>'; }
	return $o;
};
?>
<?= View::partial('account/_head', ['h' => $role === 'user' ? (string) $seller['name'] : (string) ($customer['name'] ?? 'Customer'), 'sub' => $role === 'user' ? 'Your chat with the seller' : 'Your chat with a customer', 'now' => 'messages']) ?>
	<div class="chat">
		<div class="chat__top">
			<?php if ($role === 'user'): ?>
				<a class="linkish" href="<?= e(App::url('/vendors/' . $seller['slug'])) ?>"><?= icon('store') ?>See <?= e((string) $seller['name']) ?>’s profile and reviews</a>
			<?php else: ?>
				<span class="help"><?= icon('user') ?><?= e((string) ($customer['name'] ?? 'Customer')) ?><?= $role === 'staff' ? ' · you are answering as Our team' : '' ?></span>
			<?php endif; ?>
		</div>
		<div class="chat__log" data-chat="<?= e(App::url($base)) ?>" data-last="<?= $last ?>" aria-live="polite">
			<?= ChatController::bubbles($rows, $role) ?>
			<span id="end"></span>
		</div>
		<form class="chat__say" method="post" action="<?= e(App::url($base)) ?>" data-chat-form>
			<?= Guard::field() ?><input type="hidden" name="after" value="<?= $last ?>">
			<label class="sr" for="say">Message</label>
			<textarea class="input" id="say" name="body" rows="1" maxlength="4000" required placeholder="Write a message"></textarea>
			<button class="cbtn chat__go" type="submit" aria-label="Send"><?= icon('arrow') ?></button>
		</form>
		<?php if ($role === 'user'): ?><?= View::partial('partials/pay-warning') ?><?php endif; ?>

		<?php if ($canBill): ?>
		<div class="bill">
			<details class="bill__box glass glass--plain glass--tight">
				<summary><?= icon('wallet') ?>Send an invoice</summary>
				<form method="post" action="<?= e(App::url($base . '/invoice')) ?>" class="form">
					<?= Guard::field() ?><input type="hidden" name="kind" value="invoice">
					<div class="form-grid form-grid--2">
						<label class="field span-2"><span>What it is for</span><input class="input" name="title" maxlength="190" placeholder="Bridal car for 14 December"></label>
						<?php if ($listings): ?><label class="field span-2"><span>The listing (optional)</span><select class="input" name="listing_id"><option value="">None</option><?php foreach ($listings as $l): ?><option value="<?= (int) $l['id'] ?>"><?= e((string) $l['title']) ?></option><?php endforeach; ?></select></label><?php endif; ?>
					</div>
					<div class="bill__lines">
						<?php for ($i = 0; $i < 4; $i++): ?>
							<div class="bill__line"><input class="input" name="what[]" maxlength="160" placeholder="<?= $i === 0 ? 'Item or service' : 'Another line (optional)' ?>" aria-label="Line <?= $i + 1 ?>"><input class="input" name="amt[]" inputmode="numeric" placeholder="Amount" aria-label="Amount for line <?= $i + 1 ?>"></div>
						<?php endfor; ?>
					</div>
					<div class="form-grid form-grid--2">
						<label class="field"><span>Currency</span><select class="input" name="currency"><?= $curSelect() ?></select></label>
						<label class="field"><span>Pay by (optional)</span><input class="input" type="date" name="due_on"></label>
						<label class="field span-2"><span>Your payment link</span><input class="input" name="pay_link" inputmode="url" placeholder="https://paystack.com/pay/your-page"></label>
						<label class="field span-2"><span>Or how to pay you</span><textarea class="input" name="pay_info" rows="2" maxlength="1000" placeholder="Bank, account name and number"></textarea></label>
						<label class="field span-2"><span>A note (optional)</span><input class="input" name="note" maxlength="1000"></label>
					</div>
					<button class="pill pill--block" type="submit">Send the invoice</button>
				</form>
			</details>
			<details class="bill__box glass glass--plain glass--tight">
				<summary><?= icon('link') ?>Send a payment link</summary>
				<form method="post" action="<?= e(App::url($base . '/invoice')) ?>" class="form">
					<?= Guard::field() ?><input type="hidden" name="kind" value="link">
					<div class="form-grid form-grid--2">
						<label class="field span-2"><span>What it is for</span><input class="input" name="title" maxlength="190" placeholder="Deposit for the apartment"></label>
						<label class="field"><span>Amount</span><input class="input" name="amount" inputmode="numeric" required></label>
						<label class="field"><span>Currency</span><select class="input" name="currency"><?= $curSelect() ?></select></label>
						<label class="field span-2"><span>Your payment link</span><input class="input" name="pay_link" inputmode="url" required placeholder="https://flutterwave.com/pay/your-link"></label>
						<label class="field span-2"><span>A note (optional)</span><input class="input" name="note" maxlength="1000"></label>
					</div>
					<button class="pill pill--block" type="submit">Send the link</button>
				</form>
			</details>
			<p class="help">Use your own payment page (Paystack, Flutterwave or your bank). The customer pays you directly; we never hold the money.</p>
		</div>
		<?php endif; ?>
	</div>
</div>
