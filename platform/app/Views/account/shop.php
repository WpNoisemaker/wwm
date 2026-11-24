<?php
/**
 * A seller's shop: the business profile couples see, then every listing, each editable in place,
 * and a form to add another.
 *
 * @var ?array $v @var array $rows
 */
// Sellers list only in the categories our team has opened. A listing already in one that has since
// closed keeps it.
$kindSelect = static function (string $on): string {
	$o = '';
	$open = Market::categories(true);
	if ($on !== '' && !in_array($on, array_column($open, 'slug'), true)) {
		$o .= '<option value="' . e($on) . '" selected>' . e(Market::catName($on)) . ' (closed to new listings)</option>';
	}
	foreach ($open as $c) {
		$o .= '<option value="' . e((string) $c['slug']) . '"' . ($c['slug'] === $on ? ' selected' : '') . '>' . e((string) $c['name']) . '</option>';
	}
	return $o;
};
// The photos a listing has: each can be made the cover or removed.
$photoManager = static function (array $l): string {
	$ph = Listing::photos($l);
	if (!$ph) {
		return '';
	}
	$o = '<div class="field span-2"><span>Photos (' . count($ph) . ' of 8): tap the star to choose the cover</span><div class="ph-grid">';
	foreach ($ph as $i => $p) {
		$o .= '<div class="ph-item">' . photo($p, '') . '<label class="ph-item__cover" title="Make this the cover"><input type="radio" name="cover" value="' . e($p) . '"' . ($i === 0 ? ' checked' : '') . '><span>' . icon('star') . '</span></label>'
			. '<label class="ph-item__drop" title="Remove this photo"><input type="checkbox" name="drop[]" value="' . e($p) . '"><span>' . icon('close') . '</span></label></div>';
	}
	return $o . '</div></div>';
};
?>
<?= View::partial('account/_head', ['h' => $v ? (string) $v['name'] : 'Your shop', 'sub' => 'Your seller shop', 'now' => 'shop']) ?>
	<?php if (!$v): ?>
		<p class="note note--bad"><?= icon('store') ?><span>Fill in your business profile before you list anything. Your logo, name and city show on every listing, and buyers open your profile from there.</span><a class="pill pill--sm" href="<?= e(App::url('/account/shop/profile')) ?>">Fill it in</a></p>
	<?php endif; ?>
	<div class="split-2">
		<div class="acct-list">
			<?php foreach ($rows as $l): $ph = first_photo((string) $l['photos']); ?>
				<form method="post" enctype="multipart/form-data" class="form glass glass--plain glass--tight acct-card">
					<?= Guard::field() ?><input type="hidden" name="do" value="listing"><input type="hidden" name="id" value="<?= (int) $l['id'] ?>">
					<div class="shop-head">
						<?= sq($ph, '', 'shop-head__pic') ?>
						<div><p class="row-item__t"><?= e($l['title']) ?></p><p class="row-item__s"><?= $l['status'] === 'live' ? 'Live' : 'Paused' ?> · <a class="linkish" href="<?= e(App::url('/market/' . $l['slug'])) ?>">See it</a></p></div>
					</div>
					<div class="form-grid form-grid--2">
						<label class="field span-2"><span>Title</span><input class="input" name="title" value="<?= e($l['title']) ?>" required maxlength="190"></label>
						<label class="field"><span>Kind</span><select class="input" name="kind"><?= $kindSelect((string) $l['kind']) ?></select></label>
						<label class="field"><span>City</span><input class="input" name="city" value="<?= e($l['city']) ?>"></label>
						<label class="field"><span>Price in naira</span><input class="input" name="price" inputmode="numeric" value="<?= (int) $l['price'] ?: '' ?>"></label>
						<label class="field"><span>Per</span><input class="input" name="price_unit" value="<?= e($l['price_unit']) ?>" placeholder="night, day, trip, person"></label>
						<label class="check span-2"><span>Show &ldquo;price on request&rdquo; instead</span><input type="checkbox" name="on_ask" value="1"<?= (int) $l['on_ask'] ? ' checked' : '' ?>></label>
						<label class="field span-2"><span>About it</span><textarea class="input" name="about" rows="5"><?= e((string) $l['about']) ?></textarea></label>
						<label class="field"><span>Status</span><select class="input" name="status"><option value="live"<?= $l['status'] === 'live' ? ' selected' : '' ?>>Live</option><option value="paused"<?= $l['status'] !== 'live' ? ' selected' : '' ?>>Paused</option></select></label>
						<?= $photoManager($l) ?>
						<?php if (count(Listing::photos($l)) < 3): ?><p class="help span-2">Add a few more photos. Buyers swipe through every photo on your listing card, and listings with several photos get more messages.</p><?php endif; ?>
						<label class="photo-pick span-2"><input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple data-photo-input><?= icon('camera') ?><span data-photo-name>Add photos (you can choose several at once)</span></label>
					</div>
					<div class="form-actions"><button class="pill pill--sm" type="submit">Save</button><button class="pill pill--sm pill--danger" type="submit" name="do" value="delete" data-confirm="Remove this listing from the marketplace?">Remove</button></div>
				</form>
			<?php endforeach; ?>
			<form method="post" enctype="multipart/form-data" class="form glass glass--tight acct-card">
				<?= Guard::field() ?><input type="hidden" name="do" value="listing">
				<h2 class="h3">Add a listing</h2>
				<div class="form-grid form-grid--2">
					<label class="field span-2"><span>Title</span><input class="input" name="title" required maxlength="190" placeholder="3-bed apartment in Lekki Phase 1"></label>
					<label class="field"><span>Kind</span><select class="input" name="kind"><?= $kindSelect('service') ?></select></label>
					<label class="field"><span>City</span><input class="input" name="city" value="<?= e((string) ($v['city'] ?? '')) ?>"></label>
					<label class="field"><span>Price in naira</span><input class="input" name="price" inputmode="numeric"></label>
					<label class="field"><span>Per</span><input class="input" name="price_unit" placeholder="night, day, trip, person"></label>
					<label class="check span-2"><span>Show &ldquo;price on request&rdquo; instead</span><input type="checkbox" name="on_ask" value="1"></label>
					<label class="field span-2"><span>About it</span><textarea class="input" name="about" rows="4"></textarea></label>
					<label class="photo-pick span-2"><input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple data-photo-input><?= icon('camera') ?><span data-photo-name>Photos: choose up to 8 at once</span></label>
				</div>
				<button class="pill pill--block" type="submit">Add listing</button>
			</form>
		</div>
		<aside class="acct-list">
			<div class="form glass glass--plain glass--tight shop-card">
				<?php if ($v): ?>
					<div class="shop-head"><?= sq((string) $v['logo'], '', 'shop-head__pic') ?><div><p class="row-item__t"><?= e((string) $v['name']) ?></p><p class="row-item__s"><?= e(trim((string) $v['category'] . ((string) $v['city'] !== '' ? ' · ' . $v['city'] : ''), ' ·')) ?></p></div></div>
					<?php if ((int) $v['verified']): ?><p><span class="badge badge--solid"><?= icon('check') ?>Verified seller</span></p><?php else: ?><p class="help">Our team verifies sellers after checking their business.</p><?php endif; ?>
					<p class="help"><?= count($rows) ?> listing<?= count($rows) === 1 ? '' : 's' ?> · <?= count(array_filter($rows, static fn ($r) => $r['status'] === 'live')) ?> live</p>
					<a class="pill pill--glass pill--block" href="<?= e(App::url('/vendors/' . $v['slug'])) ?>">See your public profile</a>
					<a class="pill pill--glass pill--block" href="<?= e(App::url('/account/shop/profile')) ?>">Edit your business profile</a>
				<?php else: ?>
					<h2 class="h3">Set up your shop</h2>
					<p class="help">Add your business name, what you do and how couples can reach you. Then add your first listing.</p>
					<a class="pill pill--block" href="<?= e(App::url('/account/shop/profile')) ?>">Set up your business profile</a>
				<?php endif; ?>
			</div>
			<?php if ($v && !Market::active($v)): ?><p class="note note--bad"><?= icon('lock') ?><span>Your shop is paused while we verify your business. We emailed you what we need.</span></p><?php endif; ?>
			<p class="help">You can list in these categories: <?= e(implode(', ', array_column(Market::categories(true), 'name'))) ?>. Our team opens new categories.</p>
			<p class="help">Signing in with an @example.com email? Add your own address under <a class="linkish" href="<?= e(App::url('/account/settings')) ?>">Account</a>.</p>
		</aside>
	</div>
</div>
