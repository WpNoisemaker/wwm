<?php /** Who sprayed the most, biggest total first. @var array $rows @var string $cur */ ?>
<?php if (!$rows): ?>
	<p class="sub">Nobody has sprayed yet. Be the first on the list.</p>
<?php else: ?>
	<ol class="sprayers__list">
		<?php foreach ($rows as $i => $r): ?>
			<li class="sprayers__row<?= $i < 3 ? ' is-top' : '' ?>" data-who="<?= e((string) $r['who']) ?>" data-amt="<?= (int) $r['total'] ?>"><span class="sprayers__n"><?= $i + 1 ?></span><span class="sprayers__who"><?= e((string) $r['who']) ?><?php if ((int) $r['n'] > 1): ?><small><?= (int) $r['n'] ?> times</small><?php endif; ?></span><span class="sprayers__amt"><?= money(number_format((int) $r['total']), $cur) ?></span></li>
		<?php endforeach; ?>
	</ol>
<?php endif; ?>
