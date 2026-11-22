<?php
/**
 * The homepage. On a phone it opens like the reference screen: two squircle photos and the glass
 * card. On desktop the same pieces become a landing page, section after section, each one showing
 * a part of the platform rather than describing it.
 *
 * @var array $chart @var array $fresh @var array $vendors @var array $listings @var array $talks
 * @var array $wall @var array $posts @var array $stats @var array $samples
 */
$pro   = (int) Site::setting('pro_price', '198000');
$prem  = (int) Site::setting('premium_price', '448000');
$lead  = $samples['ivory'] ?? ($chart[0] ?? null);
$other = null;
foreach ($chart as $c) { if (!$lead || $c['id'] !== $lead['id']) { $other = $c; break; } }
$free  = $samples['free'] ?? null;
$premiumDesigns = array_filter(Design::ALL, static fn ($d) => $d[1] !== 'free');
?>
<section class="hero wrap" id="start">
	<div class="hero__head">
		<span class="eyebrow">Wedding pages for African couples</span>
		<h1 class="hero__title">One beautiful page for your whole wedding</h1>
		<p class="lede">Invite, RSVP, gifts, spraying and livestream in one link. Then watch your wedding climb the Buzz Chart.</p>
		<div class="hero__ctas">
			<a class="pill" href="<?= e(App::url('/join')) ?>">Create your page, free</a>
			<a class="pill pill--glass" href="<?= e(App::url('/templates')) ?>">See every design</a>
		</div>
	</div>
	<div class="trio">
		<?php foreach ([['trio__a', $lead], ['trio__b', $other]] as [$slot, $w]): if (!$w) { continue; } ?>
			<a class="<?= $slot ?>" href="<?= e(App::url('/w/' . $w['slug'])) ?>">
				<span class="sq-label"><?= e(Wedding::names($w)) ?></span>
				<?= sq((string) $w['cover'], Wedding::names($w), '', $slot === 'trio__a', initials($w)) ?>
			</a>
		<?php endforeach; ?>
		<div class="trio__c">
			<div class="glass">
				<p class="glass__q">How many guests are you inviting?</p>
				<div class="counter" data-counter data-min="10" data-max="2000" data-step="10" data-value="150" id="guests">
					<button class="cbtn cbtn--lg" type="button" data-dec aria-label="Fewer guests"><?= icon('minus') ?></button>
					<div class="counter__mid" aria-live="polite">
						<?= dots('150', '', true) ?>
						<span class="counter__unit" data-unit="Guest|Guests">Guests</span>
					</div>
					<button class="cbtn cbtn--lg" type="button" data-inc aria-label="More guests"><?= icon('plus') ?></button>
				</div>
				<div class="total" data-plans='<?= e(json_encode(['pro' => $pro, 'premium' => $prem])) ?>'>
					<p class="total__k" id="plan-k">Pro page</p>
					<p class="total__v" id="plan-v"><?= money(number_format($pro)) ?></p>
				</div>
			</div>
			<a class="pill pill--block" href="<?= e(App::url('/join')) ?>">Create your wedding page</a>
			<p class="after-pill">Or start <a href="<?= e(App::url($free ? '/w/' . $free['slug'] : '/join')) ?>">free, in black &amp; white</a></p>
		</div>
	</div>
</section>

<section class="wrap stats reveal" aria-label="The platform today">
	<div class="tile"><?= dots((string) $stats['weddings'], 'dotnum--md') ?><span class="k">Wedding pages</span></div>
	<div class="tile"><?= dots((string) $stats['guests'], 'dotnum--md') ?><span class="k">Guests replied</span></div>
	<div class="tile"><?= dots((string) $stats['cities'], 'dotnum--md') ?><span class="k">Cities</span></div>
	<div class="tile"><?= dots((string) $stats['vendors'], 'dotnum--md') ?><span class="k">Vendors</span></div>
</section>

<?php if ($chart): ?>
<section class="sec wrap" id="ranked">
	<div class="sec-head reveal">
		<span class="eyebrow">Updated as guests reply</span>
		<h2>The Buzz Chart</h2>
		<p>Pages climb with RSVPs, wishes and shares. Tap one to see why it is up there.</p>
	</div>
	<div class="rail reveal">
		<?php foreach (array_slice($chart, 0, 4) as $n => $w): ?>
			<?= wedding_card($w, '#' . ($n + 1) . ' · ' . trim((string) $w['city'] . ' · ' . Wedding::short($w), ' ·'), Site::tier((string) $w['plan'])) ?>
		<?php endforeach; ?>
	</div>
	<p class="sec-more reveal"><a class="pill pill--glass pill--sm" href="<?= e(App::url('/rankings')) ?>">See the whole chart <?= icon('arrow') ?></a></p>
