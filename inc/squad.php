<?php
/**
 * The squad: bridesmaids & groomsmen profiles (photo, career, relationship status & social handles),
 * plus the invite link they use to add themselves. The couple approves each person before they show.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

/**
 * Social networks a squad member can list.
 *
 * @return array key => [label, profile URL pattern, icon]
 */
function wd_socials() {
	return array(
		'ig'     => array( 'Instagram', 'https://www.instagram.com/%s/', 'insta' ),
		'tiktok' => array( 'TikTok', 'https://www.tiktok.com/@%s', 'tiktok' ),
		'x'      => array( 'X', 'https://x.com/%s', 'x' ),
		'fb'     => array( 'Facebook', 'https://www.facebook.com/%s', 'facebook' ),
		'li'     => array( 'LinkedIn', 'https://www.linkedin.com/in/%s', 'linkedin' ),
		'snap'   => array( 'Snapchat', 'https://www.snapchat.com/add/%s', 'snapchat' ),
	);
}

/**
 * A clean handle from "@name", "name" or a pasted profile link.
 *
 * @param string $raw Input.
 * @return string
 */
function wd_handle( $raw ) {
	$raw = trim( (string) $raw );
	if ( false !== strpos( $raw, '/' ) ) {
		$url  = preg_match( '#^https?://#i', $raw ) ? $raw : 'https://' . $raw;
		$bits = array_values( array_filter( explode( '/', (string) wp_parse_url( $url, PHP_URL_PATH ) ) ) );
		$raw  = $bits ? end( $bits ) : '';
	}
	return substr( preg_replace( '/[^A-Za-z0-9._-]/', '', ltrim( $raw, '@' ) ), 0, 40 );
}

/**
 * Relationship status choices. An empty value is never shown.
 *
 * @return array
 */
function wd_rel_statuses() {
	return array(
		''        => __( 'Prefer not to say', 'wwmd' ),
		'single'  => __( 'Single', 'wwmd' ),
		'dating'  => __( 'In a relationship', 'wwmd' ),
		'engaged' => __( 'Engaged', 'wwmd' ),
		'married' => __( 'Married', 'wwmd' ),
	);
}

/**
 * Squad roles.
 *
 * @return string[]
 */
function wd_squad_roles() {
	return array( __( 'Chief bridesmaid', 'wwmd' ), __( 'Maid of honour', 'wwmd' ), __( 'Bridesmaid', 'wwmd' ), __( 'Best man', 'wwmd' ), __( 'Groomsman', 'wwmd' ), __( 'Flower girl', 'wwmd' ), __( 'Page boy', 'wwmd' ), __( 'Ring bearer', 'wwmd' ), __( 'Little bride', 'wwmd' ), __( 'Little groom', 'wwmd' ) );
}

/**
 * One clean squad row from posted values.
 *
 * @param array $r Raw row.
 * @return array
 */
function wd_squad_clean( $r ) {
	$roles = wd_squad_roles();
	$side  = 'groom' === ( $r['side'] ?? '' ) ? 'groom' : 'bride';
	$role  = sanitize_text_field( $r['role'] ?? '' );
	$st    = (string) ( $r['status'] ?? '' );
	$out   = array(
		'name'   => mb_substr( sanitize_text_field( $r['name'] ?? '' ), 0, 60 ),
		'role'   => in_array( $role, $roles, true ) ? $role : ( 'groom' === $side ? $roles[4] : $roles[2] ),
		'side'   => $side,
		'nick'   => mb_substr( sanitize_text_field( $r['nick'] ?? '' ), 0, 30 ),
		'line'   => mb_substr( sanitize_text_field( $r['line'] ?? '' ), 0, 160 ),
		'likes'  => mb_substr( sanitize_text_field( $r['likes'] ?? '' ), 0, 120 ),
		'career' => mb_substr( sanitize_text_field( $r['career'] ?? '' ), 0, 60 ),
		'status' => array_key_exists( $st, wd_rel_statuses() ) ? $st : '',
		'image'  => is_numeric( $r['image'] ?? '' ) ? (int) $r['image'] : '',
	);
	foreach ( array_keys( wd_socials() ) as $k ) {
		$out[ $k ] = wd_handle( $r[ $k ] ?? '' );
	}
	return $out;
}

