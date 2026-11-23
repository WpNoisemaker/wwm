<?php
/**
 * Weddings near the visitor. Nothing is asked until they tap: then the browser asks for their
 * location, and the nearest public pages appear. If they allowed it before, it fills in by itself.
 *
 * @var string $eyebrow @var string $heading
 */
?>
<section class="sec wrap near" data-near data-src="<?= e(App::url('/weddings/near')) ?>">
	<div class="sec-head reveal">
		<span class="eyebrow"><?= $eyebrow ?? 'Near you' ?></span>
		<h2><?= $heading ?? 'Weddings in your city' ?></h2>
	</div>
	<div class="near__body" data-near-body>
		<div class="glass glass--plain near__ask">
			<span class="cbtn"><?= icon('pin') ?></span>
			<p>See the wedding pages in your city and the towns nearby. We use your location only to find them, and never keep it.</p>
			<button class="pill" type="button" data-near-go><?= icon('pin') ?>Show weddings near me</button>
		</div>
	</div>
</section>
