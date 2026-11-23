<?php
/**
 * "Report this seller": a small form behind a link. Signed-in customers only, once per seller.
 *
 * @var array $v @var string $back @var ?int $listing
 */
$back = $back ?? ('/vendors/' . $v['slug']);
?>
<details class="report" id="report">
	<summary><?= icon('flag') ?>Report this seller</summary>
	<?php if (!Auth::check()): ?>
		<p class="help">Sign in to report a seller. <a class="linkish" href="<?= e(App::url('/sign-in?to=' . urlencode($back . '#report'))) ?>">Sign in</a> or <a class="linkish" href="<?= e(App::url('/join/customer?to=' . urlencode($back . '#report'))) ?>">create a customer account</a>.</p>
	<?php else: ?>
		<form method="post" action="<?= e(App::url('/vendors/' . $v['slug'] . '/report')) ?>" class="form report__form">
			<?= Guard::field() ?><input type="hidden" name="back" value="<?= e($back) ?>"><?php if (!empty($listing)): ?><input type="hidden" name="listing_id" value="<?= (int) $listing ?>"><?php endif; ?>
			<label class="field"><span>What is wrong?</span><select class="input" name="reason"><?php foreach (Market::REASONS as $k => $t): ?><option value="<?= e($k) ?>"><?= e($t) ?></option><?php endforeach; ?></select></label>
			<label class="field"><span>Tell us what happened</span><textarea class="input" name="body" rows="3" maxlength="2000" placeholder="Dates, amounts, what they said. It stays between you and us."></textarea></label>
			<button class="pill pill--sm pill--danger" type="submit">Send the report</button>
		</form>
	<?php endif; ?>
</details>
