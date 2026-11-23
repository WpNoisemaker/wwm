<?php /** Every email, as it is sent. @var array $samples */ ?>
<?= View::partial('hq/_head', ['h' => 'Emails', 'now' => 'settings', 'sub' => 'What people receive from the site']) ?>
	<p class="help">Every email the site sends wears this design: sign-up and password emails, livestream reminders, marketplace chats and invoices, and notes to sellers. Each one also carries the same words as plain text.</p>
	<div class="acct-list gap-t-sm">
		<?php foreach ($samples as $k => [$subj]): ?>
			<div class="glass glass--plain glass--tight acct-card">
				<div class="lock-row"><h2 class="h3"><?= e($subj) ?></h2><a class="pill pill--sm pill--glass" href="<?= e(App::url('/hq/emails?k=' . $k)) ?>" target="_blank" rel="noopener">Open on its own</a></div>
				<iframe class="mail-frame" src="<?= e(App::url('/hq/emails?k=' . $k)) ?>" title="<?= e($subj) ?>" loading="lazy"></iframe>
			</div>
		<?php endforeach; ?>
	</div>
</div>
