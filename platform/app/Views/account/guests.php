<?= View::partial('account/_head', ['h' => 'Guests & RSVPs', 'w' => $w]) ?>
<section class="sec wrap">
	<div class="tally">
		<?php foreach ([
			[(int) ($sum['yes'] ?? 0), 'coming'], [(int) ($sum['heads'] ?? 0), 'seats needed'],
			[(int) ($sum['maybe'] ?? 0), 'still deciding'], [(int) ($sum['no'] ?? 0), 'cannot make it'],
		] as [$n, $l]): ?>
			<span class="tally__i"><b class="dot"><?= e(number_format($n)) ?></b><em><?= e($l) ?></em></span>
		<?php endforeach; ?>
	</div>

	<form class="hero__search wfind" method="get" role="search">
		<span class="hero__search-ico"><?= icon('search', 19) ?></span>
		<input type="search" name="q" value="<?= e($q) ?>" placeholder="Find a guest" aria-label="Find a guest">
		<button class="btn btn--sm" type="submit">Search</button>
	</form>
	<p class="sub"><a href="<?= e(App::url('/account/guests?csv=1' . ($q !== '' ? '&q=' . urlencode($q) : ''))) ?>">Download as CSV</a> — opens in Excel or Sheets.</p>

	<?php if (!$rows): ?>
		<div class="blank"><p class="blank__h">No replies yet.</p><p class="sub">Share your page link and they will start coming in.</p></div>
	<?php else: ?>
		<div class="rows guests">
			<?php foreach ($rows as $g): ?>
				<form method="post" action="<?= e(App::url('/account/guests/' . $g['id'])) ?>" class="guest">
					<?= Guard::field() ?>
					<span class="guest__who">
						<b><?= e($g['name']) ?></b>
						<small><?= e(trim(($g['phone'] ? $g['phone'] . ' · ' : '') . ($g['party'] > 1 ? $g['party'] . ' people' : '1 person'), ' ·')) ?><?= $g['pass_code'] ? ' · pass ' . e($g['pass_code']) : '' ?></small>
						<?php if ($g['note']): ?><small class="guest__note">&ldquo;<?= e($g['note']) ?>&rdquo;</small><?php endif; ?>
					</span>
					<select name="coming" aria-label="Coming?">
						<?php foreach (['yes' => 'Coming', 'maybe' => 'Maybe', 'no' => 'No'] as $k => $t): ?>
							<option value="<?= e($k) ?>"<?= (string) $g['coming'] === $k ? ' selected' : '' ?>><?= e($t) ?></option>
						<?php endforeach; ?>
					</select>
					<input name="table_no" value="<?= e($g['table_no']) ?>" placeholder="Table" aria-label="Table" size="5">
					<button class="btn btn--sm btn--ghost" type="submit">Save</button>
				</form>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
