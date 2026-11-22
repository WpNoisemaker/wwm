<?php
/**
 * Guest actions on wedding pages (REST, namespace wd/v1): RSVP, love notes, likes, photos, quiz,
 * vendor reviews, payments, check-in & service requests.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

/**
 * Published wedding from a request, or error.
 *
 * @param WP_REST_Request $r Request.
 * @return int|WP_Error
 */
function wd_req_wedding( $r ) {
	$id = (int) $r->get_param( 'wedding' );
	$p  = get_post( $id );
	if ( ! $p || 'wd_wedding' !== $p->post_type || ( 'publish' !== $p->post_status && ! wd_owns( $id ) ) ) {
		return new WP_Error( 'wedding', __( 'Wedding not found.', 'wwmd' ), array( 'status' => 404 ) );
	}
	return $id;
}

/**
 * Clean text param.
 *
 * @param WP_REST_Request $r   Request.
 * @param string          $key Key.
 * @param int             $max Max length.
 * @return string
 */
function wd_p( $r, $key, $max = 120 ) {
	return mb_substr( trim( sanitize_text_field( (string) $r->get_param( $key ) ) ), 0, $max );
}

/**
 * Error when the rate limit is hit.
 *
 * @return WP_Error
 */
function wd_slow_down() {
	return new WP_Error( 'slow', __( 'Easy! Please wait a few minutes & try again.', 'wwmd' ), array( 'status' => 429 ) );
}

