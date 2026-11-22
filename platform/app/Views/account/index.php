<?= View::partial('account/_head', ['h' => Wedding::names($w), 'w' => $w, 'sub' => Wedding::when($w)]) ?>
<section class="sec wrap">
	<?php if ((string) $w['visibility'] !== 'public'): ?>
		<p class="locked"><?= icon('lock', 14) ?> Your page is private — nobody can find it yet. <a href="<?= e(App::url('/account/page')) ?>">Make it public when you are ready</a></p>
	<?php endif; ?>

	<div class="tally">
		<?php foreach ([
			[number_format($tally['guests']), 'coming'], [number_format($tally['heads']), 'seats'],
			[number_format($tally['notes']), 'love notes'], [number_format($tally['photos']), 'photos'],
		] as [$n, $l]): ?>
			<span class="tally__i"><b class="dot"><?= e($n) ?></b><em><?= e($l) ?></em></span>
		<?php endforeach; ?>
		<span class="tally__i"><b class="fig"><?= money(short_money((int) $tally['raised']), (string) $w['currency']) ?></b><em>gifted</em></span>
	</div>

	<?php if ($next): $u = Wedding::until($next['when']); ?>
		<p class="sub"><b><?= e($next['label']) ?></b> is in <?= e($u['d']) ?> day<?= $u['d'] === 1 ? '' : 's' ?>.</p>
	<?php endif; ?>

	<h3 class="form__g">What is left</h3>
	<div class="rows">
		<?php foreach ($todo as [$label, $href, $done]): ?>
			<a class="row" href="<?= e(App::url($href)) ?>">
				<span class="row__ico"><?= icon($done ? 'check' : 'plus', 18) ?></span>
				<span class="row__t"><?= e($label) ?></span>
				<span class="row__v"><?= $done ? '<em class="pill">Done</em>' : 'Add' ?></span>
			</a>
		<?php endforeach; ?>
	</div>

	<h3 class="form__g">Share it</h3>
	<p class="sub">One link. Put it in your Instagram bio, your IV and the family group.</p>
	<p class="sharelink"><?= e(App::url('/w/' . $w['slug'])) ?></p>
</section>
