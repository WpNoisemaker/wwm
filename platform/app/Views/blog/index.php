<?php
/**
 * The blog: a lead story, the sections, then every post.
 *
 * @var ?array $lead @var array $rows @var string $section @var int $page @var int $pages @var array $counts
 */
$s = $section !== '' ? Blog::SECTIONS[$section] : null;
$meta = static function (array $p): string {
	$by = trim((string) ($p['author'] ?? '')) ?: 'Wedding Websites &amp; More';
	$when = $p['published_at'] ? date('j M Y', (int) strtotime((string) $p['published_at'])) : '';
	return ($by === 'Wedding Websites &amp; More' ? $by : e($by)) . ($when !== '' ? ' · ' . e($when) : '');
};
$kindLabel = static fn (array $p): string => Blog::KIND[(string) ($p['kind'] ?? 'guide')] ?? 'Guide';
?>
<div class="page wrap">
	<header class="page-head">
		<span class="eyebrow"><?= $s ? 'The blog' : 'Written for our weddings, not borrowed' ?></span>
		<h1><?= $s ? e($s[1]) : 'Guides, news &amp; interviews' ?></h1>
		<p class="lede"><?= $s ? e($s[2]) : 'Planning advice for African weddings at home and abroad, what is new on the platform, and the couples and vendors behind the day.' ?></p>
	</header>
	<nav class="seg seg--center seg--plans" aria-label="Sections">
		<a class="seg__a<?= $section === '' ? ' is-on' : '' ?>" href="<?= e(App::url('/blog')) ?>"<?= $section === '' ? ' aria-current="page"' : '' ?>>Everything</a>
		<?php foreach (Blog::SECTIONS as $k => $sx): ?>
			<a class="seg__a<?= $section === $k ? ' is-on' : '' ?>" href="<?= e(App::url('/blog/' . $k)) ?>"<?= $section === $k ? ' aria-current="page"' : '' ?>><?= e($sx[1]) ?> <span class="chip__n"><?= (int) ($counts[$k] ?? 0) ?></span></a>
		<?php endforeach; ?>
	</nav>

	<?php if ($lead): ?>
		<a class="lead-story gap-t" href="<?= e(App::url('/blog/' . $lead['slug'])) ?>">
			<?= sq((string) $lead['cover'], '', 'sq--wide lead-story__pic', true) ?>
			<span class="lead-story__body">
				<span class="eyebrow"><?= e($kindLabel($lead)) ?> · <?= Blog::minutes($lead) ?> min read</span>
				<span class="lead-story__t"><?= e((string) $lead['title']) ?></span>
				<?php if (trim((string) $lead['excerpt']) !== ''): ?><span class="lead-story__x"><?= e((string) $lead['excerpt']) ?></span><?php endif; ?>
				<span class="lead-story__m"><?= $meta($lead) ?></span>
			</span>
		</a>
	<?php endif; ?>

	<?php if (!$rows && !$lead): ?>
		<div class="glass glass--plain empty gap-t"><span class="cbtn"><?= icon('book') ?></span><p><?= $s ? 'Nothing in ' . e($s[1]) . ' yet. The first one is on its way.' : 'Nothing published yet.' ?></p><?php if ($s): ?><a class="pill pill--glass" href="<?= e(App::url('/blog')) ?>">Read everything else</a><?php endif; ?></div>
	<?php elseif ($rows): ?>
		<div class="stories gap-t">
			<?php foreach ($rows as $bi => $po): ?>
				<?php if ($bi === 3): ?><?= Promo::html('blog', 0) ?><?php endif; ?>
				<a class="story-card" href="<?= e(App::url('/blog/' . $po['slug'])) ?>">
					<?= sq((string) $po['cover'], '', 'sq--wide') ?>
					<span class="eyebrow"><?= e($kindLabel($po)) ?> · <?= Blog::minutes($po) ?> min read</span>
					<span class="story-card__t"><?= e((string) $po['title']) ?></span>
					<?php if (trim((string) $po['excerpt']) !== ''): ?><span class="story-card__x"><?= e(mb_strimwidth((string) $po['excerpt'], 0, 140, '…')) ?></span><?php endif; ?>
					<span class="story-card__m"><?= $meta($po) ?></span>
				</a>
			<?php endforeach; ?>
		</div>
		<?= pager($page, $pages) ?>
	<?php endif; ?>
	<p class="center sub gap-t"><a class="linkish" href="<?= e(App::url('/blog/feed.xml')) ?>"><?= icon('book') ?> Follow the blog by RSS</a></p>
</div>
