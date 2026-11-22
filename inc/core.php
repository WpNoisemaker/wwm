<?php
/**
 * Core: settings, content types, tables, assets, menus, pages & small helpers.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Settings (edited in HQ → Settings). Prices are in Naira unless stated.
 * ---------------------------------------------------------------------- */

/**
 * Every feature a wedding page can have, with the label people see.
 *
 * @return array
 */
function wd_features() {
	return array(
		'invitation' => __( 'Invitation & event details', 'wwmd' ),
		'story'      => __( 'Our love story', 'wwmd' ),
		'rsvp'       => __( 'RSVP', 'wwmd' ),
		'party'      => __( 'Bridesmaids & groomsmen line-up', 'wwmd' ),
		'instagram'  => __( 'Instagram feed', 'wwmd' ),
		'quiz'       => __( 'Guest quiz & leaderboard', 'wwmd' ),
		'vendors'    => __( 'Vendor list & guest reviews', 'wwmd' ),
		'wall'       => __( 'Wall of love', 'wwmd' ),
		'gallery'    => __( 'Guest photo gallery', 'wwmd' ),
		'registry'   => __( 'Cash & gift registry', 'wwmd' ),
		'entry_pass' => __( 'RSVP with entry pass & check-in', 'wwmd' ),
		'livestream' => __( 'Livestream', 'wwmd' ),
		'spray'      => __( 'Spray money', 'wwmd' ),
		'design'     => __( 'Your own colours & fonts', 'wwmd' ),
		'templates'  => __( '10 Premium designs & your own layout', 'wwmd' ),
		'table_qr'   => __( 'Printed table QR cards, shipped to you', 'wwmd' ),
		'no_ads'     => __( 'No ads on your page', 'wwmd' ),
		'no_fees'    => __( 'No transaction charges', 'wwmd' ),
		'receipts'   => __( 'Your names, colours & logo on guests’ gift receipts', 'wwmd' ),
	);
}

/**
 * Default settings.
 *
 * @return array
 */
function wd_defaults() {
	return array(
		'whatsapp'          => '2349078987201',
		'email'             => 'more@weddingwebsites.io',
		'pro_price'         => 198000,
		'premium_price'     => 448000,
		'premium_qr_cards'  => 200,
		'instalments'       => 3,
		'extra_qr_price'    => 30000,
		'extra_qr_qty'      => 50,
		'spotlight_price'   => 50000,
		'vendor_pro_price'  => 10000,
		'seller_premium_price' => 0,
		'ad_week_price'     => 15000,
		'custom_design_price' => 150000,
		'market_fee'        => 10,
		'release_days'      => 3,
		'ai_on'             => 1,
		'ai_key'            => '',
		'ai_model'          => 'claude-opus-5-5',
		'fee_gift'          => 4,
		'fee_spray'         => 5,
		'fee_cap'           => 0,
		'payout_min'        => 5000,
		'features_free'     => array( 'invitation', 'story', 'rsvp', 'vendors', 'wall', 'registry' ),
		'features_pro'      => array( 'invitation', 'story', 'rsvp', 'party', 'instagram', 'quiz', 'vendors', 'wall', 'gallery', 'registry', 'entry_pass', 'design', 'no_ads' ),
		'features_premium'  => array_keys( wd_features() ),
		'features_society'  => array_keys( wd_features() ),
		'coins'             => wp_list_pluck( wd_coin_figures(), 'coins' ),
		'weights'           => array(
			'views'       => 1.0,
			'instagram'   => 1.0,
			'reviews'     => 1.5,
			'spray'       => 1.2,
			'photo_likes' => 1.0,
			'wall'        => 1.0,
			'wall_likes'  => 0.8,
		),
		'rates'             => array(
			'NGN' => 1,
			'GHS' => 125,
			'KES' => 12,
			'ZAR' => 85,
			'USD' => 1500,
		),
		'bachs_secret'      => '',
		'bachs_webhook_secret' => '',
		'test_payments'     => 1,
		'ig_app_id'         => '',
		'ig_app_secret'     => '',
		'preloader'         => 1,
		'home_fx'           => 1,
		'auth_title'        => 'Your wedding, on one beautiful page',
		'auth_text'         => 'One page for every part of your wedding, from the traditional & nikkah to the white wedding. Share your love story, collect RSVPs with entry passes, receive cash gifts & spraying through your cash & gift registry, stream the day live & gather guests\' photos & love notes. Start free, or go Pro, Premium or High Society for your own design & a higher place on the Buzz Chart.',
		'moderate_photos'   => 0,
		'moderate_wall'     => 0,
		'moderate_talk'     => 0,
		'news_on'           => 1,
		'news_feeds'        => "https://www.bellanaijaweddings.com/feed/\nhttps://www.loveweddingsng.com/feed/",
		'news_import'       => 1,
		'news_import_feeds' => "https://www.bellanaijaweddings.com/feed/\nhttps://www.brides.com/rss\nhttps://www.weddingwire.com/wedding-ideas/rss\nhttps://www.loveweddingsng.com/feed/\nhttps://www.africanweddingtraditions.com/feed/\nhttps://www.nigerianwedding.com/feed\nhttps://www.weddingdigestnaija.com/feed/",
		'ads'               => array(),
	);
}

/**
 * All settings, merged with defaults.
 *
 * @param bool $fresh Read again from the database.
 * @return array
 */
function wd_settings( $fresh = false ) {
	static $s = null;
	if ( null === $s || $fresh ) {
		$saved = get_option( 'wd_settings', array() );
		// Other files add their own keys through this filter. Saved values always win, so an
		// upgrade never overwrites what a live site already holds.
		$def   = apply_filters( 'wd_defaults', wd_defaults() );
		$s     = wp_parse_args( is_array( $saved ) ? $saved : array(), $def );
		$s['weights'] = wp_parse_args( (array) $s['weights'], $def['weights'] );
		$s['coins']   = wp_parse_args( (array) $s['coins'], $def['coins'] );
		$s['rates']   = wp_parse_args( (array) $s['rates'], $def['rates'] );
	}
	return $s;
}

/**
 * One setting.
 *
 * @param string $key Key.
 * @return mixed
 */
function wd_opt( $key ) {
	$s = wd_settings();
	return isset( $s[ $key ] ) ? $s[ $key ] : null;
}

/* -------------------------------------------------------------------------
 * Theme set-up.
 * ---------------------------------------------------------------------- */

add_action(
	'after_setup_theme',
	static function () {
		load_theme_textdomain( 'wwmd', WD_DIR . '/languages' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support( 'custom-logo' );
		add_image_size( 'wd-card', 720, 540, array( 'center', 'top' ) );
		add_image_size( 'wd-cover', 1800, 1100, array( 'center', 'top' ) );
		add_image_size( 'wd-square', 600, 600, array( 'center', 'top' ) );
		register_nav_menus(
			array(
				'primary' => __( 'Header menu', 'wwmd' ),
				'footer'  => __( 'Footer menu', 'wwmd' ),
				'legal'   => __( 'Footer bottom links', 'wwmd' ),
				'tabbar'  => __( 'Phone tab bar', 'wwmd' ),
			)
		);
	}
);

add_action(
	'init',
	static function () {
		register_post_type(
			'wd_wedding',
			array(
				'labels'       => array(
					'name'          => __( 'Weddings', 'wwmd' ),
					'singular_name' => __( 'Wedding', 'wwmd' ),
				),
				'public'       => true,
				'has_archive'  => 'weddings',
				'rewrite'      => array( 'slug' => 'weddings', 'with_front' => false ),
				'supports'     => array( 'title', 'author', 'thumbnail', 'excerpt' ),
				'menu_icon'    => 'dashicons-heart',
				'show_in_rest' => false,
			)
		);
		register_post_type(
			'wd_vendor',
			array(
				'labels'       => array(
					'name'          => __( 'Vendors', 'wwmd' ),
					'singular_name' => __( 'Vendor', 'wwmd' ),
				),
				'public'       => true,
				'has_archive'  => 'vendors',
				'rewrite'      => array( 'slug' => 'vendors', 'with_front' => false ),
				'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author' ),
				'menu_icon'    => 'dashicons-store',
				'show_in_rest' => false,
			)
		);
		register_taxonomy(
			'wd_style',
			array( 'wd_wedding' ),
			array(
				'labels'            => array(
					'name'          => __( 'Wedding styles', 'wwmd' ),
					'singular_name' => __( 'Wedding style', 'wwmd' ),
				),
				'public'            => true,
				'hierarchical'      => true,
				'rewrite'           => array( 'slug' => 'wedding-style', 'with_front' => false ),
				'show_admin_column' => true,
			)
		);
		register_taxonomy(
			'wd_place',
			array( 'wd_wedding', 'wd_vendor' ),
			array(
				'labels'            => array(
					'name'          => __( 'Places', 'wwmd' ),
					'singular_name' => __( 'Place', 'wwmd' ),
				),
				'public'            => true,
				'hierarchical'      => true,
				'rewrite'           => array( 'slug' => 'weddings-in', 'with_front' => false ),
				'show_admin_column' => true,
			)
		);
		register_taxonomy(
			'wd_vtype',
			array( 'wd_vendor' ),
			array(
				'labels'            => array(
					'name'          => __( 'Vendor types', 'wwmd' ),
					'singular_name' => __( 'Vendor type', 'wwmd' ),
				),
				'public'            => true,
				'hierarchical'      => true,
				'rewrite'           => array( 'slug' => 'vendor-type', 'with_front' => false ),
				'show_admin_column' => true,
			)
		);
		add_rewrite_rule( '^pass/([A-Za-z0-9]+)/?$', 'index.php?wd_pass=$matches[1]', 'top' );
		add_rewrite_rule( '^table-cards/([0-9]+)/?$', 'index.php?wd_cards=$matches[1]', 'top' );
	}
);

