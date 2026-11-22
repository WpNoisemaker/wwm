<?php
/**
 * Plans: Free, Pro, Premium & High Society.
 *
 * What each plan includes is set in HQ › Settings (features_{plan}). Pro & Premium are paid once per
 * wedding, online (in full or in instalments) or on WhatsApp. High Society is priced for each wedding
 * on WhatsApp & switched on from HQ › Weddings. Moving up a plan only costs the difference.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

/**
 * The plans, lowest first.
 *
 * @return array key => name, price (null when quoted), pitch
 */
function wd_plans() {
	return array(
		'free'    => array(
			'name'  => __( 'Free', 'wwmd' ),
			'price' => 0,
			'pitch' => __( 'Everything you need to get your page live today.', 'wwmd' ),
		),
		'pro'     => array(
			'name'  => __( 'Pro', 'wwmd' ),
			'price' => (int) wd_opt( 'pro_price' ),
			'pitch' => __( 'Your squad, guest photos, quiz, entry passes & your own colours. No ads.', 'wwmd' ),
		),
		'premium' => array(
			'name'  => __( 'Premium', 'wwmd' ),
			'price' => (int) wd_opt( 'premium_price' ),
			'pitch' => __( 'Every feature: a Premium design, livestream, spraying, printed table QR cards & no fees.', 'wwmd' ),
		),
		'society' => array(
			'name'  => __( 'High Society', 'wwmd' ),
			'price' => null,
			'pitch' => __( 'Full event branding beyond your website, with prints & livestream coverage.', 'wwmd' ),
		),
	);
}

/**
 * Plan keys, lowest first.
 *
 * @return string[]
 */
function wd_plan_keys() {
	return array( 'free', 'pro', 'premium', 'society' );
}

/**
 * Plans couples can pay for online.
 *
 * @return string[]
 */
function wd_paid_plans() {
	return array( 'pro', 'premium' );
}

/**
 * Position of a plan (free 0 … society 3).
 *
 * @param string $plan Plan.
 * @return int
 */
function wd_plan_rank( $plan ) {
	$i = array_search( $plan, wd_plan_keys(), true );
	return false === $i ? 0 : (int) $i;
}

/**
 * A plan's name.
 *
 * @param string $plan Plan.
 * @return string
 */
function wd_plan_name( $plan ) {
	$p = wd_plans();
	return isset( $p[ $plan ] ) ? $p[ $plan ]['name'] : $p['free']['name'];
}

/**
 * Whether a page is on a paid plan (Pro, Premium or High Society).
 *
 * @param int $id Wedding.
 * @return bool
 */
function wd_is_paid( $id ) {
	return 'free' !== wd_plan( $id );
}

/**
 * The lowest plan that has a feature, for labels like "Pro" on a locked section.
 *
 * @param string $feature Feature key.
 * @return string
 */
function wd_feature_plan( $feature ) {
	foreach ( wd_plan_keys() as $plan ) {
		if ( in_array( $feature, (array) wd_opt( 'features_' . $plan ), true ) ) {
			return $plan;
		}
	}
	return 'society';
}

/**
 * What the couple has paid toward their plan online.
 *
 * @param int $id Wedding.
 * @return int Naira.
 */
function wd_plan_paid( $id ) {
	return (int) get_post_meta( $id, '_wd_premium_paid', true );
}

/**
 * What is left to pay for a plan (the price less what was already paid toward any plan).
 *
 * @param int    $id   Wedding.
 * @param string $plan pro|premium.
 * @return int
 */
function wd_plan_left( $id, $plan ) {
	$p = wd_plans();
	if ( empty( $p[ $plan ]['price'] ) ) {
		return 0;
	}
	return max( 0, (int) $p[ $plan ]['price'] - wd_plan_paid( $id ) );
}

/**
 * One instalment for a plan.
 *
 * @param int    $id   Wedding.
 * @param string $plan pro|premium.
 * @return int
 */
