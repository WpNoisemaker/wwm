<?php $override = tbody('legal.body.terms', ''); if ($override !== ''): ?>
<article class="page wrap prose">
	<h1 class="prose__h">Terms &amp; conditions</h1>
	<?= View::partial('page/_policies', ['now' => 'terms']) ?>
	<?= Blog::toHtml($override) ?>
</article>
<?php return; endif; ?>
<?php $mail = Site::setting('email', 'more@' . Share::host()); ?>
<article class="page wrap prose">
	<span class="eyebrow">Last updated 23 November 2026</span>
	<h1 class="prose__h">Terms &amp; conditions</h1>
	<?= View::partial('page/_policies', ['now' => 'terms']) ?>
	<p>These terms are the agreement between you and Wedding Websites &amp; More (&ldquo;we&rdquo;, &ldquo;us&rdquo;) when you use <?= e(Share::host()) ?>, our app, wedding pages, the Buzz Chart, the vendor directory, the marketplace and the community. By using any of it you agree to these terms, our <a href="<?= e(App::url('/privacy')) ?>">privacy policy</a>, our <a href="<?= e(App::url('/refunds')) ?>">refund policy</a> and our <a href="<?= e(App::url('/community-guidelines')) ?>">community guidelines</a>.</p>

	<h2>1. Who can use it</h2>
	<p>You must be 18 or older to open an account, make a wedding page, sell on the marketplace or pay for anything. Guests of any age may view a public page with their family.</p>

	<h2>2. Your account</h2>
	<p>Keep your password to yourself. You are responsible for everything done under your account. Tell us straight away at <a href="mailto:<?= e($mail) ?>"><?= e($mail) ?></a> if you think someone else has got in.</p>

	<h2>3. Your wedding page</h2>
	<p>What you put on your page (your story, photos, programmes, registry) stays yours. By publishing it you give us permission to host it, show it to visitors, list it in the directory and on your country&rsquo;s page, rank it on the Buzz Chart and use its name and cover in link previews. You can make your page private at any time; a private page is not listed, ranked or searchable.</p>
	<p>Only post photos and words you have the right to share, and only share other people&rsquo;s details (guests, your squad) with their agreement.</p>

	<h2>4. How long your page stays up</h2>
	<p><b>Free pages.</b> A Free page is offered as it is, at no cost, and we may remove a Free page and its content at any time, with or without notice: for example when it has been inactive for a long time, when the wedding is long past, or when we need to change or end the Free plan. Keep your own copies of anything that matters to you.</p>
	<p><b>Paid pages.</b> A page on Pro, Premium or High Society stays live for as long as this service runs, for the wedding and afterwards as your keepsake, as long as it keeps to these terms and our policies. We will never take a paid page down for being old or quiet. We will only remove or suspend one that breaks these terms or our community guidelines, or where the law requires it.</p>

	<h2>5. Plans and payment</h2>
	<ul>
		<li>Pro and Premium are paid once per wedding, never as a subscription, in one payment or in instalments where offered. High Society is quoted for each wedding.</li>
		<li>Moving up costs the difference between your plan and the new one. Everything you have added carries over and the new plan&rsquo;s features switch on as soon as payment clears.</li>
		<li>Prices are set in naira. Prices shown in other currencies are a guide at our current rate; your bank or card provider converts and may charge its own fees.</li>
		<li>Free and Pro pages add a service fee to cash gifts, which the guest can choose to cover. Premium and High Society pages carry no service fee on gifts or spraying.</li>
	</ul>

	<h2>6. No refunds</h2>
	<p>All payments to us are final and non-refundable, including plans, upgrades, instalments, High Society work and any other fee. That holds if you cancel or postpone the wedding, stop using the page, or switch your page to private. If you postpone, your page and plan stay yours for the new date. The full position, and the one narrow exception for duplicate charges, is in our <a href="<?= e(App::url('/refunds')) ?>">refund policy</a>.</p>

	<h2>7. Gifts, spraying and payouts</h2>
	<p>Money guests send through your page is processed by our payment partners, held for you, and paid out to the bank or mobile money account you nominate, in the name of the account holder only. Gifts and sprays are gifts from your guests to you; we do not reverse them. We may hold or check a payout where we see signs of fraud, a disputed payment or a breach of these terms.</p>

	<h2>8. The marketplace</h2>
	<p>Stays, rides, honeymoons, aso-ebi and services are sold by independent sellers. A booking is a contract between you and the seller, on the terms in their listing. A &ldquo;Verified&rdquo; badge means we have checked that the seller is a real, reachable business; it is not a guarantee of their work. Sellers must describe what they sell accurately, honour the prices they quote and treat buyers with respect. Payments made through the marketplace are non-refundable by us; any cancellation or refund for a booking is between you and the seller.</p>

	<h2>9. The community</h2>
	<p>Anyone can post and reply under a pen name, without an account. Posts must follow our <a href="<?= e(App::url('/community-guidelines')) ?>">community guidelines</a>. We may hold, edit for safety or remove any post or reply, and we may block anyone who misuses it.</p>

	<h2>10. What is not allowed</h2>
	<ul>
		<li>Impersonating someone, or making a page for a wedding that is not yours without the couple&rsquo;s agreement.</li>
		<li>Posting content you have no right to, or content that is unlawful, hateful, sexually explicit, violent or harassing.</li>
		<li>Using the community to name, identify, expose or threaten anyone.</li>
		<li>Fraud, fake fundraising, fake reviews, or using gifts or payouts to move money that is not a wedding gift.</li>
		<li>Scraping the site, interfering with it, or trying to get into accounts or data that are not yours.</li>
	</ul>
	<p>We may remove content, suspend or close any account or page (paid or free) that breaks these terms, without refund.</p>

	<h2>11. Our service</h2>
	<p>We work hard to keep everything running, but the service is provided as it is and as available. We do not promise it will always be uninterrupted or error-free. To the extent the law allows, we are not liable for indirect or consequential loss, and our total liability to you for any claim is limited to what you paid us in the twelve months before it. Nothing here limits liability that cannot be limited by law.</p>

	<h2>12. Our name and design</h2>
	<p>The Wedding Websites &amp; More name, logo, designs, templates and software are ours. You may use them only as part of using the service.</p>

	<h2>13. Changes and ending</h2>
	<p>We may update these terms; the date at the top shows when. If a change matters to you, we will tell you on the site or by email before it applies. You can close your account at any time by writing to us. These terms are governed by the laws of the Federal Republic of Nigeria, and the courts of Lagos State have jurisdiction, unless the law where you live gives you the right to bring a claim there.</p>

	<h2>14. Contact</h2>
	<p>Questions about these terms: <a href="mailto:<?= e($mail) ?>"><?= e($mail) ?></a>.</p>
</article>
