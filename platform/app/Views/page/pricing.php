<?php
/** Pricing: the four plans on glass, Free in black & white, then the questions people ask. */
$plans = [
	['free', 'Free', 0, 'Everything you need to get your page live today.', ['Invitation &amp; event details', 'Your love story', 'RSVP &amp; wall of love', 'Cash &amp; gift registry', 'Vendor list &amp; reviews'], 'Black &amp; white. Free forever.', 'Start free'],
	['pro', 'Pro', $pro, 'Your colours, your squad, guest photos and entry passes. No ads.', ['Everything in Free, in colour', 'Bridesmaids &amp; groomsmen', 'Guest photo gallery', 'Entry passes &amp; check-in', 'Verified badge, no ads'], 'Once per wedding, or in 3 instalments.', 'Go Pro'],
	['premium', 'Premium', $premium, 'Every feature and any of the ten Premium designs.', ['We set up your page and keep it updated', 'Ten Premium designs', 'Background music on your page', 'Voice notes on your wall of love', 'Livestream on your page', 'Spraying from the seat', '200 printed table QR cards', 'No charge on gifts or spraying'], 'Once per wedding, or in 3 instalments.', 'Go Premium'],
	['society', 'High Society', null, 'Full event branding beyond your website.', ['Everything in Premium, including our team running your page', 'A page design made only for you', 'Monogram, invitations &amp; signage', 'Livestream coverage by our crew', 'Your own event manager'], 'Priced for your wedding.', 'Get a quote'],
];
?>
<div class="page wrap">
	<header class="page-head">
		<span class="eyebrow"><?= t('pricing.eyebrow.paid-once-per-wedding-never', 'Paid once per wedding, never monthly') ?></span>
		<h1><?= t('pricing.title.start-free-go-premium-for', 'Plans and prices') ?></h1>
		<p class="lede"><?= t('pricing.intro.moving-up-later-only-costs', 'Moving up later only costs the difference. Everything you have added carries over, and the new plan’s features switch on the moment you upgrade.') ?></p>
	</header>
	<?= View::partial('partials/currency', ['cur' => $cur]) ?>
	<div class="plans plans--4">
		<?php foreach ($plans as [$k, $n, $p, $pitch, $items, $note, $cta]): ?>
			<div class="glass plan<?= $k === 'free' ? ' glass--plain plan--mono' : '' ?><?= $k === 'premium' ? ' plan--best' : '' ?>" id="<?= e($k) ?>"<?= $k === 'free' ? ' data-tone="mono"' : '' ?>>
				<p class="plan__name"><?= e($n) ?><?php if ($k === 'premium'): ?> <span class="badge badge--solid">Full owambe</span><?php endif; ?></p>
				<p class="plan__price"><?= $p === null ? 'By quote' : Fx::show((int) $p, $cur) ?></p>
				<p class="plan__pitch"><?= $pitch ?></p>
				<ul><?php foreach ($items as $i): ?><li><?= icon('check') ?><?= $i ?></li><?php endforeach; ?>
					<li><?= icon('chart') ?><?= Buzz::coins('rsvp', $k) ?> buzz coins per RSVP</li></ul>
				<p class="plan__note"><?= $note ?></p>
				<a class="pill pill--block<?= $k === 'premium' ? '' : ' pill--glass' ?>" href="<?= e(App::url('/join?plan=' . $k)) ?>"><?= e($cta) ?></a>
			</div>
		<?php endforeach; ?>
	</div>
	<p class="center sub gap-t">A Free or Pro page adds a <?= e($fee) ?>% service fee to cash gifts, which guests can choose to cover. Premium adds none.</p>
	<p class="center gap-t-sm"><a class="linkish" href="<?= e(App::url(Auth::check() ? '/templates' : '/sign-in?to=%2Ftemplates')) ?>"><?= t('pricing.button.see-every-premium-design-on', 'See every Premium design on a real page') ?></a> · <a class="linkish" href="<?= e(App::url('/rankings#coins')) ?>"><?= t('pricing.button.how-each-plan-earns-buzz', 'How each plan earns buzz') ?></a></p>
	<p class="center sub gap-t-sm">Paid pages stay live for good. Free pages can be removed at any time. All payments are final: <a class="linkish" href="<?= e(App::url('/refunds')) ?>"><?= t('pricing.button.no-refunds', 'no refunds') ?></a>.</p>
</div>

<section class="sec wrap">
	<div class="sec-head"><span class="eyebrow"><?= t('pricing.eyebrow.before-you-pay', 'Before you pay') ?></span><h2><?= t('pricing.heading.questions-people-ask', 'Questions people ask') ?></h2></div>
	<div class="faq narrow" style="width:min(100%,760px)">
		<?php foreach ([
			['Is the free wedding page really free?', 'Yes. Your page, invitation and programs, RSVP, love story, wall of love, vendor list and cash and gift registry are free for as long as you want them. When guests send cash gifts through a free page a small service fee is added, which guests can choose to cover. Free pages also carry two small sponsored slots.'],
			['Can I start on Pro and move up later?', 'Yes. Pro and Premium are paid once per wedding, in one go or in instalments, and moving up only costs the difference. Your story, guests, RSVPs, gifts and photos all carry over, and the new plan’s features switch on straight away: your colours and squad on Pro, a Premium design, livestream and spraying on Premium.'],
			['How does spraying work?', 'Guests pick an amount and transfer it to your own account number from any Nigerian bank app. The page confirms each spray the moment it arrives and ranks your top sprayers live, under the livestream.'],
			['How are weddings ranked?', 'By Buzz coins. Every visit, RSVP, love note, like, review, gift, guest photo, quiz play, check-in, livestream viewer and spray earns the couple coins. A page only earns coins from the features on its plan.'],
			['Can people outside Nigeria use it?', 'Yes. African couples in the UK, the US, Canada, Australia, New Zealand, Dubai, Ghana, Kenya, Rwanda and South Africa make pages too, and each country has its own listing. Guests anywhere can RSVP, give and watch, and gifts show in the couple’s own currency.'],
			['Can I see prices in my currency?', 'Yes. Pick your currency above the plans and every price redraws. Plans are priced in naira; other currencies are a guide at today’s rate, which we keep up to date.'],
			['How long does my page stay up?', 'A page on Pro, Premium or High Society stays live for good, through the wedding and afterwards as your keepsake, as long as it keeps to our terms and policies. A Free page can be removed at any time, so keep copies of anything you love, or move up to keep it.'],
			['Do you give refunds?', 'No. All payments are final, including plans, upgrades and instalments. If the wedding moves, your page and plan move with it. You can try everything on a Free page before you pay.'],
			['Can I hide my wedding page?', 'Yes. You can make your page private at any time from your dashboard. Private pages are not listed, ranked or searchable.'],
		] as $i => [$q, $a]): ?>
			<details class="faq__q"<?= $i === 0 ? ' open' : '' ?>>
				<summary><span><?= e($q) ?></span><i class="faq__i" aria-hidden="true"></i></summary>
				<p><?= e($a) ?></p>
			</details>
		<?php endforeach; ?>
	</div>
</section>
