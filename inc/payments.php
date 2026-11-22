<?php
/**
 * Payments: spraying, cash & gift registry, Premium, custom designs & marketplace bookings.
 *
 * Runs on Bachs (bachs.io) once a secret key is saved in HQ › Settings: hosted checkout with cards,
 * bank transfer (NGN) & mobile money, with instant settlement. Keys that start with sk_sandbox_ use
 * the Bachs sandbox; sk_live_ keys take real money. With no key & "test payments" on, payments are
 * recorded as test payments so every flow can be tried without moving money.
 * The site itself only ever says "secure payment provider".
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether payments are in test mode.
 *
 * @return bool
 */
function wd_pay_test_mode() {
	return ! wd_bachs_key() && wd_opt( 'test_payments' );
}

/**
 * The payment reference from a return URL (?wdpay=REF). Copes with a provider that adds
 * "?checkout_id=…" to a link that already has a query string.
 *
 * @return string
 */
function wd_pay_ref_from_request() {
	// phpcs:ignore WordPress.Security.NonceVerification
	$raw = isset( $_GET['wdpay'] ) ? (string) wp_unslash( $_GET['wdpay'] ) : '';
	$raw = explode( '?', $raw )[0];
	return substr( preg_replace( '/[^A-Z0-9]/', '', strtoupper( sanitize_text_field( $raw ) ) ), 0, 40 );
}

/**
 * Bachs secret key (sk_sandbox_… or sk_live_…).
 *
 * @return string
 */
function wd_bachs_key() {
	return trim( (string) wd_opt( 'bachs_secret' ) );
}

/**
 * Call the Bachs API. Objects come back flat (no wrapper).
 *
 * @param string     $method GET|POST.
 * @param string     $path   e.g. /v1/checkout-sessions.
 * @param array|null $body   JSON body.
 * @param array      $extra  Extra headers (e.g. Idempotency-Key).
 * @return array|WP_Error
 */
function wd_bachs( $method, $path, $body = null, $extra = array() ) {
	$base = 0 === strpos( wd_bachs_key(), 'sk_live_' ) ? 'https://api.bachs.io' : 'https://sandbox-api.bachs.io';
	$args = array(
		'method'  => $method,
		'timeout' => 25,
		'headers' => array(
			'Authorization' => 'Bearer ' . wd_bachs_key(),
			'Content-Type'  => 'application/json',
			'Accept'        => 'application/json',
		) + $extra,
	);
	if ( null !== $body ) {
		$args['body'] = wp_json_encode( $body );
	}
	$res = wp_remote_request( $base . $path, $args );
	if ( is_wp_error( $res ) ) {
		return $res;
	}
	$code = (int) wp_remote_retrieve_response_code( $res );
	$json = json_decode( wp_remote_retrieve_body( $res ), true );
	if ( $code < 200 || $code > 299 || ! is_array( $json ) ) {
		$msg = is_array( $json ) && ! empty( $json['detail'] ) && is_string( $json['detail'] ) ? $json['detail'] : 'HTTP ' . $code;
		return new WP_Error( 'bachs', $msg, array( 'request_id' => wp_remote_retrieve_header( $res, 'x-request-id' ) ) );
	}
	return $json;
}

/**
 * Currencies Bachs can charge in. Anything else (e.g. ZAR) is charged in US dollars at the HQ rates.
 *
 * @return array
 */
function wd_pay_currencies() {
	return apply_filters( 'wd_pay_currencies', array( 'NGN', 'USD', 'GHS', 'KES', 'MWK', 'RWF', 'TZS', 'UGX', 'XAF', 'XOF', 'ZMW' ) );
}

/**
 * Create a payment & start checkout.
 *
 * @param array $a wedding_id, kind (spray|gift|premium for plans|design), item (for plans: pro|premium), amount, name, email, message, cover_fee, hide_amount, return.
 * @return array|WP_Error
 */
