<?php
/**
 * HQ: hide or remove anonymous community posts & replies, love notes, photos & vendor reviews.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
global $wpdb;
// phpcs:disable WordPress.DB
$wd_wall    = $wpdb->get_results( 'SELECT * FROM ' . wd_t( 'wall' ) . " ORDER BY FIELD(status,'pending','approved','hidden'), created DESC LIMIT 40" );
$wd_photos  = $wpdb->get_results( 'SELECT * FROM ' . wd_t( 'photos' ) . " ORDER BY FIELD(status,'pending','approved','hidden'), created DESC LIMIT 24" );
$wd_reviews = $wpdb->get_results( 'SELECT * FROM ' . wd_t( 'reviews' ) . " ORDER BY FIELD(status,'pending','approved','hidden'), created DESC LIMIT 40" );
// phpcs:enable

/**
 * Buttons for one item.
 *
 * @param string $type Type.
 * @param object $r    Row.
 */
$wd_btns = static function ( $type, $r ) {
	foreach ( array( 'hidden' === $r->status || 'pending' === $r->status ? 'show' : 'hide', 'delete' ) as $do ) {
		wd_hq_form( 'moderate' );
		printf( '<input type="hidden" name="type" value="%1$s"><input type="hidden" name="oid" value="%2$d"><input type="hidden" name="do" value="%3$s">', esc_attr( $type ), (int) $r->id, esc_attr( $do ) );
		$label = array(
			'show'   => __( 'Show', 'wwmd' ),
			'hide'   => __( 'Hide', 'wwmd' ),
			'delete' => __( 'Delete', 'wwmd' ),
		);
		printf( '<button class="linkish%1$s" type="submit"%2$s>%3$s</button></form>', 'delete' === $do ? ' linkish--danger' : '', 'delete' === $do ? ' data-confirm="' . esc_attr__( 'Delete for good?', 'wwmd' ) . '"' : '', esc_html( $label[ $do ] ) );
	}
};
?>
<div class="app-head"><div><p class="eyebrow"><?php esc_html_e( 'HQ', 'wwmd' ); ?></p><h1><?php esc_html_e( 'Moderation', 'wwmd' ); ?></h1><p><?php esc_html_e( 'Everything shows straight away unless you switch on approval in Settings. Couples can also hide items on their own page.', 'wwmd' ); ?></p></div></div>
<?php
$wd_talks = get_posts(
	array(
		'post_type'      => 'wd_talk',
		'post_status'    => array( 'publish', 'pending' ),
		'posts_per_page' => 30,
		'orderby'        => array(
			'post_status' => 'ASC',
			'date'        => 'DESC',
		),
	)
);
$wd_treps = get_comments(
	array(
		'type'   => 'wd_reply',
		'status' => 'all',
		'number' => 40,
	)
);
$wd_tbtn  = static function ( $type, $id, $hidden ) {
	foreach ( array( $hidden ? 'show' : 'hide', 'delete' ) as $do ) {
		wd_hq_form( 'moderate_talk' );
		printf( '<input type="hidden" name="type" value="%1$s"><input type="hidden" name="oid" value="%2$d"><input type="hidden" name="do" value="%3$s">', esc_attr( $type ), (int) $id, esc_attr( $do ) );
		$label = array(
			'show'   => __( 'Show', 'wwmd' ),
			'hide'   => __( 'Hide', 'wwmd' ),
			'delete' => __( 'Delete', 'wwmd' ),
		);
		printf( '<button class="linkish%1$s" type="submit"%2$s>%3$s</button></form>', 'delete' === $do ? ' linkish--danger' : '', 'delete' === $do ? ' data-confirm="' . esc_attr__( 'Delete for good?', 'wwmd' ) . '"' : '', esc_html( $label[ $do ] ) );
	}
};
$wd_tname = static function ( $uid ) {
	if ( ! (int) $uid ) {
		return __( 'Visitor, not signed in', 'wwmd' );
	}
	$u = get_userdata( (int) $uid );
	return $u ? $u->display_name . ' (' . $u->user_email . ')' : __( 'Deleted account', 'wwmd' );
};
?>
<div class="panel" id="talk">
	<h2><?php esc_html_e( 'Anonymous community', 'wwmd' ); ?></h2>
	<p class="hint"><?php esc_html_e( 'Only HQ sees who really wrote each post. Everyone else sees the made-up name. Items reported by 3 people are hidden until you show them again.', 'wwmd' ); ?> <a href="<?php echo esc_url( get_post_type_archive_link( 'wd_talk' ) ); ?>"><?php esc_html_e( 'Open the community', 'wwmd' ); ?></a></p>
	<h3><?php esc_html_e( 'Posts', 'wwmd' ); ?></h3>
	<ul class="mod-list">
		<?php
		foreach ( $wd_talks as $wd_r ) :
			$wd_hid = 'pending' === $wd_r->post_status;
			$wd_rep = count( array_filter( (array) get_post_meta( $wd_r->ID, '_wd_reports', true ) ) );
			?>
			<li class="is-<?php echo $wd_hid ? 'pending' : 'approved'; ?>"><div><b><a href="<?php echo esc_url( get_permalink( $wd_r ) ); ?>"><?php echo esc_html( get_the_title( $wd_r ) ); ?></a></b> <small><?php echo esc_html( wd_talk_who( (int) $wd_r->post_author, $wd_r->ID, (string) get_post_meta( $wd_r->ID, '_wd_guest', true ), wd_talk_name( $wd_r->ID ) )[0] . ' = ' . $wd_tname( $wd_r->post_author ) . ' · ' . wd_ago( $wd_r->post_date_gmt ) . ' · ♥ ' . (int) get_post_meta( $wd_r->ID, '_wd_likes', true ) . ' · ' . sprintf( /* translators: %d replies */ _n( '%d comment', '%d comments', (int) $wd_r->comment_count, 'wwmd' ), (int) $wd_r->comment_count ) . ( $wd_rep > 0 ? ' · ' . sprintf( /* translators: %d reports */ _n( '%d report', '%d reports', $wd_rep, 'wwmd' ), $wd_rep ) : '' ) . ( $wd_hid ? ' · ' . __( 'hidden', 'wwmd' ) : '' ) ); ?></small><p><?php echo esc_html( wp_trim_words( $wd_r->post_content, 40 ) ); ?></p></div><div class="row-actions"><?php $wd_tbtn( 'post', $wd_r->ID, $wd_hid ); ?></div></li>
		<?php endforeach; ?>
		<?php if ( ! $wd_talks ) : ?>
			<li><p class="hint"><?php esc_html_e( 'No posts yet.', 'wwmd' ); ?></p></li>
		<?php endif; ?>
	</ul>
	<h3><?php esc_html_e( 'Replies', 'wwmd' ); ?></h3>
	<ul class="mod-list">
		<?php
		foreach ( $wd_treps as $wd_c ) :
			$wd_hid = '1' !== (string) $wd_c->comment_approved;
			$wd_rep = count( array_filter( (array) get_comment_meta( $wd_c->comment_ID, '_wd_reports', true ) ) );
			?>
			<li class="is-<?php echo $wd_hid ? 'pending' : 'approved'; ?>"><div><b><?php echo esc_html( wd_talk_who( (int) $wd_c->user_id, (int) $wd_c->comment_post_ID, (string) get_comment_meta( $wd_c->comment_ID, '_wd_guest', true ), (string) get_comment_meta( $wd_c->comment_ID, '_wd_name', true ) )[0] ); ?></b> <small><?php echo esc_html( $wd_tname( $wd_c->user_id ) . ' · ' . get_the_title( (int) $wd_c->comment_post_ID ) . ' · ' . wd_ago( $wd_c->comment_date_gmt ) . ( $wd_rep > 0 ? ' · ' . sprintf( /* translators: %d reports */ _n( '%d report', '%d reports', $wd_rep, 'wwmd' ), $wd_rep ) : '' ) . ( $wd_hid ? ' · ' . __( 'hidden', 'wwmd' ) : '' ) ); ?></small><p><?php echo esc_html( $wd_c->comment_content ); ?></p></div><div class="row-actions"><?php $wd_tbtn( 'reply', $wd_c->comment_ID, $wd_hid ); ?></div></li>
		<?php endforeach; ?>
		<?php if ( ! $wd_treps ) : ?>
			<li><p class="hint"><?php esc_html_e( 'No replies yet.', 'wwmd' ); ?></p></li>
		<?php endif; ?>
	</ul>
