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
// Guests give in the couple's own money: naira in Lagos, pounds in London, cedis in Accra.
[$curName, $curMin, $curStart] = ['NGN' => ['Naira', 500, 5000], 'GBP' => ['Pounds', 5, 20], 'USD' => ['Dollars', 5, 20], 'CAD' => ['Dollars', 5, 20],
    'AUD' => ['Dollars', 5, 20], 'NZD' => ['Dollars', 5, 20], 'EUR' => ['Euros', 5, 20], 'AED' => ['Dirhams', 10, 50], 'KES' => ['Shillings', 100, 1000],
    'GHS' => ['Cedis', 10, 50], 'RWF' => ['Francs', 1000, 5000], 'ZAR' => ['Rand', 20, 100]][(string) $w['currency']] ?? ['Naira', 500, 5000];
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
		<?php $mu = Music::forWedding($w); if ($mu): ?>
		<div class="music" data-music data-src="<?= e($mu['url']) ?>" data-volume="<?= e((string) ($mu['volume'] / 100)) ?>" data-key="wwm-music-<?= (int) $w['id'] ?>">
			<button class="music__b" type="button" data-music-toggle aria-pressed="false" aria-label="Play the music<?= $mu['title'] !== '' ? ': ' . e($mu['title']) : '' ?>">
				<span class="music__bars" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
				<span class="music__t"><?= e($mu['title'] !== '' ? $mu['title'] : 'Music') ?></span>
			</button>
		</div>
		<?php endif; ?>
		<button class="pill pill--sm wed-head__rsvp" type="button" data-open="rsvp">RSVP</button>
		<button class="cbtn" type="button" data-share="<?= e($pageUrl) ?>" data-share-ping="<?= e(App::url('/w/' . $slug . '/share')) ?>" aria-label="Share this page"><?= icon('share') ?></button>
	</div>
</header>

