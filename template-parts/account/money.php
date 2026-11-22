<?php
/**
 * Money: sprays, gifts, fees, balance & payouts.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
global $wpdb;
$wd_id   = $args['id'];
$wd_d    = $args['d'];
$wd_cur  = $wd_d['currency'];
$wd_t    = wd_money_totals( $wd_id );
$wd_tx   = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . wd_t( 'tx' ) . " WHERE wedding_id = %d AND kind IN ('spray','gift') AND status = 'paid' ORDER BY id DESC LIMIT 200", $wd_id ) ); // phpcs:ignore WordPress.DB
$wd_outs = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . wd_t( 'payouts' ) . ' WHERE wedding_id = %d ORDER BY id DESC', $wd_id ) ); // phpcs:ignore WordPress.DB
$wd_bank = wp_parse_args( (array) get_user_meta( get_current_user_id(), 'wd_bank', true ), array( 'bank' => '', 'account_no' => '', 'account_name' => '' ) );
$wd_reg  = wp_list_pluck( $wd_d['registry'], 'title', 'id' );
?>
<div class="app-head"><div><p class="eyebrow"><?php esc_html_e( 'Money', 'wwmd' ); ?></p><h1><?php esc_html_e( 'Gifts & spraying', 'wwmd' ); ?></h1></div></div>
<div class="stat-grid stat-grid--4">
	<div class="stat tone-butter"><span><?php esc_html_e( 'Sprayed', 'wwmd' ); ?></span><b><?php echo esc_html( wd_money( $wd_t['spray'], $wd_cur ) ); ?></b><small><?php echo esc_html( sprintf( /* translators: %d */ _n( '%d spray', '%d sprays', $wd_t['spray_n'], 'wwmd' ), $wd_t['spray_n'] ) ); ?></small></div>
	<div class="stat tone-lavender"><span><?php esc_html_e( 'Cash gifts', 'wwmd' ); ?></span><b><?php echo esc_html( wd_money( $wd_t['gift'], $wd_cur ) ); ?></b><small><?php echo esc_html( sprintf( /* translators: %d */ _n( '%d gift', '%d gifts', $wd_t['gift_n'], 'wwmd' ), $wd_t['gift_n'] ) ); ?></small></div>
	<div class="stat tone-coral"><span><?php esc_html_e( 'Service fees', 'wwmd' ); ?></span><b><?php echo esc_html( wd_money( $wd_t['fees'], $wd_cur ) ); ?></b><small><?php echo wd_can( $wd_id, 'no_fees' ) ? esc_html( sprintf( /* translators: %s plan */ __( '%s: no new fees', 'wwmd' ), wd_plan_name( wd_plan( $wd_id ) ) ) ) : esc_html( sprintf( /* translators: %s plan */ __( '%s page fee', 'wwmd' ), wd_plan_name( wd_plan( $wd_id ) ) ) ); ?></small></div>
	<div class="stat tone-mint"><span><?php esc_html_e( 'Ready to withdraw', 'wwmd' ); ?></span><b><?php echo esc_html( wd_money( $wd_t['available'], $wd_cur ) ); ?></b><small><?php echo esc_html( sprintf( /* translators: %s */ __( '%s paid out or on its way', 'wwmd' ), wd_money( $wd_t['paid_out'], $wd_cur ) ) ); ?></small></div>
