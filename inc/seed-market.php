<?php
/**
 * Starter marketplace: partners (incl. AbujaCarhire & ViaskyNigeria), their listings, a few test
 * bookings & one chat, so the marketplace looks alive from day one. Everything carries _wd_sample &
 * goes with HQ › Overview › Remove all starter content.
 *
 * AbujaCarhire & ViaskyNigeria are real businesses we have not checked prices with, so their
 * listings show "Contact for price" & chats with them reach HQ.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

/**
 * Starter partners: login => [business, sector, city, country, about, listings, extras].
 * Listing: [title, unit, price, capacity, city, country, art, about, features, photo or photos, price mode].
 * Extras: ask (every listing is "Contact for price"), slug, hours, areas, portfolio [photo, caption].
 *
 * @return array
 */
function wd_seed_partners() {
	return array(
		'abujacarhire'  => array(
			'AbujaCarhire',
			'rides',
			'Abuja',
			'Nigeria',
			'Car hire in Abuja for weddings: bridal cars, SUVs with drivers, coaches for guests & airport pick-ups.',
			array(
				array( 'Black Range Rover Sport bridal car', 'day', 180000, 3, 'Abuja', 'Nigeria', 'car', 'A clean, black Range Rover Sport to bring the bride to the ceremony in style, then take the couple to the reception.', array( 'Chauffeur in a suit', 'Ribbons & flowers on request', 'Up to 10 hours a day', 'Fuel within Abuja' ), 'photo:listing-range-rover' ),
				array( 'Mercedes-Benz G-Class with driver', 'day', 250000, 4, 'Abuja', 'Nigeria', 'car', 'A G-Wagon for the couple, parents or VIP guests, with an experienced driver who knows every venue in town.', array( 'Professional driver', 'Fuel within Abuja', 'Air conditioning', 'Up to 12 hours a day' ), 'photo:listing-g-class' ),
				array( 'Luxury coach for up to 50 guests', 'day', 280000, 50, 'Abuja', 'Nigeria', 'danfo', 'Move guests, the bridal train or family from the ceremony to the reception together, on time & in one go.', array( 'Driver & fuel within Abuja', 'Air conditioning', 'Room for outfits & gele boxes' ), 'photo:listing-coach' ),
				array( 'Airport pick-up from Nnamdi Azikiwe Airport', 'trip', 35000, 4, 'Abuja', 'Nigeria', 'plane', 'Meet & greet for guests flying in, then a smooth ride to their hotel or apartment.', array( 'Driver waits at arrivals with a name sign', 'Help with luggage', 'Flight tracking, so late flights are fine' ), 'photo:listing-airport' ),
			),
			array( 'ask' => true, 'verified' => 1 ),
		),
		'viaskynigeria' => array(
			'ViaskyNigeria',
			'travel',
			'',
			'Nigeria',
			'Honeymoon packages, flights & visa help for couples & their guests.',
			array(
				array( 'Zanzibar honeymoon, 5 nights for two', 'package', 2450000, 2, 'Zanzibar', 'Tanzania', 'plane', 'White sand, warm water & slow mornings. A beachfront stay with a spice tour & a sunset dhow cruise.', array( 'Return flights from Lagos or Abuja', '5 nights beachfront hotel with breakfast', 'Airport transfers', 'Spice tour & sunset dhow cruise' ), 'photo:listing-zanzibar' ),
				array( 'Dubai honeymoon, 4 nights for two', 'package', 3100000, 2, 'Dubai', 'UAE', 'globe', 'City lights, desert dunes & a dinner cruise on the marina.', array( 'Return flights', '4 nights 4-star hotel with breakfast', 'Desert safari with dinner', 'Visa help' ), 'photo:listing-dubai' ),
				array( 'Cape Town honeymoon, 6 nights for two', 'package', 3650000, 2, 'Cape Town', 'South Africa', 'plane', 'Table Mountain, wine farms & Cape Point, at an easy pace.', array( 'Return flights', '6 nights boutique hotel', 'Winelands day trip', 'Cape Point tour' ), 'photo:listing-cape-town' ),
				array( 'Flight & visa help for guests flying in', 'person', 25000, 0, 'Lagos', 'Nigeria', 'globe', 'For guests coming home for the wedding: we find the best fares & help with visa letters & bookings.', array( 'Best-fare search', 'Visa letter help', 'Booking changes handled for you' ), 'photo:listing-flights' ),
			),
			array( 'ask' => true, 'verified' => 1 ),
		),
		'lekki-nest'    => array(
			'IX-Projects',
			'stays',
			'Lagos',
			'Nigeria',
			'Serviced apartments in Lekki & Ikoyi for couples, families & guests in town for the wedding.',
			array(
				array( '3-bed apartment in Lekki Phase 1', 'night', 185000, 6, 'Lagos', 'Nigeria', 'skyline', 'A bright, serviced apartment close to the big Lekki venues. Space for the bride, her mum & the make-up artist on the morning.', array( '24-hour power & security', 'Fast Wi-Fi', 'Full kitchen', 'Parking for 2 cars' ), 'photo:listing-apartment' ),
				array( 'Studio in Ikoyi near the wedding venues', 'night', 85000, 2, 'Lagos', 'Nigeria', 'skyline', 'A quiet studio for one or two guests, ten minutes from most Ikoyi & Victoria Island venues.', array( '24-hour power', 'Wi-Fi', 'Smart TV', 'Daily cleaning' ), 'photo:listing-studio' ),
				array( '5-bed family house in Lekki', 'night', 350000, 12, 'Lagos', 'Nigeria', 'house', 'Keep the whole family under one roof for the wedding week, with room to dress, rehearse & eat together.', array( '24-hour power & security', 'Garden for photos', 'Cook on request', 'Parking for 4 cars' ), 'photo:listing-family-house' ),
			),
			array( 'verified' => 1, 'slug' => 'ix-projects' ),
		),
		'cedar-court'   => array(
			'Cedar Court Villas',
			'stays',
			'Accra',
			'Ghana',
			'Private villas in Accra for wedding parties & families flying in.',
			array(
				array( '4-bed villa with pool in East Legon', 'night', 420000, 8, 'Accra', 'Ghana', 'house', 'A private villa for the couple & their closest family, with a pool for the after-party.', array( 'Pool', 'Backup power', 'Housekeeping', 'Airport pick-up on request' ), 'photo:listing-villa' ),
			),
		),
		'ebi-lace'      => array(
			'Ẹbí Lace & Gele',
			'asoebi',
			'Lagos',
			'Nigeria',
			'We source asoebi for Nigerian & African weddings: lace, aso-oke, ankara & gele for the couple, the family & every guest. Send us your colours, we match them, buy in bulk, then sort & label each guest’s fabric by name & send it to them, in Nigeria or abroad.',
			array(
				array( 'Lace asoebi with matching gele', 'person', 75000, 0, 'Lagos', 'Nigeria', 'fabric', 'Five yards of lace & a matching gele for each guest, in your colours. Send us a photo or a colour code & we match it before you pay for the lot.', array( '5 yards of lace per guest', 'Matching gele', 'Colour matched to your sample', 'Packed & labelled with each guest’s name' ), array( 'photo:listing-olive-gele', 'photo:listing-coral-gele' ) ),
				array( 'Hand-woven aso-oke for the couple', 'package', 350000, 2, 'Lagos', 'Nigeria', 'fabric', 'Aso-oke woven to order for the bride & groom in your colours: iro, buba, gele & ipele for the bride, agbada, sokoto & fila for the groom.', array( 'Woven to order in your colours', 'Bride: iro, buba, gele & ipele', 'Groom: agbada, buba, sokoto & fila', 'Ready in 3 to 4 weeks' ), array( 'photo:listing-couple-olive', 'photo:listing-couple-magenta' ) ),
				array( 'Ankara asoebi for family & friends', 'person', 18500, 0, 'Lagos', 'Nigeria', 'fabric', 'Bright, good-quality ankara for a big crowd on a budget. Six yards per guest, with plenty of prints to pick from.', array( '6 yards per guest', 'Wax prints & local brands', 'Lower price from 30 guests' ), array( 'photo:listing-ankara-rolls', 'photo:listing-wax-prints' ) ),
				array( 'Gele for the bride, the mums & the bridal train', 'person', 12000, 0, 'Lagos', 'Nigeria', 'fabric', 'Gele in your colours for the bride, both mothers & the bridal train, with a gele stylist on the morning if you are in Lagos.', array( 'Damask, aso-oke or sego gele', 'Colour matched', 'Gele stylist on the morning (Lagos)' ), array( 'photo:listing-red-gele', 'photo:listing-green-gele' ) ),
				array( 'Bulk asoebi for 50+ guests, shipped worldwide', 'package', 0, 0, 'Lagos', 'Nigeria', 'fabric', 'For big weddings: we buy your fabric in bulk, sort & label it for each guest & send it to them in Nigeria, the UK, the US & Canada. Tell us how many guests & your colours in the chat & we send you a price.', array( 'Bulk fabric at market prices', 'Sorted & labelled for each guest', 'Delivery in Nigeria & abroad', 'Price sent to you in the chat' ), array( 'photo:listing-fabric-stall', 'photo:listing-fabric-close', 'photo:listing-guests-magenta' ), 'ask' ),
			),
			array(
				'slug'      => 'ebi-lace-gele',
				'cover'     => 'photo:listing-fabric-stall',
				'hours'     => 'Mon–Sat, 9am–6pm',
				'areas'     => 'All of Nigeria; we send to the UK, US & Canada',
				'portfolio' => array(
					array( 'photo:listing-fan-lady', 'Lace asoebi with a coral gele' ),
					array( 'photo:listing-guests-magenta', 'Magenta asoebi for the bride’s friends' ),
					array( 'photo:listing-couple-magenta', 'Matching aso-oke for a couple' ),
					array( 'photo:listing-green-gele', 'Green gele for the bridal train' ),
					array( 'photo:listing-wax-prints', 'Ankara prints for family & friends' ),
					array( 'photo:listing-fabric-close', 'Lace, ready to cut & pack' ),
				),
			),
		),
	);
}

