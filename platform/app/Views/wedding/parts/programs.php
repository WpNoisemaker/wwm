<section class="sec wrap" id="programs">
	<h2 class="wed__h">The programs</h2>
	<?php if (!$programs): ?>
		<p class="sub">The couple has not added the order of the day yet.</p>
	<?php else: ?>
		<ol class="progs">
			<?php foreach ($programs as $p): ?>
				<li class="prog<?= $p['is_private'] ? ' prog--shy' : '' ?>">
					<span class="prog__when">
						<b><?= e($p['starts_at'] ? date('j M', (int) strtotime((string) $p['starts_at'])) : '—') ?></b>
						<em><?= e($p['starts_at'] ? date('g:ia', (int) strtotime((string) $p['starts_at'])) : '') ?></em>
					</span>
					<span class="prog__body">
						<b><?= e($p['title']) ?></b>
						<?php if ($p['is_private']): ?>
							<small><?= icon('lock', 12) ?> By invitation only &middot; the couple will share the venue</small>
						<?php else: ?>
							<?php if ($p['venue']): ?><small><?= icon('pin', 12) ?> <?= e($p['venue']) ?></small><?php endif; ?>
						<?php endif; ?>
						<?php if ($p['dress']): ?><small><?= icon('spark', 12) ?> <?= e($p['dress']) ?></small><?php endif; ?>
						<?php if ($p['note']): ?><small class="prog__note"><?= e($p['note']) ?></small><?php endif; ?>
					</span>
					<?php if (!$p['is_private'] && $p['map_url']): ?>
						<a class="ico" href="<?= e($p['map_url']) ?>" rel="noopener" target="_blank" aria-label="Open in maps"><?= icon('pin', 16) ?></a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>
	<?php endif; ?>
</section>
