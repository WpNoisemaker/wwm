<?php
/**
 * The upgrade sheet. Anything locked on a lower plan carries a Pro or Premium tag; tapping it,
 * or the locked control itself, opens this with what the feature is and where to get it.
 */
?>
<div class="sheet" id="upgrade" role="dialog" aria-modal="true" aria-labelledby="up-t">
	<div class="sheet__grab" aria-hidden="true"></div>
	<div class="sheet__head">
		<a class="cbtn cbtn--sm" href="#" data-close aria-label="Close"><?= icon('close') ?></a>
		<h2 id="up-t">Upgrade</h2>
		<span></span>
	</div>
	<div class="upgrade">
		<span class="up-tag up-tag--lg up-tag--pro" data-up-tag><?= icon('s-spark') ?><span>Pro</span></span>
		<p class="upgrade__t" data-up-title>This comes with Pro</p>
		<p class="upgrade__s" data-up-text>Pay once for this wedding and it switches on straight away. Everything you have added carries over.</p>
		<ul class="upgrade__list" data-up-list="pro">
			<li><?= icon('check') ?>Your colours, and your colour code under the invite</li>
			<li><?= icon('check') ?>Move and hide any section of your page</li>
			<li><?= icon('check') ?>The squad, guest photos and the quiz</li>
			<li><?= icon('check') ?>Entry passes, a verified badge and no ads</li>
		</ul>
		<ul class="upgrade__list" data-up-list="premium" hidden>
			<li><?= icon('check') ?>Every Premium design</li>
			<li><?= icon('check') ?>Background music on your page</li>
			<li><?= icon('check') ?>Voice notes on your wall of love</li>
			<li><?= icon('check') ?>Livestream on your page</li>
			<li><?= icon('check') ?>Spraying from the seat</li>
			<li><?= icon('check') ?>No charge on gifts or spraying, and everything in Pro</li>
		</ul>
		<a class="pill pill--block" href="<?= e(App::url('/pricing#premium')) ?>" data-up-cta>See Pro &amp; Premium</a>
		<a class="pill pill--glass pill--block" href="#" data-close>Not now</a>
	</div>
</div>
