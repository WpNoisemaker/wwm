<?php
/**
 * The couple's squad link: a bridesmaid or groomsman adds their own profile. It waits for the couple.
 * @var array $w @var string $names @var bool $done @var string $key
 */
$old = static fn (string $k): string => e(Guard::old($k));
?>
<div class="page wrap squad-join">
	<header class="page-head"><span class="eyebrow"><?= e($names) ?></span><h1>Join the squad</h1></header>
	<?= flashes() ?>
	<?php if ($done): ?>
		<div class="glass glass--plain empty">
			<span class="cbtn"><?= icon('check') ?></span>
			<p>Thank you. <?= e($names) ?> will look at your profile. When they approve it, we will email you and it will appear in The Squad on their page.</p>
			<a class="pill pill--glass" href="<?= e(App::url('/w/' . $w['slug'])) ?>">See their wedding page</a>
		</div>
	<?php else: ?>
		<p class="help center"><?= e($names) ?> asked you to add your own profile to The Squad on their wedding page. They will approve it before it goes live.</p>
		<form method="post" enctype="multipart/form-data" class="form glass glass--plain glass--tight acct-card squad-join__form">
			<?= Guard::field() ?>
			<fieldset class="radios" aria-label="You are a">
				<label><input type="radio" name="side" value="bride"<?= Guard::old('side') === 'bride' ? ' checked' : '' ?> required>Bridesmaid</label>
				<label><input type="radio" name="side" value="groom"<?= Guard::old('side') === 'groom' ? ' checked' : '' ?>>Groomsman</label>
			</fieldset>
			<label class="photo-pick"><input type="file" name="photo" accept="image/jpeg,image/png,image/webp" data-photo-input><?= icon('camera') ?><span data-photo-name>Add a photo of yourself</span></label>
			<div class="form-grid form-grid--2">
				<label class="field"><span>Your name</span><input class="input" name="name" required maxlength="160" autocomplete="name" value="<?= $old('name') ?>"></label>
				<label class="field"><span>Your email (we tell you when you are approved)</span><input class="input" name="email" type="email" required maxlength="190" autocomplete="email" value="<?= $old('email') ?>"></label>
				<label class="field"><span>Your role (optional)</span><input class="input" name="role" maxlength="120" placeholder="Chief bridesmaid, best man…" value="<?= $old('role') ?>"></label>
				<label class="field"><span>What you do</span><input class="input" name="work" maxlength="160" placeholder="Pharmacist" value="<?= $old('work') ?>"></label>
				<label class="field"><span>A hobby</span><input class="input" name="hobby" maxlength="160" placeholder="Afrobeats and long drives" value="<?= $old('hobby') ?>"></label>
				<label class="field"><span>Relationship status</span><select class="input" name="relationship"><?php foreach (AccountController::RELATIONSHIP as $k => $t): ?><option value="<?= e($k) ?>"<?= Guard::old('relationship') === $k ? ' selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select></label>
				<label class="field"><span>One social link (optional)</span><input class="input" name="social" maxlength="255" placeholder="@handle or a link" inputmode="url" value="<?= $old('social') ?>"></label>
				<label class="field"><span>A line about you (optional)</span><input class="input" name="words" maxlength="240" placeholder="Fondly called Timah" value="<?= $old('words') ?>"></label>
			</div>
			<button class="pill pill--block" type="submit">Send my profile</button>
			<p class="after-pill">Your email is not shown on the page. It is only used to tell you when you are approved.</p>
		</form>
	<?php endif; ?>
</div>