add_action(
	'rest_api_init',
	static function () {
		$open = array( 'permission_callback' => '__return_true' );

		register_rest_route(
			'wd/v1',
			'/rsvp',
			$open + array(
				'methods'  => 'POST',
				'callback' => static function ( WP_REST_Request $r ) {
					global $wpdb;
					$id = wd_req_wedding( $r );
					if ( is_wp_error( $id ) ) {
						return $id;
					}
					if ( ! wd_can( $id, 'rsvp' ) ) {
						return new WP_Error( 'off', __( 'RSVP is closed.', 'wwmd' ), array( 'status' => 403 ) );
					}
					if ( ! wd_rate_ok( 'rsvp' . $id, 6, HOUR_IN_SECONDS ) ) {
						return wd_slow_down();
					}
					$d    = wd_get( $id );
					$name = wd_p( $r, 'name' );
					if ( mb_strlen( $name ) < 2 ) {
						return new WP_Error( 'name', __( 'Please enter your name.', 'wwmd' ), array( 'status' => 400 ) );
					}
					$attending = 'no' === $r->get_param( 'attending' ) ? 'no' : 'yes';
					$max       = max( 1, (int) $d['rsvp']['max'] );
					$guests    = 'yes' === $attending ? min( $max, max( 1, (int) $r->get_param( 'guests' ) ) ) : 0;
					$open      = array();
					foreach ( wd_programs( $d ) as $e ) {
						if ( empty( $e['private'] ) ) {
							$open[] = $e['title'];
						}
					}
					$events    = array_values( array_intersect( $open, array_map( 'sanitize_text_field', (array) $r->get_param( 'events' ) ) ) );
					if ( ! $events && 'yes' === $attending ) {
						$events = $open;
					}
					$pass      = ( 'yes' === $attending && wd_can( $id, 'entry_pass' ) && ! empty( $d['pass']['on'] ) ) ? wd_new_pass() : '';
					$wpdb->insert(
						wd_t( 'rsvps' ),
						array(
							'wedding_id' => $id,
							'name'       => $name,
							'phone'      => wd_p( $r, 'phone', 40 ),
							'email'      => sanitize_email( (string) $r->get_param( 'email' ) ),
							'attending'  => $attending,
							'guests'     => $guests,
							'events'     => implode( ', ', array_slice( $events, 0, 10 ) ),
							'note'       => mb_substr( sanitize_textarea_field( (string) $r->get_param( 'note' ) ), 0, 500 ),
							'side'       => in_array( $r->get_param( 'side' ), array( 'bride', 'groom', 'both' ), true ) ? $r->get_param( 'side' ) : '',
							'pass_code'  => $pass,
							'table_no'   => wd_p( $r, 'table', 20 ),
							'created'    => current_time( 'mysql', true ),
						)
					);
					$out = array(
						'ok'      => true,
						'message' => 'yes' === $attending
							/* translators: %s: names */
							? sprintf( __( 'Thank you, %s! Your seat is saved.', 'wwmd' ), $name )
							: __( 'Thank you for letting us know. You will be missed!', 'wwmd' ),
					);
					if ( $pass ) {
						$out['pass']     = $pass;
						$out['pass_url'] = home_url( '/pass/' . $pass . '/' );
						$out['guests']   = $guests;
						$out['events']   = count( $open ) > 1 ? $events : array();
					}
					return $out;
				},
			)
		);

		// A guest pressed play on the livestream: one viewer for Buzz coins (once per browser).
		register_rest_route(
			'wd/v1',
			'/watch',
			$open + array(
				'methods'  => 'POST',
				'callback' => static function ( WP_REST_Request $r ) {
					$id = wd_req_wedding( $r );
					if ( is_wp_error( $id ) ) {
						return $id;
					}
					if ( ! wd_rate_ok( 'watch' . $id, 5, HOUR_IN_SECONDS ) ) {
						return wd_slow_down();
					}
					return array(
						'ok'      => true,
						'counted' => wd_count_live( $id ),
					);
				},
			)
		);

		register_rest_route(
			'wd/v1',
			'/wall',
			$open + array(
				'methods'  => 'POST',
				'callback' => static function ( WP_REST_Request $r ) {
					global $wpdb;
					$id = wd_req_wedding( $r );
					if ( is_wp_error( $id ) ) {
						return $id;
					}
					if ( ! wd_can( $id, 'wall' ) || ! wd_rate_ok( 'wall' . $id, 5, 15 * MINUTE_IN_SECONDS ) ) {
						return wd_slow_down();
					}
					$name = wd_p( $r, 'name', 60 );
					$msg  = mb_substr( trim( sanitize_textarea_field( (string) $r->get_param( 'message' ) ) ), 0, 600 );
					if ( mb_strlen( $name ) < 2 || mb_strlen( $msg ) < 2 ) {
						return new WP_Error( 'empty', __( 'Add your name & a message.', 'wwmd' ), array( 'status' => 400 ) );
					}
					$status = wd_opt( 'moderate_wall' ) ? 'pending' : 'approved';
					$wpdb->insert(
						wd_t( 'wall' ),
						array(
							'wedding_id' => $id,
							'name'       => $name,
							'message'    => $msg,
							'status'     => $status,
							'created'    => current_time( 'mysql', true ),
						)
					);
					wd_refresh( $id );
					return array(
						'ok'      => true,
						'id'      => (int) $wpdb->insert_id,
						'pending' => 'pending' === $status,
						'name'    => $name,
						'message' => $msg,
					);
				},
			)
		);

		register_rest_route(
			'wd/v1',
			'/like',
			$open + array(
				'methods'  => 'POST',
				'callback' => static function ( WP_REST_Request $r ) {
					global $wpdb;
					$type = $r->get_param( 'type' );
					$oid  = (int) $r->get_param( 'id' );
					$map  = array(
						'wall'  => 'wall',
						'photo' => 'photos',
					);
					if ( ! isset( $map[ $type ] ) || ! $oid ) {
						return new WP_Error( 'bad', 'Bad request', array( 'status' => 400 ) );
					}
					$table = wd_t( $map[ $type ] );
					$row   = $wpdb->get_row( $wpdb->prepare( "SELECT id, wedding_id, likes FROM $table WHERE id = %d AND status = 'approved'", $oid ) ); // phpcs:ignore WordPress.DB
					if ( ! $row ) {
						return new WP_Error( 'gone', 'Not found', array( 'status' => 404 ) );
					}
					if ( ! wd_rate_ok( 'like', 120, HOUR_IN_SECONDS ) ) {
						return wd_slow_down();
					}
					$voter = wd_voter();
					$lt    = wd_t( 'likes' );
					$has   = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $lt WHERE obj_type = %s AND obj_id = %d AND voter = %s", $type, $oid, $voter ) ); // phpcs:ignore WordPress.DB
					if ( $has ) {
						$wpdb->delete( $lt, array( 'id' => $has ) );
						$wpdb->query( $wpdb->prepare( "UPDATE $table SET likes = CASE WHEN likes > 0 THEN likes - 1 ELSE 0 END WHERE id = %d", $oid ) ); // phpcs:ignore WordPress.DB
						$liked = false;
					} else {
						$wpdb->insert(
							$lt,
							array(
								'obj_type' => $type,
								'obj_id'   => $oid,
								'voter'    => $voter,
								'created'  => current_time( 'mysql', true ),
							)
						);
						$wpdb->query( $wpdb->prepare( "UPDATE $table SET likes = likes + 1 WHERE id = %d", $oid ) ); // phpcs:ignore WordPress.DB
						$liked = true;
					}
					if ( ! get_transient( 'wd_lr_' . $row->wedding_id ) ) {
						set_transient( 'wd_lr_' . $row->wedding_id, 1, MINUTE_IN_SECONDS );
						wd_refresh( (int) $row->wedding_id );
					}
					return array(
						'ok'    => true,
						'liked' => $liked,
						'likes' => (int) $wpdb->get_var( $wpdb->prepare( "SELECT likes FROM $table WHERE id = %d", $oid ) ), // phpcs:ignore WordPress.DB
					);
				},
			)
		);

		register_rest_route(
			'wd/v1',
			'/photo',
			$open + array(
				'methods'  => 'POST',
				'callback' => static function ( WP_REST_Request $r ) {
					global $wpdb;
					$id = wd_req_wedding( $r );
					if ( is_wp_error( $id ) ) {
						return $id;
					}
					if ( ! wd_can( $id, 'gallery' ) || ! wd_rate_ok( 'photo' . $id, 12, HOUR_IN_SECONDS ) ) {
						return wd_slow_down();
					}
					$files = $r->get_file_params();
					if ( empty( $files['photo'] ) || ! empty( $files['photo']['error'] ) ) {
						return new WP_Error( 'file', __( 'Choose a photo to upload.', 'wwmd' ), array( 'status' => 400 ) );
					}
					$att = wd_handle_image_upload( $files['photo'], $id );
					if ( is_wp_error( $att ) ) {
						return $att;
					}
					$status = wd_opt( 'moderate_photos' ) ? 'pending' : 'approved';
					$name   = wd_p( $r, 'name', 60 );
					$wpdb->insert(
						wd_t( 'photos' ),
						array(
							'wedding_id'    => $id,
							'attachment_id' => $att,
							'name'          => $name ? $name : __( 'A guest', 'wwmd' ),
							'caption'       => wd_p( $r, 'caption', 200 ),
							'status'        => $status,
							'created'       => current_time( 'mysql', true ),
						)
					);
					return array(
						'ok'      => true,
						'id'      => (int) $wpdb->insert_id,
						'pending' => 'pending' === $status,
						'src'     => wp_get_attachment_image_url( $att, 'wd-square' ),
						'name'    => $name,
					);
				},
			)
		);

		register_rest_route(
			'wd/v1',
			'/quiz',
			$open + array(
				'methods'  => 'POST',
				'callback' => static function ( WP_REST_Request $r ) {
					global $wpdb;
					$id = wd_req_wedding( $r );
					if ( is_wp_error( $id ) ) {
						return $id;
					}
					if ( ! wd_can( $id, 'quiz' ) || ! wd_rate_ok( 'quiz' . $id, 5, HOUR_IN_SECONDS ) ) {
						return wd_slow_down();
					}
					$d       = wd_get( $id );
					$answers = (array) $r->get_param( 'answers' );
					$score   = 0;
					$right   = array();
					foreach ( $d['quiz'] as $i => $q ) {
						$right[] = (int) $q['c'];
						if ( isset( $answers[ $i ] ) && (int) $answers[ $i ] === (int) $q['c'] ) {
							$score++;
						}
					}
					$name = wd_p( $r, 'name', 40 );
					$secs = max( 1, min( 3600, (int) $r->get_param( 'seconds' ) ) );
					$wpdb->insert(
						wd_t( 'quiz' ),
						array(
							'wedding_id' => $id,
							'name'       => $name ? $name : __( 'Guest', 'wwmd' ),
							'score'      => $score,
							'total'      => count( $d['quiz'] ),
							'seconds'    => $secs,
							'created'    => current_time( 'mysql', true ),
						)
					);
					$mine = (int) $wpdb->insert_id;
					$rank = 1 + (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . wd_t( 'quiz' ) . ' WHERE wedding_id = %d AND ( score > %d OR ( score = %d AND seconds < %d ) OR ( score = %d AND seconds = %d AND id < %d ) )', $id, $score, $score, $secs, $score, $secs, $mine ) ); // phpcs:ignore WordPress.DB
					return array(
						'ok'      => true,
						'score'   => $score,
						'total'   => count( $d['quiz'] ),
						'right'   => $right,
						'board'   => wd_quiz_board( $id, 10 ),
						'my_id'   => $mine,
						'my_rank' => $rank,
					);
				},
			)
		);

		register_rest_route(
			'wd/v1',
			'/review',
			$open + array(
				'methods'  => 'POST',
				'callback' => static function ( WP_REST_Request $r ) {
					global $wpdb;
					$id = wd_req_wedding( $r );
					if ( is_wp_error( $id ) ) {
						return $id;
					}
					$vid = (int) $r->get_param( 'vendor' );
					$d   = wd_get( $id );
					$ids = wp_list_pluck( $d['vendors'], 'id' );
					if ( ! in_array( $vid, array_map( 'intval', $ids ), true ) || ! wd_rate_ok( 'review' . $vid, 3, DAY_IN_SECONDS ) ) {
						return wd_slow_down();
					}
					$name   = wd_p( $r, 'name', 60 );
					$text   = mb_substr( trim( sanitize_textarea_field( (string) $r->get_param( 'text' ) ) ), 0, 800 );
					$rating = min( 5, max( 1, (int) $r->get_param( 'rating' ) ) );
					if ( mb_strlen( $name ) < 2 || mb_strlen( $text ) < 8 ) {
						return new WP_Error( 'empty', __( 'Add your name & a few words about the vendor.', 'wwmd' ), array( 'status' => 400 ) );
					}
					$wpdb->insert(
						wd_t( 'reviews' ),
						array(
							'vendor_id'  => $vid,
							'wedding_id' => $id,
							'name'       => $name,
							'rating'     => $rating,
							'text'       => $text,
							'status'     => 'approved',
							'created'    => current_time( 'mysql', true ),
						)
					);
					wd_vendor_recount( $vid );
					wd_refresh( $id );
					return array(
						'ok'      => true,
						'rating'  => (float) get_post_meta( $vid, '_wd_rating', true ),
						'reviews' => (int) get_post_meta( $vid, '_wd_reviews', true ),
					);
				},
			)
		);

		register_rest_route(
			'wd/v1',
			'/pay',
			$open + array(
				'methods'  => 'POST',
				'callback' => static function ( WP_REST_Request $r ) {
					$id = wd_req_wedding( $r );
					if ( is_wp_error( $id ) ) {
						return $id;
					}
					$kind = $r->get_param( 'kind' );
					if ( ! in_array( $kind, array( 'spray', 'gift' ), true ) ) {
						return new WP_Error( 'kind', 'Bad request', array( 'status' => 400 ) );
					}
					if ( ( 'spray' === $kind && ! wd_show( $id, 'spray' ) ) || ( 'gift' === $kind && ! wd_show( $id, 'registry' ) ) ) {
						return new WP_Error( 'off', __( 'This is not switched on for this wedding.', 'wwmd' ), array( 'status' => 403 ) );
					}
					if ( ! wd_rate_ok( 'pay', 30, HOUR_IN_SECONDS ) ) {
						return wd_slow_down();
					}
					$d    = wd_get( $id );
					$item = sanitize_key( (string) $r->get_param( 'item' ) );
					if ( 'gift' === $kind && ! in_array( $item, wp_list_pluck( $d['registry'], 'id' ), true ) ) {
						return new WP_Error( 'item', __( 'That gift is no longer on the list.', 'wwmd' ), array( 'status' => 400 ) );
					}
					$res = wd_pay_start(
						array(
							'wedding_id'  => $id,
							'kind'        => $kind,
							'item'        => $item,
							'amount'      => (float) $r->get_param( 'amount' ),
							'name'        => wd_p( $r, 'name', 60 ) ? wd_p( $r, 'name', 60 ) : __( 'A guest', 'wwmd' ),
							'email'       => sanitize_email( (string) $r->get_param( 'email' ) ),
							'message'     => wd_p( $r, 'message', 200 ),
							'cover_fee'   => (bool) $r->get_param( 'cover' ),
							'hide_amount' => (bool) $r->get_param( 'hide' ),
							'return'      => get_permalink( $id ),
						)
					);
					return is_wp_error( $res ) ? $res : array_merge( array( 'ok' => true ), $res );
				},
			)
		);

		register_rest_route(
			'wd/v1',
			'/pay/check',
			$open + array(
				'methods'  => 'POST',
				'callback' => static function ( WP_REST_Request $r ) {
					$ref = preg_replace( '/[^A-Z0-9]/', '', strtoupper( (string) $r->get_param( 'reference' ) ) );
					return array( 'paid' => $ref ? wd_pay_verify( $ref ) : false );
				},
			)
		);

		register_rest_route(
			'wd/v1',
			'/checkin',
			$open + array(
				'methods'  => 'POST',
				'callback' => static function ( WP_REST_Request $r ) {
					global $wpdb;
					$code = preg_replace( '/[^A-Z0-9]/', '', strtoupper( (string) $r->get_param( 'code' ) ) );
					$row  = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . wd_t( 'rsvps' ) . ' WHERE pass_code = %s', $code ) ); // phpcs:ignore WordPress.DB
					if ( ! $row || ! wd_is_usher( (int) $row->wedding_id ) ) {
						return new WP_Error( 'denied', __( 'Only the couple & their ushers can check guests in.', 'wwmd' ), array( 'status' => 403 ) );
					}
					if ( ! $row->checked_in ) {
						$wpdb->update( wd_t( 'rsvps' ), array( 'checked_in' => current_time( 'mysql', true ) ), array( 'id' => $row->id ) );
					}
					return array(
						'ok'   => true,
						'name' => $row->name,
						'was'  => (bool) $row->checked_in,
					);
				},
			)
		);

		register_rest_route(
			'wd/v1',
			'/usher',
			$open + array(
				'methods'  => 'POST',
				'callback' => static function ( WP_REST_Request $r ) {
					$id = wd_req_wedding( $r );
					if ( is_wp_error( $id ) ) {
						return $id;
					}
					if ( ! wd_rate_ok( 'usher' . $id, 8, HOUR_IN_SECONDS ) ) {
						return wd_slow_down();
					}
					$d   = wd_get( $id );
					$pin = preg_replace( '/\D/', '', (string) $r->get_param( 'pin' ) );
					if ( ! $d['pass']['pin'] || ! hash_equals( (string) $d['pass']['pin'], $pin ) ) {
						return new WP_Error( 'pin', __( 'That PIN is not right.', 'wwmd' ), array( 'status' => 403 ) );
					}
					setcookie( 'wd_usher_' . $id, wp_hash( $id . '|' . $d['pass']['pin'] ), time() + 3 * DAY_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
					return array( 'ok' => true );
				},
			)
		);

		register_rest_route(
			'wd/v1',
			'/request',
			$open + array(
				'methods'  => 'POST',
				'callback' => static function ( WP_REST_Request $r ) {
					global $wpdb;
					if ( ! wd_rate_ok( 'request', 5, HOUR_IN_SECONDS ) ) {
						return wd_slow_down();
					}
					$service = sanitize_key( (string) $r->get_param( 'service' ) );
					$name    = wd_p( $r, 'name', 80 );
					$phone   = wd_p( $r, 'phone', 40 );
					if ( ! $name || ! $phone ) {
						return new WP_Error( 'empty', __( 'Add your name & WhatsApp number.', 'wwmd' ), array( 'status' => 400 ) );
					}
					$details = mb_substr( sanitize_textarea_field( (string) $r->get_param( 'details' ) ), 0, 1500 );
					$wpdb->insert(
						wd_t( 'requests' ),
						array(
							'user_id'    => get_current_user_id(),
							'wedding_id' => (int) $r->get_param( 'wedding' ),
							'service'    => $service,
							'name'       => $name,
							'phone'      => $phone,
							'email'      => sanitize_email( (string) $r->get_param( 'email' ) ),
							'details'    => $details,
							'created'    => current_time( 'mysql', true ),
						)
					);
					$services = wd_services();
					$label    = isset( $services[ $service ] ) ? $services[ $service ]['title'] : $service;
					/* translators: 1: service 2: name 3: details */
					$text = sprintf( __( "Hello, I'd like to ask about %1\$s.\nName: %2\$s\n%3\$s", 'wwmd' ), $label, $name, $details );
					return array(
						'ok'       => true,
						'whatsapp' => wd_whatsapp( $text ),
					);
				},
			)
		);
	}
);