function wd_pay_start( $a ) {
	global $wpdb;
	$id   = (int) $a['wedding_id'];
	$kind = $a['kind'];
	$d    = wd_get( $id );
	$cur  = ! empty( $a['currency'] ) ? $a['currency'] : ( in_array( $kind, array( 'premium', 'design' ), true ) ? 'NGN' : ( $d['currency'] ? $d['currency'] : 'NGN' ) );
	$amt  = (int) round( (float) $a['amount'] );
	if ( $amt < 1 ) {
		return new WP_Error( 'amount', __( 'Enter an amount.', 'wwmd' ) );
	}
	$fee   = 0;
	$cover = ! empty( $a['cover_fee'] );
	if ( in_array( $kind, array( 'spray', 'gift' ), true ) ) {
		$rate = wd_fee_rate( $id, $kind );
		$fee  = (int) ceil( $amt * $rate / 100 );
		$cap  = (int) wd_opt( 'fee_cap' );
		if ( $cap > 0 ) {
			$fee = min( $fee, $cap );
		}
	}
	$charged = $cover ? $amt + $fee : $amt;
	$net     = in_array( $kind, array( 'spray', 'gift' ), true ) ? ( $cover ? $amt : $amt - $fee ) : 0;
	$ref     = 'WD' . strtoupper( wp_generate_password( 10, false ) );
	$email   = is_email( $a['email'] ) ? $a['email'] : 'guest+' . strtolower( $ref ) . '@' . wp_parse_url( home_url(), PHP_URL_HOST );
	$wpdb->insert(
		wd_t( 'tx' ),
		array(
			'wedding_id'  => $id,
			'kind'        => $kind,
			'item'        => substr( sanitize_key( $a['item'] ), 0, 64 ),
			'name'        => substr( $a['name'], 0, 120 ),
			'email'       => $email,
			'message'     => substr( $a['message'], 0, 255 ),
			'amount'      => $amt,
			'fee'         => $fee,
			'net'         => $net,
			'charged'     => $charged,
			'currency'    => $cur,
			'cover_fee'   => $cover ? 1 : 0,
			'hide_amount' => empty( $a['hide_amount'] ) ? 0 : 1,
			'reference'   => $ref,
			'status'      => 'pending',
			'test'        => wd_pay_test_mode() ? 1 : 0,
			'created'     => current_time( 'mysql', true ),
		)
	);
	if ( wd_pay_test_mode() ) {
		wd_pay_mark_paid( $ref );
		return array(
			'reference' => $ref,
			'test'      => true,
			'charged'   => $charged,
			'fee'       => $fee,
		);
	}
	if ( ! wd_bachs_key() ) {
		return new WP_Error( 'off', __( 'Online payments are not switched on yet.', 'wwmd' ) );
	}
	$return = ! empty( $a['return'] ) ? $a['return'] : get_permalink( $id );
	$pay    = array( $cur, $charged );
	if ( ! in_array( $cur, wd_pay_currencies(), true ) ) {
		$usd = wd_to_ngn( 1, 'USD' );
		$pay = array( 'USD', $usd > 0 ? round( wd_to_ngn( $charged, $cur ) / $usd, 2 ) : $charged );
	}
	$res = wd_bachs(
		'POST',
		'/v1/checkout-sessions',
		array(
			'pricing'            => array(
				'currency' => $pay[0],
				'amount'   => number_format( (float) $pay[1], 2, '.', '' ),
			),
			'customer'           => array_filter(
				array(
					'email' => $email,
					'name'  => mb_substr( $a['name'], 0, 120 ),
				)
			),
			'reference'          => $ref,
			'success_url'        => add_query_arg( 'wdpay', $ref, $return ),
			'cancel_url'         => $return,
			'expires_in_minutes' => 120,
			'metadata'           => array(
				'site'    => wp_parse_url( home_url(), PHP_URL_HOST ),
				'kind'    => $kind,
				'wedding' => (string) $id,
				'amount'  => $charged . ' ' . $cur,
			),
		)
	);
	if ( is_wp_error( $res ) || empty( $res['checkout_url'] ) || empty( $res['checkout_id'] ) ) {
		$wpdb->update( wd_t( 'tx' ), array( 'status' => 'failed' ), array( 'reference' => $ref ) );
		return new WP_Error( 'gateway', __( 'We could not reach the payment page. Please try again.', 'wwmd' ) );
	}
	// Remember the checkout so the return page & webhook can confirm it with Bachs.
	set_transient( 'wd_chk_' . $ref, array( $res['checkout_id'], $pay[0], number_format( (float) $pay[1], 2, '.', '' ) ), 3 * DAY_IN_SECONDS );
	return array(
		'reference' => $ref,
		'url'       => $res['checkout_url'],
		'charged'   => $charged,
		'fee'       => $fee,
	);
}

