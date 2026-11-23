<?php
/**
 * The top of every account screen: which profile you are in (your wedding page or your seller
 * shop, with a switch when you have both), its sections, flashes and the email note.
 */
$now  = $now ?? '';
Guard::boot();
$ctx  = $ctx ?? (in_array($now, ['shop', 'shop-profile'], true) ? 'shop' : ($now === 'settings' ? ($_SESSION['acct_ctx'] ?? 'wedding') : 'wedding'));
$uid  = (int) Auth::id();
$hasShop = Auth::is('partner') || (bool) Db::val('SELECT 1 FROM vendors WHERE user_id = ?', [$uid]);
$hasWed  = (bool) Db::val('SELECT 1 FROM weddings WHERE owner_id = ?', [$uid]);
if ($ctx === 'shop' && !$hasShop) { $ctx = 'wedding'; }
$tabs = $ctx === 'shop'
	? [['shop', 'Listings', '/account/shop', 'bag'], ['shop-profile', 'Business profile', '/account/shop/profile', 'store'], ['settings', 'Account', '/account/settings', 'gear']]
	: (!empty($w) || $hasWed
		? [['overview', 'Overview', '/account', 'grid'], ['page', 'Page', '/account/page', 'pen'], ['sections', 'Sections', '/account/sections', 'sliders'], ['programs', 'Programs', '/account/programs', 'calendar'],
		   ['squad', 'Squad', '/account/squad', 'people'], ['registry', 'Registry', '/account/registry', 'gift'], ['quiz', 'Quiz', '/account/quiz', 'star'], ['guests', 'Guests', '/account/guests', 'user'],
		   ['money', 'Money', '/account/money', 'wallet'], ['settings', 'Account', '/account/settings', 'gear']]
		: []);
?>
<div class="page wrap">
	<header class="page-head">
		<span class="eyebrow"><?= e($sub ?? '') ?></span>
		<h1><?= e($h) ?></h1>
	</header>
	<?php if ($hasShop): ?>
		<nav class="seg seg--center seg--plans profile-switch" aria-label="Your profiles">
			<a class="seg__a<?= $ctx === 'wedding' ? ' is-on' : '' ?>" href="<?= e(App::url($hasWed ? '/account?as=wedding' : '/account/new')) ?>"<?= $ctx === 'wedding' ? ' aria-current="true"' : '' ?>><?= icon('ring') ?><?= $hasWed ? 'My wedding page' : 'Make a wedding page' ?></a>
			<a class="seg__a<?= $ctx === 'shop' ? ' is-on' : '' ?>" href="<?= e(App::url('/account/shop')) ?>"<?= $ctx === 'shop' ? ' aria-current="true"' : '' ?>><?= icon('store') ?>My seller shop</a>
		</nav>
	<?php endif; ?>
	<?php if ($tabs): ?>
		<nav class="subnav" aria-label="<?= $ctx === 'shop' ? 'Your shop' : 'Your page' ?>">
			<?php foreach ($tabs as [$k, $label, $href, $ic]): ?>
				<a href="<?= e(App::url($href)) ?>"<?= $now === $k ? ' class="is-on" aria-current="page"' : '' ?>><?= icon($ic) ?><?= e($label) ?></a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>
	<?= flashes() ?>
	<?= View::partial('partials/upgrade') ?>
	<?php if (Verify::needed(Auth::user())): ?>
		<div class="note note--bad">
			<?= icon('mail') ?><span><b>Confirm your email.</b> We sent a link to <?= e((string) Auth::user()['email']) ?>. Until you follow it you can build your page, but not make it public or ask for a payout.
			<form method="post" action="<?= e(App::url('/account/verify/resend')) ?>" style="display:inline"><?= Guard::field() ?><button class="linkish" type="submit">Send it again</button></form></span>
		</div>
	<?php endif; ?>
