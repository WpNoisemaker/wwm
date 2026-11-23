<?php
/** A seller's business profile: what couples see on the public page. @var ?array $v */
?>
<?= View::partial('account/_head', ['h' => 'Business profile', 'sub' => $v ? (string) $v['name'] : 'Your seller shop', 'now' => 'shop-profile']) ?>
	<div class="split-2">
		<form method="post" action="<?= e(App::url('/account/shop')) ?>" class="form glass glass--plain glass--tight acct-card">
			<?= Guard::field() ?><input type="hidden" name="do" value="profile">
			<div class="row row--between"><h2 class="h3">What couples see</h2><?php if ($v && (int) $v['verified']): ?><span class="badge badge--solid"><?= icon('check') ?>Verified</span><?php endif; ?></div>
			<div class="form-grid form-grid--2">
				<label class="field span-2"><span>Business name</span><input class="input" name="name" value="<?= e((string) ($v['name'] ?? '')) ?>" required></label>
				<label class="field span-2"><span>What you do</span><input class="input" name="category" value="<?= e((string) ($v['category'] ?? '')) ?>" placeholder="Car hire, stays, aso-ebi, home organising"></label>
				<label class="field"><span>City</span><input class="input" name="city" value="<?= e((string) ($v['city'] ?? '')) ?>"></label>
				<label class="field"><span>State</span><input class="input" name="state" value="<?= e((string) ($v['state'] ?? '')) ?>"></label>
				<label class="field"><span>Phone</span><input class="input" name="phone" type="tel" value="<?= e((string) ($v['phone'] ?? '')) ?>"></label>
				<label class="field"><span>Instagram</span><input class="input" name="instagram" value="<?= e((string) ($v['instagram'] ?? '')) ?>" placeholder="yourhandle"></label>
				<label class="field span-2"><span>Website</span><input class="input" name="website" value="<?= e((string) ($v['website'] ?? '')) ?>"></label>
				<label class="field span-2"><span>About your business</span><textarea class="input" name="about" rows="6"><?= e((string) ($v['about'] ?? '')) ?></textarea></label>
			</div>
			<button class="pill pill--block" type="submit">Save profile</button>
		</form>
		<aside class="form glass glass--plain glass--tight">
			<h2 class="h3">Your public page</h2>
			<?php if ($v): ?>
				<?= sq((string) $v['logo'], (string) $v['name'], 'sq--wide') ?>
				<p class="help">Couples find you in Vendors and on every listing you post.</p>
				<a class="pill pill--glass pill--block" href="<?= e(App::url('/vendors/' . $v['slug'])) ?>">Open it</a>
			<?php else: ?>
				<p class="help">Save your profile and it appears in Vendors, with your listings.</p>
			<?php endif; ?>
			<p class="help"><?= $v && (int) $v['verified'] ? 'Verified by Wedding Websites &amp; More.' : 'HQ verifies sellers once they have checked the business.' ?></p>
		</aside>
	</div>
</div>
