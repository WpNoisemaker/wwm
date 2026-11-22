<?php
/**
 * HQ: service requests (apartments, rides, honeymoon & more).
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
global $wpdb;
$wd_rows = $wpdb->get_results( 'SELECT * FROM ' . wd_t( 'requests' ) . " ORDER BY FIELD(status,'new','talking','booked','closed'), created DESC LIMIT 100" ); // phpcs:ignore WordPress.DB
$wd_svc  = wd_services();
$wd_st   = array(
	'new'     => __( 'New', 'wwmd' ),
	'talking' => __( 'In talks', 'wwmd' ),
	'booked'  => __( 'Booked', 'wwmd' ),
	'closed'  => __( 'Closed', 'wwmd' ),
);
?>
<div class="app-head"><div><p class="eyebrow"><?php esc_html_e( 'HQ', 'wwmd' ); ?></p><h1><?php esc_html_e( 'Service requests', 'wwmd' ); ?></h1><p><?php esc_html_e( 'Requests from the Services page & couple dashboards. Reply on WhatsApp, then move each one along.', 'wwmd' ); ?></p></div></div>
<?php if ( ! $wd_rows ) : ?>
	<p class="empty"><?php esc_html_e( 'No requests yet.', 'wwmd' ); ?></p>
<?php else : ?>
<ul class="req-list">
	<?php foreach ( $wd_rows as $wd_r ) : ?>
		<li class="panel is-<?php echo esc_attr( $wd_r->status ); ?>">
			<div class="req-list__top"><b><?php echo esc_html( $wd_svc[ $wd_r->service ]['title'] ?? ucfirst( $wd_r->service ) ); ?></b><span class="pill<?php echo 'new' === $wd_r->status ? ' pill--warn' : ''; ?>"><?php echo esc_html( $wd_st[ $wd_r->status ] ?? $wd_r->status ); ?></span><small><?php echo esc_html( wd_ago( $wd_r->created ) ); ?></small></div>
			<p><b><?php echo esc_html( $wd_r->name ); ?></b> · <?php echo esc_html( $wd_r->phone ); ?> · <?php echo esc_html( $wd_r->email ); ?><?php echo $wd_r->wedding_id ? ' · ' . esc_html( get_the_title( $wd_r->wedding_id ) ) : ''; ?></p>
			<p class="req-list__details"><?php echo esc_html( $wd_r->details ); ?></p>
			<div class="row-actions">
				<?php $wd_ph = preg_replace( '/\D/', '', $wd_r->phone ); ?>
				<?php if ( $wd_ph ) : ?><a class="btn btn--wa btn--sm" href="<?php echo esc_url( 'https://wa.me/' . ( 0 === strpos( $wd_ph, '0' ) ? '234' . substr( $wd_ph, 1 ) : $wd_ph ) ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'whatsapp', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Reply on WhatsApp', 'wwmd' ); ?></a><?php endif; ?>
				<?php wd_hq_form( 'request', 'inline inline--fields' ); ?><input type="hidden" name="id" value="<?php echo (int) $wd_r->id; ?>"><select name="status" onchange="this.form.submit()" aria-label="<?php esc_attr_e( 'Status', 'wwmd' ); ?>"><?php foreach ( $wd_st as $wd_k => $wd_l ) : ?><option value="<?php echo esc_attr( $wd_k ); ?>" <?php selected( $wd_r->status, $wd_k ); ?>><?php echo esc_html( $wd_l ); ?></option><?php endforeach; ?></select></form>
			</div>
		</li>
	<?php endforeach; ?>
</ul>
<?php endif; ?>
