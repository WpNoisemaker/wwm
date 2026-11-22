<section class="sec wrap wed__story">
	<div class="wed__tally">
		<?php foreach ([
			[(string) number_format($tally['guests']), 'coming'],
			[(string) number_format($tally['notes']), 'love notes'],
			[(string) number_format($tally['photos']), 'photos'],
		] as [$n, $l]): ?>
			<span class="wed__stat"><b class="dot"><?= e($n) ?></b><em><?= e($l) ?></em></span>
		<?php endforeach; ?>
	</div>
	<?php if (trim((string) $w['story']) !== ''): ?>
		<h2 class="wed__h">Our story</h2>
		<div class="wed__prose"><?= nl2br(e((string) $w['story'])) ?></div>
	<?php endif; ?>
</section>
