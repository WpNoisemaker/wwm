<?php
/**
 * The page a couple shares.
 *
 * Every design is drawn with the same design system; the design decides the light, the way the
 * top of the page is composed (wedding/hero) and one motif. The plan decides which sections exist:
 * Free is Classic in black & white, Pro is Classic in the couple's colours, Premium picks a design
 * and adds spraying, livestream and the rest.
 *
 * @var array $w @var array $design @var array $dz @var bool $mine @var array $programs @var array $squad
 * @var array $gifts @var array $notes @var array $photos @var array $tally @var ?array $next @var ?array $left
 * @var array $locked @var array $sponsors
 */
$names   = Wedding::names($w);
$slug    = (string) $w['slug'];
$key     = $design['key'];
$hero    = $design['hero'];
$isFree  = (string) $w['plan'] === 'free';
$premium = Wedding::has($w, 'spray');
$photo2  = (string) ($dz['photo2'] ?? '');
if ($photo2 === '' && $photos) { $photo2 = (string) $photos[0]['image']; }
$canGive = Site::setting('pay_public', '') !== '';
$pageUrl = App::url('/w/' . $slug);
$timeline = is_array($dz['timeline'] ?? null) ? $dz['timeline'] : [];
// A Pro page wears the couple's own two colours: in the glow, the squircles and the ticks.
if ($key === 'classic' && !$isFree && Wedding::has($w, 'colours')) {
    $pc = (string) ($dz['primary'] ?? ''); $ac = (string) ($dz['accent'] ?? '');
    if (preg_match('/^#[0-9a-f]{6}$/i', $pc) && preg_match('/^#[0-9a-f]{6}$/i', $ac)) {
        echo '<style>:root{--glow-a:color-mix(in srgb,' . $pc . ' 78%,transparent);--glow-b:color-mix(in srgb,' . $ac . ' 82%,transparent);'
           . '--sky:linear-gradient(180deg,' . $pc . ' 0%,' . $ac . ' 100%);--accent:' . $pc . ';--accent-ink:' . $ac . '}</style>';
    }
}
?>
<header class="wed-head" data-hero="<?= e($hero) ?>">
	<a class="cbtn" href="<?= e(App::url('/')) ?>" aria-label="Made on Wedding Websites &amp; More"><span class="logo">&amp;</span></a>
	<p class="wed-head__title"><?= e($names) ?></p>
	<div class="wed-head__tools">
		<button class="pill pill--sm wed-head__rsvp" type="button" data-open="rsvp">RSVP</button>
		<button class="cbtn" type="button" data-share="<?= e($pageUrl) ?>" aria-label="Share this page"><?= icon('share') ?></button>
	</div>
</header>

