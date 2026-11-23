<?php
/**
 * The squad: bridesmaids and groomsmen, each with a photo (or the first letter of their name),
 * what they do, a hobby, their relationship status and one social link.
 *
 * @var array $w @var array $rows
 */
$sides = ['bride' => 'Bridesmaids', 'groom' => 'Groomsmen', 'both' => 'Both'];
$member = static function (array $r) use ($sides): string {
	$id = (int) ($r['id'] ?? 0);
	$v = static fn (string $k): string => e((string) ($r[$k] ?? ''));
	ob_start(); ?>
	<form method="post" class="form glass glass--plain glass--tight acct-card" enctype="multipart/form-data">
		<?= Guard::field() ?><?php if ($id): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>
		<?php if (!$id): ?><h2 class="h3">Add someone</h2><?php endif; ?>
		<div class="squad-edit">
			<div class="squad-edit__pic">
				<?php if ((string) ($r['photo'] ?? '') !== ''): ?><?= sq((string) $r['photo'], (string) $r['name'], 'squad__pic') ?><?php else: ?><span class="squad__av" aria-hidden="true"><?= e(mb_strtoupper(mb_substr(trim((string) ($r['name'] ?? '')) ?: '+', 0, 1))) ?></span><?php endif; ?>
				<label class="photo-pick photo-pick--sm"><input type="file" name="photo" accept="image/jpeg,image/png,image/webp" data-photo-input><?= icon('camera') ?><span data-photo-name><?= (string) ($r['photo'] ?? '') !== '' ? 'Change' : 'Photo' ?></span></label>
				<?php if ((string) ($r['photo'] ?? '') !== ''): ?><label class="squad-edit__off"><input type="checkbox" name="photo_off" value="1"> Remove photo</label><?php endif; ?>
			</div>
			<div class="form-grid form-grid--2">
				<label class="field"><span>Name</span><input class="input" name="name" value="<?= $v('name') ?>" required maxlength="160"></label>
				<label class="field"><span>Role</span><input class="input" name="role" value="<?= $v('role') ?>" maxlength="120" placeholder="<?= $id ? 'Chief bridesmaid' : 'Best man' ?>"></label>
				<label class="field"><span>Squad</span><select class="input" name="side"><?php foreach ($sides as $k => $t): ?><option value="<?= e($k) ?>"<?= (string) ($r['side'] ?? 'bride') === $k ? ' selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select></label>
				<label class="field"><span>What they do</span><input class="input" name="work" value="<?= $v('work') ?>" maxlength="160" placeholder="Architect in Lagos"></label>
				<label class="field"><span>A hobby</span><input class="input" name="hobby" value="<?= $v('hobby') ?>" maxlength="160" placeholder="Afrobeats and long drives"></label>
				<label class="field"><span>Relationship status</span><select class="input" name="relationship"><?php foreach (AccountController::RELATIONSHIP as $k => $t): ?><option value="<?= e($k) ?>"<?= (string) ($r['relationship'] ?? '') === $k ? ' selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select></label>
				<label class="field"><span>One social link</span><input class="input" name="social" value="<?= $v('social') ?>" maxlength="255" placeholder="@handle or a link to their profile" inputmode="url"></label>
				<label class="field"><span>A line about them</span><input class="input" name="words" value="<?= $v('words') ?>" maxlength="240"></label>
			</div>
		</div>
		<?php if ($id): ?>
			<div class="form-actions"><button class="pill pill--sm" type="submit">Save</button><button class="pill pill--sm pill--danger" type="submit" name="do" value="delete" formnovalidate>Remove</button></div>
		<?php else: ?>
			<button class="pill pill--block" type="submit">Add to the squad</button>
		<?php endif; ?>
	</form>
	<?php return (string) ob_get_clean();
};
?>
<?= View::partial('account/_head', ['h' => 'The squad', 'w' => $w, 'sub' => 'Your bridesmaids and groomsmen', 'now' => 'squad']) ?>
	<?php if (!Wedding::has($w, 'squad')): ?>
		<p class="note"><?= icon('lock') ?><span>The squad shows on your page from the Pro plan upwards. Fill it in now and it will appear when you upgrade. </span><?= up_tag('pro', 'The squad') ?></p>
	<?php endif; ?>
	<p class="help">No photo? Their card shows the first letter of their name. An @handle is treated as Instagram; for anything else, paste the link.</p>
	<div class="acct-list">
		<?php foreach ($rows as $r): ?><?= $member($r) ?><?php endforeach; ?>
		<?= $member([]) ?>
	</div>
</div>
