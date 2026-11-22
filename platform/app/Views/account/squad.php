<?= View::partial('account/_head', ['h' => 'The squad', 'w' => $w, 'sub' => 'Bridesmaids, groomsmen and everyone standing with you.']) ?>
<section class="sec wrap">
	<?php if (!Wedding::has($w, 'squad')): ?>
		<p class="locked"><?= icon('lock', 14) ?> The squad shows on your page from Pro. You can fill it in now and it appears when you upgrade. <a href="<?= e(App::url('/pricing')) ?>">See the plans</a></p>
	<?php endif; ?>
	<?php foreach ($rows as $r): ?>
		<form method="post" class="form form--card form--row">
			<?= Guard::field() ?><input type="hidden" name="id" value="<?= e($r['id']) ?>">
			<div class="form__pair">
				<span><label>Name</label><input name="name" value="<?= e($r['name']) ?>" required></span>
				<span><label>Role</label><input name="role" value="<?= e($r['role']) ?>" placeholder="Chief bridesmaid"></span>
			</div>
			<label>Side</label>
			<select name="side"><?php foreach (['bride' => 'Bride', 'groom' => 'Groom', 'both' => 'Both'] as $k => $t): ?><option value="<?= e($k) ?>"<?= (string) $r['side'] === $k ? ' selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select>
			<label>A line about them</label>
			<input name="words" value="<?= e($r['words']) ?>">
			<div class="form__btns">
				<button class="btn btn--sm btn--acid" type="submit">Save</button>
				<button class="btn btn--sm btn--ghost" type="submit" name="do" value="delete">Remove</button>
			</div>
		</form>
	<?php endforeach; ?>
	<form method="post" class="form form--card">
		<?= Guard::field() ?>
		<h3 class="form__g">Add someone</h3>
		<div class="form__pair">
			<span><label for="s-name">Name</label><input id="s-name" name="name" required></span>
			<span><label for="s-role">Role</label><input id="s-role" name="role"></span>
		</div>
		<label for="s-side">Side</label>
		<select id="s-side" name="side"><option value="bride">Bride</option><option value="groom">Groom</option><option value="both">Both</option></select>
		<label for="s-words">A line about them</label>
		<input id="s-words" name="words">
		<button class="btn btn--acid" type="submit">Add</button>
	</form>
</section>
