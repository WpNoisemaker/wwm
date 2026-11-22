<?php
/**
 * Updates for sites that already run an older version of the theme.
 * Each step runs once & only fills in what is missing, so it never overwrites the owner's edits.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'wd_upgrade', 30 );

/**
 * Run the steps for this version once.
 */
function wd_upgrade() {
	if ( get_option( 'wd_theme_ver' ) === WD_VER || ! get_option( 'wd_seeded' ) ) {
		return;
	}
	update_option( 'wd_theme_ver', WD_VER );
	wd_make_pages();
	wd_upgrade_samples();
	wd_upgrade_squads();
	wd_upgrade_listings();
	wd_upgrade_14();
	wd_upgrade_15();
	wd_upgrade_151();
	wd_upgrade_170();
	if ( ! get_option( 'wd_samples_removed' ) ) {
		wd_seed_market();
		wd_seed_asoebi();
	}
	foreach ( wd_partner_ids() as $uid ) {
		wd_partner_slug( $uid );
	}
	wd_seed_posts();
	wd_upgrade_menus();
	wd_talk_seed();
	wd_upgrade_talk_menu();
	wd_custom_install();
	foreach ( get_posts( array( 'post_type' => 'wd_wedding', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) ) as $id ) {
		update_post_meta( $id, '_wd_prog', ',' . implode( ',', array_unique( wp_list_pluck( wd_programs( wd_get( $id ) ), 'type' ) ) ) . ',' );
	}
	do_action( 'wd_upgraded' );
	flush_rewrite_rules( false );
}

/**
 * Add the Blog link to the header & footer menus once (only if those menus have no blog link yet).
 */
function wd_upgrade_menus() {
	if ( get_option( 'wd_menu_blog_added' ) ) {
		return;
	}
	update_option( 'wd_menu_blog_added', 1 );
	$url = wd_url( 'blog' );
	foreach ( array( 'primary', 'footer' ) as $loc ) {
		$locs = (array) get_theme_mod( 'nav_menu_locations', array() );
		if ( empty( $locs[ $loc ] ) ) {
			continue;
		}
		$items = (array) wp_get_nav_menu_items( $locs[ $loc ] );
		foreach ( $items as $it ) {
			if ( untrailingslashit( $it->url ) === untrailingslashit( $url ) ) {
				continue 2;
			}
		}
		$after = 0;
		foreach ( $items as $it ) {
			if ( untrailingslashit( $it->url ) === untrailingslashit( wd_url( 'services' ) ) ) {
				$after = (int) $it->menu_order;
			}
		}
		wp_update_nav_menu_item(
			$locs[ $loc ],
			0,
			array(
				'menu-item-title'    => __( 'Blog', 'wwmd' ),
				'menu-item-url'      => $url,
				'menu-item-status'   => 'publish',
				'menu-item-type'     => 'custom',
				'menu-item-position' => $after ? $after + 1 : count( $items ) + 1,
			)
		);
	}
}

/**
 * Give the sample Premium weddings their templates & gift credit (only where nothing is set yet).
 */
function wd_upgrade_samples() {
	foreach ( wd_seed_weddings() as $slug => $w ) {
		if ( 'premium' !== $w['plan'] ) {
			continue;
		}
		$post = get_page_by_path( $slug, OBJECT, 'wd_wedding' );
		if ( ! $post || ! get_post_meta( $post->ID, '_wd_sample', true ) ) {
			continue;
		}
		$raw = (array) get_post_meta( $post->ID, '_wd', true );
		$d   = wd_get( $post->ID );
		if ( empty( $raw['design']['template'] ) && ! empty( $w['template'] ) ) {
			$d['design']['template'] = $w['template'];
			if ( 'garden' === $w['template'] && 'marcellus' === ( $raw['design']['head'] ?? '' ) ) {
				$d['design']['head'] = 'cormorant';
			}
		}
		if ( empty( $raw['gift']['by'] ) && ! empty( $w['gift'] ) ) {
			$d['gift'] = array( 'by' => $w['gift'][0], 'link' => $w['gift'][1] );
		}
		update_post_meta( $post->ID, '_wd', $d );
	}
}

/**
 * 1.2: give the sample squads their careers, relationship status & handles. Only rows that were
 * never saved in 1.2 (no career field at all) on sample pages are filled in.
 */
