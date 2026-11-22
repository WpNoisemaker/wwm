<?php
/**
 * Couple account: saving everything from the front-end dashboard (admin-post.php?action=wd_save).
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

/**
 * The wedding the dashboard is working on (?wid=ID, else the user's newest).
 *
 * @return int
 */
function wd_current_wedding() {
	$ids = wd_user_weddings( get_current_user_id() );
	// phpcs:ignore WordPress.Security.NonceVerification
	$w = isset( $_GET['wid'] ) ? (int) $_GET['wid'] : 0;
	if ( $w && ( in_array( $w, $ids, true ) || ( current_user_can( 'manage_options' ) && 'wd_wedding' === get_post_type( $w ) ) ) ) {
		return $w;
	}
	return $ids ? (int) $ids[0] : 0;
}

/**
 * Gift credit from a form: who gave the Premium page & an optional link to one of their social pages.
 *
 * @param array $p Unslashed POST.
 * @return array
 */
function wd_gift_from_post( $p ) {
	$link = esc_url_raw( trim( (string) ( $p['gift_link'] ?? '' ) ), array( 'http', 'https' ) );
	return array(
		'by'   => mb_substr( sanitize_text_field( $p['gift_by'] ?? '' ), 0, 80 ),
		'link' => $link,
	);
}

/**
 * Rows from a repeater (skips the hidden template row).
 *
 * @param array  $post   Posted data.
 * @param string $key    Field name.
 * @param array  $fields Fields per row.
 * @param int    $max    Most rows.
 * @return array
 */
function wd_rows( $post, $key, $fields, $max = 40 ) {
	$out = array();
	if ( empty( $post[ $key ] ) || ! is_array( $post[ $key ] ) ) {
		return $out;
	}
	foreach ( $post[ $key ] as $rk => $row ) {
		if ( ! is_array( $row ) || '__KEY__' === $rk ) {
			continue;
		}
		$clean = array();
		foreach ( $fields as $f ) {
			$v           = isset( $row[ $f ] ) ? $row[ $f ] : '';
			$clean[ $f ] = in_array( $f, array( 'text', 'line', 'note', 'message', 'plan' ), true ) ? sanitize_textarea_field( $v ) : sanitize_text_field( $v );
		}
		$out[ sanitize_key( $rk ) ] = $clean;
		if ( count( $out ) >= $max ) {
			break;
		}
	}
	return $out;
}

/**
 * Uploaded file for a repeater row (name="{$key}_file[rowkey]").
 *
 * @param string $key Group.
 * @param string $rk  Row key.
 * @param int    $id  Wedding.
 * @return int Attachment ID or 0.
 */
function wd_row_upload( $key, $rk, $id ) {
	// phpcs:disable WordPress.Security.NonceVerification
	$f = $key . '_file';
	if ( empty( $_FILES[ $f ]['name'][ $rk ] ) || ! empty( $_FILES[ $f ]['error'][ $rk ] ) ) {
		return 0;
	}
	$file = array(
		'name'     => sanitize_file_name( wp_unslash( $_FILES[ $f ]['name'][ $rk ] ) ),
		'type'     => sanitize_mime_type( wp_unslash( $_FILES[ $f ]['type'][ $rk ] ) ),
		'tmp_name' => $_FILES[ $f ]['tmp_name'][ $rk ], // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		'error'    => (int) $_FILES[ $f ]['error'][ $rk ],
		'size'     => (int) $_FILES[ $f ]['size'][ $rk ],
	);
	// phpcs:enable
	$att = wd_handle_image_upload( $file, $id );
	return is_wp_error( $att ) ? 0 : $att;
}

/**
 * Single uploaded file field.
 *
 * @param string $name Field.
 * @param int    $id   Wedding.
 * @return int
 */
function wd_upload_field( $name, $id ) {
	// phpcs:disable WordPress.Security.NonceVerification
	if ( empty( $_FILES[ $name ]['name'] ) || ! empty( $_FILES[ $name ]['error'] ) ) {
		return 0;
	}
	$file             = $_FILES[ $name ]; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$file['name']     = sanitize_file_name( $file['name'] );
	// phpcs:enable
	$att = wd_handle_image_upload( $file, $id );
	return is_wp_error( $att ) ? 0 : $att;
}

