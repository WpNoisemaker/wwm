<?php
/**
 * Premium add-on: a custom template designed for one couple.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_id    = $args['id'];
$wd_d     = $args['d'];
$wd_price = (int) wd_opt( 'custom_design_price' );
$wd_state = get_post_meta( $wd_id, '_wd_custom', true );
// phpcs:disable WordPress.Security.NonceVerification
if ( isset( $_GET['custom'], $_GET['_wpnonce'] ) && ! $wd_state && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'wd_custom_' . $wd_id ) ) {
	$wd_u   = wp_get_current_user();
	$wd_res = wd_pay_start(
		array(
			'wedding_id' => $wd_id,
			'kind'       => 'design',
			'item'       => 'custom_design',
			'amount'     => $wd_price,
			'name'       => $wd_u->display_name,
			'email'      => $wd_u->user_email,
			'message'    => '',
			'return'     => wd_account_url( 'page', array( 'wid' => $wd_id, 'part' => 'design' ) ),
		)
	);
	if ( ! is_wp_error( $wd_res ) && ! empty( $wd_res['url'] ) ) {
		echo '<script>location.href=' . wp_json_encode( esc_url_raw( $wd_res['url'] ) ) . ';</script>';
	} elseif ( is_wp_error( $wd_res ) ) {
		echo '<div class="notice notice--err" role="alert"><span>' . esc_html( $wd_res->get_error_message() ) . '</span></div>';
	}
	$wd_state = get_post_meta( $wd_id, '_wd_custom', true );
}
if ( isset( $_GET['wdpay'] ) ) {
	wd_pay_verify( wd_pay_ref_from_request() );
	$wd_state = get_post_meta( $wd_id, '_wd_custom', true );
}
// phpcs:enable
$wd_wa = wd_whatsapp( sprintf( /* translators: %s couple */ __( 'Hello! We paid for a custom design for our wedding page (%s). Here are our invitation card, colours & ideas:', 'wwmd' ), wd_names( $wd_d ) ) );
?>
<section class="panel custom-design">
	<span class="custom-design__art" aria-hidden="true"><?php echo wd_art( 'notes' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
	<div>
		<p class="eyebrow"><?php esc_html_e( 'Custom design', 'wwmd' ); ?></p>
		<?php if ( wd_custom_tpl( $wd_id ) ) : ?>
			<h2><?php echo esc_html( sprintf( /* translators: %s template */ __( 'Your custom design is ready: %s', 'wwmd' ), wd_tpl_info( wd_custom_tpl( $wd_id ) )['name'] ) ); ?></h2>
			<p><?php esc_html_e( 'It is the first template above & only your page can use it. Want a change? Tell our designer on WhatsApp.', 'wwmd' ); ?></p>
			<div class="btn-row">
				<a class="btn btn--ink" href="<?php echo esc_url( get_permalink( $wd_id ) ); ?>"><?php esc_html_e( 'See my page', 'wwmd' ); ?></a>
				<a class="btn btn--ghost" href="<?php echo esc_url( $wd_wa ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'whatsapp', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Ask for a change', 'wwmd' ); ?></a>
			</div>
		<?php elseif ( $wd_state ) : ?>
			<h2><?php esc_html_e( 'Your custom design is in the works', 'wwmd' ); ?></h2>
			<p><?php esc_html_e( 'Thank you! Send us your invitation card, colours, fabric photos & any page you love on WhatsApp. Our designer will share a first look within 7 working days, with two rounds of changes.', 'wwmd' ); ?></p>
			<a class="btn btn--ink" href="<?php echo esc_url( $wd_wa ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'whatsapp', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Send our ideas on WhatsApp', 'wwmd' ); ?></a>
		<?php else : ?>
			<h2><?php esc_html_e( 'Want a design no one else has?', 'wwmd' ); ?></h2>
			<p><?php echo esc_html( sprintf( /* translators: %s price */ __( 'Our designer builds a one-of-a-kind template from your invitation card, aso-ebi colours & story. %s, one payment. First look within 7 working days, with two rounds of changes.', 'wwmd' ), wd_money( $wd_price ) ) ); ?></p>
			<a class="btn btn--marigold" href="<?php echo esc_url( wp_nonce_url( wd_account_url( 'page', array( 'wid' => $wd_id, 'part' => 'design', 'custom' => 1 ) ), 'wd_custom_' . $wd_id ) ); ?>"><?php echo esc_html( sprintf( /* translators: %s price */ __( 'Request a custom design · %s', 'wwmd' ), wd_money( $wd_price ) ) ); ?></a>
		<?php endif; ?>
	</div>
</section>
