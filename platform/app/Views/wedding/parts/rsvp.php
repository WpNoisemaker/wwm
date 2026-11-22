<section class="sec wrap" id="rsvp">
	<h2 class="wed__h">Will you be there?</h2>
	<p class="sub">No account needed. It takes a few seconds.</p>
	<form class="rsvp" method="post" action="<?= e(App::url('/w/' . $w['slug'] . '/rsvp')) ?>">
		<?= Guard::field() ?>
		<div class="rsvp__pair">
			<span><label for="r-name">Your name</label><input id="r-name" name="name" required></span>
			<span><label for="r-phone">Phone <em>optional</em></label><input id="r-phone" name="phone" inputmode="tel"></span>
		</div>
		<div class="rsvp__pair">
			<span><label for="r-party">How many of you?</label><input id="r-party" name="party" type="number" min="1" max="20" value="1"></span>
			<span>
				<label for="r-coming">Coming?</label>
				<select id="r-coming" name="coming">
					<option value="yes">Yes, we will be there</option>
					<option value="maybe">Still deciding</option>
					<option value="no">Sadly no</option>
				</select>
			</span>
		</div>
		<label for="r-note">A word for them <em>optional</em></label>
		<input id="r-note" name="note" placeholder="Congratulations!">
		<button class="btn btn--acid btn--block" type="submit">Send my RSVP</button>
	</form>
</section>
