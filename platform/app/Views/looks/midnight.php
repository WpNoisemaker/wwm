<div class="wrap">
	<section style="padding:10px 0 18px">
		<p class="mn-tile__k" style="margin-bottom:6px">Saturday 27 June · Lagos</p>
		<h1 style="font-weight:300;letter-spacing:-.04em">Tolu &amp; Ayo</h1>
	</section>

	<div class="cols"><div>
		<!-- Top row: the live control, then the two readouts, the way the panel reads. -->
		<div class="mn" style="grid-template-columns:120px 1fr;align-items:start">
			<div class="mn-tile mn-tile--pad">
				<span class="mn-pow">
					<button class="mn-pow__btn is-on" type="button" aria-pressed="true"><?= icon('spark', 20) ?></button>
					<span class="mn-pow__s">Page live</span>
				</span>
			</div>
			<div class="mn" style="gap:10px">
				<div class="mn-tile"><span class="mn-tile__k">Countdown</span><span class="mn-tile__v mn-tile__v--big">84<small style="font-size:.9rem;font-weight:400;color:var(--muted)"> days</small></span></div>
				<div class="mn-tile"><span class="mn-tile__k">Plan</span><span class="mn-tile__v">Premium</span></div>
			</div>
		</div>

		<div class="mn" style="grid-template-columns:1fr 1fr;margin-top:10px">
			<div class="mn-tile"><span class="mn-tile__k">RSVPs in</span><span class="mn-tile__v">312 of 418</span>
				<span class="mn-slide" style="--p:75%;margin-top:10px"><i class="mn-slide__fill"></i><span>75%</span><span class="mn-slide__grip"><i></i><i></i></span></span>
			</div>
			<div class="mn-tile"><span class="mn-tile__k">Registry</span><span class="mn-tile__v"><?= money("2.7m") ?> of <?= money("3.6m") ?></span>
				<span class="mn-slide" style="--p:68%;margin-top:10px"><i class="mn-slide__fill"></i><span>68%</span><span class="mn-slide__grip"><i></i><i></i></span></span>
			</div>
		</div>

		<div class="mn" style="grid-template-columns:1fr 1fr 1fr;margin-top:10px">
			<button class="mn-btn" type="button"><?= icon('cam', 19) ?>Gallery</button>
			<button class="mn-btn is-on" type="button"><?= icon('spark', 19) ?>Livestream</button>
			<button class="mn-btn" type="button"><?= icon('bell', 19) ?>Alerts</button>
		</div>

		<div class="mn" style="grid-template-columns:1fr 1fr;margin-top:10px">
			<div class="mn-tile"><span class="mn-tile__k">Guests at the gate</span><span class="mn-tile__v mn-tile__v--big">0</span></div>
			<div class="mn-tile"><span class="mn-tile__k">Sprayed tonight</span><span class="mn-tile__v mn-tile__v--big"><?= money("0") ?></span></div>
		</div>
	</div>

	<aside class="cols__side">
		<div class="mn-tile mn-tile--pad" style="display:grid;grid-template-columns:1fr 26px;gap:14px;min-height:230px">
			<div style="display:grid;align-content:space-between">
				<span class="mn-tile__k">Buzz chart</span>
				<span><span class="mn-tile__v mn-tile__v--big">3rd</span><br><span class="mn-tile__k">this month, up 4</span></span>
				<span class="mn-tile__k">832 points</span>
			</div>
			<span class="mn-scale" aria-hidden="true"><?= str_repeat('<i></i>', 16) ?></span>
		</div>
		<div class="mn-tile mn-tile--pad"><span class="mn-tile__k">Vendors booked</span><span class="mn-tile__v">12 of 14</span></div>
		<a class="mn-btn mn-btn--wide" href="<?= e(App::url('/community')) ?>"><?= icon('talk', 19) ?>Community</a>
	</aside></div>
</div>
