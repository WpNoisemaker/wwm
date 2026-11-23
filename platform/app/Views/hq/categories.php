<?php /** What can be sold on the marketplace. Sellers only list in an open category. @var array $rows */ ?>
<?= View::partial('hq/_head', ['h' => 'Categories', 'now' => 'market', 'sub' => 'What sellers can list']) ?>
	<p class="help">Sellers can only list in an open category; they cannot make new ones. Close a category to stop new listings in it; existing listings stay up.</p>
	<div class="acct-list gap-t-sm">
		<?php foreach ($rows as $c): ?>
			<form method="post" class="form glass glass--plain glass--tight acct-card">
				<?= Guard::field() ?><input type="hidden" name="do" value="save"><input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
				<div class="form-grid form-grid--2">
					<label class="field"><span>Name</span><input class="input" name="name" value="<?= e((string) $c['name']) ?>" required maxlength="80"></label>
					<label class="field"><span>What it covers</span><input class="input" name="about" value="<?= e((string) $c['about']) ?>" maxlength="255"></label>
					<label class="check span-2"><span>Open to sellers · <?= (int) $c['listings'] ?> listing<?= (int) $c['listings'] === 1 ? '' : 's' ?></span><input type="checkbox" name="open" value="1"<?= (int) $c['open'] ? ' checked' : '' ?>></label>
				</div>
				<button class="pill pill--sm" type="submit">Save</button>
			</form>
		<?php endforeach; ?>
		<form method="post" class="form glass glass--tight acct-card">
			<?= Guard::field() ?><input type="hidden" name="do" value="add">
			<h2 class="h3">Open a new category</h2>
			<div class="form-grid form-grid--2">
				<label class="field"><span>Name</span><input class="input" name="name" required maxlength="80" placeholder="Make-up & gele"></label>
				<label class="field"><span>What it covers</span><input class="input" name="about" maxlength="255"></label>
			</div>
			<button class="pill pill--block" type="submit">Open it</button>
		</form>
	</div>
</div>
