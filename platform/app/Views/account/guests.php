<?= View::partial('account/_head', ['h' => 'Guests & RSVPs', 'w' => $w, 'sub' => 'Everyone who replied', 'now' => 'guests']) ?>
	<div class="kpis">
		<?php foreach ([[(int) ($sum['yes'] ?? 0), 'Coming'], [(int) ($sum['heads'] ?? 0), 'Seats needed'], [(int) ($sum['maybe'] ?? 0), 'Not sure'], [(int) ($sum['no'] ?? 0), "Can't make it"]] as [$n, $l]): ?>
			<div class="tile"><span class="k"><?= e($l) ?></span><?= dots((string) $n, 'dotnum--sm kpi-dots') ?></div>
		<?php endforeach; ?>
	</div>
	<section class="gap-t" id="waiting">
		<div class="block-head"><h2 class="h2"><?= $wait ? 'Waiting for you (' . count($wait) . ')' : 'Waiting for you' ?></h2>
			<?php if (count($wait) > 1): ?><form method="post" action="<?= e(App::url('/account/guests/approve-all')) ?>" data-confirm="Approve all <?= count($wait) ?> and email each one their code?"><?= Guard::field() ?><button class="pill pill--sm" type="submit">Approve all</button></form><?php endif; ?>
		</div>
		<?php if (!$wait): ?>
			<p class="help">Nobody is waiting. Every new RSVP shows here first. When you approve it, the guest gets an email with their access code.</p>
		<?php else: ?>
			<div class="rows">
				<?php foreach ($wait as $g): ?>
					<div class="row-item rsvp-wait">
						<?php if ($g['photo'] !== ''): ?><img class="rsvp-wait__face" src="<?= e(App::url('/face/' . (int) $g['id'])) ?>" alt="Photo of <?= e($g['name']) ?>" width="56" height="56" loading="lazy"><?php else: ?><span class="row-item__ico"><?= e(mb_strtoupper(mb_substr((string) $g['name'], 0, 1))) ?></span><?php endif; ?>
						<span>
							<span class="row-item__t"><?= e($g['name']) ?></span>
							<span class="row-item__s"><?= (int) $g['party'] > 1 ? (int) $g['party'] . ' people' : '1 person' ?> · <?= $g['coming'] === 'maybe' ? 'Not sure yet' : 'Coming' ?> · <?= e(ago((string) $g['created_at'])) ?></span>
							<span class="row-item__s"><?= e(trim($g['email'] . ($g['phone'] ? ' · ' . $g['phone'] : ''), ' ·')) ?></span>
							<?php if ($g['note']): ?><span class="row-item__s">“<?= e($g['note']) ?>”</span><?php endif; ?>
						</span>
						<form class="rsvp-wait__acts" method="post" action="<?= e(App::url('/account/guests/' . (int) $g['id'] . '/decide')) ?>">
							<?= Guard::field() ?>
							<button class="pill pill--sm" type="submit" name="do" value="approve">Approve</button>
							<button class="pill pill--sm pill--glass" type="submit" name="do" value="decline">Decline</button>
						</form>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>

	<section class="gap-t" id="door">
		<div class="block-head"><h2 class="h2">At the entrance</h2></div>
		<form class="door glass glass--plain glass--tight" method="post" action="<?= e(App::url('/account/guests/door')) ?>">
			<?= Guard::field() ?>
			<label class="field"><span>Type the guest's code to see their face and check them in</span>
				<span class="door__row"><input class="input" name="code" required autocomplete="off" autocapitalize="characters" spellcheck="false" placeholder="AB7-Q4K" maxlength="12"><button class="pill" type="submit">Look up</button></span>
			</label>
		</form>
	</section>

	<div class="guest-tools gap-t">
		<form class="searchbox" method="get" role="search"><?= icon('search') ?><input class="input" type="search" name="q" value="<?= e($q) ?>" placeholder="Find a guest" aria-label="Find a guest"></form>
		<a class="pill pill--glass pill--sm" href="<?= e(App::url('/account/guests?csv=1' . ($q !== '' ? '&q=' . urlencode($q) : ''))) ?>" data-no-prefetch><?= icon('download') ?>CSV</a>
	</div>
	<?php if (!$rows): ?>
		<div class="glass glass--plain empty gap-t"><span class="cbtn"><?= icon('people') ?></span><p><?= $wait ? 'Nobody approved yet. Approve the replies waiting above.' : 'No replies yet. Share your page link and they will start coming in.' ?></p></div>
	<?php else: ?>
		<div class="rows gap-t">
			<?php foreach ($rows as $g): ?>
				<form method="post" action="<?= e(App::url('/account/guests/' . $g['id'])) ?>" class="row-item guest-row">
					<?= Guard::field() ?>
					<?php if ($g['photo'] !== ''): ?><img class="rsvp-wait__face" src="<?= e(App::url('/face/' . (int) $g['id'])) ?>" alt="" width="56" height="56" loading="lazy"><?php else: ?><span class="row-item__ico"><?= e(mb_strtoupper(mb_substr((string) $g['name'], 0, 1))) ?></span><?php endif; ?>
					<span>
						<span class="row-item__t"><?= e($g['name']) ?></span>
						<span class="row-item__s"><?= e(trim(($g['phone'] ? $g['phone'] . ' · ' : '') . ((int) $g['party'] > 1 ? $g['party'] . ' people' : '1 person'), ' ·')) ?><?= $g['pass_code'] ? ' · code ' . e(Rsvp::pretty((string) $g['pass_code'])) : '' ?><?= $g['status'] === 'declined' ? ' · declined' : '' ?><?= $g['checked_in'] ? ' · checked in' : '' ?></span>
						<?php if ($g['note']): ?><span class="row-item__s">“<?= e($g['note']) ?>”</span><?php endif; ?>
					</span>
					<span class="guest-row__edit">
						<select class="input" name="coming" aria-label="Coming?"><?php foreach (['yes' => 'Coming', 'maybe' => 'Not sure', 'no' => 'No'] as $k => $t): ?><option value="<?= e($k) ?>"<?= (string) $g['coming'] === $k ? ' selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select>
						<input class="input" name="table_no" value="<?= e($g['table_no']) ?>" placeholder="Table" aria-label="Table">
						<button class="cbtn" type="submit" aria-label="Save"><?= icon('check') ?></button>
					</span>
				</form>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
