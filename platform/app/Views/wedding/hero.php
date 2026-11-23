<?php
/**
 * The top of a wedding page. Each design composes the same pieces (squircle photos, the frosted
 * RSVP card with its − / + counter, dot numerals, tiles, the white pill) in its own way.
 *
 * @var array $w @var array $design @var array $dz @var string $names @var string $photo2 @var ?array $next @var bool $premium
 */
$hero   = $design['hero'];
$a      = (string) $w['partner_a'];
$b      = (string) $w['partner_b'];
$when   = !empty($w['wed_date']) ? date('l j F Y', (int) strtotime((string) $w['wed_date'])) : 'Date to be announced';
$whenS  = !empty($w['wed_date']) ? date('l j F', (int) strtotime((string) $w['wed_date'])) : 'Date to come';
$city   = trim((string) $w['city']);
$where  = Wedding::where($w);
$dDate  = Wedding::dotDate($w);
$cover  = (string) $w['cover'];
$init   = initials($w);
$isFree = (string) $w['plan'] === 'free';
$party  = $isFree ? 1 : 2;
$ini2   = e(mb_strtoupper(mb_substr($a, 0, 1))) . '<span>&amp;</span>' . e(mb_strtoupper(mb_substr($b, 0, 1)));

$eyebrow = '<span class="eyebrow">' . e($when) . ($city !== '' ? ' · ' . e($city) : '') . '</span>';
$tag     = trim((string) $w['tagline']) !== '' ? '<p class="w-tag">' . e($w['tagline']) . '</p>' : '';
$amp     = '<span class="w-amp">&amp;</span>';

ob_start(); ?>
<div class="glass w-card">
	<p class="glass__q">How many of you are coming?</p>
	<div class="counter" data-counter data-min="1" data-max="<?= (int) ($dz['rsvp_max'] ?? 10) ?>" data-value="<?= $party ?>" data-input="#r-party">
		<button class="cbtn cbtn--lg" type="button" data-dec aria-label="One fewer"><?= icon('minus') ?></button>
		<div class="counter__mid" aria-live="polite"><?= dots((string) $party, '', true) ?><span class="counter__unit" data-unit="Guest|Guests"><?= $party === 1 ? 'Guest' : 'Guests' ?></span></div>
		<button class="cbtn cbtn--lg" type="button" data-inc aria-label="One more"><?= icon('plus') ?></button>
	</div>
	<div class="total">
		<p class="total__k"><?= e($next['label'] ?? 'The celebration') ?></p>
		<?php $tv = trim((string) ($next['venue'] ?? '')) !== '' ? (string) $next['venue'] : ((string) $w['venue'] ?: ($city ?: 'Venue to come')); ?>
		<p class="total__v<?= mb_strlen($tv) > 18 ? ' total__v--long' : '' ?>"><?= e($tv) ?></p>
	</div>
</div>
<a class="pill pill--block w-rsvp" href="#rsvp" data-open="rsvp">Send RSVP</a>
<p class="after-pill">No account needed. It takes a few seconds.</p>
<?php $card = (string) ob_get_clean();

$photoA = sq($cover, $names, '', true, $init);
$photoB = sq($photo2 !== '' ? $photo2 : $cover, '', '', false, $init);
?>
<section class="w-hero w-hero--<?= e($hero) ?> wrap">
<?php switch ($hero):

case 'portrait': ?>
	<div class="w-portrait">
		<div class="w-ring"><?= sq($cover, $names, 'sq--tall', true, $init) ?></div>
	</div>
	<div class="w-col">
		<span class="eyebrow"><?= e($dz['hosts'] ?? 'Together with their families') ?></span>
		<h1 class="w-names w-names--caps"><?= e($a) ?> <?= $amp ?> <?= e($b) ?></h1>
		<?php if ($dDate !== ''): ?><div class="w-dotdate"><?= dots($dDate) ?></div><?php endif; ?>
		<?php if ($where !== ''): ?><p class="w-where"><?= e($where) ?></p><?php endif; ?>
		<?= $tag ?>
		<div class="w-cardwrap"><?= $card ?></div>
	</div>
<?php break;

case 'framed': ?>
	<div class="w-frame"><?= sq($cover, $names, 'sq--wide', true, $init) ?>
		<span class="w-pearl" aria-hidden="true"><svg viewBox="0 0 100 100"><circle cx="50" cy="50" r="46" pathLength="360" stroke-dasharray="0 12"/></svg><b><?= $ini2 ?></b></span>
	</div>
	<div class="w-col w-col--center">
		<?= $eyebrow ?>
		<h1 class="w-names"><?= e($a) ?> <?= $amp ?> <?= e($b) ?></h1>
		<?= $tag ?>
	</div>
	<div class="w-pair"><?= $photoB ?><div><?= $card ?></div></div>