add_filter(
	'query_vars',
	static function ( $vars ) {
		$vars[] = 'wd_pass';
		$vars[] = 'wd_cards';
		return $vars;
	}
);

/* Archive pages show 12 cards; the explore page reads its own filters. */
add_action(
	'pre_get_posts',
	static function ( $q ) {
		if ( is_admin() || ! $q->is_main_query() ) {
			return;
		}
		if ( $q->is_post_type_archive( 'wd_wedding' ) || $q->is_tax( array( 'wd_style', 'wd_place' ) ) ) {
			if ( $q->is_tax( 'wd_place' ) ) {
				$q->set( 'post_type', 'wd_wedding' );
			}
			wd_apply_wedding_filters( $q );
		}
		if ( $q->is_post_type_archive( 'wd_vendor' ) || $q->is_tax( 'wd_vtype' ) ) {
			$q->set( 'posts_per_page', 12 );
			$q->set( 'meta_key', '_wd_rating_score' );
			$q->set( 'orderby', array( 'meta_value_num' => 'DESC', 'title' => 'ASC' ) );
			if ( ! empty( $_GET['city'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$q->set( 'tax_query', array( array( 'taxonomy' => 'wd_place', 'field' => 'slug', 'terms' => sanitize_title( wp_unslash( $_GET['city'] ) ) ) ) ); // phpcs:ignore
			}
			if ( ! empty( $_GET['q'] ) ) { // phpcs:ignore
				$q->set( 's', sanitize_text_field( wp_unslash( $_GET['q'] ) ) ); // phpcs:ignore
			}
		}
	}
);

/**
 * Explore filters & sorting (shared by the archive & the load-more requests).
 *
 * @param WP_Query $q Query.
 */
function wd_apply_wedding_filters( $q ) {
	// phpcs:disable WordPress.Security.NonceVerification
	$g    = wp_unslash( $_GET );
	$sort = isset( $g['sort'] ) ? sanitize_key( $g['sort'] ) : 'trending';
	$q->set( 'posts_per_page', 12 );
	$meta = array();
	$tax  = array();
	if ( ! empty( $g['plan'] ) && in_array( $g['plan'], wd_plan_keys(), true ) ) {
		$meta[] = array( 'key' => '_wd_plan', 'value' => $g['plan'] );
	}
	if ( ! empty( $g['style'] ) ) {
		$tax[] = array( 'taxonomy' => 'wd_style', 'field' => 'slug', 'terms' => sanitize_title( $g['style'] ) );
	}
	if ( ! empty( $g['place'] ) ) {
		$tax[] = array( 'taxonomy' => 'wd_place', 'field' => 'slug', 'terms' => sanitize_title( $g['place'] ) );
	}
	if ( ! empty( $g['program'] ) && array_key_exists( $g['program'], wd_program_types() ) ) {
		$meta[] = array( 'key' => '_wd_prog', 'value' => ',' . sanitize_key( $g['program'] ) . ',', 'compare' => 'LIKE' );
	}
	$today = wp_date( 'Y-m-d' );
	if ( ! empty( $g['when'] ) ) {
		if ( 'upcoming' === $g['when'] ) {
			$meta[] = array( 'key' => '_wd_date', 'value' => $today, 'compare' => '>=', 'type' => 'DATE' );
		} elseif ( 'past' === $g['when'] ) {
			$meta[] = array( 'key' => '_wd_date', 'value' => $today, 'compare' => '<', 'type' => 'DATE' );
		} elseif ( 'month' === $g['when'] ) {
			$meta[] = array( 'key' => '_wd_date', 'value' => array( wp_date( 'Y-m-01' ), wp_date( 'Y-m-t' ) ), 'compare' => 'BETWEEN', 'type' => 'DATE' );
		}
	}
	if ( ! empty( $g['q'] ) ) {
		$q->set( 's', sanitize_text_field( $g['q'] ) );
	}
	switch ( $sort ) {
		case 'new':
			$q->set( 'orderby', 'date' );
			$q->set( 'order', 'DESC' );
			break;
		case 'top':
			$q->set( 'meta_key', '_wd_score' );
			$q->set( 'orderby', 'meta_value_num' );
			$q->set( 'order', 'DESC' );
			break;
		case 'soon':
			$meta[] = array( 'key' => '_wd_date', 'value' => $today, 'compare' => '>=', 'type' => 'DATE' );
			$q->set( 'meta_key', '_wd_date' );
			$q->set( 'orderby', 'meta_value' );
			$q->set( 'order', 'ASC' );
			break;
		default:
			$q->set( 'meta_key', '_wd_score_week' );
			$q->set( 'orderby', 'meta_value_num' );
			$q->set( 'order', 'DESC' );
	}
	if ( $meta ) {
		$q->set( 'meta_query', array_merge( array( 'relation' => 'AND' ), $meta ) );
	}
	if ( $tax ) {
		$existing = $q->get( 'tax_query' );
		$q->set( 'tax_query', array_merge( is_array( $existing ) ? $existing : array(), $tax ) );
	}
	// phpcs:enable
}

/* -------------------------------------------------------------------------
 * Roles, tables & first-run set-up.
 * ---------------------------------------------------------------------- */

/**
 * Create or update the custom tables.
 */
function wd_install_tables() {
	global $wpdb;
	$c = $wpdb->get_charset_collate();
	$p = $wpdb->prefix . 'wd_';
	$sql = array(
		"CREATE TABLE IF NOT EXISTS {$p}rsvps ( id bigint(20) unsigned NOT NULL AUTO_INCREMENT, wedding_id bigint(20) unsigned NOT NULL, name varchar(120) NOT NULL DEFAULT '', phone varchar(40) NOT NULL DEFAULT '', email varchar(120) NOT NULL DEFAULT '', attending varchar(10) NOT NULL DEFAULT 'yes', guests int(11) NOT NULL DEFAULT 1, events text, note text, side varchar(20) NOT NULL DEFAULT '', pass_code varchar(16) NOT NULL DEFAULT '', table_no varchar(20) NOT NULL DEFAULT '', checked_in datetime DEFAULT NULL, created datetime NOT NULL, PRIMARY KEY  (id), KEY wedding_id (wedding_id), KEY pass_code (pass_code) ) $c",
		"CREATE TABLE IF NOT EXISTS {$p}wall ( id bigint(20) unsigned NOT NULL AUTO_INCREMENT, wedding_id bigint(20) unsigned NOT NULL, name varchar(120) NOT NULL DEFAULT '', message text, likes int(11) NOT NULL DEFAULT 0, status varchar(12) NOT NULL DEFAULT 'approved', created datetime NOT NULL, PRIMARY KEY  (id), KEY wedding_id (wedding_id) ) $c",
		"CREATE TABLE IF NOT EXISTS {$p}photos ( id bigint(20) unsigned NOT NULL AUTO_INCREMENT, wedding_id bigint(20) unsigned NOT NULL, attachment_id bigint(20) unsigned NOT NULL DEFAULT 0, name varchar(120) NOT NULL DEFAULT '', caption varchar(255) NOT NULL DEFAULT '', likes int(11) NOT NULL DEFAULT 0, status varchar(12) NOT NULL DEFAULT 'approved', created datetime NOT NULL, PRIMARY KEY  (id), KEY wedding_id (wedding_id) ) $c",
		"CREATE TABLE IF NOT EXISTS {$p}quiz ( id bigint(20) unsigned NOT NULL AUTO_INCREMENT, wedding_id bigint(20) unsigned NOT NULL, name varchar(120) NOT NULL DEFAULT '', score int(11) NOT NULL DEFAULT 0, total int(11) NOT NULL DEFAULT 0, seconds int(11) NOT NULL DEFAULT 0, created datetime NOT NULL, PRIMARY KEY  (id), KEY wedding_id (wedding_id) ) $c",
		"CREATE TABLE IF NOT EXISTS {$p}reviews ( id bigint(20) unsigned NOT NULL AUTO_INCREMENT, vendor_id bigint(20) unsigned NOT NULL, wedding_id bigint(20) unsigned NOT NULL DEFAULT 0, name varchar(120) NOT NULL DEFAULT '', rating int(11) NOT NULL DEFAULT 5, text text, status varchar(12) NOT NULL DEFAULT 'approved', created datetime NOT NULL, PRIMARY KEY  (id), KEY vendor_id (vendor_id), KEY wedding_id (wedding_id) ) $c",
		"CREATE TABLE IF NOT EXISTS {$p}tx ( id bigint(20) unsigned NOT NULL AUTO_INCREMENT, wedding_id bigint(20) unsigned NOT NULL DEFAULT 0, kind varchar(20) NOT NULL DEFAULT 'gift', item varchar(64) NOT NULL DEFAULT '', name varchar(120) NOT NULL DEFAULT '', email varchar(120) NOT NULL DEFAULT '', message varchar(255) NOT NULL DEFAULT '', amount bigint(20) NOT NULL DEFAULT 0, fee bigint(20) NOT NULL DEFAULT 0, net bigint(20) NOT NULL DEFAULT 0, charged bigint(20) NOT NULL DEFAULT 0, currency varchar(3) NOT NULL DEFAULT 'NGN', cover_fee tinyint(1) NOT NULL DEFAULT 0, hide_amount tinyint(1) NOT NULL DEFAULT 0, reference varchar(40) NOT NULL DEFAULT '', status varchar(12) NOT NULL DEFAULT 'pending', test tinyint(1) NOT NULL DEFAULT 0, created datetime NOT NULL, paid_at datetime DEFAULT NULL, PRIMARY KEY  (id), KEY wedding_id (wedding_id), KEY reference (reference) ) $c",
		"CREATE TABLE IF NOT EXISTS {$p}likes ( id bigint(20) unsigned NOT NULL AUTO_INCREMENT, obj_type varchar(12) NOT NULL DEFAULT '', obj_id bigint(20) unsigned NOT NULL, voter varchar(64) NOT NULL DEFAULT '', created datetime NOT NULL, PRIMARY KEY  (id), KEY obj (obj_type, obj_id) ) $c",
		"CREATE TABLE IF NOT EXISTS {$p}views ( id bigint(20) unsigned NOT NULL AUTO_INCREMENT, wedding_id bigint(20) unsigned NOT NULL, day date NOT NULL, views int(11) NOT NULL DEFAULT 0, PRIMARY KEY  (id), KEY wedding_day (wedding_id, day) ) $c",
		"CREATE TABLE IF NOT EXISTS {$p}payouts ( id bigint(20) unsigned NOT NULL AUTO_INCREMENT, user_id bigint(20) unsigned NOT NULL, wedding_id bigint(20) unsigned NOT NULL, amount bigint(20) NOT NULL DEFAULT 0, currency varchar(3) NOT NULL DEFAULT 'NGN', bank varchar(120) NOT NULL DEFAULT '', account_no varchar(40) NOT NULL DEFAULT '', account_name varchar(120) NOT NULL DEFAULT '', status varchar(12) NOT NULL DEFAULT 'requested', note varchar(255) NOT NULL DEFAULT '', created datetime NOT NULL, paid_at datetime DEFAULT NULL, PRIMARY KEY  (id), KEY wedding_id (wedding_id) ) $c",
		"CREATE TABLE IF NOT EXISTS {$p}requests ( id bigint(20) unsigned NOT NULL AUTO_INCREMENT, user_id bigint(20) unsigned NOT NULL DEFAULT 0, wedding_id bigint(20) unsigned NOT NULL DEFAULT 0, service varchar(40) NOT NULL DEFAULT '', name varchar(120) NOT NULL DEFAULT '', phone varchar(40) NOT NULL DEFAULT '', email varchar(120) NOT NULL DEFAULT '', details text, status varchar(12) NOT NULL DEFAULT 'new', created datetime NOT NULL, PRIMARY KEY  (id) ) $c",
		"CREATE TABLE IF NOT EXISTS {$p}previews ( id bigint(20) unsigned NOT NULL AUTO_INCREMENT, partner_id bigint(20) unsigned NOT NULL, listing_id bigint(20) unsigned NOT NULL DEFAULT 0, booking_id bigint(20) unsigned NOT NULL DEFAULT 0, user_id bigint(20) unsigned NOT NULL DEFAULT 0, name varchar(120) NOT NULL DEFAULT '', rating int(11) NOT NULL DEFAULT 5, text text, reply text, status varchar(12) NOT NULL DEFAULT 'pending', created datetime NOT NULL, PRIMARY KEY  (id), KEY partner_id (partner_id), KEY booking_id (booking_id) ) $c",
		"CREATE TABLE IF NOT EXISTS {$p}chats ( id bigint(20) unsigned NOT NULL AUTO_INCREMENT, partner_id bigint(20) unsigned NOT NULL, user_id bigint(20) unsigned NOT NULL, sender varchar(10) NOT NULL DEFAULT 'user', author_id bigint(20) unsigned NOT NULL DEFAULT 0, listing_id bigint(20) unsigned NOT NULL DEFAULT 0, body text, offer_amount bigint(20) NOT NULL DEFAULT 0, offer_start varchar(10) NOT NULL DEFAULT '', offer_end varchar(10) NOT NULL DEFAULT '', offer_status varchar(12) NOT NULL DEFAULT '', offer_items text, offer_code varchar(16) NOT NULL DEFAULT '', booking_id bigint(20) unsigned NOT NULL DEFAULT 0, seen tinyint(1) NOT NULL DEFAULT 0, created datetime NOT NULL, PRIMARY KEY  (id), KEY thread (partner_id, user_id), KEY user_id (user_id), KEY booking_id (booking_id) ) $c",
		"CREATE TABLE IF NOT EXISTS {$p}bookings ( id bigint(20) unsigned NOT NULL AUTO_INCREMENT, listing_id bigint(20) unsigned NOT NULL DEFAULT 0, partner_id bigint(20) unsigned NOT NULL DEFAULT 0, user_id bigint(20) unsigned NOT NULL DEFAULT 0, wedding_id bigint(20) unsigned NOT NULL DEFAULT 0, name varchar(120) NOT NULL DEFAULT '', email varchar(120) NOT NULL DEFAULT '', phone varchar(40) NOT NULL DEFAULT '', start_date varchar(10) NOT NULL DEFAULT '', end_date varchar(10) NOT NULL DEFAULT '', qty int(11) NOT NULL DEFAULT 1, units int(11) NOT NULL DEFAULT 1, amount bigint(20) NOT NULL DEFAULT 0, fee bigint(20) NOT NULL DEFAULT 0, net bigint(20) NOT NULL DEFAULT 0, currency varchar(3) NOT NULL DEFAULT 'NGN', reference varchar(40) NOT NULL DEFAULT '', status varchar(12) NOT NULL DEFAULT 'pending', note text, log text, test tinyint(1) NOT NULL DEFAULT 0, created datetime NOT NULL, paid_at datetime DEFAULT NULL, released_at datetime DEFAULT NULL, PRIMARY KEY  (id), KEY listing_id (listing_id), KEY partner_id (partner_id), KEY user_id (user_id) ) $c",
	);
	foreach ( $sql as $q ) {
		// Skip tables that already exist (some database layers still try to re-add their indexes).
		if ( preg_match( '/EXISTS (\S+)/', $q, $m ) && $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $m[1] ) ) === $m[1] ) {
			continue;
		}
		$wpdb->query( $q ); // phpcs:ignore WordPress.DB
	}
	wd_chat_columns();
	update_option( 'wd_db_ver', WD_VER );
}