/**
 * Valid hex colour or fallback.
 *
 * @param string $c Colour.
 * @param string $f Fallback.
 * @return string
 */
function wd_hex( $c, $f ) {
	$c = sanitize_hex_color( $c );
	return $c ? $c : $f;
}

/**
 * Redirect back to the dashboard.
 *
 * @param string $tab  Tab.
 * @param string $note Notice key.
 * @param array  $args More args.
 */
function wd_back( $tab, $note = 'saved', $args = array() ) {
	wp_safe_redirect( wd_account_url( $tab, array_merge( $args, array( 'wdn' => $note ) ) ) );
	exit;
}

add_action( 'admin_post_wd_save', 'wd_account_save' );
add_action( 'admin_post_nopriv_wd_save', static function () {
	wp_safe_redirect( wd_url( 'sign-in' ) );
	exit;
} );

/**
 * Save handler.
 */
function wd_account_save() {
	$p    = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification
	$part = isset( $p['part'] ) ? sanitize_key( $p['part'] ) : '';
	$id   = isset( $p['wid'] ) ? (int) $p['wid'] : 0;
	if ( ! isset( $p['_wpnonce'] ) || ! wp_verify_nonce( $p['_wpnonce'], 'wd_save_' . $part . '_' . $id ) ) {
		wd_back( '', 'denied' );
	}
	$uid = get_current_user_id();

	// Account-level parts.
	if ( 'profile' === $part ) {
		$email = sanitize_email( $p['email'] ?? '' );
		$owner = email_exists( $email );
		if ( $email && $owner && (int) $owner !== $uid ) {
			wd_back( 'profile', 'email' );
		}
		$me = wp_get_current_user();
		if ( $email && strtolower( $email ) !== strtolower( $me->user_email ) && ! wp_check_password( $p['current'] ?? '', $me->user_pass, $uid ) ) {
			wd_back( 'profile', 'email_pw' );
		}
		wp_update_user(
			array(
				'ID'           => $uid,
				'first_name'   => sanitize_text_field( $p['first_name'] ?? '' ),
				'last_name'    => sanitize_text_field( $p['last_name'] ?? '' ),
				'display_name' => trim( sanitize_text_field( ( $p['first_name'] ?? '' ) . ' ' . ( $p['last_name'] ?? '' ) ) ),
				'user_email'   => $email ? $email : wp_get_current_user()->user_email,
			)
		);
		update_user_meta( $uid, 'wd_phone', sanitize_text_field( $p['phone'] ?? '' ) );
		update_user_meta( $uid, 'wd_country', sanitize_text_field( $p['country'] ?? '' ) );
		wd_back( 'profile' );
	}
	if ( 'password' === $part ) {
		$u = wp_get_current_user();
		if ( ! wp_check_password( $p['current'] ?? '', $u->user_pass, $u->ID ) || strlen( $p['new'] ?? '' ) < 8 || ( $p['new'] ?? '' ) !== ( $p['again'] ?? '' ) ) {
			wd_back( 'profile', 'pwbad' );
		}
		wp_set_password( $p['new'], $u->ID );
		wp_set_current_user( $u->ID );
		wp_set_auth_cookie( $u->ID, true, is_ssl() );
		wd_back( 'profile', 'password' );
	}
	if ( 'new' === $part ) {
		$d             = wd_blank();
		$d['p1']       = sanitize_text_field( $p['p1'] ?? '' );
		$d['p2']       = sanitize_text_field( $p['p2'] ?? '' );
		$d['date']     = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $p['date'] ?? '' ) ? $p['date'] : '';
		$d['city']     = sanitize_text_field( $p['city'] ?? '' );
		$d['country']  = sanitize_text_field( $p['country'] ?? '' );
		$d['currency'] = array_key_exists( $p['currency'] ?? '', wd_currencies() ) ? $p['currency'] : 'NGN';
		$d['hashtag']  = ltrim( sanitize_text_field( $p['hashtag'] ?? '' ), '#' );
		if ( ! $d['p1'] || ! $d['p2'] ) {
			wd_back( 'new', 'denied' );
		}
		$d['invite']['message'] = __( 'With joy in our hearts, we invite you to celebrate our wedding with us.', 'wwmd' );
		$want = in_array( $p['plan'] ?? '', wd_paid_plans(), true ) ? $p['plan'] : '';
		if ( $want ) {
			$d['gift'] = wd_gift_from_post( $p );
			// The design picked at sign-up: its starting colours, fonts & layout. Pro uses the colours & fonts on Classic.
			$tpls = wd_templates();
			$tk   = sanitize_key( $p['template'] ?? '' );
			if ( isset( $tpls[ $tk ] ) ) {
				$v           = $tpls[ $tk ][2];
				$d['design'] = array(
					'template' => $tk,
					'primary'  => $v[0],
					'accent'   => $v[1],
					'bg'       => $v[2],
					'ink'      => $v[3],
					'head'     => $v[4],
					'body'     => $v[5],
					'pattern'  => $v[6],
					'hero'     => $v[7],
					'corners'  => $v[8],
				) + (array) $d['design'];
			}
		}
		$slug = sanitize_title( $d['hashtag'] ? $d['hashtag'] : $d['p1'] . '-and-' . $d['p2'] );
		$id   = wp_insert_post(
			array(
				'post_type'   => 'wd_wedding',
				'post_status' => 'publish',
				'post_title'  => wd_names( $d ),
				'post_name'   => $slug,
				'post_author' => $uid,
			)
		);
		if ( ! $id || is_wp_error( $id ) ) {
			wd_back( 'new', 'denied' );
		}
		update_post_meta( $id, '_wd_plan', 'free' );
		if ( ! empty( $p['style'] ) ) {
			$style = sanitize_title( $p['style'] );
			wp_set_object_terms( $id, $style, 'wd_style' );
		}
		wd_put( $id, $d );
		wd_refresh( $id );
		if ( $want ) {
			wd_back( 'plan', 'created', array( 'wid' => $id, 'tier' => $want ) );
		}
		wd_back( 'page', 'created', array( 'wid' => $id ) );
	}

	// Wedding-level parts need ownership.
	if ( ! $id || ! wd_owns( $id ) ) {
		wd_back( '', 'denied' );
	}
	$d    = wd_get( $id );
	$args = array( 'wid' => $id );

	switch ( $part ) {
		case 'basics':
			foreach ( array( 'p1', 'p2', 'tagline', 'city', 'country', 'venue', 'time' ) as $k ) {
				$d[ $k ] = sanitize_text_field( $p[ $k ] ?? '' );
			}
			$d['bride']    = 'p2' === ( $p['bride'] ?? '' ) ? 'p2' : 'p1';
			$d['hashtag']  = ltrim( sanitize_text_field( $p['hashtag'] ?? '' ), '#' );
			$d['date']     = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $p['date'] ?? '' ) ? $p['date'] : $d['date'];
			$d['currency'] = array_key_exists( $p['currency'] ?? '', wd_currencies() ) ? $p['currency'] : $d['currency'];
			$d['invite']   = array(
				'hosts'   => sanitize_text_field( $p['hosts'] ?? '' ),
				'message' => sanitize_textarea_field( $p['message'] ?? '' ),
			);
			$d['rsvp']     = array(
				'deadline' => preg_match( '/^\d{4}-\d{2}-\d{2}$/', $p['deadline'] ?? '' ) ? $p['deadline'] : '',
				'max'      => min( 10, max( 1, (int) ( $p['max'] ?? 2 ) ) ),
			);
			$cover = wd_upload_field( 'cover_file', $id );
			if ( $cover ) {
				$d['cover'] = $cover;
				set_post_thumbnail( $id, $cover );
			}
			$photo = wd_upload_field( 'photo_file', $id );
			if ( $photo ) {
				$d['photo'] = $photo;
			}
			if ( ! empty( $p['remove_cover'] ) ) {
				$d['cover'] = '';
				delete_post_thumbnail( $id );
			}
			$styles = array_filter( array_map( 'sanitize_title', (array) ( $p['style'] ?? array() ) ) );
			wp_set_object_terms( $id, $styles, 'wd_style' );
			if ( isset( $p['gift_by'] ) ) {
				$d['gift'] = wd_gift_from_post( $p );
			}
			wp_update_post(
				array(
					'ID'          => $id,
					'post_status' => empty( $p['hidden'] ) ? 'publish' : 'private',
				)
			);
			break;

		case 'sections':
			$d['sections'] = array();
			foreach ( array_keys( wd_features() ) as $f ) {
				$d['sections'][ $f ] = empty( $p['sections'][ $f ] ) ? 0 : 1;
			}
			break;

		case 'events':
			$d['events'] = array();
			foreach ( wd_rows( $p, 'events', array( 'type', 'title', 'kind', 'date', 'time', 'venue', 'address', 'dress', 'c1', 'c2', 'private', 'note', 'plan' ) ) as $r ) {
				if ( '' === $r['title'] ) {
					continue;
				}
				$r['type']     = array_key_exists( $r['type'], wd_program_types() ) ? $r['type'] : '';
				$r['date']     = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $r['date'] ) ? $r['date'] : '';
				$r['private']  = $r['private'] ? 1 : 0;
				$r['c1']       = wd_hex( $r['c1'], '' );
				$r['c2']       = wd_hex( $r['c2'], '' );
				$r['kind']     = mb_substr( $r['kind'], 0, 40 );
				$r['note']     = mb_substr( $r['note'], 0, 300 );
				$r['plan']     = mb_substr( $r['plan'], 0, 1500 );
				$d['events'][] = $r;
			}
			break;

		case 'story':
			$old        = array();
			$d['story'] = array();
			foreach ( wd_rows( $p, 'story', array( 'when', 'title', 'text', 'image' ) ) as $rk => $r ) {
				if ( '' === $r['title'] && '' === $r['text'] ) {
					continue;
				}
				$up         = wd_row_upload( 'story', $rk, $id );
				$r['image'] = $up ? $up : ( is_numeric( $r['image'] ) || 0 === strpos( $r['image'], 'photo:' ) ? $r['image'] : '' );
				$d['story'][] = $r;
			}
			break;

		case 'party':
			$d['party'] = array();
			foreach ( wd_rows( $p, 'party', array_merge( array( 'name', 'role', 'side', 'line', 'career', 'status', 'image', 'nick', 'likes' ), array_keys( wd_socials() ) ) ) as $rk => $r ) {
				if ( '' === $r['name'] ) {
					continue;
				}
				$up           = wd_row_upload( 'party', $rk, $id );
				$r['image']   = $up ? $up : $r['image'];
				$d['party'][] = wd_squad_clean( $r );
			}
			break;

		case 'squad':
			$note = wd_squad_action( $id, $p, $d );
			wd_put( $id, $d );
			wd_back( 'page', $note, array_merge( $args, array( 'part' => 'party' ) ) );
			break;

		case 'quiz':
			$d['quiz'] = array();
			foreach ( wd_rows( $p, 'quiz', array( 'q', 'a0', 'a1', 'a2', 'a3', 'c' ), 15 ) as $r ) {
				$answers = array_values( array_filter( array( $r['a0'], $r['a1'], $r['a2'], $r['a3'] ), 'strlen' ) );
				if ( '' === $r['q'] || count( $answers ) < 2 ) {
					continue;
				}
				$d['quiz'][] = array(
					'q' => $r['q'],
					'a' => $answers,
					'c' => min( count( $answers ) - 1, max( 0, (int) $r['c'] ) ),
				);
			}
			break;

		case 'registry':
			$d['registry'] = array();
			foreach ( wd_rows( $p, 'registry', array( 'id', 'type', 'title', 'note', 'price', 'url', 'store', 'image' ) ) as $rk => $r ) {
				if ( '' === $r['title'] ) {
					continue;
				}
				$up              = wd_row_upload( 'registry', $rk, $id );
				$d['registry'][] = array(
					'id'    => $r['id'] ? sanitize_key( $r['id'] ) : 'r' . strtolower( wp_generate_password( 6, false ) ),
					'type'  => 'fund' === $r['type'] ? 'fund' : 'gift',
					'title' => $r['title'],
					'note'  => $r['note'],
					'price' => (int) preg_replace( '/\D/', '', $r['price'] ),
					'url'   => esc_url_raw( $r['url'] ),
					'store' => $r['store'],
					'image' => $up ? $up : ( is_numeric( $r['image'] ) || 0 === strpos( $r['image'], 'photo:' ) || 0 === strpos( $r['image'], 'art:' ) ? $r['image'] : '' ),
				);
			}
			break;

		case 'vendors':
			$d['vendors'] = array();
			foreach ( wd_rows( $p, 'vendors', array( 'name', 'type', 'ig', 'role' ), 30 ) as $r ) {
				if ( '' === $r['name'] ) {
					continue;
				}
				$vid = wd_find_or_make_vendor( $r['name'], $r['type'], ltrim( $r['ig'], '@' ), $d['city'], $d['country'] );
				if ( $vid ) {
					$d['vendors'][] = array(
						'id'   => $vid,
						'role' => $r['role'],
					);
				}
			}
			break;

		case 'instagram':
			$d['ig']['handle'] = ltrim( sanitize_text_field( $p['handle'] ?? '' ), '@' );
			$posts             = array();
			foreach ( preg_split( '/\s+/', (string) ( $p['posts'] ?? '' ) ) as $u ) {
				$u = wd_ig_post_url( $u );
				if ( $u ) {
					$posts[] = $u;
				}
			}
			$d['ig']['posts'] = array_slice( array_values( array_unique( $posts ) ), 0, 9 );
			if ( ! empty( $p['disconnect'] ) ) {
				delete_post_meta( $id, '_wd_ig_token' );
				delete_post_meta( $id, '_wd_ig_media' );
			}
			break;

		case 'design':
			if ( ! wd_can( $id, 'design' ) ) {
				wd_back( 'plan', 'premium', $args );
			}
			$b           = wd_blank()['design'];
			$fonts       = wd_fonts();
			$d['design'] = array(
				'primary' => wd_hex( $p['primary'] ?? '', $b['primary'] ),
				'accent'  => wd_hex( $p['accent'] ?? '', $b['accent'] ),
				'bg'      => wd_hex( $p['bg'] ?? '', $b['bg'] ),
				'ink'     => wd_hex( $p['ink'] ?? '', $b['ink'] ),
				'head'    => isset( $fonts[ $p['head'] ?? '' ] ) ? $p['head'] : $b['head'],
				'body'    => isset( $fonts[ $p['body'] ?? '' ] ) ? $p['body'] : $b['body'],
				'pattern' => array_key_exists( $p['pattern'] ?? '', wd_patterns() ) ? $p['pattern'] : $b['pattern'],
				'template' => ( array_key_exists( $p['template'] ?? '', wd_templates() ) || ( wd_custom_tpl( $id ) && wd_custom_tpl( $id ) === ( $p['template'] ?? '' ) ) ) ? $p['template'] : wd_template( $id, $d ),
				'hero'     => isset( wd_hero_layouts()[ $p['hero'] ?? '' ] ) ? $p['hero'] : '',
				'corners'  => isset( wd_corner_styles()[ $p['corners'] ?? '' ] ) ? $p['corners'] : '',
				'order'    => array_values( array_intersect( array_map( 'sanitize_key', (array) ( $p['order'] ?? array() ) ), array_keys( wd_page_sections() ) ) ),
			) + $d['design'];
			// Without Premium designs (Pro), keep the template & layout the couple had; only colours & fonts change.
			if ( ! wd_can( $id, 'templates' ) ) {
				$old = wd_get( $id )['design'];
				foreach ( array( 'template', 'hero', 'corners', 'order' ) as $k ) {
					$d['design'][ $k ] = $old[ $k ] ?? ( 'order' === $k ? array() : '' );
				}
			}
			if ( wd_custom_tpl( $id ) && isset( $p['custom'] ) && is_array( $p['custom'] ) ) {
				$d['custom'] = wd_custom_clean( wd_custom_tpl( $id ), $p['custom'], (array) $d['custom'], $id );
			}
			break;

		case 'extras':
			if ( wd_can( $id, 'livestream' ) ) {
				$d['live'] = array(
					'on'   => empty( $p['live_on'] ) ? 0 : 1,
					'url'  => esc_url_raw( $p['live_url'] ?? '' ),
					'note'  => sanitize_text_field( $p['live_note'] ?? '' ),
					'opens' => preg_match( '/^\d{4}-\d{2}-\d{2}$/', $p['live_opens'] ?? '' ) ? $p['live_opens'] : '',
				);
			}
			if ( wd_can( $id, 'spray' ) ) {
				$d['spray'] = array(
					'on'         => empty( $p['spray_on'] ) ? 0 : 1,
					'show_total' => empty( $p['show_total'] ) ? 0 : 1,
					'opens'      => preg_match( '/^\d{4}-\d{2}-\d{2}$/', $p['spray_opens'] ?? '' ) ? $p['spray_opens'] : '',
				);
			}
			if ( wd_can( $id, 'entry_pass' ) ) {
				$d['pass'] = array(
					'on'  => empty( $p['pass_on'] ) ? 0 : 1,
					'pin' => substr( preg_replace( '/\D/', '', $p['pin'] ?? '' ), 0, 8 ),
				);
			}
			break;

		case 'cards':
			if ( ! wd_can( $id, 'table_qr' ) ) {
				wd_back( 'plan', 'premium', $args );
			}
			$d['cards']['tables']  = min( 200, max( 1, (int) ( $p['tables'] ?? 20 ) ) );
			$d['cards']['address'] = sanitize_textarea_field( $p['address'] ?? '' );
			$d['cards']['phone']   = sanitize_text_field( $p['phone'] ?? '' );
			if ( $d['cards']['address'] && ! $d['cards']['status'] ) {
				$d['cards']['status'] = 'requested';
			}
			wd_put( $id, $d );
			wd_back( 'cards', 'order', $args );
			break;

		case 'receipts':
			if ( ! wd_can( $id, 'receipts' ) ) {
				wd_back( 'money', 'denied', $args );
			}
			$r         = wp_parse_args( (array) ( $d['receipt'] ?? array() ), array( 'logo' => '', 'note' => '' ) );
			$r['note'] = mb_substr( sanitize_textarea_field( $p['receipt_note'] ?? '' ), 0, 240 );
			$logo      = wd_upload_field( 'receipt_logo_file', $id );
			if ( $logo ) {
				$r['logo'] = $logo;
			}
			if ( ! empty( $p['remove_receipt_logo'] ) ) {
				$r['logo'] = '';
			}
			$d['receipt'] = $r;
			wd_put( $id, $d );
			wd_back( 'money', 'saved', $args );
			break;

		case 'payout':
			$t      = wd_money_totals( $id );
			$amount = (int) preg_replace( '/\D/', '', $p['amount'] ?? '' );
			if ( $amount < (int) wd_opt( 'payout_min' ) || $amount > $t['available'] ) {
				wd_back( 'money', 'payout_low', $args );
			}
			global $wpdb;
			$wpdb->insert(
				wd_t( 'payouts' ),
				array(
					'user_id'      => $uid,
					'wedding_id'   => $id,
					'amount'       => $amount,
					'currency'     => $d['currency'],
					'bank'         => sanitize_text_field( $p['bank'] ?? '' ),
					'account_no'   => sanitize_text_field( $p['account_no'] ?? '' ),
					'account_name' => sanitize_text_field( $p['account_name'] ?? '' ),
					'created'      => current_time( 'mysql', true ),
				)
			);
			update_user_meta(
				$uid,
				'wd_bank',
				array(
					'bank'         => sanitize_text_field( $p['bank'] ?? '' ),
					'account_no'   => sanitize_text_field( $p['account_no'] ?? '' ),
					'account_name' => sanitize_text_field( $p['account_name'] ?? '' ),
				)
			);
			wd_back( 'money', 'payout', $args );
			break;

		case 'moderate':
			global $wpdb;
			$table = 'photo' === ( $p['type'] ?? '' ) ? wd_t( 'photos' ) : ( 'rsvp' === ( $p['type'] ?? '' ) ? wd_t( 'rsvps' ) : wd_t( 'wall' ) );
			$oid   = (int) ( $p['oid'] ?? 0 );
			$do    = sanitize_key( $p['do'] ?? '' );
			// One form for a whole list: the pressed button sends "do-ID".
			if ( ! empty( $p['row'] ) && preg_match( '/^([a-z]+)-(\d+)$/', (string) $p['row'], $m ) ) {
				$do  = $m[1];
				$oid = (int) $m[2];
			}
			$own   = (int) $wpdb->get_var( $wpdb->prepare( "SELECT wedding_id FROM $table WHERE id = %d", $oid ) ); // phpcs:ignore WordPress.DB
			if ( $own !== $id ) {
				wd_back( '', 'denied' );
			}
			if ( 'delete' === $do ) {
				$wpdb->delete( $table, array( 'id' => $oid ) );
			} elseif ( in_array( $do, array( 'hide', 'approve' ), true ) && wd_t( 'rsvps' ) !== $table ) {
				$wpdb->update( $table, array( 'status' => 'hide' === $do ? 'hidden' : 'approved' ), array( 'id' => $oid ) );
			} elseif ( 'checkin' === $do && wd_t( 'rsvps' ) === $table ) {
				$wpdb->query( $wpdb->prepare( "UPDATE $table SET checked_in = CASE WHEN checked_in IS NULL THEN %s ELSE NULL END WHERE id = %d", current_time( 'mysql', true ), $oid ) ); // phpcs:ignore WordPress.DB
			}
			wd_refresh( $id );
			wd_back( sanitize_key( $p['back'] ?? 'guests' ), 'done', $args );
			break;
	}
	wd_put( $id, $d );
	wd_refresh( $id );
	wd_back( 'page', 'saved', array_merge( $args, array( 'part' => $part ) ) );
}

