<?php
/**
 * Services marketplace, like Airbnb or Jiji for wedding services.
 *
 * Partners (short-let hosts, car hire firms, travel agents, asoebi sellers) apply to join. HQ approves
 * them by hand, then they list their apartments, cars, trips & asoebi. A listing shows a price, or
 * "Contact for price": then the customer chats with the partner here & the partner sends an offer.
 * Nobody books directly: customers chat with the seller, & the seller sends an invoice (paid to the
 * seller however they agree) or a payment link (paid on the site, the seller's share goes straight to
 * them, less our commission). We are not an escrow & do not hold money. Each partner has a public profile, like on Jiji.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sectors: key => [label, icon, art, tone].
 *
 * @return array
 */
function wd_sectors() {
	return array(
		'stays'  => array( __( 'Apartments & stays', 'wwmd' ), 'home', 'house', 'butter' ),
		'rides'  => array( __( 'Cars & rides', 'wwmd' ), 'car', 'car', 'sky' ),
		'travel' => array( __( 'Honeymoon & travel', 'wwmd' ), 'plane', 'plane', 'coral' ),
		'asoebi' => array( __( 'Asoebi sourcing', 'wwmd' ), 'fabric', 'fabric', 'lavender' ),
	);
}

/**
 * Price units: key => [one, many, how it is counted (range = dates, qty = number)].
 *
 * @return array
 */
function wd_units() {
	return array(
		'night'   => array( __( 'night', 'wwmd' ), __( 'nights', 'wwmd' ), 'range' ),
		'day'     => array( __( 'day', 'wwmd' ), __( 'days', 'wwmd' ), 'range' ),
		'trip'    => array( __( 'trip', 'wwmd' ), __( 'trips', 'wwmd' ), 'qty' ),
		'person'  => array( __( 'person', 'wwmd' ), __( 'people', 'wwmd' ), 'qty' ),
		'package' => array( __( 'package', 'wwmd' ), __( 'packages', 'wwmd' ), 'qty' ),
	);
}

/**
 * Booking statuses: key => [label, tone].
 *
 * @return array
 */
function wd_booking_statuses() {
	return array(
		'pending'   => array( __( 'Waiting for payment', 'wwmd' ), 'butter' ),
		'held'      => array( __( 'Paid', 'wwmd' ), 'sky' ),
		'released'  => array( __( 'Paid', 'wwmd' ), 'mint' ),
		'direct'    => array( __( 'Paid to the seller', 'wwmd' ), 'mint' ),
		'disputed'  => array( __( 'Problem reported', 'wwmd' ), 'coral' ),
		'refunded'  => array( __( 'Refunded', 'wwmd' ), 'lavender' ),
		'cancelled' => array( __( 'Cancelled', 'wwmd' ), 'lavender' ),
	);
}

add_action(
	'init',
	static function () {
		add_rewrite_rule( '^seller/([^/]+)/?$', 'index.php?wd_partner=$matches[1]', 'top' );
		// Old /partner/ links (1.4) move to /seller/.
		add_rewrite_rule( '^partner/([^/]+)/?$', 'index.php?wd_partner=$matches[1]&wd_old_partner=1', 'top' );
		register_post_type(
			'wd_listing',
			array(
				'labels'       => array(
					'name'          => __( 'Service listings', 'wwmd' ),
					'singular_name' => __( 'Service listing', 'wwmd' ),
				),
				'public'       => true,
				'show_ui'      => true,
				'menu_icon'    => 'dashicons-car',
				'has_archive'  => false,
				'rewrite'      => array(
					'slug'       => 'service',
					'with_front' => false,
				),
				'supports'     => array( 'title', 'thumbnail', 'author' ),
				'show_in_rest' => false,
			)
		);
	}
);

add_filter(
	'query_vars',
	static function ( $vars ) {
		$vars[] = 'wd_partner';
		$vars[] = 'wd_old_partner';
		return $vars;
	}
);

/* A profile is a page of its own: not the home page, & it loads no blog posts. */
add_action(
	'parse_query',
	static function ( $q ) {
		if ( $q->is_main_query() && $q->get( 'wd_partner' ) ) {
			$q->is_home = false;
		}
	}
);
add_filter(
	'posts_pre_query',
	static function ( $posts, $q ) {
		return ( $q->is_main_query() && $q->get( 'wd_partner' ) ) ? array() : $posts;
	},
	10,
	2
);
add_filter(
	'pre_handle_404',
	static function ( $done, $q ) {
		$slug = $q->is_main_query() ? $q->get( 'wd_partner' ) : '';
		if ( $slug ) {
			$uid = wd_partner_by_slug( $slug );
			return $uid && wd_is_partner( $uid ) ? true : $done;
		}
		return $done;
	},
	10,
	2
);
add_filter(
	'body_class',
	static function ( $c ) {
		return get_query_var( 'wd_partner_id' ) ? array_merge( array_diff( $c, array( 'home', 'blog' ) ), array( 'partner-profile' ) ) : $c;
	}
);

/* /seller/{slug}/ shows the seller's profile, for approved sellers only. */
add_filter(
	'template_include',
	static function ( $tpl ) {
		$slug = get_query_var( 'wd_partner' );
		if ( ! $slug ) {
			return $tpl;
		}
		if ( get_query_var( 'wd_old_partner' ) ) {
			wp_safe_redirect( home_url( '/seller/' . rawurlencode( $slug ) . '/' ), 301 );
			exit;
		}
		$uid = wd_partner_by_slug( $slug );
		if ( ! $uid ) {
			// A seller who was renamed keeps their old address working.
			$was = get_users(
				array(
					'meta_key'   => 'wd_partner_slug_old', // phpcs:ignore WordPress.DB.SlowDBQuery
					'meta_value' => sanitize_title( $slug ), // phpcs:ignore WordPress.DB.SlowDBQuery
					'fields'     => 'ID',
					'number'     => 1,
				)
			);
			if ( $was && wd_is_partner( (int) $was[0] ) ) {
				wp_safe_redirect( wd_partner_url( (int) $was[0] ), 301 );
				exit;
			}
		}
		if ( ! $uid || ! wd_is_partner( $uid ) ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			return get_404_template();
		}
		set_query_var( 'wd_partner_id', $uid );
		status_header( 200 );
		return WD_DIR . '/partner.php';
	},
	5
);

/**
 * Listing data.
 *
 * @param int $id Listing ID.
 * @return array
 */
function wd_listing( $id ) {
	$l = get_post_meta( $id, '_wl', true );
	return wp_parse_args(
		is_array( $l ) ? $l : array(),
		array(
			'sector'   => 'stays',
			'city'     => '',
			'country'  => 'Nigeria',
			'price'    => 0,
			'unit'     => 'night',
			'capacity' => 0,
			'features' => array(),
			'about'    => '',
			'photos'   => array(),
			'art'      => '',
			'price_mode' => 'fixed',
		)
	);
}

/**
 * Whether a listing hides its price ("Contact for price"): the customer chats & the partner sends an offer.
 *
 * @param array $l Listing data.
 * @return bool
 */
function wd_listing_asks( $l ) {
	return 'ask' === ( $l['price_mode'] ?? 'fixed' );
}

/**
 * Save listing data & its search keys.
 *
 * @param int   $id Listing ID.
 * @param array $l  Data.
 */
