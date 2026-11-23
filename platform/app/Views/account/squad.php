<?= View::partial('account/_head', ['h' => 'The squad', 'w' => $w, 'sub' => 'Everyone standing with you', 'now' => 'squad']) ?>
	<?php if (!Wedding::has($w, 'squad')): ?>
		<p class="note"><?= icon('lock') ?><span>The squad shows on your page from Pro. Fill it in now and it appears when you upgrade. </span><?= up_tag('pro', 'The squad') ?></p>
	<?php endif; ?>
	<div class="acct-list">
		<?php foreach ($rows as $r): ?>
			<form method="post" class="form glass glass--plain glass--tight acct-card">
				<?= Guard::field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
				<div class="form-grid form-grid--2">
					<label class="field"><span>Name</span><input class="input" name="name" value="<?= e($r['name']) ?>" required></label>
					<label class="field"><span>Role</span><input class="input" name="role" value="<?= e($r['role']) ?>" placeholder="Chief bridesmaid"></label>
					<label class="field"><span>Side</span><select class="input" name="side"><?php foreach (['bride' => 'Bride', 'groom' => 'Groom', 'both' => 'Both'] as $k => $t): ?><option value="<?= e($k) ?>"<?= (string) $r['side'] === $k ? ' selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select></label>
					<label class="field"><span>A line about them</span><input class="input" name="words" value="<?= e($r['words']) ?>"></label>
				</div>
				<div class="form-actions"><button class="pill pill--sm" type="submit">Save</button><button class="pill pill--sm pill--danger" type="submit" name="do" value="delete">Remove</button></div>
			</form>
		<?php endforeach; ?>
		<form method="post" class="form glass glass--tight acct-card">
			<?= Guard::field() ?>
			<h2 class="h3">Add someone</h2>
			<div class="form-grid form-grid--2">
				<label class="field"><span>Name</span><input class="input" name="name" required></label>
				<label class="field"><span>Role</span><input class="input" name="role" placeholder="Best man"></label>
				<label class="field"><span>Side</span><select class="input" name="side"><option value="bride">Bride</option><option value="groom">Groom</option><option value="both">Both</option></select></label>
				<label class="field"><span>A line about them</span><input class="input" name="words"></label>
			</div>
			<button class="pill pill--block" type="submit">Add</button>
		</form>
	</div>
</div>
