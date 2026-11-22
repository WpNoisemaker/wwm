<div class="wrap" style="max-width:520px">
	<div class="sky-head">
		<button class="sky-back" type="button" aria-label="Back"><?= icon('back', 20) ?></button>
		<h1>Your wedding</h1>
	</div>

	<div class="sky-pair">
		<div class="sky-prod">
			<span class="sky-prod__k">White wedding</span>
			<span class="sky-prod__box"><?= icon('rings', 76) ?></span>
		</div>
		<div class="sky-prod sky-prod--sm">
			<span class="sky-prod__k">Traditional</span>
			<span class="sky-prod__box"><?= icon('gift', 60) ?></span>
		</div>
	</div>

	<div class="sky-card">
		<span class="sky-card__k">How many guests?</span>
		<div class="sky-count">
			<button class="sky-count__btn" type="button" aria-label="Fewer">−</button>
			<b class="sky-count__n dot" style="--dot-color:#fff">418</b>
			<button class="sky-count__btn" type="button" aria-label="More">+</button>
		</div>
		<span class="sky-card__u">Guests</span>
		<div class="sky-foot">
			<span class="sky-foot__k">entry passes</span>
			<span class="sky-foot__v">312 confirmed</span>
		</div>
	</div>

	<div style="display:grid;gap:12px;margin-top:18px">
		<a class="btn btn--block" href="<?= e(App::url('/account/guests')) ?>">Open the guest list</a>
		<a class="btn btn--ghost btn--block" href="<?= e(App::url('/account')) ?>">Your page</a>
	</div>
</div>