function wd_listing_put( $id, $l ) {
	update_post_meta( $id, '_wl', $l );
	update_post_meta( $id, '_wl_sector', $l['sector'] );
	update_post_meta( $id, '_wl_city', $l['city'] );
	update_post_meta( $id, '_wl_price', wd_listing_asks( $l ) ? 0 : (int) $l['price'] );
}

/**
 * Partner profile (user meta), or an empty array for people who never applied.
 *
 * @param int $uid User ID.
 * @return array
 */
function wd_partner( $uid ) {
	$p = get_user_meta( (int) $uid, 'wd_partner', true );
	if ( ! is_array( $p ) || empty( $p['status'] ) ) {
		return array();
	}
	return wp_parse_args(
		$p,
		array(
			'status'   => 'pending',
			'business' => '',
			'sector'   => 'stays',
			'city'     => '',
			'country'  => 'Nigeria',
			'phone'    => '',
			'about'    => '',
			'website'  => '',
			'applied'  => '',
			'approved' => '',
			'hours'    => '',
			'areas'    => '',
			'logo'     => 0,
			'cover'    => 0,
			'portfolio' => array(),
			'premium'  => 0,
			'brand'    => array(),
		)
	);
}

/**
 * Is this seller on Premium? HQ switches it on in HQ › Marketplace › Sellers.
 *
 * @param array $p Seller profile.
 * @return bool
 */
function wd_seller_premium( $p ) {
	return ! empty( $p['premium'] );
}

/**
 * A seller's invoice look: their colours, font & logo, as saved. Defaults to the brand purple.
 *
 * @param array $p Seller profile.
 * @return array primary, accent, font, logo (attachment ID, 0 = their profile logo)
 */
function wd_seller_look( $p ) {
	$b = isset( $p['brand'] ) && is_array( $p['brand'] ) ? $p['brand'] : array();
	return array(
		'primary' => wd_hex( $b['primary'] ?? '', '#23094B' ),
		'accent'  => wd_hex( $b['accent'] ?? '', '#A65BF2' ),
		'font'    => isset( $b['font'], wd_fonts()[ $b['font'] ] ) ? $b['font'] : 'avantgarde',
		'logo'    => (int) ( $b['logo'] ?? 0 ),
	);
}

/**
 * The look to show on a seller's invoices, payment links & receipts: theirs for Premium sellers,
 * else an empty array (the standard look).
 *
 * @param array $p Seller profile.
 * @return array
 */
function wd_seller_brand( $p ) {
	if ( ! $p || ! wd_seller_premium( $p ) ) {
		return array();
	}
	$b       = wd_seller_look( $p );
	$b['on'] = wd_text_on( $b['primary'] );
	$f       = wd_fonts()[ $b['font'] ];
	$b['css'] = $f[1];
	$b['gf']  = $f[2];
	$b['img'] = $b['logo'] ? $b['logo'] : (int) $p['logo'];
	return $b;
}

/**
 * Text colour that reads well on a background colour: white on dark colours, ink on light ones.
 *
 * @param string $hex Background.
 * @return string
 */
function wd_text_on( $hex ) {
	$h = ltrim( (string) $hex, '#' );
	if ( 3 === strlen( $h ) ) {
		$h = $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2];
	}
	if ( 6 !== strlen( $h ) ) {
		return '#ffffff';
	}
	$c = array();
	foreach ( array( 0, 2, 4 ) as $i ) {
		$v   = hexdec( substr( $h, $i, 2 ) ) / 255;
		$c[] = $v <= 0.03928 ? $v / 12.92 : pow( ( $v + 0.055 ) / 1.055, 2.4 );
	}
	$l = 0.2126 * $c[0] + 0.7152 * $c[1] + 0.0722 * $c[2];
	return $l > 0.36 ? '#1C0B33' : '#ffffff';
}

/**
 * CSS variables for a seller's branded invoice.
 *
 * @param array $b From wd_seller_brand().
 * @return string
 */
function wd_seller_brand_style( $b ) {
	if ( ! $b ) {
		return '';
	}
	return sprintf( '--b-primary:%1$s;--b-accent:%2$s;--b-on:%3$s;--b-on2:%5$s;--b-font:%4$s;', $b['primary'], $b['accent'], $b['on'], str_replace( '"', "'", $b['css'] ), wd_text_on( $b['accent'] ) );
}

/**
 * Save a partner profile (& the status on its own, so we can query by it).
 *
 * @param int   $uid User ID.
 * @param array $p   Profile.
 */
function wd_partner_put( $uid, $p ) {
	update_user_meta( $uid, 'wd_partner', $p );
	update_user_meta( $uid, 'wd_partner_status', $p['status'] );
}

/**
 * A seller's profile address part, e.g. "ebi-lace-gele" for /seller/ebi-lace-gele/. Made once from
 * the business name & kept, so shared links keep working when the name changes.
 *
 * @param int $uid Partner user ID.
 * @return string
 */
function wd_partner_slug( $uid ) {
	$slug = (string) get_user_meta( $uid, 'wd_partner_slug', true );
	if ( $slug ) {
		return $slug;
	}
	$p    = wd_partner( $uid );
	$base = sanitize_title( $p ? $p['business'] : '' );
	$base = $base ? substr( $base, 0, 60 ) : 'partner-' . (int) $uid;
	$slug = $base;
	for ( $i = 2; wd_partner_by_slug( $slug ); $i++ ) {
		$slug = $base . '-' . $i;
	}
	update_user_meta( $uid, 'wd_partner_slug', $slug );
	return $slug;
}

/**
 * The partner with this profile address.
 *
 * @param string $slug Slug.
 * @return int User ID or 0.
 */
function wd_partner_by_slug( $slug ) {
	$ids = get_users(
		array(
			'meta_key'   => 'wd_partner_slug', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value' => sanitize_title( $slug ), // phpcs:ignore WordPress.DB.SlowDBQuery
			'fields'     => 'ID',
			'number'     => 1,
		)
	);
	return $ids ? (int) $ids[0] : 0;
}

/**
 * A partner's public profile page.
 *
 * @param int $uid Partner user ID.
 * @return string
 */
function wd_partner_url( $uid ) {
	return home_url( '/seller/' . wd_partner_slug( $uid ) . '/' );
}

/**
 * Whether to show the "Verified partner" badge: HQ approved the partner after checking them.
 * Starter partners that came with the theme are not checked, so they never show it.
 *
 * @param int $uid Partner user ID.
 * @return bool
 */
function wd_partner_verified( $uid ) {
	if ( ! wd_is_partner( $uid ) ) {
		return false;
	}
	$p = wd_partner( $uid );
	// HQ can switch the badge on or off (HQ › Marketplace › Sellers); otherwise real sellers are verified.
	return isset( $p['verified'] ) && '' !== (string) $p['verified'] ? (bool) $p['verified'] : ! get_user_meta( $uid, '_wd_sample', true );
}

/**
 * Small "Verified partner" badge.
 *
 * @param int  $uid   Partner.
 * @param bool $label With the words.
 * @return string HTML or ''.
 */
function wd_partner_badge( $uid, $label = true ) {
	if ( ! wd_partner_verified( $uid ) ) {
		return '';
	}
	if ( ! $label ) {
		return '<span class="l-tick" title="' . esc_attr__( 'Verified seller', 'wwmd' ) . '">' . wd_icon( 'verified', 15 ) . '</span>';
	}
	return '<span class="vbadge vbadge--partner">' . wd_icon( 'verified', 15 ) . esc_html__( 'Verified seller', 'wwmd' ) . '</span>';
}