/**
 * Table name helper.
 *
 * @param string $t Short name.
 * @return string
 */
function wd_t( $t ) {
	global $wpdb;
	return $wpdb->prefix . 'wd_' . $t;
}

add_action(
	'init',
	static function () {
		if ( ! get_role( 'couple' ) ) {
			add_role( 'couple', __( 'Couple', 'wwmd' ), array( 'read' => true ) );
		}
		// 1.7.0: people who only shop & chat with sellers (or sell) have a plain user account.
		if ( ! get_role( 'member' ) ) {
			add_role( 'member', __( 'User', 'wwmd' ), array( 'read' => true ) );
		}
		if ( get_option( 'wd_db_ver' ) !== WD_VER ) {
			wd_install_tables();
		}
	},
	5
);

add_action(
	'after_switch_theme',
	static function () {
		if ( get_option( 'wd_db_ver' ) !== WD_VER ) {
			wd_install_tables();
		}
		wd_make_pages();
		wd_make_menus();
		update_option( 'users_can_register', 1 );
		update_option( 'default_role', 'couple' );
		if ( ! get_option( 'permalink_structure' ) ) {
			update_option( 'permalink_structure', '/%postname%/' );
		}
		if ( ! get_option( 'wd_seeded' ) ) {
			wd_seed();
		}
		flush_rewrite_rules();
	}
);

/**
 * The theme's own pages, by template. Made once; never remade after the owner deletes one.
 *
 * @return array slug => [title, template, content]
 */