function wd_upgrade_squads() {
	foreach ( wd_seed_weddings() as $slug => $w ) {
		$post = get_page_by_path( $slug, OBJECT, 'wd_wedding' );
		if ( ! $post || ! get_post_meta( $post->ID, '_wd_sample', true ) ) {
			continue;
		}
		$d    = wd_get( $post->ID );
		$seed = array();
		foreach ( $w['party'] as $p ) {
			$seed[ $p[0] ] = wd_seed_squad_row( $p );
		}
		$changed = false;
		foreach ( $d['party'] as $i => $r ) {
			if ( ! array_key_exists( 'career', $r ) && isset( $seed[ $r['name'] ] ) ) {
				$extra             = array_diff_key( $seed[ $r['name'] ], array_flip( array( 'name', 'role', 'side', 'line', 'image' ) ) );
				$d['party'][ $i ] = array_merge( $r, $extra, array( 'ig' => $r['ig'] ? $r['ig'] : $extra['ig'] ) );
				$changed           = true;
			}
		}
		if ( $changed ) {
			update_post_meta( $post->ID, '_wd', $d );
		}
	}
}

/**
 * 1.2: give the untouched sample listings their real photos. The three sample rides that changed vehicle
 * (so the photo matches) only change if the owner never edited them.
 */
function wd_upgrade_listings() {
	$old  = array(
		'Mercedes-Benz E-Class bridal car'             => array( 'Black Range Rover Sport bridal car', 'A clean, black E-Class to bring the bride to the ceremony in style, then take the couple to the reception.' ),
		'Toyota Land Cruiser Prado with driver'        => array( 'Mercedes-Benz G-Class with driver', 'A comfortable SUV for the couple, parents or VIP guests, with an experienced driver who knows every venue in town.' ),
		'14-seat Toyota Hiace bus for the bridal train' => array( 'Luxury coach for up to 50 guests', 'Move the bridal train, groomsmen or family together, on time & in one go.' ),
	);
	$seed = array();
	foreach ( wd_seed_partners() as $p ) {
		foreach ( $p[5] as $l ) {
			$seed[ $l[0] ] = $l;
		}
	}
	$ids = get_posts(
		array(
			'post_type'   => 'wd_listing',
			'post_status' => 'any',
			'numberposts' => -1,
			'fields'      => 'ids',
			'meta_key'    => '_wd_sample', // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	foreach ( $ids as $lid ) {
		$l     = wd_listing( $lid );
		$title = wp_specialchars_decode( get_post_field( 'post_title', $lid ), ENT_QUOTES );
		if ( ! empty( $l['photos'] ) ) {
			continue;
		}
		if ( isset( $old[ $title ] ) ) {
			if ( $l['about'] !== $old[ $title ][1] ) {
				continue;
			}
			$s = $seed[ $old[ $title ][0] ];
			wp_update_post( array( 'ID' => $lid, 'post_title' => $s[0], 'post_name' => sanitize_title( $s[0] ) ) );
			$l['price']    = $s[2];
			$l['capacity'] = $s[3];
			$l['about']    = $s[7];
		} elseif ( empty( $seed[ $title ] ) ) {
			continue;
		} else {
			$s = $seed[ $title ];
		}
		if ( $s[9] ) {
			$l['photos'] = (array) $s[9];
		}
		wd_listing_put( $lid, $l );
	}
}

/**
 * 1.4: four plans & Buzz coins, starter pages that show each plan, "Contact for price" for the real
 * businesses we never checked prices with, & honest starter content. Runs once. Settings, pages & the
 * seeded ad only change where they still hold the 1.3 defaults, so HQ's own edits stay.
 */
function wd_upgrade_14() {
	if ( get_option( 'wd_up_14' ) ) {
		return;
	}
	update_option( 'wd_up_14', 1 );
	global $wpdb;
	$same = static function ( $a, $b ) {
		$a = array_values( (array) $a );
		$b = array_values( (array) $b );
		sort( $a );
		sort( $b );
		return $a === $b;
	};

	// Plan settings saved while they were still the 1.3 defaults.
	$saved = get_option( 'wd_settings', array() );
	if ( is_array( $saved ) && $saved ) {
		$def      = wd_defaults();
		$old_free = array( 'invitation', 'story', 'rsvp', 'party', 'instagram', 'quiz', 'vendors', 'wall', 'gallery', 'registry' );
		$old_all  = array( 'invitation', 'story', 'rsvp', 'party', 'instagram', 'quiz', 'vendors', 'wall', 'gallery', 'registry', 'entry_pass', 'livestream', 'spray', 'design', 'table_qr', 'no_ads', 'no_fees' );
		if ( isset( $saved['features_free'] ) && $same( $saved['features_free'], $old_free ) ) {
			$saved['features_free'] = $def['features_free'];
		}
		if ( isset( $saved['features_premium'] ) ) {
			if ( $same( $saved['features_premium'], $old_all ) ) {
				$saved['features_premium'] = $def['features_premium'];
			} elseif ( in_array( 'design', (array) $saved['features_premium'], true ) && ! in_array( 'templates', (array) $saved['features_premium'], true ) ) {
				$saved['features_premium'][] = 'templates';
			}
		}
		if ( isset( $saved['premium_price'] ) && 450000 === (int) $saved['premium_price'] ) {
			$saved['premium_price'] = $def['premium_price'];
		}
		$old_ad = sprintf( 'Go Premium for %s, with 200 table QR cards printed & shipped to you.', wd_money( 450000 ) );
		foreach ( (array) ( $saved['ads'] ?? array() ) as $i => $ad ) {
			if ( 'Your colours. Your fonts. No ads.' === ( $ad['title'] ?? '' ) && 'See Premium' === ( $ad['cta'] ?? '' ) && $old_ad === ( $ad['text'] ?? '' ) ) {
				/* translators: %s price */
				$saved['ads'][ $i ]['text'] = sprintf( __( 'Go Pro for %s: your squad, guest photos, the couple quiz & entry passes, with no ads.', 'wwmd' ), wd_money( (int) ( $saved['pro_price'] ?? $def['pro_price'] ) ) );
				$saved['ads'][ $i ]['cta']  = __( 'Compare plans', 'wwmd' );
			}
		}
		update_option( 'wd_settings', $saved );
		wd_settings( true );
	}

	// About & terms pages nobody edited get the new copy (off-platform payments, plans).
	$norm = static function ( $h ) {
		return md5( trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( html_entity_decode( (string) $h, ENT_QUOTES, 'UTF-8' ) ) ) ) );
	};
	$made = (array) get_option( 'wd_made_pages', array() );
	foreach ( array( 'about' => 'dedcd2cc565d8a733415b0957814df63', 'terms' => '6527e12fe25eb35a099f83b3ce9f3a52' ) as $k => $hash ) {
		$post = empty( $made[ $k ] ) ? null : get_post( (int) $made[ $k ] );
		if ( $post && $norm( $post->post_content ) === $hash ) {
			wp_update_post(
				array(
					'ID'           => $post->ID,
					'post_content' => wd_page_copy( $k ),
				)
			);
		}
	}

	if ( ! get_option( 'wd_samples_removed' ) ) {
		wd_upgrade_14_starter( $same );
	}

	// Buzz coins for everyone.
	foreach ( get_posts( array( 'post_type' => 'wd_wedding', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) ) as $id ) {
		wd_refresh( $id );
	}
}

