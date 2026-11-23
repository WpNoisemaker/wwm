<?php
/** One message. An invoice or payment link shows as a card that opens its page. @var array $m @var bool $mine */
$who = ['user' => 'Customer', 'partner' => 'Seller', 'staff' => 'Our team'][(string) $m['sender']] ?? '';
?>
<div class="msg<?= $mine ? ' msg--me' : '' ?><?= (string) $m['sender'] === 'staff' ? ' msg--team' : '' ?>" data-id="<?= (int) $m['id'] ?>">
	<?php if (!empty($m['listing_title']) && empty($m['invoice_id'])): ?><a class="msg__about" href="<?= e(App::url('/market/' . $m['listing_slug'])) ?>"><?= icon('bag') ?><?= e((string) $m['listing_title']) ?></a><?php endif; ?>
	<?php if (!empty($m['inv_code'])): $paid = (string) $m['inv_status'] === 'paid'; $off = (string) $m['inv_status'] === 'cancelled'; ?>
		<a class="msg__inv<?= $paid ? ' is-paid' : ($off ? ' is-off' : '') ?>" href="<?= e(App::url('/pay/' . $m['inv_code'])) ?>">
			<span class="msg__inv-k"><?= icon('wallet') ?><?= $paid ? 'Receipt' : ((string) $m['inv_kind'] === 'link' ? 'Payment link' : 'Invoice') ?> #<?= e((string) $m['inv_code']) ?></span>
			<span class="msg__inv-t"><?= e((string) $m['inv_title']) ?></span>
			<span class="msg__inv-v"><?= money(number_format((int) $m['inv_amount']), (string) $m['inv_cur']) ?></span>
			<span class="msg__inv-s"><?= $paid ? 'Paid' : ($off ? 'Cancelled' : 'Open it to pay') ?></span>
		</a>
		<?php if (strpos((string) $m['body'], 'Marked as paid') === 0 || strpos((string) $m['body'], 'Cancelled:') === 0): ?><p class="msg__b"><?= e((string) $m['body']) ?></p><?php endif; ?>
	<?php else: ?>
		<p class="msg__b"><?= nl2br(e((string) $m['body'])) ?></p>
	<?php endif; ?>
	<span class="msg__m"><?= $mine ? '' : e($who) . ' · ' ?><?= e(date('j M, g:ia', (int) strtotime((string) $m['created_at']))) ?></span>
</div>