function wd_pages() {
	return array(
		'rankings'  => array( __( 'Rankings', 'wwmd' ), 'page-templates/rankings.php', '' ),
		'pricing'   => array( __( 'Pricing', 'wwmd' ), 'page-templates/pricing.php', '' ),
		'services'  => array( __( 'Services', 'wwmd' ), 'page-templates/services.php', '' ),
		'blog'      => array( __( 'Blog', 'wwmd' ), 'page-templates/blog.php', '' ),
		'sign-in'   => array( __( 'Sign in', 'wwmd' ), 'page-templates/sign-in.php', '' ),
		'account'   => array( __( 'My account', 'wwmd' ), 'page-templates/account.php', '' ),
		'hq'        => array( __( 'HQ', 'wwmd' ), 'page-templates/hq.php', '' ),
		'discover'  => array( __( 'Weddings, made social', 'wwmd' ), 'page-templates/landing.php', '' ),
		'about'     => array( __( 'About us', 'wwmd' ), '', wd_page_copy( 'about' ) ),
		'terms'     => array( __( 'Terms', 'wwmd' ), '', wd_page_copy( 'terms' ) ),
		'privacy'   => array( __( 'Privacy', 'wwmd' ), '', wd_page_copy( 'privacy' ) ),
	);
}

/**
 * Make the theme pages that do not exist yet.
 */
function wd_make_pages() {
	$made = (array) get_option( 'wd_made_pages', array() );
	foreach ( wd_pages() as $slug => $p ) {
		if ( isset( $made[ $slug ] ) ) {
			continue;
		}
		$existing = get_page_by_path( $slug );
		if ( $existing ) {
			$made[ $slug ] = $existing->ID;
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $p[0],
				'post_name'    => $slug,
				'post_content' => $p[2],
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			if ( $p[1] ) {
				update_post_meta( $id, '_wp_page_template', $p[1] );
			}
			$made[ $slug ] = $id;
		}
	}
	update_option( 'wd_made_pages', $made );
}

/**
 * URL of a theme page.
 *
 * @param string $slug Page key.
 * @param array  $args Query args.
 * @return string
 */
function wd_url( $slug, $args = array() ) {
	$made = (array) get_option( 'wd_made_pages', array() );
	$url  = '';
	if ( ! empty( $made[ $slug ] ) && 'publish' === get_post_status( $made[ $slug ] ) ) {
		$url = get_permalink( $made[ $slug ] );
	}
	if ( ! $url ) {
		switch ( $slug ) {
			case 'explore':
				$url = get_post_type_archive_link( 'wd_wedding' );
				break;
			case 'vendors':
				$url = get_post_type_archive_link( 'wd_vendor' );
				break;
			case 'community':
				$url = get_post_type_archive_link( 'wd_talk' );
				break;
			default:
				$url = home_url( '/' . $slug . '/' );
		}
	}
	return $args ? add_query_arg( $args, $url ) : $url;
}

/**
 * Account URL for a tab.
 *
 * @param string $tab  Tab.
 * @param array  $args More args.
 * @return string
 */
function wd_account_url( $tab = '', $args = array() ) {
	if ( $tab ) {
		$args = array_merge( array( 'tab' => $tab ), $args );
	}
	return wd_url( 'account', $args );
}

/**
 * Default menus, made once.
 */
function wd_make_menus() {
	if ( get_option( 'wd_menus_made' ) ) {
		return;
	}
	$locations = (array) get_theme_mod( 'nav_menu_locations', array() );
	$menus     = array(
		'primary' => array(
			__( 'Header menu', 'wwmd' ),
			array(
				array( __( 'Explore weddings', 'wwmd' ), 'explore' ),
				array( __( 'Rankings', 'wwmd' ), 'rankings' ),
				array( __( 'Anonymous community', 'wwmd' ), 'community' ),
				array( __( 'Pricing', 'wwmd' ), 'pricing' ),
				array( __( 'Services', 'wwmd' ), 'services' ),
				array( __( 'Blog', 'wwmd' ), 'blog' ),
			),
		),
		'footer'  => array(
			__( 'Footer menu', 'wwmd' ),
			array(
				array( __( 'Explore weddings', 'wwmd' ), 'explore' ),
				array( __( 'Rankings', 'wwmd' ), 'rankings' ),
				array( __( 'Vendors', 'wwmd' ), 'vendors' ),
				array( __( 'Anonymous community', 'wwmd' ), 'community' ),
				array( __( 'Pricing', 'wwmd' ), 'pricing' ),
				array( __( 'Services', 'wwmd' ), 'services' ),
				array( __( 'Blog', 'wwmd' ), 'blog' ),
				array( __( 'About us', 'wwmd' ), 'about' ),
				array( __( 'Create a wedding page', 'wwmd' ), 'sign-in' ),
			),
		),
		'legal'   => array(
			__( 'Footer bottom links', 'wwmd' ),
			array(
				array( __( 'Terms', 'wwmd' ), 'terms' ),
				array( __( 'Privacy', 'wwmd' ), 'privacy' ),
			),
		),
	);
	foreach ( $menus as $loc => $m ) {
		if ( ! empty( $locations[ $loc ] ) ) {
			continue;
		}
		$id = wp_create_nav_menu( $m[0] );
		if ( is_wp_error( $id ) ) {
			$obj = wp_get_nav_menu_object( $m[0] );
			$id  = $obj ? $obj->term_id : 0;
		}
		if ( ! $id ) {
			continue;
		}
		foreach ( $m[1] as $i => $item ) {
			wp_update_nav_menu_item(
				$id,
				0,
				array(
					'menu-item-title'    => $item[0],
					'menu-item-url'      => wd_url( $item[1] ),
					'menu-item-status'   => 'publish',
					'menu-item-type'     => 'custom',
					'menu-item-position' => $i + 1,
				)
			);
		}
		$locations[ $loc ] = $id;
	}
	set_theme_mod( 'nav_menu_locations', $locations );
	update_option( 'wd_menus_made', 1 );
}

/**
 * Menu items for a location as [title, url] pairs, with the theme's defaults when no menu is set.
 *
 * @param string $loc Location.
 * @return array
 */
function wd_menu( $loc ) {
	$locations = get_nav_menu_locations();
	if ( ! empty( $locations[ $loc ] ) ) {
		$items = wp_get_nav_menu_items( $locations[ $loc ] );
		if ( $items ) {
			$out = array();
			foreach ( $items as $it ) {
				if ( ! $it->menu_item_parent ) {
					$out[] = array( $it->title, $it->url );
				}
			}
			return $out;
		}
	}
	if ( 'tabbar' === $loc ) {
		return array(
			array( __( 'Home', 'wwmd' ), home_url( '/' ) ),
			array( __( 'Weddings', 'wwmd' ), wd_url( 'explore' ) ),
			array( __( 'Marketplace', 'wwmd' ), wd_url( 'services' ) ),
			array( __( 'Buzz Chart', 'wwmd' ), wd_url( 'rankings' ) ),
		);
	}
	return array(
		array( __( 'Explore weddings', 'wwmd' ), wd_url( 'explore' ) ),
		array( __( 'Rankings', 'wwmd' ), wd_url( 'rankings' ) ),
		array( __( 'Anonymous community', 'wwmd' ), wd_url( 'community' ) ),
		array( __( 'Pricing', 'wwmd' ), wd_url( 'pricing' ) ),
		array( __( 'Services', 'wwmd' ), wd_url( 'services' ) ),
	);
}

/**
 * Icon for a phone tab bar link, picked from where it goes.
 *
 * @param string $url Link.
 * @return string Icon name.
 */
function wd_tab_icon( $url, $label = '' ) {
	$path = trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' );
	$home = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	$host = (string) wp_parse_url( $url, PHP_URL_HOST );
	if ( $path === $home && ( ! $host || wp_parse_url( home_url(), PHP_URL_HOST ) === $host ) ) {
		return 'home';
	}
	$map = array(
		'communit' => 'chat',
		'rank'     => 'chart',
		'buzz'     => 'chart',
		'wedding'  => 'heart',
		'service'  => 'store',
		'seller'   => 'store',
		'market'   => 'store',
		'vendor'   => 'users',
		'pricing'  => 'crown',
		'blog'     => 'book',
		'about'    => 'info',
		'account'  => 'settings',
		'sign-in'  => 'users',
		'search'   => 'search',
		'whatsapp' => 'whatsapp',
		'wa.me'    => 'whatsapp',
		'home'     => 'home',
	);
	// The link's words first (e.g. "Marketplace"), then its path. Never the whole address: a site
	// called weddingwebsites.io has "wedding" in every link, which gave every tab the heart icon.
	$own = $host && wp_parse_url( home_url(), PHP_URL_HOST ) !== $host ? $url : $path;
	foreach ( array( $label, $own ) as $where ) {
		foreach ( $map as $k => $icon ) {
			if ( '' !== (string) $where && false !== stripos( (string) $where, $k ) ) {
				return $icon;
			}
		}
	}
	return 'spark';
}

/**
 * Pages with the phone tab bar: everywhere except couples' own wedding pages, which keep their own look.
 *
 * @return bool
 */
function wd_has_tabbar() {
	return ! is_singular( 'wd_wedding' ) && ! is_page_template( 'page-templates/table-cards.php' );
}

add_filter(
	'body_class',
	static function ( $c ) {
		if ( wd_has_tabbar() ) {
			$c[] = 'has-tabbar';
		}
		return $c;
	}
);

/* -------------------------------------------------------------------------
 * Everyone works on the front end: wp-admin & the admin bar are for site admins only.
 * ---------------------------------------------------------------------- */

