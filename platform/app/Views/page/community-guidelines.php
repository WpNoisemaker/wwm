<?php $mail = Site::setting('email', 'more@' . Share::host()); ?>
<article class="page wrap prose">
	<span class="eyebrow">Last updated 23 November 2026</span>
	<h1 class="prose__h">Community guidelines</h1>
	<?= View::partial('page/_policies', ['now' => 'community-guidelines']) ?>
	<p>The community is where people say what they can&rsquo;t say at home: about love, family, money, faith, in-laws and starting over. It only works if it is safe. Post under any pen name, without an account, and keep to these rules.</p>

	<h2>Do</h2>
	<ul>
		<li>Speak from your own life, and reply the way you would want to be answered.</li>
		<li>Disagree with the idea, not the person.</li>
		<li>Keep it about feelings and choices, so anyone can relate.</li>
	</ul>

	<h2>Don&rsquo;t</h2>
	<ul>
		<li><b>Name or identify anyone.</b> No names, photos, phone numbers, workplaces, addresses or details that point to a real person, including your partner or family.</li>
		<li>Harass, threaten, shame or bully anyone.</li>
		<li>Post hate, slurs or attacks on anyone&rsquo;s tribe, religion, nationality, gender or sexuality.</li>
		<li>Post sexual content, violence, or anything about a child that is not about their wellbeing.</li>
		<li>Advertise, sell, ask for money or post links to make money.</li>
		<li>Pretend to be someone else, or post the same thing over and over.</li>
	</ul>

	<h2>If someone is in danger</h2>
	<p>The community is not an emergency service. If you or someone else is in immediate danger, contact your local emergency number or someone you trust right away.</p>

	<h2>What we do</h2>
	<p>Every post carries our address and a watermark, so it is clear where it came from when it is shared. We may hold a post for review, remove it, or block whoever posted it, without warning. To report a post, write to <a href="mailto:<?= e($mail) ?>"><?= e($mail) ?></a> with its link.</p>
</article>
