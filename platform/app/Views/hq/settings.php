<?= View::partial('account/_head', ['h' => 'HQ settings', 'sub' => 'These drive the pricing page and the fee on every gift.']) ?>
<section class="sec wrap">
	<form method="post" class="form form--card">
		<?= Guard::field() ?>
		<h3 class="form__g">Plans</h3>
		<div class="form__pair">
			<span><label for="pro">Pro price</label><input id="pro" name="pro_price" type="number" min="0" value="<?= e($v['pro_price']) ?>"></span>
			<span><label for="prem">Premium price</label><input id="prem" name="premium_price" type="number" min="0" value="<?= e($v['premium_price']) ?>"></span>
		</div>
		<label for="fee">Service fee on cash gifts (%)</label>
		<input id="fee" name="fee_gift" type="number" min="0" max="20" step="0.1" value="<?= e($v['fee_gift']) ?>">
		<small class="gate__hint">Applied to Free and Pro pages. Premium and above pay none.</small>

		<h3 class="form__g">Contact</h3>
		<label for="email">Contact email</label>
		<input id="email" name="email" type="email" value="<?= e($v['email']) ?>">
		<label for="wa">WhatsApp number</label>
		<input id="wa" name="whatsapp" value="<?= e($v['whatsapp']) ?>" placeholder="2348000000000">
		<small class="gate__hint">Used for the High Society quote link. Setting a number here does <b>not</b> switch on WhatsApp notifications — those stay off until a provider is configured.</small>

		<button class="btn btn--acid" type="submit">Save</button>
	</form>
</section>
