<?php
/**
 * HQ: the words and pictures on each page of the site, in the order they appear there.
 *
 * @var string $page @var array $fields
 */
$urls = ['home' => '/', 'site' => '/', 'weddings' => '/weddings', 'rankings' => '/rankings', 'vendors' => '/vendors', 'market' => '/market',
         'community' => '/community', 'pricing' => '/pricing', 'templates' => '/templates', 'about' => '/about', 'legal' => '/terms'];
?>
<?= View::partial('hq/_head', ['h' => 'Site content', 'sub' => 'The words and pictures on every page', 'now' => 'content']) ?>
	<nav class="chips gap-t-sm" aria-label="Pages">
		<?php foreach (Content::PAGES as $k => [$label]): ?>
			<a class="chip<?= $k === $page ? ' is-on' : '' ?>" href="<?= e(App::url('/hq/content?page=' . $k)) ?>"<?= $k === $page ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
		<?php endforeach; ?>
	</nav>
	<form method="post" enctype="multipart/form-data" class="split-2 gap-t-sm">
		<?= Guard::field() ?>
		<div class="form glass glass--plain glass--tight">
			<?php if (!$fields): ?><p class="help">Nothing to edit on this page yet.</p><?php endif; ?>
			<?php foreach ($fields as $f): $id = str_replace('.', '__', $f['key']); $cur = Content::get($f['key'], $f['default']); $edited = $cur !== $f['default']; ?>
				<?php if ($f['type'] === 'timg'): ?>
					<div class="field cms-img">
						<span><?= e(Content::label($f)) ?> · <?= e(str_replace('-', ' ', explode('.', $f['key'])[2] ?? '')) ?><?= $edited ? ' <b class="cms-edited">changed</b>' : '' ?></span>
						<?php if ($cur !== ''): ?><?= sq($cur, '', 'sq--wide cms-img__pic') ?><?php else: ?><p class="help">No picture yet.</p><?php endif; ?>
						<input class="input" type="file" name="img[<?= e($id) ?>]" accept="image/jpeg,image/png,image/webp">
						<?php if ($edited): ?><label class="check"><span>Go back to the standard picture</span><input type="checkbox" name="reset[<?= e($id) ?>]" value="1"></label><?php endif; ?>
					</div>
				<?php elseif ($f['type'] === 'tbody'): ?>
					<label class="field"><span><?= e(Content::label($f)) ?>: <?= e(ucfirst(str_replace('-', ' ', explode('.', $f['key'])[2] ?? ''))) ?><?= $edited ? ' <b class="cms-edited">changed</b>' : '' ?></span>
						<textarea class="input blog-body" name="v[<?= e($id) ?>]" rows="10" placeholder="Leave empty to keep the standard page. Write here to replace it whole, in the same format as the blog."><?= e($edited ? $cur : '') ?></textarea></label>
				<?php else: ?>
					<label class="field"><span><?= e(Content::label($f)) ?><?= $edited ? ' <b class="cms-edited">changed</b>' : '' ?></span>
						<?php if ($f['type'] === 'tl' || mb_strlen($f['default']) > 90): ?>
							<textarea class="input" name="v[<?= e($id) ?>]" rows="3"><?= e($cur) ?></textarea>
						<?php else: ?>
							<input class="input" name="v[<?= e($id) ?>]" value="<?= e($cur) ?>">
						<?php endif; ?>
					</label>
					<?php if ($edited): ?><p class="help cms-was">Standard: <?= e(mb_strimwidth($f['default'], 0, 120, '…')) ?></p><?php endif; ?>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
		<aside class="acct-list">
			<div class="form glass glass--plain glass--tight">
				<h2 class="h3"><?= e(Content::PAGES[$page][0]) ?></h2>
				<p class="help">Every box is a piece of this page, in the order it appears. Change the words, save, and they are live. Clear a box to go back to the standard text.</p>
				<button class="pill pill--block" type="submit">Save changes</button>
				<a class="pill pill--glass pill--block" href="<?= e(App::url($urls[$page] ?? '/')) ?>" target="_blank" rel="noopener">Open the page</a>
			</div>
			<div class="form glass glass--plain glass--tight">
				<h2 class="h3">Elsewhere</h2>
				<p class="help">Blog posts are in <a class="linkish" href="<?= e(App::url('/hq/blog')) ?>">Blog</a>. Prices, exchange rates and Google Drive are in <a class="linkish" href="<?= e(App::url('/hq/settings')) ?>">Settings</a>. Couples, vendors and sellers edit their own pages.</p>
			</div>
		</aside>
	</form>
</div>
