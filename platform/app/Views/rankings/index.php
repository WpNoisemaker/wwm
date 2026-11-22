<?php $lead = $rows[0] ?? null; ?>
<section class="sec wrap">
	<div class="sec__head">
		<div>
			<h2>The Buzz Chart<?= $city !== '' ? ' &middot; ' . e($city) : '' ?></h2>
			<p class="sub">Ranked by what guests actually did — not by who paid.</p>
		</div>
	</div>

	<div class="rail chips">
		<a class="chip<?= $city === '' ? ' is-on' : '' ?>" href="<?= e(App::url('/rankings')) ?>">Everywhere</a>
		<?php foreach ($cities as $c): ?>
			<a class="chip<?= $city === $c['city'] ? ' is-on' : '' ?>" href="<?= e(App::url('/rankings?city=' . urlencode((string) $c['city']))) ?>"><?= e($c['city']) ?><em><?= e($c['n']) ?></em></a>
		<?php endforeach; ?>
	</div>

	<?php if (!$lead): ?>
		<div class="blank"><p class="blank__h">The chart is empty here.</p><p class="sub">It fills up as guests visit, reply and give.</p></div>
	<?php else: $p = Wedding::palette($lead); ?>
		<a class="lead" href="<?= e(App::url('/w/' . $lead['slug'])) ?>" style="--p:<?= e($p['p']) ?>;--a:<?= e($p['a']) ?>">
			<span class="lead__art"><?= photo((string) $lead['cover'], '', true) ?></span>
			<span class="lead__in">
				<span class="lead__no"><em>No.</em><b>1</b></span>
				<b class="lead__h"><?= e(Wedding::names($lead)) ?></b>
				<span class="lead__meta"><?= e($lead['city']) ?> &middot; <?= e(Site::tier((string) $lead['plan'])) ?><?= $lead['hashtag'] ? ' &middot; ' . e($lead['hashtag']) : '' ?></span>
				<span class="lead__buzz"><b><?= e(number_format((int) $lead['buzz'])) ?></b> buzz coins</span>
			</span>
		</a>

		<ol class="chart chart--full">
			<?php foreach (array_slice($rows, 1) as $i => $w): $pp = Wedding::palette($w); ?>
				<li class="chart__row">
					<span class="chart__no"><?= e($i + 2) ?></span>
					<span class="chart__pic" style="--p:<?= e($pp['p']) ?>;--a:<?= e($pp['a']) ?>"><?= photo((string) $w['cover'], '') ?></span>
					<span class="chart__who">
						<b><?= e(Wedding::names($w)) ?></b>
						<small><?= e($w['city']) ?> &middot; <?= e(Site::tier((string) $w['plan'])) ?><?= $w['hashtag'] ? ' &middot; ' . e($w['hashtag']) : '' ?></small>
					</span>
					<span class="chart__buzz"><b><?= e(number_format((int) $w['buzz'])) ?></b><small>buzz</small></span>
					<a class="chart__go ico" href="<?= e(App::url('/w/' . $w['slug'])) ?>" aria-label="Open <?= e(Wedding::names($w)) ?>"><?= icon('up', 16) ?></a>
				</li>
			<?php endforeach; ?>
		</ol>
	<?php endif; ?>
</section>

<section class="sec wrap">
	<div class="coins">
		<h2 class="wed__h">How a page earns coins</h2>
		<p class="sub">Every one of these, done by a real guest, moves a page up. A page only earns from the features its plan has, so a Free page has fewer ways to climb.</p>
		<div class="rail chips coins__list">
			<?php foreach (RankingsController::COINS as $c): ?><span class="chip"><?= e($c) ?></span><?php endforeach; ?>
		</div>
	</div>
</section>