/**
 * 1.5: Pearl replaces the Editorial design, & partners are now called sellers. Runs once.
 * Pages on Editorial move to Pearl; their colours & fonts only change if they were still Editorial's
 * starting ones. The starter blog post only changes if nobody edited it.
 */
function wd_upgrade_15() {
	if ( get_option( 'wd_up_15' ) ) {
		return;
	}
	update_option( 'wd_up_15', 1 );
	$pearl = wd_templates()['pearl'][2];
	$keys  = array( 'primary', 'accent', 'bg', 'ink', 'head', 'body' );
	$olds  = array(
		array( '#111111', '#C8553D', '#F7F3EE', '#111111', 'dmserif', 'montserrat' ), // Editorial's starting look.
		array( '#1F6FB2', '#F28C28', '#F3F8FC', '#0B2440', 'dmserif', 'montserrat' ), // The 1.4 starter page (Wanjiru & Brian).
	);
	foreach ( get_posts( array( 'post_type' => 'wd_wedding', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) ) as $id ) {
		$d = wd_get( $id );
		if ( 'editorial' !== ( $d['design']['template'] ?? '' ) ) {
			continue;
		}
		$d['design']['template'] = 'pearl';
		$now                     = array();
		foreach ( $keys as $k ) {
			$now[] = strtolower( (string) ( $d['design'][ $k ] ?? '' ) );
		}
		foreach ( $olds as $i => $old ) {
			if ( array_map( 'strtolower', $old ) !== $now ) {
				continue;
			}
			$to = 1 === $i && get_post_meta( $id, '_wd_sample', true ) ? array( '#2F6E8E', '#E6D3B3', '#FBFCFC', '#1D2B33', 'marcellus', 'jost' ) : $pearl;
			foreach ( $keys as $j => $k ) {
				$d['design'][ $k ] = $to[ $j ];
			}
			$d['design']['pattern'] = 'none';
			if ( 'square' === ( $d['design']['corners'] ?? '' ) ) {
				$d['design']['corners'] = 'round';
			}
		}
		wd_put( $id, $d );
	}
	// The starter blog post about the marketplace says sellers now.
	foreach ( get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => -1, 's' => 'verified partners' ) ) as $post ) {
		$c = $post->post_content;
		foreach ( array(
		array( 'Book from verified partners</h2>', 'Book from verified sellers</h2>' ),
		array( 'listed by partners we have checked.', 'listed by sellers we have checked.' ),
		array( 'then pay the partner.', 'then pay the seller.' ),
		array( 'you can apply to become a partner.', 'you can apply to become a seller.' ),
		) as $pair ) {
			$c = str_replace( $pair[0], $pair[1], $c );
		}
		if ( $c !== $post->post_content ) {
			wp_update_post(
				array(
					'ID'           => $post->ID,
					'post_content' => $c,
				)
			);
		}
	}
}

