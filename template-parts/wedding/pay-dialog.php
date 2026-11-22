<?php
/**
 * Payment dialog for gifts & spraying.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_id = $args['id'];
$wd_d  = $args['d'];
?>
<dialog class="pay-dialog" data-pay-dialog data-fee-gift="<?php echo esc_attr( wd_fee_rate( $wd_id, 'gift' ) ); ?>" data-fee-spray="<?php echo esc_attr( wd_fee_rate( $wd_id, 'spray' ) ); ?>" data-fee-cap="<?php echo (int) wd_opt( 'fee_cap' ); ?>" data-names="<?php echo esc_attr( wd_names( $wd_d ) ); ?>">
	<form method="dialog" class="form" data-pay-form>
		<button type="submit" class="pay-dialog__x" value="cancel" formnovalidate aria-label="<?php esc_attr_e( 'Close', 'wwmd' ); ?>"><?php echo wd_icon( 'close', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
		<p class="w-eyebrow" data-pay-kind></p>
		<h2 class="pay-dialog__title" data-pay-title></h2>
		<input type="hidden" name="wedding" value="<?php echo (int) $wd_id; ?>">
		<input type="hidden" name="kind" value="gift">
		<input type="hidden" name="item" value="">
		<label class="field"><span><?php echo esc_html( sprintf( /* translators: %s currency */ __( 'Amount (%s)', 'wwmd' ), $wd_d['currency'] ) ); ?></span><input name="amount" type="number" min="1" step="1" inputmode="numeric" required></label>
		<div class="chips" data-pay-chips></div>
		<div class="field-row">
			<label class="field"><span><?php esc_html_e( 'Your name', 'wwmd' ); ?></span><input name="name" maxlength="60" required autocomplete="name"></label>
			<label class="field"><span><?php esc_html_e( 'Email for your receipt', 'wwmd' ); ?></span><input name="email" type="email" autocomplete="email"></label>
		</div>
		<label class="field"><span><?php esc_html_e( 'A short message (optional)', 'wwmd' ); ?></span><input name="message" maxlength="200"></label>
		<label class="check" data-pay-cover><input type="checkbox" name="cover" value="1" checked><span data-pay-cover-text></span></label>
		<label class="check"><input type="checkbox" name="hide" value="1"><span><?php esc_html_e( 'Hide my amount from other guests', 'wwmd' ); ?></span></label>
		<p class="pay-dialog__total"><span><?php esc_html_e( 'You pay', 'wwmd' ); ?></span><b data-pay-total>–</b></p>
		<button class="btn w-btn btn--block btn--lg" type="submit" value="pay" data-pay-go><?php esc_html_e( 'Pay securely', 'wwmd' ); ?></button>
		<p class="fine center"><?php echo wd_pay_test_mode() ? esc_html__( 'Test mode: no real money moves. Payments are recorded so you can see how it works.', 'wwmd' ) : esc_html__( 'Card, bank transfer or USSD. Guests abroad can pay with any card.', 'wwmd' ); ?></p>
		<p class="form__msg" data-msg role="status"></p>
	</form>
	<div class="pay-dialog__done" hidden data-pay-done>
		<?php echo wd_art( 'notes' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<h2><?php esc_html_e( 'Thank you for the love!', 'wwmd' ); ?></h2>
		<p data-pay-done-text></p>
		<button type="button" class="btn w-btn" data-close><?php esc_html_e( 'Back to the page', 'wwmd' ); ?></button>
	</div>
</dialog>
