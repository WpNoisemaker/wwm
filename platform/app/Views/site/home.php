<?php
/** The page a visitor lands on. A wedding listing and ranking directory first, then everything else. */
/** @var array $chart @var array $fresh @var array $cities @var array $vendors @var array $listings @var array $talks @var array $stats */
$cur = fn(array $w) => (string) ($w['currency'] ?: 'NGN');
?>

<!-- 1. Hero ----------------------------------------------------------------- -->
<section class="hero wrap">
<div class="hero__copy">
	<p class="hero__greet" aria-label="Congratulations on your marriage">
		<?php foreach ([
			['Barka da aure!',      'Hausa'],
			['Ẹ kú ayẹyẹ!',       'Yoruba'],
			['Nnọọ!',             'Igbo'],
			['Hongera kwa harusi!', 'Swahili'],
			['Ayekoo!',             'Twi'],
			['Halala!',             'Zulu'],
			['Una go last o!',      'Pidgin'],
		] as $gi => [$gw, $gl]): ?>
			<span class="hero__greet-w" style="--i:<?= e($gi) ?>" aria-hidden="true"><b><?= $gw ?></b><em><?= e($gl) ?></em></span>
		<?php endforeach; ?>
	</p>
	<h1 class="hero__h">The weddings<br>everyone is<br>talking about.</h1>
	<p class="lede hero__p">Browse real wedding pages, see who is climbing the chart, book the people who make the day, and make your own page free.</p>

	<form class="hero__search" action="<?= e(App::url('/weddings')) ?>" method="get" role="search">
		<span class="hero__search-ico"><?= icon('search', 19) ?></span>
		<input type="search" name="q" placeholder="Search a couple, a city or a hashtag" aria-label="Search weddings">
		<button class="btn btn--sm" type="submit">Search</button>
	</form>

	<div class="hero__stats">
		<?php foreach ([
			['n' => number_format($stats['weddings']), 'l' => 'Wedding pages', 'p' => 'Live'],
			['n' => number_format($stats['guests']),   'l' => 'Guests replied', 'p' => null],
			['n' => number_format($stats['cities']),   'l' => 'Cities',        'p' => null],
			['n' => short_money($stats['raised']),     'l' => 'Gifted',        'p' => null, 'money' => true],
		] as $s): ?>
			<span class="hero__stat">
				<span class="hero__stat-t">
					<b class="<?= $s['money'] ?? false ? 'fig' : 'dot' ?>"><?= $s['money'] ?? false ? money($s['n']) : e($s['n']) ?></b>
					<?php if ($s['p']): ?><em class="pill" style="font-style:normal"><?= e($s['p']) ?></em><?php endif; ?>
				</span>
				<span class="sub"><?= e($s['l']) ?></span>
			</span>
		<?php endforeach; ?>
	</div>
</div>

<?php if ($chart): $top = $chart[0]; $td = json_decode((string) $top['design'], true) ?: []; ?>
	<!-- Desktop only: the page at number one, shown the way a magazine would lead with it. -->
	<a class="hero__feat" href="<?= e(App::url('/w/' . $top['slug'])) ?>" style="--p:<?= e($td['primary'] ?? '#7A5C3A') ?>;--a:<?= e($td['accent'] ?? '#C3A46A') ?>">
		<span class="hero__feat-art"><?= photo((string) $top['cover'], '', true) ?></span>
		<span class="hero__feat-tag"><em>1</em> on the Buzz Chart</span>
		<span class="hero__feat-body">
			<b><?= e(Wedding::names($top)) ?></b>
			<small><?= e($top['city']) ?> · <?= e(Site::tier((string) $top['plan'])) ?> · <?= e(number_format((int) $top['buzz'])) ?> buzz</small>
		</span>
	</a>
<?php endif; ?>
</section>

