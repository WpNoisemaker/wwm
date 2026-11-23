<article class="page wrap prose">
	<h1 class="prose__h">Help</h1>
	<p class="lede">These are the questions that come up most often. If yours is not here, use the contact link in the footer.</p>
	<div class="faq">
		<?php foreach ([
			['For couples', [
				['How do I make my page public?', 'Your page starts private so nobody lands on it half-made. When you are ready, go to your dashboard, open Settings and switch visibility to public. It joins the directory and the Buzz Chart straight away.'],
				['Can I have more than one ceremony on one page?', 'Yes, and that is what it is built for. Add a programme for the introduction, the traditional wedding, the nikkah or church service and the reception. Each has its own date, venue, dress code and colours, and guests tick the ones they are attending.'],
				['How do I keep a ceremony private?', 'Mark that programme as private. The page shows it as invitation-only, hides the venue and leaves it out of the public RSVP form. Share the details with those guests yourself.'],
				['How do I get the gift money?', 'Gifts and sprays are collected under Money. You can request a payout to a Nigerian bank account or mobile money whenever you like.'],
			]],
			['For guests', [
				['Do I need an account to RSVP?', 'No. Open the page, fill in your name and how many people are coming, and send it. There is no app to install and no sign-up.'],
				['How do I send a gift?', 'You send it by bank transfer. Open the gift or tap Spray and enter your name, and the page shows the couple’s account number and the exact amount to send. The page confirms the payment as soon as the money arrives.'],
				['I lost the link to a wedding page.', 'Search for the couple or their hashtag on the Weddings page. If the page is private, you will need the code from the couple.'],
			]],
            ['For vendors and sellers', [
				['How do I get listed?', 'Tap Sell with us on the marketplace, create your seller account and fill in your business profile, then list in any of the open categories. Listing is free.'],
				['How do customers pay me?', 'Customers message you from your listing. Agree on the details in the chat, then send an invoice with your payment link, or just a payment link. They pay you directly; we never hold the money.'],
				['How do reviews work?', 'Customers who have talked to you here can leave a review. You can reply publicly to each review, once.'],
			]],
		] as [$group, $items]): ?>
			<h2 class="help__g"><?= e($group) ?></h2>
			<?php foreach ($items as [$q, $a]): ?>
				<details class="faq__q"><summary><span><?= e($q) ?></span><i class="faq__i" aria-hidden="true"></i></summary><p><?= e($a) ?></p></details>
			<?php endforeach; ?>
		<?php endforeach; ?>
	</div>
</article>
