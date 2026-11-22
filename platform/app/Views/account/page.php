<?= View::partial('account/_head', ['h' => 'Your page', 'w' => $w, 'sub' => 'Everything a guest sees.']) ?>
<section class="sec wrap">
	<form method="post" class="form form--card">
		<?= Guard::field() ?>
		<h3 class="form__g">The couple</h3>
		<div class="form__pair">
			<span><label for="a">First name</label><input id="a" name="a" value="<?= e($w['partner_a']) ?>" required></span>
			<span><label for="b">Their first name</label><input id="b" name="b" value="<?= e($w['partner_b']) ?>" required></span>
		</div>
		<label for="hashtag">Hashtag</label>
		<input id="hashtag" name="hashtag" value="<?= e($w['hashtag']) ?>" placeholder="#IbrahimMeetsUmmi">
		<label for="tagline">One line for the top of the page</label>
		<input id="tagline" name="tagline" value="<?= e($w['tagline']) ?>" placeholder="Come and celebrate with us">
		<label for="story">Your story</label>
		<textarea id="story" name="story" rows="6" placeholder="How you met, in your own words."><?= e($w['story']) ?></textarea>

		<h3 class="form__g">When and where</h3>
		<div class="form__pair">
			<span><label for="date">Date</label><input id="date" name="date" type="date" value="<?= e($w['wed_date']) ?>"></span>
			<span><label for="time">Time</label><input id="time" name="time" type="time" value="<?= e($w['wed_time']) ?>"></span>
		</div>
		<label for="venue">Venue</label>
		<input id="venue" name="venue" value="<?= e($w['venue']) ?>">
		<div class="form__pair">
			<span><label for="city">City</label><input id="city" name="city" value="<?= e($w['city']) ?>"></span>
			<span><label for="state">State</label><input id="state" name="state" value="<?= e($w['state']) ?>"></span>
		</div>

		<h3 class="form__g">How it looks</h3>
		<?php if (Wedding::has($w, 'colours')): ?>
			<div class="form__pair">
				<span><label for="primary">Main colour</label><input id="primary" name="primary" type="color" value="<?= e($d['primary'] ?? '#7A5C3A') ?>"></span>
				<span><label for="accent">Accent</label><input id="accent" name="accent" type="color" value="<?= e($d['accent'] ?? '#C3A46A') ?>"></span>
			</div>
		<?php else: ?>
			<p class="locked"><?= icon('lock', 14) ?> Your own colours come with Pro. <a href="<?= e(App::url('/pricing')) ?>">See the plans</a></p>
		<?php endif; ?>
		<label for="template">Template</label>
		<select id="template" name="template">
			<?php foreach (['classic' => 'Classic', 'ivory' => 'Ivory', 'aso' => 'Aso-oke', 'studio' => 'Studio'] as $k => $t): ?>
				<option value="<?= e($k) ?>"<?= (string) $w['template'] === $k ? ' selected' : '' ?>><?= e($t) ?></option>
			<?php endforeach; ?>
		</select>

		<h3 class="form__g">Who can see it</h3>
		<label class="check"><input type="radio" name="visibility" value="private" <?= (string) $w['visibility'] !== 'public' ? 'checked' : '' ?>> <span><b>Private</b> — only people with the link and the code</span></label>
		<label class="check"><input type="radio" name="visibility" value="public" <?= (string) $w['visibility'] === 'public' ? 'checked' : '' ?>> <span><b>Public</b> — listed, searchable and on the Buzz Chart</span></label>

		<button class="btn btn--acid" type="submit">Save</button>
	</form>
</section>
