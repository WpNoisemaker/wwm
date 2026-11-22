<?php
/**
 * Seller payout accounts (Bachs Connect sub-accounts).
 *
 * Each approved seller gets a sub-account on the payment provider. The seller finishes it once
 * (bank & identity, on the provider's own secure page) from their dashboard. When a customer pays a
 * payment link, the seller's share (after our commission) is transferred to their sub-account
 * straight away. We do not hold money. Sellers without a
 * finished payout account, test payments & failed transfers fall back to the manual "Withdraw" flow.
 *
 * The public site never names the provider.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

/**
 * A seller's payout account: id, ready (can receive transfers), at.
 *
 * @param int $uid Seller.
 * @return array
 */
function wd_seller_acct( $uid ) {
	return wp_parse_args(
		(array) get_user_meta( (int) $uid, 'wd_bachs_acct', true ),
		array(
			'id'    => '',
			'ready' => false,
			'at'    => '',
		)
	);
}

/**
 * Create the seller's sub-account if they have none. Quietly does nothing without a secret key.
 *
 * @param int $uid Seller.
 * @return array|WP_Error The account record.
 */
function wd_seller_acct_make( $uid ) {
	$a = wd_seller_acct( $uid );
	if ( $a['id'] ) {
		return $a;
	}
	if ( ! wd_bachs_key() ) {
		return new WP_Error( 'nokey', __( 'Payments are not set up on this site yet.', 'wwmd' ) );
	}
	$p   = wd_partner( $uid );
	$res = wd_bachs(
		'POST',
		'/v1/accounts',
		array(
			'display_name'  => $p ? $p['business'] : get_userdata( $uid )->display_name,
			'country'       => 'NG',
			'entity_type'   => 'company',
			'configuration' => array(
				'recipient' => array(
					'capabilities' => array(
						'payouts'   => array( 'requested' => true ),
						'transfers' => array( 'requested' => true ),
					),
				),
			),
			'metadata'      => array( 'seller' => (string) (int) $uid ),
		),
		array( 'Idempotency-Key' => 'wd-acct-' . md5( home_url() . '-' . $uid ) )
	);
	if ( is_wp_error( $res ) || empty( $res['id'] ) ) {
		return is_wp_error( $res ) ? $res : new WP_Error( 'bachs', __( 'Could not create the payout account.', 'wwmd' ) );
	}
	$a = array(
		'id'    => sanitize_text_field( $res['id'] ),
		'ready' => wd_seller_acct_can_transfer( $res ),
		'at'    => current_time( 'mysql', true ),
	);
	update_user_meta( $uid, 'wd_bachs_acct', $a );
	return $a;
}

/**
 * Whether an account object from the provider can receive transfers.
 *
 * @param array $acct Account object.
 * @return bool
 */
function wd_seller_acct_can_transfer( $acct ) {
	$found = false;
	$walk = static function ( $x ) use ( &$walk, &$found ) {
		if ( ! is_array( $x ) ) {
			return;
		}
		foreach ( $x as $k => $v ) {
			if ( 'transfers' === $k && is_array( $v ) && isset( $v['status'] ) && 'active' === $v['status'] ) {
				$found = true;
			}
			$walk( $v );
		}
	};
	$walk( $acct );
	return $found;
}

/**
 * Ask the provider whether the seller's account is ready, & remember it.
 *
 * @param int $uid Seller.
 * @return array The account record.
 */
function wd_seller_acct_refresh( $uid ) {
	$a = wd_seller_acct( $uid );
	if ( ! $a['id'] || ! wd_bachs_key() ) {
		return $a;
	}
	$res = wd_bachs( 'GET', '/v1/accounts/' . rawurlencode( $a['id'] ) );
	if ( ! is_wp_error( $res ) ) {
		$a['ready'] = wd_seller_acct_can_transfer( $res );
		update_user_meta( $uid, 'wd_bachs_acct', $a );
	}
	return $a;
}

/* Approving a seller gives them a payout account (when payments are set up). */
add_action(
	'wd_partner_approved',
	static function ( $uid ) {
		if ( ! get_user_meta( $uid, '_wd_sample', true ) ) {
			wd_seller_acct_make( $uid );
		}
	}
);

