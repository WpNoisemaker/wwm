<?php /** The frame every sign-in style page sits in: one frosted card, centred. */ ?>
<div class="page auth">
	<div class="glass">
		<?= flashes() ?>
		<?= $slot ?>
	</div>
	<?php if (!empty($foot)): ?><p class="auth__foot"><?= $foot ?></p><?php endif; ?>
</div>