/**
 * Find a vendor by name, or list a new one.
 *
 * @param string $name    Name.
 * @param string $type    Vendor type name.
 * @param string $ig      Instagram handle.
 * @param string $city    City.
 * @param string $country Country.
 * @return int
 */
function wd_find_or_make_vendor( $name, $type, $ig, $city, $country ) {
	global $wpdb;
	$found = (int) $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'wd_vendor' AND post_status = 'publish' AND LOWER(post_title) = LOWER(%s) LIMIT 1", $name ) ); // phpcs:ignore WordPress.DB
	if ( $found ) {
		return $found;
	}
	$vid = wp_insert_post(
		array(
			'post_type'   => 'wd_vendor',
			'post_status' => 'publish',
			'post_title'  => $name,
			'post_author' => get_current_user_id(),
		)
	);
	if ( ! $vid || is_wp_error( $vid ) ) {
		return 0;
	}
	if ( $type ) {
		wp_set_object_terms( $vid, $type, 'wd_vtype' );
	}
	$tones = array( 'lavender', 'butter', 'sky', 'mint', 'coral' );
	update_post_meta(
		$vid,
		'_wdv',
		array(
			'city'    => $city,
			'country' => $country,
			'ig'      => $ig,
			'colour'  => $tones[ $vid % 5 ],
		)
	);
	if ( $city ) {
		$t = get_term_by( 'name', $city, 'wd_place' );
		if ( $t ) {
			wp_set_object_terms( $vid, array( (int) $t->term_id ), 'wd_place' );
		}
	}
	wd_vendor_recount( $vid );
	return (int) $vid;
}

