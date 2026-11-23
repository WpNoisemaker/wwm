<?= View::partial('hq/_head', ['h' => 'Weddings', 'now' => 'weddings']) ?>
	<?php $driveOn = Drive::connected(); if (!$driveOn): ?>
		<p class="note"><?= icon('info') ?><span>Connect Google Drive in <a href="<?= e(App::url('/hq/settings#drive')) ?>">Settings</a> to give each wedding a folder for its guests' photos.</span></p>
	<?php endif; ?>
	<div class="rows">
		<?php foreach ($rows as $w): $f = Drive::folder((int) $w['id']); $dc = $f ? Drive::counts((int) $w['id']) : null; ?>
			<div class="hq-wed" id="w<?= (int) $w['id'] ?>">
				<a class="row-item" href="<?= e(App::url('/w/' . $w['slug'])) ?>">
					<?= sq((string) $w['cover'], '', 'row-item__pic', false, initials($w)) ?>
					<span><span class="row-item__t"><?= e(Wedding::names($w)) ?></span><span class="row-item__s"><?= e($w['city'] ?: 'No city') ?> · <?= e($w['email'] ?: 'No owner') ?> · <?= e(Design::forWedding($w)['name']) ?></span></span>
					<span class="row-item__end"><span class="badge"><?= e(Site::tier((string) $w['plan'])) ?></span><span class="sub"><?= e($w['visibility']) ?></span></span>
				</a>
				<?php if (Wedding::has($w, 'gallery')): ?>
					<div class="hq-wed__drive">
						<?php if ($f): ?>
							<span class="hq-wed__k"><?= icon('image') ?>Guest photos</span>
							<a class="linkish" href="<?= e((string) $f['link']) ?>" target="_blank" rel="noopener"><?= e((string) $f['name']) ?></a>
							<span class="sub"><?= (int) $dc['sent'] ?> in Drive<?= $dc['waiting'] ? ' · ' . (int) $dc['waiting'] . ' waiting' : '' ?></span>
						<?php elseif ($driveOn): ?>
							<span class="hq-wed__k"><?= icon('image') ?>No Drive folder yet</span>
							<form method="post" action="<?= e(App::url('/hq/drive/folder/' . (int) $w['id'])) ?>" class="hq-wed__form">
								<?= Guard::field() ?>
								<button class="pill pill--sm" type="submit" name="do" value="create">Make a folder</button>
								<input class="input" name="link" placeholder="or paste a folder link" aria-label="Drive folder link for <?= e(Wedding::names($w)) ?>">
								<button class="pill pill--sm pill--glass" type="submit" name="do" value="attach">Use it</button>
							</form>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>
