<?= View::partial('account/_head', ['h' => 'Cash & gift registry', 'w' => $w, 'sub' => 'Funds and gifts guests can give towards', 'now' => 'registry']) ?>
	<div class="acct-list">
		<?php foreach ($rows as $r): $pct = (int) $r['target'] > 0 ? min(100, (int) round((int) $r['raised'] / (int) $r['target'] * 100)) : 0; ?>
			<form method="post" class="form glass glass--plain glass--tight acct-card">
				<?= Guard::field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
				<div class="form-grid form-grid--2">
					<label class="field"><span>Title</span><input class="input" name="title" value="<?= e($r['title']) ?>" required></label>
					<label class="field"><span>Target (<?= e($w['currency']) ?>)</span><input class="input" name="target" type="number" min="0" inputmode="numeric" value="<?= (int) $r['target'] ?>"></label>
					<label class="field"><span>A note for guests</span><input class="input" name="note" value="<?= e($r['note']) ?>"></label>
					<label class="field"><span>Kind</span><select class="input" name="kind"><?php foreach (['cash' => 'Cash fund', 'item' => 'A gift item', 'fund' => 'A named fund'] as $k => $t): ?><option value="<?= e($k) ?>"<?= (string) $r['kind'] === $k ? ' selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select></label>
				</div>
				<?= meter($pct, 'Raised: ' . short_money((int) $r['raised'])) ?>
				<div class="form-actions"><button class="pill pill--sm" type="submit">Save</button><button class="pill pill--sm pill--danger" type="submit" name="do" value="delete">Remove</button></div>
			</form>
		<?php endforeach; ?>
		<form method="post" class="form glass glass--tight acct-card">
			<?= Guard::field() ?>
			<h2 class="h3">Add a fund or a gift</h2>
			<div class="form-grid form-grid--2">
				<label class="field"><span>Title</span><input class="input" name="title" placeholder="Our honeymoon" required></label>
				<label class="field"><span>Target</span><input class="input" name="target" type="number" min="0" inputmode="numeric"></label>
				<label class="field span-2"><span>A note for guests</span><input class="input" name="note" placeholder="Zanzibar, if you can help us get there."></label>
			</div>
			<button class="pill pill--block" type="submit">Add</button>
		</form>
	</div>
</div>