<!-- 2. The Buzz Chart ------------------------------------------------------- -->
<section class="sec wrap">
	<div class="sec__head">
		<div><h2>The Buzz Chart</h2><p class="sub">Ranked by views, gifts, photos and love notes this month.</p></div>
		<a class="pill pill--quiet" href="<?= e(App::url('/rankings')) ?>">Full chart <?= icon('arrow', 13) ?></a>
	</div>
	<ol class="chart">
		<?php foreach ($chart as $i => $w): ?>
			<li class="chart__row<?= $i === 0 ? ' chart__row--one' : '' ?>">
				<span class="chart__no dot"><?= e($i + 1) ?></span>
				<span class="chart__pic" style="--p:<?= e(json_decode((string) $w['design'], true)['primary'] ?? '#888') ?>;--a:<?= e(json_decode((string) $w['design'], true)['accent'] ?? '#ccc') ?>"><?= photo((string) $w['cover'], '') ?></span>
				<span class="chart__who">
					<b><?= e(Wedding::names($w)) ?></b>
					<small><?= e($w['city']) ?> · <?= e(Site::tier((string) $w['plan'])) ?><?= $w['hashtag'] ? ' · ' . e($w['hashtag']) : '' ?></small>
				</span>
				<span class="chart__buzz"><b><?= e(number_format((int) $w['buzz'])) ?></b><small>buzz</small></span>
				<a class="chart__go ico" href="<?= e(App::url('/w/' . $w['slug'])) ?>" aria-label="Open <?= e(Wedding::names($w)) ?>"><?= icon('up', 16) ?></a>
			</li>
		<?php endforeach; ?>
	</ol>
</section>

<!-- 3. Coming up ------------------------------------------------------------ -->
<?php if ($fresh): ?>
<section class="sec wrap">
	<div class="sec__head">
		<div><h2>Coming up</h2><p class="sub">Pages you can RSVP to right now.</p></div>
		<a class="pill pill--quiet" href="<?= e(App::url('/weddings')) ?>">All weddings <?= icon('arrow', 13) ?></a>
	</div>
	<div class="wgrid">
		<?php foreach ($fresh as $w): $d = json_decode((string) $w['design'], true) ?: []; ?>
			<a class="wcard" href="<?= e(App::url('/w/' . $w['slug'])) ?>" style="--p:<?= e($d['primary'] ?? '#7A5C3A') ?>;--a:<?= e($d['accent'] ?? '#C3A46A') ?>">
				<span class="wcard__art"><?= photo((string) $w['cover'], '') ?></span>
				<span class="wcard__tier"><?= e(Site::tier((string) $w['plan'])) ?></span>
				<span class="wcard__body">
					<b><?= e(Wedding::names($w)) ?></b>
					<small><?= e(date('j M Y', strtotime((string) $w['wed_date']))) ?> · <?= e($w['city']) ?></small>
				</span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>

<!-- 4. Styles --------------------------------------------------------------- -->
<section class="sec wrap">
	<div class="sec__head">
		<div>
			<h2>Owambe, igba nkwu, kamu, white wedding or nikkah</h2>
			<p class="sub">Whatever you are holding, and however many of them, they all live on one page &mdash; each with its own venue, dress code and colours.</p>
		</div>
	</div>
	<div class="rail styles">
		<?php foreach ([
			['Traditional',     'Engagements, introductions and the ceremonies before the white wedding: gele, agbada, kola, palm wine and the loudest talking drums.', ['Yoruba engagement', 'Igba nkwu', 'Kamu', 'Knocking', 'Ruracio', 'Lobola'], '#F0B24A', '#B4571B'],
			['White wedding',   'Church or garden vows, the reception, first dance and the cake. Often the same weekend as the traditional.',                           ['Church', 'Reception', 'First dance', 'Cake cutting'],                        '#A9B6E8', '#44508F'],
			['Nikkah',          'Nikkah, kamu, walima and dinner. Elegant Northern and Muslim weddings with family at the centre.',                                      ['Fatiha', 'Kamu', 'Sadaki', 'Walima', 'Dinner'],                              '#8FD3AE', '#2A6D4C'],
			['Court &amp; registry', 'Registry and city hall weddings, then lunch with the people who matter. Small guest list, big love.',                                   ['Ikoyi registry', 'City hall', 'Intimate lunch'],                             '#9FD0E4', '#2F6C85'],
			['Destination',     'Couples who flew everyone out: beaches, mountains and cities across Africa and beyond.',                                                ['Zanzibar', 'Diani', 'Cape Town', 'Marrakech', 'Dubai'],                      '#F3A58E', '#A8412A'],
			['Diaspora',        'London, Houston, Toronto and Dublin. African weddings abroad, with family at home watching live.',                                      ['London', 'Houston', 'Toronto', 'Dublin', 'Atlanta'],                         '#C8B7F0', '#5B3FA0'],
		] as [$st, $sd, $chips, $sa, $sp]): ?>
			<a class="style" href="<?= e(App::url('/weddings?style=' . urlencode(strip_tags($st)))) ?>" style="--a:<?= e($sa) ?>;--p:<?= e($sp) ?>">
				<span class="style__art"></span>
				<b class="style__t"><?= $st ?></b>
				<span class="style__d"><?= $sd ?></span>
				<span class="style__chips"><?php foreach ($chips as $c): ?><em><?= e($c) ?></em><?php endforeach; ?></span>
			</a>
		<?php endforeach; ?>
	</div>
