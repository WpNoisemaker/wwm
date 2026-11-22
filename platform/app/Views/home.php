<?php
/** @var array $w @var array $d @var ?int $days @var ?array $talk */
$names = Wedding::names($w);
$pct   = $d['target'] > 0 ? min(100, (int) round($d['raised'] / $d['target'] * 100)) : 0;
[$cdN, $cdLabel, $cdIsNumber] = Wedding::countdown($w);
?>
<section class="wrap" style="padding:14px 0 10px">
	<p class="cap" style="margin-bottom:10px"><?= e($cdIsNumber ? $cdN . ' ' . $cdLabel : $cdLabel) ?></p>
	<h1 style="margin-bottom:6px"><?= e($names) ?></h1>
	<p class="lede" style="max-width:36ch">
		<?php if ($d['guests']): ?>
			<?= e($d['coming']) ?> of <?= e($d['guests']) ?> have replied<?= $d['raised'] ? ', and you have raised ' : '' ?><?= $d['raised'] ? money(short_money($d['raised']), (string) $w['currency']) : '' ?>.
		<?php else: ?>
			Your page is live. Share it and the replies will start coming in.
		<?php endif; ?>
	</p>
</section>

<div class="wrap cols"><div>
	<section style="padding:14px 0 0">
		<div class="grid grid--2">
			<a class="tile m-score" href="<?= e(App::url('/account')) ?>">
				<span class="tile__cap"><?= e((string) $w['city'] ?: 'Your wedding') ?></span>
				<b class="tile__big<?= $cdIsNumber ? ' dot' : '' ?>" style="--dot-color:#fff<?= $cdIsNumber ? '' : ';font-size:clamp(1.6rem,6vw,2.1rem)' ?>"><?= e($cdN) ?></b>
				<span class="tile__foot"><?= e($cdLabel) ?></span>
			</a>
			<a class="tile m-age" href="<?= e(App::url('/account/money')) ?>">
				<span class="tile__cap">Raised so far</span>
				<b class="tile__big dot" style="--dot-color:#fff;font-size:clamp(1.8rem,7vw,2.5rem)"><?= money(short_money($d['raised']), (string) $w['currency']) ?></b>
				<span class="tile__foot"><?= $d['target'] ? e($pct . '% of ') . money(short_money($d['target']), (string) $w['currency']) : 'from your registry' ?></span>
			</a>
		</div>
	</section>

	<section style="padding:18px 0 0">
		<div style="display:flex;gap:22px 30px;flex-wrap:wrap;align-items:flex-start">
			<?php foreach ([
				['n' => $d['guests'],  'l' => 'Invited',  'p' => 'Total'],
				['n' => $d['coming'],  'l' => 'Coming',   'p' => null],
				['n' => $d['guests'] - $d['coming'] - $d['declined'], 'l' => 'To reply', 'p' => null],
				['n' => $d['heads'],   'l' => 'Seats',    'p' => null],
			] as $s): ?>
				<span style="display:grid;gap:5px">
					<span style="display:flex;align-items:center;gap:8px">
						<b class="dot" style="font-size:1.75rem"><?= e(max(0, (int) $s['n'])) ?></b>
						<?php if ($s['p']): ?><em class="pill" style="font-style:normal"><?= e($s['p']) ?></em><?php endif; ?>
					</span>
					<span class="sub"><?= e($s['l']) ?></span>
				</span>
			<?php endforeach; ?>
		</div>
	</section>

	<section style="padding:22px 0 0">
		<div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:12px">
			<h2>Your page</h2>
			<a class="sub" href="<?= e(App::url('/w/' . $w['slug'])) ?>">View it</a>
		</div>
		<div class="rows">
			<?php foreach ($tasks as $t): ?>
				<a class="row" href="<?= e(App::url($t['href'])) ?>">
					<span class="row__ico"><?= icon($t['i'], 21) ?></span>
					<span class="row__t"><?= e($t['t']) ?></span>
					<?php if (!empty($t['done'])): ?>
						<span class="pill"><?= icon('check', 13) ?>Done</span>
					<?php elseif (isset($t['money'])): ?>
						<span class="row__v"><?= money($t['money'], (string) $w['currency']) ?></span>
					<?php else: ?>
						<span class="row__v"><?= e((string) ($t['v'] ?? '')) ?></span>
					<?php endif; ?>
				</a>
			<?php endforeach; ?>
		</div>
	</section>
</div>

<aside class="cols__side">
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

	<?php if ($talk): ?>
		<section style="padding:14px 0 0">
			<div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:12px">
				<h2>Community</h2>
				<a class="pill pill--quiet" href="<?= e(App::url('/community')) ?>">Open<?= icon('arrow', 13) ?></a>
			</div>
			<a class="tile m-night" href="<?= e(App::url('/community')) ?>" style="min-height:220px">
				<span style="display:flex;align-items:center;justify-content:space-between;gap:10px">
					<em class="pill" style="font-style:normal;background:rgba(255,255,255,.16);color:#fff"><?= e(Talk::topic((string) $talk['topic'])) ?></em>
					<span style="font-size:.76rem;opacity:.6"><?= e(ago((string) $talk['created_at'])) ?></span>
				</span>
				<span style="font-size:1.2rem;line-height:1.3;font-weight:450;letter-spacing:-.02em"><?= e(mb_strimwidth((string) $talk['body'], 0, 150, '…')) ?></span>
				<span style="display:flex;align-items:center;justify-content:space-between;font-size:.8rem;opacity:.72">
					<span><?= e((string) ($talk['pen_name'] ?: 'Someone')) ?> · <?= e((int) $talk['replies']) ?> replies</span>
					<span>♥ <?= e((int) $talk['likes']) ?></span>
				</span>
			</a>
		</section>
	<?php endif; ?>
</aside></div>