</div>
<div class="app-cols">
	<div class="panel">
		<h2><?php esc_html_e( 'Withdraw', 'wwmd' ); ?></h2>
		<?php wd_form( 'payout', $wd_id, false, 'form' ); ?>
			<?php wd_field( 'amount', sprintf( /* translators: %s currency */ __( 'Amount (%s)', 'wwmd' ), $wd_cur ), $wd_t['available'] ? $wd_t['available'] : '', 'number', 'min="' . (int) wd_opt( 'payout_min' ) . '" max="' . (int) $wd_t['available'] . '" required' ); ?>
			<?php wd_field( 'bank', __( 'Bank or mobile money', 'wwmd' ), $wd_bank['bank'], 'text', 'required placeholder="' . esc_attr__( 'GTBank, Access, M-Pesa, MTN MoMo…', 'wwmd' ) . '"' ); ?>
			<div class="field-row">
				<?php wd_field( 'account_no', __( 'Account or phone number', 'wwmd' ), $wd_bank['account_no'], 'text', 'required inputmode="numeric"' ); ?>
				<?php wd_field( 'account_name', __( 'Account name', 'wwmd' ), $wd_bank['account_name'], 'text', 'required' ); ?>
			</div>
			<p class="hint"><?php echo esc_html( sprintf( /* translators: %s min */ __( 'Minimum %s. Free to Nigerian banks. We check & pay within one working day.', 'wwmd' ), wd_money( wd_opt( 'payout_min' ), $wd_cur ) ) ); ?></p>
			<button class="btn btn--ink" type="submit" <?php disabled( $wd_t['available'] < (int) wd_opt( 'payout_min' ) ); ?>><?php esc_html_e( 'Request payout', 'wwmd' ); ?></button>
		</form>
		<?php if ( $wd_outs ) : ?>
			<h3><?php esc_html_e( 'Payouts', 'wwmd' ); ?></h3>
			<ul class="mini-list">
				<?php foreach ( $wd_outs as $wd_o ) : ?>
					<li><span><?php echo esc_html( wd_money( $wd_o->amount, $wd_o->currency ) ); ?> <small><?php echo esc_html( $wd_o->bank . ' · ' . wd_ago( $wd_o->created ) ); ?></small></span><span class="pill<?php echo 'paid' === $wd_o->status ? ' pill--ok' : ''; ?>"><?php echo esc_html( ucfirst( $wd_o->status ) ); ?></span></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
	<?php
	$wd_rc = wp_parse_args( (array) ( $wd_d['receipt'] ?? array() ), array( 'logo' => '', 'note' => '' ) );
	$wd_lt = $wd_tx ? $wd_tx[0] : null;
	?>
	<div class="panel" id="receipts">
		<h2><?php esc_html_e( 'Your gift receipts', 'wwmd' ); ?></h2>
		<?php if ( wd_can( $wd_id, 'receipts' ) ) : ?>
			<p class="hint"><?php esc_html_e( 'Guests who send a gift or spray get a receipt by email & on its own page. Yours carry your names, your page colours & your logo or initials.', 'wwmd' ); ?></p>
			<?php wd_form( 'receipts', $wd_id, true, 'form' ); ?>
				<div class="receipt-logo">
					<span class="receipt-logo__pic" style="--r-primary:<?php echo esc_attr( $wd_d['design']['primary'] ); ?>;--r-accent:<?php echo esc_attr( $wd_d['design']['accent'] ); ?>;--r-ink:<?php echo esc_attr( $wd_d['design']['ink'] ); ?>">
						<?php if ( $wd_rc['logo'] ) : ?><img src="<?php echo esc_url( (string) wp_get_attachment_image_url( (int) $wd_rc['logo'], 'thumbnail' ) ); ?>" alt=""><?php else : ?><span class="receipt__mono"><?php echo esc_html( wd_initials( $wd_d ) ); ?></span><?php endif; ?>
					</span>
					<label class="field"><span><?php esc_html_e( 'Logo or monogram (optional, square works best)', 'wwmd' ); ?></span><input type="file" name="receipt_logo_file" accept="image/jpeg,image/png,image/webp"></label>
				</div>
				<?php if ( $wd_rc['logo'] ) : ?><label class="check"><input type="checkbox" name="remove_receipt_logo" value="1"> <?php esc_html_e( 'Use our initials instead', 'wwmd' ); ?></label><?php endif; ?>
				<label class="field"><span><?php esc_html_e( 'Thank-you note on every receipt (optional)', 'wwmd' ); ?></span><textarea name="receipt_note" rows="2" maxlength="240" placeholder="<?php esc_attr_e( 'e.g. Thank you for celebrating with us. We love you!', 'wwmd' ); ?>"><?php echo esc_textarea( $wd_rc['note'] ); ?></textarea></label>
				<div class="btn-row">
					<button class="btn btn--ink" type="submit"><?php esc_html_e( 'Save receipts', 'wwmd' ); ?></button>
					<?php if ( $wd_lt ) : ?><a class="btn btn--ghost" href="<?php echo esc_url( wd_receipt_url( $wd_lt->reference ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'See the latest receipt', 'wwmd' ); ?></a><?php endif; ?>
				</div>
			</form>
		<?php else : ?>
			<p class="hint"><?php esc_html_e( 'Guests who send a gift or spray get a receipt by email & on its own page.', 'wwmd' ); ?> <?php echo esc_html( sprintf( /* translators: %s plan */ __( 'On %s, receipts carry your names, colours & logo instead of ours.', 'wwmd' ), wd_plan_name( wd_feature_plan( 'receipts' ) ) ) ); ?></p>
			<a class="btn btn--ghost btn--sm" href="<?php echo esc_url( wd_account_url( 'plan', array( 'wid' => $wd_id ) ) ); ?>"><?php esc_html_e( 'See plans', 'wwmd' ); ?></a>
		<?php endif; ?>
	</div>
	<div class="panel">
		<h2><?php esc_html_e( 'Every gift', 'wwmd' ); ?></h2>
		<div class="table-wrap">
			<table class="table table--compact">
				<thead><tr><th><?php esc_html_e( 'From', 'wwmd' ); ?></th><th><?php esc_html_e( 'For', 'wwmd' ); ?></th><th><?php esc_html_e( 'Amount', 'wwmd' ); ?></th><th><?php esc_html_e( 'You get', 'wwmd' ); ?></th></tr></thead>
				<tbody>
					<?php foreach ( (array) $wd_tx as $wd_x ) : ?>
						<tr><td><b><?php echo esc_html( $wd_x->name ); ?></b><small><?php echo esc_html( wd_ago( $wd_x->created ) ); ?><?php echo $wd_x->test ? ' · ' . esc_html__( 'test', 'wwmd' ) : ''; ?></small><?php if ( $wd_x->message ) : ?><small>“<?php echo esc_html( $wd_x->message ); ?>”</small><?php endif; ?></td><td><?php echo 'spray' === $wd_x->kind ? esc_html__( 'Spray', 'wwmd' ) : esc_html( $wd_reg[ $wd_x->item ] ?? __( 'Gift', 'wwmd' ) ); ?></td><td><?php echo esc_html( wd_money( $wd_x->amount, $wd_x->currency ) ); ?></td><td><?php echo esc_html( wd_money( $wd_x->net, $wd_x->currency ) ); ?><small><a href="<?php echo esc_url( wd_receipt_url( $wd_x->reference ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Receipt', 'wwmd' ); ?></a></small></td></tr>
					<?php endforeach; ?>
					<?php if ( ! $wd_tx ) : ?><tr><td colspan="4" class="empty-note"><?php esc_html_e( 'No gifts yet.', 'wwmd' ); ?></td></tr><?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
