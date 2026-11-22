<?php
/**
 * The marketplace's social layer (1.5): the familiar photo-app feel, in our brand.
 *
 * Sellers show as round avatars with a brand-gradient ring (a "stories" row), listings as posts with
 * a photo carousel, a heart, chat, share & save, & seller profiles with a photo grid. Hearts are real
 * (one per person, counted per listing); saves are private to each signed-in person.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

/**
 * A seller's short handle, e.g. "@ebi-lace-gele".
 *
 * @param int $pid Seller.
 * @return string
 */
function wd_seller_handle( $pid ) {
	$slug = (string) get_user_meta( (int) $pid, 'wd_partner_slug', true );
	return '@' . ( $slug ? $slug : 'seller' . (int) $pid );
}

/**
 * A seller's round picture inside the brand-gradient ring.
 *
 * @param int    $pid  Seller.
 * @param int    $size Size in px.
 * @param string $tag  a|span.
 * @return string HTML.
 */
function wd_seller_ring( $pid, $size = 56, $tag = 'span' ) {
	$p    = wd_partner( $pid );
	$pic  = $p ? wd_partner_logo( $p, $size ) : wd_avatar( (string) $pid );
	$attr = 'a' === $tag ? ' href="' . esc_url( wd_partner_url( $pid ) ) . '" aria-label="' . esc_attr( $p ? $p['business'] : '' ) . '"' : '';
	return '<' . $tag . ' class="ig-ring" style="--s:' . (int) $size . 'px"' . $attr . '><span class="ig-ring__pic">' . $pic . '</span></' . $tag . '>';
}

/**
 * Sellers for the stories row: approved, with live listings, newest listing first.
 *
 * @param int $max How many.
 * @return int[]
 */
function wd_story_sellers( $max = 12 ) {
	$out = array();
	foreach ( get_posts( array( 'post_type' => 'wd_listing', 'post_status' => 'publish', 'numberposts' => 80, 'orderby' => 'date', 'order' => 'DESC', 'fields' => 'ids' ) ) as $lid ) {
		$pid = (int) get_post_field( 'post_author', $lid );
		if ( ! in_array( $pid, $out, true ) && wd_is_partner( $pid ) ) {
			$out[] = $pid;
		}
		if ( count( $out ) >= $max ) {
			break;
		}
	}
	return $out;
}

/**
 * The stories row of sellers.
 *
 * @param int[] $pids Sellers.
 * @param bool  $join Start with a "Sell here" bubble for people who are not sellers yet.
 * @return string HTML.
 */
function wd_stories_html( $pids, $join = false ) {
	$join = $join && ! wd_is_partner();
	if ( ! $pids && ! $join ) {
		return '';
	}
	$out = '<nav class="ig-stories" aria-label="' . esc_attr__( 'Sellers', 'wwmd' ) . '"><ul>';
	if ( $join ) {
		$out .= '<li class="ig-stories__join"><a href="' . esc_url( wd_seller_join_url() ) . '"><span class="ig-ring ig-ring--join" style="--s:66px"><span class="ig-ring__pic">' . wd_icon( 'store', 26 ) . '</span><i aria-hidden="true">' . wd_icon( 'plus', 14 ) . '</i></span><span class="ig-stories__name">' . esc_html__( 'Sell here', 'wwmd' ) . '</span></a></li>';
	}
	foreach ( $pids as $pid ) {
		$p = wd_partner( $pid );
		if ( ! $p ) {
			continue;
		}
		$out .= '<li><a href="' . esc_url( wd_partner_url( $pid ) ) . '">' . wd_seller_ring( $pid, 66 ) . '<span class="ig-stories__name">' . esc_html( $p['business'] ) . '</span></a></li>';
	}
	return $out . '</ul></nav>';
}

/**
 * Where "Become a seller" goes: the seller form, or sign up first.
 *
 * @return string
 */
function wd_seller_join_url() {
	return is_user_logged_in() ? wd_account_url( 'partner' ) : wd_url( 'sign-in', array( 'tab' => 'join', 'as' => 'seller' ) );
}

/**
 * Hearts on a listing.
 *
 * @param int $lid Listing.
 * @return int
 */
function wd_listing_likes( $lid ) {
	return max( 0, (int) get_post_meta( (int) $lid, '_wd_likes', true ) );
}

/**
 * Whether this visitor hearted a listing.
 *
 * @param int $lid Listing.
 * @return bool
 */
function wd_listing_liked( $lid ) {
	global $wpdb;
	return (bool) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . wd_t( 'likes' ) . " WHERE obj_type = 'listing' AND obj_id = %d AND voter = %s", (int) $lid, wd_voter() ) ); // phpcs:ignore WordPress.DB
}

/**
 * Listings a signed-in person saved.
 *
 * @param int $uid Person.
 * @return int[]
 */
function wd_saved_listings( $uid = 0 ) {
	$uid = $uid ? $uid : get_current_user_id();
	return $uid ? array_values( array_filter( array_map( 'intval', (array) get_user_meta( $uid, 'wd_saved', true ) ) ) ) : array();
}

