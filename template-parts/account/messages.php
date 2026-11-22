<?php
/**
 * Love notes & guest photos: hide, show or delete.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
global $wpdb;
$wd_id     = $args['id'];
$wd_notes  = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . wd_t( 'wall' ) . ' WHERE wedding_id = %d ORDER BY id DESC LIMIT 200', $wd_id ) ); // phpcs:ignore WordPress.DB
$wd_photos = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . wd_t( 'photos' ) . ' WHERE wedding_id = %d ORDER BY id DESC LIMIT 200', $wd_id ) ); // phpcs:ignore WordPress.DB
$wd_act    = static function ( $type, $row, $id ) {
	foreach ( array( 'approved' === $row->status ? 'hide' : 'approve', 'delete' ) as $do ) {
		wd_form( 'moderate', $id, false, 'inline' );
		printf( '<input type="hidden" name="type" value="%1$s"><input type="hidden" name="oid" value="%2$d"><input type="hidden" name="do" value="%3$s"><input type="hidden" name="back" value="messages"><button class="linkish%4$s" type="submit"%5$s>%6$s</button></form>', esc_attr( $type ), (int) $row->id, esc_attr( $do ), 'delete' === $do ? ' linkish--danger' : '', 'delete' === $do ? ' data-confirm="' . esc_attr__( 'Delete for good?', 'wwmd' ) . '"' : '', esc_html( 'hide' === $do ? __( 'Hide', 'wwmd' ) : ( 'approve' === $do ? __( 'Show', 'wwmd' ) : __( 'Delete', 'wwmd' ) ) ) );
	}
};
?>
<div class="app-head"><div><p class="eyebrow"><?php esc_html_e( 'From your guests', 'wwmd' ); ?></p><h1><?php esc_html_e( 'Love notes & photos', 'wwmd' ); ?></h1></div></div>
<p class="muted"><?php esc_html_e( 'Everything shows on your page straight away. Hide anything you do not want there.', 'wwmd' ); ?></p>
<div class="panel">
	<h2><?php echo esc_html( sprintf( /* translators: %d */ __( 'Love notes (%d)', 'wwmd' ), count( (array) $wd_notes ) ) ); ?></h2>
	<ul class="mod-list">
		<?php foreach ( (array) $wd_notes as $wd_n ) : ?>
			<li class="<?php echo 'approved' === $wd_n->status ? '' : 'is-hidden'; ?>"><div><b><?php echo esc_html( $wd_n->name ); ?></b> <small><?php echo esc_html( wd_ago( $wd_n->created ) . ' · ♥ ' . (int) $wd_n->likes ); ?><?php echo 'approved' === $wd_n->status ? '' : ' · ' . esc_html__( 'hidden', 'wwmd' ); ?></small><p><?php echo esc_html( $wd_n->message ); ?></p></div><div class="row-actions"><?php $wd_act( 'wall', $wd_n, $wd_id ); ?></div></li>
		<?php endforeach; ?>
		<?php if ( ! $wd_notes ) : ?><li class="empty-note"><?php esc_html_e( 'No love notes yet.', 'wwmd' ); ?></li><?php endif; ?>
	</ul>
</div>
<div class="panel">
	<h2><?php echo esc_html( sprintf( /* translators: %d */ __( 'Guest photos (%d)', 'wwmd' ), count( (array) $wd_photos ) ) ); ?></h2>
	<div class="mod-photos">
		<?php foreach ( (array) $wd_photos as $wd_p ) : ?>
			<figure class="<?php echo 'approved' === $wd_p->status ? '' : 'is-hidden'; ?>"><img src="<?php echo esc_url( (string) wp_get_attachment_image_url( (int) $wd_p->attachment_id, 'wd-square' ) ); ?>" alt="" loading="lazy"><figcaption><small><?php echo esc_html( $wd_p->name . ' · ♥ ' . (int) $wd_p->likes ); ?></small><span class="row-actions"><?php $wd_act( 'photo', $wd_p, $wd_id ); ?></span></figcaption></figure>
		<?php endforeach; ?>
		<?php if ( ! $wd_photos ) : ?><p class="empty-note"><?php esc_html_e( 'No photos yet.', 'wwmd' ); ?></p><?php endif; ?>
	</div>
</div>
