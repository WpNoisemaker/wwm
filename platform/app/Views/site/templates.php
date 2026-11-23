<?php
/**
 * Every design, each shown on a real page that wears it, with the parts of the design system it
 * is built from. @var array $samples
 */
$order = ['free', 'pro', 'royal', 'pearl', 'garden', 'noir', 'coral', 'indigo', 'asooke', 'keepsake', 'savannah', 'owambe', 'ivory'];
?>
<div class="page wrap">
	<header class="page-head">
		<span class="eyebrow"><?= t('templates.eyebrow.one-design-system-thirteen-ways', 'One design system, thirteen ways to wear it') ?></span>
		<h1><?= t('templates.title.wedding-page-designs', 'Wedding page designs') ?></h1>
		<p class="lede"><?= t('templates.intro.free-pages-are-classic-in', 'Free pages are Classic in black & white. Pro pages are Classic in your colours. Premium pages choose any design.') ?></p>
	</header>
	<div class="designs">
		<?php foreach ($order as $k): if (!isset($samples[$k])) { continue; } $w = $samples[$k];
			$d = in_array($k, ['free', 'pro'], true) ? Design::get('classic') : Design::get($k);
			$label = $k === 'free' ? 'Classic · Free' : ($k === 'pro' ? 'Classic · Pro' : $d['name']);
			$plan = $k === 'free' ? 'Free' : ($k === 'pro' ? 'Pro' : ($d['plan'] === 'custom' ? 'Custom' : 'Premium')); ?>
			<a class="design" href="<?= e(App::url('/w/' . $w['slug'])) ?>" style="--s1:<?= e($k === 'free' ? '#6E6E6E' : $d['swatch'][0]) ?>;--s2:<?= e($k === 'free' ? '#BDBDBD' : $d['swatch'][1]) ?>">
				<span class="design__art">
					<span class="design__sky"></span>
					<?= sq((string) $w['cover'], Wedding::names($w), 'design__photo' . ($k === 'free' ? ' is-mono' : ''), false, initials($w)) ?>
				</span>
				<span class="design__body">
					<span class="design__name"><?= e($label) ?> <span class="badge<?= $plan === 'Premium' || $plan === 'Custom' ? ' badge--solid' : '' ?>"><?= e($plan) ?></span></span>
					<span class="design__line"><?= e($k === 'free' ? 'The reference screen in black & white: squircle photos, the frosted RSVP card, the white pill.' : ($k === 'pro' ? 'The same composition in the couple’s own two colours, in the glow, the squircles and the ticks.' : $d['line'])) ?></span>
					<span class="design__on">See it on <?= e(Wedding::names($w)) ?>&rsquo;s page <?= icon('arrow') ?></span>
				</span>
			</a>
		<?php endforeach; ?>
	</div>
</div>