<?php break;

case 'wide': ?>
	<div class="w-wide"><?= sq($cover, $names, 'sq--wide', true, $init) ?><span class="pollen" aria-hidden="true"></span></div>
	<div class="w-col w-col--center">
		<?= $eyebrow ?>
		<h1 class="w-names"><?= e($a) ?> <?= $amp ?> <?= e($b) ?></h1>
		<?= $tag ?>
	</div>
	<div class="w-pair"><?= $photoB ?><div><?= $card ?></div></div>
<?php break;

case 'split': ?>
	<div class="w-split__pic"><?= sq($cover, $names, 'sq--tall', true, $init) ?></div>
	<div class="w-col">
		<?= $eyebrow ?>
		<h1 class="w-names w-names--stack"><span><?= e($a) ?></span><?= $amp ?><span><?= e($b) ?></span></h1>
		<div class="ticks ticks--row w-ticks" aria-hidden="true"><?= str_repeat('<i></i>', 15) ?><i class="is-on"></i><?= str_repeat('<i></i>', 15) ?></div>
		<?php if ($dDate !== ''): ?><div class="w-dotdate"><?= dots($dDate) ?></div><?php endif; ?>
		<?php if ($where !== ''): ?><p class="w-where"><?= e($where) ?></p><?php endif; ?>
		<?= $tag ?>
		<div class="w-cardwrap"><?= $card ?></div>
	</div>
<?php break;

case 'circle': ?>
	<div class="w-circle">
		<svg class="w-beads" viewBox="0 0 100 100" aria-hidden="true"><circle cx="50" cy="50" r="48" pathLength="300" stroke-dasharray="0 10"/><circle class="w-beads__in" cx="50" cy="50" r="44.5" pathLength="300" stroke-dasharray="0 5"/></svg>
		<?= sq($cover, $names, 'sq--round', true, $init) ?>
		<?php if ($photo2 !== ''): ?><?= sq($photo2, '', 'sq--round w-circle__small') ?><?php endif; ?>
	</div>
	<div class="w-col">
		<?= $eyebrow ?>
		<h1 class="w-names"><?= e($a) ?> <?= $amp ?> <?= e($b) ?></h1>
		<?= $tag ?>
		<div class="w-cardwrap"><?= $card ?></div>
	</div>
<?php break;

case 'overlay': ?>
	<div class="w-over"><?= sq($cover, $names, 'sq--wide', true, $init) ?>
		<div class="w-over__cap"><?= $eyebrow ?><h1 class="w-names"><?= e($a) ?> <?= $amp ?> <?= e($b) ?></h1></div>
	</div>
	<?= $tag !== '' ? '<div class="w-col w-col--center">' . $tag . '</div>' : '' ?>
	<div class="w-pair"><?= $photoB ?><div><?= $card ?></div></div>
<?php break;

case 'blocks': ?>
	<div class="w-col w-col--center">
		<?= $eyebrow ?>
		<h1 class="w-names w-names--bold"><span><?= e($a) ?></span><span><?= $amp ?> <?= e($b) ?></span></h1>
	</div>
	<div class="weave" aria-hidden="true"><?= str_repeat('<i></i><i></i><i></i><i></i>', 5) ?></div>
	<div class="w-blocks">
		<?= sq($cover, $names, 'sq--square', true, $init) ?>
		<div class="w-block">
			<span class="k">Save the date</span>
			<?php if ($dDate !== ''): ?><?= dots($dDate) ?><?php endif; ?>
			<span class="v"><?= e($w['venue'] ?: $city) ?></span>
		</div>
		<?= sq($photo2 !== '' ? $photo2 : $cover, '', 'sq--square w-blocks__b', false, $init) ?>
	</div>
	<div class="w-pair w-pair--solo"><div><?= $card ?></div></div>
<?php break;

case 'album': ?>
	<div class="album">
		<figure class="print print--a"><i class="tape" aria-hidden="true"></i><?= sq($cover, $names, 'sq--square', true, $init) ?><figcaption><?= e(Wedding::short($w)) ?></figcaption></figure>
		<figure class="print print--b"><i class="tape" aria-hidden="true"></i><?= sq($photo2 !== '' ? $photo2 : $cover, '', 'sq--square', false, $init) ?><figcaption><?= e($city ?: 'Us') ?></figcaption></figure>
		<span class="album__heart album__heart--a" aria-hidden="true"><?= icon('s-heart') ?></span>
		<span class="album__heart album__heart--b" aria-hidden="true"><?= icon('s-heart') ?></span>
	</div>
	<div class="w-col">
		<?= $eyebrow ?>
		<h1 class="w-names"><?= e($a) ?> <?= $amp ?> <?= e($b) ?></h1>
		<?= $tag ?>
		<div class="w-cardwrap"><?= $card ?></div>
	</div>