/**
 * Send the seller to the provider's secure page to finish their payout account.
 *
 * @param int $uid Seller.
 * @return string|WP_Error URL.
 */
function wd_seller_onboard_url( $uid ) {
	$a = wd_seller_acct_make( $uid );
	if ( is_wp_error( $a ) ) {
		return $a;
	}
	$back = wd_account_url( 'partner', array( 'payouts' => 'back' ) ) . '#payouts';
	$res  = wd_bachs(
		'POST',
		'/v1/accounts/' . rawurlencode( $a['id'] ) . '/account-links',
		array(
			'type'        => 'onboarding',
			'refresh_url' => wd_account_url( 'partner', array( 'payouts' => 'again' ) ) . '#payouts',
			'return_url'  => $back,
		)
	);
	if ( is_wp_error( $res ) || empty( $res['url'] ) ) {
		return is_wp_error( $res ) ? $res : new WP_Error( 'bachs', __( 'Could not open the payout set-up page. Try again in a minute.', 'wwmd' ) );
	}
	return esc_url_raw( $res['url'] );
}

/* A released booking: send the seller's share to their payout account, if it is ready. */
add_action(
	'wd_booking_released',
	static function ( $b ) {
		if ( ! $b || $b->test || (int) $b->net <= 0 || ! wd_bachs_key() || 'NGN' !== strtoupper( (string) $b->currency ) ) {
			return;
		}
		$a = wd_seller_acct( (int) $b->partner_id );
		if ( ! $a['id'] || ! $a['ready'] ) {
			return;
		}
		$res = wd_bachs(
			'POST',
			'/v1/transfers',
			array(
				'destination'    => $a['id'],
				'amount'         => number_format( (int) $b->net, 2, '.', '' ),
				'currency'       => 'NGN',
				'transfer_group' => 'bk' . (int) $b->id,
				'description'    => 'Booking ' . (int) $b->id,
			),
			array( 'Idempotency-Key' => 'wd-tr-' . md5( home_url() . '-bk' . (int) $b->id ) )
		);
		global $wpdb;
		if ( is_wp_error( $res ) ) {
			// Stays in the seller's balance to withdraw by hand.
			$wpdb->update( wd_t( 'bookings' ), array( 'log' => trim( $b->log . "\n" . wp_date( 'j M Y H:i' ) . ': automatic payout failed (' . $res->get_error_message() . ')' ) ), array( 'id' => (int) $b->id ) );
			return;
		}
		$wpdb->insert(
			wd_t( 'payouts' ),
			array(
				'user_id'      => (int) $b->partner_id,
				'wedding_id'   => 0,
				'amount'       => (int) $b->net,
				'currency'     => 'NGN',
				'bank'         => __( 'Payout account', 'wwmd' ),
				'account_no'   => '',
				'account_name' => '',
				'status'       => 'paid',
				'note'         => 'auto bk' . (int) $b->id . ( ! empty( $res['id'] ) ? ' ' . sanitize_text_field( $res['id'] ) : '' ),
				'created'      => current_time( 'mysql', true ),
				'paid_at'      => current_time( 'mysql', true ),
			)
		);
	},
	20
);

/* Account updates from the provider's webhook (account.updated, capability.updated). */
add_action(
	'wd_bachs_event',
	static function ( $e ) {
		if ( ! in_array( $e['type'] ?? '', array( 'account.updated', 'capability.updated' ), true ) ) {
			return;
		}
		$id = sanitize_text_field( (string) ( $e['data']['id'] ?? $e['data']['account'] ?? '' ) );
		if ( ! $id ) {
			return;
		}
		foreach ( get_users( array( 'meta_key' => 'wd_bachs_acct', 'fields' => 'ID' ) ) as $uid ) { // phpcs:ignore WordPress.DB.SlowDBQuery
			if ( wd_seller_acct( $uid )['id'] === $id ) {
				wd_seller_acct_refresh( (int) $uid );
			}
		}
	}
);
