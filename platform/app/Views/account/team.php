<?= View::partial('account/_head', ['h' => 'Your team', 'w' => $w, 'sub' => 'People who help with your page, each with only the access they need', 'now' => 'team']) ?>
	<div class="acct-list">
		<?php if ($rows): ?>
			<div class="rows">
				<?php foreach ($rows as $t): $perms = array_filter(explode(',', (string) $t['perms'])); ?>
					<details class="row-item team-row">
						<summary>
							<span class="row-item__ico"><?= icon($t['role'] === 'bouncer' ? 'shield' : ($t['role'] === 'vendor' ? 'store' : 'people')) ?></span>
							<span><span class="row-item__t"><?= e($t['name'] ?: $t['email']) ?></span>
								<span class="row-item__s"><?= e(Team::ROLES[$t['role']]) ?><?= $t['role'] === 'vendor' && $t['vendor_name'] ? ' · ' . e($t['vendor_name']) : '' ?> · <?= $t['status'] === 'active' ? 'Active' : 'Invite sent, not accepted yet' ?></span>
								<span class="row-item__s"><?= $t['role'] === 'planner' ? ($perms ? 'Can open: ' . e(implode(', ', array_map(static fn ($p) => explode(':', Team::AREAS[$p] ?? $p)[0], $perms))) : 'Can open the overview only') : ($t['role'] === 'bouncer' ? 'Can see the guest list at the door and check guests in and out' : 'Can reply to the reviews guests leave for them on your page') ?></span>
							</span>
							<span class="row-item__end"><?= icon('chev-down') ?></span>
						</summary>
						<form method="post" action="<?= e(App::url('/account/team/' . (int) $t['id'])) ?>" class="form team-row__form">
							<?= Guard::field() ?>
							<?php if ($t['role'] === 'planner'): ?>
								<fieldset class="checks"><legend class="help">What they can open</legend>
									<?php foreach (Team::AREAS as $k => $label): ?><label class="check"><span><?= e($label) ?></span><input type="checkbox" name="perms[]" value="<?= e($k) ?>"<?= in_array($k, $perms, true) ? ' checked' : '' ?>></label><?php endforeach; ?>
								</fieldset>
								<button class="pill pill--sm" type="submit">Save access</button>
							<?php endif; ?>
							<button class="pill pill--sm pill--glass" type="submit" name="do" value="remove" data-confirm="Remove <?= e($t['name'] ?: $t['email']) ?> from your page?">Remove</button>
						</form>
					</details>
				<?php endforeach; ?>
			</div>
		<?php else: ?>
			<p class="help">Nobody is on your team yet. Invite your event planner, the people at your door and your vendors below. Each person gets an email with a link to accept.</p>
		<?php endif; ?>

		<form method="post" class="form glass glass--tight acct-card" data-team-form>
			<?= Guard::field() ?>
			<h2 class="h3">Invite someone</h2>
			<fieldset class="radios radios--3" aria-label="What will they do?">
				<?php foreach (Team::ROLES as $k => $label): ?><label><input type="radio" name="role" value="<?= e($k) ?>"<?= $k === 'planner' ? ' checked' : '' ?>><?= e($label) ?></label><?php endforeach; ?>
			</fieldset>
			<div class="form-grid form-grid--2">
				<label class="field"><span>Their name</span><input class="input" name="name" maxlength="160" autocomplete="off"></label>
				<label class="field"><span>Their email</span><input class="input" name="email" type="email" required maxlength="190" autocomplete="off"></label>
			</div>
			<fieldset class="checks" data-team-for="planner"><legend class="help">What your planner can open</legend>
				<?php foreach (Team::AREAS as $k => $label): ?><label class="check"><span><?= e($label) ?></span><input type="checkbox" name="perms[]" value="<?= e($k) ?>"<?= in_array($k, ['programs', 'guests', 'vendors'], true) ? ' checked' : '' ?>></label><?php endforeach; ?>
				<p class="help">Your bank details, payouts and account settings always stay with you.</p>
			</fieldset>
			<p class="help" data-team-for="bouncer" hidden>A bouncer sees only the list of approved guests with their photos and codes, and checks people in and out at the door.</p>
			<div data-team-for="vendor" hidden>
				<?php if ($vendors): ?>
					<label class="field"><span>Which vendor</span><select class="input" name="vendor_id"><?php foreach ($vendors as $v): ?><option value="<?= (int) $v['id'] ?>"><?= e($v['name']) ?></option><?php endforeach; ?></select></label>
					<p class="help">The vendor can only reply to the reviews your guests leave for them on your page.</p>
				<?php else: ?>
					<p class="help">Add your vendors under <a href="<?= e(App::url('/account/vendors')) ?>">Vendors</a> first, then invite them here.</p>
				<?php endif; ?>
			</div>
			<button class="pill pill--block" type="submit">Send the invite</button>
		</form>
	</div>
</div>