<?php break;

case 'horizon': ?>
	<div class="w-col w-col--center">
		<?= $eyebrow ?>
		<h1 class="w-names"><?= e($a) ?> <?= $amp ?> <?= e($b) ?></h1>
		<?= $tag ?>
	</div>
	<div class="horizon">
		<?= sq($cover, $names, 'sq--round horizon__sun', true, $init) ?>
		<svg class="horizon__land" viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0 64 C150 52 300 60 450 55 C600 50 750 62 900 56 C1020 52 1110 58 1200 54 L1200 120 L0 120 Z"/></svg>
		<svg class="horizon__tree" viewBox="0 0 120 84" aria-hidden="true"><path d="M8 27 C22 15 52 10 68 12 C88 10 108 16 117 25 C103 30 82 31 61 30 C40 31 21 31 8 27 Z"/><path d="M57 30 L55 52 L50 84 L66 84 L62 52 L62 30 Z"/><path d="M57 40 L40 30 L44 29 L58 37 Z M62 38 L80 28 L83 30 L63 42 Z"/></svg>
	</div>
	<div class="w-pair"><?= $photoB ?><div><?= $card ?></div></div>
<?php break;

case 'party': ?>
	<div class="confetti" aria-hidden="true"><?= str_repeat('<i></i>', 18) ?></div>
	<div class="hero__head hero__head--show">
		<?= $eyebrow ?>
		<h1 class="w-names w-names--bold"><?= e($a) ?> <?= $amp ?> <?= e($b) ?></h1>
		<?= $tag ?>
	</div>
	<div class="trio">
		<div class="trio__a"><span class="sq-label">The party</span><?= $photoA ?></div>
		<div class="trio__b"><span class="sq-label"><?= e($city ?: 'Owambe') ?></span><?= $photoB ?></div>
		<div class="trio__c"><?= $card ?></div>
	</div>
<?php break;

case 'invite':
	$full = is_array($dz['names'] ?? null) ? $dz['names'] : [$a, $b]; ?>
	<div class="trio trio--invite">
		<?php $lab = is_array($dz['labels'] ?? null) ? $dz['labels'] : []; ?>
		<div class="trio__a"><span class="sq-label"><?= e((string) ($lab[0] ?? ($w['venue'] ?: 'The venue'))) ?></span><?= $photoA ?></div>
		<div class="trio__b"><span class="sq-label"><?= e((string) ($lab[1] ?? ($city ?: 'Our story'))) ?></span><?= $photoB ?></div>
		<div class="trio__c">
			<div class="glass invite">
				<p class="invite__hosts"><?= e($dz['hosts'] ?? 'Together with their families') ?></p>
				<p class="invite__msg"><?= e($dz['invite'] ?? 'request the pleasure of your company at the wedding of') ?></p>
				<h1 class="invite__names"><span><?= e($full[0]) ?></span><span class="w-amp">&amp;</span><span><?= e($full[1]) ?></span></h1>
				<span class="invite__orn" aria-hidden="true"><?= str_repeat('<i></i>', 9) ?></span>
				<?php if ($dDate !== ''): ?><div class="w-dotdate"><?= dots($dDate) ?></div><?php endif; ?>
				<p class="invite__when"><?= e($whenS) ?><?= !empty($w['wed_time']) ? ' · ' . e(date('g:ia', (int) strtotime('2000-01-01 ' . $w['wed_time']))) : '' ?></p>
				<?php if ($where !== ''): ?><p class="invite__where"><?= e($where) ?></p><?php endif; ?>
			</div>
			<a class="pill pill--block w-rsvp" href="#rsvp" data-open="rsvp">Send RSVP</a>
			<?= $tag !== '' ? '<p class="after-pill">' . e($w['tagline']) . '</p>' : '' ?>
		</div>
	</div>
<?php break;

default: /* trio: the reference screen itself */ ?>
	<div class="hero__head">
		<?= $eyebrow ?>
		<h1 class="hero__title"><?= e($names) ?></h1>
		<?= $tag !== '' ? '<p class="lede">' . e($w['tagline']) . '</p>' : '' ?>
	</div>
	<p class="w-date" data-phone><?= e($whenS) ?><?= $city !== '' ? ' · ' . e($city) : '' ?></p>
	<div class="trio">
		<div class="trio__a"><span class="sq-label">Save the date</span><?= $photoA ?></div>
		<div class="trio__b"><span class="sq-label"><?= e($city ?: 'With love') ?></span><?= $photoB ?></div>
		<div class="trio__c"><?= $card ?></div>
	</div>
<?php endswitch; ?>
</section>