/**
 * Make one starter partner, their profile & their listings.
 *
 * @param string $login Login without the starter prefix.
 * @param array  $p     From wd_seed_partners().
 * @param int    $order Listing order, counted up.
 * @return array title => [listing ID, partner ID, price]
 */
function wd_seed_partner( $login, $p, &$order ) {
	$uid = username_exists( 'sample-' . $login );
	if ( ! $uid ) {
		$uid = wp_insert_user(
			array(
				'user_login'    => 'sample-' . $login,
				'user_nicename' => $login,
				'user_email'    => $login . '@example.com',
				'user_pass'     => wp_generate_password( 24 ),
				'display_name'  => $p[0],
				'role'          => 'couple',
			)
		);
	}
	if ( ! $uid || is_wp_error( $uid ) ) {
		return array();
	}
	$x = $p[6] ?? array();
	update_user_meta( $uid, '_wd_sample', 1 );
	wd_partner_put(
		$uid,
		array(
			'status'    => 'approved',
			'business'  => $p[0],
			'sector'    => $p[1],
			'city'      => $p[2],
			'country'   => $p[3],
			'phone'     => '',
			'about'     => $p[4],
			'website'   => '',
			'applied'   => gmdate( 'Y-m-d H:i:s', time() - 60 * DAY_IN_SECONDS ),
			'approved'  => gmdate( 'Y-m-d H:i:s', time() - 58 * DAY_IN_SECONDS ),
			'hours'     => $x['hours'] ?? '',
			'areas'     => $x['areas'] ?? '',
			'logo'      => 0,
			'cover'     => $x['cover'] ?? 0,
			'verified'  => (int) ( $x['verified'] ?? 0 ),
			'portfolio' => array_map(
				static function ( $ph ) {
					return array(
						'img' => $ph[0],
						'cap' => $ph[1],
					);
				},
				$x['portfolio'] ?? array()
			),
		)
	);
	if ( ! empty( $x['slug'] ) ) {
		update_user_meta( $uid, 'wd_partner_slug', $x['slug'] );
	}
	wd_partner_slug( $uid );
	$ids = array();
	foreach ( $p[5] as $l ) {
		$lid = wp_insert_post(
			array(
				'post_type'   => 'wd_listing',
				'post_status' => 'publish',
				'post_title'  => $l[0],
				'post_author' => $uid,
				'menu_order'  => ++$order,
			)
		);
		if ( ! $lid || is_wp_error( $lid ) ) {
			continue;
		}
		update_post_meta( $lid, '_wd_sample', 1 );
		wd_listing_put(
			$lid,
			array(
				'sector'     => $p[1],
				'unit'       => $l[1],
				'price'      => $l[2],
				'capacity'   => $l[3],
				'city'       => $l[4],
				'country'    => $l[5],
				'art'        => $l[6],
				'about'      => $l[7],
				'features'   => $l[8],
				'photos'     => $l[9] ? (array) $l[9] : array(),
				'price_mode' => ( ! empty( $x['ask'] ) || 'ask' === ( $l[10] ?? '' ) ) ? 'ask' : 'fixed',
			)
		);
		$ids[ $l[0] ] = array( (int) $lid, (int) $uid, $l[2] );
	}
	return $ids;
}