</div>
<div class="panel">
	<h2><?php esc_html_e( 'Love notes', 'wwmd' ); ?></h2>
	<ul class="mod-list">
		<?php foreach ( $wd_wall as $wd_r ) : ?>
			<li class="is-<?php echo esc_attr( $wd_r->status ); ?>"><div><b><?php echo esc_html( $wd_r->name ); ?></b> <small><?php echo esc_html( get_the_title( $wd_r->wedding_id ) . ' · ' . wd_ago( $wd_r->created ) . ' · ♥ ' . $wd_r->likes ); ?></small><p><?php echo esc_html( $wd_r->message ); ?></p></div><div class="row-actions"><?php $wd_btns( 'wall', $wd_r ); ?></div></li>
		<?php endforeach; ?>
	</ul>
</div>
<div class="panel">
	<h2><?php esc_html_e( 'Guest photos', 'wwmd' ); ?></h2>
	<ul class="mod-photos">
		<?php foreach ( $wd_photos as $wd_r ) : ?>
			<li class="is-<?php echo esc_attr( $wd_r->status ); ?>"><?php echo wp_get_attachment_image( (int) $wd_r->attachment_id, 'wd-square', false, array( 'loading' => 'lazy', 'alt' => esc_attr( $wd_r->caption ) ) ); ?><small><?php echo esc_html( $wd_r->name . ' · ' . get_the_title( $wd_r->wedding_id ) ); ?></small><div class="row-actions"><?php $wd_btns( 'photo', $wd_r ); ?></div></li>
		<?php endforeach; ?>
	</ul>
</div>
<div class="panel">
	<h2><?php esc_html_e( 'Vendor reviews', 'wwmd' ); ?></h2>
	<ul class="mod-list">
		<?php foreach ( $wd_reviews as $wd_r ) : ?>
			<li class="is-<?php echo esc_attr( $wd_r->status ); ?>"><div><b><?php echo esc_html( $wd_r->name ); ?></b> <?php echo wd_stars( (float) $wd_r->rating ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <small><?php echo esc_html( get_the_title( $wd_r->vendor_id ) . ' · ' . get_the_title( $wd_r->wedding_id ) . ' · ' . wd_ago( $wd_r->created ) ); ?></small><p><?php echo esc_html( $wd_r->text ); ?></p></div><div class="row-actions"><?php $wd_btns( 'review', $wd_r ); ?></div></li>
		<?php endforeach; ?>
	</ul>
</div>
