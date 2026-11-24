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
	<section class="glass glass--plain glass--tight acct-card" id="invite">
		<h2 class="h3">Let them fill in their own profiles</h2>
		<p class="help">Send this link to your bridesmaids and groomsmen. Each person adds their photo and details, then waits for you to approve them. Once you approve someone, we email them and their profile appears in The Squad on your page.</p>
		<div class="door__row"><input class="input" value="<?= e($joinUrl) ?>" readonly aria-label="Your squad link"><button class="pill pill--sm pill--glass" type="button" data-copy="<?= e($joinUrl) ?>" data-copied="Link copied"><?= icon('copy') ?>Copy</button></div>
		<div class="form-actions">
			<a class="pill pill--sm pill--wa" href="<?= e('https://wa.me/?text=' . rawurlencode('Please add your profile to The Squad on our wedding page: ' . $joinUrl)) ?>" target="_blank" rel="noopener"><?= icon('chat') ?>Share on WhatsApp</a>
			<form method="post" action="<?= e(App::url('/account/squad/link')) ?>" data-confirm="Make a new link? The old one will stop working."><?= Guard::field() ?><button class="pill pill--sm pill--glass" type="submit">Make a new link</button></form>
		</div>
	</section>

	<?php if (!empty($pending)): ?>
	<section class="gap-t" id="waiting">
		<div class="block-head"><h2 class="h2">Waiting for you (<?= count($pending) ?>)</h2></div>
		<div class="rows">
			<?php foreach ($pending as $p): ?>
				<div class="row-item rsvp-wait">
					<?php if ((string) $p['photo'] !== ''): ?><?= sq((string) $p['photo'], (string) $p['name'], 'rsvp-wait__face') ?><?php else: ?><span class="row-item__ico"><?= e(mb_strtoupper(mb_substr((string) $p['name'], 0, 1))) ?></span><?php endif; ?>
					<span>
						<span class="row-item__t"><?= e($p['name']) ?></span>
						<span class="row-item__s"><?= e(trim($p['role'] . ' · ' . ($p['side'] === 'bride' ? 'Bridesmaids' : 'Groomsmen'), ' ·')) ?></span>
						<span class="row-item__s"><?= e(trim(implode(' · ', array_filter([(string) $p['work'], (string) $p['hobby'], (string) $p['email']])))) ?></span>
						<?php if ($p['words']): ?><span class="row-item__s">“<?= e($p['words']) ?>”</span><?php endif; ?>
					</span>
					<form class="rsvp-wait__acts" method="post" action="<?= e(App::url('/account/squad/' . (int) $p['id'] . '/decide')) ?>">
						<?= Guard::field() ?>
						<button class="pill pill--sm" type="submit" name="do" value="approve">Approve</button>
						<button class="pill pill--sm pill--glass" type="submit" name="do" value="decline">Decline</button>
					</form>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<p class="help gap-t">No photo? Their card shows the first letter of their name. An @handle is treated as Instagram; for anything else, paste the link.</p>
	<div class="acct-list">
		<?php foreach ($rows as $r): ?><?= $member($r) ?><?php endforeach; ?>
		<?= $member([]) ?>
	</div>
</div>