/**
 * Heart, chat, share & save, under a listing's photos.
 *
 * @param int $lid Listing.
 * @return string HTML.
 */
function wd_listing_actions( $lid ) {
	$n     = wd_listing_likes( $lid );
	$liked = wd_listing_liked( $lid );
	$saved = in_array( (int) $lid, wd_saved_listings(), true );
	$url   = get_permalink( $lid );
	$out   = '<div class="ig-acts">';
	$out  .= '<button class="ig-act ig-act--like' . ( $liked ? ' is-on' : '' ) . '" type="button" data-heart="' . (int) $lid . '" aria-pressed="' . ( $liked ? 'true' : 'false' ) . '" aria-label="' . esc_attr__( 'Like', 'wwmd' ) . '">' . wd_icon( 'heart', 24 ) . '</button>';
	$out  .= '<a class="ig-act" href="' . esc_url( $url . '#book' ) . '" aria-label="' . esc_attr__( 'Chat or book', 'wwmd' ) . '">' . wd_icon( 'chat', 24 ) . '</a>';
	$out  .= '<button class="ig-act" type="button" data-share-url="' . esc_url( $url ) . '" data-share-title="' . esc_attr( get_the_title( $lid ) ) . '" aria-label="' . esc_attr__( 'Share', 'wwmd' ) . '">' . wd_icon( 'send', 24 ) . '</button>';
	$out  .= '<button class="ig-act ig-act--save' . ( $saved ? ' is-on' : '' ) . '" type="button" data-save="' . (int) $lid . '" aria-pressed="' . ( $saved ? 'true' : 'false' ) . '" aria-label="' . esc_attr__( 'Save', 'wwmd' ) . '">' . wd_icon( 'bookmark', 24 ) . '</button>';
	$out  .= '</div>';
	/* translators: %s likes */
	$out .= '<p class="ig-likes" data-heart-count="' . (int) $lid . '"' . ( $n ? '' : ' hidden' ) . '>' . esc_html( sprintf( _n( '%s like', '%s likes', $n, 'wwmd' ), number_format_i18n( $n ) ) ) . '</p>';
	return $out;
}

/**
 * A listing's photos as a swipeable carousel (or its illustration when it has none).
 *
 * @param int    $lid  Listing.
 * @param array  $l    Listing data.
 * @param string $size Image size.
 * @param int    $max  Most photos to show.
 * @param string $mod  Extra class, e.g. "ig-media--wide".
 * @return string HTML.
 */
function wd_listing_carousel( $lid, $l, $size = 'wd-square', $max = 6, $mod = '' ) {
	$cls = 'ig-media' . ( $mod ? ' ' . sanitize_html_class( $mod ) : '' );
	$ph = array_slice( array_values( array_filter( (array) $l['photos'] ) ), 0, $max );
	if ( ! $ph ) {
		return '<div class="' . $cls . ' tone-' . esc_attr( wd_listing_tone( $lid, $l ) ) . '"><div class="ig-media__one">' . wd_listing_cover( $lid, $l, $size ) . '</div></div>';
	}
	$out = '<div class="' . $cls . '" data-carousel><div class="ig-media__track" tabindex="0" aria-label="' . esc_attr__( 'Photos', 'wwmd' ) . '">';
	foreach ( $ph as $i => $p ) {
		$out .= '<img src="' . esc_url( wd_img_url( $p, $size ) ) . '" alt="" ' . ( $i ? 'loading="lazy"' : 'decoding="async"' ) . '>';
	}
	$out .= '</div>';
	if ( count( $ph ) > 1 ) {
		$out .= '<button class="ig-media__nav ig-media__nav--prev" type="button" data-slide="-1" aria-label="' . esc_attr__( 'Previous photo', 'wwmd' ) . '">' . wd_icon( 'arrow', 16 ) . '</button>';
		$out .= '<button class="ig-media__nav ig-media__nav--next" type="button" data-slide="1" aria-label="' . esc_attr__( 'Next photo', 'wwmd' ) . '">' . wd_icon( 'arrow', 16 ) . '</button>';
		$out .= '<p class="ig-media__dots" aria-hidden="true">' . str_repeat( '<i></i>', count( $ph ) ) . '</p>';
	}
	return $out . '</div>';
}

/**
 * Price line for a listing caption.
 *
 * @param array $l Listing data.
 * @return string
 */
function wd_listing_price_line( $l ) {
	if ( wd_listing_asks( $l ) ) {
		return __( 'Contact for price', 'wwmd' );
	}
	$u = wd_units();
	return wd_money( $l['price'] ) . ' / ' . ( $u[ $l['unit'] ][0] ?? $l['unit'] );
}

