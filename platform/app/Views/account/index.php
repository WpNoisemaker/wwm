<?php
/**
 * The couple's dashboard: their link, two buttons, four numbers and what is still missing. Nothing else.
 * @var array $w @var array $tally @var array $dash @var ?array $next @var array $todo
 */
$public  = (string) $w['visibility'] === 'public';
$days    = Wedding::daysTo($w);
$url     = App::url('/w/' . $w['slug']);
?>
<?= View::partial('account/_head', ['h' => Wedding::names($w), 'w' => $w, 'sub' => Wedding::when($w), 'now' => 'overview']) ?>
	<div class="cdash">
		<div class="glass glass--tight linkcard">
			<div><span class="k">Your page</span><span class="v v--sm linkcard__url"><?= e(preg_replace('#^https?://#', '', $url)) ?></span></div>
			<button class="cbtn" type="button" data-share="<?= e($url) ?>" aria-label="Share your page"><?= icon('share') ?></button>
		</div>
		<div class="cdash__acts">
			<a class="pill" href="<?= e(App::url('/account/page')) ?>"><?= icon('pen') ?>Edit your page</a>
			<a class="pill pill--glass" href="<?= e($url) ?>"><?= icon('eye') ?>View it</a>
		</div>
		<?php if (!$public): ?>
			<p class="note"><?= icon('lock') ?><span>Your page is private. Only people with the pass code can open it. <a href="<?= e(App::url('/account/page#visibility')) ?>">Make it public</a> when you are ready.</span></p>
		<?php endif; ?>

		<div class="cdash__stats">
			<a class="tile cdash__stat" href="<?= e(App::url('/account/guests')) ?>"><span class="k">Guests coming</span><span class="v"><?= number_format((int) $dash['heads']) ?></span><span class="sub"><?= plural((int) $dash['guests'], 'reply', 'replies') ?></span></a>
			<?php $pend = (int) ($dash['pending'] ?? 0); ?>
			<a class="tile cdash__stat<?= $pend ? ' is-on' : '' ?>" href="<?= e(App::url('/account/guests#waiting')) ?>"><span class="k">Waiting for you</span><span class="v"><?= number_format($pend) ?></span><span class="sub"><?= $pend ? 'RSVPs to approve' : 'All RSVPs checked' ?></span></a>
			<a class="tile cdash__stat" href="<?= e(App::url('/account/money')) ?>"><span class="k">Gifts received</span><span class="v"><?= money(short_money((int) $tally['raised']), (string) $w['currency']) ?></span><span class="sub"><?= money(short_money((int) $dash['week']), (string) $w['currency']) ?> this week</span></a>
			<div class="tile cdash__stat"><span class="k"><?= $days !== null && $days >= 0 ? 'Days to go' : 'Wedding' ?></span><span class="v"><?= $days !== null && $days >= 0 ? (int) $days : 'Done' ?></span><span class="sub"><?= number_format((int) $w['views']) ?> page views</span></div>
		</div>

		<?php if ($next): $u = Wedding::until($next['when']); ?>
			<div class="tile next-up"><span class="k">Next up</span><span class="v"><?= e($next['label']) ?></span><span class="sub">in <?= (int) $u['d'] ?> day<?= (int) $u['d'] === 1 ? '' : 's' ?><?= $next['venue'] ? ' · ' . e($next['venue']) : '' ?></span></div>
		<?php endif; ?>

		<?php $left = array_values(array_filter($todo, static fn ($t) => !$t[2])); ?>
		<div class="gap-t">
			<div class="block-head"><h2 class="h2"><?= $left ? 'Still to add' : 'Your page has everything' ?></h2></div>
			<?php if ($left): ?>
				<div class="rows">
					<?php foreach ($left as [$label, $href]): ?>
						<a class="row-item" href="<?= e(App::url($href)) ?>">
							<span class="row-item__ico"><?= icon('plus') ?></span>
							<span><span class="row-item__t"><?= e($label) ?></span></span>
							<span class="row-item__end"><span class="badge">Add</span></span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php else: ?>
				<p class="help">Story, programme, squad and registry are in. You can change anything from the tabs above at any time.</p>
			<?php endif; ?>
		</div>

		<?php if (Managed::has($w)): ?>
		<div class="gap-t" id="managed">
			<details class="glass glass--plain glass--tight cdash__help"<?= array_filter($requests ?? [], static fn ($r) => $r['status'] !== 'done') ? ' open' : '' ?>>
				<summary><span><span class="row-item__t">Want us to make the changes?</span><span class="row-item__s">Optional. Your <?= e(Site::tier((string) $w['plan'])) ?> plan includes our team. You can still do everything yourself.</span></span><?= icon('chev-down') ?></summary>
				<?php if (!Managed::managing()): ?>
				<form method="post" action="<?= e(App::url('/account/request')) ?>" enctype="multipart/form-data" class="form gap-t-sm">
					<?= Guard::field() ?>
					<label class="field"><span>What should we add or change?</span><textarea class="input" name="body" rows="3" required maxlength="4000" placeholder="Add the after-party on 20 December, 8pm, at the same venue."></textarea></label>
					<label class="photo-pick"><input type="file" name="files[]" accept="image/jpeg,image/png,image/webp" multiple data-photo-input><?= icon('camera') ?><span data-photo-name>Add up to 3 photos</span></label>
					<button class="pill pill--block" type="submit">Send to our team</button>
				</form>
				<?php endif; ?>
				<?php if (!empty($requests)): ?>
					<div class="rows gap-t-sm">
						<?php foreach ($requests as $rq): ?>
							<div class="row-item"><span class="row-item__ico"><?= icon($rq['status'] === 'done' ? 'check' : 'clock') ?></span>
								<span><span class="row-item__t"><?= e(mb_strimwidth((string) $rq['body'], 0, 90, '…')) ?></span><span class="row-item__s"><?= $rq['status'] === 'done' ? 'Done' . (trim((string) $rq['reply']) !== '' ? ': ' . e((string) $rq['reply']) : '') : 'With our team' ?> · <?= e(ago((string) $rq['created_at'])) ?></span></span></div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</details>
		</div>
		<?php endif; ?>

		<form method="post" action="<?= e(App::url('/sign-out')) ?>" class="gap-t center"><?= Guard::field() ?><button class="linkish" type="submit">Sign out</button></form>
	</div>
</div>