add_action(
	'admin_init',
	static function () {
		if ( wp_doing_ajax() || current_user_can( 'manage_options' ) ) {
			return;
		}
		$file = isset( $_SERVER['SCRIPT_NAME'] ) ? basename( sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_NAME'] ) ) ) : '';
		if ( in_array( $file, array( 'admin-post.php', 'async-upload.php' ), true ) ) {
			return;
		}
		wp_safe_redirect( wd_account_url() );
		exit;
	}
);

add_filter( 'show_admin_bar', '__return_false' );

/* -------------------------------------------------------------------------
 * Assets.
 * ---------------------------------------------------------------------- */

add_action(
	'wp_enqueue_scripts',
	static function () {
		$u = get_template_directory_uri() . '/assets/';
		wp_enqueue_style( 'wd-main', $u . 'css/main.css', array(), WD_VER );
		wp_enqueue_script( 'wd-main', $u . 'js/main.js', array(), WD_VER, true );
		$is_wedding = is_singular( 'wd_wedding' );
		$is_app     = is_page_template( array( 'page-templates/account.php', 'page-templates/hq.php' ) );
		if ( $is_wedding || get_query_var( 'wd_pass' ) || $is_app || get_query_var( 'wd_cards' ) ) {
			wp_enqueue_script( 'wd-qr', $u . 'vendor/qrcode.js', array(), '2.0.4', true );
		}
		if ( $is_wedding ) {
			wp_enqueue_style( 'wd-wedding', $u . 'css/wedding.css', array( 'wd-main' ), WD_VER );
			wp_enqueue_script( 'wd-confetti', $u . 'vendor/confetti.min.js', array(), '1.9.3', true );
			wp_enqueue_script( 'wd-wedding', $u . 'js/wedding.js', array( 'wd-main', 'wd-qr', 'wd-confetti' ), WD_VER, true );
		}
		if ( is_front_page() && wd_opt( 'home_fx' ) ) {
			wp_enqueue_script( 'wd-confetti', $u . 'vendor/confetti.min.js', array(), '1.9.3', true );
		}
		if ( $is_app || get_query_var( 'wd_cards' ) ) {
			wp_enqueue_style( 'wd-app', $u . 'css/app.css', array( 'wd-main' ), WD_VER );
			wp_enqueue_script( 'wd-app', $u . 'js/app.js', array( 'wd-main', 'wd-qr' ), WD_VER, true );
		}
		wp_localize_script(
			'wd-main',
			'WD',
			array(
				'api'      => esc_url_raw( rest_url( 'wd/v1/' ) ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
				'home'     => home_url( '/' ),
				'signin'   => wd_url( 'sign-in' ),
				'fx'       => ( is_front_page() && wd_opt( 'home_fx' ) && ! defined( 'WD_NO_FX' ) ) ? 'on' : '',
				'i18n'     => array(
					'error'     => __( 'Something went wrong. Please try again.', 'wwmd' ),
					'copied'    => __( 'Link copied', 'wwmd' ),
					'sending'   => __( 'Sending…', 'wwmd' ),
					'like'      => __( 'like', 'wwmd' ),
					'likes'     => __( 'likes', 'wwmd' ),
					'talkSaved' => __( 'Picture saved & link copied. Share it anywhere.', 'wwmd' ),
				),
			)
		);
	}
);

add_action(
	'wp_head',
	static function () {
		$u = get_template_directory_uri() . '/assets/';
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( $u . 'fonts/tex-gyre-adventor-bold.woff2' ) );
		if ( wd_opt( 'preloader' ) ) {
			printf( '<link rel="preload" href="%s" as="image" type="image/svg+xml">' . "\n", esc_url( $u . 'img/brand/seal.svg' ) );
		}
		printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url( $u . 'img/brand/mark.svg' ) );
		printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( $u . 'img/brand/apple-touch-icon.png' ) );
		echo '<meta name="theme-color" content="#23094B">' . "\n";
	},
	2
);

/* -------------------------------------------------------------------------
 * Small helpers.
 * ---------------------------------------------------------------------- */

/**
 * Currencies a wedding can take money in (the ones the payment provider supports).
 *
 * @return array code => [symbol, name]
 */
function wd_currencies() {
	return array(
		'NGN' => array( '₦', __( 'Naira', 'wwmd' ) ),
		'GHS' => array( 'GH₵', __( 'Ghana cedi', 'wwmd' ) ),
		'KES' => array( 'KSh', __( 'Kenyan shilling', 'wwmd' ) ),
		'ZAR' => array( 'R', __( 'South African rand', 'wwmd' ) ),
		'USD' => array( '$', __( 'US dollar', 'wwmd' ) ),
	);
}

/**
 * Format money.
 *
 * @param int|float $amount   Amount in major units.
 * @param string    $currency Code.
 * @return string
 */
function wd_money( $amount, $currency = 'NGN' ) {
	$c   = wd_currencies();
	$sym = isset( $c[ $currency ] ) ? $c[ $currency ][0] : $currency . ' ';
	return $sym . number_format_i18n( (float) $amount, 0 );
}

/**
 * Short number: 1.2k, 3.4m.
 *
 * @param int|float $n Number.
 * @return string
 */
function wd_short( $n ) {
	$n = (float) $n;
	if ( $n >= 1000000 ) {
		return rtrim( rtrim( number_format( $n / 1000000, 1 ), '0' ), '.' ) . 'm';
	}
	if ( $n >= 1000 ) {
		return rtrim( rtrim( number_format( $n / 1000, 1 ), '0' ), '.' ) . 'k';
	}
	return number_format_i18n( $n );
}

/**
 * Amount in Naira (used only to compare weddings for the rankings).
 *
 * @param float  $amount   Amount.
 * @param string $currency Code.
 * @return float
 */
function wd_to_ngn( $amount, $currency ) {
	$r = wd_opt( 'rates' );
	return (float) $amount * ( isset( $r[ $currency ] ) ? (float) $r[ $currency ] : 1 );
}

/**
 * WhatsApp link with a message.
 *
 * @param string $text Message.
 * @return string
 */
function wd_whatsapp( $text = '' ) {
	$n = preg_replace( '/\D+/', '', (string) wd_opt( 'whatsapp' ) );
	return 'https://wa.me/' . $n . ( $text ? '?text=' . rawurlencode( $text ) : '' );
}

/**
 * The WhatsApp number as people read it.
 *
 * @return string
 */
function wd_whatsapp_display() {
	$n = preg_replace( '/\D+/', '', (string) wd_opt( 'whatsapp' ) );
	if ( 13 === strlen( $n ) && 0 === strpos( $n, '234' ) ) {
		return '+234 ' . substr( $n, 3, 3 ) . ' ' . substr( $n, 6, 3 ) . ' ' . substr( $n, 9 );
	}
	return '+' . $n;
}

/**
 * The brand seal (never altered).
 *
 * @param int    $size  Size.
 * @param string $class Class.
 */
function wd_seal( $size = 44, $class = '' ) {
	printf(
		'<img class="seal %1$s" src="%2$s" width="%3$d" height="%3$d" alt="" decoding="async">',
		esc_attr( $class ),
		esc_url( get_template_directory_uri() . '/assets/img/brand/seal.svg' ),
		(int) $size
	);
}

/**
 * Full logo: the brand seal & the name in the brand typeface.
 *
 * @param bool   $reverse Light text.
 * @param string $class   Class.
 */
function wd_logo( $reverse = false, $class = '' ) {
	$logo_id = get_theme_mod( 'custom_logo' );
	if ( $logo_id && ! $reverse ) {
		echo wp_get_attachment_image( $logo_id, 'medium', false, array( 'class' => 'logo ' . esc_attr( $class ), 'alt' => get_bloginfo( 'name' ) ) );
		return;
	}
	printf(
		'<span class="logo%1$s%2$s"><img class="logo__mark" src="%3$s" width="512" height="512" alt="" decoding="async"><span class="logo__text"><span class="logo__line">%4$s</span> <span class="logo__line"><span class="logo__amp">&amp;</span> %5$s</span></span></span>',
		$reverse ? ' logo--reverse' : '',
		$class ? ' ' . esc_attr( $class ) : '',
		esc_url( get_template_directory_uri() . '/assets/img/brand/seal.svg' ),
		esc_html__( 'Wedding Websites', 'wwmd' ),
		esc_html__( 'More', 'wwmd' )
	);
}

/**
 * Image URL from an attachment ID or a theme photo name.
 *
 * @param int|string $img  ID or "photo:name".
 * @param string     $size Size.
 * @return string
 */
function wd_img_url( $img, $size = 'wd-card' ) {
	if ( is_numeric( $img ) && $img ) {
		$src = wp_get_attachment_image_url( (int) $img, $size );
		return $src ? $src : '';
	}
	if ( is_string( $img ) && 0 === strpos( $img, 'photo:' ) ) {
		return get_template_directory_uri() . '/assets/img/photos/' . sanitize_file_name( substr( $img, 6 ) ) . '.jpg';
	}
	return is_string( $img ) ? esc_url_raw( $img ) : '';
}

/**
 * Time since, in plain words.
 *
 * @param string $mysql Date.
 * @return string
 */
