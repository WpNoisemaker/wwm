<?php
/**
 * An invoice or payment link the seller sent; once the seller marks it paid, the receipt. The pay
 * button goes to the seller's own payment page: we show where it goes, so nobody is surprised.
 *
 * @var array $inv @var string $role @var ?array $seller @var ?array $customer @var ?array $listing
 */
$lines = Market::lines($inv);
$paid = (string) $inv['status'] === 'paid';
$off = (string) $inv['status'] === 'cancelled';
$cur = (string) $inv['currency'];
$host = (string) parse_url((string) $inv['pay_link'], PHP_URL_HOST);
$label = $paid ? 'Receipt' : ((string) $inv['kind'] === 'link' ? 'Payment link' : 'Invoice');
// A Premium seller's invoices wear their own colours, font and logo.
$brand = Market::brand($seller);
?>
<?php if ($brand && $brand['gf'] !== ''): ?><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=<?= e($brand['gf']) ?>&display=swap"><?php endif; ?>
<div class="page wrap narrow">
	<?= flashes() ?>
	<article class="invoice glass<?= $brand ? ' invoice--brand' : '' ?>"<?= $brand ? ' style="--ip: ' . e($brand['primary']) . '; --ia: ' . e($brand['accent']) . '; --io: ' . e($brand['on']) . '; --if: ' . e($brand['css']) . '"' : '' ?>>
		<?php if ($brand): ?><div class="invoice__band"><?= $brand['logo'] !== '' ? sq($brand['logo'], '', 'invoice__logo') : '' ?><b><?= e((string) $seller['name']) ?></b></div><?php endif; ?>
		<header class="invoice__head">
			<div>
				<span class="eyebrow"><?= e($label) ?> · #<?= e((string) $inv['code']) ?></span>
				<h1 class="h2"><?= e((string) $inv['title']) ?></h1>
				<p class="help"><?= e(date('j F Y', (int) strtotime((string) $inv['created_at']))) ?><?= $inv['due_on'] && !$paid ? ' · pay by ' . e(date('j F Y', (int) strtotime((string) $inv['due_on']))) : '' ?><?= $paid && $inv['paid_at'] ? ' · paid ' . e(date('j F Y', (int) strtotime((string) $inv['paid_at']))) : '' ?></p>
			</div>
			<span class="invoice__state<?= $paid ? ' is-paid' : ($off ? ' is-off' : '') ?>"><?= $paid ? 'Paid' : ($off ? 'Cancelled' : 'Open') ?></span>
		</header>
		<div class="invoice__who">
			<div><span class="k">From</span><b><?= e((string) ($seller['name'] ?? 'Seller')) ?></b><?php if ($seller && (int) $seller['verified']): ?> <span class="badge"><?= icon('check') ?>Verified</span><?php endif; ?></div>
			<div><span class="k">To</span><b><?= e((string) ($customer['name'] ?? 'Customer')) ?></b></div>
		</div>
		<?php if ($listing): ?><p class="help">For <a class="linkish" href="<?= e(App::url('/market/' . $listing['slug'])) ?>"><?= e((string) $listing['title']) ?></a></p><?php endif; ?>
		<table class="invoice__lines">
			<?php if ($lines): foreach ($lines as [$what, $amt]): ?>
				<tr><td><?= e($what) ?></td><td><?= money(number_format($amt), $cur) ?></td></tr>
			<?php endforeach; endif; ?>
			<tr class="invoice__total"><td>Total</td><td><?= money(number_format((int) $inv['amount']), $cur) ?></td></tr>
		</table>
		<?php if (trim((string) $inv['note']) !== ''): ?><p class="invoice__note"><?= nl2br(e((string) $inv['note'])) ?></p><?php endif; ?>

		<?php if (!$paid && !$off): ?>
			<?php if ((string) $inv['pay_link'] !== ''): ?>
				<a class="pill pill--block" href="<?= e((string) $inv['pay_link']) ?>" target="_blank" rel="noopener nofollow">Pay on <?= e($host) ?><?= icon('arrow') ?></a>
				<p class="help center">This opens the seller’s own payment page on <b><?= e($host) ?></b>.</p>
			<?php endif; ?>
			<?php if (trim((string) $inv['pay_info']) !== ''): ?>
				<div class="invoice__how"><span class="k">How to pay <?= e((string) ($seller['name'] ?? 'the seller')) ?></span><p><?= nl2br(e((string) $inv['pay_info'])) ?></p></div>
			<?php endif; ?>
		<?php endif; ?>
		<?= View::partial('partials/pay-warning') ?>

		<?php if (in_array($role, ['partner', 'staff'], true) && !$paid && !$off): ?>
			<form method="post" class="form-actions invoice__acts">
				<?= Guard::field() ?>
				<button class="pill pill--sm" type="submit" name="do" value="paid">I have been paid</button>
				<button class="pill pill--sm pill--glass" type="submit" name="do" value="cancel" data-confirm="Cancel this <?= e(strtolower($label)) ?>?">Cancel it</button>
			</form>
		<?php endif; ?>
		<div class="form-actions">
			<a class="pill pill--sm pill--glass" href="<?= e(App::url('/account/messages/' . $inv['partner_id'] . '-' . $inv['user_id'])) ?>"><?= icon('chat') ?>Back to the chat</a>
			<button class="pill pill--sm pill--glass" type="button" onclick="print()"><?= icon('download') ?>Print or save</button>
		</div>
	</article>
</div>
