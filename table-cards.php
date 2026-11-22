<?php
/**
 * Printable table QR cards (/table-cards/{wedding id}/) for Premium couples.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_id = (int) get_query_var( 'wd_cards' );
if ( ! $wd_id || ! wd_owns( $wd_id ) || ! wd_can( $wd_id, 'table_qr' ) ) {
	wp_safe_redirect( wd_account_url( 'cards', array( 'wdn' => 'denied' ) ) );
	exit;
}
$wd_d   = wd_get( $wd_id );
$wd_n   = max( 1, (int) $wd_d['cards']['tables'] );
$wd_url = get_permalink( $wd_id );
$wd_pri = wd_can( $wd_id, 'design' ) ? $wd_d['design']['primary'] : '#6B2BD9';
?><!doctype html>
<html <?php language_attributes(); ?>>
<head><meta charset="<?php bloginfo( 'charset' ); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?></head>
<body class="print-body">
<div class="print-bar no-print"><p><?php echo esc_html( sprintf( /* translators: %d cards */ __( '%d table cards. Print on A4 card, 4 to a page, then cut.', 'wwmd' ), $wd_n ) ); ?></p><button class="btn btn--ink" type="button" onclick="window.print()"><?php echo wd_icon( 'print', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Print', 'wwmd' ); ?></button><a class="btn btn--ghost" href="<?php echo esc_url( wd_account_url( 'cards', array( 'wid' => $wd_id ) ) ); ?>"><?php esc_html_e( 'Back', 'wwmd' ); ?></a></div>
<div class="cards-sheet" style="--c:<?php echo esc_attr( $wd_pri ); ?>">
	<?php for ( $wd_i = 1; $wd_i <= $wd_n; $wd_i++ ) : ?>
		<div class="tcard">
			<p class="tcard__t"><?php esc_html_e( 'Table', 'wwmd' ); ?> <b><?php echo (int) $wd_i; ?></b></p>
			<p class="tcard__names"><?php echo esc_html( wd_names( $wd_d ) ); ?></p>
			<div class="tcard__qr" data-qr="<?php echo esc_url( add_query_arg( 'table', $wd_i, $wd_url ) ); ?>"></div>
			<p class="tcard__do"><?php esc_html_e( 'Scan to spray, share your photos, play the quiz & leave a love note', 'wwmd' ); ?></p>
			<p class="tcard__hash"><?php echo $wd_d['hashtag'] ? '#' . esc_html( $wd_d['hashtag'] ) : ''; ?></p>
		</div>
	<?php endfor; ?>
</div>
<?php wp_footer(); ?>
</body>
</html>
