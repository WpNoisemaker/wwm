<?php
/**
 * A seller's shop: the business profile couples see, then every listing, each editable in place,
 * and a form to add another.
 *
 * @var ?array $v @var array $rows
 */
$kindSelect = static function (string $on): string {
	$o = '';
	foreach (Listing::KINDS as $k => $label) {
		$o .= '<option value="' . e($k) . '"' . ($k === $on ? ' selected' : '') . '>' . e($label) . '</option>';
	}
	return $o;
};
?>
<?= View::partial('account/_head', ['h' => $v ? (string) $v['name'] : 'Your shop', 'sub' => 'Your shop on the marketplace']) ?>
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
						<label class="field"><span>Add a photo</span><input class="input" type="file" name="photo" accept="image/jpeg,image/png,image/webp"></label>
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
					<label class="field span-2"><span>A photo</span><input class="input" type="file" name="photo" accept="image/jpeg,image/png,image/webp"></label>
				</div>
				<button class="pill pill--block" type="submit">Add listing</button>
			</form>
		</div>
		<aside>
			<form method="post" class="form glass glass--plain glass--tight">
				<?= Guard::field() ?><input type="hidden" name="do" value="profile">
				<div class="row row--between"><h2 class="h3">Your profile</h2><?php if ($v && (int) $v['verified']): ?><span class="badge badge--solid"><?= icon('check') ?>Verified</span><?php endif; ?></div>
				<label class="field"><span>Business name</span><input class="input" name="name" value="<?= e((string) ($v['name'] ?? '')) ?>" required></label>
				<label class="field"><span>What you do</span><input class="input" name="category" value="<?= e((string) ($v['category'] ?? '')) ?>" placeholder="Car hire, stays, aso-ebi"></label>
				<div class="form-grid form-grid--2">
					<label class="field"><span>City</span><input class="input" name="city" value="<?= e((string) ($v['city'] ?? '')) ?>"></label>
					<label class="field"><span>State</span><input class="input" name="state" value="<?= e((string) ($v['state'] ?? '')) ?>"></label>
				</div>
				<label class="field"><span>Phone</span><input class="input" name="phone" type="tel" value="<?= e((string) ($v['phone'] ?? '')) ?>"></label>
				<label class="field"><span>Instagram</span><input class="input" name="instagram" value="<?= e((string) ($v['instagram'] ?? '')) ?>" placeholder="yourhandle"></label>
				<label class="field"><span>Website</span><input class="input" name="website" value="<?= e((string) ($v['website'] ?? '')) ?>"></label>
				<label class="field"><span>About your business</span><textarea class="input" name="about" rows="5"><?= e((string) ($v['about'] ?? '')) ?></textarea></label>
				<button class="pill pill--block" type="submit">Save profile</button>
				<p class="help"><?= $v && (int) $v['verified'] ? 'Verified by Wedding Websites &amp; More.' : 'HQ verifies sellers once they have checked the business.' ?><?php if ($v): ?> <a class="linkish" href="<?= e(App::url('/vendors/' . $v['slug'])) ?>">See your public profile</a><?php endif; ?></p>
			</form>
			<p class="help gap-t-sm">Signing in with an @example.com email? Put your own in <a class="linkish" href="<?= e(App::url('/account/settings')) ?>">Settings</a>.</p>
		</aside>
	</div>
</div>