</section>
<?php endif; ?>

<section class="sec wrap">
	<div class="sec-head reveal">
		<span class="eyebrow">Your dashboard</span>
		<h2>Your wedding, at a glance</h2>
		<p>This is what couples see. Try the switches and drag the sliders.</p>
	</div>
	<div class="mosaic mosaic--wide reveal">
		<button class="tile tile--glyph tile--sq is-on" type="button" data-toggle aria-pressed="true" data-toast-on="Your page is live" data-toast-off="Your page is hidden"><?= icon('s-power') ?><span data-on="Live" data-off="Off">Live</span></button>
		<a class="tile tile--glyph tile--sq" href="<?= e(App::url($lead ? '/w/' . $lead['slug'] . '#story' : '/weddings')) ?>"><?= icon('s-book') ?><span>Story</span></a>
		<div class="tile tile--w2"><span class="k">Page design</span><span class="v">Ivory &amp; Gold</span></div>
		<button class="tile tile--glyph tile--sq" type="button" data-toggle aria-pressed="false" data-toast-on="Guests can spray from their seats" data-toast-off="Spraying is off"><?= icon('s-wallet') ?><span data-on="Spray on" data-off="Spray">Spray</span></button>
		<button class="tile tile--glyph tile--sq" type="button" data-toggle aria-pressed="false" data-toast-on="Only guests with your code can open it" data-toast-off="Anyone with the link can open it"><?= icon('s-shield') ?><span data-on="Private" data-off="Public">Public</span></button>
		<div class="slider" data-slider data-value="68" aria-label="RSVPs in"><div class="slider__fill"><span class="slider__txt"><span class="slider__val">68 %</span><span class="slider__lab">RSVPs in</span></span><i class="slider__grip"></i></div></div>
		<div class="tile tile--w2"><span class="k">Dress code</span><span class="v">Ivory, beige &amp; gold</span></div>
		<div class="tile tile--w2 tile--h2 tile--ticks">
			<div><span class="k">Guests coming</span><span class="v">312</span><span class="k gap-k">Gifts</span><span class="v"><?= money('1.84m') ?></span><span class="k gap-k">Sprayed so far</span><span class="v"><?= money('420k') ?></span></div>
			<div class="ticks" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i class="is-on"></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
		</div>
		<div class="slider" data-slider data-value="39" aria-label="Gifts funded"><div class="slider__fill"><span class="slider__txt"><span class="slider__val">39 %</span><span class="slider__lab">Gifts funded</span></span><i class="slider__grip"></i></div></div>
		<button class="tile tile--glyph tile--sq" type="button" data-toast="Reminders go to guests who have not replied"><?= icon('s-warning') ?><span>Remind</span></button>
		<button class="tile tile--glyph tile--sq" type="button" data-toggle aria-pressed="false" data-toast-on="Livestream is on your page" data-toast-off="Livestream is off"><?= icon('s-bulb') ?><span data-on="Live on" data-off="Stream">Stream</span></button>
		<div class="tile tile--w2 tile--num"><div><span class="k">Days to go</span><span class="v">Sat, 24 Dec</span></div><?= dots('20', 'dotnum--sm') ?></div>
		<div class="tile tile--w2"><span class="k">Reception</span><span class="v">Are Osan, Ilorin</span></div>
		<div class="tile tile--w2"><span class="k">Guests arrive</span><span class="v">11:00 am</span></div>
	</div>
</section>