/**
 * A partner's numbers for their profile: live listings, finished bookings & reviews.
 *
 * @param int $uid Partner user ID.
 * @return array listings, done, rating, reviews
 */
function wd_partner_stats( $uid ) {
	global $wpdb;
	$r = $wpdb->get_row( $wpdb->prepare( 'SELECT COUNT(*) n, COALESCE(AVG(rating),0) a FROM ' . wd_t( 'previews' ) . " WHERE partner_id = %d AND status = 'approved'", $uid ) ); // phpcs:ignore WordPress.DB
	return array(
		'listings' => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'wd_listing' AND post_status = 'publish' AND post_author = %d", $uid ) ),
		'done'     => (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . wd_t( 'bookings' ) . " WHERE partner_id = %d AND status IN ('released','direct') AND test = 0", $uid ) ), // phpcs:ignore WordPress.DB
		'rating'   => $r ? round( (float) $r->a, 1 ) : 0,
		'reviews'  => $r ? (int) $r->n : 0,
	);
}

/**
 * Reviews of a partner by customers who booked & paid on the site.
 *
 * @param int    $uid    Partner.
 * @param string $status approved|pending|any.
 * @param int    $limit  How many.
 * @return object[]
 */
function wd_partner_reviews( $uid, $status = 'approved', $limit = 30 ) {
	global $wpdb;
	$t = wd_t( 'previews' );
	if ( 'any' === $status ) {
		return (array) $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $t WHERE partner_id = %d ORDER BY id DESC LIMIT %d", $uid, $limit ) ); // phpcs:ignore WordPress.DB
	}
	return (array) $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $t WHERE partner_id = %d AND status = %s ORDER BY id DESC LIMIT %d", $uid, $status, $limit ) ); // phpcs:ignore WordPress.DB
}

/**
 * Portfolio photos: the partner's own, else their listing photos.
 *
 * @param int   $uid Partner.
 * @param array $p   Profile.
 * @return array [img, caption]
 */
function wd_partner_portfolio( $uid, $p ) {
	$out = array();
	foreach ( (array) $p['portfolio'] as $ph ) {
		if ( ! empty( $ph['img'] ) ) {
			$out[] = array( $ph['img'], (string) ( $ph['cap'] ?? '' ) );
		}
	}
	return $out;
}

/**
 * Logo or initials for a partner.
 *
 * @param array $p    Profile.
 * @param int   $size Pixels.
 * @return string HTML.
 */
function wd_partner_logo( $p, $size = 64 ) {
	if ( ! empty( $p['logo'] ) ) {
		return '<img src="' . esc_url( wd_img_url( $p['logo'], 'thumbnail' ) ) . '" alt="" width="' . (int) $size . '" height="' . (int) $size . '" loading="lazy">';
	}
	return wd_avatar( $p['business'] );
}

/**
 * IDs of approved partners.
 *
 * @return int[]
 */
function wd_partner_ids() {
	static $ids = null;
	if ( null === $ids ) {
		$ids = array_map(
			'intval',
			get_users(
				array(
					'meta_key'   => 'wd_partner_status', // phpcs:ignore WordPress.DB.SlowDBQuery
					'meta_value' => 'approved', // phpcs:ignore WordPress.DB.SlowDBQuery
					'fields'     => 'ID',
				)
			)
		);
	}
	return $ids;
}

/**
 * Live listings from approved partners.
 *
 * @param array $a sector, city, q, paged, per, exclude.
 * @return WP_Query
 */
function wd_listing_query( $a ) {
	$a    = wp_parse_args(
		$a,
		array(
			'sector'  => '',
			'city'    => '',
			'q'       => '',
			'paged'   => 1,
			'per'     => 12,
			'exclude' => array(),
		)
	);
	$meta = array();
	if ( $a['sector'] ) {
		$meta[] = array(
			'key'   => '_wl_sector',
			'value' => $a['sector'],
		);
	}
	if ( $a['city'] ) {
		$meta[] = array(
			'key'   => '_wl_city',
			'value' => $a['city'],
		);
	}
	$ids = wd_partner_ids();
	return new WP_Query(
		array(
			'post_type'      => 'wd_listing',
			'post_status'    => 'publish',
			'posts_per_page' => (int) $a['per'],
			'paged'          => (int) $a['paged'],
			's'              => $a['q'],
			'author__in'     => $ids ? $ids : array( 0 ),
			'post__not_in'   => (array) $a['exclude'],
			'meta_query'     => $meta, // phpcs:ignore WordPress.DB.SlowDBQuery
			'orderby'        => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
		)
	);
}

/**
 * Live listings per sector.
 *
 * @return array sector => count
 */
function wd_listing_counts() {
	$out = array();
	foreach ( array_keys( wd_sectors() ) as $k ) {
		$out[ $k ] = (int) wd_listing_query(
			array(
				'sector' => $k,
				'per'    => 1,
			)
		)->found_posts;
	}
	return $out;
}

/**
 * Cities with live listings.
 *
 * @return string[]
 */
function wd_listing_cities() {
	global $wpdb;
	$ids = wd_partner_ids();
	if ( ! $ids ) {
		return array();
	}
	return $wpdb->get_col( "SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = '_wl_city' AND pm.meta_value <> '' AND p.post_type = 'wd_listing' AND p.post_status = 'publish' AND p.post_author IN (" . implode( ',', array_map( 'intval', $ids ) ) . ') ORDER BY pm.meta_value' ); // phpcs:ignore WordPress.DB
}

/**
 * Approved partner?
 *
 * @param int $uid User ID (0 = current user).
 * @return bool
 */
function wd_is_partner( $uid = 0 ) {
	$p = wd_partner( $uid ? $uid : get_current_user_id() );
	return $p && 'approved' === $p['status'];
}

/**
 * Listing picture: the first photo, or the sector's illustration.
 *
 * @param int        $id   Listing ID.
 * @param array|null $l    Data.
 * @param string     $size Image size.
 * @return string HTML.
 */
function wd_listing_cover( $id, $l = null, $size = 'wd-card' ) {
	$l = $l ? $l : wd_listing( $id );
	if ( ! empty( $l['photos'][0] ) ) {
		return '<img class="cover-img" src="' . esc_url( wd_img_url( $l['photos'][0], $size ) ) . '" alt="" loading="lazy">';
	}
	$l['tone'] = wd_listing_tone( $id, $l );
	return wd_listing_scene( $l );
}

/**
 * Background colour for a listing without photos. Varies within the sector so cards side by side differ.
 *
 * @param int   $id Listing ID.
 * @param array $l  Data.
 * @return string Tone name.
 */
function wd_listing_tone( $id, $l ) {
	$pal = array(
		'stays'  => array( 'butter', 'mint', 'lavender' ),
		'rides'  => array( 'sky', 'mint', 'butter' ),
		'travel' => array( 'coral', 'lavender', 'sky' ),
		'asoebi' => array( 'lavender', 'coral', 'mint' ),
	);
	$p   = isset( $pal[ $l['sector'] ] ) ? $pal[ $l['sector'] ] : $pal['stays'];
	return $p[ absint( $id ) % count( $p ) ];
}

/**
 * Illustrated cover for listings without photos.
 *
 * @param array $l Listing data (art: house, skyline, car, danfo, plane, globe).
 * @return string SVG.
 */
