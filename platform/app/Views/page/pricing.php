<section class="sec wrap">
	<div class="sec__head">
		<div>
			<h2>Start free. Go Premium when you want the full owambe</h2>
			<p class="sub">Paid once per wedding, never monthly. Moving up later only costs the difference.</p>
		</div>
	</div>

	<div class="plans">
		<?php foreach ([
			['free', 'Free', '0', 'Everything you need to get your page live today.', '',
				['Invitation &amp; event details', 'Your love story', 'RSVP', 'Wall of love', 'Cash &amp; gift registry', 'Vendor list &amp; guest reviews'],
				'In our colours. Free forever.', 'Start free'],
			['pro', 'Pro', $pro, 'Your squad, guest photos, quiz, entry passes and your own colours. No ads.', 'Everything in Free, plus',
				['Bridesmaids &amp; groomsmen', 'Guest photo gallery', 'Guest quiz &amp; leaderboard', 'Entry passes &amp; check-in', 'Your own colours &amp; fonts', 'Verified badge, no ads'],
				'Once per wedding, or in 3 instalments.', 'Go Pro'],
			['premium', 'Premium', $premium, 'Every feature: a Premium design, livestream, spraying, printed table QR cards and no fees.', 'Everything in Pro, plus',
				['Livestream on your page', 'Spraying from the seat', '10 Premium designs', '200 printed table QR cards', 'No charge on gifts or spraying', 'Your logo on guest receipts'],
				'Once per wedding, or in 3 instalments.', 'Go Premium'],
			['society', 'High Society', '', 'Full event branding beyond your website, with prints and livestream coverage.', 'Everything in Premium, plus',
				['A page design made only for you', 'Monogram, invitations, programmes, signage', 'Livestream coverage by our crew', 'Your own event manager on WhatsApp'],
				'Priced for your wedding, on WhatsApp.', 'Get a quote'],
		] as [$k, $n, $p, $pitch, $from, $items, $note, $cta]): ?>
			<div class="plan plan--<?= e($k) ?><?= $k === 'premium' ? ' plan--best' : '' ?>">
				<?php if ($k === 'premium'): ?><em class="plan__ribbon">Full owambe</em><?php endif; ?>
				<?php if ($k === 'society'): ?><em class="plan__ribbon">Beyond the website</em><?php endif; ?>
				<b class="plan__n"><?= e($n) ?></b>
				<span class="plan__p"><?php if ($p === ''): ?><span class="plan__quote">By quote</span><?php else: ?><?= money($p) ?><small>once</small><?php endif; ?></span>
				<span class="plan__pitch"><?= $pitch ?></span>
				<?php if ($from): ?><span class="plan__from"><?= e($from) ?>:</span><?php endif; ?>
				<ul class="plan__list"><?php foreach ($items as $i): ?><li><?= icon('check', 14) ?><span><?= $i ?></span></li><?php endforeach; ?></ul>
				<span class="plan__note"><?= e($note) ?></span>
				<a class="btn <?= $k === 'premium' ? 'btn--acid' : 'btn--ghost' ?>" href="<?= e(App::url('/join?plan=' . $k)) ?>"><?= e($cta) ?></a>
			</div>
		<?php endforeach; ?>
	</div>
	<p class="plans__fee">A Free or Pro page adds a <?= e($fee) ?>% service fee to cash gifts, which guests can choose to cover. Premium adds none.</p>
</section>

<section class="sec wrap">
	<div class="sec__head"><div><h2>Questions people ask</h2></div></div>
	<div class="faq">
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