/**
 * The starter chat: a couple asks Ẹbí Lace & Gele about asoebi & gets a payment link.
 */
function wd_seed_chat() {
	global $wpdb;
	$who = get_user_by( 'login', 'sample-bisola-femi' );
	$pid = username_exists( 'sample-ebi-lace' );
	$lid = 0;
	foreach ( get_posts( array( 'post_type' => 'wd_listing', 'author' => (int) $pid, 'numberposts' => 10, 'post_status' => 'publish' ) ) as $l ) {
		if ( wd_listing_asks( wd_listing( $l->ID ) ) ) {
			$lid = $l->ID;
		}
	}
	if ( ! $who || ! $pid || ! $lid ) {
		return;
	}
	$at   = static function ( $mins ) {
		return gmdate( 'Y-m-d H:i:s', time() - $mins * MINUTE_IN_SECONDS );
	};
	$rows = array(
		array( 'user', 'Hello! We need asoebi for about 60 guests, 12 of them in London. Our colours are emerald & gold. Can you send us a price?', 0, $at( 190 ) ),
		array( 'partner', 'Hello Bisola, congratulations! Yes we can: lace with a matching gele for 60 guests, with the 12 London parcels sent by courier. Here is the payment link.', 0, $at( 150 ) ),
		array( 'partner', 'Tell us if you want to change anything before you pay.', 4650000, $at( 149 ) ),
	);
	$items = array(
		array( 't' => 'Lace & matching gele for 60 guests', 'a' => 3900000 ),
		array( 't' => 'Delivery to your address in Lagos', 'a' => 150000 ),
		array( 't' => 'Courier to London (12 parcels)', 'a' => 600000 ),
	);
	foreach ( $rows as $r ) {
		$wpdb->insert(
			wd_t( 'chats' ),
			array(
				'partner_id'   => (int) $pid,
				'user_id'      => $who->ID,
				'sender'       => $r[0],
				'author_id'    => 'user' === $r[0] ? $who->ID : (int) $pid,
				'listing_id'   => $lid,
				'body'         => $r[1],
				'offer_amount' => $r[2],
				'offer_start'  => $r[2] ? gmdate( 'Y-m-d', strtotime( wp_date( 'Y-m-d' ) . ' 00:00:00 UTC' ) + 30 * DAY_IN_SECONDS ) : '',
				'offer_status' => $r[2] ? 'open' : '',
				'offer_items'  => $r[2] ? wp_json_encode( $items ) : null,
				'offer_code'   => $r[2] ? wd_offer_code() : '',
				'seen'         => 1,
				'created'      => $r[3],
			)
		);
	}
}

