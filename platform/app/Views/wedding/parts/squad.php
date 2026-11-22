<section class="sec wrap" id="squad">
	<h2 class="wed__h">The squad</h2>
	<div class="rail squad">
		<?php foreach ($squad as $s): ?>
			<figure class="mate">
				<span class="mate__art blob blob--pink"><?= photo((string) $s['photo'], '') ?></span>
				<figcaption>
					<b><?= e($s['name']) ?></b>
					<small><?= e($s['role'] ?: ucfirst((string) $s['side'])) ?></small>
					<?php if ($s['words']): ?><p><?= e($s['words']) ?></p><?php endif; ?>
				</figcaption>
			</figure>
		<?php endforeach; ?>
	</div>
</section>