<article class="wed wed--<?= e($key) ?>" data-hero="<?= e($hero) ?>">
	<div class="wrap"><?= flashes() ?></div>

	<?php if ($isFree): ?>
		<div class="plan-flag"><span>Free page · black &amp; white<?php if ($mine): ?> <a href="<?= e(App::url('/pricing')) ?>">Add colour</a><?php endif; ?></span></div>
	<?php endif; ?>
	<?php if ($mine): ?>
		<p class="wed-owner wrap"><?= icon('lock') ?><span>This is your page<?= (string) $w['visibility'] !== 'public' ? ', and it is private' : '' ?>. <a href="<?= e(App::url('/account/page')) ?>">Edit it</a></span></p>
	<?php endif; ?>

	<?= View::partial('wedding/hero', compact('w', 'design', 'dz', 'names', 'photo2', 'next', 'premium')) ?>

	<?php if ($next && $left): ?>
		<section class="wrap w-cd-wrap">
			<p class="w-cd-k">Counting down to <?= e($next['label']) ?></p>
			<div class="cd" data-countdown="<?= e(date('c', (int) strtotime((string) $next['when']))) ?>" aria-label="Countdown to <?= e($next['label']) ?>">
				<div class="tile"><?= dots((string) $left['d']) ?><span class="k">Days</span></div>
				<div class="tile"><?= dots(str_pad((string) $left['h'], 2, '0', STR_PAD_LEFT)) ?><span class="k">Hours</span></div>
				<div class="tile"><?= dots(str_pad((string) $left['m'], 2, '0', STR_PAD_LEFT)) ?><span class="k">Minutes</span></div>
				<div class="tile is-on"><?= dots(str_pad((string) $left['s'], 2, '0', STR_PAD_LEFT)) ?><span class="k">Seconds</span></div>
			</div>
		</section>
	<?php elseif (!empty($w['wed_date']) && strtotime((string) $w['wed_date']) < time()): ?>
		<section class="wrap w-cd-wrap"><p class="w-married"><?= icon('s-heart') ?> Married <?= e(Wedding::short($w)) ?>. Thank you for celebrating with them.</p></section>
	<?php endif; ?>

	<?php $chapters = is_array($dz['chapters'] ?? null) ? $dz['chapters'] : []; ?>
	<section class="sec wrap w-story" id="story">
		<div class="w-head reveal"><span class="eyebrow"><?= $chapters ? 'Our diary' : 'How it started' ?></span><h2>Our story</h2></div>
		<?php if ($chapters): ?>
			<ol class="diary">
				<?php foreach ($chapters as $i => [$when, $title, $text, $sign, $img]): ?>
					<li class="diary__item reveal">
						<span class="diary__dot" aria-hidden="true"></span>
						<div class="diary__card">
							<?php if ($img): ?><?= sq($img, $title, 'sq--wide diary__pic') ?><?php endif; ?>
							<span class="eyebrow"><?= e($when) ?></span>
							<h3><?= e($title) ?></h3>
							<p><?= nl2br(e($text)) ?></p>
							<?php if ($sign): ?><span class="diary__sign">&mdash; <?= e($sign) ?></span><?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php else: ?>
			<div class="story reveal">
				<?php $sp = $photo2 !== '' ? $photo2 : (string) $w['cover']; if ($sp !== ''): ?><?= sq($sp, $names, 'sq--wide') ?><?php endif; ?>
				<div class="story__text"><?php foreach (preg_split('/\n\s*\n/', trim((string) $w['story'])) as $para): if (trim($para) === '') { continue; } ?><p><?= nl2br(e(trim($para))) ?></p><?php endforeach; ?>
					<?php if (trim((string) $w['story']) === ''): ?><p><?= e($w['tagline'] ?: 'Their story is coming soon.') ?></p><?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	</section>

	<?php if ($programs): ?>
	<section class="sec wrap" id="when">
		<div class="w-head reveal"><span class="eyebrow"><?= count($programs) > 1 ? count($programs) . ' celebrations' : 'The day' ?></span><h2>When &amp; where</h2></div>
		<div class="mosaic sched reveal">
			<?php foreach ($programs as $p):
				$t = !empty($p['starts_at']) ? (int) strtotime((string) $p['starts_at']) : 0;
				$place = trim(implode(', ', array_filter([(string) $p['venue'], (string) $p['address']])));
				$map = (string) $p['map_url'] !== '' ? (string) $p['map_url'] : ($place !== '' ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($place) : ''); ?>
				<div class="tile tile--w2 sched__item">
					<span class="k"><?= e(trim((string) $p['kind'] !== '' && (string) $p['kind'] !== 'other' ? ucfirst((string) $p['kind']) : 'Celebration')) ?><?= $t ? ' · ' . e(date('D j M, g:ia', $t)) : '' ?></span>
					<span class="v"><?= e($p['title']) ?></span>
					<?php if ($place !== ''): ?><span class="sched__where"><?= icon('pin') ?><?= e($place) ?></span><?php endif; ?>
					<?php if ((string) $p['dress'] !== ''): ?><span class="sched__where"><?= icon('spark') ?><?= e($p['dress']) ?></span><?php endif; ?>
					<?php if (trim((string) $p['note']) !== ''): ?><p class="sched__note"><?= e($p['note']) ?></p><?php endif; ?>
					<?php if ($map !== ''): ?><a class="sched__go" href="<?= e($map) ?>" target="_blank" rel="noopener"><?= icon('map') ?>Directions</a><?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php foreach ($timeline as $for => $steps): if (!is_array($steps) || !$steps) { continue; } ?>
			<div class="w-timeline reveal">
				<p class="cap">On the day · <?= e($for) ?></p>
				<ol class="steps">
					<?php foreach ($steps as [$at, $what, $desc]): ?>
						<li><span class="steps__at"><?= e($at) ?></span><span class="steps__body"><b><?= e($what) ?></b><span><?= e($desc) ?></span></span></li>
					<?php endforeach; ?>
				</ol>
			</div>
		<?php endforeach; ?>
	</section>
	<?php endif; ?>

	<?php if ($squad): ?>
	<section class="sec wrap" id="squad">
		<div class="w-head reveal"><span class="eyebrow"><?= count($squad) ?> people holding it together</span><h2>The squad</h2></div>
		<?php $sides = array_unique(array_map(static fn ($s) => (string) $s['side'], $squad)); if (count($sides) > 1): ?>
			<div class="chips chips--center reveal" data-choose="#squad-list">
				<button class="chip is-on" type="button" data-f="">Everyone</button>
				<button class="chip" type="button" data-f="bride">Bride&rsquo;s side</button>
				<button class="chip" type="button" data-f="groom">Groom&rsquo;s side</button>
			</div>
		<?php endif; ?>
		<div class="squad reveal" id="squad-list">
			<?php foreach ($squad as $s): ?>
				<div class="squad__item" data-tags="<?= e($s['side']) ?>">
					<?php if ((string) $s['photo'] !== ''): ?><?= sq((string) $s['photo'], (string) $s['name'], 'squad__pic') ?><?php else: ?><span class="squad__av" aria-hidden="true"><?= e(mb_strtoupper(mb_substr((string) $s['name'], 0, 1))) ?></span><?php endif; ?>
					<span class="squad__name"><?= e($s['name']) ?></span>
					<span class="squad__role"><?= e($s['role']) ?></span>
					<?php if (trim((string) $s['words']) !== ''): ?><span class="squad__words"><?= e($s['words']) ?></span><?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php if ($gifts || $premium): ?>
	<section class="sec wrap" id="gifts">
		<div class="two">
			<?php if ($premium): ?>
			<div class="reveal" id="spray">
				<div class="w-head"><span class="eyebrow">From your seat, or from London</span><h2>Spray the couple</h2></div>
				<div class="glass">
					<p class="glass__q">How much are you spraying?</p>
					<div class="counter" data-counter data-min="500" data-max="1000000" data-step="500" data-value="5000" data-comma data-input="#g-amount" id="spray-counter">
						<button class="cbtn cbtn--lg" type="button" data-dec aria-label="Less"><?= icon('minus') ?></button>
						<div class="counter__mid" aria-live="polite"><?= dots('5,000', 'dotnum--md', true) ?><span class="counter__unit">Naira</span></div>
						<button class="cbtn cbtn--lg" type="button" data-inc aria-label="More"><?= icon('plus') ?></button>
					</div>
					<div class="total"><p class="total__k">Sprayed so far</p><p class="total__v"><?= money(number_format((int) Db::val("SELECT COALESCE(SUM(amount),0) FROM payments WHERE wedding_id = ? AND purpose = 'spray' AND status IN ('paid','released')", [$w['id']], 0)), (string) $w['currency']) ?></p></div>
				</div>
				<button class="pill pill--block gap-t-sm" type="button" data-open="give" data-give="Spray">Spray them</button>
			</div>
			<?php endif; ?>
			<?php if ($gifts): ?>
			<div class="reveal">
				<div class="w-head"><span class="eyebrow">Paid to the couple, with your note</span><h2>Gifts</h2></div>
				<div class="gifts">
					<?php foreach ($gifts as $g):
						$target = (int) $g['target']; $raised = (int) $g['raised'];
						$pct = $target > 0 ? min(100, (int) round($raised / $target * 100)) : 0; ?>
						<button class="gift" type="button" data-open="give" data-give="<?= e($g['title']) ?>">
							<?= (string) $g['image'] !== '' ? sq((string) $g['image'], '') : '<span class="sq gift__ico">' . icon((string) $g['kind'] === 'item' ? 'gift' : ((string) $g['kind'] === 'fund' ? 'spark' : 'wallet')) . '</span>' ?>
							<span class="gift__body">
								<span class="gift__t"><?= e($g['title']) ?></span>
								<span class="gift__s"><?= $target > 0 ? money(short_money($raised), (string) $w['currency']) . ' of ' . money(short_money($target), (string) $w['currency']) : e($g['note'] ?: 'Any amount, with a note') ?></span>
								<?php if ($target > 0): ?><?= meter($pct) ?><?php endif; ?>
							</span>
						</button>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php if (Wedding::has($w, 'live')): ?>
	<section class="sec wrap w-live-sec">
		<div class="tile w-live reveal">
			<span class="live__dot" aria-hidden="true"></span>
			<div><span class="v">Watch live</span><span class="k"><?= e($dz['live'] ?? 'The ceremony streams right here on the day.') ?></span></div>
			<button class="pill pill--sm pill--glass" type="button" data-toggle aria-pressed="false" data-toast-on="We will remind you when it starts" data-toast-off="Reminder off"><span data-on="Reminder on" data-off="Remind me">Remind me</span></button>
		</div>
	</section>
	<?php endif; ?>

	<?php if ($photos): ?>
	<section class="sec wrap" id="photos">
		<div class="w-head reveal"><span class="eyebrow"><?= (int) $tally['photos'] ?> from guests</span><h2>Photos</h2></div>
		<div class="gallery reveal">
			<?php foreach (array_slice($photos, 0, 8) as $ph): ?><?= sq((string) $ph['image'], (string) $ph['body']) ?><?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php $quiz = is_array($dz['quiz'] ?? null) ? $dz['quiz'] : []; if ($quiz && $premium): ?>
	<section class="sec wrap" id="quiz">
		<div class="w-head reveal"><span class="eyebrow">How well do you know them?</span><h2>The quiz</h2></div>
		<div class="glass glass--plain quiz reveal" data-quiz>
			<?php foreach ($quiz as $qi => [$q, $opts, $right]): ?>
				<div class="quiz__q" data-q>
					<p><span class="quiz__n"><?= $qi + 1 ?></span><?= e($q) ?></p>
					<div class="quiz__opts"><?php foreach ($opts as $oi => $o): ?><button class="chip" type="button"<?= $oi === (int) $right ? ' data-right' : '' ?>><?= e($o) ?></button><?php endforeach; ?></div>
				</div>
			<?php endforeach; ?>
			<p class="quiz__score" data-quiz-score aria-live="polite">Tap an answer to start</p>
		</div>
	</section>
	<?php endif; ?>

	<?php $palette = is_array($dz['palette'] ?? null) ? $dz['palette'] : []; if ($palette): ?>
	<section class="sec wrap w-palette-sec">
		<div class="w-head reveal"><span class="eyebrow">Dress in these, if you can</span><h2>Colours of the day</h2></div>
		<div class="palette reveal"><?php foreach ($palette as [$pn, $pc]): ?><span class="palette__i"><i style="background:<?= e($pc) ?>"></i><b><?= e($pn) ?></b><small><?= e($pc) ?></small></span><?php endforeach; ?></div>
	</section>
	<?php endif; ?>

	<section class="sec wrap w-wall" id="wall">
		<div class="w-head reveal"><span class="eyebrow"><?= (int) $tally['notes'] ? (int) $tally['notes'] . ' wishes so far' : 'Be the first' ?></span><h2>Wall of love</h2></div>
		<form class="wish-compose reveal" method="post" action="<?= e(App::url('/w/' . $slug . '/note')) ?>">
			<?= Guard::field() ?>
			<label class="sr" for="n-body">Your wish</label>
			<textarea class="input" id="n-body" name="body" rows="3" maxlength="1000" required placeholder="Write something they will keep…"></textarea>
			<div class="wish-compose__row">
				<label class="sr" for="n-name">Your name</label>
				<input class="input" id="n-name" name="name" required placeholder="Your name" autocomplete="name">
				<button class="cbtn wish-compose__go" type="submit" aria-label="Post your wish"><?= icon('arrow') ?></button>
			</div>
		</form>
		<?php if ($notes): ?>
			<div class="wishes reveal">
				<?php foreach ($notes as $n): ?>
					<div class="wish"><p><?= e((string) $n['body']) ?></p><span><?= e((string) $n['name']) ?> · <?= e(ago((string) $n['created_at'])) ?></span></div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>

	<?php if ($sponsors): ?>
	<section class="sec wrap w-ads">
		<p class="cap">Sponsored</p>
		<div class="rows">
			<?php foreach ($sponsors as $s): ?>
				<a class="row-item" href="<?= e(App::url('/vendors/' . $s['slug'])) ?>">
					<?= sq((string) $s['logo'], '', 'row-item__pic') ?>
					<span><span class="row-item__t"><?= e($s['name']) ?></span><span class="row-item__s"><?= e($s['category'] . ' · ' . $s['city']) ?></span></span>
					<?= icon('next') ?>
				</a>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php if ($locked): ?>
	<section class="sec wrap">
		<div class="glass uplift">
			<span class="eyebrow">Only you can see this</span>
			<h2>There is more your page could do</h2>
			<div class="uplift__grid">
				<?php foreach ($locked as $plan => $items): ?>
					<div class="tile"><span class="k"><?= e(Site::tier((string) $plan)) ?></span><ul><?php foreach ($items as $i): ?><li><?= icon('check') ?><?= e($i) ?></li><?php endforeach; ?></ul></div>
				<?php endforeach; ?>
			</div>
			<a class="pill" href="<?= e(App::url('/pricing')) ?>">See what each plan adds</a>
		</div>
	</section>
	<?php endif; ?>

	<footer class="wed-foot wrap">
		<p class="wed-foot__names"><?= e($names) ?></p>
		<p><?= e(Wedding::when($w)) ?></p>
		<?php if (!empty($dz['sign'])): ?><p class="wed-foot__sign"><?= e($dz['sign']) ?></p><?php endif; ?>
		<p class="sub"><?= e($design['name']) ?> design · Made on <a href="<?= e(App::url('/')) ?>">Wedding Websites &amp; More</a> · <a href="<?= e(App::url('/join')) ?>">Make yours free</a></p>
	</footer>