<section class="sec wrap">
	<div class="sec-head reveal">
		<span class="eyebrow">One link on every invitation</span>
		<h2>Everything your guests need</h2>
	</div>
	<div class="mosaic feat reveal">
		<?php foreach ([
			['s-ring', 'Your page', 'Story, schedule, venue map and dress code in one beautiful link.'],
			['s-users', 'RSVP &amp; guest list', 'Guests answer in seconds, no account. You see who is coming, live.'],
			['s-gift', 'Gifts &amp; cash', 'A registry that pays the couple, with a note from every guest.'],
			['s-wallet', 'Spraying', 'Guests spray from their seats, or from London. The total climbs on screen.'],
			['s-live', 'Livestream', 'Family abroad watch the ceremony right on your page.'],
			['s-crown', 'Buzz Chart', 'The week&rsquo;s most-loved weddings, city by city.'],
			['s-store', 'Marketplace', 'Aso-ebi, gele, stays, rides and honeymoons.'],
			['s-chat', 'Community', 'Say what you can&rsquo;t say at home, under any pen name.'],
		] as [$i, $t, $s]): ?>
			<div class="tile tile--w2"><?= icon($i) ?><span class="v"><?= $t ?></span><span class="k"><?= $s ?></span></div>
		<?php endforeach; ?>
	</div>
</section>

<section class="sec wrap" id="designs">
	<div class="sec-head reveal">
		<span class="eyebrow">Ten Premium designs and counting</span>
		<h2>A design for every kind of wedding</h2>
		<p>Every page is built from the same glass and light. Each design composes it differently.</p>
	</div>
	<div class="rail rail--designs reveal">
		<?php foreach ($samples as $k => $w): if (in_array($k, ['free', 'pro'], true)) { continue; } $d = Design::get($k); ?>
			<a class="card design-card" href="<?= e(App::url('/w/' . $w['slug'])) ?>" style="--s1:<?= e($d['swatch'][0]) ?>;--s2:<?= e($d['swatch'][1]) ?>">
				<span class="sq"><?= photo((string) $w['cover'], '') ?><span class="sq__tag"><?= e($d['name']) ?></span></span>
				<span class="card__cap"><span class="card__t"><?= e(Wedding::names($w)) ?></span><span class="card__s"><?= e($d['plan'] === 'custom' ? 'Custom design' : 'Premium') ?></span></span>
			</a>
		<?php endforeach; ?>
	</div>
	<p class="sec-more reveal"><a class="pill pill--glass pill--sm" href="<?= e(App::url('/templates')) ?>">Compare every design <?= icon('arrow') ?></a></p>
</section>

<section class="sec wrap" id="plans">
	<div class="sec-head reveal">
		<span class="eyebrow">Pay once per wedding</span>
		<h2>Pick your page</h2>
		<p>Free pages are black &amp; white. Pro pages wear your colours. Premium pages pick a design.</p>
	</div>
	<div class="plans reveal">
		<div class="glass glass--plain plan plan--mono" data-tone="mono">
			<p class="plan__name">Free</p>
			<p class="plan__price"><?= money('0') ?></p>
			<p class="plan__pitch">Everything you need to get your page live today.</p>
			<div class="plan__swatch" aria-hidden="true"><i style="background:#111"></i><i style="background:#fff"></i></div>
			<ul>
				<li><?= icon('check') ?>Invitation &amp; event details</li>
				<li><?= icon('check') ?>Your love story</li>
				<li><?= icon('check') ?>RSVP &amp; wall of wishes</li>
				<li><?= icon('check') ?>Cash &amp; gift registry</li>
			</ul>
			<a class="pill pill--glass pill--block" href="<?= e(App::url($free ? '/w/' . $free['slug'] : '/join')) ?>">See a free page</a>
		</div>
		<div class="glass plan">
			<p class="plan__name">Pro</p>
			<p class="plan__price"><?= money(number_format($pro)) ?></p>
			<p class="plan__pitch">Your colours, your squad, guest photos and entry passes. No ads.</p>
			<div class="plan__swatch" aria-hidden="true"><i style="background:#6EAAE6"></i><i style="background:#E3DABF"></i><i style="background:#7B2D43"></i><i style="background:#C9A24B"></i></div>
			<ul>
				<li><?= icon('check') ?>Everything in Free, in colour</li>
				<li><?= icon('check') ?>Bridesmaids &amp; groomsmen</li>
				<li><?= icon('check') ?>Guest photo gallery</li>
				<li><?= icon('check') ?>Entry passes &amp; check-in</li>
			</ul>
			<a class="pill pill--glass pill--block" href="<?= e(App::url(isset($samples['pro']) ? '/w/' . $samples['pro']['slug'] : '/pricing')) ?>">See a Pro page</a>
		</div>
		<div class="glass plan plan--best">
			<p class="plan__name">Premium <span class="badge badge--solid">Full owambe</span></p>
			<p class="plan__price"><?= money(number_format($prem)) ?></p>
			<p class="plan__pitch">Every feature and any of the Premium designs. No fees on gifts.</p>
			<div class="plan__swatch" aria-hidden="true"><?php foreach (array_slice($premiumDesigns, 1, 6) as $d): ?><i style="background:linear-gradient(135deg,<?= e($d[6][0]) ?>,<?= e($d[6][1]) ?>)"></i><?php endforeach; ?></div>
			<ul>
				<li><?= icon('check') ?>Ten Premium designs</li>
				<li><?= icon('check') ?>Livestream on your page</li>
				<li><?= icon('check') ?>Spraying from the seat</li>
				<li><?= icon('check') ?>No charge on gifts or spraying</li>
			</ul>
			<a class="pill pill--block" href="<?= e(App::url($lead ? '/w/' . $lead['slug'] : '/pricing')) ?>">See a Premium page</a>
		</div>
	</div>
	<p class="sec-more reveal"><a class="linkish" href="<?= e(App::url('/pricing')) ?>">Compare every plan, including High Society</a></p>