function wd_listing_scene( $l ) {
	$t   = wd_tones();
	$sec = wd_sectors();
	$s   = isset( $sec[ $l['sector'] ] ) ? $sec[ $l['sector'] ] : $sec['stays'];
	$art = ! empty( $l['art'] ) ? $l['art'] : $s[2];
	$tn  = ! empty( $l['tone'] ) ? $l['tone'] : $s[3];
	$bg  = isset( $t[ $tn ] ) ? $t[ $tn ] : $t['butter'];
	$box = array(
		'skyline' => array( 0, 120, 720, 420 ),
		'danfo'   => array( 90, 170, 540, 252 ),
		'phone'   => array( 270, 70, 180, 270 ),
	);
	$b   = isset( $box[ $art ] ) ? $box[ $art ] : array( 170, 70, 380, 380 );
	$in  = preg_replace( '/^<svg /', sprintf( '<svg x="%d" y="%d" width="%d" height="%d" ', $b[0], $b[1], $b[2], $b[3] ), wd_art( $art ), 1 );
	$ink = $t['ink'];
	$out = '<svg class="l-scene" viewBox="0 0 720 540" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">';
	$out .= '<rect width="720" height="540" fill="' . $bg . '"/>';
	$out .= '<g fill="' . $t['white'] . '" opacity=".45"><circle cx="96" cy="96" r="54"/><circle cx="640" cy="120" r="30"/><circle cx="626" cy="452" r="76"/><circle cx="70" cy="470" r="22"/></g>';
	$out .= '<g fill="' . $ink . '" opacity=".14">';
	for ( $y = 30; $y < 540; $y += 44 ) {
		for ( $x = 22 + ( intdiv( $y, 44 ) % 2 ) * 22; $x < 720; $x += 44 ) {
			$out .= '<circle cx="' . $x . '" cy="' . $y . '" r="2.4"/>';
		}
	}
	$out .= '</g>';
	if ( 'skyline' !== $art ) {
		$out .= '<ellipse cx="360" cy="' . ( $b[1] + $b[3] - 26 ) . '" rx="' . (int) ( $b[2] * 0.42 ) . '" ry="16" fill="' . $ink . '" opacity=".12"/>';
	}
	return $out . $in . '</svg>';
}

/**
 * "₦120,000 / day".
 *
 * @param array $l Listing data.
 * @return string
 */
function wd_listing_price( $l ) {
	if ( wd_listing_asks( $l ) ) {
		return __( 'Contact for price', 'wwmd' );
	}
	$u = wd_units();
	$u = isset( $u[ $l['unit'] ] ) ? $u[ $l['unit'] ][0] : $l['unit'];
	return wd_money( $l['price'] ) . ' / ' . $u;
}

/**
 * What the "how many" box counts for this listing.
 *
 * @param array $l Listing data.
 * @return string
 */
function wd_listing_qty_label( $l ) {
	if ( 'person' === $l['unit'] ) {
		return __( 'People', 'wwmd' );
	}
	if ( 'package' === $l['unit'] ) {
		return __( 'Packages', 'wwmd' );
	}
	if ( 'trip' === $l['unit'] ) {
		return __( 'Trips', 'wwmd' );
	}
	return 'rides' === $l['sector'] ? __( 'Vehicles', 'wwmd' ) : __( 'Apartments or rooms', 'wwmd' );
}

/**
 * Price for dates & quantity.
 *
 * @param array  $l     Listing data.
 * @param string $start Y-m-d.
 * @param string $end   Y-m-d.
 * @param int    $qty   How many.
 * @return array|WP_Error units, qty, amount.
 */
function wd_booking_quote( $l, $start, $end, $qty ) {
	$units = wd_units();
	$u     = isset( $units[ $l['unit'] ] ) ? $units[ $l['unit'] ] : $units['night'];
	$qty   = max( 1, min( 50, (int) $qty ) );
	$a     = preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $start ) ? strtotime( $start . ' 00:00:00 UTC' ) : 0;
	if ( ! $a ) {
		return new WP_Error( 'date', __( 'Pick a date.', 'wwmd' ) );
	}
	if ( $a < strtotime( wp_date( 'Y-m-d' ) . ' 00:00:00 UTC' ) ) {
		return new WP_Error( 'date', __( 'Pick a date from today on.', 'wwmd' ) );
	}
	$n = 1;
	if ( 'range' === $u[2] ) {
		$b = preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $end ) ? strtotime( $end . ' 00:00:00 UTC' ) : $a;
		if ( $b < $a ) {
			return new WP_Error( 'date', __( 'The end date is before the start date.', 'wwmd' ) );
		}
		$days = (int) round( ( $b - $a ) / DAY_IN_SECONDS );
		$n    = 'night' === $l['unit'] ? max( 1, $days ) : $days + 1;
		if ( $n > 60 ) {
			return new WP_Error( 'date', __( 'For more than 60 days, message us on WhatsApp.', 'wwmd' ) );
		}
	}
	return array(
		'units'  => $n,
		'qty'    => $qty,
		'amount' => (int) $l['price'] * $n * $qty,
	);
}

/**
 * One booking row.
 *
 * @param int $id Booking ID.
 * @return object|null
 */
function wd_booking( $id ) {
	global $wpdb;
	return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . wd_t( 'bookings' ) . ' WHERE id = %d', $id ) ); // phpcs:ignore WordPress.DB
}

/**
 * Money a partner can withdraw: released bookings minus payouts already asked for.
 *
 * @param int $uid Partner user ID.
 * @return array released, held, paid_out, balance.
 */
function wd_partner_money( $uid ) {
	global $wpdb;
	$t        = wd_t( 'bookings' );
	$released = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COALESCE(SUM(net),0) FROM $t WHERE partner_id = %d AND status = 'released'", $uid ) ); // phpcs:ignore WordPress.DB
	$held     = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COALESCE(SUM(net),0) FROM $t WHERE partner_id = %d AND status IN ('held','disputed')", $uid ) ); // phpcs:ignore WordPress.DB
	$out      = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COALESCE(SUM(amount),0) FROM ' . wd_t( 'payouts' ) . " WHERE user_id = %d AND wedding_id = 0 AND status <> 'rejected'", $uid ) ); // phpcs:ignore WordPress.DB
	return array(
		'released' => $released,
		'held'     => $held,
		'paid_out' => $out,
		'balance'  => max( 0, $released - $out ),
	);
}

/**
 * Move a booking to a new status (with a note for the record).
 *
 * @param int    $id     Booking ID.
 * @param string $status New status.
 * @param string $note   Note.
 * @return bool
 */
function wd_booking_set( $id, $status, $note = '' ) {
	global $wpdb;
	$b = wd_booking( $id );
	if ( ! $b ) {
		return false;
	}
	$allowed = array(
		'held'     => array( 'released', 'disputed', 'refunded' ),
		'disputed' => array( 'released', 'refunded', 'held' ),
		'pending'  => array( 'cancelled' ),
	);
	if ( ! isset( $allowed[ $b->status ] ) || ! in_array( $status, $allowed[ $b->status ], true ) ) {
		return false;
	}
	$data = array(
		'status' => $status,
		'log'    => trim( $b->log . "\n" . wp_date( 'j M Y H:i' ) . ': ' . $status . ( $note ? ' (' . $note . ')' : '' ) ),
	);
	if ( 'released' === $status ) {
		$data['released_at'] = current_time( 'mysql', true );
	}
	$wpdb->update( wd_t( 'bookings' ), $data, array( 'id' => $id ) );
	do_action( 'wd_booking_' . $status, wd_booking( $id ) );
	return true;
}

