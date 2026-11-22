<?php
/**
 * Table QR cards (Premium): delivery details, status & printable cards.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_id = $args['id'];
$wd_d  = $args['d'];
$wd_st = array(
	''          => __( 'Add your delivery details to order', 'wwmd' ),
	'requested' => __( 'Ordered: we are designing your cards', 'wwmd' ),
	'printing'  => __( 'Printing', 'wwmd' ),
	'shipped'   => __( 'Shipped', 'wwmd' ),
	'delivered' => __( 'Delivered', 'wwmd' ),
);
?>
<div class="app-head"><div><p class="eyebrow"><?php esc_html_e( 'Premium', 'wwmd' ); ?></p><h1><?php esc_html_e( 'Table QR cards', 'wwmd' ); ?></h1></div></div>
<?php if ( ! wd_can( $wd_id, 'table_qr' ) ) : ?>
	<?php get_template_part( 'template-parts/account/upsell', null, array( 'id' => $wd_id, 'what' => sprintf( /* translators: %d cards */ __( '%d printed table QR cards, shipped to you', 'wwmd' ), (int) wd_opt( 'premium_qr_cards' ) ) ) ); ?>
	<div class="panel panel--soft"><p><?php echo esc_html( sprintf( /* translators: 1 price 2 qty */ __( 'On a free page? You can still order printed cards: %1$s for %2$d cards.', 'wwmd' ), wd_money( wd_opt( 'extra_qr_price' ) ), (int) wd_opt( 'extra_qr_qty' ) ) ); ?></p><a class="btn btn--ghost btn--sm" href="<?php echo esc_url( wd_whatsapp( sprintf( /* translators: %s url */ __( 'Hello! I would like to order table QR cards for %s', 'wwmd' ), get_permalink( $wd_id ) ) ) ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'whatsapp', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Order on WhatsApp', 'wwmd' ); ?></a></div>
<?php else : ?>
	<div class="app-cols">
		<div class="panel">
			<div class="panel__head"><h2><?php esc_html_e( 'Your printed cards', 'wwmd' ); ?></h2><span class="pill<?php echo in_array( $wd_d['cards']['status'], array( 'shipped', 'delivered' ), true ) ? ' pill--ok' : ''; ?>"><?php echo esc_html( $wd_st[ $wd_d['cards']['status'] ] ?? $wd_d['cards']['status'] ); ?></span></div>
			<?php if ( $wd_d['cards']['tracking'] ) : ?><p><?php esc_html_e( 'Tracking:', 'wwmd' ); ?> <b><?php echo esc_html( $wd_d['cards']['tracking'] ); ?></b></p><?php endif; ?>
			<p class="muted"><?php echo esc_html( sprintf( /* translators: %d cards */ __( 'Your plan includes %d cards. Each table gets its own card that opens your page with a “Welcome to table 5” note, so guests can spray, upload photos & leave love notes from their seat.', 'wwmd' ), (int) wd_opt( 'premium_qr_cards' ) ) ); ?></p>
			<?php wd_form( 'cards', $wd_id, false, 'form' ); ?>
				<?php wd_field( 'tables', __( 'How many tables?', 'wwmd' ), $wd_d['cards']['tables'], 'number', 'min="1" max="200"' ); ?>
				<?php wd_field( 'address', __( 'Delivery address', 'wwmd' ), $wd_d['cards']['address'], 'textarea', 'required' ); ?>
				<?php wd_field( 'phone', __( 'Phone for the courier', 'wwmd' ), $wd_d['cards']['phone'], 'tel', 'required' ); ?>
				<button class="btn btn--ink" type="submit"><?php esc_html_e( 'Save & order', 'wwmd' ); ?></button>
			</form>
		</div>
		<div class="panel">
			<h2><?php esc_html_e( 'Print your own too', 'wwmd' ); ?></h2>
			<p class="muted"><?php esc_html_e( 'Need extra cards for the after-party? Print them here any time.', 'wwmd' ); ?></p>
			<div class="tcard tcard--preview" style="--c:<?php echo esc_attr( $wd_d['design']['primary'] ); ?>">
				<p class="tcard__t"><?php esc_html_e( 'Table', 'wwmd' ); ?> <b>1</b></p>
				<p class="tcard__names"><?php echo esc_html( wd_names( $wd_d ) ); ?></p>
				<div class="tcard__qr" data-qr="<?php echo esc_url( add_query_arg( 'table', 1, get_permalink( $wd_id ) ) ); ?>"></div>
				<p class="tcard__do"><?php esc_html_e( 'Scan to spray, share your photos, play the quiz & leave a love note', 'wwmd' ); ?></p>
			</div>
			<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/table-cards/' . $wd_id . '/' ) ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'print', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Open printable cards', 'wwmd' ); ?></a>
		</div>
	</div>
<?php endif; ?>