/**
 * Check a payment with the provider & mark it paid.
 *
 * @param string $ref Reference.
 * @return bool Paid.
 */
function wd_pay_verify( $ref ) {
	global $wpdb;
	$tx = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . wd_t( 'tx' ) . ' WHERE reference = %s', $ref ) ); // phpcs:ignore WordPress.DB
	if ( ! $tx ) {
		return false;
	}
	if ( 'paid' === $tx->status ) {
		return true;
	}
	$chk = get_transient( 'wd_chk_' . $ref );
	if ( ! wd_bachs_key() || ! is_array( $chk ) ) {
		return false;
	}
	$res = wd_bachs( 'GET', '/v1/checkout-sessions/' . rawurlencode( $chk[0] ) );
	if ( is_wp_error( $res ) ) {
		return false;
	}
	$ok = isset( $res['reference'], $res['status'] ) && $res['reference'] === $ref && 'completed' === $res['status']
		&& ( empty( $res['payment_status'] ) || in_array( $res['payment_status'], array( 'succeeded', 'paid' ), true ) )
		&& strtoupper( (string) ( $res['currency'] ?? '' ) ) === $chk[1] && (float) ( $res['amount'] ?? 0 ) >= (float) $chk[2] - 0.009;
	if ( $ok ) {
		wd_pay_mark_paid( $ref );
		return true;
	}
	if ( ! empty( $res['status'] ) && in_array( $res['status'], array( 'expired', 'cancelled' ), true ) ) {
		$wpdb->update( wd_t( 'tx' ), array( 'status' => 'failed' ), array( 'reference' => $ref ) );
	}
	return false;
}

/**
 * Mark paid & apply what the payment was for.
 *
 * @param string $ref Reference.
 */
function wd_pay_mark_paid( $ref ) {
	global $wpdb;
	$t  = wd_t( 'tx' );
	$tx = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $t WHERE reference = %s", $ref ) ); // phpcs:ignore WordPress.DB
	if ( ! $tx || 'paid' === $tx->status ) {
		return;
	}
	$wpdb->update(
		$t,
		array(
			'status'  => 'paid',
			'paid_at' => current_time( 'mysql', true ),
		),
		array( 'id' => $tx->id )
	);
	if ( 'premium' === $tx->kind ) {
		// Plan payments (Pro or Premium; older payments were always Premium). The total counts toward
		// any plan, so moving up from Pro only costs the difference.
		$paid = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COALESCE(SUM(amount),0) FROM $t WHERE wedding_id = %d AND kind = 'premium' AND status = 'paid'", $tx->wedding_id ) ); // phpcs:ignore WordPress.DB
		update_post_meta( $tx->wedding_id, '_wd_premium_paid', $paid );
		// The plan switches on with the first instalment; Premium's printed cards ship once it is paid in full.
		$want = in_array( $tx->item, wd_paid_plans(), true ) ? $tx->item : 'premium';
		if ( wd_plan_rank( $want ) > wd_plan_rank( wd_plan( (int) $tx->wedding_id ) ) ) {
			update_post_meta( $tx->wedding_id, '_wd_plan', $want );
		}
		wd_refresh( (int) $tx->wedding_id );
	}
	if ( in_array( $tx->kind, array( 'spray', 'gift' ), true ) ) {
		wd_refresh( (int) $tx->wedding_id );
	}
	if ( 'design' === $tx->kind ) {
		update_post_meta( $tx->wedding_id, '_wd_custom', current_time( 'mysql', true ) );
		$wpdb->insert(
			wd_t( 'requests' ),
			array(
				'user_id'    => (int) get_post_field( 'post_author', $tx->wedding_id ),
				'wedding_id' => (int) $tx->wedding_id,
				'service'    => 'custom_design',
				'name'       => $tx->name,
				'email'      => $tx->email,
				/* translators: 1: amount, 2: reference */
				'details'    => sprintf( __( 'Custom design paid (%1$s). Reference %2$s. Ask the couple for their invitation card, colours & ideas on WhatsApp.', 'wwmd' ), wd_money( $tx->amount, $tx->currency ), $tx->reference ) . ( $tx->test ? ' [TEST]' : '' ),
				'status'     => 'new',
				'created'    => current_time( 'mysql', true ),
			)
		);
	}
	do_action( 'wd_paid', $tx );
}