add_action(
	'rest_api_init',
	static function () {
		register_rest_route(
			'wd/v1',
			'/listing/like',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => static function ( WP_REST_Request $r ) {
					global $wpdb;
					$lid = (int) $r->get_param( 'id' );
					if ( 'wd_listing' !== get_post_type( $lid ) || 'publish' !== get_post_status( $lid ) ) {
						return new WP_Error( 'gone', 'Not found', array( 'status' => 404 ) );
					}
					if ( ! wd_rate_ok( 'like', 120, HOUR_IN_SECONDS ) ) {
						return wd_slow_down();
					}
					$lt    = wd_t( 'likes' );
					$voter = wd_voter();
					$has   = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $lt WHERE obj_type = 'listing' AND obj_id = %d AND voter = %s", $lid, $voter ) ); // phpcs:ignore WordPress.DB
					if ( $has ) {
						$wpdb->delete( $lt, array( 'id' => $has ) );
					} else {
						$wpdb->insert(
							$lt,
							array(
								'obj_type' => 'listing',
								'obj_id'   => $lid,
								'voter'    => $voter,
								'created'  => current_time( 'mysql', true ),
							)
						);
					}
					$n = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $lt WHERE obj_type = 'listing' AND obj_id = %d", $lid ) ); // phpcs:ignore WordPress.DB
					update_post_meta( $lid, '_wd_likes', $n );
					return array(
						'ok'    => true,
						'liked' => ! $has,
						'likes' => $n,
						/* translators: %s likes */
						'label' => sprintf( _n( '%s like', '%s likes', $n, 'wwmd' ), number_format_i18n( $n ) ),
					);
				},
			)
		);
		register_rest_route(
			'wd/v1',
			'/listing/save',
			array(
				'methods'             => 'POST',
				'permission_callback' => 'is_user_logged_in',
				'callback'            => static function ( WP_REST_Request $r ) {
					$lid = (int) $r->get_param( 'id' );
					if ( 'wd_listing' !== get_post_type( $lid ) ) {
						return new WP_Error( 'gone', 'Not found', array( 'status' => 404 ) );
					}
					$all = wd_saved_listings();
					$on  = ! in_array( $lid, $all, true );
					$all = $on ? array_merge( array( $lid ), $all ) : array_diff( $all, array( $lid ) );
					update_user_meta( get_current_user_id(), 'wd_saved', array_slice( array_values( $all ), 0, 200 ) );
					return array(
						'ok'    => true,
						'saved' => $on,
					);
				},
			)
		);
	}
);

/**
 * Latest love notes & guest photos from public wedding pages, for "From the community" excerpts.
 * Only approved items, only from published pages that still show that section.
 *
 * @param int $notes  How many love notes.
 * @param int $photos How many photos.
 * @return array notes, photos (rows with wedding_id).
 */
function wd_community( $notes = 3, $photos = 6 ) {
	global $wpdb;
	$out = array(
		'notes'  => array(),
		'photos' => array(),
	);
	$ok  = static function ( $wid, $sec ) {
		static $seen = array();
		$k = $wid . $sec;
		if ( ! isset( $seen[ $k ] ) ) {
			$seen[ $k ] = 'publish' === get_post_status( $wid ) && 'wd_wedding' === get_post_type( $wid ) && wd_show( $wid, $sec );
		}
		return $seen[ $k ];
	};
	$rows = $wpdb->get_results( 'SELECT id, wedding_id, name, message, likes FROM ' . wd_t( 'wall' ) . " WHERE status = 'approved' AND message <> '' ORDER BY id DESC LIMIT 60" ); // phpcs:ignore WordPress.DB
	$per  = array();
	foreach ( (array) $rows as $r ) {
		// One note per wedding, so the excerpt shows several couples.
		$key = md5( strtolower( trim( $r->message ) ) );
		if ( count( $out['notes'] ) >= $notes || isset( $per[ $r->wedding_id ] ) || isset( $per[ $key ] ) || ! $ok( (int) $r->wedding_id, 'wall' ) || mb_strlen( $r->message ) > 260 ) {
			continue;
		}
		$per[ $r->wedding_id ] = 1;
		$per[ $key ]           = 1;
		$out['notes'][]        = $r;
	}
	$rows = $wpdb->get_results( 'SELECT id, wedding_id, attachment_id, name, caption FROM ' . wd_t( 'photos' ) . " WHERE status = 'approved' AND attachment_id > 0 ORDER BY id DESC LIMIT 80" ); // phpcs:ignore WordPress.DB
	$per  = array();
	foreach ( (array) $rows as $r ) {
		$src = (string) get_attached_file( (int) $r->attachment_id );
		$key = 'f' . md5( (string) basename( $src ) );
		if ( count( $out['photos'] ) >= $photos || isset( $per[ $key ] ) || ( $per[ $r->wedding_id ] ?? 0 ) >= 2 || ! $ok( (int) $r->wedding_id, 'gallery' ) || ! wp_get_attachment_image_url( (int) $r->attachment_id, 'wd-square' ) ) {
			continue;
		}
		$per[ $r->wedding_id ] = ( $per[ $r->wedding_id ] ?? 0 ) + 1;
		$per[ $key ]           = 1;
		$out['photos'][]       = $r;
	}
	return $out;
}