/**
 * Whether the visitor is an usher (or the couple) for a wedding.
 *
 * @param int $id Wedding.
 * @return bool
 */
function wd_is_usher( $id ) {
	if ( wd_owns( $id ) ) {
		return true;
	}
	$d = wd_get( $id );
	if ( ! $d['pass']['pin'] ) {
		return false;
	}
	$c = isset( $_COOKIE[ 'wd_usher_' . $id ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ 'wd_usher_' . $id ] ) ) : '';
	return $c && hash_equals( wp_hash( $id . '|' . $d['pass']['pin'] ), $c );
}

/**
 * Quiz leaderboard.
 *
 * @param int $id Wedding.
 * @param int $n  Rows.
 * @return array
 */
function wd_quiz_board( $id, $n = 10 ) {
	global $wpdb;
	$rows = $wpdb->get_results( $wpdb->prepare( 'SELECT id, name, score, total, seconds FROM ' . wd_t( 'quiz' ) . ' WHERE wedding_id = %d ORDER BY score DESC, seconds ASC, id ASC LIMIT %d', $id, $n ) ); // phpcs:ignore WordPress.DB
	return array_map(
		static function ( $r ) {
			return array(
				'id'      => (int) $r->id,
				'name'    => $r->name,
				'score'   => (int) $r->score,
				'total'   => (int) $r->total,
				'seconds' => (int) $r->seconds,
			);
		},
		(array) $rows
	);
}

