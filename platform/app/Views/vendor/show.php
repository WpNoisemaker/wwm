<section class="sec wrap">
	<div class="vhead">
		<span class="vhead__art blob blob--pink"><?= photo((string) $v['logo'], '', true) ?></span>
		<div class="vhead__body">
			<p class="cap"><?= e($v['category'] ?: 'Vendor') ?><?= $v['city'] ? ' &middot; ' . e($v['city']) : '' ?></p>
			<h1 class="vhead__h"><?= e($v['name']) ?></h1>
			<p class="vhead__rate">
				<b><?= e(number_format((float) $v['rating'], 1)) ?></b> &#9733;
				<span class="sub">from <?= e((int) $v['reviews']) ?> review<?= (int) $v['reviews'] === 1 ? '' : 's' ?></span>
				<?php if ($v['verified']): ?><em class="pill">Verified</em><?php endif; ?>
			</p>
			<?php if (trim((string) $v['about']) !== ''): ?><p class="vhead__about"><?= nl2br(e((string) $v['about'])) ?></p><?php endif; ?>
			<div class="vhead__btns">
				<a class="btn btn--acid" href="<?= e(App::url(Auth::check() ? '/account/vendors' : '/sign-in?to=' . urlencode('/vendors/' . $v['slug']))) ?>"><?= icon('talk', 16) ?> Message them</a>
				<?php if ($v['website']): ?><a class="btn btn--ghost" href="<?= e($v['website']) ?>" rel="noopener nofollow" target="_blank">Website</a><?php endif; ?>
				<?php if ($v['instagram']): ?><a class="btn btn--ghost" href="https://instagram.com/<?= e(ltrim((string) $v['instagram'], '@')) ?>" rel="noopener nofollow" target="_blank">Instagram</a><?php endif; ?>
			</div>
		</div>
	</div>
</section>

<section class="sec wrap">
	<h2 class="wed__h">What couples said</h2>
	<?php if (!$reviews): ?>
		<p class="sub">No reviews yet. Couples who book through a wedding page can leave one afterwards.</p>
	<?php else: ?>
		<div class="revs">
			<?php foreach ($reviews as $r): ?>
				<figure class="rev">
					<p class="rev__stars" aria-label="<?= e((int) $r['stars']) ?> out of 5"><?= str_repeat('&#9733;', max(0, min(5, (int) $r['stars']))) ?><span class="rev__dim"><?= str_repeat('&#9733;', 5 - max(0, min(5, (int) $r['stars']))) ?></span></p>
					<blockquote><?= e((string) $r['body']) ?></blockquote>
					<figcaption><?= e((string) ($r['name'] ?: 'A couple')) ?> &middot; <?= e(ago((string) $r['created_at'])) ?></figcaption>
					<?php if (trim((string) $r['reply']) !== ''): ?>
						<p class="rev__reply"><b><?= e($v['name']) ?> replied:</b> <?= e((string) $r['reply']) ?></p>
					<?php endif; ?>
				</figure>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