/* A paid payment link: the seller's share goes to them straight away (we do not hold money). */
add_action(
	'wd_paid',
	static function ( $tx ) {
		if ( 'booking' !== $tx->kind || 0 !== strpos( $tx->item, 'bk' ) ) {
			return;
		}
		global $wpdb;
		$id  = (int) substr( $tx->item, 2 );
		$now = current_time( 'mysql', true );
		$wpdb->update(
			wd_t( 'bookings' ),
			array(
				'status'      => 'released',
				'reference'   => $tx->reference,
				'test'        => (int) $tx->test,
				'paid_at'     => $now,
				'released_at' => $now,
			),
			array(
				'id'     => $id,
				'status' => 'pending',
			)
		);
		$b = wd_booking( $id );
		if ( ! $b ) {
			return;
		}
		do_action( 'wd_booking_released', $b );
		$partner = get_userdata( (int) $b->partner_id );
		if ( $partner && ! $b->test ) {
			wp_mail(
				$partner->user_email,
				/* translators: %s listing */
				sprintf( __( 'Payment received: %s', 'wwmd' ), get_the_title( $b->listing_id ) ),
				sprintf(
					/* translators: 1 customer, 2 amount, 3 listing, 4 your share, 5 dashboard link */
					__( "%1\$s paid %2\$s for %3\$s.\n\nYour share (%4\$s) goes to your payout account. If you have not set one up yet, it waits in your seller dashboard for you to withdraw: %5\$s", 'wwmd' ),
					$b->name,
					wd_money( $b->amount, $b->currency ),
					get_the_title( $b->listing_id ),
					wd_money( $b->net, $b->currency ),
					wd_account_url( 'partner' )
				)
			);
		}
	}
);

/* Release held money automatically a few days after the booking ends, unless a problem was reported. */
add_action(
	'wd_hourly',
	static function () {
		global $wpdb;
		$days = max( 0, (int) wd_opt( 'release_days' ) );
		$cut  = gmdate( 'Y-m-d', strtotime( wp_date( 'Y-m-d' ) . ' 00:00:00 UTC' ) - $days * DAY_IN_SECONDS );
		$ids  = $wpdb->get_col( $wpdb->prepare( 'SELECT id FROM ' . wd_t( 'bookings' ) . " WHERE status = 'held' AND COALESCE(NULLIF(end_date,''), start_date) <= %s LIMIT 200", $cut ) ); // phpcs:ignore WordPress.DB
		foreach ( $ids as $id ) {
			wd_booking_set( (int) $id, 'released', __( 'released automatically', 'wwmd' ) );
		}
	}
);

/* Book & pay: POST wd/v1/book */
add_action(
	'rest_api_init',
	static function () {
		register_rest_route(
			'wd/v1',
			'/book',
			array(
				'methods'             => 'POST',
				'permission_callback' => 'is_user_logged_in',
				'callback'            => 'wd_rest_book',
			)
		);
	}
);

/**
 * Create a booking & start the payment.
 *
 * @param WP_REST_Request $r Request.
 * @return array|WP_Error
 */
function wd_rest_book( WP_REST_Request $r ) {
	// 1.5.1: nobody books directly. Customers chat with the seller, who sends an invoice or a payment link.
	if ( ! apply_filters( 'wd_direct_booking', false ) ) {
		return new WP_Error( 'chat', __( 'Chat with the seller about your date & needs. They send you an invoice or a payment link.', 'wwmd' ), array( 'status' => 400 ) );
	}
	if ( ! wd_rate_ok( 'book', 12, 600 ) ) {
		return new WP_Error( 'slow', __( 'Too many tries. Please wait a few minutes.', 'wwmd' ), array( 'status' => 429 ) );
	}
	$lid  = (int) $r->get_param( 'listing' );
	$post = get_post( $lid );
	if ( ! $post || 'wd_listing' !== $post->post_type || 'publish' !== $post->post_status ) {
		return new WP_Error( 'gone', __( 'This listing is not available.', 'wwmd' ), array( 'status' => 404 ) );
	}
	$pid = (int) $post->post_author;
	if ( ! wd_is_partner( $pid ) ) {
		return new WP_Error( 'paused', __( 'This seller is not taking bookings right now.', 'wwmd' ), array( 'status' => 400 ) );
	}
	if ( get_current_user_id() === $pid ) {
		return new WP_Error( 'own', __( 'You cannot book your own listing.', 'wwmd' ), array( 'status' => 400 ) );
	}
	$l     = wd_listing( $lid );
	if ( wd_listing_asks( $l ) ) {
		return new WP_Error( 'ask', __( 'This seller gives prices in chat. Send them a message & pay their offer here.', 'wwmd' ), array( 'status' => 400 ) );
	}
	$start = sanitize_text_field( (string) $r->get_param( 'start' ) );
	$end   = sanitize_text_field( (string) $r->get_param( 'end' ) );
	$q     = wd_booking_quote( $l, $start, $end, (int) $r->get_param( 'qty' ) );
	if ( is_wp_error( $q ) ) {
		$q->add_data( array( 'status' => 400 ) );
		return $q;
	}
	$who = wd_booking_who( $r );
	if ( is_wp_error( $who ) ) {
		return $who;
	}
	return wd_booking_create(
		array(
			'listing' => $lid,
			'name'    => $who[0],
			'phone'   => $who[1],
			'start'   => $start,
			'end'     => 'range' === wd_units()[ $l['unit'] ][2] ? ( $end ? $end : $start ) : '',
			'qty'     => $q['qty'],
			'units'   => $q['units'],
			'amount'  => $q['amount'],
			'note'    => mb_substr( sanitize_textarea_field( (string) $r->get_param( 'note' ) ), 0, 500 ),
		)
	);
}

/**
 * The customer's name & phone from a booking request.
 *
 * @param WP_REST_Request $r Request.
 * @return array|WP_Error [name, phone]
 */
function wd_booking_who( WP_REST_Request $r ) {
	$name  = mb_substr( sanitize_text_field( (string) $r->get_param( 'name' ) ), 0, 120 );
	$phone = mb_substr( sanitize_text_field( (string) $r->get_param( 'phone' ) ), 0, 40 );
	if ( mb_strlen( $name ) < 2 || strlen( preg_replace( '/\D/', '', $phone ) ) < 7 ) {
		return new WP_Error( 'who', __( 'Add your name & a phone number the seller can reach.', 'wwmd' ), array( 'status' => 400 ) );
	}
	return array( $name, $phone );
}

/**
 * Save a booking for the signed-in customer & start the payment (a payment link from the chat).
 *
 * @param array $a listing, name, phone, start, end, qty, units, amount, note.
 * @return array|WP_Error
 */
