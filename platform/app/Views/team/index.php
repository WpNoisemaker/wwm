<?php /** Everything a planner, bouncer or vendor helps with, and the way into each. @var array $rows */ ?>
<div class="page wrap">
	<header class="page-head"><span class="eyebrow">Invited by the couples</span><h1>Weddings you help with</h1></header>
	<?= flashes() ?>
	<?php if (!$rows): ?>
		<div class="glass glass--plain empty"><span class="cbtn"><?= icon('people') ?></span><p>You are not on any wedding team yet. When a couple invites you, the link in their email brings you here.</p></div>
	<?php else: ?>
		<div class="rows">
			<?php foreach ($rows as $t): $names = Wedding::names($t); ?>
				<div class="row-item team-mine">
					<span class="row-item__ico"><?= icon($t['role'] === 'bouncer' ? 'shield' : ($t['role'] === 'vendor' ? 'store' : 'people')) ?></span>
					<span><span class="row-item__t"><?= e($names) ?></span><span class="row-item__s"><?= e(Team::ROLES[$t['role']]) ?><?= $t['role'] === 'vendor' && $t['vendor_name'] ? ' · ' . e($t['vendor_name']) : '' ?><?= $t['wed_date'] ? ' · ' . e(date('j M Y', strtotime((string) $t['wed_date']))) : '' ?></span></span>
					<?php if ($t['role'] === 'planner'): ?>
						<a class="pill pill--sm" href="<?= e(App::url('/team/open/' . (int) $t['id'])) ?>">Open their dashboard</a>
					<?php elseif ($t['role'] === 'bouncer'): ?>
						<a class="pill pill--sm" href="<?= e(App::url('/door/' . $t['slug'])) ?>">Open the door list</a>
					<?php else: ?>
						<a class="pill pill--sm" href="<?= e(App::url('/team/' . (int) $t['id'] . '/reviews')) ?>">Reply to reviews</a>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
