<?php /** HQ: every post, newest first, with what state it is in. @var array $rows */ ?>
<?= View::partial('hq/_head', ['h' => 'Blog', 'sub' => 'Guides, news, features and interviews', 'now' => 'blog']) ?>
	<div class="row row--between gap-t-sm">
		<p class="sub"><?= count($rows) ?> post<?= count($rows) === 1 ? '' : 's' ?></p>
		<a class="pill" href="<?= e(App::url('/hq/blog/new')) ?>"><?= icon('pen') ?>Write a post</a>
	</div>
	<?php if (!$rows): ?>
		<div class="glass glass--plain empty gap-t"><span class="cbtn"><?= icon('book') ?></span><p>No posts yet.</p></div>
	<?php else: ?>
		<div class="rows gap-t-sm">
			<?php foreach ($rows as $r):
				$future = $r['published_at'] && strtotime((string) $r['published_at']) > time();
				$state = $r['status'] !== 'live' ? 'Draft' : ($future ? 'Scheduled for ' . date('j M', (int) strtotime((string) $r['published_at'])) : 'Live'); ?>
				<a class="row-item" href="<?= e(App::url('/hq/blog/' . $r['id'])) ?>">
					<span class="row-item__ico"><?= icon($r['kind'] === 'feature' ? 'star' : ($r['kind'] === 'news' ? 'bell' : 'book')) ?></span>
					<span><span class="row-item__t"><?= e((string) $r['title']) ?><?php if ((int) $r['featured']): ?> <span class="badge">Lead</span><?php endif; ?></span>
						<span class="row-item__s"><?= e(Blog::KIND[(string) $r['kind']] ?? 'Guide') ?> · <?= e(trim((string) $r['author']) ?: 'Wedding Websites & More') ?><?= $r['published_at'] ? ' · ' . e(date('j M Y', (int) strtotime((string) $r['published_at']))) : '' ?></span></span>
					<span class="row-item__end"><span class="badge<?= $state === 'Live' ? ' badge--solid' : '' ?>"><?= e($state) ?></span></span>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
