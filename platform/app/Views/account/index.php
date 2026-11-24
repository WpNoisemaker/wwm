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
				<p class="help">Your story, programme, squad and registry are all in place. You can change anything from the tabs above at any time.</p>
			<?php endif; ?>
		</div>

		<?php if (Managed::has($w)):
			// Premium and High Society: ask our team on WhatsApp or by email. The message is written for them,
			// with the page, the account it belongs to and their permission for us to make the changes.
			$me    = Auth::user();
			$plan  = Site::tier((string) $w['plan']);
			$ask   = "Hello Wedding Websites & More,\n\nPlease make these changes to our wedding page:\n\n[Write what you want added or changed here]\n\n"
			       . "Wedding page: " . $url . "\nAccount email: " . (string) ($me['email'] ?? '') . "\nPlan: " . $plan . "\n\n"
			       . "I give Wedding Websites & More express permission to sign in to our account and make these changes to our wedding page on our behalf.\n\n" . Wedding::names($w);
			$wa    = preg_replace('/\D+/', '', Site::setting('whatsapp', ''));
			$mail  = Site::setting('email', 'more@' . Share::host());
			$subj  = 'Changes to our wedding page: ' . Wedding::names($w);
		?>
		<div class="gap-t" id="managed">
			<div class="glass glass--plain glass--tight cdash__help">
				<span class="row-item__t">Want us to make the changes?</span>
				<span class="row-item__s">This is optional. Your <?= e($plan) ?> plan includes help from our team, but you can still do everything yourself. Send us what you want changed on WhatsApp or by email. The message is already written: add your changes and send it.</span>
				<div class="cdash__ask">
					<?php if ($wa !== ''): ?><a class="pill pill--wa" href="<?= e('https://wa.me/' . $wa . '?text=' . rawurlencode($ask)) ?>" target="_blank" rel="noopener"><?= icon('chat') ?>WhatsApp us</a><?php endif; ?>
					<a class="pill pill--glass" href="<?= e('mailto:' . $mail . '?subject=' . rawurlencode($subj) . '&body=' . rawurlencode($ask)) ?>"><?= icon('mail') ?>Email us</a>
				</div>
				<p class="help">The message includes your page link, your account email and your written permission for our team to make the changes. Never send us your password: our team can open your page without it.</p>
				<?php if (!empty($requests)): ?>
					<div class="rows gap-t-sm">
						<?php foreach ($requests as $rq): ?>
							<div class="row-item"><span class="row-item__ico"><?= icon($rq['status'] === 'done' ? 'check' : 'clock') ?></span>
								<span><span class="row-item__t"><?= e(mb_strimwidth((string) $rq['body'], 0, 90, '…')) ?></span><span class="row-item__s"><?= $rq['status'] === 'done' ? 'Done' . (trim((string) $rq['reply']) !== '' ? ': ' . e((string) $rq['reply']) : '') : 'With our team' ?> · <?= e(ago((string) $rq['created_at'])) ?></span></span></div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php endif; ?>

		<form method="post" action="<?= e(App::url('/sign-out')) ?>" class="gap-t center"><?= Guard::field() ?><button class="linkish" type="submit">Sign out</button></form>
	</div>
</div>
