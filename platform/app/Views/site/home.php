<?php
/** The page a visitor lands on. A wedding listing and ranking directory first, then everything else. */
/** @var array $chart @var array $fresh @var array $cities @var array $vendors @var array $listings @var array $talks @var array $stats */
$cur = fn(array $w) => (string) ($w['currency'] ?: 'NGN');
?>

<!-- 1. Hero ------------------------------------------------------------------ -->
<section class="hero wrap">
<div class="hero__copy">
	<p class="cap hero__eyebrow">Nigerian &amp; African weddings, everywhere</p>
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

<!-- 2. The chart ------------------------------------------------------------- -->
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

<!-- 3. Coming up ------------------------------------------------------------- -->
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

<!-- 4. Cities ---------------------------------------------------------------- -->
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

<!-- 5. Marketplace ----------------------------------------------------------- -->
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

<!-- 6. Vendors --------------------------------------------------------------- -->
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

<!-- 7. Community ------------------------------------------------------------- -->
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

<!-- 8. Make your own --------------------------------------------------------- -->
<section class="sec wrap">
	<div class="cta">
		<div>
			<h2>Your wedding, on one beautiful page.</h2>
			<p class="lede">RSVPs and entry passes, a cash and gift registry, spraying, a guest gallery, a livestream and table QR cards. Start free.</p>
		</div>
		<div class="cta__btns">
			<a class="btn btn--acid" href="<?= e(App::url('/join')) ?>">Create a free page</a>
			<a class="btn btn--ghost" href="<?= e(App::url('/pricing')) ?>">See pricing</a>
		</div>
	</div>
</section>
