<?php /** The door: find a guest, compare the face, check them in or out. @var array $w @var array $rows @var string $q @var array $sum */ ?>
<div class="page wrap door-page">
	<header class="page-head"><span class="eyebrow">At the door</span><h1><?= e(Wedding::names($w)) ?></h1></header>
	<?= flashes() ?>
	<div class="kpis kpis--3">
		<div class="tile"><span class="k">Inside now</span><span class="v"><?= (int) ($sum['inside'] ?? 0) ?></span></div>
		<div class="tile"><span class="k">Arrived</span><span class="v"><?= (int) ($sum['arrived'] ?? 0) ?></span></div>
		<div class="tile"><span class="k">On the list</span><span class="v"><?= (int) ($sum['n'] ?? 0) ?></span></div>
	</div>
	<form class="searchbox gap-t" method="get" role="search"><?= icon('search') ?><input class="input" type="search" name="q" value="<?= e($q) ?>" placeholder="Type a code or a name" aria-label="Find a guest by code or name" autocapitalize="characters" autocomplete="off" autofocus></form>
	<?php if (!$rows): ?>
		<div class="glass glass--plain empty gap-t"><span class="cbtn"><?= icon('people') ?></span><p><?= $q !== '' ? 'Nobody on the list matches that. Check the code with the guest.' : 'No approved guests yet.' ?></p></div>
	<?php else: ?>
		<div class="rows gap-t">
			<?php foreach ($rows as $g): $in = $g['checked_in'] && !$g['checked_out']; ?>
				<div class="row-item door-row<?= $in ? ' is-in' : '' ?>">
					<?php if ($g['photo'] !== ''): ?><img class="door-row__face" src="<?= e(App::url('/face/' . (int) $g['id'])) ?>" alt="Photo of <?= e($g['name']) ?>" width="72" height="72" loading="lazy"><?php else: ?><span class="row-item__ico"><?= e(mb_strtoupper(mb_substr((string) $g['name'], 0, 1))) ?></span><?php endif; ?>
					<span><span class="row-item__t"><?= e($g['name']) ?></span>
						<span class="row-item__s"><?= (int) $g['party'] === 1 ? '1 person' : (int) $g['party'] . ' people' ?><?= $g['pass_code'] ? ' · ' . e(Rsvp::pretty((string) $g['pass_code'])) : '' ?><?= $g['table_no'] !== '' ? ' · table ' . e($g['table_no']) : '' ?></span>
						<span class="row-item__s"><?= $in ? 'Inside since ' . e(date('g:ia', strtotime((string) $g['checked_in']))) : ($g['checked_out'] ? 'Left at ' . e(date('g:ia', strtotime((string) $g['checked_out']))) : 'Not arrived') ?></span>
					</span>
					<form method="post" action="<?= e(App::url('/door/' . $w['slug'] . '/' . (int) $g['id'] . '/' . ($in ? 'out' : 'in'))) ?>"><?= Guard::field() ?><input type="hidden" name="q" value="<?= e($q) ?>"><button class="pill pill--sm<?= $in ? ' pill--glass' : '' ?>" type="submit"><?= $in ? 'Check out' : 'Check in' ?></button></form>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
