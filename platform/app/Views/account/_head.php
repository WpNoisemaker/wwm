<?php /** Shared top of every account screen: flashes and a title. */ ?>
<section class="sec wrap acct__top">
	<?php foreach (Guard::takeFlash() as $f): ?><p class="note note--<?= e($f['kind']) ?>"><?= e($f['msg']) ?></p><?php endforeach; ?>
	<?php if (Verify::needed(Auth::user())): ?>
		<div class="note note--bad verify">
			<span><b>Confirm your email.</b> We sent a link to <?= e((string) Auth::user()['email']) ?>. Until you follow it you can build your page, but not make it public or ask for a payout.</span>
			<form method="post" action="<?= e(App::url('/account/verify/resend')) ?>" class="verify__f"><?= Guard::field() ?><button class="btn btn--sm btn--ghost" type="submit">Send it again</button></form>
		</div>
	<?php endif; ?>
	<div class="sec__head acct__head">
		<div><h2><?= e($h) ?></h2><?php if (!empty($sub)): ?><p class="sub"><?= e($sub) ?></p><?php endif; ?></div>
		<?php if (!empty($w)): ?><a class="pill pill--quiet" href="<?= e(App::url('/w/' . $w['slug'])) ?>">View the page <?= icon('arrow', 13) ?></a><?php endif; ?>
	</div>
</section>