function wd_booking_create( $a ) {
	global $wpdb;
	$u   = wp_get_current_user();
	$lid = (int) $a['listing'];
	$fee = (int) round( $a['amount'] * (float) wd_opt( 'market_fee' ) / 100 );
	$ids = wd_user_weddings( $u->ID );
	$wpdb->insert(
		wd_t( 'bookings' ),
		array(
			'listing_id' => $lid,
			'partner_id' => (int) get_post_field( 'post_author', $lid ),
			'user_id'    => $u->ID,
			'wedding_id' => $ids ? (int) $ids[0] : 0,
			'name'       => $a['name'],
			'email'      => $u->user_email,
			'phone'      => $a['phone'],
			'start_date' => $a['start'],
			'end_date'   => $a['end'],
			'qty'        => (int) $a['qty'],
			'units'      => (int) $a['units'],
			'amount'     => (int) $a['amount'],
			'fee'        => $fee,
			'net'        => (int) $a['amount'] - $fee,
			'currency'   => 'NGN',
			'status'     => 'pending',
			'note'       => $a['note'],
			'created'    => current_time( 'mysql', true ),
		)
	);
	$bid = (int) $wpdb->insert_id;
	if ( ! $bid ) {
		return new WP_Error( 'db', __( 'Something went wrong. Please try again.', 'wwmd' ), array( 'status' => 500 ) );
	}
	if ( ! get_user_meta( $u->ID, 'wd_phone', true ) ) {
		update_user_meta( $u->ID, 'wd_phone', $a['phone'] );
	}
	if ( ! empty( $a['offer'] ) ) {
		// Link the chat offer first: in test mode the payment completes inside wd_pay_start().
		$wpdb->update( wd_t( 'chats' ), array( 'booking_id' => $bid ), array( 'id' => (int) $a['offer'] ) );
	}
	$res = wd_pay_start(
		array(
			'wedding_id' => 0,
			'kind'       => 'booking',
			'item'       => 'bk' . $bid,
			'amount'     => (int) $a['amount'],
			'name'       => $a['name'],
			'email'      => $u->user_email,
			'message'    => mb_substr( get_the_title( $lid ), 0, 200 ),
			'currency'   => 'NGN',
			'return'     => add_query_arg( 'booked', $bid, get_permalink( $lid ) ),
		)
	);
	if ( is_wp_error( $res ) ) {
		wd_booking_set( $bid, 'cancelled', 'payment did not start' );
		$res->add_data( array( 'status' => 400 ) );
		return $res;
	}
	$wpdb->update( wd_t( 'bookings' ), array( 'reference' => $res['reference'] ), array( 'id' => $bid ) );
	return array(
		'ok'       => true,
		'booking'  => $bid,
		'test'     => ! empty( $res['test'] ),
		'url'      => isset( $res['url'] ) ? $res['url'] : '',
		'next'     => wd_account_url( 'services' ),
		'message'  => __( 'Paid! The seller can see it in their dashboard.', 'wwmd' ),
	);
}

/* Front-end forms for partners, customers & HQ: admin-post.php?action=wd_market */
add_action( 'admin_post_wd_market', 'wd_market_save' );
add_action(
	'admin_post_nopriv_wd_market',
	static function () {
		wp_safe_redirect( wd_url( 'sign-in' ) );
		exit;
	}
);

/**
 * Opening tag & hidden fields for a marketplace form.
 *
 * @param string $op       Operation.
 * @param array  $hidden   Extra hidden fields.
 * @param bool   $multi    File uploads.
 * @param string $class    Form class.
 */
function wd_market_form( $op, $hidden = array(), $multi = false, $class = 'form panel' ) {
	printf( '<form class="%s" method="post" action="%s"%s>', esc_attr( $class ), esc_url( admin_url( 'admin-post.php' ) ), $multi ? ' enctype="multipart/form-data"' : '' );
	echo '<input type="hidden" name="action" value="wd_market"><input type="hidden" name="op" value="' . esc_attr( $op ) . '">';
	foreach ( $hidden as $k => $v ) {
		echo '<input type="hidden" name="' . esc_attr( $k ) . '" value="' . esc_attr( $v ) . '">';
	}
	wp_nonce_field( 'wd_market_' . $op );
}

/**
 * Handle marketplace forms.
 */
