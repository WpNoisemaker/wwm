<?= View::partial('account/_head', ['h' => 'Make your wedding page', 'sub' => 'You can change all of this later.']) ?>
<section class="sec wrap">
	<form method="post" class="form form--card">
		<?= Guard::field() ?>
		<div class="form__pair">
			<span><label for="a">Your first name</label><input id="a" name="a" required></span>
			<span><label for="b">Their first name</label><input id="b" name="b" required></span>
		</div>
		<div class="form__pair">
			<span><label for="date">Wedding date</label><input id="date" name="date" type="date"></span>
			<span><label for="city">City</label><input id="city" name="city"></span>
		</div>
		<button class="btn btn--acid" type="submit">Make it</button>
	</form>
</section>
