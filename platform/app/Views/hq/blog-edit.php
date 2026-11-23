<?php
/** HQ: write or edit a post. @var ?array $p */
$v = static fn (string $k, string $d = ''): string => (string) ($p[$k] ?? $d);
$src = $p ? ((string) ($p['source'] ?? '') !== '' ? (string) $p['source'] : (string) $p['body']) : '';
$pub = $p && $p['published_at'] ? (int) strtotime((string) $p['published_at']) : 0;
?>
<?= View::partial('hq/_head', ['h' => $p ? 'Edit post' : 'Write a post', 'sub' => 'The blog', 'now' => 'blog']) ?>
	<form method="post" enctype="multipart/form-data" class="split-2 gap-t-sm">
		<?= Guard::field() ?>
		<div class="form glass glass--plain glass--tight">
			<label class="field"><span>Title</span><input class="input" name="title" value="<?= e($v('title')) ?>" required maxlength="255" placeholder="How to plan a London wedding with family flying in from Lagos"></label>
			<label class="field"><span>Standfirst: one or two sentences under the title</span><textarea class="input" name="excerpt" rows="2" maxlength="500"><?= e($v('excerpt')) ?></textarea></label>
			<label class="field"><span>The post</span><textarea class="input blog-body" name="body" rows="22" placeholder="Write here. Leave a blank line between paragraphs."><?= e($src) ?></textarea></label>
			<details class="faq__q blog-help">
				<summary><span>How to format</span><i class="faq__i" aria-hidden="true"></i></summary>
				<p>A blank line starts a new paragraph. <code>## Heading</code> and <code>### Smaller heading</code>. <code>&gt; A quote</code>, with <code>— Name</code> on the last line to credit it. <code>- item</code> for a list, <code>1. item</code> for numbers. <code>**bold**</code>, <code>*italic*</code>, <code>[words](https://link)</code>. For an interview, start the question with <code>Q:</code> and the answer with <code>A:</code>.</p>
			</details>
			<div class="form-grid form-grid--2">
				<label class="field"><span>Add a photo to the end</span><input class="input" type="file" name="inline" accept="image/jpeg,image/png,image/webp"></label>
				<label class="field"><span>Its caption</span><input class="input" name="inline_alt" maxlength="160"></label>
			</div>
		</div>
		<aside class="acct-list">
			<div class="form glass glass--plain glass--tight">
				<label class="field"><span>Status</span><select class="input" name="status"><option value="draft"<?= $v('status', 'draft') !== 'live' ? ' selected' : '' ?>>Draft</option><option value="live"<?= $v('status') === 'live' ? ' selected' : '' ?>>Published</option></select></label>
				<label class="field"><span>Section</span><select class="input" name="kind">
					<?php foreach (Blog::SECTIONS as $sx): ?><option value="<?= e($sx[0]) ?>"<?= $v('kind', 'guide') === $sx[0] ? ' selected' : '' ?>><?= e($sx[1]) ?></option><?php endforeach; ?>
				</select></label>
				<label class="field"><span>Author</span><input class="input" name="author" value="<?= e($v('author')) ?>" placeholder="Wedding Websites &amp; More"></label>
				<div class="form-grid form-grid--2">
					<label class="field"><span>Publish date</span><input class="input" type="date" name="date" value="<?= $pub ? e(date('Y-m-d', $pub)) : '' ?>"></label>
					<label class="field"><span>Time</span><input class="input" type="time" name="time" value="<?= $pub ? e(date('H:i', $pub)) : '09:00' ?>"></label>
				</div>
				<p class="help">Leave the date empty to publish now. A future date schedules it.</p>
				<label class="check"><span>Lead story on the blog</span><input type="checkbox" name="featured" value="1"<?= (int) $v('featured', '0') ? ' checked' : '' ?>></label>
				<button class="pill pill--block" type="submit">Save</button>
				<?php if ($p): ?><a class="pill pill--glass pill--block" href="<?= e(App::url('/blog/' . $p['slug'])) ?>" target="_blank" rel="noopener"><?= $v('status') === 'live' ? 'View it' : 'Preview' ?></a><?php endif; ?>
			</div>
			<div class="form glass glass--plain glass--tight">
				<h2 class="h3">Cover photo</h2>
				<?php if ($v('cover') !== ''): ?><?= sq($v('cover'), '', 'sq--wide') ?><label class="check"><span>Remove the cover</span><input type="checkbox" name="no_cover" value="1"></label><?php endif; ?>
				<label class="field"><span><?= $v('cover') !== '' ? 'Replace it' : 'Upload one' ?></span><input class="input" type="file" name="cover" accept="image/jpeg,image/png,image/webp"></label>
				<p class="help">Wide photos work best. It is also the picture when the post is shared.</p>
			</div>
			<div class="form glass glass--plain glass--tight">
				<h2 class="h3">Search</h2>
				<label class="field"><span>Address</span><input class="input" name="slug" value="<?= e($v('slug')) ?>" placeholder="made-from-the-title"></label>
				<label class="field"><span>Title for Google (optional)</span><input class="input" name="meta_title" value="<?= e($v('meta_title')) ?>" maxlength="190" placeholder="About 60 characters"></label>
				<label class="field"><span>Description for Google (optional)</span><textarea class="input" name="meta_desc" rows="3" maxlength="300" placeholder="About 155 characters"><?= e($v('meta_desc')) ?></textarea></label>
			</div>
			<?php if ($p): ?>
				<button class="pill pill--danger pill--block" type="submit" name="do" value="delete" formnovalidate data-confirm="Delete this post for good?">Delete post</button>
			<?php endif; ?>
		</aside>
	</form>
</div>