</article>

<nav class="wed-bar" aria-label="On this page">
	<button type="button" data-open="rsvp"><?= icon('check') ?>RSVP</button>
	<?php if ($gifts): ?><a href="#gifts"><?= icon('gift') ?>Gifts</a><?php endif; ?>
	<?php if ($premium): ?><button type="button" data-open="give" data-give="Spray"><?= icon('wallet') ?>Spray</button><?php endif; ?>
	<a href="#wall"><?= icon('heart') ?>Wishes</a>
	<button type="button" data-share="<?= e($pageUrl) ?>"><?= icon('share') ?>Share</button>
</nav>

<div class="sheet" id="rsvp" role="dialog" aria-modal="true" aria-labelledby="rsvp-t">
	<div class="sheet__grab" aria-hidden="true"></div>
	<div class="sheet__head">
		<a class="cbtn cbtn--sm" href="#" data-close aria-label="Close"><?= icon('close') ?></a>
		<h2 id="rsvp-t">Your RSVP</h2><span></span>
	</div>
	<form class="form" method="post" action="<?= e(App::url('/w/' . $slug . '/rsvp')) ?>">
		<?= Guard::field() ?>
		<input type="hidden" name="party" id="r-party" value="<?= $key === 'classic' && $isFree ? 1 : 2 ?>">
		<div class="radios" role="radiogroup" aria-label="Are you coming?">
			<label><input type="radio" name="coming" value="yes" checked>Coming</label>
			<label><input type="radio" name="coming" value="maybe">Not sure</label>
			<label><input type="radio" name="coming" value="no">Can&rsquo;t make it</label>
		</div>
		<div class="counter counter--sheet" data-counter data-min="1" data-max="<?= (int) ($dz['rsvp_max'] ?? 10) ?>" data-value="<?= $key === 'classic' && $isFree ? 1 : 2 ?>" data-input="#r-party">
			<button class="cbtn" type="button" data-dec aria-label="One fewer"><?= icon('minus') ?></button>
			<div class="counter__mid"><?= dots($key === 'classic' && $isFree ? '1' : '2', 'dotnum--md', true) ?><span class="counter__unit" data-unit="Guest|Guests"><?= $key === 'classic' && $isFree ? 'Guest' : 'Guests' ?></span></div>
			<button class="cbtn" type="button" data-inc aria-label="One more"><?= icon('plus') ?></button>
		</div>
		<label class="field"><span>Your name</span><input class="input" name="name" required autocomplete="name"></label>
		<label class="field"><span>Phone, so they can reach you (optional)</span><input class="input" name="phone" inputmode="tel" autocomplete="tel"></label>
		<label class="field"><span>A word for them (optional)</span><input class="input" name="note" placeholder="Congratulations!"></label>
		<button class="pill pill--block" type="submit">Send RSVP</button>
		<p class="after-pill">No account needed. Only <?= e($names) ?> see your reply.</p>
	</form>