</section>

<!-- 5. Cities --------------------------------------------------------------- -->
<section class="sec wrap">
	<div class="sec__head"><div><h2>Weddings in</h2></div></div>
	<div class="rail chips">
		<?php foreach ($cities as $c): ?>
			<a class="chip" href="<?= e(App::url('/weddings?city=' . urlencode((string) $c['city']))) ?>">
				<?= e($c['city']) ?><em><?= e($c['n']) ?></em>
			</a>
		<?php endforeach; ?>
	</div>
</section>

<!-- 6. One link: what the page does ----------------------------------------- -->
<section class="sec wrap">
	<div class="feat">
		<div class="feat__head">
			<p class="cap feat__cap">For couples</p>
			<h2 class="feat__h">One link for everything your guests need.</h2>
			<p class="feat__p">Put it in your Instagram bio, your IV and your family WhatsApp group. Guests RSVP, play, give and celebrate on one page.</p>
		</div>
		<div class="feat__grid">
			<?php foreach ([
				['ticket', 'butter',   'RSVP &amp; entry passes',      'Guests RSVP in seconds. Premium guests get an entry pass with a QR code, and your ushers check them in at the gate.'],
				['cash',   'mint',     'Spray money from anywhere',    'Family abroad tap to spray notes on the couple, live, with the naira raining across the screen.'],
				['talk',   'coral',    'Wall of love',                 'Goodwill messages, prayers and jokes from everyone who loves you, with likes.'],
				['cam',    'sky',      'Guest photo gallery',          'Guests upload what they shot on the day. The best ones rise to the top.'],
				['spark',  'lavender', 'Couple quiz &amp; leaderboard', '&ldquo;Who said I love you first?&rdquo; Your guests compete to prove who knows you best.'],
				['gift',   'butter',   'Cash &amp; gift registry',      'Cash funds for the honeymoon or your first home, plus gifts from any store.'],
				['user',   'mint',     'Bridesmaids &amp; groomsmen',   'Introduce your squad with a line about each of them.'],
				['play',   'coral',    'Livestream',                   'Your stream, right on your page, for everyone who could not make it.'],
				['store',  'sky',      'Vendor reviews',               'Guests rate your photographer, caterer and DJ. Great vendors get found.'],
			] as [$fi, $ft, $fn, $fd]): ?>
				<div class="ftile ftile--<?= e($ft) ?>">
					<span class="ftile__i"><?= icon($fi, 19) ?></span>
					<b class="ftile__t"><?= $fn ?></b>
					<span class="ftile__d"><?= $fd ?></span>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="feat__foot">
			<a class="btn btn--acid" href="<?= e(App::url('/join')) ?>">Create your free wedding page</a>
			<span class="feat__note">Free forever on the essentials. Nothing to install, for you or your guests.</span>
		</div>
	</div>
</section>

