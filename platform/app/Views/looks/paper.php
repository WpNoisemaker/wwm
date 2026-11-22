<div class="wrap">
	<p class="pp-k" style="margin:2px 0 2px">June 27, 2026</p>
	<nav class="pp-seg">
		<a class="is-on" href="#">Day</a><a href="#">Week</a><a href="#">Month</a><a href="#">All</a>
	</nav>

	<div class="cols"><div>
		<article class="pp-card">
			<span class="pp-blob pp-blob--orange"><span class="pp-blob__tick"><?= icon('check', 14) ?></span></span>
			<div class="pp-body">
				<div class="pp-top">
					<span><span class="pp-k">Before the day</span><span class="pp-v" style="display:block">418 guests</span></span>
					<em class="pp-pill" style="font-style:normal">RSVP</em>
				</div>
				<div class="pp-scale">
					<b class="pp-scale__lab">312 in</b>
					<span class="pp-scale__ticks" aria-hidden="true"><?= str_repeat('<i></i>', 9) ?><i class="is-now"></i><?= str_repeat('<i></i>', 11) ?></span>
					<span class="pp-scale__days"><span>Su</span><span>Mo</span><b>Tu</b><span>We</span><span>Th</span><span>Fr</span><span>Sa</span></span>
				</div>
				<p class="pp-note">Send the reminder on Tuesday so the caterer has a number by Friday.</p>
			</div>
		</article>

		<article class="pp-card">
			<span class="pp-blob pp-blob--green"><span class="pp-blob__tick"><?= icon('check', 14) ?></span></span>
			<div class="pp-body">
				<div class="pp-top">
					<span><span class="pp-k">Cash &amp; gifts</span><span class="pp-v" style="display:block"><?= money("2.7m") ?></span></span>
					<em class="pp-pill" style="font-style:normal">Registry</em>
				</div>
				<p class="pp-note" style="margin-top:8px">Paid out every Friday. <?= money("900,000") ?> still to come from the honeymoon fund.</p>
			</div>
		</article>

		<article class="pp-card">
			<span class="pp-blob pp-blob--blue"><span class="pp-blob__tick"><?= icon('check', 14) ?></span></span>
			<div class="pp-body">
				<div class="pp-top">
					<span><span class="pp-k">From your guests</span><span class="pp-v" style="display:block">214 photos</span></span>
					<em class="pp-pill" style="font-style:normal">Gallery</em>
				</div>
				<p class="pp-note" style="margin-top:8px">38 love notes waiting on the wall. Nothing to approve.</p>
			</div>
		</article>
	</div>

	<aside class="cols__side">
		<article class="pp-card" style="grid-template-columns:1fr">
			<div class="pp-body">
				<span class="pp-k">Days to go</span>
				<span class="pp-v" style="font-size:2.6rem">84</span>
				<p class="pp-note" style="margin-top:6px">Saturday 27 June, Lagos.</p>
			</div>
		</article>
		<article class="pp-card" style="grid-template-columns:1fr">
			<div class="pp-body">
				<div class="pp-top"><span class="pp-k">Your plan</span><em class="pp-pill" style="font-style:normal">Premium</em></div>
				<p class="pp-note" style="margin-top:8px">Own design, entry passes, livestream and table cards.</p>
			</div>
		</article>
	</aside></div>
</div>
<a class="pp-edge" href="<?= e(App::url('/community')) ?>" aria-label="Community"><?= icon('talk', 20) ?></a>
