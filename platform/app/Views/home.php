<section class="wrap" style="padding:14px 0 10px">
	<p class="cap" style="margin-bottom:10px">Good afternoon</p>
	<h1 style="margin-bottom:6px"><?= e($hello) ?></h1>
	<p class="lede" style="max-width:34ch"><?= e($sub) ?></p>
</section>

<div class="wrap cols"><div>
<!-- The two mesh tiles, the way the reference leads with score and age. -->
<section style="padding:14px 0 0">
	<div class="grid grid--2">
		<a class="tile m-score" href="<?= e(App::url('/account')) ?>">
			<span class="tile__cap">Your wedding</span>
			<b class="tile__big dot" style="--dot-color:#fff"><?= e($days) ?></b>
			<span class="tile__foot">days to go</span>
		</a>
		<a class="tile m-age" href="<?= e(App::url('/account/money')) ?>">
			<span class="tile__cap">Raised so far</span>
			<b class="tile__big dot" style="--dot-color:#fff;font-size:clamp(1.9rem,7.6vw,2.6rem)"><?= e($raised) ?></b>
			<span class="tile__foot"><?= e($raisedSub) ?></span>
		</a>
	</div>
</section>

<!-- Quick numbers, dot-matrix, the way the reference sets 106 / 80 / 21 / 5. -->
<section style="padding:18px 0 0">
	<div style="display:flex;gap:22px;flex-wrap:wrap;align-items:flex-start">
		<?php foreach ($stats as $s): ?>
			<span style="display:grid;gap:5px">
				<span style="display:flex;align-items:center;gap:7px">
					<b class="dot" style="font-size:1.7rem"><?= e($s['n']) ?></b>
					<?php if (!empty($s['pill'])): ?><em class="pill" style="font-style:normal"><?= e($s['pill']) ?></em><?php endif; ?>
				</span>
				<span class="sub"><?= e($s['l']) ?></span>
			</span>
		<?php endforeach; ?>
	</div>
</section>

<!-- The pill list, straight off the Superpower sidebar. -->
<section style="padding:22px 0 0">
	<div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:12px">
		<h2>Your page</h2>
		<a class="sub" href="<?= e(App::url('/account')) ?>">Open<?= '' ?></a>
	</div>
	<div class="rows">
		<?php foreach ($tasks as $t): ?>
			<a class="row" href="<?= e(App::url($t['href'])) ?>">
				<span class="row__ico"><?= icon($t['i'], 21) ?></span>
				<span class="row__t"><?= e($t['t']) ?></span>
				<?php if (!empty($t['done'])): ?>
					<span class="pill"><?= icon('check', 13) ?>Done</span>
				<?php else: ?>
					<span class="row__v"><?= e($t['v'] ?? '') ?></span>
				<?php endif; ?>
			</a>
		<?php endforeach; ?>
	</div>
</section>

</div>
<aside class="cols__side">
<!-- Blob cards: the pills-screen treatment, used for the features. -->
<section style="padding:24px 0 0">
	<h2 style="margin-bottom:12px">What guests can do</h2>
	<div class="rail">
		<?php foreach ($feats as $f): ?>
			<a class="card card--pad" style="width:168px" href="<?= e(App::url($f['href'])) ?>">
				<span class="blob blob--<?= e($f['c']) ?>" style="border-radius:var(--r-sm);margin-bottom:14px"></span>
				<b style="display:block;font-weight:500;font-size:1rem;letter-spacing:-.02em"><?= e($f['t']) ?></b>
				<span class="sub"><?= e($f['s']) ?></span>
			</a>
		<?php endforeach; ?>
	</div>
</section>

<!-- The community, previewed as the swipe deck. -->
<section style="padding:14px 0 0">
	<div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:12px">
		<h2>Community</h2>
		<a class="pill pill--quiet" href="<?= e(App::url('/community')) ?>">Open<?= icon('arrow', 13) ?></a>
	</div>
	<a class="tile m-night" href="<?= e(App::url('/community')) ?>" style="min-height:220px">
		<span style="display:flex;align-items:center;justify-content:space-between">
			<em class="pill" style="font-style:normal;background:rgba(255,255,255,.16);color:#fff">Family &amp; in-laws</em>
			<span style="font-size:.78rem;opacity:.6">2 hours ago</span>
		</span>
		<span style="font-size:1.22rem;line-height:1.3;font-weight:450;letter-spacing:-.02em"><?= e($talk) ?></span>
		<span style="display:flex;align-items:center;justify-content:space-between;font-size:.8rem;opacity:.72">
			<span>Tired wife · 4 replies</span><span>♥ 596</span>
		</span>
	</a>
</section>
</aside></div>
