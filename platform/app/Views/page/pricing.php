<?php
/** Pricing: the four plans on glass, Free in black & white, then the questions people ask. */
$plans = [
	['free', 'Free', '0', 'Everything you need to get your page live today.', ['Invitation &amp; event details', 'Your love story', 'RSVP &amp; wall of love', 'Cash &amp; gift registry', 'Vendor list &amp; reviews'], 'Black &amp; white. Free forever.', 'Start free'],
	['pro', 'Pro', $pro, 'Your colours, your squad, guest photos and entry passes. No ads.', ['Everything in Free, in colour', 'Bridesmaids &amp; groomsmen', 'Guest photo gallery', 'Entry passes &amp; check-in', 'Verified badge, no ads'], 'Once per wedding, or in 3 instalments.', 'Go Pro'],
	['premium', 'Premium', $premium, 'Every feature and any of the ten Premium designs.', ['Ten Premium designs', 'Livestream on your page', 'Spraying from the seat', '200 printed table QR cards', 'No charge on gifts or spraying'], 'Once per wedding, or in 3 instalments.', 'Go Premium'],
	['society', 'High Society', '', 'Full event branding beyond your website.', ['A page design made only for you', 'Monogram, invitations &amp; signage', 'Livestream coverage by our crew', 'Your own event manager'], 'Priced for your wedding.', 'Get a quote'],
];
?>
<div class="page wrap">
	<header class="page-head">
		<span class="eyebrow">Paid once per wedding, never monthly</span>
		<h1>Start free. Go Premium for the full owambe</h1>
		<p class="lede">Moving up later only costs the difference. Your page stays exactly as it is.</p>
	</header>
	<div class="plans plans--4">
		<?php foreach ($plans as [$k, $n, $p, $pitch, $items, $note, $cta]): ?>
			<div class="glass plan<?= $k === 'free' ? ' glass--plain plan--mono' : '' ?><?= $k === 'premium' ? ' plan--best' : '' ?>"<?= $k === 'free' ? ' data-tone="mono"' : '' ?>>
				<p class="plan__name"><?= e($n) ?><?php if ($k === 'premium'): ?> <span class="badge badge--solid">Full owambe</span><?php endif; ?></p>
				<p class="plan__price"><?= $p === '' ? 'By quote' : money(number_format((int) $p)) ?></p>
				<p class="plan__pitch"><?= $pitch ?></p>
				<ul><?php foreach ($items as $i): ?><li><?= icon('check') ?><?= $i ?></li><?php endforeach; ?></ul>
				<p class="plan__note"><?= $note ?></p>
				<a class="pill pill--block<?= $k === 'premium' ? '' : ' pill--glass' ?>" href="<?= e(App::url('/join?plan=' . $k)) ?>"><?= e($cta) ?></a>
			</div>
		<?php endforeach; ?>
	</div>
	<p class="center sub gap-t">A Free or Pro page adds a <?= e($fee) ?>% service fee to cash gifts, which guests can choose to cover. Premium adds none.</p>
	<p class="center gap-t-sm"><a class="linkish" href="<?= e(App::url('/templates')) ?>">See every Premium design on a real page</a></p>
</div>

<section class="sec wrap">
	<div class="sec-head"><span class="eyebrow">Before you pay</span><h2>Questions people ask</h2></div>
	<div class="faq narrow" style="width:min(100%,760px)">
		<?php foreach ([
			['Is the free wedding page really free?', 'Yes. Your page, invitation and programs, RSVP, love story, wall of love, vendor list and cash and gift registry are free for as long as you want them. When guests send cash gifts through a free page a small service fee is added, which guests can choose to cover. Free pages also carry two small sponsored slots.'],
			['Can I start on Pro and move up later?', 'Yes. Pro and Premium are paid once per wedding, in one go or in instalments, and moving up only costs the difference. Your content stays exactly as it is.'],
			['How does spraying work?', 'Guests choose a note, tap to spray as many as they like and pay by card, bank transfer or USSD. Family abroad pay with their card. You see every spray on your dashboard and withdraw to your bank or mobile money account.'],
			['How are weddings ranked?', 'By Buzz coins. Every visit, RSVP, love note, like, review, gift, guest photo, quiz play, check-in, livestream viewer and spray earns the couple coins. A page only earns coins from the features on its plan.'],
			['Can people outside Nigeria use it?', 'Yes. Couples in Ghana, Kenya, South Africa and the diaspora make pages too, and guests anywhere can RSVP, give and watch.'],
			['Can I hide my wedding page?', 'Yes. You can make your page private at any time from your dashboard. Private pages are not listed, ranked or searchable.'],
		] as $i => [$q, $a]): ?>
			<details class="faq__q"<?= $i === 0 ? ' open' : '' ?>>
				<summary><span><?= e($q) ?></span><i class="faq__i" aria-hidden="true"></i></summary>
				<p><?= e($a) ?></p>
			</details>
		<?php endforeach; ?>
	</div>
</section>