</section>

<?php if ($fresh): ?>
<section class="sec wrap">
	<div class="split split--list">
		<div class="sec-head sec-head--side reveal">
			<span class="eyebrow">Coming up</span>
			<h2>Weddings this season</h2>
			<p>Every public page, soonest first. Open one, RSVP if you are invited, or just enjoy the story.</p>
			<a class="pill pill--glass pill--sm" href="<?= e(App::url('/weddings')) ?>">Browse every page <?= icon('arrow') ?></a>
		</div>
		<div class="rows reveal">
			<?php foreach (array_slice($fresh, 0, 5) as $w): ?>
				<a class="row-item" href="<?= e(App::url('/w/' . $w['slug'])) ?>">
					<?= sq((string) $w['cover'], '', 'row-item__pic', false, initials($w)) ?>
					<span><span class="row-item__t"><?= e(Wedding::names($w)) ?></span><span class="row-item__s"><?= e(trim((string) $w['city'] . ' · ' . Site::tier((string) $w['plan']), ' ·')) ?></span></span>
					<span class="row-item__end"><?= e(Wedding::short($w)) ?><span class="sub"><?php $dl = Wedding::daysTo($w); echo $dl !== null && $dl >= 0 ? e($dl . ($dl === 1 ? ' day' : ' days')) : ''; ?></span></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ($listings): ?>
<section class="sec wrap" id="market">
	<div class="sec-head reveal">
		<span class="eyebrow">Marketplace</span>
		<h2>Aso-ebi, stays &amp; rides</h2>
	</div>
	<div class="rail reveal">
		<?php foreach (array_slice($listings, 0, 4) as $l): ?>
			<a class="card" href="<?= e(App::url('/market/' . $l['slug'])) ?>">
				<?= sq(first_photo((string) $l['photos']), (string) $l['title']) ?>
				<span class="card__cap"><span class="card__t"><?= e($l['title']) ?></span><span class="card__s"><?= (int) $l['on_ask'] ? 'Price on request' : money(short_money((int) $l['price'])) . ' ' . e((string) $l['price_unit']) ?> · <?= e($l['city']) ?></span></span>
			</a>
		<?php endforeach; ?>
	</div>
	<p class="sec-more reveal"><a class="pill pill--glass pill--sm" href="<?= e(App::url('/market')) ?>">Open the marketplace <?= icon('arrow') ?></a></p>
</section>
<?php endif; ?>

