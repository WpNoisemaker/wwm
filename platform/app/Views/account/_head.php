<?php
/**
 * The top of every account screen: which profile you are in (your wedding page or your seller
 * shop, with a switch when you have both), its sections, flashes and the email note.
 */
$now  = $now ?? '';
Guard::boot();
$ctx  = $ctx ?? (in_array($now, ['shop', 'shop-profile'], true) ? 'shop' : (in_array($now, ['settings', 'messages'], true) ? ($_SESSION['acct_ctx'] ?? 'wedding') : 'wedding'));
$uid  = (int) Auth::id();
$hasShop = Auth::is('partner') || (bool) Db::val('SELECT 1 FROM vendors WHERE user_id = ?', [$uid]);
$hasWed  = (bool) Db::val('SELECT 1 FROM weddings WHERE owner_id = ?', [$uid]);
if ($ctx === 'shop' && !$hasShop) { $ctx = 'wedding'; }
if ($ctx === 'wedding' && !$hasWed && empty($w) && !$hasShop) { $ctx = 'customer'; }
// Messages with sellers (or customers), with how many are waiting.
$unread = Market::unread($uid);
$hasChats = $unread > 0 || (bool) Db::val('SELECT 1 FROM messages WHERE user_id = ? OR partner_id = ? LIMIT 1', [$uid, $uid]);
$msgTab = ['messages', 'Messages' . ($unread ? ' (' . $unread . ')' : ''), '/account/messages', 'chat'];
$tabs = $ctx === 'shop'
	? [['shop', 'Listings', '/account/shop', 'bag'], $msgTab, ['shop-profile', 'Business profile', '/account/shop/profile', 'store'], ['settings', 'Account', '/account/settings', 'gear']]
	: ($ctx === 'customer' ? [$msgTab, ['settings', 'Account', '/account/settings', 'gear']] : (!empty($w) || $hasWed
		? [['overview', 'Overview', '/account', 'grid'], ['page', 'Page', '/account/page', 'pen'], ['story', 'Story', '/account/story', 'book'], ['sections', 'Sections', '/account/sections', 'sliders'], ['programs', 'Programs', '/account/programs', 'calendar'],
		   ['squad', 'Squad', '/account/squad', 'people'], ['registry', 'Registry', '/account/registry', 'gift'], ['vendors', 'Vendors', '/account/vendors', 'store'], ['quiz', 'Quiz', '/account/quiz', 'star'], ['guests', 'Guests', '/account/guests', 'user'],
		   ['money', 'Money', '/account/money', 'wallet'], ['team', 'Team', '/account/team', 'people']] + ($hasChats ? [13 => $msgTab] : []) + [14 => ['settings', 'Account', '/account/settings', 'gear']]
		: []));
// A planner the couple invited sees only the parts of the dashboard they were given.
$acting = Team::acting();
if ($acting) {
	$area  = ['page' => 'page', 'story' => 'page', 'sections' => 'page', 'programs' => 'programs', 'squad' => 'squad', 'registry' => 'registry', 'vendors' => 'vendors', 'quiz' => 'quiz', 'guests' => 'guests', 'money' => 'money'];
	$perms = Team::perms($acting);
	$tabs  = array_values(array_filter($tabs, static fn ($t) => $t[0] === 'overview' || in_array($area[$t[0]] ?? '', $perms, true)));
	$hasShop = false;
}
$helps = !$acting && Team::forUser($uid);
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
	<?php if ($acting && !empty($w)): ?>
		<p class="note"><?= icon('people') ?><span>You are working on <b><?= e(Wedding::names($w)) ?></b>’s page as their event planner. Your changes save to their page.</span><a class="pill pill--sm pill--glass" href="<?= e(App::url('/team/leave')) ?>">Back to your teams</a></p>
	<?php elseif ($helps): ?>
		<p class="note"><?= icon('people') ?><span>You also help with other couples’ weddings.</span><a class="pill pill--sm pill--glass" href="<?= e(App::url('/team')) ?>">Your teams</a></p>
	<?php endif; ?>
	<?php if (Managed::managing() && !empty($w)): ?>
		<p class="note note--bad"><?= icon('shield') ?><span>You are managing <b><?= e(Wedding::names($w)) ?></b>’s page as a member of our team. Your changes are saved to their page.</span><a class="pill pill--sm" href="<?= e(App::url('/hq/manage/stop')) ?>">Stop managing</a></p>
	<?php endif; ?>
	<?= flashes() ?>
	<?= View::partial('partials/upgrade') ?>
	<?php if (Verify::needed(Auth::user())): ?>
		<div class="note note--bad">
			<?= icon('mail') ?><span><b>Confirm your email.</b> We sent a link to <?= e((string) Auth::user()['email']) ?>. Until you follow it, you can build your page, but you cannot make it public or ask for a payout.
			<form method="post" action="<?= e(App::url('/account/verify/resend')) ?>" style="display:inline"><?= Guard::field() ?><button class="linkish" type="submit">Send it again</button></form></span>
		</div>
	<?php endif; ?>
