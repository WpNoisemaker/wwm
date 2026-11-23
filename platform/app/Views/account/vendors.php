<?= View::partial('account/_head', ['h' => 'Your vendors', 'w' => $w, 'sub' => 'The vendors you booked. Guests can rate them on your page', 'now' => 'vendors']) ?>
	<div class="acct-list">
		<?php if (!$rows): ?>
			<p class="help">Add the caterer, decorator, photographer and anyone else you booked. They appear on your page under "The people behind our day", and guests who enjoyed their work can rate them there without creating an account.</p>
		<?php else: ?>
			<div class="rows">
				<?php foreach ($rows as $v): ?>
					<form method="post" class="row-item">
						<?= Guard::field() ?><input type="hidden" name="vendor_id" value="<?= (int) $v['id'] ?>">
						<?= sq((string) $v['logo'], '', 'row-item__pic') ?>
						<span><span class="row-item__t"><?= e($v['name']) ?></span><span class="row-item__s"><?= e(trim(($v['role'] ?: $v['category']) . ' · ' . $v['city'], ' ·')) ?></span></span>
						<button class="pill pill--sm pill--glass" type="submit" name="do" value="remove" aria-label="Remove <?= e($v['name']) ?>">Remove</button>
					</form>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<form method="post" class="form glass glass--tight acct-card">
			<?= Guard::field() ?>
			<h2 class="h3">Add a vendor</h2>
			<div class="form-grid form-grid--2">
				<label class="field"><span>Vendor name</span><input class="input" name="vendor" list="vendor-names" required autocomplete="off" placeholder="Start typing a name"></label>
				<label class="field"><span>What they did (optional)</span><input class="input" name="role" maxlength="80" placeholder="Catering"></label>
			</div>
			<datalist id="vendor-names"><?php foreach ($all as $a): ?><option value="<?= e($a['name']) ?>"><?= e(trim($a['category'] . ' · ' . $a['city'], ' ·')) ?></option><?php endforeach; ?></datalist>
			<button class="pill pill--block" type="submit">Add to my page</button>
			<p class="help">Only vendors listed on this site can be added. Is yours missing? Ask them to <a href="<?= e(App::url('/sell')) ?>">join as a seller</a>.</p>
		</form>
	</div>
</div>
