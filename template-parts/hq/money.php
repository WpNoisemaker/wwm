<?php
/**
 * HQ: payments ledger & payouts.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
global $wpdb;
// phpcs:disable WordPress.DB
$wd_pay = $wpdb->get_results( 'SELECT * FROM ' . wd_t( 'payouts' ) . " WHERE wedding_id > 0 ORDER BY FIELD(status,'requested','paid','rejected'), created DESC LIMIT 60" );
$wd_tx  = $wpdb->get_results( 'SELECT * FROM ' . wd_t( 'tx' ) . " WHERE status = 'paid' ORDER BY paid_at DESC LIMIT 80" );
// phpcs:enable
$wd_kinds = array(
	'gift'    => __( 'Registry gift', 'wwmd' ),
	'spray'   => __( 'Spray', 'wwmd' ),
	'premium' => __( 'Plan upgrade', 'wwmd' ),
	'cards'   => __( 'Extra QR cards', 'wwmd' ),
	'spot'    => __( 'Spotlight', 'wwmd' ),
	'design'  => __( 'Custom design', 'wwmd' ),
	'booking' => __( 'Service booking', 'wwmd' ),
);
?>
<div class="app-head"><div><p class="eyebrow"><?php esc_html_e( 'HQ', 'wwmd' ); ?></p><h1><?php esc_html_e( 'Payments & payouts', 'wwmd' ); ?></h1></div></div>
<?php if ( wd_pay_test_mode() ) : ?><p class="notice notice--warn"><?php esc_html_e( 'Test mode: payments are recorded but no real money moves. Add your payment keys in Settings to go live.', 'wwmd' ); ?></p><?php endif; ?>
<div class="panel">
	<h2><?php esc_html_e( 'Payout requests', 'wwmd' ); ?></h2>
	<p class="hint"><?php esc_html_e( 'Pay the couple from your business account, then mark the request as paid. The couple sees the status in their dashboard.', 'wwmd' ); ?></p>
	<?php if ( ! $wd_pay ) : ?>
		<p class="empty"><?php esc_html_e( 'No payout requests yet.', 'wwmd' ); ?></p>
	<?php else : ?>
		<div class="table-wrap"><table class="table">
			<thead><tr><th><?php esc_html_e( 'Couple', 'wwmd' ); ?></th><th><?php esc_html_e( 'Amount', 'wwmd' ); ?></th><th><?php esc_html_e( 'Bank details', 'wwmd' ); ?></th><th><?php esc_html_e( 'Status', 'wwmd' ); ?></th></tr></thead>
			<tbody>
			<?php foreach ( $wd_pay as $wd_r ) : ?>
				<tr>
					<td><b><?php echo esc_html( get_the_title( $wd_r->wedding_id ) ); ?></b><small><?php echo esc_html( wd_ago( $wd_r->created ) ); ?></small></td>
					<td><b><?php echo esc_html( wd_money( $wd_r->amount, $wd_r->currency ) ); ?></b></td>
					<td><?php echo esc_html( $wd_r->bank . ' · ' . $wd_r->account_no ); ?><small><?php echo esc_html( $wd_r->account_name ); ?></small></td>
					<td><?php wd_hq_form( 'payout', 'inline inline--fields' ); ?><input type="hidden" name="id" value="<?php echo (int) $wd_r->id; ?>"><select name="status" aria-label="<?php esc_attr_e( 'Status', 'wwmd' ); ?>"><?php foreach ( array( 'requested' => __( 'Waiting', 'wwmd' ), 'paid' => __( 'Paid', 'wwmd' ), 'rejected' => __( 'Rejected', 'wwmd' ) ) as $wd_k => $wd_l ) : ?><option value="<?php echo esc_attr( $wd_k ); ?>" <?php selected( $wd_r->status, $wd_k ); ?>><?php echo esc_html( $wd_l ); ?></option><?php endforeach; ?></select><input type="text" name="note" value="<?php echo esc_attr( $wd_r->note ); ?>" placeholder="<?php esc_attr_e( 'Note to couple', 'wwmd' ); ?>" aria-label="<?php esc_attr_e( 'Note to couple', 'wwmd' ); ?>"><button class="linkish" type="submit"><?php esc_html_e( 'Save', 'wwmd' ); ?></button></form></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table></div>
	<?php endif; ?>
</div>
<div class="panel">
	<h2><?php esc_html_e( 'Latest payments', 'wwmd' ); ?></h2>
	<div class="table-wrap"><table class="table">
		<thead><tr><th><?php esc_html_e( 'When', 'wwmd' ); ?></th><th><?php esc_html_e( 'What', 'wwmd' ); ?></th><th><?php esc_html_e( 'From', 'wwmd' ); ?></th><th><?php esc_html_e( 'Amount', 'wwmd' ); ?></th><th><?php esc_html_e( 'Our fee', 'wwmd' ); ?></th><th><?php esc_html_e( 'Reference', 'wwmd' ); ?></th></tr></thead>
		<tbody>
		<?php foreach ( $wd_tx as $wd_r ) : ?>
			<tr>
				<td><?php echo esc_html( wd_ago( $wd_r->paid_at ? $wd_r->paid_at : $wd_r->created ) ); ?></td>
				<td><b><?php echo esc_html( $wd_kinds[ $wd_r->kind ] ?? $wd_r->kind ); ?></b><small><?php echo esc_html( $wd_r->wedding_id ? get_the_title( $wd_r->wedding_id ) : $wd_r->message ); ?></small></td>
				<td><?php echo esc_html( $wd_r->name ); ?><small><?php echo esc_html( $wd_r->email ); ?></small></td>
				<td><?php echo esc_html( wd_money( $wd_r->amount, $wd_r->currency ) ); ?></td>
				<td><?php echo esc_html( in_array( $wd_r->kind, array( 'spray', 'gift' ), true ) ? wd_money( $wd_r->fee, $wd_r->currency ) : ( 'booking' === $wd_r->kind ? __( 'Commission on release', 'wwmd' ) : '–' ) ); ?></td>
				<td><code><?php echo esc_html( $wd_r->reference ); ?></code><?php echo $wd_r->test ? ' <span class="pill">' . esc_html__( 'Test', 'wwmd' ) . '</span>' : ''; ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table></div>
</div>
