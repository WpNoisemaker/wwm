<?php
/** The Buzz Chart: the top three as squircle cards with dot-numeral ranks, the rest as rows. */
$top  = array_slice($rows, 0, 3);
$rest = array_slice($rows, 3);
$max  = max(1, (int) ($rows[0]['buzz'] ?? 1));
?>
<div class="page wrap">
	<header class="page-head">
		<span class="eyebrow"><?= t('rankings.eyebrow.ranked-by-what-guests-did', 'Ranked by guest activity, not by payment') ?></span>
		<h1>The Buzz Chart<?= $city !== '' ? ' · ' . e($city) : '' ?></h1>
		<p class="lede"><?= t('rankings.intro.every-visit-rsvp-wish-and', 'The Buzz Chart ranks wedding pages by how guests use them: visits, RSVPs, wishes and gifts.') ?></p>
	</header>

	<nav class="chips chips--center" aria-label="Cities">
		<a class="chip<?= $city === '' ? ' is-on' : '' ?>" href="<?= e(App::url('/rankings')) ?>">Everywhere</a>
		<?php foreach ($cities as $c): ?>
			<a class="chip<?= $city === $c['city'] ? ' is-on' : '' ?>" href="<?= e(App::url('/rankings?city=' . urlencode((string) $c['city']))) ?>"><?= e($c['city']) ?> <span class="chip__n"><?= (int) $c['n'] ?></span></a>
		<?php endforeach; ?>
	</nav>

	<?php if (!$rows): ?>
		<div class="glass glass--plain empty gap-t"><span class="cbtn"><?= icon('chart') ?></span><p><?= t('rankings.text.the-chart-is-empty-here', 'There are no pages here yet. Pages appear once guests visit, reply and give.') ?></p></div>
	<?php else: ?>
		<div class="podium gap-t">
			<?php foreach ($top as $i => $w): ?>
				<a class="podium__item podium__item--<?= $i + 1 ?>" href="<?= e(App::url('/w/' . $w['slug'])) ?>">
					<span class="podium__rank"><?= dots((string) ($i + 1)) ?></span>
					<?= sq((string) $w['cover'], Wedding::names($w), '', $i === 0, initials($w)) ?>
					<span class="card__cap"><span class="card__t"><?= e(Wedding::names($w)) ?></span><span class="card__s"><?= e($w['city']) ?> · <?= e(number_format((int) $w['buzz'])) ?> buzz</span></span>
				</a>
			<?php endforeach; ?>
		</div>

		<?php if ($rest): ?>
			<ol class="rows chart gap-t">
				<?php foreach ($rest as $i => $w): $pct = (int) round((int) $w['buzz'] / $max * 100); ?>
					<li>
						<a class="row-item chart__row" href="<?= e(App::url('/w/' . $w['slug'])) ?>">
							<span class="num"><?= $i + 4 ?></span>
							<span>
								<span class="row-item__t"><?= e(Wedding::names($w)) ?></span>
								<span class="row-item__s"><?= e(trim((string) $w['city'] . ' · ' . Site::tier((string) $w['plan']), ' ·')) ?></span>
								<?= meter($pct, '', number_format((int) $w['buzz']), 'slider--thin chart__meter') ?>
							</span>
							<?= sq((string) $w['cover'], '', 'row-item__pic', false, initials($w)) ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>
	<?php endif; ?>

	<section class="sec" id="coins">
		<div class="sec-head"><span class="eyebrow"><?= t('rankings.eyebrow.the-rules', 'The rules') ?></span><h2><?= t('rankings.heading.how-a-page-earns-buzz', 'How the ranking works') ?></h2><p><?= tl('rankings.text.every-coin-comes-from-something', 'Every coin comes from something a real guest did. The same action earns more on a higher plan, and some actions only exist on the higher plans.') ?></p></div>
		<div class="tbl-wrap coins-tbl">
			<table class="tbl">
				<thead><tr><th scope="col">When a guest&hellip;</th><?php foreach (Buzz::PLANS as $pl): ?><th scope="col" class="r"><?= e(Site::tier($pl)) ?></th><?php endforeach; ?></tr></thead>
				<tbody>
					<?php foreach (Buzz::ACTS as [$label, $coins]): ?>
						<tr><th scope="row"><?= e($label) ?></th><?php foreach ($coins as $i => $c): ?><td class="r<?= $i === 3 ? ' coins-tbl__top' : '' ?>"><?= $c > 0 ? '<b>' . $c . '</b>' : '<span class="sub" aria-label="Not on this plan">&mdash;</span>' ?></td><?php endforeach; ?></tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<p class="center sub gap-t-sm">These are the coins earned for each guest action. A visit counts once per guest; refreshing the page does not add more. <a class="linkish" href="<?= e(App::url('/pricing')) ?>"><?= t('rankings.button.compare-the-plans', 'Compare the plans') ?></a></p>
	</section>
</div>