function wd_plan_instalment( $id, $plan ) {
	$price = (int) ( wd_plans()[ $plan ]['price'] ?? 0 );
	$inst  = max( 1, (int) wd_opt( 'instalments' ) );
	return min( wd_plan_left( $id, $plan ), (int) ceil( $price / $inst ) );
}

/**
 * Whether a couple can pay toward a plan now: a higher paid plan, or the rest of their own plan.
 *
 * @param int    $id   Wedding.
 * @param string $plan Plan.
 * @return bool
 */
function wd_plan_payable( $id, $plan ) {
	if ( ! in_array( $plan, wd_paid_plans(), true ) || wd_plan_left( $id, $plan ) < 1 ) {
		return false;
	}
	$now = wd_plan( $id );
	if ( wd_plan_rank( $plan ) > wd_plan_rank( $now ) ) {
		return true;
	}
	// The rest of the plan they are on, if they paid part of it online.
	return $plan === $now && wd_plan_paid( $id ) > 0;
}

/**
 * The features a plan adds on top of the plan below it.
 *
 * @param string $plan Plan.
 * @return array key => label
 */
function wd_plan_adds( $plan ) {
	$keys = wd_plan_keys();
	$i    = wd_plan_rank( $plan );
	$have = (array) wd_opt( 'features_' . $plan );
	$prev = $i > 0 ? (array) wd_opt( 'features_' . $keys[ $i - 1 ] ) : array();
	return array_intersect_key( wd_features(), array_flip( array_diff( $have, $prev ) ) );
}

/**
 * High Society extras (on top of every Premium feature).
 *
 * @return string[]
 */
function wd_society_perks() {
	return array(
		__( 'A page design made only for you, matched to your invitation & culture', 'wwmd' ),
		__( 'Event branding beyond your website: monogram, invitation cards, programmes, signage & table cards, printed for you', 'wwmd' ),
		__( 'Livestream coverage of your day by our crew', 'wwmd' ),
		__( 'Your own event manager on WhatsApp, from planning to the thank-you cards', 'wwmd' ),
	);
}

/**
 * WhatsApp link to ask for a High Society quote.
 *
 * @param int $id Wedding (optional).
 * @return string
 */
function wd_society_quote_url( $id = 0 ) {
	$text = __( 'Hello! We would like a quote for High Society: event branding, prints & livestream coverage for our wedding.', 'wwmd' );
	if ( $id ) {
		$d    = wd_get( $id );
		$text .= "\n" . sprintf( /* translators: 1: names 2: date 3: city 4: link */ __( 'Couple: %1$s · %2$s · %3$s · %4$s', 'wwmd' ), wd_names( $d ), wd_date( $d['date'], 'j M Y' ), $d['city'], get_permalink( $id ) );
	}
	return wd_whatsapp( $text );
}

/**
 * A small tag with the plan's name, for cards & lists.
 *
 * @param string $plan Plan.
 * @return string HTML.
 */
function wd_plan_tag( $plan ) {
	$icon = array(
		'pro'     => 'spark',
		'premium' => 'crown',
		'society' => 'gem',
	);
	return '<span class="tag tag--' . esc_attr( $plan ) . '">' . ( isset( $icon[ $plan ] ) ? wd_icon( $icon[ $plan ], 14 ) : '' ) . esc_html( wd_plan_name( $plan ) ) . '</span>';
}

/**
 * Service fee % a plan pays on a kind of payment.
 *
 * @param string $plan Plan.
 * @param string $kind spray|gift.
 * @return float
 */
function wd_fee_rate_for( $plan, $kind ) {
	if ( in_array( 'no_fees', (array) wd_opt( 'features_' . $plan ), true ) ) {
		return 0.0;
	}
	return (float) ( 'spray' === $kind ? wd_opt( 'fee_spray' ) : wd_opt( 'fee_gift' ) );
}
