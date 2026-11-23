<?php
/**
 * The marketplace from HQ: sellers with open reports first, each with what they were reported for,
 * then everyone else. Actions: verify, pause (asks them to verify), reinstate, remove, delete.
 *
 * @var array $sellers @var array $reports
 */
$state = ['active' => 'Live', 'suspended' => 'Paused, verifying', 'removed' => 'Removed'];
?>
<?= View::partial('hq/_head', ['h' => 'Marketplace', 'now' => 'market', 'sub' => 'Sellers, reports and categories']) ?>
	<div class="form-actions"><a class="pill pill--sm pill--glass" href="<?= e(App::url('/hq/categories')) ?>"><?= icon('grid') ?>Categories</a></div>
	<p class="help">Once a seller has <?= Market::SUSPEND_AT ?> open reports, they are paused automatically and emailed to verify their business by hand. At <?= Market::REMOVE_AT ?>, they are removed from the site. You can reverse either step here.</p>
	<div class="acct-list gap-t-sm">
		<?php foreach ($sellers as $v): $n = (int) $v['reports']; $st = (string) ($v['status'] ?? 'active'); ?>
			<div class="glass glass--plain glass--tight hq-seller<?= $n >= Market::SUSPEND_AT ? ' is-hot' : '' ?>" id="s<?= (int) $v['id'] ?>">
				<div class="hq-seller__top">
					<?= sq((string) $v['logo'], '', 'row-item__pic', false, '<span class="fill-art" aria-hidden="true">' . e(mb_strtoupper(mb_substr((string) $v['name'], 0, 1))) . '</span>') ?>
					<div>
						<p class="row-item__t"><a class="linkish" href="<?= e(App::url('/vendors/' . $v['slug'])) ?>"><?= e((string) $v['name']) ?></a><?php if ((int) $v['verified']): ?> <span class="badge"><?= icon('check') ?>Verified</span><?php endif; ?></p>
						<p class="row-item__s"><?= e(trim((string) $v['category'] . ' · ' . (string) ($v['email'] ?? '') . ' · ' . (int) $v['listings'] . ' listing' . ((int) $v['listings'] === 1 ? '' : 's'), ' ·')) ?></p>
					</div>
					<span class="hq-seller__end"><span class="badge<?= $st !== 'active' ? ' badge--warn' : '' ?>"><?= e($state[$st] ?? $st) ?></span><?php if ($n): ?><span class="badge badge--warn"><?= $n ?> report<?= $n === 1 ? '' : 's' ?></span><?php endif; ?></span>
				</div>
				<?php if (!empty($reports[(int) $v['id']])): ?>
					<details class="hq-reports"><summary>What they were reported for</summary>
						<?php foreach ($reports[(int) $v['id']] as $r): ?>
							<div class="tile"><p><b><?= e(Market::REASONS[(string) $r['reason']] ?? (string) $r['reason']) ?></b><?= $r['title'] ? ' · on “' . e((string) $r['title']) . '”' : '' ?></p>
								<?php if (trim((string) $r['body']) !== ''): ?><p class="help"><?= nl2br(e((string) $r['body'])) ?></p><?php endif; ?>
								<p class="help"><?= e((string) ($r['name'] ?: 'Someone')) ?> · <?= e((string) $r['email']) ?> · <?= e(ago((string) $r['created_at'])) ?></p></div>
						<?php endforeach; ?>
					</details>
				<?php endif; ?>
				<form method="post" class="form-actions hq-seller__acts">
					<?= Guard::field() ?><input type="hidden" name="id" value="<?= (int) $v['id'] ?>">
					<button class="pill pill--sm<?= Market::premium($v) ? '' : ' pill--glass' ?>" name="do" value="premium"><?= Market::premium($v) ? 'Premium: on' : 'Make Premium' ?></button>
					<?php if ((int) $v['verified']): ?><button class="pill pill--sm pill--glass" name="do" value="unverify">Unverify</button><?php else: ?><button class="pill pill--sm" name="do" value="verify">Verify</button><?php endif; ?>
					<?php if ($st === 'active'): ?><button class="pill pill--sm pill--glass" name="do" value="suspend" data-confirm="Pause <?= e((string) $v['name']) ?> and email them to ask them to verify?">Pause &amp; ask to verify</button><?php else: ?><button class="pill pill--sm" name="do" value="reinstate">Reinstate</button><?php endif; ?>
					<?php if ($n): ?><button class="pill pill--sm pill--glass" name="do" value="clear">Clear reports</button><?php endif; ?>
					<?php if ($st !== 'removed'): ?><button class="pill pill--sm pill--glass" name="do" value="remove" data-confirm="Remove <?= e((string) $v['name']) ?> from the site?">Remove</button><?php endif; ?>
					<button class="pill pill--sm pill--danger" name="do" value="delete" data-confirm="Delete <?= e((string) $v['name']) ?> and all their listings for good? This cannot be undone.">Delete</button>
				</form>
			</div>
		<?php endforeach; ?>
	</div>
</div>
