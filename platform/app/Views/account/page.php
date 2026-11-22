<?php /** Everything a guest sees: names, story, date and place, the design, and who can see it. */
$choices = Design::choices($w);
$isPremium = Wedding::rank((string) $w['plan']) >= Wedding::rank('premium');
?>
<?= View::partial('account/_head', ['h' => 'Your page', 'w' => $w, 'sub' => 'Everything a guest sees', 'now' => 'page']) ?>
	<form method="post" class="form acct-form">
		<?= Guard::field() ?>
		<section class="glass glass--plain glass--tight acct-card" id="story">
			<h2 class="h3">The couple</h2>
			<div class="form-grid form-grid--2">
				<label class="field"><span>First name</span><input class="input" name="a" value="<?= e($w['partner_a']) ?>" required></label>
				<label class="field"><span>Their first name</span><input class="input" name="b" value="<?= e($w['partner_b']) ?>" required></label>
				<label class="field"><span>Hashtag</span><input class="input" name="hashtag" value="<?= e($w['hashtag']) ?>" placeholder="#IbrahimMeetsUmmi"></label>
				<label class="field"><span>One line for the top of the page</span><input class="input" name="tagline" value="<?= e($w['tagline']) ?>" placeholder="Come and celebrate with us"></label>
				<label class="field span-2"><span>Your story</span><textarea class="input" name="story" rows="6" placeholder="How you met, in your own words."><?= e($w['story']) ?></textarea></label>
			</div>
		</section>
		<section class="glass glass--plain glass--tight acct-card">
			<h2 class="h3">When and where</h2>
			<div class="form-grid form-grid--2">
				<label class="field"><span>Date</span><input class="input" name="date" type="date" value="<?= e($w['wed_date']) ?>"></label>
				<label class="field"><span>Time</span><input class="input" name="time" type="time" value="<?= e($w['wed_time'] ? substr((string) $w['wed_time'], 0, 5) : '') ?>"></label>
				<label class="field span-2"><span>Venue</span><input class="input" name="venue" value="<?= e($w['venue']) ?>"></label>
				<label class="field"><span>City</span><input class="input" name="city" value="<?= e($w['city']) ?>"></label>
				<label class="field"><span>State</span><input class="input" name="state" value="<?= e($w['state']) ?>"></label>
			</div>
		</section>
		<section class="glass glass--plain glass--tight acct-card">
			<h2 class="h3">How it looks</h2>
			<label class="field"><span>Design</span>
				<select class="input" name="template">
					<?php foreach ($choices as $k => $label): ?><option value="<?= e($k) ?>"<?= Design::get((string) $w['template'])['key'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
				</select>
			</label>
			<p class="help"><?= $isPremium ? 'Your page wears this design.' : 'Free pages are Classic in black &amp; white and Pro pages are Classic in your colours. The other designs come with Premium.' ?> <a class="linkish" href="<?= e(App::url('/templates')) ?>">See them all</a></p>
			<?php if (Wedding::has($w, 'colours')): ?>
				<div class="form-grid form-grid--2 gap-t-sm">
					<label class="field"><span>Main colour</span><input name="primary" type="color" value="<?= e($d['primary'] ?? '#7A5C3A') ?>"></label>
					<label class="field"><span>Accent</span><input name="accent" type="color" value="<?= e($d['accent'] ?? '#C3A46A') ?>"></label>
				</div>
			<?php else: ?>
				<p class="note gap-t-sm"><?= icon('lock') ?><span>Your own colours come with Pro. <a href="<?= e(App::url('/pricing')) ?>">See the plans</a></span></p>
			<?php endif; ?>
		</section>
		<section class="glass glass--plain glass--tight acct-card" id="visibility">
			<h2 class="h3">Who can see it</h2>
			<div class="radios">
				<label><input type="radio" name="visibility" value="private" <?= (string) $w['visibility'] !== 'public' ? 'checked' : '' ?>>Private</label>
				<label><input type="radio" name="visibility" value="public" <?= (string) $w['visibility'] === 'public' ? 'checked' : '' ?>>Public</label>
			</div>
			<p class="help">Private: only people with the link and the code. Public: listed, searchable and on the Buzz Chart.</p>
		</section>
		<div class="form-actions acct-save"><button class="pill" type="submit">Save changes</button><a class="pill pill--glass" href="<?= e(App::url('/w/' . $w['slug'])) ?>">View the page</a></div>
	</form>
</div>
