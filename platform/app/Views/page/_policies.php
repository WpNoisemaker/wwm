<?php
/** The policies, linked from each other. @var string $now */
$mail = Site::setting('email', 'more@' . Share::host());
$all = [['terms', 'Terms & conditions'], ['privacy', 'Privacy policy'], ['refunds', 'Refund policy'], ['community-guidelines', 'Community guidelines'], ['cookies', 'Cookie policy']];
?>
<nav class="chips chips--center policy-nav" aria-label="Policies">
	<?php foreach ($all as [$k, $label]): ?>
		<a class="chip<?= $k === $now ? ' is-on' : '' ?>" href="<?= e(App::url('/' . $k)) ?>"<?= $k === $now ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
	<?php endforeach; ?>
</nav>
