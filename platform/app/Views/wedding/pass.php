<?php
/**
 * A guest's entry pass: the link in their approval email. At the gate the couple (or our team)
 * signed in on this page sees the guest's face next to the code and can check them in.
 * @var array $w @var ?array $g @var string $code @var bool $door
 */
$names = Wedding::names($w);
$page  = App::url('/w/' . $w['slug']);
?>
<div class="wrap pass-page">
	<?= flashes() ?>
	<?php if (!$g): ?>
		<div class="glass glass--plain empty">
			<span class="cbtn"><?= icon('ticket') ?></span>
			<p>This code is not on <?= e($names) ?>&rsquo;s guest list. Check the code in your email, or ask the couple.</p>
			<a class="pill pill--glass" href="<?= e($page) ?>">Open their page</a>
		</div>
	<?php else: $party = (int) $g['party']; $faceUrl = $g['photo'] !== '' ? App::url('/face/' . (int) $g['id'] . '?c=' . rawurlencode((string) $g['pass_code'])) : ''; ?>
		<div class="pass glass">
			<span class="eyebrow">Entry pass · <?= e($names) ?></span>
			<?php if ($faceUrl): ?><img class="pass__face" src="<?= e($faceUrl) ?>" alt="Photo of <?= e($g['name']) ?>" width="120" height="120"><?php endif; ?>
			<p class="pass__name"><?= e($g['name']) ?></p>
			<p class="pass__party"><?= $party === 1 ? 'Admits 1 person' : 'Admits ' . $party . ' people' ?></p>
			<p class="pass__code" aria-label="Pass code <?= e(implode(' ', str_split((string) $g['pass_code']))) ?>"><?php foreach (str_split(Rsvp::pretty((string) $g['pass_code'])) as $ch): ?><span<?= $ch === '-' ? ' class="pass__dash"' : '' ?>><?= e($ch) ?></span><?php endforeach; ?></p>
			<p class="pass__when"><?= e(Wedding::when($w)) ?><?= ($where = Wedding::where($w)) !== '' ? ' · ' . e($where) : '' ?></p>
			<?php if ($g['checked_in']): ?>
				<p class="badge badge--solid pass__in"><?= icon('check') ?>Checked in <?= e(date('g:ia', strtotime((string) $g['checked_in']))) ?></p>
			<?php endif; ?>
			<?php if ($door): ?>
				<div class="pass__door">
					<p class="help">You are signed in as the couple. Check that the face matches the person in front of you.</p>
					<?php if (!$g['checked_in']): ?>
						<form method="post" action="<?= e(App::url('/w/' . $w['slug'] . '/pass/' . $g['pass_code'] . '/in')) ?>"><?= Guard::field() ?><button class="pill pill--block" type="submit"><?= icon('check') ?>Check them in</button></form>
					<?php endif; ?>
					<a class="pill pill--block pill--glass" href="<?= e(App::url('/door/' . $w['slug'])) ?>">Back to the door list</a>
				</div>
			<?php else: ?>
				<p class="after-pill">Show this at the entrance. Take a screenshot so you have it even without a signal. Please do not share your code.</p>
				<a class="pill pill--glass pill--block" href="<?= e($page) ?>">Programme, venue and dress code</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