function wd_market_save() {
	$p  = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification
	$op = sanitize_key( $p['op'] ?? '' );
	if ( ! wp_verify_nonce( $p['_wpnonce'] ?? '', 'wd_market_' . $op ) ) {
		wd_back( 'partner', 'denied' );
	}
	$uid = get_current_user_id();
	$hq  = 0 === strpos( $op, 'hq_' );
	if ( $hq && ! current_user_can( 'manage_options' ) ) {
		wd_back( '', 'denied' );
	}
	$back_hq = static function ( $note = 'done' ) {
		wp_safe_redirect( add_query_arg( array( 'tab' => 'market', 'wdn' => $note ), wd_url( 'hq' ) ) );
		exit;
	};
	switch ( $op ) {
		case 'apply':
			$old = wd_partner( $uid );
			$new = array(
				'status'   => ( $old && 'approved' === $old['status'] ) ? 'approved' : 'pending',
				'business' => mb_substr( sanitize_text_field( $p['business'] ?? '' ), 0, 80 ),
				'sector'   => array_key_exists( $p['sector'] ?? '', wd_sectors() ) ? $p['sector'] : 'stays',
				'city'     => mb_substr( sanitize_text_field( $p['city'] ?? '' ), 0, 60 ),
				'country'  => mb_substr( sanitize_text_field( $p['country'] ?? '' ), 0, 60 ),
				'phone'    => mb_substr( sanitize_text_field( $p['phone'] ?? '' ), 0, 40 ),
				'about'    => mb_substr( sanitize_textarea_field( $p['about'] ?? '' ), 0, 800 ),
				'website'  => esc_url_raw( $p['website'] ?? '', array( 'http', 'https' ) ),
				'applied'  => $old ? $old['applied'] : current_time( 'mysql', true ),
				'approved' => $old ? $old['approved'] : '',
			);
			if ( ! $new['business'] || ! $new['phone'] || ! $new['city'] ) {
				wd_back( 'partner', 'partner_missing' );
			}
			wd_partner_put( $uid, array_merge( $old ? $old : array(), $new ) );
			if ( ! $old ) {
				// HQ approves every partner by hand.
				wp_mail(
					get_option( 'admin_email' ),
					/* translators: %s business */
					sprintf( __( 'New seller application: %s', 'wwmd' ), $new['business'] ),
					sprintf(
						/* translators: 1 business 2 sector 3 city 4 phone 5 about 6 link */
						__( "%1\$s applied to become a seller.\n\nOffers: %2\$s\nCity: %3\$s\nWhatsApp: %4\$s\n\n%5\$s\n\nApprove or decline in HQ › Market: %6\$s", 'wwmd' ),
						$new['business'],
						wd_sectors()[ $new['sector'] ][0],
						trim( $new['city'] . ', ' . $new['country'], ', ' ),
						$new['phone'],
						$new['about'],
						add_query_arg( 'tab', 'market', wd_url( 'hq' ) )
					)
				);
			}
			wd_back( 'partner', $old ? 'saved' : 'partner_applied' );
			break;

		case 'profile':
			if ( ! wd_is_partner( $uid ) ) {
				wd_back( 'partner', 'denied' );
			}
			$pp = wd_partner( $uid );
			foreach ( array( 'business' => 80, 'city' => 60, 'country' => 60, 'phone' => 40 ) as $k => $max ) {
				$v = isset( $p[ $k ] ) ? mb_substr( sanitize_text_field( $p[ $k ] ), 0, $max ) : '';
				if ( '' !== $v ) {
					$pp[ $k ] = $v;
				}
			}
			if ( isset( $p['website'] ) ) {
				$pp['website'] = esc_url_raw( $p['website'], array( 'http', 'https' ) );
			}
			$pp['hours'] = mb_substr( sanitize_text_field( $p['hours'] ?? '' ), 0, 120 );
			$pp['areas'] = mb_substr( sanitize_text_field( $p['areas'] ?? '' ), 0, 160 );
			$pp['about'] = mb_substr( sanitize_textarea_field( $p['about'] ?? $pp['about'] ), 0, 800 );
			foreach ( array( 'logo', 'cover' ) as $k ) {
				if ( ! empty( $p[ 'remove_' . $k ] ) ) {
					$pp[ $k ] = 0;
				}
				$att = wd_upload_field( $k . '_file', 0 );
				if ( $att ) {
					$pp[ $k ] = $att;
				}
			}
			$keep = array();
			foreach ( array_values( (array) $pp['portfolio'] ) as $i => $ph ) {
				if ( empty( $p['remove_photo'][ $i ] ) ) {
					$ph['cap'] = mb_substr( sanitize_text_field( $p['cap'][ $i ] ?? ( $ph['cap'] ?? '' ) ), 0, 80 );
					$keep[]    = $ph;
				}
			}
			$att = count( $keep ) < 12 ? wd_upload_field( 'photo_file', 0 ) : 0;
			if ( $att ) {
				$keep[] = array(
					'img' => $att,
					'cap' => mb_substr( sanitize_text_field( $p['new_cap'] ?? '' ), 0, 80 ),
				);
			}
			$pp['portfolio'] = $keep;
			wd_partner_put( $uid, $pp );
			wd_back( 'partner', 'saved', array( 'part' => 'profile' ) );
			break;

		case 'review':
			global $wpdb;
			$b = wd_booking( (int) ( $p['bid'] ?? 0 ) );
			if ( ! $b || (int) $b->user_id !== $uid || ! in_array( $b->status, array( 'released', 'direct' ), true ) ) {
				wd_back( 'services', 'denied' );
			}
			if ( $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . wd_t( 'previews' ) . ' WHERE booking_id = %d', $b->id ) ) ) { // phpcs:ignore WordPress.DB
				wd_back( 'services', 'review_sent' );
			}
			$text = mb_substr( sanitize_textarea_field( $p['text'] ?? '' ), 0, 600 );
			if ( mb_strlen( trim( $text ) ) < 10 ) {
				wd_back( 'services', 'review_short' );
			}
			$wpdb->insert(
				wd_t( 'previews' ),
				array(
					'partner_id' => (int) $b->partner_id,
					'listing_id' => (int) $b->listing_id,
					'booking_id' => (int) $b->id,
					'user_id'    => $uid,
					'name'       => mb_substr( wp_get_current_user()->display_name, 0, 120 ),
					'rating'     => max( 1, min( 5, (int) ( $p['rating'] ?? 5 ) ) ),
					'text'       => $text,
					'status'     => 'pending',
					'created'    => current_time( 'mysql', true ),
				)
			);
			wd_back( 'services', 'review_sent' );
			break;

		case 'review_reply':
			global $wpdb;
			$rv = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . wd_t( 'previews' ) . ' WHERE id = %d', (int) ( $p['rid'] ?? 0 ) ) ); // phpcs:ignore WordPress.DB
			if ( ! $rv || (int) $rv->partner_id !== $uid || '' !== (string) $rv->reply ) {
				wd_back( 'partner', 'denied' );
			}
			$reply = mb_substr( sanitize_textarea_field( $p['reply'] ?? '' ), 0, 400 );
			if ( '' !== trim( $reply ) ) {
				$wpdb->update( wd_t( 'previews' ), array( 'reply' => $reply ), array( 'id' => (int) $rv->id ) );
			}
			wd_back( 'partner', 'saved', array( 'part' => 'profile' ) );
			break;

		case 'listing':
			if ( ! wd_is_partner( $uid ) ) {
				wd_back( 'partner', 'denied' );
			}
			$lid = (int) ( $p['lid'] ?? 0 );
			if ( $lid && ( 'wd_listing' !== get_post_type( $lid ) || (int) get_post_field( 'post_author', $lid ) !== $uid ) ) {
				wd_back( 'partner', 'denied' );
			}
			$title = mb_substr( sanitize_text_field( $p['title'] ?? '' ), 0, 100 );
			$ask   = 'ask' === ( $p['price_mode'] ?? '' );
			if ( ! $title || ( ! $ask && (int) preg_replace( '/\D/', '', (string) ( $p['price'] ?? '' ) ) < 1 ) ) {
				wd_back( 'partner', 'listing_missing', array( 'part' => 'listing', 'lid' => $lid ) );
			}
			if ( ! $lid ) {
				$lid = wp_insert_post(
					array(
						'post_type'   => 'wd_listing',
						'post_status' => 'pending',
						'post_title'  => $title,
						'post_author' => $uid,
					)
				);
			} else {
				wp_update_post(
					array(
						'ID'         => $lid,
						'post_title' => $title,
					)
				);
			}
			$l             = wd_listing( $lid );
			$l['sector']   = array_key_exists( $p['sector'] ?? '', wd_sectors() ) ? $p['sector'] : $l['sector'];
			$l['unit']     = array_key_exists( $p['unit'] ?? '', wd_units() ) ? $p['unit'] : $l['unit'];
			$l['city']     = mb_substr( sanitize_text_field( $p['city'] ?? '' ), 0, 60 );
			$l['country']  = mb_substr( sanitize_text_field( $p['country'] ?? '' ), 0, 60 );
			$l['price_mode'] = $ask ? 'ask' : 'fixed';
			$l['price']      = (int) preg_replace( '/\D/', '', (string) ( $p['price'] ?? '' ) );
			$l['capacity'] = max( 0, min( 500, (int) ( $p['capacity'] ?? 0 ) ) );
			$l['about']    = mb_substr( sanitize_textarea_field( $p['about'] ?? '' ), 0, 2000 );
			$l['features'] = array_slice( array_values( array_filter( array_map( 'sanitize_text_field', preg_split( '/\r\n|\r|\n/', (string) ( $p['features'] ?? '' ) ) ) ) ), 0, 12 );
			if ( ! empty( $p['remove_photos'] ) ) {
				$l['photos'] = array();
			}
			$photo = function_exists( 'wd_upload_field' ) ? wd_upload_field( 'photo_file', $lid ) : 0;
			if ( $photo ) {
				$l['photos'] = array_slice( array_merge( array( $photo ), (array) $l['photos'] ), 0, 6 );
				set_post_thumbnail( $lid, $photo );
			}
			wd_listing_put( $lid, $l );
			wd_back( 'partner', 'publish' === get_post_status( $lid ) ? 'saved' : 'listing_pending' );
			break;

		case 'listing_pause':
			$lid = (int) ( $p['lid'] ?? 0 );
			if ( 'wd_listing' !== get_post_type( $lid ) || (int) get_post_field( 'post_author', $lid ) !== $uid ) {
				wd_back( 'partner', 'denied' );
			}
			$st = get_post_status( $lid );
			if ( in_array( $st, array( 'publish', 'draft' ), true ) ) {
				wp_update_post(
					array(
						'ID'          => $lid,
						'post_status' => 'publish' === $st ? 'draft' : 'publish',
					)
				);
			}
			wd_back( 'partner', 'saved' );
			break;

		case 'payout_setup':
			if ( ! wd_is_partner( $uid ) ) {
				wd_back( 'partner', 'denied' );
			}
			$go = wd_seller_onboard_url( $uid );
			if ( is_wp_error( $go ) ) {
				wd_back( 'partner', 'payout_setup' );
			}
			wp_redirect( $go ); // phpcs:ignore WordPress.Security.SafeRedirect -- the payment provider's own page.
			exit;

		case 'payout':
			if ( ! wd_is_partner( $uid ) ) {
				wd_back( 'partner', 'denied' );
			}
			$amt = (int) preg_replace( '/\D/', '', (string) ( $p['amount'] ?? '' ) );
			$m   = wd_partner_money( $uid );
			if ( $amt < max( 1, (int) wd_opt( 'payout_min' ) ) || $amt > $m['balance'] ) {
				wd_back( 'partner', 'payout_low' );
			}
			global $wpdb;
			$wpdb->insert(
				wd_t( 'payouts' ),
				array(
					'user_id'      => $uid,
					'wedding_id'   => 0,
					'amount'       => $amt,
					'currency'     => 'NGN',
					'bank'         => mb_substr( sanitize_text_field( $p['bank'] ?? '' ), 0, 120 ),
					'account_no'   => mb_substr( preg_replace( '/\D/', '', (string) ( $p['account_no'] ?? '' ) ), 0, 40 ),
					'account_name' => mb_substr( sanitize_text_field( $p['account_name'] ?? '' ), 0, 120 ),
					'status'       => 'requested',
					'note'         => 'partner',
					'created'      => current_time( 'mysql', true ),
				)
			);
			update_user_meta(
				$uid,
				'wd_bank',
				array(
					'bank'         => mb_substr( sanitize_text_field( $p['bank'] ?? '' ), 0, 120 ),
					'account_no'   => mb_substr( preg_replace( '/\D/', '', (string) ( $p['account_no'] ?? '' ) ), 0, 40 ),
					'account_name' => mb_substr( sanitize_text_field( $p['account_name'] ?? '' ), 0, 120 ),
				)
			);
			wd_back( 'partner', 'payout' );
			break;

		case 'received':
		case 'problem':
			$b = wd_booking( (int) ( $p['bid'] ?? 0 ) );
			if ( ! $b || (int) $b->user_id !== $uid ) {
				wd_back( 'services', 'denied' );
			}
			if ( 'received' === $op ) {
				wd_booking_set( (int) $b->id, 'released', __( 'customer confirmed the service', 'wwmd' ) );
				wd_back( 'services', 'released' );
			}
			wd_booking_set( (int) $b->id, 'disputed', mb_substr( sanitize_text_field( $p['why'] ?? '' ), 0, 200 ) );
			wd_back( 'services', 'reported' );
			break;

		case 'hq_partner':
			$who = (int) ( $p['uid'] ?? 0 );
			$pp  = wd_partner( $who );
			$to  = sanitize_key( $p['to'] ?? '' );
			if ( $pp && in_array( $to, array( 'approved', 'rejected', 'paused' ), true ) ) {
				$pp['status'] = $to;
				if ( 'approved' === $to && ! $pp['approved'] ) {
					$pp['approved'] = current_time( 'mysql', true );
				}
				wd_partner_put( $who, $pp );
				if ( 'approved' === $to ) {
					do_action( 'wd_partner_approved', $who );
				}
			}
			$back_hq();
			break;

		case 'hq_partner_verify':
			$who = (int) ( $p['uid'] ?? 0 );
			$pp  = wd_partner( $who );
			if ( $pp ) {
				$pp['verified'] = empty( $p['on'] ) ? 0 : 1;
				wd_partner_put( $who, $pp );
			}
			$back_hq();
			break;

		case 'hq_partner_premium':
			$who = (int) ( $p['uid'] ?? 0 );
			$pp  = wd_partner( $who );
			if ( $pp ) {
				$pp['premium'] = empty( $p['on'] ) ? 0 : 1;
				wd_partner_put( $who, $pp );
			}
			$back_hq();
			break;

		case 'invoice_look':
			$pp = wd_partner( $uid );
			if ( ! $pp || ! wd_seller_premium( $pp ) ) {
				wd_back( 'partner', 'denied' );
			}
			$b            = wd_seller_look( $pp );
			$b['primary'] = wd_hex( $p['primary'] ?? '', $b['primary'] );
			$b['accent']  = wd_hex( $p['accent'] ?? '', $b['accent'] );
			$f            = sanitize_key( $p['font'] ?? '' );
			$b['font']    = isset( wd_fonts()[ $f ] ) ? $f : $b['font'];
			if ( ! empty( $p['remove_inv_logo'] ) ) {
				$b['logo'] = 0;
			}
			$att = wd_upload_field( 'inv_logo_file', 0 );
			if ( $att ) {
				$b['logo'] = $att;
			}
			if ( ! empty( $p['reset'] ) ) {
				$b = array();
			}
			$pp['brand'] = $b;
			wd_partner_put( $uid, $pp );
			wp_safe_redirect( wd_account_url( 'partner', array( 'wdn' => 'saved' ) ) . '#invoice-look' );
			exit;

		case 'hq_listing':
			$lid = (int) ( $p['lid'] ?? 0 );
			$to  = sanitize_key( $p['to'] ?? '' );
			if ( 'wd_listing' === get_post_type( $lid ) && in_array( $to, array( 'publish', 'draft', 'trash' ), true ) ) {
				if ( 'trash' === $to ) {
					wp_trash_post( $lid );
				} else {
					wp_update_post(
						array(
							'ID'          => $lid,
							'post_status' => $to,
						)
					);
				}
			}
			$back_hq();
			break;

		case 'hq_review':
			global $wpdb;
			$to = sanitize_key( $p['to'] ?? '' );
			if ( in_array( $to, array( 'approved', 'hidden' ), true ) ) {
				$wpdb->update( wd_t( 'previews' ), array( 'status' => $to ), array( 'id' => (int) ( $p['rid'] ?? 0 ) ) );
			}
			$back_hq();
			break;

		case 'hq_booking':
			$to = sanitize_key( $p['to'] ?? '' );
			if ( in_array( $to, array( 'released', 'refunded', 'held' ), true ) ) {
				wd_booking_set( (int) ( $p['bid'] ?? 0 ), $to, __( 'by HQ', 'wwmd' ) );
			}
			$back_hq();
			break;

		case 'hq_payout':
			global $wpdb;
			$to = sanitize_key( $p['to'] ?? '' );
			if ( in_array( $to, array( 'paid', 'rejected' ), true ) ) {
				$wpdb->update(
					wd_t( 'payouts' ),
					array(
						'status'  => $to,
						'paid_at' => 'paid' === $to ? current_time( 'mysql', true ) : null,
					),
					array( 'id' => (int) ( $p['pid'] ?? 0 ) )
				);
			}
			$back_hq();
			break;
	}
	wd_back( 'partner', 'denied' );
}

/**
 * Status pill for a booking.
 *
 * @param string $status Status.
 * @return string HTML.
 */
function wd_booking_pill( $status ) {
	$s = wd_booking_statuses();
	$s = isset( $s[ $status ] ) ? $s[ $status ] : array( $status, 'butter' );
	return '<span class="pill tone-' . esc_attr( $s[1] ) . '">' . esc_html( $s[0] ) . '</span>';
}

/**
 * Dates for a booking, for people.
 *
 * @param object $b Booking.
 * @return string
 */
function wd_booking_when( $b ) {
	$s = wd_date( $b->start_date, 'D j M Y' );
	if ( $b->end_date && $b->end_date !== $b->start_date ) {
		$s = wd_date( $b->start_date, 'j M' ) . ' – ' . wd_date( $b->end_date, 'j M Y' );
	}
	return $s;
}
