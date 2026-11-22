<?php /** @var string $content */ ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#F0EFED">
<title><?= e($title ?? 'Wedding Websites & More') ?></title>
<?php if (!empty($desc)): ?><meta name="description" content="<?= e($desc) ?>"><?php endif; ?>
<link rel="preload" as="font" type="font/woff2" href="<?= e(App::url('assets/fonts/outfit-latin.woff2')) ?>" crossorigin>
<link rel="stylesheet" href="<?= e(App::asset('assets/css/tokens.css')) ?>">
<link rel="stylesheet" href="<?= e(App::asset('assets/css/dots.css')) ?>">
<link rel="stylesheet" href="<?= e(App::asset('assets/css/app.css')) ?>">
<?php foreach (($css ?? []) as $sheet): ?><link rel="stylesheet" href="<?= e(App::asset('assets/css/' . $sheet . '.css')) ?>"><?php endforeach; ?>
</head>
<body class="<?= e(trim(($bodyClass ?? '') . (empty($rail) ? '' : ' has-rail'))) ?>">
<div class="shell">
	<?php if (empty($bare)): ?><?= View::partial('partials/bar', get_defined_vars()) ?><?php endif; ?>
	<?php if (empty($bare) && !empty($rail)): ?><?= View::partial('partials/rail', get_defined_vars()) ?><?php endif; ?>
	<main class="main" id="main"><?= $content ?></main>
	<?php if (empty($bare) && empty($rail)): ?><?= View::partial('partials/footer', get_defined_vars()) ?><?php endif; ?>
	<?php if (empty($bare)): ?><?= View::partial('partials/tabs', get_defined_vars()) ?><?php endif; ?>
</div>
<div class="scrim" data-scrim></div>
<script src="<?= e(App::asset('assets/js/app.js')) ?>" defer></script>
<?php foreach (($js ?? []) as $s): ?><script src="<?= e(App::asset('assets/js/' . $s . '.js')) ?>" defer></script><?php endforeach; ?>
</body>
</html>