/**
 * A squad member's likes as short chips: "Baking, thrift shopping & voice notes" => 3 items.
 *
 * @param array $p Row.
 * @return string[]
 */
function wd_squad_likes( $p ) {
	$bits = preg_split( '/\s*(?:,|&|\band\b)\s*/i', (string) ( $p['likes'] ?? '' ) );
	return array_slice( array_values( array_filter( array_map( 'trim', (array) $bits ), 'strlen' ) ), 0, 5 );
}

/**
 * Social links for a squad member.
 *
 * @param array $p Row.
 * @return array key => [label, url, icon, handle]
 */
function wd_squad_links( $p ) {
	$out = array();
	foreach ( wd_socials() as $k => $s ) {
		if ( ! empty( $p[ $k ] ) ) {
			$out[ $k ] = array( $s[0], sprintf( $s[1], rawurlencode( $p[ $k ] ) ), $s[2], $p[ $k ] );
		}
	}
	return $out;
}

/**
 * The secret in the squad invite link. Made on first use; a fresh one stops the old link.
 *
 * @param int  $id    Wedding.
 * @param bool $fresh Make a new one.
 * @return string
 */
function wd_squad_key( $id, $fresh = false ) {
	$k = (string) get_post_meta( $id, '_wd_squad_key', true );
	if ( $fresh || ! $k ) {
		$k = wp_generate_password( 16, false );
		update_post_meta( $id, '_wd_squad_key', $k );
	}
	return $k;
}

/**
 * Whether the invite link is switched on.
 *
 * @param int $id Wedding.
 * @return bool
 */
function wd_squad_open( $id ) {
	return ! get_post_meta( $id, '_wd_squad_off', true );
}

/**
 * The invite link the couple shares.
 *
 * @param int $id Wedding.
 * @return string
 */
function wd_squad_link( $id ) {
	return add_query_arg( 'join', wd_squad_key( $id ), get_permalink( $id ) );
}

/**
 * Whether a key opens the join form.
 *
 * @param int    $id  Wedding.
 * @param string $key Key from the link.
 * @return bool
 */
function wd_squad_valid( $id, $key ) {
	$k = (string) get_post_meta( $id, '_wd_squad_key', true );
	return $k && wd_squad_open( $id ) && wd_can( $id, 'party' ) && hash_equals( $k, (string) $key );
}

/**
 * Profiles waiting for the couple's approval.
 *
 * @param int $id Wedding.
 * @return array
 */
function wd_squad_pending( $id ) {
	$p = get_post_meta( $id, '_wd_squad_pending', true );
	return is_array( $p ) ? array_values( $p ) : array();
}

add_action(
	'rest_api_init',
	static function () {
		register_rest_route(
			'wd/v1',
			'/squad',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => 'wd_api_squad_join',
			)
		);
	}
);

/**
 * A bridesmaid or groomsman sends their profile from the invite link.
 *
 * @param WP_REST_Request $r Request.
 * @return array|WP_Error
 */
