<?php
/**
 * "Show prices in": one chip per currency. Works as plain links without script; with script the
 * prices redraw in place and the choice is remembered for the next visit.
 *
 * @var string $cur
 */
$labels = ['NGN' => 'Naira', 'GBP' => 'Pound', 'USD' => 'Dollar', 'EUR' => 'Euro', 'CAD' => 'Canada', 'AUD' => 'Australia',
           'NZD' => 'New Zealand', 'AED' => 'Dirham', 'GHS' => 'Cedi', 'KES' => 'Shilling', 'ZAR' => 'Rand', 'RWF' => 'Franc'];
?>
<div class="fx" id="currency" data-fx="<?= e(Fx::forScript()) ?>">
	<span class="eyebrow">Show prices in</span>
	<nav class="chips chips--center" aria-label="Currency">
		<?php foreach (Fx::NAMES as $k => $name): ?>
			<a class="chip<?= $k === $cur ? ' is-on' : '' ?>" href="?cur=<?= e($k) ?>#currency" data-cur="<?= e($k) ?>" title="<?= e($name) ?>"<?= $k === $cur ? ' aria-current="true"' : '' ?>><span class="fx__sign"><?= e(trim(cur_sign($k))) ?></span> <?= e($labels[$k]) ?></a>
		<?php endforeach; ?>
	</nav>
	<p class="fx__note" data-fx-note<?= $cur === 'NGN' ? ' hidden' : '' ?>>Plans are priced in naira. Other currencies are a guide at today&rsquo;s rate.</p>
</div>