/* Guest list as CSV. */
add_action(
	'admin_post_wd_csv',
	static function () {
		$id = isset( $_GET['wid'] ) ? (int) $_GET['wid'] : 0; // phpcs:ignore WordPress.Security.NonceVerification
		if ( ! $id || ! wd_owns( $id ) || ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'wd_csv_' . $id ) ) {
			wp_die( esc_html__( 'You do not have access to that.', 'wwmd' ) );
		}
		global $wpdb;
		$rows = $wpdb->get_results( $wpdb->prepare( 'SELECT name, phone, email, attending, guests, events, side, pass_code, table_no, checked_in, note, created FROM ' . wd_t( 'rsvps' ) . ' WHERE wedding_id = %d ORDER BY created DESC', $id ), ARRAY_A ); // phpcs:ignore WordPress.DB
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=guest-list-' . $id . '.csv' );
		$out = fopen( 'php://output', 'w' );
		fputcsv( $out, array( 'Name', 'Phone', 'Email', 'Attending', 'Guests', 'Events', 'Side', 'Entry pass', 'Table', 'Checked in', 'Note', 'RSVP date' ) );
		foreach ( $rows as $r ) {
			// Stop spreadsheet formulas in guest-typed text.
			fputcsv( $out, array_map( static function ( $v ) {
				return preg_match( '/^[=+\-@]/', (string) $v ) ? "'" . $v : $v;
			}, $r ) );
		}
		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		exit;
	}
);