function wd_ago( $mysql ) {
	$t = strtotime( $mysql . ' UTC' );
	if ( ! $t ) {
		return '';
	}
	/* translators: %s: time, e.g. 5 mins */
	return sprintf( __( '%s ago', 'wwmd' ), human_time_diff( $t, time() ) );
}

/**
 * Voter ID for likes (a random cookie set by JS, else a hash of the visitor).
 *
 * @return string
 */
function wd_voter() {
	if ( is_user_logged_in() ) {
		return 'u' . get_current_user_id();
	}
	$c = isset( $_COOKIE['wd_vid'] ) ? preg_replace( '/[^a-z0-9]/i', '', sanitize_text_field( wp_unslash( $_COOKIE['wd_vid'] ) ) ) : '';
	if ( strlen( $c ) >= 12 ) {
		return 'c' . substr( $c, 0, 40 );
	}
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
	return 'h' . substr( wp_hash( $ip . $ua ), 0, 30 );
}

/**
 * Columns for a masonry block so no column is left empty: the most columns (up to $max)
 * that $n items of about the same height fill when they flow top to bottom.
 *
 * @param int $n   Items.
 * @param int $max Most columns.
 * @return int
 */
function wd_cols( $n, $max ) {
	$n = max( 1, (int) $n );
	for ( $c = min( (int) $max, $n ); $c > 1; $c-- ) {
		if ( (int) ceil( $n / ceil( $n / $c ) ) === $c ) {
			return $c;
		}
	}
	return 1;
}

/**
 * Simple rate limit per visitor & action.
 *
 * @param string $action Action.
 * @param int    $max    Max hits.
 * @param int    $window Seconds.
 * @return bool True when allowed.
 */
function wd_rate_ok( $action, $max = 10, $window = 600 ) {
	$key = 'wd_rl_' . md5( $action . wd_voter() );
	$n   = (int) get_transient( $key );
	if ( $n >= $max ) {
		return false;
	}
	set_transient( $key, $n + 1, $window );
	return true;
}

/**
 * Icon set (24px grid, rounded strokes).
 *
 * @param string $name Icon.
 * @param int    $size Size.
 * @return string
 */
