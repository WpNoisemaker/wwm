<?php /** The owner's staff list: what each person can reach, changeable any time. @var array $rows */ ?>
<?= View::partial('hq/_head', ['h' => 'Staff', 'sub' => 'Who can do what in HQ', 'now' => 'staff']) ?>
	<div class="split-2 gap-t-sm">
		<div class="acct-list">
			<?php if (!$rows): ?>
				<div class="glass glass--plain empty"><span class="cbtn"><?= icon('people') ?></span><p>No staff yet. Add someone and choose what they can do.</p></div>
			<?php endif; ?>
			<?php foreach ($rows as $r): ?>
				<form method="post" class="form glass glass--plain glass--tight acct-card">
					<?= Guard::field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
					<div class="row row--between"><div><p class="row-item__t"><?= e((string) $r['name']) ?></p><p class="row-item__s"><?= e((string) $r['email']) ?><?= $r['last_seen_at'] ? ' · last signed in ' . e(ago((string) $r['last_seen_at'])) : ' · has not signed in yet' ?></p></div></div>
					<div class="perm-grid">
						<?php foreach (Staff::PERMS as $k => [$label, $what]): ?>
							<label class="check perm"><span><b><?= e($label) ?></b><small><?= e($what) ?></small></span><input type="checkbox" name="perm[<?= e($k) ?>]" value="1"<?= in_array($k, $r['perms'], true) ? ' checked' : '' ?>></label>
						<?php endforeach; ?>
					</div>
					<div class="form-actions"><button class="pill pill--sm" type="submit" name="do" value="save">Save access</button><button class="pill pill--sm pill--danger" type="submit" name="do" value="remove" data-confirm="Remove <?= e((string) $r['name']) ?> from staff? Their account stays, but without HQ access.">Remove from staff</button></div>
				</form>
			<?php endforeach; ?>
		</div>
		<aside>
			<form method="post" class="form glass glass--tight">
				<?= Guard::field() ?><input type="hidden" name="do" value="add">
				<h2 class="h3">Add someone</h2>
				<label class="field"><span>Name</span><input class="input" name="name" autocomplete="off"></label>
				<label class="field"><span>Email</span><input class="input" name="email" type="email" required autocomplete="off"></label>
				<p class="help">What they can do</p>
				<div class="perm-grid perm-grid--one">
					<?php foreach (Staff::PERMS as $k => [$label, $what]): ?>
						<label class="check perm"><span><b><?= e($label) ?></b><small><?= e($what) ?></small></span><input type="checkbox" name="perm[<?= e($k) ?>]" value="1"<?= in_array($k, ['weddings', 'blog'], true) ? ' checked' : '' ?>></label>
					<?php endforeach; ?>
				</div>
				<button class="pill pill--block" type="submit">Add to staff</button>
				<p class="help">They get an email with a link to set their own password. If they already have an account, it simply gains HQ access.</p>
			</form>
			<p class="help gap-t-sm">Only you, the owner, can add staff, change their access, add or remove the demo, and restore pages from the old site.</p>
		</aside>
	</div>
</div>