/**
 * 1.4.0 changes to the starter content (skipped once it was removed).
 *
 * @param callable $same Compares two lists.
 */
function wd_upgrade_14_starter( $same ) {
	global $wpdb;
	$sample = static function ( $type ) {
		return get_posts(
			array(
				'post_type'   => $type,
				'post_status' => 'any',
				'numberposts' => -1,
				'fields'      => 'ids',
				'meta_key'    => '_wd_sample', // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
	};
	$tx = wd_t( 'tx' );

	// Starter pages now show every plan: Chiamaka & Obinna move from Premium to Pro.
	$p = get_page_by_path( 'chiamaka-obinna', OBJECT, 'wd_wedding' );
	if ( $p && get_post_meta( $p->ID, '_wd_sample', true ) && 'premium' === get_post_meta( $p->ID, '_wd_plan', true ) ) {
		update_post_meta( $p->ID, '_wd_plan', 'pro' );
		foreach ( $wpdb->get_col( $wpdb->prepare( "SELECT id FROM $tx WHERE wedding_id = %d AND kind = 'premium' ORDER BY id ASC", $p->ID ) ) as $i => $tid ) { // phpcs:ignore WordPress.DB
			if ( $i < 2 ) {
				$wpdb->update( $tx, array( 'item' => 'pro', 'amount' => 99000, 'charged' => 99000 ), array( 'id' => (int) $tid ) );
			} else {
				$wpdb->delete( $tx, array( 'id' => (int) $tid ) );
			}
		}
		update_post_meta( $p->ID, '_wd_premium_paid', 198000 );
	}
	// …& Efua & Kojo move from Free to Pro, with their colours & entry passes.
	$p = get_page_by_path( 'efua-kojo', OBJECT, 'wd_wedding' );
	$w = wd_seed_weddings()['efua-kojo'] ?? null;
	if ( $w && $p && get_post_meta( $p->ID, '_wd_sample', true ) && 'free' === get_post_meta( $p->ID, '_wd_plan', true ) ) {
		update_post_meta( $p->ID, '_wd_plan', 'pro' );
		$raw = (array) get_post_meta( $p->ID, '_wd', true );
		$d   = wd_get( $p->ID );
		if ( empty( $raw['design'] ) || $same( array_values( (array) $raw['design'] ), array_values( wd_blank()['design'] ) ) ) {
			$d['design'] = array_merge( $d['design'], array_combine( array( 'primary', 'accent', 'bg', 'ink', 'head', 'body', 'pattern' ), $w['design'] ) );
		}
		if ( empty( $raw['pass']['pin'] ) ) {
			$d['pass'] = array(
				'on'  => $w['pass'][0],
				'pin' => $w['pass'][1],
			);
		}
		update_post_meta( $p->ID, '_wd', $d );
		$wpdb->insert(
			$tx,
			array(
				'wedding_id' => $p->ID,
				'kind'       => 'premium',
				'item'       => 'pro',
				'name'       => $w['p1'] . ' & ' . $w['p2'],
				'email'      => 'efua-kojo@example.com',
				'amount'     => 198000,
				'charged'    => 198000,
				'currency'   => 'NGN',
				'reference'  => 'WDS' . strtoupper( wp_generate_password( 9, false ) ),
				'status'     => 'paid',
				'test'       => 1,
				'created'    => gmdate( 'Y-m-d H:i:s', time() - 20 * DAY_IN_SECONDS ),
				'paid_at'    => gmdate( 'Y-m-d H:i:s', time() - 20 * DAY_IN_SECONDS ),
			)
		);
		update_post_meta( $p->ID, '_wd_premium_paid', 198000 );
		foreach ( $wpdb->get_col( $wpdb->prepare( 'SELECT id FROM ' . wd_t( 'rsvps' ) . " WHERE wedding_id = %d AND attending = 'yes' AND pass_code = ''", $p->ID ) ) as $rid ) { // phpcs:ignore WordPress.DB
			$wpdb->update( wd_t( 'rsvps' ), array( 'pass_code' => wd_new_pass() ), array( 'id' => (int) $rid ) );
		}
	}

	// No made-up reviews or badges: hide the reviews that came with the starter pages & unverify starter vendors.
	$weds = $sample( 'wd_wedding' );
	if ( $weds ) {
		$wpdb->query( 'UPDATE ' . wd_t( 'reviews' ) . " SET status = 'hidden' WHERE status = 'approved' AND wedding_id IN (" . implode( ',', array_map( 'intval', $weds ) ) . ')' ); // phpcs:ignore WordPress.DB
	}
	foreach ( $sample( 'wd_vendor' ) as $vid ) {
		$v = (array) get_post_meta( $vid, '_wdv', true );
		if ( ! empty( $v['verified'] ) ) {
			$v['verified'] = 0;
			update_post_meta( $vid, '_wdv', $v );
		}
	}

	// AbujaCarhire & ViaskyNigeria are real: listings still on our made-up prices become "Contact for price".
	$seed = array();
	foreach ( array( 'abujacarhire', 'viaskynigeria' ) as $k ) {
		foreach ( wd_seed_partners()[ $k ][5] as $l ) {
			$seed[ $l[0] ] = $l;
		}
	}
	foreach ( $sample( 'wd_listing' ) as $lid ) {
		$t = wp_specialchars_decode( get_post_field( 'post_title', $lid ), ENT_QUOTES );
		$l = wd_listing( $lid );
		if ( isset( $seed[ $t ] ) && (int) $l['price'] === (int) $seed[ $t ][2] && ! wd_listing_asks( $l ) ) {
			$l['price_mode'] = 'ask';
			wd_listing_put( $lid, $l );
		}
	}

	// Starter accounts & booking numbers without "sample" in them.
	foreach ( get_users( array( 'meta_key' => '_wd_sample', 'fields' => array( 'ID', 'user_nicename' ) ) ) as $u ) { // phpcs:ignore WordPress.DB.SlowDBQuery
		if ( 0 === strpos( $u->user_nicename, 'sample-' ) && ! get_user_by( 'slug', substr( $u->user_nicename, 7 ) ) ) {
			wp_update_user(
				array(
					'ID'            => (int) $u->ID,
					'user_nicename' => substr( $u->user_nicename, 7 ),
				)
			);
		}
	}
	$wpdb->query( 'UPDATE ' . wd_t( 'bookings' ) . " SET reference = REPLACE(reference, 'WDSAMPLE', 'WD') WHERE reference LIKE 'WDSAMPLE%'" ); // phpcs:ignore WordPress.DB

	// Starter visit totals, so each plan keeps its usual place on the all-time chart.
	$vb = array(
		'tolani-kayode'   => array( 9800, 24000 ),
		'chiamaka-obinna' => array( 21000, 0 ),
		'amina-yusuf'     => array( 3100, 30000 ),
		'wanjiru-brian'   => array( 4200, 16000 ),
		'efua-kojo'       => array( 1900, 12000 ),
		'thandi-sipho'    => array( 3600, 1000 ),
	);
	foreach ( $vb as $slug => $v ) {
		$p = get_page_by_path( $slug, OBJECT, 'wd_wedding' );
		if ( $p && get_post_meta( $p->ID, '_wd_sample', true ) && (int) get_post_meta( $p->ID, '_wd_views_base', true ) === $v[0] ) {
			update_post_meta( $p->ID, '_wd_views_base', $v[1] );
		}
	}
}

/**
 * 1.5.1: no direct booking & no escrow. The terms page & the guest-travel blog post get the new copy,
 * but only if nobody edited them. Runs once.
 */
function wd_upgrade_151() {
	if ( get_option( 'wd_up_151' ) ) {
		return;
	}
	update_option( 'wd_up_151', 1 );
	$saved = get_option( 'wd_settings' );
	if ( is_array( $saved ) ) {
		foreach ( array( 'features_premium', 'features_society' ) as $k ) {
			if ( isset( $saved[ $k ] ) && in_array( 'templates', (array) $saved[ $k ], true ) && ! in_array( 'receipts', (array) $saved[ $k ], true ) ) {
				$saved[ $k ][] = 'receipts';
			}
		}
		update_option( 'wd_settings', $saved );
		wd_settings( true );
	}
	$norm = static function ( $h ) {
		return md5( trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( html_entity_decode( (string) $h, ENT_QUOTES, 'UTF-8' ) ) ) ) );
	};
	$made  = (array) get_option( 'wd_made_pages', array() );
	$terms = empty( $made['terms'] ) ? null : get_post( (int) $made['terms'] );
	if ( $terms && $norm( $terms->post_content ) === '463ce8969a60f27271fe50b9640bb234' ) {
		wp_update_post(
			array(
				'ID'           => $terms->ID,
				'post_content' => wd_page_copy( 'terms' ),
			)
		);
	}
	$post = get_page_by_path( 'booking-apartments-rides-for-wedding-guests-without-the-stress', OBJECT, 'post' );
	if ( $post && $norm( $post->post_content ) === 'fe95cd0523e8891c768804aee904d49a' ) {
		foreach ( wd_starter_posts() as $p ) {
			if ( false !== strpos( $p['content'], 'Chat, then pay the seller' ) ) {
				wp_update_post(
					array(
						'ID'           => $post->ID,
						'post_excerpt' => $p['excerpt'],
						'post_content' => $p['content'],
					)
				);
			}
		}
	}
	// Payments held before 1.5.1 keep their release date (HQ › Marketplace can release them sooner).
}