/**
 * Save an uploaded image as an attachment, after checking it really is an image.
 *
 * @param array $file   $_FILES entry.
 * @param int   $parent Parent post.
 * @return int|WP_Error Attachment ID.
 */
function wd_handle_image_upload( $file, $parent = 0 ) {
	if ( empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
		return new WP_Error( 'file', __( 'Upload failed. Please try again.', 'wwmd' ), array( 'status' => 400 ) );
	}
	if ( (int) $file['size'] > 8 * MB_IN_BYTES ) {
		return new WP_Error( 'big', __( 'That photo is over 8 MB. Please pick a smaller one.', 'wwmd' ), array( 'status' => 400 ) );
	}
	$check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], array( 'jpg|jpeg|jpe' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif' ) );
	if ( empty( $check['type'] ) || ! @getimagesize( $file['tmp_name'] ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors
		return new WP_Error( 'type', __( 'Please upload a JPG, PNG or WebP photo.', 'wwmd' ), array( 'status' => 400 ) );
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	$up = wp_handle_upload(
		$file,
		array(
			'test_form' => false,
			'mimes'     => array( 'jpg|jpeg|jpe' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif' ),
		)
	);
	if ( ! empty( $up['error'] ) ) {
		return new WP_Error( 'upload', $up['error'], array( 'status' => 400 ) );
	}
	$att = wp_insert_attachment(
		array(
			'post_mime_type' => $up['type'],
			'post_title'     => sanitize_file_name( pathinfo( $file['name'], PATHINFO_FILENAME ) ),
			'post_status'    => 'inherit',
			'post_parent'    => $parent,
		),
		$up['file'],
		$parent
	);
	if ( is_wp_error( $att ) ) {
		return $att;
	}
	wp_update_attachment_metadata( $att, wp_generate_attachment_metadata( $att, $up['file'] ) );
	return (int) $att;
}

/**
 * Extra services couples can ask for.
 *
 * @return array
 */
function wd_services() {
	return array(
		'apartments' => array(
			'title' => __( 'Apartments & hotel blocks', 'wwmd' ),
			'icon'  => 'home',
			'art'   => 'house',
			'tone'  => 'butter',
			'text'  => __( 'Short-let apartments near your venue for you, your family & guests flying in. We hold a block & share one booking link on your page.', 'wwmd' ),
			'from'  => __( 'From ₦65,000 a night', 'wwmd' ),
		),
		'rides'      => array(
			'title' => __( 'Rides & wedding cars', 'wwmd' ),
			'icon'  => 'car',
			'art'   => 'car',
			'tone'  => 'sky',
			'text'  => __( 'Airport pick-ups, a bridal car, buses for the bridal train & drivers for the day, booked by the hour or the day.', 'wwmd' ),
			'from'  => __( 'From ₦85,000 a day', 'wwmd' ),
		),
		'honeymoon'  => array(
			'title' => __( 'Honeymoon experiences', 'wwmd' ),
			'icon'  => 'plane',
			'art'   => 'plane',
			'tone'  => 'coral',
			'text'  => __( 'Zanzibar, Diani, Cape Town, Obudu, Mauritius & more. Flights, stays & little extras, with a honeymoon fund guests can add to.', 'wwmd' ),
			'from'  => __( 'Packages from ₦1,200,000 for two', 'wwmd' ),
		),
		'livestream' => array(
			'title' => __( 'Livestream crew', 'wwmd' ),
			'icon'  => 'video',
			'art'   => 'camera',
			'tone'  => 'lavender',
			'text'  => __( 'A camera crew streams your day to your wedding page so family abroad can watch, comment & spray live. Nigeria only for now.', 'wwmd' ),
			'from'  => __( '₦350,000 a day', 'wwmd' ),
		),
		'qr'         => array(
			'title' => __( 'Extra table QR cards', 'wwmd' ),
			'icon'  => 'qr',
			'art'   => 'qr',
			'tone'  => 'mint',
			'text'  => __( 'More printed table cards that open your wedding page so guests can spray, upload photos & leave love notes from their seat.', 'wwmd' ),
			/* translators: 1: price 2: count */
			'from'  => sprintf( __( '%1$s for %2$d cards', 'wwmd' ), wd_money( wd_opt( 'extra_qr_price' ) ), (int) wd_opt( 'extra_qr_qty' ) ),
		),
		'other'      => array(
			'title' => __( 'Something else', 'wwmd' ),
			'icon'  => 'gift',
			'art'   => 'gift',
			'tone'  => 'leaf',
			'text'  => __( 'Aso-ebi orders, souvenirs, a wedding planner or a vendor you cannot find? Tell us what you need.', 'wwmd' ),
			'from'  => __( 'Ask us', 'wwmd' ),
		),
	);
}
