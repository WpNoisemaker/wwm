<?= View::partial('account/_head', ['h' => 'Cash & gift registry', 'w' => $w, 'sub' => 'Funds and gifts guests can give towards, from anywhere.']) ?>
<section class="sec wrap">
	<?php foreach ($rows as $r): ?>
		<form method="post" class="form form--card form--row">
			<?= Guard::field() ?><input type="hidden" name="id" value="<?= e($r['id']) ?>">
			<div class="form__pair">
				<span><label>Title</label><input name="title" value="<?= e($r['title']) ?>" required></span>
				<span><label>Target (<?= e($w['currency']) ?>)</label><input name="target" type="number" min="0" value="<?= e((int) $r['target']) ?>"></span>
			</div>
			<label>A note for guests</label>
			<input name="note" value="<?= e($r['note']) ?>">
			<label>Kind</label>
			<select name="kind"><?php foreach (['cash' => 'Cash fund', 'item' => 'A gift item', 'fund' => 'A named fund'] as $k => $t): ?><option value="<?= e($k) ?>"<?= (string) $r['kind'] === $k ? ' selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select>
			<p class="sub">Raised so far: <?= money(number_format((int) $r['raised']), (string) $w['currency']) ?></p>
			<div class="form__btns">
				<button class="btn btn--sm btn--acid" type="submit">Save</button>
				<button class="btn btn--sm btn--ghost" type="submit" name="do" value="delete">Remove</button>
			</div>
		</form>
	<?php endforeach; ?>
	<form method="post" class="form form--card">
		<?= Guard::field() ?>
		<h3 class="form__g">Add a fund or a gift</h3>
		<div class="form__pair">
			<span><label for="g-title">Title</label><input id="g-title" name="title" placeholder="Our honeymoon" required></span>
			<span><label for="g-target">Target</label><input id="g-target" name="target" type="number" min="0"></span>
		</div>
		<label for="g-note">A note for guests</label>
		<input id="g-note" name="note" placeholder="Zanzibar, if you can help us get there.">
		<button class="btn btn--acid" type="submit">Add</button>
	</form>
</section>
