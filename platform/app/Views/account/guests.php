<?= View::partial('account/_head', ['h' => 'Guests & RSVPs', 'w' => $w, 'sub' => 'Everyone who replied', 'now' => 'guests']) ?>
	<div class="kpis">
		<?php foreach ([[(int) ($sum['yes'] ?? 0), 'Coming'], [(int) ($sum['heads'] ?? 0), 'Seats needed'], [(int) ($sum['maybe'] ?? 0), 'Not sure'], [(int) ($sum['no'] ?? 0), "Can't make it"]] as [$n, $l]): ?>
			<div class="tile"><span class="k"><?= e($l) ?></span><?= dots((string) $n, 'dotnum--sm kpi-dots') ?></div>
		<?php endforeach; ?>
	</div>
	<div class="guest-tools gap-t">
		<form class="searchbox" method="get" role="search"><?= icon('search') ?><input class="input" type="search" name="q" value="<?= e($q) ?>" placeholder="Find a guest" aria-label="Find a guest"></form>
		<a class="pill pill--glass pill--sm" href="<?= e(App::url('/account/guests?csv=1' . ($q !== '' ? '&q=' . urlencode($q) : ''))) ?>" data-no-prefetch><?= icon('download') ?>CSV</a>
	</div>
	<?php if (!$rows): ?>
		<div class="glass glass--plain empty gap-t"><span class="cbtn"><?= icon('people') ?></span><p>No replies yet. Share your page link and they will start coming in.</p></div>
	<?php else: ?>
		<div class="rows gap-t">
			<?php foreach ($rows as $g): ?>
				<form method="post" action="<?= e(App::url('/account/guests/' . $g['id'])) ?>" class="row-item guest-row">
					<?= Guard::field() ?>
					<span class="row-item__ico"><?= e(mb_strtoupper(mb_substr((string) $g['name'], 0, 1))) ?></span>
					<span>
						<span class="row-item__t"><?= e($g['name']) ?></span>
						<span class="row-item__s"><?= e(trim(($g['phone'] ? $g['phone'] . ' · ' : '') . ((int) $g['party'] > 1 ? $g['party'] . ' people' : '1 person'), ' ·')) ?><?= $g['pass_code'] ? ' · pass ' . e($g['pass_code']) : '' ?></span>
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