<article class="wed wed--<?= e($key) ?>" data-hero="<?= e($hero) ?>">
	<div class="wrap"><?= flashes() ?></div>
	<?php if (!empty($pass)): ?>
		<section class="wrap pass-wrap">
			<div class="pass glass">
				<span class="eyebrow">Your entry pass · <?= e($names) ?></span>
				<p class="pass__name"><?= e($pass['name']) ?><?= (int) $pass['party'] > 1 ? ' + ' . ((int) $pass['party'] - 1) : '' ?></p>
				<p class="pass__code" aria-label="Pass code <?= e(implode(' ', str_split($pass['code']))) ?>"><?php foreach (str_split((string) $pass['code']) as $ch): ?><span><?= e($ch) ?></span><?php endforeach; ?></p>
				<p class="pass__when"><?= e(Wedding::when($w)) ?><?= ($where = Wedding::where($w)) !== '' ? ' · ' . e($where) : '' ?></p>
				<p class="after-pill">Show this at the gate. Take a screenshot so you have it without signal.</p>
			</div>
		</section>
	<?php endif; ?>

	<?php /* Every page says which plan it is on, right at the top. */
	$planName = Site::tier((string) $w['plan']);
	$planLine = $isFree ? 'black &amp; white' : ($key === 'classic' ? 'their own colours' : e($design['name']) . ' design'); ?>
	<div class="plan-flag plan-flag--<?= e((string) $w['plan']) ?>">
		<span><?php if (Wedding::has($w, 'badge')): ?><?= icon('s-shield') ?><?php endif; ?><b><?= e($planName) ?> page</b> · <?= $planLine ?><?php if ($isFree && $mine): ?> <a href="<?= e(App::url('/pricing#premium')) ?>" data-upgrade="pro" data-feature="Your colours">Add colour</a><?php endif; ?></span>
	</div>
	<?php if (Wedding::has($w, 'monogram')): ?>
		<div class="monogram" aria-hidden="true"><svg viewBox="0 0 100 100"><circle cx="50" cy="50" r="46" pathLength="360" stroke-dasharray="0 9"/><circle cx="50" cy="50" r="40" pathLength="360" stroke-dasharray="0 4.5" class="monogram__in"/></svg><b><?= e(mb_strtoupper(mb_substr((string) $w['partner_a'], 0, 1))) ?><span>&amp;</span><?= e(mb_strtoupper(mb_substr((string) $w['partner_b'], 0, 1))) ?></b></div>
	<?php endif; ?>
	<?php if ($mine): ?>
		<p class="wed-owner wrap"><?= icon('lock') ?><span>This is your page<?= (string) $w['visibility'] !== 'public' ? ', and it is private' : '' ?>. <a href="<?= e(App::url('/account/page')) ?>">Edit it</a></span></p>
	<?php endif; ?>

	<?= View::partial('wedding/hero', compact('w', 'design', 'dz', 'names', 'photo2', 'next', 'premium')) ?>

	<?php /* Each part below the invite is drawn into its own block, then shown in the order the couple chose. */
	$blocks = []; $layout = Wedding::sections($w); $shows = static fn (string $k): bool => !in_array($k, $layout['hidden'], true); ?>
	<?php ob_start(); ?>
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
	<?php $blocks['countdown'] = ob_get_clean(); ?>

	<?php ob_start(); ?>
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
	<?php $blocks['story'] = ob_get_clean(); ?>

	<?php ob_start(); ?>
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
	<?php $blocks['when'] = ob_get_clean(); ?>

	<?php ob_start(); ?>
	<?php if ($squad): ?>
	<section class="sec wrap" id="squad">
		<div class="w-head reveal"><span class="eyebrow"><?= count($squad) ?> people holding it together</span><h2>The squad</h2></div>
		<?php
		$tagsOf = static fn (array $s): string => (string) $s['side'] === 'both' ? 'bride groom' : (string) $s['side'];
		$hasB = (bool) array_filter($squad, static fn ($s) => (string) $s['side'] !== 'groom');
		$hasG = (bool) array_filter($squad, static fn ($s) => (string) $s['side'] !== 'bride');
		$tabs = $hasB && $hasG;
		$rel = AccountController::RELATIONSHIP;
		?>
		<?php if ($tabs): ?>
			<div class="chips chips--center reveal" data-choose="#squad-list" role="tablist" aria-label="The squad">
				<button class="chip is-on" type="button" data-f="bride" role="tab" aria-pressed="true">Bridesmaids</button>
				<button class="chip" type="button" data-f="groom" role="tab" aria-pressed="false">Groomsmen</button>
			</div>
		<?php endif; ?>
		<div class="squad reveal" id="squad-list">
			<?php foreach ($squad as $s): $tags = $tagsOf($s); [$net, $handle] = social_label((string) ($s['social'] ?? '')); ?>
				<div class="squad__item" data-tags="<?= e($tags) ?>"<?= $tabs && !str_contains($tags, 'bride') ? ' hidden' : '' ?>>
					<?php if ((string) $s['photo'] !== ''): ?><?= sq((string) $s['photo'], (string) $s['name'], 'squad__pic') ?><?php else: ?><span class="squad__av" aria-hidden="true"><?= e(mb_strtoupper(mb_substr(trim((string) $s['name']), 0, 1))) ?></span><?php endif; ?>
					<span class="squad__name"><?= e($s['name']) ?></span>
					<?php if (trim((string) $s['role']) !== ''): ?><span class="squad__role"><?= e($s['role']) ?></span><?php endif; ?>
					<?php $facts = array_filter([['work', (string) ($s['work'] ?? '')], ['star', (string) ($s['hobby'] ?? '')], ['heart', $rel[(string) ($s['relationship'] ?? '')] ?? '']], static fn ($f) => trim($f[1]) !== '' && $f[1] !== $rel['']); ?>
					<?php if ($facts): ?>
						<ul class="squad__facts">
							<?php foreach ($facts as [$ic, $txt]): ?><li><?= icon($ic) ?><span><?= e($txt) ?></span></li><?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<?php if (trim((string) $s['words']) !== ''): ?><span class="squad__words"><?= e($s['words']) ?></span><?php endif; ?>
					<?php if ($net !== ''): ?><a class="squad__social" href="<?= e((string) $s['social']) ?>" target="_blank" rel="noopener nofollow ugc" aria-label="<?= e($s['name']) ?> on <?= e($net) ?>"><?= icon('link') ?><span><?= e($handle !== '' ? $handle : $net) ?></span></a><?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>
	<?php $blocks['squad'] = ob_get_clean(); ?>

	<?php ob_start(); ?>
	<?php if ($gifts || $premium): ?>
	<section class="sec wrap" id="gifts">
		<div class="two">
			<?php if ($premium): ?>
			<div class="reveal" id="spray">
				<div class="w-head"><span class="eyebrow">From your seat, or from London</span><h2>Spray the couple</h2></div>
				<div class="glass">
					<p class="glass__q">How much are you spraying?</p>
					<div class="counter" data-counter data-min="<?= $curMin ?>" data-max="<?= $curMin * 2000 ?>" data-step="<?= $curMin ?>" data-value="<?= $curStart ?>" data-comma data-input="#g-amount" id="spray-counter">
						<button class="cbtn cbtn--lg" type="button" data-dec aria-label="Less"><?= icon('minus') ?></button>
						<div class="counter__mid" aria-live="polite"><?= dots(number_format($curStart), 'dotnum--md', true) ?><span class="counter__unit"><?= e($curName) ?></span></div>
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
	<?php $blocks['gifts'] = ob_get_clean(); ?>

	<?php ob_start(); ?>
	<?php if (Wedding::has($w, 'live')):
		$yt = '';
		if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|live/|embed/))([A-Za-z0-9_-]{11})~', (string) ($dz['live_url'] ?? ''), $m)) { $yt = $m[1]; } ?>
	<section class="sec wrap w-live-sec" id="live">
		<?php if ($yt !== ''): ?>
			<div class="w-head reveal"><span class="eyebrow">Wherever you are</span><h2>Watch live</h2></div>
			<div class="live-player reveal"><iframe src="https://www.youtube-nocookie.com/embed/<?= e($yt) ?>" title="<?= e($names) ?> livestream" loading="lazy" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe></div>
		<?php endif; ?>
		<?php $liveAt = Reminder::startsAt($w); ?>
		<div class="tile w-live reveal<?= $yt !== '' ? ' gap-t-sm' : '' ?>">
			<span class="live__dot" aria-hidden="true"></span>
			<div><span class="v"><?= $yt !== '' ? 'Watch live' : 'Livestream' ?></span><span class="k"><?= e($dz['live'] ?? ($yt !== '' ? ($liveAt && $liveAt > time() ? 'Starts ' . date('D j M, g:ia', $liveAt) . '. It plays right here.' : 'It plays right here.') : 'The link goes up here before the day. Tap Remind me and we will email you when it does.')) ?></span></div>
			<?php if (!$liveAt || $liveAt > time() - 3600): ?><button class="pill pill--sm pill--glass" type="button" data-open="remind"><?= icon('bell') ?>Remind me</button><?php endif; ?>
		</div>
	</section>
	<?php endif; ?>
	<?php $blocks['live'] = ob_get_clean(); ?>

	<?php ob_start(); ?>
	<?php if (Wedding::has($w, 'gallery')): ?>
	<section class="sec wrap" id="photos">
		<div class="w-head reveal"><span class="eyebrow"><?= (int) $tally['photos'] ? (int) $tally['photos'] . ' from guests' : 'From guests' ?></span><h2>Photos</h2></div>
		<?php if ($photos): ?>
			<div class="gallery reveal">
				<?php foreach (array_slice($photos, 0, 12) as $ph): ?><?= sq((string) $ph['image'], (string) $ph['body']) ?><?php endforeach; ?>
			</div>
		<?php endif; ?>
		<form class="photo-add reveal" method="post" action="<?= e(App::url('/w/' . $slug . '/photo')) ?>" enctype="multipart/form-data">
			<?= Guard::field() ?>
			<label class="photo-pick"><input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required data-photo-input><?= icon('camera') ?><span data-photo-name>Add your photo from the day</span></label>
			<div class="photo-add__row">
				<label class="sr" for="p-name">Your name</label>
				<input class="input" id="p-name" name="name" required placeholder="Your name" autocomplete="name">
				<label class="sr" for="p-body">Caption</label>
				<input class="input" id="p-body" name="body" maxlength="300" placeholder="A caption, if you like">
				<button class="cbtn wish-compose__go" type="submit" aria-label="Add your photo"><?= icon('arrow') ?></button>
			</div>
		</form>
	</section>
	<?php endif; ?>
	<?php $blocks['photos'] = ob_get_clean(); ?>

	<?php ob_start(); ?>
	<?php
	// Who knows them best: one quiz per partner, written by the couple, and a leaderboard for each.
	$qz = Wedding::has($w, 'squad') ? Quiz::forWedding($w) : ['a' => [], 'b' => []];
	$qSides = array_keys(array_filter($qz));
	$played = $_SESSION['quiz'][(int) $w['id']] ?? [];
	$qOn = isset($played['b']) && !isset($played['a']) && in_array('b', $qSides, true) ? 'b' : ($qSides[0] ?? 'a');
	if ($qSides): ?>
	<section class="sec wrap" id="quiz">
		<div class="w-head reveal">
			<span class="eyebrow">Who knows them best?</span>
			<h2>The quiz</h2>
			<p class="sub">Pick the one you know. Answer what their family and friends should know, and see where you land on their leaderboard.</p>
		</div>
		<?php if (count($qSides) > 1): ?>
			<div class="quiz-pick reveal" role="tablist" aria-label="Whose quiz">
				<?php foreach ($qSides as $sd): $nm = (string) ($sd === 'a' ? $w['partner_a'] : $w['partner_b']); ?>
					<button class="quiz-pick__b<?= $sd === $qOn ? ' is-on' : '' ?>" type="button" role="tab" aria-selected="<?= $sd === $qOn ? 'true' : 'false' ?>" aria-controls="quiz-<?= $sd ?>" data-quiz-pick="<?= $sd ?>">
						<span class="quiz-pick__i" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($nm, 0, 1))) ?></span>
						<span><span class="quiz-pick__k">I know</span><span class="quiz-pick__n"><?= e($nm) ?></span></span>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<div class="quiz-sides" data-quiz-sides>
		<?php foreach ($qSides as $sd):
			$nm = (string) ($sd === 'a' ? $w['partner_a'] : $w['partner_b']);
			$board = Quiz::board((int) $w['id'], $sd, 10);
			$players = Quiz::players((int) $w['id'], $sd);
			$me = $played[$sd] ?? null; ?>
			<div class="quiz-side<?= $sd === $qOn ? ' is-on' : '' ?>" id="quiz-<?= $sd ?>" data-quiz-side="<?= $sd ?>" role="tabpanel">
				<?php if ($me): ?>
					<div class="glass quiz quiz--done reveal">
						<span class="eyebrow"><?= e($nm) ?>&rsquo;s quiz</span>
						<p class="quiz__big"><?= dots((string) $me['score'], 'dotnum--md') ?><span>of <?= (int) $me['total'] ?></span></p>
						<p class="quiz__score">You are number <?= (int) $me['rank'] ?> of <?= $players ?> on <?= e($nm) ?>&rsquo;s leaderboard.</p>
						<?php foreach ($qSides as $other): if ($other !== $sd && empty($played[$other])): ?>
							<button class="pill pill--glass pill--sm" type="button" data-quiz-pick="<?= $other ?>">Now try <?= e((string) ($other === 'a' ? $w['partner_a'] : $w['partner_b'])) ?>&rsquo;s</button>
						<?php endif; endforeach; ?>
					</div>
				<?php else: ?>
					<form class="glass glass--plain quiz reveal" method="post" action="<?= e(App::url('/w/' . $w['slug'] . '/quiz')) ?>">
						<?= Guard::field() ?><input type="hidden" name="side" value="<?= $sd ?>">
						<p class="quiz__lead"><?= count($qz[$sd]) ?> questions about <?= e($nm) ?></p>
						<?php foreach ($qz[$sd] as $qi => [$q, $opts]): ?>
							<fieldset class="quiz__q">
								<legend><span class="quiz__n"><?= $qi + 1 ?></span><span><?= e($q) ?></span></legend>
								<div class="quiz__opts">
									<?php foreach ($opts as $oi => $o): ?>
										<label class="chip"><input type="radio" name="q[<?= $qi ?>]" value="<?= $oi ?>" required><span><?= e($o) ?></span></label>
									<?php endforeach; ?>
								</div>
							</fieldset>
						<?php endforeach; ?>
						<label class="field quiz__name"><span>Your name, for the leaderboard</span><input class="input" name="name" maxlength="60" autocomplete="name" required></label>
						<button class="pill pill--block" type="submit">See how well I know <?= e($nm) ?></button>
					</form>
				<?php endif; ?>
				<div class="glass glass--plain board reveal">
					<div class="board__head"><p class="board__h">Who knows <?= e($nm) ?> best</p><span class="board__n"><?= $players ?> played</span></div>
					<?php if ($board): ?>
						<ol class="board__list">
							<?php foreach ($board as $bi => $b): $mineRow = $me && (string) $b['name'] === (string) $me['name'] && (int) $b['score'] === (int) $me['score']; ?>
								<li class="board__row<?= $bi === 0 ? ' is-top' : '' ?><?= $mineRow ? ' is-me' : '' ?>">
									<span class="board__rank"><?= $bi + 1 ?></span>
									<span class="board__name"><?= e((string) $b['name']) ?></span>
									<span class="board__score"><?= (int) $b['score'] ?><span>/<?= (int) $b['total'] ?></span></span>
								</li>
							<?php endforeach; ?>
						</ol>
					<?php else: ?>
						<p class="board__empty">Nobody yet. Be the first name on <?= e($nm) ?>&rsquo;s board.</p>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
		</div>
	</section>
	<?php elseif ($mine && Wedding::has($w, 'squad')): ?>
	<section class="sec wrap" id="quiz">
		<div class="glass glass--plain quiz-empty reveal">
			<span class="eyebrow">Only you can see this</span>
			<h2>Who knows you best?</h2>
			<p class="sub">Write a short quiz for each of you: the nickname at home, the food you always order, where you grew up. Guests pick the one they know and climb that person&rsquo;s leaderboard.</p>
			<a class="pill" href="<?= e(App::url('/account/quiz')) ?>">Write your quizzes</a>
		</div>
	</section>
	<?php endif; ?>
	<?php $blocks['quiz'] = ob_get_clean(); ?>

	<?php ob_start(); ?>
	<?php $palette = Wedding::has($w, 'colours') && is_array($dz['palette'] ?? null) ? $dz['palette'] : []; if ($palette): ?>
	<section class="sec wrap w-palette-sec" id="colours">
		<div class="w-head reveal"><span class="eyebrow">Dress in these, if you can</span><h2>Colours of the day</h2></div>
		<div class="palette reveal"><?php foreach ($palette as [$pn, $pc]): ?><span class="palette__i"><i style="background:<?= e($pc) ?>"></i><b><?= e($pn) ?></b><small><?= e($pc) ?></small></span><?php endforeach; ?></div>
	</section>
	<?php endif; ?>
	<?php $blocks['palette'] = ob_get_clean(); ?>

	<?php ob_start(); ?>
	<?php if ($vendors): ?>
	<section class="sec wrap" id="vendors">
		<div class="w-head reveal"><span class="eyebrow">Booked and reviewed by <?= e($names) ?></span><h2>The people behind our day</h2></div>
		<div class="rows narrow reveal" style="width:min(100%,720px)">
			<?php foreach ($vendors as $v): ?>
				<a class="row-item" href="<?= e(App::url('/vendors/' . $v['slug'])) ?>">
					<?= sq((string) $v['logo'], '', 'row-item__pic') ?>
					<span><span class="row-item__t"><?= e($v['name']) ?><?php if ((int) $v['verified']): ?> <span class="badge"><?= icon('check') ?>Verified</span><?php endif; ?></span><span class="row-item__s"><?= e($v['category'] . ' · ' . $v['city']) ?></span><?php if ($v['said']): ?><span class="row-item__s">“<?= e(mb_strimwidth((string) $v['said'], 0, 90, '…')) ?>”</span><?php endif; ?></span>
					<span class="row-item__end"><?= str_repeat('★', max(0, min(5, (int) $v['stars']))) ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>
	<?php $blocks['vendors'] = ob_get_clean(); ?>

	<?php ob_start(); ?>
	<section class="sec wrap w-wall" id="wall">
		<div class="w-head reveal"><span class="eyebrow"><?= (int) $tally['notes'] ? (int) $tally['notes'] . ' wishes so far' : 'Be the first' ?></span><h2>Wall of love</h2></div>
		<?php $canVoice = Wedding::has($w, 'voice'); ?>
		<form class="wish-compose reveal" method="post" action="<?= e(App::url('/w/' . $slug . '/note')) ?>"<?= $canVoice ? ' enctype="multipart/form-data" data-voice-form' : '' ?>>
			<?= Guard::field() ?>
			<label class="sr" for="n-body">Your wish</label>
			<textarea class="input" id="n-body" name="body" rows="3" maxlength="1000"<?= $canVoice ? '' : ' required' ?> placeholder="<?= $canVoice ? 'Write something they will keep, or leave a voice note…' : 'Write something they will keep…' ?>"></textarea>
			<?php if ($canVoice): ?>
				<div class="voice-rec" data-voice-rec>
					<button class="voice-rec__go" type="button" data-voice-start><?= icon('mic') ?><span data-voice-label>Record a voice note</span><span class="voice-rec__t" data-voice-time hidden>0:00</span></button>
					<div class="voice voice--draft" data-voice-draft hidden>
						<button class="cbtn voice__play" type="button" data-vplay aria-label="Play your voice note"><?= icon('play') ?></button>
						<span class="voice__bar"><i data-vbar></i></span><span class="voice__t" data-vt>0:00</span>
						<audio preload="metadata" data-vaudio></audio>
						<button class="cbtn voice__x" type="button" data-voice-clear aria-label="Remove the voice note"><?= icon('close') ?></button>
					</div>
					<label class="voice-rec__file" data-voice-pick><input type="file" name="voice" accept="audio/*" data-voice-file><span>or upload a recording</span></label>
				</div>
			<?php elseif ($mine): ?>
				<p class="voice-up"><?= icon('mic') ?><span>Guests can leave voice notes on Premium and High Society pages.</span><?= up_tag('premium', 'Voice notes on your wall') ?></p>
			<?php endif; ?>
			<div class="wish-compose__row">
				<label class="sr" for="n-name">Your name</label>
				<input class="input" id="n-name" name="name" required placeholder="Your name" autocomplete="name">
				<button class="cbtn wish-compose__go" type="submit" aria-label="Post your wish"><?= icon('arrow') ?></button>
			</div>
		</form>
		<?php if ($notes): ?>
			<div class="wishes reveal">
				<?php foreach ($notes as $n): ?>
					<div class="wish<?= (string) $n['kind'] === 'voice' ? ' wish--voice' : '' ?>">
						<?php if ((string) $n['kind'] === 'voice' && (string) ($n['audio'] ?? '') !== ''): ?>
							<div class="voice" data-voice-note>
								<button class="cbtn voice__play" type="button" data-vplay aria-label="Play the voice note from <?= e((string) $n['name']) ?>"><?= icon('play') ?></button>
								<span class="voice__bar"><i data-vbar></i></span><span class="voice__t" data-vt><?= icon('mic') ?></span>
								<audio preload="none" data-vaudio src="<?= e(App::url('assets/' . (string) $n['audio'])) ?>"></audio>
							</div>
						<?php endif; ?>
						<?php if (trim((string) $n['body']) !== ''): ?><p><?= e((string) $n['body']) ?></p><?php endif; ?>
						<span><?= e((string) $n['name']) ?> · <?= e(ago((string) $n['created_at'])) ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>
	<?php $blocks['wall'] = ob_get_clean(); ?>

	<?php foreach ($layout['order'] as $bk) { if (!in_array($bk, $layout['hidden'], true)) { echo $blocks[$bk] ?? ''; } } ?>

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
		<?php if ($isFree): ?>
			<p class="sub"><?= e($design['name']) ?> design · Made on <a href="<?= e(App::url('/')) ?>">Wedding Websites &amp; More</a> · <a href="<?= e(App::url('/join')) ?>">Make yours free</a></p>
		<?php else: ?>
			<p class="sub"><?= e($design['name']) ?> design · Made on <a href="<?= e(App::url('/')) ?>">Wedding Websites &amp; More</a></p>
			<a class="pill pill--glass wed-foot__cta" href="<?= e(App::url('/pricing#premium')) ?>">Make your own Premium wedding page, designed your way</a>
		<?php endif; ?>
	</footer>