/*
 * Bachs webhook: POST {site}/wp-json/wd/v1/pay/hook. Add it in the Bachs dashboard for the events
 * checkout.completed & collection.succeeded, then paste its signing secret into HQ › Settings.
 * Each delivery is signed: X-Bachs-Signature-V2 = "t={timestamp},v1={hex HMAC-SHA256 of "{t}.{raw body}"}".
 */
add_action(
	'rest_api_init',
	static function () {
		register_rest_route(
			'wd/v1',
			'/pay/hook',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => static function ( WP_REST_Request $r ) {
					$secret = trim( (string) wd_opt( 'bachs_webhook_secret' ) );
					$body   = $r->get_body();
					$head   = (string) $r->get_header( 'x_bachs_signature_v2' );
					$t      = 0;
					$sigs   = array();
					foreach ( explode( ',', $head ) as $part ) {
						$kv = explode( '=', trim( $part ), 2 );
						if ( 2 === count( $kv ) && 't' === $kv[0] ) {
							$t = (int) $kv[1];
						} elseif ( 2 === count( $kv ) && 'v1' === $kv[0] ) {
							$sigs[] = $kv[1];
						}
					}
					$good = false;
					if ( $secret && $t && abs( time() - $t ) <= 300 ) {
						$want = hash_hmac( 'sha256', $t . '.' . $body, $secret );
						foreach ( $sigs as $sig ) {
							$good = $good || hash_equals( $want, $sig );
						}
					}
					if ( ! $good ) {
						return new WP_REST_Response( array( 'ok' => false ), 401 );
					}
					$e   = json_decode( $body, true );
					do_action( 'wd_bachs_event', is_array( $e ) ? $e : array() );
					$ref = isset( $e['data']['reference'] ) ? preg_replace( '/[^A-Z0-9]/', '', strtoupper( (string) $e['data']['reference'] ) ) : '';
					if ( $ref && in_array( $e['type'] ?? '', array( 'checkout.completed', 'collection.succeeded' ), true ) ) {
						if ( ! get_transient( 'wd_chk_' . $ref ) && ! empty( $e['data']['checkout_id'] ) ) {
							global $wpdb;
							$tx = $wpdb->get_row( $wpdb->prepare( 'SELECT currency, charged FROM ' . wd_t( 'tx' ) . ' WHERE reference = %s', $ref ) ); // phpcs:ignore WordPress.DB
							if ( $tx ) {
								set_transient( 'wd_chk_' . $ref, array( sanitize_text_field( $e['data']['checkout_id'] ), $tx->currency, number_format( (float) $tx->charged, 2, '.', '' ) ), DAY_IN_SECONDS );
							}
						}
						wd_pay_verify( $ref );
					}
					return array( 'ok' => true );
				},
			)
		);
	}
);
