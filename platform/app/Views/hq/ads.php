<?php
/**
 * HQ → Ads: the master switch, each slot on or off, and every ad: headline, text, button, link,
 * picture, price, where it shows. A slot that is off, or has no ad, shows nothing.
 *
 * @var array $ads @var array $slots @var bool $on
 */
$form = static function (array $a): string {
	$id = (int) ($a['id'] ?? 0);
	$in = array_filter(explode(',', (string) ($a['slots'] ?? implode(',', array_keys(Promo::SLOTS)))));
	ob_start(); ?>
	<form method="post" enctype="multipart/form-data" class="form glass glass--plain glass--tight acct-card" id="<?= $id ? 'ad' . $id : 'new' ?>">
		<?= Guard::field() ?><input type="hidden" name="do" value="save"><?php if ($id): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>
		<div class="lock-row"><h2 class="h3"><?= $id ? 'Ad ' . $id : 'Add an ad' ?></h2><?php if ($id): ?><span class="badge"><?= (int) $a['active'] ? 'Showing' : 'Hidden' ?></span><?php endif; ?></div>
		<div class="form-grid form-grid--2">
			<label class="field span-2"><span>Headline</span><input class="input" name="title" required maxlength="160" value="<?= e((string) ($a['title'] ?? '')) ?>"></label>
			<label class="field span-2"><span>Text</span><input class="input" name="line" maxlength="300" value="<?= e((string) ($a['line'] ?? '')) ?>"></label>
			<label class="field"><span>Button</span><input class="input" name="button" maxlength="60" value="<?= e((string) ($a['button'] ?? 'See Premium')) ?>"></label>
			<label class="field"><span>Link (a page on this site like /pricing#premium, or https://…)</span><input class="input" name="link" maxlength="500" value="<?= e((string) ($a['link'] ?? '/pricing#premium')) ?>"></label>
			<div class="field span-2"><span>Picture</span>
				<?php if ((string) ($a['image'] ?? '') !== ''): ?><div class="proj-edit__pic"><?= sq((string) $a['image'], '', '') ?><label class="squad-edit__off"><input type="checkbox" name="image_off" value="1"> Use a Premium page photo instead</label></div><?php else: ?><p class="help">None: a photo from a Premium page is used.</p><?php endif; ?>
				<label class="photo-pick"><input type="file" name="image" accept="image/jpeg,image/png,image/webp" data-photo-input><?= icon('camera') ?><span data-photo-name>Choose a picture</span></label>
			</div>
			<label class="check"><span>Show the Premium price</span><input type="checkbox" name="show_price" value="1"<?= (int) ($a['show_price'] ?? 1) ? ' checked' : '' ?>></label>
			<label class="check"><span>Show this ad</span><input type="checkbox" name="active" value="1"<?= (int) ($a['active'] ?? 1) ? ' checked' : '' ?>></label>
			<div class="field span-2"><span>Where it shows</span>
				<?php foreach (Promo::SLOTS as $k => [$name]): ?><label class="check"><span><?= e($name) ?></span><input type="checkbox" name="slots[<?= e($k) ?>]" value="1"<?= in_array($k, $in, true) ? ' checked' : '' ?>></label><?php endforeach; ?>
			</div>
			<label class="field"><span>Order (lower shows first)</span><input class="input" name="sort" inputmode="numeric" value="<?= (int) ($a['sort'] ?? 0) ?>"></label>
		</div>
		<div class="form-actions"><button class="pill pill--sm" type="submit"><?= $id ? 'Save' : 'Add the ad' ?></button><?php if ($id): ?><button class="pill pill--sm pill--danger" type="submit" name="do" value="delete" formnovalidate data-confirm="Delete this ad?">Delete</button><?php endif; ?></div>
	</form>
	<?php return (string) ob_get_clean();
};
?>
<?= View::partial('hq/_head', ['h' => 'Ads', 'now' => 'ads', 'sub' => 'Our own ads on the site']) ?>
	<div class="split-2">
		<div class="acct-list">
			<?php foreach ($ads as $a): ?><?= $form($a) ?><?php endforeach; ?>
			<?= $form([]) ?>
		</div>
		<aside class="acct-list">
			<form method="post" class="form glass glass--plain glass--tight">
				<?= Guard::field() ?><input type="hidden" name="do" value="slots">
				<h2 class="h3">Where ads show</h2>
				<label class="check"><span><b>Ads on</b> (turn off to hide every ad)</span><input type="checkbox" name="on" value="1"<?= $on ? ' checked' : '' ?>></label>
				<?php foreach (Promo::SLOTS as $k => [$name]): ?><label class="check"><span><?= e($name) ?></span><input type="checkbox" name="slot[<?= e($k) ?>]" value="1"<?= $slots[$k] ? ' checked' : '' ?>></label><?php endforeach; ?>
				<p class="help">A slot that is off, or has no ad in it, shows nothing; the page closes up around it. Pro, Premium and High Society pages never show ads, and people whose own page is Premium never see them.</p>
				<button class="pill pill--block" type="submit">Save</button>
			</form>
		</aside>
	</div>
</div>
