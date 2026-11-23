<?php
/**
 * The order of the page: the invite always first, then every section in the order the couple
 * chose, each with arrows, a grip to drag and a switch to show or hide it. On a Free page the
 * list shows the order guests see, and every control opens the upgrade sheet.
 *
 * @var array $w @var array $layout
 */
$can = Wedding::has($w, 'layout');
$lock = static fn (string $feature): string => ' data-upgrade="pro" data-feature="' . e($feature) . '"';
?>
<?= View::partial('account/_head', ['h' => 'Sections', 'w' => $w, 'sub' => 'What guests see, in the order they see it', 'now' => 'sections']) ?>
	<div class="split-2">
		<form method="post" class="form glass glass--plain glass--tight acct-card">
			<?= Guard::field() ?>
			<div class="lock-row"><h2 class="h3">The order of your page</h2><?php if (!$can): ?><?= up_tag('pro', 'Moving and hiding sections') ?><?php endif; ?></div>
			<p class="help"><?= $can ? 'Drag the grip or use the arrows. Tap the eye to hide a section; it keeps everything in it, ready to show again.' : 'Free pages show every section, in this order. With Pro you can move them around and hide the ones you do not need.' ?></p>
			<ol class="sec-list" data-sec-list<?= $can ? '' : ' data-locked' ?>>
				<li class="sec-item sec-item--fixed"><span class="sec-item__grip" aria-hidden="true"><?= icon('lock') ?></span><span class="sec-item__body"><span class="sec-item__t">The invite</span><span class="sec-item__s">Your names, date, photos and the RSVP. Always first.</span></span><span></span></li>
				<?php foreach ($layout['order'] as $k): [$name, $about, $needs] = Wedding::SECTIONS[$k]; $off = in_array($k, $layout['hidden'], true); $planOk = Wedding::rank((string) $w['plan']) >= Wedding::rank($needs); ?>
					<li class="sec-item<?= $off ? ' is-off' : '' ?>" data-key="<?= e($k) ?>">
						<span class="sec-item__grip"<?= $can ? ' aria-hidden="true"' : $lock('Moving sections') . ' role="button" tabindex="0" aria-label="Move ' . e($name) . '"' ?>><?= icon('menu') ?></span>
						<span class="sec-item__body">
							<span class="sec-item__t"><?= e($name) ?><?php if (!$planOk): ?><?= up_tag($needs, $name) ?><?php endif; ?></span>
							<span class="sec-item__s"><?= e($about) ?><?= !$planOk ? ' · shows from ' . e(Site::tier($needs)) : '' ?></span>
						</span>
						<span class="sec-item__acts">
							<input type="hidden" name="order[]" value="<?= e($k) ?>">
							<button class="cbtn" type="button" data-sec-up aria-label="Move <?= e($name) ?> up"<?= $can ? '' : $lock('Moving sections') ?>><?= icon('chev-up') ?></button>
							<button class="cbtn" type="button" data-sec-down aria-label="Move <?= e($name) ?> down"<?= $can ? '' : $lock('Moving sections') ?>><?= icon('chev-down') ?></button>
							<label class="sec-show"<?= $can ? '' : $lock('Hiding sections') ?>><input type="checkbox" name="show[<?= e($k) ?>]" value="1"<?= $off ? '' : ' checked' ?> aria-label="Show <?= e($name) ?>"><span><?= icon('eye') ?></span></label>
						</span>
					</li>
				<?php endforeach; ?>
				<li class="sec-item sec-item--fixed"><span class="sec-item__grip" aria-hidden="true"><?= icon('lock') ?></span><span class="sec-item__body"><span class="sec-item__t">The footer</span><span class="sec-item__s">Your names and date, to close the page. Always last.</span></span><span></span></li>
			</ol>
			<?php if ($can): ?>
				<div class="form-actions gap-t-sm"><button class="pill" type="submit">Save the order</button><a class="pill pill--glass" href="<?= e(App::url('/w/' . $w['slug'])) ?>">View the page</a></div>
			<?php else: ?>
				<button class="pill pill--block gap-t-sm" type="button" data-upgrade="pro" data-feature="Moving and hiding sections">Move and hide sections with Pro</button>
			<?php endif; ?>
		</form>
		<aside class="form glass glass--plain glass--tight">
			<h2 class="h3">Good to know</h2>
			<p class="help">Your colours of the day sit right under the invite unless you move them, so guests see what to wear first.</p>
			<p class="help">A hidden section keeps everything in it. Show it again any time.</p>
			<p class="help">Sections your plan does not include carry a <b>Pro</b> or <b>Premium</b> tag and only appear once you move up.</p>
		</aside>
	</div>
</div>