/**
 * Add the asoebi partner to sites that already have the starter marketplace (once).
 */
function wd_seed_asoebi() {
	if ( get_option( 'wd_asoebi_seeded' ) ) {
		return;
	}
	update_option( 'wd_asoebi_seeded', 1 );
	if ( get_option( 'wd_samples_removed' ) || username_exists( 'sample-ebi-lace' ) ) {
		return;
	}
	$order = 100;
	wd_seed_partner( 'ebi-lace', wd_seed_partners()['ebi-lace'], $order );
	wd_seed_chat();
}

/**
 * Make the starter marketplace (once).
 */
function wd_seed_market() {
	if ( get_option( 'wd_market_seeded' ) ) {
		return;
	}
	update_option( 'wd_market_seeded', 1 );
	global $wpdb;
	$order = 0;
	$ids   = array();
	foreach ( wd_seed_partners() as $login => $p ) {
		$ids = array_merge( $ids, wd_seed_partner( $login, $p, $order ) );
	}
	update_option( 'wd_asoebi_seeded', 1 );

	// A few test bookings by the starter couples, so the dashboards have something to show.
	$fee   = (float) wd_opt( 'market_fee' );
	$day   = static function ( $n ) {
		return gmdate( 'Y-m-d', strtotime( wp_date( 'Y-m-d' ) . ' 00:00:00 UTC' ) + $n * DAY_IN_SECONDS );
	};
	$books = array(
		array( 'amina-yusuf', 'Mercedes-Benz G-Class with driver', 18, 19, 2, 2, 'held' ),
		array( 'amina-yusuf', 'Airport pick-up from Nnamdi Azikiwe Airport', 17, '', 1, 3, 'held' ),
		array( 'tolani-kayode', '3-bed apartment in Lekki Phase 1', 12, 15, 3, 1, 'held' ),
		array( 'tolani-kayode', 'Zanzibar honeymoon, 5 nights for two', 16, '', 1, 1, 'held' ),
		array( 'chiamaka-obinna', 'Dubai honeymoon, 4 nights for two', -26, '', 1, 1, 'released' ),
		array( 'chiamaka-obinna', 'Luxury coach for up to 50 guests', -31, -31, 1, 1, 'released' ),
		array( 'efua-kojo', '4-bed villa with pool in East Legon', -12, -10, 2, 1, 'released' ),
	);
	foreach ( $books as $b ) {
		$who = get_user_by( 'login', 'sample-' . $b[0] );
		$wed = get_page_by_path( $b[0], OBJECT, 'wd_wedding' );
		if ( ! $who || empty( $ids[ $b[1] ] ) ) {
			continue;
		}
		list( $lid, $pid, $price ) = $ids[ $b[1] ];
		$amount = $price * $b[4] * $b[5];
		$cut    = (int) round( $amount * $fee / 100 );
		$paid   = gmdate( 'Y-m-d H:i:s', time() - wp_rand( 3, 20 ) * DAY_IN_SECONDS );
		$wpdb->insert(
			wd_t( 'bookings' ),
			array(
				'listing_id'  => $lid,
				'partner_id'  => $pid,
				'user_id'     => $who->ID,
				'wedding_id'  => $wed ? (int) $wed->ID : 0,
				'name'        => $who->display_name,
				'email'       => $who->user_email,
				'phone'       => '',
				'start_date'  => $day( $b[2] ),
				'end_date'    => '' === $b[3] ? '' : $day( $b[3] ),
				'qty'         => $b[5],
				'units'       => $b[4],
				'amount'      => $amount,
				'fee'         => $cut,
				'net'         => $amount - $cut,
				'currency'    => 'NGN',
				'reference'   => 'WD' . strtoupper( wp_generate_password( 10, false ) ),
				'status'      => $b[6],
				'note'        => '',
				'log'         => '',
				'test'        => 1,
				'created'     => $paid,
				'paid_at'     => $paid,
				'released_at' => 'released' === $b[6] ? gmdate( 'Y-m-d H:i:s', time() - 5 * DAY_IN_SECONDS ) : null,
			)
		);
	}
	wd_seed_chat();
}