</div>

<div class="sheet" id="give" role="dialog" aria-modal="true" aria-labelledby="give-t">
	<div class="sheet__grab" aria-hidden="true"></div>
	<div class="sheet__head">
		<a class="cbtn cbtn--sm" href="#" data-close aria-label="Close"><?= icon('close') ?></a>
		<h2 id="give-t" data-give-title>A gift</h2><span></span>
	</div>
	<div class="counter counter--sheet" data-counter data-min="500" data-max="1000000" data-step="500" data-value="5000" data-comma data-input="#g-amount">
		<button class="cbtn" type="button" data-dec aria-label="Less"><?= icon('minus') ?></button>
		<div class="counter__mid"><?= dots('5,000', 'dotnum--md', true) ?><span class="counter__unit">Naira</span></div>
		<button class="cbtn" type="button" data-inc aria-label="More"><?= icon('plus') ?></button>
	</div>
	<input type="hidden" id="g-amount" value="5000">
	<?php if ($canGive): ?>
		<button class="pill pill--block" type="button">Continue to pay</button>
	<?php else: ?>
		<p class="note"><?= icon('info') ?><span>Paying online switches on once this site&rsquo;s payment account is connected. Until then, ask <?= e($names) ?> how they would like to receive gifts.</span></p>
		<button class="pill pill--block" type="button" disabled>Paying online is not on yet</button>
	<?php endif; ?>
</div>

<script>
	document.addEventListener('click', function (e) {
		var b = e.target.closest('[data-give]'); if (!b) { return; }
		var t = document.querySelector('[data-give-title]'); if (t) { t.textContent = b.getAttribute('data-give') === 'Spray' ? 'Spray the couple' : b.getAttribute('data-give'); }
	});
</script>