function wd_api_squad_join( WP_REST_Request $r ) {
	$id = wd_req_wedding( $r );
	if ( is_wp_error( $id ) ) {
		return $id;
	}
	if ( ! wd_squad_valid( $id, (string) $r->get_param( 'key' ) ) ) {
		return new WP_Error( 'key', __( 'This invite link no longer works. Ask the couple for a new one.', 'wwmd' ), array( 'status' => 403 ) );
	}
	if ( '' !== trim( (string) $r->get_param( 'website' ) ) ) {
		return array( 'ok' => true ); // Honeypot: only bots fill it in.
	}
	if ( ! wd_rate_ok( 'squad' . $id, 6, HOUR_IN_SECONDS ) ) {
		return wd_slow_down();
	}
	$row = wd_squad_clean( $r->get_params() );
	$err = '';
	if ( '' === $row['name'] ) {
		$err = __( 'Add your name.', 'wwmd' );
	} elseif ( '' === $row['career'] ) {
		$err = __( 'Add your career or job title.', 'wwmd' );
	} elseif ( ! array_filter( array_intersect_key( $row, wd_socials() ) ) ) {
		$err = __( 'Add at least one social media handle.', 'wwmd' );
	} elseif ( ! $r->get_param( 'agree' ) ) {
		$err = __( 'Tick the box to agree to your profile showing on the wedding page.', 'wwmd' );
	}
	if ( $err ) {
		return new WP_Error( 'missing', $err, array( 'status' => 400 ) );
	}
	$waiting = wd_squad_pending( $id );
	if ( count( $waiting ) >= 40 ) {
		return new WP_Error( 'full', __( 'The couple has a lot of profiles to check. Please try again in a day or two.', 'wwmd' ), array( 'status' => 429 ) );
	}
	$files = $r->get_file_params();
	if ( empty( $files['photo'] ) || ! empty( $files['photo']['error'] ) ) {
		return new WP_Error( 'file', __( 'Add a photo of you.', 'wwmd' ), array( 'status' => 400 ) );
	}
	$att = wd_handle_image_upload( $files['photo'], $id );
	if ( is_wp_error( $att ) ) {
		return $att;
	}
	$row['image'] = $att;
	$row['id']    = wp_generate_password( 10, false );
	$row['at']    = time();
	$waiting[]    = $row;
	update_post_meta( $id, '_wd_squad_pending', $waiting );

	$d     = wd_get( $id );
	$owner = get_userdata( (int) get_post_field( 'post_author', $id ) );
	if ( $owner && $owner->user_email && ! get_post_meta( $id, '_wd_sample', true ) ) {
		wp_mail(
			$owner->user_email,
			/* translators: %s person's name */
			sprintf( __( '%s wants to join your squad', 'wwmd' ), $row['name'] ),
			/* translators: 1: name, 2: role, 3: link */
			sprintf( __( "%1\$s added a profile as your %2\$s. Approve or decline it here:\n\n%3\$s", 'wwmd' ), $row['name'], strtolower( $row['role'] ), wd_account_url( 'page', array( 'wid' => $id, 'part' => 'party' ) ) )
		);
	}
	return array(
		'ok'  => true,
		/* translators: 1: first name, 2: couple names */
		'msg' => sprintf( __( 'Thank you, %1$s! %2$s will check your profile & add you to the line-up.', 'wwmd' ), strtok( $row['name'], ' ' ), wd_names( $d ) ),
	);
}

/**
 * Couple actions from the squad editor: a new link, switching it off or on, approving or declining.
 *
 * @param int   $id Wedding (ownership already checked).
 * @param array $p  Posted values.
 * @param array $d  Wedding data.
 * @return string Notice key.
 */
function wd_squad_action( $id, $p, &$d ) {
	$do = sanitize_key( $p['do'] ?? '' );
	if ( 'new' === $do ) {
		wd_squad_key( $id, true );
		delete_post_meta( $id, '_wd_squad_off' );
		return 'squad_link';
	}
	if ( 'off' === $do || 'on' === $do ) {
		'off' === $do ? update_post_meta( $id, '_wd_squad_off', 1 ) : delete_post_meta( $id, '_wd_squad_off' );
		return 'squad_' . $do;
	}
	$pid     = sanitize_text_field( $p['pid'] ?? '' );
	$waiting = wd_squad_pending( $id );
	foreach ( $waiting as $i => $row ) {
		if ( $row['id'] !== $pid ) {
			continue;
		}
		unset( $waiting[ $i ] );
		update_post_meta( $id, '_wd_squad_pending', array_values( $waiting ) );
		if ( 'approve' === $do ) {
			unset( $row['id'], $row['at'] );
			$d['party'][] = wd_squad_clean( $row );
			return 'approved';
		}
		if ( $row['image'] ) {
			wp_delete_attachment( (int) $row['image'], true );
		}
		return 'declined';
	}
	return 'done';
}

add_filter(
	'wp_robots',
	static function ( $robots ) {
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( is_singular( 'wd_wedding' ) && isset( $_GET['join'] ) ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
		}
		return $robots;
	}
);
