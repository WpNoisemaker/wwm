<?php
/**
 * HQ edits the site's menus. Each row is a label and a link; order them with the numbers (lowest
 * first), tick Remove to drop one, and use the empty rows to add more.
 * @var array $menus
 */
$row = static function (string $name, int $i, array $r, bool $icon): string {
	ob_start(); ?>
	<div class="menu-row<?= $icon ? '' : ' menu-row--plain' ?>">
		<label class="field"><span class="sr">Order</span><input class="input menu-row__n" name="<?= e($name) ?>[<?= $i ?>][sort]" type="number" step="1" value="<?= ($i + 1) * 10 ?>" aria-label="Order"></label>
		<label class="field"><span class="sr">Label</span><input class="input" name="<?= e($name) ?>[<?= $i ?>][label]" value="<?= e((string) ($r[0] ?? '')) ?>" maxlength="60" placeholder="Label" aria-label="Label"></label>
		<label class="field"><span class="sr">Link</span><input class="input" name="<?= e($name) ?>[<?= $i ?>][url]" value="<?= e((string) ($r[1] ?? '')) ?>" maxlength="300" placeholder="/page or https://…" aria-label="Link"></label>
		<?php if ($icon): ?><label class="field"><span class="sr">Icon</span><select class="input" name="<?= e($name) ?>[<?= $i ?>][icon]" aria-label="Icon"><?php foreach (Menu::ICONS as $ic): ?><option value="<?= e($ic) ?>"<?= ($r[2] ?? 'link') === $ic ? ' selected' : '' ?>><?= e($ic) ?></option><?php endforeach; ?></select></label><?php endif; ?>
		<?php if (($r[0] ?? '') !== ''): ?><label class="menu-row__off"><input type="checkbox" name="<?= e($name) ?>[<?= $i ?>][off]" value="1"> Remove</label><?php else: ?><span class="menu-row__off help">New</span><?php endif; ?>
	</div>
	<?php return (string) ob_get_clean();
};
$list = static function (string $name, array $items, bool $icon, int $spare) use ($row): string {
	$o = '';
	foreach ($items as $i => $r) { $o .= $row($name, $i, $r, $icon); }
	for ($k = 0; $k < $spare; $k++) { $o .= $row($name, count($items) + $k, [], $icon); }
	return $o;
};
?>
<?= View::partial('hq/_head', ['h' => 'Menus', 'now' => 'menus', 'sub' => 'The links in the header, the phone menu and the footer']) ?>
	<form method="post" class="form acct-list">
		<?= Guard::field() ?>
		<section class="glass glass--plain glass--tight acct-card">
			<h2 class="h3">Main menu</h2>
			<p class="help">Shown in the desktop header and at the top of the phone menu. Keep it short: seven or eight links fit the header. Lower numbers come first. The icon shows on the phone menu.</p>
			<?= $list('main', $menus['main'], true, 2) ?>
		</section>
		<section class="glass glass--plain glass--tight acct-card">
			<h2 class="h3">Phone menu extras</h2>
			<p class="help">Shown in the phone menu after the main menu.</p>
			<?= $list('phone', $menus['phone'], true, 2) ?>
		</section>
		<?php foreach (array_merge($menus['footer'], [['', []]]) as $g => [$title, $links]): ?>
			<section class="glass glass--plain glass--tight acct-card">
				<div class="menu-group">
					<label class="field"><span><?= $title === '' ? 'New footer group: heading' : 'Footer group: heading' ?></span><input class="input" name="footer[<?= $g ?>][title]" value="<?= e($title) ?>" maxlength="40" placeholder="Heading"></label>
					<label class="field"><span>Order</span><input class="input menu-row__n" name="footer[<?= $g ?>][sort]" type="number" step="1" value="<?= ($g + 1) * 10 ?>"></label>
					<?php if ($title !== ''): ?><label class="menu-row__off"><input type="checkbox" name="footer[<?= $g ?>][off]" value="1"> Remove group</label><?php endif; ?>
				</div>
				<?= $list('footer[' . $g . '][links]', $links, false, $title === '' ? 4 : 2) ?>
			</section>
		<?php endforeach; ?>
		<div class="acct-save form-actions"><button class="pill" type="submit">Save menus</button><button class="pill pill--glass" type="submit" name="reset" value="1" formnovalidate data-confirm="Put every menu back to how it was first set up?">Reset to the original</button></div>
	</form>
</div>