function wd_icon( $name, $size = 20 ) {
	$p = array(
		'store'    => '<path d="M4.5 11v9h15v-9"/><path d="M3 9.5 4.8 4h14.4L21 9.5a2.6 2.6 0 0 1-4.5 1.8 2.6 2.6 0 0 1-4.5 0 2.6 2.6 0 0 1-4.5 0A2.6 2.6 0 0 1 3 9.5z"/><path d="M10 20v-4.5h4V20"/>',
		'lock'     => '<rect x="5" y="10.5" width="14" height="10" rx="2.5"/><path d="M8.5 10.5v-3a3.5 3.5 0 0 1 7 0v3M12 14.5V17"/>',
		'sun'      => '<circle cx="12" cy="12" r="4.2"/><path d="M12 2.5v2.2M12 19.3v2.2M4.2 4.2l1.6 1.6M18.2 18.2l1.6 1.6M2.5 12h2.2M19.3 12h2.2M4.2 19.8l1.6-1.6M18.2 5.8l1.6-1.6"/>',
		'spark'    => '<path d="m12 3 1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9z"/><path d="m18.5 15 .8 2.2 2.2.8-2.2.8-.8 2.2-.8-2.2-2.2-.8 2.2-.8z"/>',
		'send'     => '<path d="M4 11.5 20 4l-6.5 16-2.6-6.4z"/><path d="M10.9 13.6 20 4"/>',
		'bookmark' => '<path d="M6.5 3.5h11a1 1 0 0 1 1 1v16l-6.5-4.6-6.5 4.6v-16a1 1 0 0 1 1-1z"/>',
		'grid'     => '<rect x="3.5" y="3.5" width="17" height="17" rx="2"/><path d="M9.2 3.5v17M14.8 3.5v17M3.5 9.2h17M3.5 14.8h17"/>',
		'feed'     => '<rect x="4" y="3.5" width="16" height="11" rx="2"/><path d="M4 18h16M4 21h10"/>',
		'info'     => '<circle cx="12" cy="12" r="8.5"/><path d="M12 11v5.5M12 7.6v.4"/>',
		'book'     => '<path d="M5 5a2 2 0 0 1 2-2h12v14H7a2 2 0 0 0-2 2z"/><path d="M5 19a2 2 0 0 0 2 2h12v-4M9 7h6"/>',
		'heart'    => '<path d="M12 20s-7.5-4.6-9.3-9.2C1.5 7.6 3.6 4.5 6.9 4.5c2 0 3.4 1.1 5.1 3 1.7-1.9 3.1-3 5.1-3 3.3 0 5.4 3.1 4.2 6.3C19.5 15.4 12 20 12 20z"/>',
		'search'   => '<circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/>',
		'pin'      => '<path d="M12 21s-6.5-6.1-6.5-11.2A6.5 6.5 0 0 1 18.5 9.8C18.5 14.9 12 21 12 21z"/><circle cx="12" cy="9.8" r="2.3"/>',
		'calendar' => '<rect x="3.5" y="5" width="17" height="15.5" rx="3"/><path d="M3.5 10h17M8 3v4M16 3v4"/>',
		'eye'      => '<path d="M2.5 12s3.5-6.5 9.5-6.5 9.5 6.5 9.5 6.5-3.5 6.5-9.5 6.5S2.5 12 2.5 12z"/><circle cx="12" cy="12" r="2.8"/>',
		'fire'     => '<path d="M12 21c-4 0-7-2.7-7-6.6 0-3.5 2.4-5.4 3.6-8.4.4 1.9 1.3 3 2.4 3.6.1-3.1 1.6-5.6 4-7.1-.2 3 1 4.7 2.4 6.5 1.2 1.6 1.6 3 1.6 5.2 0 4-3 6.8-7 6.8z"/>',
		'star'     => '<path d="m12 3.5 2.6 5.4 5.9.8-4.3 4.1 1 5.8L12 16.9l-5.2 2.7 1-5.8-4.3-4.1 5.9-.8z"/>',
		'gift'     => '<rect x="3.5" y="8.5" width="17" height="4" rx="1"/><path d="M5 12.5v8h14v-8M12 8.5v12M12 8.5S10.6 4 8.2 4C6.6 4 6 5.2 6.4 6.3c.6 1.6 3.4 2.2 5.6 2.2zm0 0s1.4-4.5 3.8-4.5c1.6 0 2.2 1.2 1.8 2.3-.6 1.6-3.4 2.2-5.6 2.2z"/>',
		'cash'     => '<rect x="2.5" y="6" width="19" height="12" rx="2.5"/><circle cx="12" cy="12" r="2.8"/><path d="M6 9.5v5M18 9.5v5"/>',
		'camera'   => '<path d="M3.5 8.5A2 2 0 0 1 5.5 6.5h2l1.5-2h6l1.5 2h2a2 2 0 0 1 2 2V18a2 2 0 0 1-2 2h-13a2 2 0 0 1-2-2z"/><circle cx="12" cy="13" r="3.6"/>',
		'chat'     => '<path d="M4 18.5V6.5a2.5 2.5 0 0 1 2.5-2.5h11A2.5 2.5 0 0 1 20 6.5v7a2.5 2.5 0 0 1-2.5 2.5H8.5z"/><path d="M8 9h8M8 12.5h5"/>',
		'live'     => '<circle cx="12" cy="12" r="2.5"/><path d="M7.8 7.8a6 6 0 0 0 0 8.4M16.2 16.2a6 6 0 0 0 0-8.4M5 5a10 10 0 0 0 0 14M19 19a10 10 0 0 0 0-14"/>',
		'qr'       => '<rect x="3.5" y="3.5" width="6.5" height="6.5" rx="1"/><rect x="14" y="3.5" width="6.5" height="6.5" rx="1"/><rect x="3.5" y="14" width="6.5" height="6.5" rx="1"/><path d="M14 14h2.5v2.5H14zM18 18h2.5v2.5H18zM14 19.5h1.5M19.5 14v1.5"/>',
		'check'    => '<path d="m5 12.5 4.5 4.5L19.5 7"/>',
		'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'plus'     => '<path d="M12 5v14M5 12h14"/>',
		'close'    => '<path d="m6 6 12 12M18 6 6 18"/>',
		'share'    => '<circle cx="6.5" cy="12" r="2.5"/><circle cx="17.5" cy="6" r="2.5"/><circle cx="17.5" cy="18" r="2.5"/><path d="m8.7 10.8 6.6-3.6M8.7 13.2l6.6 3.6"/>',
		'tiktok'   => '<path d="M13.5 3.5v11.2a3.8 3.8 0 1 1-3.8-3.8"/><path d="M13.5 3.5c.5 2.7 2.4 4.6 5.3 4.8"/>',
		'x'        => '<path d="M4.5 4h4.2l10.8 16h-4.2zM19.2 4l-6.3 7.2M4.8 20l6.4-7.3"/>',
		'facebook' => '<path d="M14.5 21v-7.6h2.8l.5-3.4h-3.3V7.9c0-1 .4-1.7 1.8-1.7H18V3.2a21 21 0 0 0-2.7-.2C12.6 3 11 4.6 11 7.5V10H8v3.4h3V21"/>',
		'linkedin' => '<rect x="3.5" y="3.5" width="17" height="17" rx="3.5"/><path d="M8 10.5V17M8 7.4v.1M11.8 17v-6.5M11.8 13.3c0-1.7 1-2.8 2.5-2.8s2.2 1 2.2 2.8V17"/>',
		'snapchat' => '<path d="M12 3.5c-2.9 0-4.9 2.1-4.9 5v2.3l-1.9.6c.4 1 1.3 1.4 2.1 1.6-.6 1.6-1.8 2.8-3.5 3.4.9.9 2.2 1.1 3 1.2.2.8.5 1.4 1.4 1.4.9 0 1.6-.6 3.8-.6s2.9.6 3.8.6c.9 0 1.2-.6 1.4-1.4.8-.1 2.1-.3 3-1.2-1.7-.6-2.9-1.8-3.5-3.4.8-.2 1.7-.6 2.1-1.6l-1.9-.6V8.5c0-2.9-2-5-4.9-5z"/>',
		'briefcase' => '<rect x="3.5" y="7" width="17" height="12.5" rx="2.5"/><path d="M9 7V5.5A1.5 1.5 0 0 1 10.5 4h3A1.5 1.5 0 0 1 15 5.5V7M3.5 12.5h17"/>',
		'insta'    => '<rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r=".6" fill="currentColor"/>',
		'whatsapp' => '<path d="M4 20l1.2-3.9A8.5 8.5 0 1 1 8 19z"/><path d="M9 8.5c0 3.3 2.8 6.5 6.4 6.6l1.1-1.4-2-1-1 .8c-1-.4-2.1-1.4-2.6-2.5l.8-1-1-2z"/>',
		'users'    => '<circle cx="9" cy="8.5" r="3.2"/><path d="M3.5 19.5c.6-3.3 2.8-5 5.5-5s4.9 1.7 5.5 5"/><circle cx="17" cy="9.5" r="2.5"/><path d="M15.5 14.6c2.6-.3 4.5 1.2 5 4.4"/>',
		'trophy'   => '<path d="M7.5 4h9v5a4.5 4.5 0 0 1-9 0zM7.5 6H4.5a3 3 0 0 0 3 4M16.5 6h3a3 3 0 0 1-3 4M12 13.5v3.5M8.5 20h7M9.5 17h5v3h-5z"/>',
		'music'    => '<path d="M9 18V5.5l10-2V16"/><circle cx="6.5" cy="18" r="2.5"/><circle cx="16.5" cy="16" r="2.5"/>',
		'home'     => '<path d="M4 11 12 4l8 7v9h-5.5v-5.5h-5V20H4z"/>',
		'car'      => '<path d="M4.5 16.5V12l2-5h11l2 5v4.5zM4.5 16.5V19h3v-2.5M16.5 16.5V19h3v-2.5M4.5 12h15"/><circle cx="8" cy="14.3" r=".8"/><circle cx="16" cy="14.3" r=".8"/>',
		'plane'    => '<path d="M2.5 13.5 21 6.5l-3.5 11-5.5-2.8-2.5 3.8-.8-5zM9 13.5l12-7"/>',
		'video'    => '<rect x="3" y="6" width="13" height="12" rx="2.5"/><path d="m16 10 5-3v10l-5-3"/>',
		'settings' => '<circle cx="12" cy="12" r="3"/><path d="M12 2.8v2.4M12 18.8v2.4M21.2 12h-2.4M5.2 12H2.8M18.5 5.5l-1.7 1.7M7.2 16.8l-1.7 1.7M18.5 18.5l-1.7-1.7M7.2 7.2 5.5 5.5"/>',
		'logout'   => '<path d="M14 4.5h4a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2h-4M10 16.5l-4.5-4.5L10 7.5M5.5 12H16"/>',
		'edit'     => '<path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/>',
		'menu'     => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'crown'    => '<path d="M3.5 8 8 11.5 12 5l4 6.5L20.5 8 19 18H5z"/><path d="M5.5 21h13"/>',
		'trend'    => '<path d="m3.5 17 6-6 4 4 7-7.5M15 7.5h5.5V13"/>',
		'upload'   => '<path d="M12 15.5V4M7 8.5l5-5 5 5M4.5 15v3.5a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2V15"/>',
		'ticket'   => '<path d="M3.5 7.5a2 2 0 0 1 2-2h13a2 2 0 0 1 2 2V10a2 2 0 0 0 0 4v2.5a2 2 0 0 1-2 2h-13a2 2 0 0 1-2-2V14a2 2 0 0 0 0-4z"/><path d="M14.5 6v12" stroke-dasharray="2 2"/>',
		'shield'   => '<path d="M12 3.5 19 6v5.5c0 4.4-3 7.6-7 9-4-1.4-7-4.6-7-9V6z"/><path d="m8.8 12 2.2 2.2 4.4-4.4"/>',
		'chart'    => '<path d="M4 20V10M10 20V4M16 20v-7M21 20H3"/>',
		'bell'     => '<path d="M6 16.5V11a6 6 0 0 1 12 0v5.5l1.5 2h-15zM10 20.5a2 2 0 0 0 4 0"/>',
		'link'     => '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>',
		'download' => '<path d="M12 4v11.5M7 10.5l5 5 5-5M4.5 19.5h15"/>',
		'print'    => '<path d="M7 8V3.5h10V8M7 17H4.5V9.5a1.5 1.5 0 0 1 1.5-1.5h12a1.5 1.5 0 0 1 1.5 1.5V17H17M7 13.5h10v7H7z"/>',
		'rings'    => '<circle cx="9" cy="14.5" r="5"/><circle cx="15" cy="14.5" r="5"/><path d="m10.6 6 1.4-2.5L13.4 6 12 7.5z"/>',
		'moon'     => '<path d="M14.5 4a7.8 7.8 0 1 0 5.8 12.6A6.3 6.3 0 0 1 14.5 4z"/><path d="m18.2 5.2.5 1.2 1.3.2-1 .9.3 1.3-1.1-.7-1.1.7.3-1.3-1-.9 1.3-.2z"/>',
		'glass'    => '<path d="M5 3.5h5.5L10 8.2a2.5 2.5 0 0 1-4.5 0zM7.8 10.8v8.4M5.5 20.5h4.6M13.5 3.5H19l-.5 4.7a2.5 2.5 0 0 1-4.5 0zM16.2 10.8v8.4M14 20.5h4.5"/>',
		'henna'    => '<path d="M8.5 21v-3.8L5.4 13a1.6 1.6 0 0 1 2.4-2.1L9 12.2V5.4a1.4 1.4 0 0 1 2.8 0V11V4.4a1.4 1.4 0 0 1 2.8 0V11V5.8a1.4 1.4 0 0 1 2.8 0v8.9a6 6 0 0 1-2.8 5V21"/><circle cx="13" cy="15.2" r="1.3"/>',
		'scroll'   => '<path d="M7.5 4H18a2 2 0 0 1 2 2v1.5h-3.5M7.5 4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h7.5a2 2 0 0 0 2-2V6.5M9 9h5M9 12.5h5M9 16h3"/>',
		'hands'    => '<path d="M2.5 11.5 6.5 7.5l3.2 1 2.3-1.3h2.8l6.7 4.3M2.5 11.5l5.2 5.2a1.9 1.9 0 0 0 2.7 0M21.5 11.5 18 15M7.6 14.2l2 2M10.4 11.4l3.2 3.2a1.9 1.9 0 0 0 2.7 0l1.7-1.7"/>',
		'church'   => '<path d="M12 2.5v4.2M10 4.5h4M6 21v-8.5l6-5 6 5V21M3.5 21h17M10 21v-3.6a2 2 0 0 1 4 0V21"/>',
		'verified' => '<path d="M15.44 3.69A3.74 3.74 0 0 1 20.31 8.56A3.74 3.74 0 0 1 20.31 15.44A3.74 3.74 0 0 1 15.44 20.31A3.74 3.74 0 0 1 8.56 20.31A3.74 3.74 0 0 1 3.69 15.44A3.74 3.74 0 0 1 3.69 8.56A3.74 3.74 0 0 1 8.56 3.69A3.74 3.74 0 0 1 15.44 3.69z" fill="currentColor" stroke="none"/><path d="m7.9 12.3 2.7 2.7 5.5-5.8"/>',
		'gem'      => '<path d="M6.5 4h11L21 9l-9 11L3 9z"/><path d="M3 9h18M9.6 4 8 9l4 11 4-11-1.6-5"/>',
		'coin'     => '<ellipse cx="12" cy="6.5" rx="7" ry="3"/><path d="M5 6.5v5c0 1.7 3.1 3 7 3s7-1.3 7-3v-5M5 11.5v5c0 1.7 3.1 3 7 3s7-1.3 7-3v-5"/>',
		'fabric'   => '<path d="M8.5 16.5v3.8c1.6 1 3.2 1 4.8 0s3.2-1 4.8 0v-3.8"/><path d="M8.5 16.5h9.6v-9"/><rect x="3" y="3.5" width="15.1" height="6.5" rx="3.25"/><ellipse cx="6.25" cy="6.75" rx="1.4" ry="3.25"/><path d="M12 13.2h3M12 17.6h1.2"/>',
		'play'     => '<path d="M8.5 5.5v13l10-6.5z" fill="currentColor"/>',
		'clock'    => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
		'drum'     => '<ellipse cx="12" cy="6" rx="5" ry="2.2"/><ellipse cx="12" cy="18" rx="5" ry="2.2"/><path d="M7 6c2 3 2 9 0 12M17 6c-2 3-2 9 0 12M9 7.5l6 9M15 7.5l-6 9"/>',
	);
	if ( ! isset( $p[ $name ] ) ) {
		return '';
	}
	return '<svg class="icon icon--' . esc_attr( $name ) . '" width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $p[ $name ] . '</svg>';
}