<!-- 7. Love notes & photos -------------------------------------------------- -->
<?php if ($wall): ?>
<section class="sec wrap">
	<div class="sec__head">
		<div>
			<h2>Love notes &amp; photos, from the guests themselves</h2>
			<p class="sub">Every page collects them. This is what came in this week.</p>
		</div>
		<a class="pill pill--quiet" href="<?= e(App::url('/weddings')) ?>">See the pages <?= icon('arrow', 13) ?></a>
	</div>
	<div class="wall">
		<?php foreach ($wall as $x): $isPhoto = $x['kind'] === 'photo' && $x['image'] !== '' && !str_starts_with((string) $x['image'], 'wp:'); ?>
			<a class="wnote<?= $isPhoto ? ' wnote--photo' : '' ?>" href="<?= e(App::url('/w/' . $x['slug'])) ?>">
				<?php if ($isPhoto): ?>
					<span class="wnote__art"><?= photo((string) $x['image'], '') ?></span>
				<?php endif; ?>
				<span class="wnote__body"><?= e((string) $x['body']) ?></span>
				<span class="wnote__foot">
					<span class="wnote__who"><?= e((string) $x['name']) ?> <span class="sub">&middot; on <?= e(Wedding::names($x)) ?></span></span>
					<span class="wnote__likes"><?= icon('heart', 13) ?> <?= e((int) $x['likes']) ?></span>
				</span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>

<!-- 8. Marketplace ---------------------------------------------------------- -->
<section class="sec wrap">
	<div class="sec__head">
		<div><h2>Stays, rides &amp; honeymoons</h2><p class="sub">Booked here, and your money is held until you confirm.</p></div>
		<a class="pill pill--quiet" href="<?= e(App::url('/market')) ?>">Marketplace <?= icon('arrow', 13) ?></a>
	</div>
	<div class="rail">
		<?php foreach ($listings as $i => $l): ?>
			<a class="mcard" href="<?= e(App::url('/market/' . $l['slug'])) ?>">
				<span class="mcard__art blob blob--<?= e(['orange','green','blue','pink'][$i % 4]) ?>"><?= photo(first_photo((string) $l['photos']), '') ?></span>
				<span class="mcard__k"><?= e(ucfirst((string) $l['kind'])) ?> · <?= e($l['city']) ?></span>
				<b class="mcard__t"><?= e($l['title']) ?></b>
				<span class="mcard__p"><?= money(short_money((int) $l['price'])) ?> <small><?= e($l['price_unit']) ?></small></span>
			</a>
		<?php endforeach; ?>
	</div>
</section>

<!-- 9. Vendors -------------------------------------------------------------- -->
<section class="sec wrap">
	<div class="sec__head">
		<div><h2>The people who make the day</h2><p class="sub">Verified vendors, reviewed by couples who used them.</p></div>
		<a class="pill pill--quiet" href="<?= e(App::url('/vendors')) ?>">All vendors <?= icon('arrow', 13) ?></a>
	</div>
	<div class="rows">
		<?php foreach ($vendors as $v): ?>
			<a class="row" href="<?= e(App::url('/vendors/' . $v['slug'])) ?>">
				<span class="row__ico"><?= photo((string) $v['logo'], '') ?: icon('store', 21) ?></span>
				<span class="row__t"><?= e($v['name']) ?> <span class="sub">· <?= e(trim(($v['category'] ? $v['category'] . ', ' : '') . $v['city'], ', ')) ?></span></span>
				<span class="row__v"><?= $v['verified'] ? '<em class="pill" style="font-style:normal">Verified</em> ' : '' ?><?= e(number_format((float) $v['rating'], 1)) ?> ★</span>
			</a>
		<?php endforeach; ?>
	</div>
</section>

<!-- 10. Plans --------------------------------------------------------------- -->
<?php
/* Prices come from settings where the import carried them over, so the page never contradicts
   what the old site is charging today. */
