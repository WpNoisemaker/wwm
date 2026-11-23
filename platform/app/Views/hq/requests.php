<?php /** Change requests from Premium and High Society couples. @var array $rows */ ?>
<?= View::partial('hq/_head', ['h' => 'Requests', 'now' => 'requests', 'sub' => 'Changes Premium and High Society couples asked us to make']) ?>
	<p class="help">Open the couple's dashboard with <b>Manage page</b>, make the change, then mark the request as done. The couple will get an email.</p>
	<?php if (!$rows): ?>
		<div class="glass glass--plain empty gap-t"><span class="cbtn"><?= icon('chat') ?></span><p>No requests yet.</p></div>
	<?php endif; ?>
	<div class="acct-list gap-t-sm">
		<?php foreach ($rows as $r): $files = json_decode((string) $r['files'], true) ?: []; ?>
			<div class="glass glass--plain glass--tight acct-card<?= $r['status'] === 'open' ? ' is-open' : '' ?>">
				<div class="lock-row">
					<h2 class="h3"><a class="linkish" href="<?= e(App::url('/w/' . $r['slug'])) ?>" target="_blank" rel="noopener"><?= e($r['partner_a'] . ' & ' . $r['partner_b']) ?></a> <span class="badge"><?= e(Site::tier((string) $r['plan'])) ?></span></h2>
					<span class="badge<?= $r['status'] === 'open' ? ' badge--warn' : '' ?>"><?= $r['status'] === 'open' ? 'Open' : 'Done' ?> · <?= e(ago((string) $r['created_at'])) ?></span>
				</div>
				<p><?= nl2br(e((string) $r['body'])) ?></p>
				<?php if ($files): ?><div class="ph-grid"><?php foreach ($files as $f): ?><a class="ph-item" href="<?= e(img_url((string) $f)) ?>" target="_blank" rel="noopener"><?= photo((string) $f, '') ?></a><?php endforeach; ?></div><?php endif; ?>
				<?php if ($r['status'] === 'done' && trim((string) $r['reply']) !== ''): ?><p class="help">Our note: <?= e((string) $r['reply']) ?></p><?php endif; ?>
				<?php if ($r['status'] === 'open'): ?>
					<form method="post" class="form">
						<?= Guard::field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
						<label class="sr" for="rq<?= (int) $r['id'] ?>">A note to the couple</label>
						<input class="input" id="rq<?= (int) $r['id'] ?>" name="reply" maxlength="2000" placeholder="A note to the couple (optional)">
						<div class="form-actions"><a class="pill pill--sm pill--glass" href="<?= e(App::url('/hq/manage/' . (int) $r['wedding_id'])) ?>"><?= icon('pen') ?>Manage page</a><button class="pill pill--sm" type="submit" name="do" value="done">Mark done</button></div>
					</form>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>