</article>

<nav class="wed-bar" aria-label="On this page">
	<button type="button" data-open="rsvp"><?= icon('check') ?>RSVP</button>
	<?php if ($gifts && $shows('gifts')): ?><a href="#gifts"><?= icon('gift') ?>Gifts</a><?php endif; ?>
	<?php if ($premium && $shows('gifts')): ?><button type="button" data-open="give" data-give="Spray"><?= icon('wallet') ?>Spray</button><?php endif; ?>
	<?php if ($shows('wall')): ?><a href="#wall"><?= icon('heart') ?>Wishes</a><?php endif; ?>
	<button type="button" data-share="<?= e($pageUrl) ?>" data-share-ping="<?= e(App::url('/w/' . $slug . '/share')) ?>"><?= icon('share') ?>Share</button>
</nav>

<?php if ($mine): ?><?= View::partial('partials/upgrade') ?><?php endif; ?>
<?php if (Wedding::has($w, 'live')): ?>
<div class="sheet" id="remind" role="dialog" aria-modal="true" aria-labelledby="remind-t">
	<div class="sheet__grab" aria-hidden="true"></div>
	<div class="sheet__head">
		<a class="cbtn cbtn--sm" href="#" data-close aria-label="Close"><?= icon('close') ?></a>
		<h2 id="remind-t">Remind me</h2>
		<span></span>
	</div>
	<form method="post" action="<?= e(App::url('/w/' . $slug . '/remind')) ?>" class="form">
		<?= Guard::field() ?>
		<p class="glass__q"><?= Reminder::link($w) !== '' ? 'We will email you fifteen minutes before ' . e($names) . ' go live.' : 'We will email you the moment ' . e($names) . ' add the livestream link, and again fifteen minutes before it starts.' ?></p>
		<label class="field"><span>Your email</span><input class="input" type="email" name="email" required autocomplete="email" inputmode="email" placeholder="you@example.com"></label>
		<button class="pill pill--block" type="submit"><?= icon('bell') ?>Remind me</button>
		<p class="after-pill">Two emails at most, each with a link to stop them. We never use it for anything else.</p>
		<?php if ((string) $w['visibility'] === 'public'): ?><a class="pill pill--glass pill--block" href="<?= e(App::url('/w/' . $slug . '/live.ics')) ?>" data-no-prefetch><?= icon('calendar') ?>Or add it to your calendar</a><?php endif; ?>
	</form>
</div>
<?php endif; ?>

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
	<div class="counter counter--sheet" data-counter data-min="<?= $curMin ?>" data-max="<?= $curMin * 2000 ?>" data-step="<?= $curMin ?>" data-value="<?= $curStart ?>" data-comma data-input="#g-amount">
		<button class="cbtn" type="button" data-dec aria-label="Less"><?= icon('minus') ?></button>
		<div class="counter__mid"><?= dots(number_format($curStart), 'dotnum--md', true) ?><span class="counter__unit"><?= e($curName) ?></span></div>
		<button class="cbtn" type="button" data-inc aria-label="More"><?= icon('plus') ?></button>
	</div>
	<input type="hidden" id="g-amount" value="<?= $curStart ?>">
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