$proPrice = number_format((int) Site::setting('pro_price', '198000'));
$premPrice = number_format((int) Site::setting('premium_price', '448000'));
$fee = Site::setting('fee_gift', '4');
?>
<section class="sec wrap">
	<div class="sec__head">
		<div>
			<h2>Start free. Go Premium when you want the full owambe</h2>
			<p class="sub">Paid once per wedding, never monthly. Moving up later only costs the difference.</p>
		</div>
		<a class="pill pill--quiet" href="<?= e(App::url('/pricing')) ?>">Compare everything <?= icon('arrow', 13) ?></a>
	</div>
	<div class="plans">
		<?php foreach ([
			['free', 'Free', '0', 'Everything you need to get your page live today.', '',
				['Invitation &amp; event details', 'Your love story', 'RSVP', 'Wall of love', 'Cash &amp; gift registry', 'Vendor list &amp; guest reviews'],
				'In our colours. Free forever.', 'Start free'],
			['pro', 'Pro', $proPrice, 'Your squad, guest photos, quiz, entry passes and your own colours. No ads.', 'Everything in Free, plus',
				['Bridesmaids &amp; groomsmen', 'Guest photo gallery', 'Guest quiz &amp; leaderboard', 'Entry passes &amp; check-in', 'Your own colours &amp; fonts', 'Verified badge, no ads'],
				'Once per wedding, or in 3 instalments.', 'Go Pro'],
			['premium', 'Premium', $premPrice, 'Every feature: a Premium design, livestream, spraying, printed table QR cards and no fees.', 'Everything in Pro, plus',
				['Livestream on your page', 'Spraying from the seat', '10 Premium designs', '200 printed table QR cards', 'No charge on gifts or spraying', 'Your logo on guest receipts'],
				'Once per wedding, or in 3 instalments.', 'Go Premium'],
			['society', 'High Society', '', 'Full event branding beyond your website, with prints and livestream coverage.', 'Everything in Premium, plus',
				['A page design made only for you', 'Monogram, invitations, programmes, signage', 'Livestream coverage by our crew', 'Your own event manager on WhatsApp'],
				'Priced for your wedding, on WhatsApp.', 'Get a quote'],
		] as [$pk, $pn, $pp, $ppitch, $pfrom, $pitems, $pnote, $pcta]): ?>
			<div class="plan plan--<?= e($pk) ?><?= $pk === 'premium' ? ' plan--best' : '' ?>">
				<?php if ($pk === 'premium'): ?><em class="plan__ribbon">Full owambe</em><?php endif; ?>
				<?php if ($pk === 'society'): ?><em class="plan__ribbon">Beyond the website</em><?php endif; ?>
				<b class="plan__n"><?= e($pn) ?></b>
				<span class="plan__p">
					<?php if ($pp === ''): ?>
						<span class="plan__quote">By quote</span>
					<?php else: ?>
						<?= money($pp) ?><small>once</small>
					<?php endif; ?>
				</span>
				<span class="plan__pitch"><?= $ppitch ?></span>
				<?php if ($pfrom): ?><span class="plan__from"><?= e($pfrom) ?>:</span><?php endif; ?>
				<ul class="plan__list">
					<?php foreach ($pitems as $pi): ?><li><?= icon('check', 14) ?><span><?= $pi ?></span></li><?php endforeach; ?>
				</ul>
				<span class="plan__note"><?= e($pnote) ?></span>
				<a class="btn <?= $pk === 'premium' ? 'btn--acid' : 'btn--ghost' ?>" href="<?= e(App::url('/join?plan=' . $pk)) ?>"><?= e($pcta) ?></a>
			</div>
		<?php endforeach; ?>
	</div>
	<p class="plans__fee">A free or Pro page adds a <?= e($fee) ?>% service fee to cash gifts, which guests can choose to cover. Premium adds none.</p>
</section>

<!-- 11. Community ----------------------------------------------------------- -->
<?php if ($talks): ?>
<section class="sec wrap">
	<div class="sec__head">
		<div><h2>The anonymous community</h2><p class="sub">Love, family, money, faith and starting over. No account, no real names.</p></div>
		<a class="pill pill--quiet" href="<?= e(App::url('/community')) ?>">Open <?= icon('arrow', 13) ?></a>
	</div>
	<div class="rail">
		<?php foreach ($talks as $t): ?>
			<a class="tcard tcard--<?= e($t['bg']) ?>" href="<?= e(App::url('/community')) ?>">
				<span class="tcard__top">
					<em class="pill" style="font-style:normal"><?= e(Talk::topic((string) $t['topic'])) ?></em>
					<span><?= e(ago((string) $t['created_at'])) ?></span>
				</span>
				<span class="tcard__body"><?= e(mb_strimwidth((string) $t['body'], 0, 132, '…')) ?></span>
				<span class="tcard__foot"><span><?= e((string) ($t['pen_name'] ?: 'Someone')) ?> · <?= e((int) $t['replies']) ?> replies</span><span>♥ <?= e((int) $t['likes']) ?></span></span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>

