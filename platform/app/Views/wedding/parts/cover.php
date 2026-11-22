<?php /** The cover. Free gets a band; Premium gets the full-bleed treatment. */
$full = Wedding::has($w, 'cover'); ?>
<header class="cover<?= $full ? ' cover--full' : '' ?>">
	<span class="cover__art"><?= photo((string) $w['cover'], '', true) ?></span>
	<div class="cover__in wrap">
		<?php if ($gold): ?><span class="cover__mono" aria-hidden="true"><?= e(Wedding::monogram($w)) ?></span><?php endif; ?>
		<?php if ($w['hashtag']): ?><p class="cover__tag"><?= e($w['hashtag']) ?></p><?php endif; ?>
		<h1 class="cover__h"><?= e($names) ?></h1>
		<p class="cover__when"><?= e(Wedding::when($w)) ?></p>
		<?php if ($where = Wedding::where($w)): ?><p class="cover__where"><?= icon('pin', 14) ?> <?= e($where) ?></p><?php endif; ?>
		<?php if ($w['tagline']): ?><p class="cover__line"><?= e($w['tagline']) ?></p><?php endif; ?>

		<?php if ($next && $left): ?>
			<div class="count" data-count data-at="<?= e($next['when']) ?>">
				<p class="count__k"><?= e($next['label']) ?> &middot; in</p>
				<div class="count__row">
					<?php foreach ([['d', 'days'], ['h', 'hrs'], ['m', 'min']] as [$k, $lab]): ?>
						<span class="count__u"><b data-<?= e($k) ?>><?= e(str_pad((string) $left[$k], 2, '0', STR_PAD_LEFT)) ?></b><em><?= e($lab) ?></em></span>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="cover__btns">
			<a class="btn btn--acid" href="#rsvp">RSVP</a>
			<a class="btn btn--ghost" href="#wall">Leave a note</a>
			<?php if (Wedding::has($w, 'spray')): ?><a class="btn btn--ghost" href="#gifts"><?= icon('cash', 16) ?> Spray</a><?php endif; ?>
		</div>
	</div>
</header>
