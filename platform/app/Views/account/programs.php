<?= View::partial('account/_head', ['h' => 'Programme', 'w' => $w, 'sub' => 'Introduction, traditional, nikkah, church, reception', 'now' => 'programs']) ?>
	<div class="acct-list">
		<?php foreach ($rows as $r): ?>
			<form method="post" class="form glass glass--plain glass--tight acct-card">
				<?= Guard::field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
				<div class="form-grid form-grid--2">
					<label class="field"><span>Title</span><input class="input" name="title" value="<?= e($r['title']) ?>" required></label>
					<label class="field"><span>When</span><input class="input" name="starts_at" type="datetime-local" value="<?= e($r['starts_at'] ? date('Y-m-d\TH:i', (int) strtotime((string) $r['starts_at'])) : '') ?>"></label>
					<label class="field"><span>Venue</span><input class="input" name="venue" value="<?= e($r['venue']) ?>"></label>
					<label class="field"><span>Dress code</span><input class="input" name="dress" value="<?= e($r['dress']) ?>"></label>
				</div>
				<label class="check"><span>By invitation only</span><input type="checkbox" name="is_private" <?= $r['is_private'] ? 'checked' : '' ?>></label>
				<div class="form-actions"><button class="pill pill--sm" type="submit">Save</button><button class="pill pill--sm pill--danger" type="submit" name="do" value="delete">Remove</button></div>
			</form>
		<?php endforeach; ?>
		<form method="post" class="form glass glass--tight acct-card">
			<?= Guard::field() ?>
			<h2 class="h3">Add a program</h2>
			<div class="form-grid form-grid--2">
				<label class="field"><span>Title</span><input class="input" name="title" placeholder="Traditional wedding" required></label>
				<label class="field"><span>When</span><input class="input" name="starts_at" type="datetime-local"></label>
				<label class="field"><span>Venue</span><input class="input" name="venue"></label>
				<label class="field"><span>Dress code</span><input class="input" name="dress" placeholder="Gold and burgundy"></label>
			</div>
			<label class="check"><span>By invitation only</span><input type="checkbox" name="is_private"></label>
			<button class="pill pill--block" type="submit">Add it</button>
		</form>
	</div>
</div>