<!-- 12. Tips & how-tos ------------------------------------------------------ -->
<?php if ($posts): ?>
<section class="sec wrap">
	<div class="sec__head">
		<div><h2>Wedding tips &amp; how-tos</h2><p class="sub">Written for Nigerian and African weddings, not borrowed from somewhere else.</p></div>
		<a class="pill pill--quiet" href="<?= e(App::url('/blog')) ?>">All stories <?= icon('arrow', 13) ?></a>
	</div>
	<div class="pgrid">
		<?php foreach ($posts as $po): ?>
			<a class="pcard" href="<?= e(App::url('/blog/' . $po['slug'])) ?>">
				<span class="pcard__art blob blob--orange"><?= photo((string) $po['cover'], '') ?></span>
				<span class="pcard__k"><?= e((string) $po['category']) ?> &middot; <?= e(max(1, (int) round(str_word_count(strip_tags((string) $po['body'])) / 200))) ?> min read</span>
				<b class="pcard__t"><?= e((string) $po['title']) ?></b>
				<span class="pcard__x"><?= e(mb_strimwidth((string) $po['excerpt'], 0, 118, '…')) ?></span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>

<!-- 13. Questions ----------------------------------------------------------- -->
<section class="sec wrap">
	<div class="sec__head">
		<div><h2>Questions people ask</h2><p class="sub">The ones that come up before anybody makes a page.</p></div>
	</div>
	<div class="faq">
		<?php foreach ([
			['Is the free wedding page really free?',
			 'Yes. Your page, invitation and programs, RSVP, love story, wall of love, vendor list and cash and gift registry are free for as long as you want them. When guests send cash gifts through a free page a small service fee is added, which guests can choose to cover. Free pages also carry two small sponsored slots.'],
			['What are the four plans?',
			 'Free has the essentials. Pro adds your bridesmaids and groomsmen line-up, guest photos, the quiz, Instagram, entry passes, your own colours and fonts, a verified badge and no ads. Premium adds a Premium design, livestream, spraying, printed table QR cards and no transaction charges. High Society is everything in Premium plus branding, prints and livestream coverage by our crew.'],
			['Can I start on Pro and move up later?',
			 'Yes. Pro and Premium are paid once per wedding, in one go or in instalments, and moving up only costs the difference. Your content stays exactly as it is.'],
			['How does spraying work?',
			 'Guests choose a note, tap to spray as many as they like and pay by card, bank transfer or USSD. Family abroad pay with their card. You see every spray on your dashboard and withdraw to your bank or mobile money account.'],
			['How are weddings ranked?',
			 'By Buzz coins. Every visit, RSVP, love note, like, review, gift, guest photo, quiz play, check-in, livestream viewer and spray earns the couple coins. A page only earns coins from the features on its plan, so Free and Pro pages have fewer ways to earn. Coins come only from real guest activity.'],
			['Can people outside Nigeria use it?',
			 'Yes. Couples in Ghana, Kenya, South Africa and the diaspora make pages too, and guests anywhere can RSVP, give and watch. Pages take gifts in naira, cedis, shillings, rand or US dollars.'],
			['Can I hide my wedding page?',
			 'Yes. You can make your page private at any time from your dashboard. Private pages are not listed, ranked or searchable, and only you can see them.'],
		] as $qi => [$q, $a]): ?>
			<details class="faq__q"<?= $qi === 0 ? ' open' : '' ?>>
				<summary><span><?= e($q) ?></span><i class="faq__i" aria-hidden="true"></i></summary>
				<p><?= e($a) ?></p>
			</details>
		<?php endforeach; ?>
	</div>
</section>

<!-- 14. Make your own ------------------------------------------------------- -->
<section class="sec wrap">
	<div class="cta">
		<div>
			<h2>Your wedding deserves its own page.</h2>
			<p class="lede">Make it free in five minutes. Share the link, and let the love (and the spraying) begin.</p>
		</div>
		<div class="cta__btns">
			<a class="btn btn--acid" href="<?= e(App::url('/join')) ?>">Create a free page</a>
			<a class="btn btn--ghost" href="<?= e(App::url('/pricing')) ?>">See Premium</a>
		</div>
	</div>
</section>
