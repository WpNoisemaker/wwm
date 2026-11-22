<?php
/** The top of every account screen: flashes, the email note, a title on desktop, and the sections. */
$now  = $now ?? '';
$tabs = [['overview', 'Overview', '/account', 'grid'], ['page', 'Page', '/account/page', 'pen'], ['programs', 'Programs', '/account/programs', 'calendar'],
         ['squad', 'Squad', '/account/squad', 'people'], ['registry', 'Registry', '/account/registry', 'gift'], ['guests', 'Guests', '/account/guests', 'user'],
         ['money', 'Money', '/account/money', 'wallet'], ['settings', 'Settings', '/account/settings', 'gear']];
?>
<div class="page wrap">
	<header class="page-head">
		<span class="eyebrow"><?= e($sub ?? '') ?></span>
		<h1><?= e($h) ?></h1>
	</header>
	<?php if (!empty($w)): ?>
		<nav class="subnav" aria-label="Your page">
			<?php foreach ($tabs as [$k, $label, $href, $ic]): ?>
				<a href="<?= e(App::url($href)) ?>"<?= $now === $k ? ' class="is-on" aria-current="page"' : '' ?>><?= icon($ic) ?><?= e($label) ?></a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>
	<?= flashes() ?>
	<?php if (Verify::needed(Auth::user())): ?>
		<div class="note note--bad">
			<?= icon('mail') ?><span><b>Confirm your email.</b> We sent a link to <?= e((string) Auth::user()['email']) ?>. Until you follow it you can build your page, but not make it public or ask for a payout.
			<form method="post" action="<?= e(App::url('/account/verify/resend')) ?>" style="display:inline"><?= Guard::field() ?><button class="linkish" type="submit">Send it again</button></form></span>
		</div>
	<?php endif; ?>