/**
 * 1.7.0 (Ayoola, 2026-11-17): verified badges for AbujaCarhire, ViaskyNigeria & IX-Projects (the starter
 * "Lekki Nest Short-lets" is renamed IX-Projects, only if its name was never changed), & the extra
 * "Community" menu item goes, since "Anonymous community" is there. Runs once.
 */
function wd_upgrade_170() {
	if ( get_option( 'wd_up_170' ) ) {
		return;
	}
	update_option( 'wd_up_170', 1 );
	foreach ( array( 'abujacarhire', 'viaskynigeria', 'lekki-nest' ) as $login ) {
		$u = get_user_by( 'login', 'sample-' . $login );
		$p = $u ? wd_partner( $u->ID ) : array();
		if ( ! $p ) {
			continue;
		}
		$p['verified'] = 1;
		if ( 'lekki-nest' === $login && 'Lekki Nest Short-lets' === $p['business'] ) {
			$p['business'] = 'IX-Projects';
			$old           = (string) get_user_meta( $u->ID, 'wd_partner_slug', true );
			if ( $old && ! wd_partner_by_slug( 'ix-projects' ) ) {
				update_user_meta( $u->ID, 'wd_partner_slug_old', $old );
				update_user_meta( $u->ID, 'wd_partner_slug', 'ix-projects' );
			}
			wp_update_user(
				array(
					'ID'           => $u->ID,
					'display_name' => 'IX-Projects',
				)
			);
		}
		wd_partner_put( $u->ID, $p );
	}
	foreach ( wp_get_nav_menus() as $menu ) {
		$items = (array) wp_get_nav_menu_items( $menu->term_id, array( 'post_status' => 'any' ) );
		$has   = false;
		foreach ( $items as $it ) {
			$has = $has || false !== stripos( (string) $it->title, 'anonymous community' );
		}
		foreach ( $items as $it ) {
			if ( $has && 'community' === strtolower( trim( wp_strip_all_tags( (string) $it->title ) ) ) ) {
				wp_delete_post( $it->ID, true );
			}
		}
	}
}
