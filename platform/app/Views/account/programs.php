<?= View::partial('account/_head', ['h' => 'Programs', 'w' => $w, 'sub' => 'The introduction, traditional, nikkah, church, reception — each with its own details.']) ?>
<section class="sec wrap">
	<?php foreach ($rows as $r): ?>
		<form method="post" class="form form--card form--row">
			<?= Guard::field() ?><input type="hidden" name="id" value="<?= e($r['id']) ?>">
			<div class="form__pair">
				<span><label>Title</label><input name="title" value="<?= e($r['title']) ?>" required></span>
				<span><label>When</label><input name="starts_at" type="datetime-local" value="<?= e($r['starts_at'] ? date('Y-m-d\TH:i', (int) strtotime((string) $r['starts_at'])) : '') ?>"></span>
			</div>
			<div class="form__pair">
				<span><label>Venue</label><input name="venue" value="<?= e($r['venue']) ?>"></span>
				<span><label>Dress code</label><input name="dress" value="<?= e($r['dress']) ?>"></span>
			</div>
			<label class="check"><input type="checkbox" name="is_private" <?= $r['is_private'] ? 'checked' : '' ?>> <span>By invitation only — hide the venue and keep it off the public RSVP</span></label>
			<div class="form__btns">
				<button class="btn btn--sm btn--acid" type="submit">Save</button>
				<button class="btn btn--sm btn--ghost" type="submit" name="do" value="delete">Remove</button>
			</div>
		</form>
	<?php endforeach; ?>

	<form method="post" class="form form--card">
		<?= Guard::field() ?>
		<h3 class="form__g">Add a program</h3>
		<div class="form__pair">
			<span><label for="n-title">Title</label><input id="n-title" name="title" placeholder="Traditional wedding" required></span>
			<span><label for="n-when">When</label><input id="n-when" name="starts_at" type="datetime-local"></span>
		</div>
		<div class="form__pair">
			<span><label for="n-venue">Venue</label><input id="n-venue" name="venue"></span>
			<span><label for="n-dress">Dress code</label><input id="n-dress" name="dress" placeholder="Gold and burgundy"></span>
		</div>
		<label class="check"><input type="checkbox" name="is_private"> <span>By invitation only</span></label>
		<button class="btn btn--acid" type="submit">Add it</button>
	</form>
</section>