/**
 * Open a dashboard form that posts to admin-post.php.
 *
 * @param string $part      Part.
 * @param int    $id        Wedding.
 * @param bool   $multipart Files.
 * @param string $class     Class.
 */
function wd_form( $part, $id = 0, $multipart = false, $class = 'form panel' ) {
	printf( '<form class="%s" method="post" action="%s"%s>', esc_attr( $class ), esc_url( admin_url( 'admin-post.php' ) ), $multipart ? ' enctype="multipart/form-data"' : '' );
	echo '<input type="hidden" name="action" value="wd_save"><input type="hidden" name="part" value="' . esc_attr( $part ) . '"><input type="hidden" name="wid" value="' . (int) $id . '">';
	wp_nonce_field( 'wd_save_' . $part . '_' . (int) $id );
}

/**
 * Repeater wrapper. $row_cb( $key, $row ) prints one row's fields.
 *
 * @param string   $group  Field group.
 * @param array    $rows   Rows.
 * @param callable $row_cb Row printer.
 * @param string   $add    Add button label.
 * @param array    $blank  Blank row.
 */
function wd_repeater( $group, $rows, $row_cb, $add, $blank ) {
	echo '<div class="rep" data-rep><div class="rep__rows" data-rep-rows>';
	foreach ( array_values( (array) $rows ) as $i => $row ) {
		$key = 'k' . $i;
		echo '<div class="rep__row" data-rep-row>';
		call_user_func( $row_cb, $group . '[' . $key . ']', $row, $group . '_file[' . $key . ']' );
		wd_rep_tools();
		echo '</div>';
	}
	echo '</div><template data-rep-tpl><div class="rep__row" data-rep-row>';
	call_user_func( $row_cb, $group . '[__KEY__]', $blank, $group . '_file[__KEY__]' );
	wd_rep_tools();
	echo '</div></template><button type="button" class="btn btn--ghost btn--sm" data-rep-add>' . wd_icon( 'plus', 16 ) . esc_html( $add ) . '</button></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
}