<?php if ($vendors): ?>
<section class="sec wrap">
	<div class="split split--list">
		<div class="sec-head sec-head--side reveal">
			<span class="eyebrow">Rated by couples</span>
			<h2>The people behind the day</h2>
			<p>Photographers, planners, caterers, gele and makeup, every one reviewed by a couple who booked them.</p>
			<a class="pill pill--glass pill--sm" href="<?= e(App::url('/vendors')) ?>">Find a vendor <?= icon('arrow') ?></a>
		</div>
		<div class="rows reveal">
			<?php foreach (array_slice($vendors, 0, 4) as $v): ?>
				<a class="row-item" href="<?= e(App::url('/vendors/' . $v['slug'])) ?>">
					<?= sq((string) $v['logo'], '', 'row-item__pic') ?>
					<span><span class="row-item__t"><?= e($v['name']) ?><?php if ((int) $v['verified']): ?> <span class="badge"><?= icon('check') ?>Verified</span><?php endif; ?></span><span class="row-item__s"><?= e($v['category'] . ' · ' . $v['city']) ?></span></span>
					<span class="row-item__end"><?= (float) $v['rating'] > 0 ? e(number_format((float) $v['rating'], 1)) : '' ?><span class="sub"><?= (int) $v['reviews'] ?> reviews</span></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="sec wrap">
	<div class="split">
		<div class="quote-cta reveal">
			<div class="sec-head">
				<span class="eyebrow">Community · no account needed</span>
				<h2>Say it here instead</h2>
				<p>Text-only posts on colour, under any pen name you like. Swipe through what other people are carrying.</p>
			</div>
			<a class="pill" href="<?= e(App::url('/community')) ?>">Open the community</a>
		</div>
		<?php if ($talks): $t = $talks[0]; ?>
			<a class="post-solo reveal" href="<?= e(App::url('/community/' . $t['id'])) ?>">
				<article class="post <?= e(talk_colour((string) $t['bg'])) ?>">
					<span class="post__topic"><?= e(Talk::topic((string) $t['topic'])) ?></span>
					<p class="post__text<?= mb_strlen((string) $t['body']) > 140 ? ' post__text--long' : '' ?>"><?= e(mb_strimwidth((string) $t['body'], 0, 220, '…')) ?></p>
					<span class="post__meta"><?= e($t['pen_name'] ?: 'Someone') ?> · <?= (int) $t['likes'] ?> likes</span>
				</article>
			</a>
		<?php endif; ?>
	</div>
</section>

<?php if ($wall): ?>
<section class="sec wrap">
	<div class="sec-head reveal">
		<span class="eyebrow">Left on real pages this week</span>
		<h2>Wall of love</h2>
	</div>
	<div class="rail rail--wishes reveal">
		<?php foreach ($wall as $n): if ((string) $n['kind'] !== 'note' || trim((string) $n['body']) === '') { continue; } ?>
			<a class="wish wish--card" href="<?= e(App::url('/w/' . $n['slug'] . '#wall')) ?>">
				<p><?= e(mb_strimwidth((string) $n['body'], 0, 140, '…')) ?></p>
				<span><?= e($n['name'] ?: 'A guest') ?> · for <?= e($n['partner_a'] . ' & ' . $n['partner_b']) ?></span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>

<?php if ($posts): ?>
<section class="sec wrap">
	<div class="sec-head reveal">
		<span class="eyebrow">Stories</span>
		<h2>Planning, honestly</h2>
	</div>
	<div class="rail reveal">
		<?php foreach ($posts as $p): ?>
			<a class="card" href="<?= e(App::url('/blog/' . $p['slug'])) ?>">
				<?= sq((string) $p['cover'], '', 'sq--wide') ?>
				<span class="card__cap"><span class="card__t"><?= e($p['title']) ?></span><span class="card__s"><?= e($p['category'] ?: 'Story') ?></span></span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>

<section class="sec wrap">
	<div class="glass finale reveal">
		<span class="eyebrow">It takes about ten minutes</span>
		<h2>Make your page tonight</h2>
		<p>Start free in black &amp; white. Add your colours, your squad and a Premium design whenever you are ready.</p>
		<div class="hero__ctas">
			<a class="pill" href="<?= e(App::url('/join')) ?>">Create your page, free</a>
			<a class="pill pill--glass" href="<?= e(App::url('/pricing')) ?>">See pricing</a>
		</div>
	</div>
</section>

<script>
	(function () {
		var g = document.getElementById('guests'); if (!g) { return; }
		var box = document.querySelector('[data-plans]'), p = JSON.parse(box.getAttribute('data-plans'));
		var k = document.getElementById('plan-k'), v = document.getElementById('plan-v');
		g.addEventListener('change', function (e) {
			var n = e.detail;
			if (n <= 100) { k.textContent = 'Free page'; WWM.text(v, '₦0'); }
			else if (n < 400) { k.textContent = 'Pro page'; WWM.text(v, '₦' + WWM.fmt(p.pro)); }
			else { k.textContent = 'Premium page'; WWM.text(v, '₦' + WWM.fmt(p.premium)); }
		});
	})();
</script>
