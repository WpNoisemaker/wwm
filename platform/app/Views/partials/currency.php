<?php
/**
 * "Show prices in": one chip per currency (or, $compact, one small menu for the top of a price list).
 * Works as plain links without script; with script the prices redraw in place and the choice is
 * remembered for the next visit.
 *
 * @var string $cur @var bool $compact
 */
$labels = ['NGN' => 'Naira', 'GBP' => 'Pound', 'USD' => 'Dollar', 'EUR' => 'Euro', 'CAD' => 'Canada', 'AUD' => 'Australia',
           'NZD' => 'New Zealand', 'AED' => 'Dirham', 'GHS' => 'Cedi', 'KES' => 'Shilling', 'ZAR' => 'Rand', 'RWF' => 'Franc'];
$compact = $compact ?? false;
?>
<?php if ($compact): ?>
<form class="fx-pick" method="get" action="#plans" data-fx-pick>
	<label><?= icon('globe') ?><span>Prices in</span>
		<select name="cur" data-cur-select aria-label="Show prices in">
			<?php foreach (Fx::NAMES as $k => $name): ?><option value="<?= e($k) ?>"<?= $k === $cur ? ' selected' : '' ?>><?= e(trim(cur_sign($k))) ?> <?= e($name) ?></option><?php endforeach; ?>
		</select>
	</label>
	<noscript><button class="pill pill--sm" type="submit">Show</button></noscript>
	<p class="fx__note" data-fx-note<?= $cur === 'NGN' ? ' hidden' : '' ?>>This is a guide at today&rsquo;s rate; plans are priced in naira.</p>
</form>
<?php return; endif; ?>
<div class="fx" id="currency" data-fx="<?= e(Fx::forScript()) ?>">
	<span class="eyebrow">Show prices in</span>
	<nav class="chips chips--center" aria-label="Currency">
		<?php foreach (Fx::NAMES as $k => $name): ?>
			<a class="chip<?= $k === $cur ? ' is-on' : '' ?>" href="?cur=<?= e($k) ?>#currency" data-cur="<?= e($k) ?>" title="<?= e($name) ?>"<?= $k === $cur ? ' aria-current="true"' : '' ?>><span class="fx__sign"><?= e(trim(cur_sign($k))) ?></span> <?= e($labels[$k]) ?></a>
		<?php endforeach; ?>
	</nav>
	<p class="fx__note" data-fx-note<?= $cur === 'NGN' ? ' hidden' : '' ?>>Plans are priced in naira. Prices in other currencies are a guide at today&rsquo;s rate.</p>
</div>
