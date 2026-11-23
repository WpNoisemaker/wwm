<?php /** Everything a guest sees: names, story, date and place, the design, and who can see it. */
$choices = Design::choices($w);
$isPremium = Wedding::rank((string) $w['plan']) >= Wedding::rank('premium');
?>
<?= View::partial('account/_head', ['h' => 'Your page', 'w' => $w, 'sub' => 'Everything a guest sees', 'now' => 'page']) ?>
	<form method="post" class="form acct-form" enctype="multipart/form-data">
		<?= Guard::field() ?>
		<section class="glass glass--plain glass--tight acct-card" id="story">
			<h2 class="h3">The couple</h2>
			<div class="form-grid form-grid--2">
				<label class="field"><span>First name</span><input class="input" name="a" value="<?= e($w['partner_a']) ?>" required></label>
				<label class="field"><span>Their first name</span><input class="input" name="b" value="<?= e($w['partner_b']) ?>" required></label>
				<label class="field"><span>Hashtag</span><input class="input" name="hashtag" value="<?= e($w['hashtag']) ?>" placeholder="#IbrahimMeetsUmmi"></label>
				<label class="field"><span>One line for the top of the page</span><input class="input" name="tagline" value="<?= e($w['tagline']) ?>" placeholder="Come and celebrate with us"></label>
				<label class="field span-2"><span>Your story</span><textarea class="input" name="story" rows="6" placeholder="How you met, in your own words."><?= e($w['story']) ?></textarea><span class="help">Or tell it as diary cards, one moment each, with photos: <a class="linkish" href="<?= e(App::url('/account/story')) ?>">write your diary</a>.</span></label>
			</div>
		</section>
		<section class="glass glass--plain glass--tight acct-card">
			<h2 class="h3">When and where</h2>
			<div class="form-grid form-grid--2">
				<label class="field"><span>Date</span><input class="input" name="date" type="date" value="<?= e($w['wed_date']) ?>"></label>
				<label class="field"><span>Time</span><input class="input" name="time" type="time" value="<?= e($w['wed_time'] ? substr((string) $w['wed_time'], 0, 5) : '') ?>"></label>
				<label class="field span-2"><span>Venue</span><input class="input" name="venue" value="<?= e($w['venue']) ?>"></label>
				<label class="field"><span>City</span><input class="input" name="city" value="<?= e($w['city']) ?>"></label>
				<label class="field"><span>State or region</span><input class="input" name="state" value="<?= e($w['state']) ?>"></label>
				<label class="field span-2"><span>Country</span><select class="input" name="country"><?php foreach (Wedding::COUNTRIES as $c): ?><option<?= (string) $w['country'] === $c ? ' selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select></label>
			</div>
		</section>
		<section class="glass glass--plain glass--tight acct-card">
			<h2 class="h3">How it looks</h2>
			<label class="field"><span>Design</span>
				<select class="input" name="template">
					<?php foreach ($choices as $k => $label): ?><option value="<?= e($k) ?>"<?= Design::get((string) $w['template'])['key'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
				</select>
			</label>
			<p class="help"><?= $isPremium ? 'Your page wears this design.' : 'Free pages are Classic in black &amp; white and Pro pages are Classic in your colours.' ?> <a class="linkish" href="<?= e(App::url('/templates')) ?>">See them all</a></p>
			<?php if (!$isPremium): ?><div class="lock-row gap-t-sm"><span class="help">Ten more designs, from Royal to Savannah</span><?= up_tag('premium', 'Every Premium design') ?></div><?php endif; ?>
			<?php if (Wedding::has($w, 'colours')): ?>
				<div class="form-grid form-grid--2 gap-t-sm">
					<label class="field"><span>Main colour</span><input name="primary" type="color" value="<?= e($d['primary'] ?? '#7A5C3A') ?>"></label>
					<label class="field"><span>Accent</span><input name="accent" type="color" value="<?= e($d['accent'] ?? '#C3A46A') ?>"></label>
				</div>
			<?php else: ?>
				<div class="form-grid form-grid--2 gap-t-sm locked" data-upgrade="pro" data-feature="Your own colours">
					<label class="field"><span>Main colour</span><input type="color" value="#7A5C3A" disabled></label>
					<label class="field"><span>Accent</span><input type="color" value="#C3A46A" disabled></label>
				</div>
				<div class="lock-row"><span class="help">Your own colours</span><?= up_tag('pro', 'Your own colours') ?></div>
			<?php endif; ?>
		</section>
		<section class="glass glass--plain glass--tight acct-card" id="colours">
			<div class="lock-row"><h2 class="h3">Colours of the day</h2><?php if (!Wedding::has($w, 'colours')): ?><?= up_tag('pro', 'Your colour code') ?><?php endif; ?></div>
			<p class="help">The colours you would like guests to wear. They show right under your invite.</p>
			<?php $pal = is_array($d['palette'] ?? null) ? array_values($d['palette']) : []; $canPal = Wedding::has($w, 'colours'); ?>
			<div class="palette-ed<?= $canPal ? '' : ' locked' ?>"<?= $canPal ? '' : ' data-upgrade="pro" data-feature="Your colour code"' ?>>
				<?php for ($i = 0; $i < 6; $i++): $row = $pal[$i] ?? null; ?>
					<div class="palette-ed__row">
						<input type="color" name="pal_hex[]" value="<?= e((string) ($row[1] ?? ['#C9A24B', '#7B2D43', '#E9DCC0', '#2E4A3A', '#1F3A5F', '#F2E6D8'][$i])) ?>" aria-label="Colour <?= $i + 1 ?>"<?= $canPal ? '' : ' disabled' ?>>
						<input class="input" name="pal_name[]" value="<?= e((string) ($row[0] ?? '')) ?>" placeholder="<?= e(['Gold', 'Wine', 'Ivory', 'Emerald', 'Royal blue', 'Champagne'][$i]) ?>" aria-label="Name of colour <?= $i + 1 ?>"<?= $canPal ? '' : ' disabled' ?>>
					</div>
				<?php endfor; ?>
			</div>
			<p class="help">Name a colour to use it; leave the name empty to leave it out.</p>
		</section>
		<section class="glass glass--plain glass--tight acct-card" id="live">
			<div class="lock-row"><h2 class="h3">Livestream</h2><?php if (!Wedding::has($w, 'live')): ?><?= up_tag('premium', 'Livestream on your page') ?><?php endif; ?></div>
			<?php if (Wedding::has($w, 'live')): ?>
				<div class="form-grid form-grid--2">
					<label class="field"><span>YouTube link for the day</span><input class="input" name="live_url" type="url" inputmode="url" value="<?= e($d['live_url'] ?? '') ?>" placeholder="https://youtube.com/live/…"></label>
					<label class="field"><span>It starts at (optional)</span><input class="input" name="live_at" type="datetime-local" value="<?= e((string) ($d['live_at'] ?? '')) ?>"></label>
				</div>
				<p class="help">Start a YouTube live (or schedule one), paste its link here, and it plays right on your page. Leave the time empty and we use your next event's. <?php $subs = Reminder::count((int) $w['id']); ?><?= $subs ? 'The moment you add it, ' . $subs . ' guest' . ($subs === 1 ? '' : 's') . ' who asked to be reminded get an email.' : 'Guests who tap Remind me get an email the moment you add it.' ?></p>
			<?php else: ?>
				<label class="field locked" data-upgrade="premium" data-feature="Livestream on your page"><span>YouTube link for the day</span><input class="input" type="url" placeholder="https://youtube.com/live/…" disabled></label>
				<p class="help">Family who cannot travel watch the ceremony right on your page.</p>
			<?php endif; ?>
		</section>
		<section class="glass glass--plain glass--tight acct-card" id="music">
			<div class="lock-row"><h2 class="h3">Background music</h2><?php if (!Wedding::has($w, 'music')): ?><?= up_tag('premium', 'Background music') ?><?php endif; ?></div>
			<?php if (Wedding::has($w, 'music')): $mu = Music::forWedding($w); ?>
				<p class="help">A song that plays softly while guests look around your page. It starts on their first tap (phones never play sound before that), and a music button lets anyone pause it.</p>
				<?php if ($mu): ?>
					<div class="music-now">
						<span class="music-now__k"><?= icon('music') ?><?= e($mu['title'] !== '' ? $mu['title'] : 'Your song') ?></span>
						<audio controls preload="none" src="<?= e($mu['url']) ?>"></audio>
					</div>
					<label class="check"><span>Remove the music</span><input type="checkbox" name="music_off" value="1"></label>
				<?php endif; ?>
				<div class="form-grid form-grid--2">
					<label class="field"><span><?= $mu ? 'Replace it with a file' : 'Upload a song' ?></span><input class="input" type="file" name="music_file" accept="audio/mpeg,audio/mp4,audio/aac,audio/ogg,audio/wav,.mp3,.m4a,.aac,.ogg,.wav"></label>
					<label class="field"><span>Or paste a link to the song</span><input class="input" name="music_link" type="url" inputmode="url" value="<?= e($mu['link'] ?? '') ?>" placeholder="Google Drive, Dropbox or an .mp3 link"></label>
					<label class="field"><span>What it is called (optional)</span><input class="input" name="music_title" value="<?= e($mu['title'] ?? '') ?>" maxlength="80" placeholder="Our song"></label>
					<label class="field"><span>How loud</span><select class="input" name="music_volume"><?php foreach (Music::VOLUMES as $v => $label): ?><option value="<?= $v ?>"<?= (int) ($mu['volume'] ?? 20) === $v ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
				</div>
				<p class="help">MP3, M4A, AAC, OGG or WAV, up to 15 MB. Links to Google Drive or Dropbox files work when the file is shared with anyone who has the link. YouTube and streaming apps cannot play in the background. Use music you have the right to share.</p>
			<?php else: ?>
				<label class="field locked" data-upgrade="premium" data-feature="Background music"><span>Upload a song</span><input class="input" type="file" disabled></label>
				<p class="help">Your song playing softly while guests look around your page.</p>
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
