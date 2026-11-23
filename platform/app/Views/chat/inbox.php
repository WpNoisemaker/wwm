<?php
/** Every chat: with sellers (as a customer) and with customers (as a seller), newest first. @var array $rows @var int $me */
?>
<?= View::partial('account/_head', ['h' => 'Messages', 'sub' => 'Chats with sellers and customers', 'now' => 'messages']) ?>
	<?php if (!$rows): ?>
		<div class="glass glass--plain empty gap-t">
			<span class="cbtn"><?= icon('chat') ?></span>
			<p>No messages yet. Find a shortlet, a ride or your aso-ebi on the marketplace and message the seller from the listing.</p>
			<a class="pill" href="<?= e(App::url('/market')) ?>">Go to the marketplace</a>
		</div>
	<?php else: ?>
		<div class="rows chat-list">
			<?php foreach ($rows as $t):
				$asSeller = (int) $t['partner_id'] === $me && (int) $t['user_id'] !== $me;
				$who = $asSeller ? ((string) $t['customer'] ?: 'Customer') : ((string) $t['seller'] ?: 'Seller');
				$prefix = ((string) $t['sender'] === 'user') === !$asSeller ? 'You: ' : ''; ?>
				<a class="row-item chat-row<?= (int) $t['unread'] ? ' is-new' : '' ?>" href="<?= e(App::url('/account/messages/' . (int) $t['partner_id'] . '-' . (int) $t['user_id'])) ?>">
					<?php if (!$asSeller): ?><?= sq((string) $t['logo'], '', 'row-item__pic', false, '<span class="fill-art" aria-hidden="true">' . e(mb_strtoupper(mb_substr($who, 0, 1))) . '</span>') ?><?php else: ?><span class="row-item__pic chat-row__av" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($who, 0, 1))) ?></span><?php endif; ?>
					<span>
						<span class="row-item__t"><?= e($who) ?><?= $asSeller ? ' <small class="chat-row__k">customer</small>' : '' ?></span>
						<span class="row-item__s"><?= $t['invoice_id'] ? icon('wallet') . ' ' : '' ?><?= e($prefix . mb_strimwidth((string) $t['body'], 0, 90, '…')) ?></span>
					</span>
					<span class="chat-row__m"><?= e(ago((string) $t['created_at'])) ?><?php if ((int) $t['unread']): ?><b class="chat-row__n"><?= (int) $t['unread'] ?></b><?php endif; ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<?= View::partial('partials/pay-warning', ['short' => true]) ?>
</div>