/**
 * Notice from a redirect (?wdn=key).
 */
function wd_notice() {
	// phpcs:ignore WordPress.Security.NonceVerification
	$k = isset( $_GET['wdn'] ) ? sanitize_key( wp_unslash( $_GET['wdn'] ) ) : '';
	if ( ! $k ) {
		return;
	}
	$m = array(
		'saved'      => array( 'ok', __( 'Saved. Your changes are live.', 'wwmd' ) ),
		'created'    => array( 'ok', __( 'Your wedding page is ready. Add your details below, then share the link.', 'wwmd' ) ),
		'welcome'    => array( 'ok', __( 'Welcome! Your account is ready.', 'wwmd' ) ),
		'news_pulled'    => array( 'ok', __( 'Saved. New stories are in Posts as drafts, ready for you to rewrite. If none came in, the sites may be blocking us or have nothing new.', 'wwmd' ) ),
		'welcome_user'   => array( 'ok', __( 'Welcome! Your account is ready. You can now chat with sellers.', 'wwmd' ) ),
		'welcome_seller' => array( 'ok', __( 'Welcome! Your account is ready. Tell us about your business to start selling.', 'wwmd' ) ),
		'payout'     => array( 'ok', __( 'Payout request sent. We will pay it into your account & let you know on WhatsApp.', 'wwmd' ) ),
		'payout_setup' => array( 'err', __( 'We could not open the payout set-up page. Try again in a minute, or withdraw by hand below.', 'wwmd' ) ),
		'payout_low' => array( 'err', __( 'That amount is more than your available balance or under the minimum payout.', 'wwmd' ) ),
		'request'    => array( 'ok', __( 'Request sent. We will reply on WhatsApp shortly.', 'wwmd' ) ),
		'password'   => array( 'ok', __( 'Password changed.', 'wwmd' ) ),
		'pwbad'      => array( 'err', __( 'Your current password is not right, or the new passwords do not match.', 'wwmd' ) ),
		'email'      => array( 'err', __( 'That email is already used by another account.', 'wwmd' ) ),
		'talk_short'    => array( 'err', __( 'Pick a topic & write at least a few words (under 2,000 characters).', 'wwmd' ) ),
		'talk_name'     => array( 'err', __( 'Type a name to post under. Anything you like — it does not have to be your real one.', 'wwmd' ) ),
		'talk_held'     => array( 'ok', __( 'Thanks. It goes live once it has been checked.', 'wwmd' ) ),
		'talk_posted'   => array( 'ok', __( 'Posted. Only the name you typed is shown.', 'wwmd' ) ),
		'talk_replied'  => array( 'ok', __( 'Comment posted.', 'wwmd' ) ),
		'talk_reported' => array( 'ok', __( 'Thanks for telling us. Our team will look at it.', 'wwmd' ) ),
		'slow'          => array( 'err', __( 'Easy! Please wait a little & try again.', 'wwmd' ) ),
		'email_pw'   => array( 'err', __( 'To change your sign-in email, type your current password too.', 'wwmd' ) ),
		'login_saved' => array( 'ok', __( 'Sign-in details saved. Share the new password with them privately.', 'wwmd' ) ),
		'login_bad'  => array( 'err', __( 'Use a valid email nobody else has, & a password of 8 or more characters.', 'wwmd' ) ),
		'upgraded'   => array( 'ok', __( 'Your new plan is on. Enjoy the new features!', 'wwmd' ) ),
		'denied'     => array( 'err', __( 'You do not have access to that.', 'wwmd' ) ),
		'done'       => array( 'ok', __( 'Done.', 'wwmd' ) ),
		'deleted'    => array( 'ok', __( 'Deleted.', 'wwmd' ) ),
		'premium'    => array( 'err', __( 'That feature is not on your plan yet. Upgrade to turn it on.', 'wwmd' ) ),
		'order'      => array( 'ok', __( 'Delivery details saved. We will print your table cards & send tracking on WhatsApp.', 'wwmd' ) ),
		'partner_applied' => array( 'ok', __( 'Thanks for applying! We will check your details & reply on WhatsApp within 2 working days.', 'wwmd' ) ),
		'partner_missing' => array( 'err', __( 'Add your business name, city & a phone number.', 'wwmd' ) ),
		'listing_missing' => array( 'err', __( 'Add a title & a price.', 'wwmd' ) ),
		'listing_pending' => array( 'ok', __( 'Listing saved. We check new listings before they go live, usually the same day.', 'wwmd' ) ),
		'released'        => array( 'ok', __( 'Thank you! We have released the money to the seller.', 'wwmd' ) ),
		'reported'        => array( 'ok', __( 'Problem reported. We are holding the money & will reach you on WhatsApp.', 'wwmd' ) ),
		'review_sent'     => array( 'ok', __( 'Thank you! Your review shows on the seller’s profile once we check it.', 'wwmd' ) ),
		'review_short'    => array( 'err', __( 'Write a few words about how it went (at least 10 letters).', 'wwmd' ) ),
		'squad_link'      => array( 'ok', __( 'New invite link ready. The old link no longer works.', 'wwmd' ) ),
		'squad_off'       => array( 'ok', __( 'Invite link switched off. Nobody new can use it until you switch it back on.', 'wwmd' ) ),
		'squad_on'        => array( 'ok', __( 'Invite link switched on. Share it with your squad.', 'wwmd' ) ),
		'approved'        => array( 'ok', __( 'Approved. They now show in your line-up.', 'wwmd' ) ),
		'declined'        => array( 'ok', __( 'Declined & removed.', 'wwmd' ) ),
	);
	if ( isset( $m[ $k ] ) ) {
		printf( '<div class="notice notice--%1$s" role="status">%2$s<span>%3$s</span></div>', esc_attr( $m[ $k ][0] ), wd_icon( 'ok' === $m[ $k ][0] ? 'check' : 'close', 18 ), esc_html( $m[ $k ][1] ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

/**
 * Copy for the simple pages.
 *
 * @param string $key Page.
 * @return string
 */
function wd_page_copy( $key ) {
	switch ( $key ) {
		case 'about':
			return "<!-- wp:paragraph -->\n<p>Wedding Websites &amp; More is an African wedding business. We build wedding pages for Nigerian &amp; African couples at home &amp; abroad, &amp; this site is where those weddings live, so friends, family &amp; anyone who loves a good owambe can find them, join in &amp; celebrate.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>Couples start free. Guests RSVP, leave love notes, send gifts &amp; review the vendors who made the day. Pro pages add the squad line-up, guest photos, the couple quiz, entry passes &amp; the couple's own colours. Premium pages add a Premium design, livestream, spraying &amp; printed table QR cards. High Society brands the whole event, from the prints to the livestream crew.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>Questions? Chat with us on WhatsApp on +234 907 898 7201 or email more@weddingwebsites.io.</p>\n<!-- /wp:paragraph -->";
		case 'terms':
			return "<!-- wp:paragraph -->\n<p>By using Wedding Websites &amp; More, you agree to these terms.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:list -->\n<ul><li>Couples own the content on their wedding page &amp; are responsible for it.</li><li>Guests' love notes, photos &amp; reviews must be kind &amp; true. We remove anything abusive, false or not safe for work.</li><li>Money sent through a Free or Pro page carries the service fee shown at payment. Premium &amp; High Society pages carry no service fee.</li><li>Payouts go to the couple's bank or mobile money account after checks. Refunds follow the couple's own decision &amp; the card provider's rules.</li><li>On the services marketplace, customers chat with sellers, &amp; sellers send an invoice or a payment link. Payment links are paid on this site &amp; the seller's share goes to the seller. Invoices are paid to the seller directly. The sale is between the customer &amp; the seller, who agree any changes or refunds between them.</li><li>Rankings come from real activity on each page. We remove weddings that fake views, likes or payments.</li></ul>\n<!-- /wp:list -->";
		case 'privacy':
			return "<!-- wp:paragraph -->\n<p>We collect what you give us (your name, email, phone number &amp; RSVP details) to run your wedding page, send you updates &amp; pay out your gifts. Card payments are handled by a secure payment provider; we never see full card numbers.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>Couples can download or delete their guest list at any time. Ask us to delete your account on WhatsApp or by email.</p>\n<!-- /wp:paragraph -->";
	}
	return '';
}
