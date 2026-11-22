<?= View::partial('account/_head', ['h' => 'Make your wedding page', 'sub' => 'You can change all of this later']) ?>
	<form method="post" class="form glass glass--plain glass--tight narrow">
		<?= Guard::field() ?>
		<div class="form-grid form-grid--2">
			<label class="field"><span>Your first name</span><input class="input" id="a" name="a" required autofocus></label>
			<label class="field"><span>Their first name</span><input class="input" id="b" name="b" required></label>
			<label class="field"><span>Wedding date</span><input class="input" id="date" name="date" type="date"></label>
			<label class="field"><span>City</span><input class="input" id="city" name="city"></label>
		</div>
		<button class="pill pill--block" type="submit">Make it</button>
	</form>
</div>
