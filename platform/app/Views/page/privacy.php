<?php $override = tbody('legal.body.privacy', ''); if ($override !== ''): ?>
<article class="page wrap prose">
	<h1 class="prose__h">Privacy policy</h1>
	<?= View::partial('page/_policies', ['now' => 'privacy']) ?>
	<?= Blog::toHtml($override) ?>
</article>
<?php return; endif; ?>
<?php $mail = Site::setting('email', 'more@' . Share::host()); ?>
<article class="page wrap prose">
	<span class="eyebrow">Last updated 23 November 2026</span>
	<h1 class="prose__h">Privacy policy</h1>
	<?= View::partial('page/_policies', ['now' => 'privacy']) ?>
	<p>This policy explains what Wedding Websites &amp; More collects when you use <?= e(Share::host()) ?> and our app, why, who sees it and what you can ask us to do with it. We handle personal data under the Nigeria Data Protection Act 2023 and, for visitors in the United Kingdom and the European Union, the UK GDPR and the GDPR.</p>

	<h2>What we collect</h2>
	<ul>
		<li><b>Your account:</b> your name, email, phone number if you add it, and your password, which we only ever store as a scrambled hash.</li>
		<li><b>Your wedding page:</b> everything you add to it: names, dates, venues, your story, photos, programmes, squad, registry, quizzes and design choices.</li>
		<li><b>Guests:</b> when someone RSVPs, the name, phone number, party size and note they give, and their entry pass. When they leave a note, a photo or a quiz score, the name they type.</li>
		<li><b>Payments:</b> the amount, currency, the name and message the payer gives, and the reference from our payment partner. Card and bank details go straight to the payment partner; we never see or store them.</li>
		<li><b>Sellers:</b> the business details and listings you publish, and the payout account you give us.</li>
		<li><b>The community:</b> the post or reply and the pen name you choose. No account, email or real name is asked for. To stop spam we briefly keep a scrambled form of your network address, then delete it.</li>
		<li><b>Technical data:</b> basic logs (pages requested, time, browser) kept to run and protect the service.</li>
	</ul>

	<h2>Why we use it</h2>
	<ul>
		<li>To run the service you asked for: your page, RSVPs, gifts, payouts, bookings and the community.</li>
		<li>To keep it safe: preventing fraud, spam and abuse, and enforcing our terms.</li>
		<li>To tell you what you need to know: confirmations, receipts, RSVP alerts and important changes.</li>
		<li>To meet legal duties, such as tax and anti-fraud records.</li>
	</ul>
	<p>We do not sell your personal data and we do not run third-party advertising trackers.</p>

	<h2>What is public</h2>
	<p>A public wedding page can be seen by anyone with the link, appears in the directory and on its country&rsquo;s page, is ranked on the Buzz Chart and shows a preview when the link is shared. Guest notes, photos and quiz names on a public page are public too. A private page is shown only to the couple and to people with its pass code. Guest phone numbers and RSVP notes are only ever shown to the couple.</p>

	<h2>Who else sees it</h2>
	<p>Only the providers we need to run the service: our hosting company, our email provider, and our payment partners, each bound to protect it and use it only for us. We share data with authorities only when the law requires it.</p>

	<h2>How long we keep it</h2>
	<p>Paid pages are kept for as long as the service runs, as the couple&rsquo;s keepsake. Free pages may be removed at any time (see our <a href="<?= e(App::url('/terms')) ?>">terms</a>), and their content goes with them. Payment records are kept for as long as the law requires. Close your account and we delete your personal data, except what we must keep by law.</p>

	<h2>Your rights</h2>
	<p>You can ask to see the personal data we hold about you, correct it, delete it, object to how we use it, or receive a copy. Guests can ask the couple, or us, to remove their RSVP, note or photo. Write to <a href="mailto:<?= e($mail) ?>"><?= e($mail) ?></a> and we will reply within 30 days. You can also complain to the Nigeria Data Protection Commission, or to your own country&rsquo;s regulator.</p>

	<h2>Children</h2>
	<p>Accounts are for adults. We do not knowingly collect data from children. A couple who adds a child&rsquo;s photo to their page does so as the child&rsquo;s parent or with the parent&rsquo;s agreement.</p>

	<h2>Security and transfers</h2>
	<p>Pages are served over an encrypted connection, passwords are hashed and access to data is limited to people who need it. Our providers may store data outside your country; where they do, we make sure it stays protected to the standard the law requires.</p>

	<h2>Cookies</h2>
	<p>We use a small number of cookies to keep you signed in and remember your choices. Our <a href="<?= e(App::url('/cookies')) ?>">cookie policy</a> lists every one.</p>

	<h2>Changes and contact</h2>
	<p>We will update this page when anything changes; the date at the top shows when. Questions: <a href="mailto:<?= e($mail) ?>"><?= e($mail) ?></a>.</p>
</article>
