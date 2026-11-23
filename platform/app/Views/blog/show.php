<?php
/**
 * One post: what it is, who wrote it and when, the photo, the words, then more to read.
 *
 * @var array $p @var string $sec @var bool $draft @var array $more
 */
$kind = (string) ($p['kind'] ?? 'guide');
$by = trim((string) ($p['author'] ?? '')) ?: 'Wedding Websites & More';
$pub = $p['published_at'] ? (int) strtotime((string) $p['published_at']) : 0;
$upd = !empty($p['updated_at']) ? (int) strtotime((string) $p['updated_at']) : 0;
$url = App::url('/blog/' . $p['slug']);
$img = (string) $p['cover'] !== '' ? Share::url('b', (string) $p['slug'], implode('|', [$p['cover'], $p['title'], $kind])) : App::url('assets/img/brand/og.jpg');
$ld = [
	'@context' => 'https://schema.org',
	'@type' => $kind === 'news' ? 'NewsArticle' : 'Article',
	'headline' => mb_substr((string) $p['title'], 0, 110),
	'description' => trim((string) $p['excerpt']),
	'image' => [$img],
	'datePublished' => $pub ? date('c', $pub) : null,
	'dateModified' => date('c', $upd ?: ($pub ?: time())),
	'author' => [$by === 'Wedding Websites & More' ? ['@type' => 'Organization', 'name' => $by, 'url' => App::url('/')] : ['@type' => 'Person', 'name' => $by]],
	'publisher' => ['@type' => 'Organization', 'name' => 'Wedding Websites & More', 'logo' => ['@type' => 'ImageObject', 'url' => App::url('assets/img/brand/icon-512.png')]],
	'mainEntityOfPage' => $url,
	'articleSection' => Blog::SECTIONS[$sec][1],
];
$crumbs = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [
	['@type' => 'ListItem', 'position' => 1, 'name' => 'Blog', 'item' => App::url('/blog')],
	['@type' => 'ListItem', 'position' => 2, 'name' => Blog::SECTIONS[$sec][1], 'item' => App::url('/blog/' . $sec)],
	['@type' => 'ListItem', 'position' => 3, 'name' => (string) $p['title'], 'item' => $url],
]];
?>
<script type="application/ld+json"><?= json_encode(array_filter($ld, static fn ($v) => $v !== null && $v !== ''), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
<script type="application/ld+json"><?= json_encode($crumbs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
<article class="page wrap post-page">
	<?php if ($draft): ?><p class="note note--bad narrow"><?= icon('lock') ?><span>Draft: only HQ can see this. <a href="<?= e(App::url('/hq/blog/' . $p['id'])) ?>">Edit it</a></span></p><?php endif; ?>
	<header class="page-head page-head--show">
		<span class="eyebrow"><a class="linkish" href="<?= e(App::url('/blog/' . $sec)) ?>"><?= e(Blog::SECTIONS[$sec][1]) ?></a> · <?= Blog::minutes($p) ?> min read</span>
		<h1><?= e((string) $p['title']) ?></h1>
		<?php if ((string) $p['excerpt'] !== ''): ?><p class="lede"><?= e((string) $p['excerpt']) ?></p><?php endif; ?>
		<p class="byline"><span class="byline__who"><?= e($by) ?></span><?php if ($pub): ?> · <time datetime="<?= e(date('c', $pub)) ?>"><?= e(date('j F Y', $pub)) ?></time><?php endif; ?><?php if ($upd && $upd - $pub > 86400): ?> · updated <time datetime="<?= e(date('c', $upd)) ?>"><?= e(date('j F Y', $upd)) ?></time><?php endif; ?></p>
	</header>
	<?php if ($p['cover']): ?><div class="post-page__pic"><?= sq((string) $p['cover'], (string) $p['title'], 'sq--wide', true) ?></div><?php endif; ?>
	<div class="prose gap-t"><?= $p['body'] /* written in HQ: either the old site's HTML, or the escaped writing format */ ?></div>
	<div class="prose post-page__share">
		<span class="sub">Found it useful? Pass it on.</span>
		<button class="pill pill--sm pill--glass" type="button" data-share="<?= e($url) ?>"><?= icon('share') ?>Share</button>
		<button class="pill pill--sm pill--glass" type="button" data-copy="<?= e($url) ?>"><?= icon('link') ?>Copy link</button>
	</div>
</article>
<?php if ($more): ?>
<section class="sec wrap">
	<div class="sec-head"><span class="eyebrow">Keep reading</span><h2>Read next</h2></div>
	<div class="stories">
		<?php foreach ($more as $m): ?>
			<a class="story-card" href="<?= e(App::url('/blog/' . $m['slug'])) ?>">
				<?= sq((string) $m['cover'], '', 'sq--wide') ?>
				<span class="eyebrow"><?= e(Blog::KIND[(string) ($m['kind'] ?? 'guide')] ?? 'Guide') ?> · <?= Blog::minutes($m) ?> min read</span>
				<span class="story-card__t"><?= e((string) $m['title']) ?></span>
			</a>
		<?php endforeach; ?>
		<?= Promo::html('story', 1) ?>
	</div>
</section>
<?php endif; ?>
