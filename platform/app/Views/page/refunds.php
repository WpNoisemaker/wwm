<?php $override = tbody('legal.body.refunds', ''); if ($override !== ''): ?>
<article class="page wrap prose">
	<h1 class="prose__h">Refund policy</h1>
	<?= View::partial('page/_policies', ['now' => 'refunds']) ?>
	<?= Blog::toHtml($override) ?>
</article>
<?php return; endif; ?>
<?php $mail = Site::setting('email', 'more@' . Share::host()); ?>
<article class="page wrap prose">
	<span class="eyebrow">Last updated 23 November 2026</span>
	<h1 class="prose__h">Refund policy</h1>
	<?= View::partial('page/_policies', ['now' => 'refunds']) ?>
	<p class="lede"><b>All payments are final. We do not offer refunds.</b></p>
	<p>Please choose your plan carefully. You can start on a Free page and see exactly how your page works before you pay for anything.</p>

	<h2>What this covers</h2>
	<ul>
		<li>Pro, Premium and High Society plans, whether paid in one go or in instalments.</li>
		<li>Upgrades from one plan to another.</li>
		<li>High Society design, branding, printing and livestream work.</li>
		<li>Any other fee paid to Wedding Websites &amp; More.</li>
	</ul>

	<h2>When plans change</h2>
	<ul>
		<li><b>The wedding is postponed:</b> your page and plan stay yours. Change the date and carry on.</li>
		<li><b>The wedding is cancelled, or you stop using the page:</b> no refund. You can make the page private at any time.</li>
		<li><b>Instalments:</b> instalments already paid are not refunded. If later instalments are not paid, the page returns to the plan that has been paid for.</li>
		<li><b>A page removed for breaking our terms:</b> no refund.</li>
	</ul>

	<h2>Gifts and spraying</h2>
	<p>Cash gifts and sprays are gifts from guests to the couple. Once sent they are not refunded or reversed by us. A guest who made a mistake should speak to the couple.</p>

	<h2>Marketplace bookings</h2>
	<p>Payments for stays, rides, honeymoons, aso-ebi and other listings are not refunded by us. Any cancellation or refund for a booking is between the buyer and the seller, on the terms in the seller&rsquo;s listing.</p>

	<h2>Duplicate charges</h2>
	<p>The one exception: if you are charged twice for the same thing because of a technical error, tell us within 14 days at <a href="mailto:<?= e($mail) ?>"><?= e($mail) ?></a> with the payment reference, and we will reverse the duplicate charge.</p>
</article>
