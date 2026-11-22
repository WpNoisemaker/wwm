<section class="sec wrap" id="gifts">
	<h2 class="wed__h">Cash &amp; gift registry</h2>
	<p class="sub">Give from anywhere, by card, bank transfer or mobile money.</p>
	<div class="gifts">
		<?php foreach ($gifts as $g):
			$target = (int) $g['target'];
			$raised = (int) $g['raised'];
			$pct    = $target > 0 ? min(100, (int) round($raised / $target * 100)) : 0; ?>
			<div class="gift">
				<span class="gift__art blob blob--orange"><?= photo((string) $g['image'], '') ?></span>
				<div class="gift__body">
					<b><?= e($g['title']) ?></b>
					<?php if ($g['note']): ?><small><?= e($g['note']) ?></small><?php endif; ?>
					<?php if ($target > 0): ?>
						<span class="gift__bar"><i style="width:<?= e($pct) ?>%"></i></span>
						<small class="gift__fig"><?= money(short_money($raised), (string) $w['currency']) ?> of <?= money(short_money($target), (string) $w['currency']) ?></small>
					<?php endif; ?>
					<a class="btn btn--sm btn--acid" href="<?= e(App::url('/sign-in?to=' . urlencode('/w/' . $w['slug'] . '#gifts'))) ?>">
						<?= e($g['kind'] === 'item' ? 'Buy this' : 'Give') ?>
					</a>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php if (Wedding::has($w, 'spray')): ?>
		<div class="spray">
			<b>Spraying from your seat</b>
			<p class="sub">Tap to spray and it lands on their page live, wherever you are watching from.</p>
			<a class="btn btn--acid" href="<?= e(App::url('/sign-in?to=' . urlencode('/w/' . $w['slug'] . '#gifts'))) ?>"><?= icon('cash', 16) ?> Spray them</a>
		</div>
	<?php endif; ?>
</section>