/**
 * Row tools: move up, down, remove.
 */
function wd_rep_tools() {
	echo '<div class="rep__tools"><button type="button" data-rep-up aria-label="' . esc_attr__( 'Move up', 'wwmd' ) . '">↑</button><button type="button" data-rep-down aria-label="' . esc_attr__( 'Move down', 'wwmd' ) . '">↓</button><button type="button" data-rep-del>' . esc_html__( 'Remove', 'wwmd' ) . '</button></div>';
}

/**
 * Text field helper.
 *
 * @param string $name  Name.
 * @param string $label Label.
 * @param string $value Value.
 * @param string $type  Type.
 * @param string $extra Extra attributes.
 */
function wd_field( $name, $label, $value = '', $type = 'text', $extra = '' ) {
	if ( 'textarea' === $type ) {
		printf( '<label class="field"><span>%1$s</span><textarea name="%2$s" rows="3" %4$s>%3$s</textarea></label>', esc_html( $label ), esc_attr( $name ), esc_textarea( $value ), $extra ); // phpcs:ignore WordPress.Security.EscapeOutput
		return;
	}
	printf( '<label class="field"><span>%1$s</span><input type="%4$s" name="%2$s" value="%3$s" %5$s></label>', esc_html( $label ), esc_attr( $name ), esc_attr( $value ), esc_attr( $type ), $extra ); // phpcs:ignore WordPress.Security.EscapeOutput
}
