<?php
/**
 * Buzz coins: how weddings are ranked.
 *
 * Every figure the chart counts earns coins (HQ → Settings sets how many): page visits, guests
 * coming, love notes & their likes, vendor reviews, gifts, Instagram likes & comments, guest photos
 * & their likes, quiz plays, gate check-ins, livestream viewers & money sprayed. A figure only earns
 * coins when the page's plan has the feature behind it, so Free & Pro pages have fewer ways to earn
 * & find it hard to reach the top. "This week" counts the last 7 days; "all time" counts everything.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

/**
 * The figures that earn coins.
 *
 * @return array key => label, short (for chips), unit (counted per), coins (default), feature, icon, per (unit words)
 */
function wd_coin_figures() {
	return array(
		'views'       => array( 'label' => __( 'Page visits', 'wwmd' ), 'short' => __( 'Visits', 'wwmd' ), 'unit' => 1, 'coins' => 1, 'feature' => '', 'icon' => 'eye', 'per' => __( 'a visit', 'wwmd' ) ),
		'rsvps'       => array( 'label' => __( 'Guests coming (RSVP)', 'wwmd' ), 'short' => __( 'RSVPs', 'wwmd' ), 'unit' => 1, 'coins' => 3, 'feature' => 'rsvp', 'icon' => 'users', 'per' => __( 'a guest', 'wwmd' ) ),
		'wall'        => array( 'label' => __( 'Love notes', 'wwmd' ), 'short' => __( 'Love notes', 'wwmd' ), 'unit' => 1, 'coins' => 10, 'feature' => 'wall', 'icon' => 'chat', 'per' => __( 'a note', 'wwmd' ) ),
		'wall_likes'  => array( 'label' => __( 'Likes on love notes', 'wwmd' ), 'short' => __( 'Note likes', 'wwmd' ), 'unit' => 1, 'coins' => 1, 'feature' => 'wall', 'icon' => 'heart', 'per' => __( 'a like', 'wwmd' ) ),
		'reviews'     => array( 'label' => __( 'Vendor reviews', 'wwmd' ), 'short' => __( 'Reviews', 'wwmd' ), 'unit' => 1, 'coins' => 20, 'feature' => 'vendors', 'icon' => 'star', 'per' => __( 'a review', 'wwmd' ) ),
		'gifts'       => array( 'label' => __( 'Gifts & registry money', 'wwmd' ), 'short' => __( 'Gifts', 'wwmd' ), 'unit' => 1000, 'coins' => 2, 'feature' => 'registry', 'icon' => 'gift', 'per' => __( 'every ₦1,000', 'wwmd' ) ),
		'instagram'   => array( 'label' => __( 'Instagram likes & comments', 'wwmd' ), 'short' => __( 'Instagram', 'wwmd' ), 'unit' => 10, 'coins' => 1, 'feature' => 'instagram', 'icon' => 'insta', 'per' => __( 'every 10', 'wwmd' ) ),
		'photos'      => array( 'label' => __( 'Guest photos', 'wwmd' ), 'short' => __( 'Photos', 'wwmd' ), 'unit' => 1, 'coins' => 6, 'feature' => 'gallery', 'icon' => 'camera', 'per' => __( 'a photo', 'wwmd' ) ),
		'photo_likes' => array( 'label' => __( 'Likes on guest photos', 'wwmd' ), 'short' => __( 'Photo likes', 'wwmd' ), 'unit' => 1, 'coins' => 1, 'feature' => 'gallery', 'icon' => 'heart', 'per' => __( 'a like', 'wwmd' ) ),
		'quiz'        => array( 'label' => __( 'Quiz plays', 'wwmd' ), 'short' => __( 'Quiz', 'wwmd' ), 'unit' => 1, 'coins' => 4, 'feature' => 'quiz', 'icon' => 'trophy', 'per' => __( 'a play', 'wwmd' ) ),
		'checkins'    => array( 'label' => __( 'Guests checked in at the gate', 'wwmd' ), 'short' => __( 'Check-ins', 'wwmd' ), 'unit' => 1, 'coins' => 5, 'feature' => 'entry_pass', 'icon' => 'ticket', 'per' => __( 'a guest', 'wwmd' ) ),
		'live'        => array( 'label' => __( 'Livestream viewers', 'wwmd' ), 'short' => __( 'Live viewers', 'wwmd' ), 'unit' => 1, 'coins' => 6, 'feature' => 'livestream', 'icon' => 'live', 'per' => __( 'a viewer', 'wwmd' ) ),
		'spray'       => array( 'label' => __( 'Money sprayed', 'wwmd' ), 'short' => __( 'Spraying', 'wwmd' ), 'unit' => 1000, 'coins' => 5, 'feature' => 'spray', 'icon' => 'cash', 'per' => __( 'every ₦1,000', 'wwmd' ) ),
	);
}

/**
 * Coins a figure earns per unit (HQ → Settings).
 *
 * @param string $k Figure.
 * @return int
 */
function wd_coin_rate( $k ) {
	$r = (array) wd_opt( 'coins' );
	$f = wd_coin_figures();
	return isset( $r[ $k ] ) ? max( 0, (int) $r[ $k ] ) : (int) ( $f[ $k ]['coins'] ?? 0 );
}

/**
 * The figures a plan can earn coins from.
 *
 * @param string $plan Plan.
 * @return string[] Figure keys.
 */
function wd_plan_figures( $plan ) {
	$have = (array) wd_opt( 'features_' . $plan );
	$out  = array();
	foreach ( wd_coin_figures() as $k => $f ) {
		if ( wd_coin_rate( $k ) > 0 && ( ! $f['feature'] || in_array( $f['feature'], $have, true ) ) ) {
			$out[] = $k;
		}
	}
	return $out;
}

/**
 * Raw counts for a wedding.
 *
 * @param int         $id    Post ID.
 * @param string|null $since Only count activity after this UTC datetime.
 * @return array
 */
function wd_metrics( $id, $since = null ) {
	global $wpdb;
	$since_day = $since ? substr( $since, 0, 10 ) : '1970-01-01';
	$all       = ! $since;
	$since     = $since ? $since : '1970-01-01 00:00:00';
	$m         = array();
	$m['views'] = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COALESCE(SUM(views),0) FROM ' . wd_t( 'views' ) . ' WHERE wedding_id = %d AND day >= %s', $id, $since_day ) ); // phpcs:ignore WordPress.DB
	$m['views'] += $all ? (int) get_post_meta( $id, '_wd_views_base', true ) : 0;
	$m['rsvps'] = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COALESCE(SUM(guests),0) FROM ' . wd_t( 'rsvps' ) . " WHERE wedding_id = %d AND attending = 'yes' AND created >= %s", $id, $since ) ); // phpcs:ignore WordPress.DB
	$w = $wpdb->get_row( $wpdb->prepare( 'SELECT COUNT(*) n, COALESCE(SUM(likes),0) l FROM ' . wd_t( 'wall' ) . " WHERE wedding_id = %d AND status = 'approved' AND created >= %s", $id, $since ) ); // phpcs:ignore WordPress.DB
	$m['wall']       = $w ? (int) $w->n : 0;
	$m['wall_likes'] = $w ? (int) $w->l : 0;
	$m['reviews']    = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . wd_t( 'reviews' ) . " WHERE wedding_id = %d AND status = 'approved' AND created >= %s", $id, $since ) ); // phpcs:ignore WordPress.DB
	foreach ( array( 'gift' => 'gifts', 'spray' => 'spray' ) as $kind => $k ) {
		$m[ $k ] = 0;
		$rows    = $wpdb->get_results( $wpdb->prepare( 'SELECT currency, SUM(amount) a FROM ' . wd_t( 'tx' ) . " WHERE wedding_id = %d AND kind = %s AND status = 'paid' AND created >= %s GROUP BY currency", $id, $kind, $since ) ); // phpcs:ignore WordPress.DB
		foreach ( (array) $rows as $r ) {
			$m[ $k ] += wd_to_ngn( $r->a, $r->currency );
		}
		$m[ $k ] = (int) round( $m[ $k ] );
	}
	$m['instagram'] = (int) get_post_meta( $id, $all ? '_wd_ig_eng' : '_wd_ig_eng_week', true );
	$p = $wpdb->get_row( $wpdb->prepare( 'SELECT COUNT(*) n, COALESCE(SUM(likes),0) l FROM ' . wd_t( 'photos' ) . " WHERE wedding_id = %d AND status = 'approved' AND created >= %s", $id, $since ) ); // phpcs:ignore WordPress.DB
	$m['photos']      = $p ? (int) $p->n : 0;
	$m['photo_likes'] = $p ? (int) $p->l : 0;
	$m['quiz']        = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . wd_t( 'quiz' ) . ' WHERE wedding_id = %d AND created >= %s', $id, $since ) ); // phpcs:ignore WordPress.DB
	$m['checkins']    = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COALESCE(SUM(guests),0) FROM ' . wd_t( 'rsvps' ) . ' WHERE wedding_id = %d AND checked_in IS NOT NULL AND checked_in >= %s', $id, $since ) ); // phpcs:ignore WordPress.DB
	$m['live']        = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . wd_t( 'likes' ) . " WHERE obj_type = 'live' AND obj_id = %d AND created >= %s", $id, $since ) ); // phpcs:ignore WordPress.DB
	return $m;
}

/**
 * Coins from counts. Figures the page's plan does not have earn nothing & are marked locked.
 *
 * @param int   $id Wedding.
 * @param array $m  Counts.
 * @return array total, rows (key => n, coins, locked)
 */
function wd_coins( $id, $m ) {
	$rows  = array();
	$total = 0;
	foreach ( wd_coin_figures() as $k => $f ) {
		$n      = isset( $m[ $k ] ) ? (int) $m[ $k ] : 0;
		$locked = $f['feature'] && ! wd_can( $id, $f['feature'] );
		$c      = $locked ? 0 : intdiv( max( 0, $n ), max( 1, (int) $f['unit'] ) ) * wd_coin_rate( $k );
		$rows[ $k ] = array(
			'n'      => $n,
			'coins'  => $c,
			'locked' => $locked,
		);
		$total += $c;
	}
	return array(
		'total' => $total,
		'rows'  => $rows,
	);
}

/**
 * A wedding's coins: all time or this week.
 *
 * @param int    $id     Wedding.
 * @param string $period all|week.
 * @return int
 */
function wd_coin_total( $id, $period = 'all' ) {
	return (int) get_post_meta( $id, 'week' === $period ? '_wd_score_week' : '_wd_score', true );
}

/**
 * Recalculate & store a wedding's numbers & coins.
 *
 * @param int $id Post ID.
 * @return array
 */
function wd_refresh( $id ) {
	$all  = wd_metrics( $id );
	$week = wd_metrics( $id, gmdate( 'Y-m-d H:i:s', time() - 7 * DAY_IN_SECONDS ) );
	update_post_meta( $id, '_wd_m', $all );
	update_post_meta( $id, '_wd_m_week', $week );
	update_post_meta( $id, '_wd_score', wd_coins( $id, $all )['total'] );
	update_post_meta( $id, '_wd_score_week', wd_coins( $id, $week )['total'] );
	update_post_meta( $id, '_wd_sprayed', (int) $all['spray'] );
	delete_transient( 'wd_ranks' );
	return $all;
}

/**
 * Count a livestream viewer (once per browser per wedding).
 *
 * @param int $id Wedding.
 * @return bool Counted now.
 */
function wd_count_live( $id ) {
	global $wpdb;
	if ( wd_owns( $id ) || ! wd_can( $id, 'livestream' ) ) {
		return false;
	}
	$t     = wd_t( 'likes' );
	$voter = wd_voter();
	if ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $t WHERE obj_type = 'live' AND obj_id = %d AND voter = %s", $id, $voter ) ) ) { // phpcs:ignore WordPress.DB
		return false;
	}
	$wpdb->insert(
		$t,
		array(
			'obj_type' => 'live',
			'obj_id'   => $id,
			'voter'    => $voter,
			'created'  => current_time( 'mysql', true ),
		)
	);
	if ( ! get_transient( 'wd_vr_' . $id ) ) {
		set_transient( 'wd_vr_' . $id, 1, 5 * MINUTE_IN_SECONDS );
		wd_refresh( $id );
	}
	return true;
}

/**
 * Ranks (all time & this week) for every published wedding, cached for 10 minutes.
 *
 * @return array [ 'all' => [id => rank], 'week' => [id => rank] ]
 */
function wd_ranks() {
	$r = get_transient( 'wd_ranks' );
	if ( is_array( $r ) ) {
		return $r;
	}
	global $wpdb;
	$r = array(
		'all'  => array(),
		'week' => array(),
	);
	foreach ( array( 'all' => '_wd_score', 'week' => '_wd_score_week' ) as $k => $key ) {
		$ids = $wpdb->get_col( $wpdb->prepare( "SELECT p.ID FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = %s WHERE p.post_type = 'wd_wedding' AND p.post_status = 'publish' ORDER BY CAST(m.meta_value AS SIGNED) DESC, p.ID ASC", $key ) ); // phpcs:ignore WordPress.DB
		foreach ( $ids as $i => $id ) {
			$r[ $k ][ (int) $id ] = $i + 1;
		}
	}
	set_transient( 'wd_ranks', $r, 10 * MINUTE_IN_SECONDS );
	return $r;
}

/**
 * Rank of a wedding.
 *
 * @param int    $id     Post ID.
 * @param string $period all|week.
 * @return int 0 when unranked.
 */
function wd_rank( $id, $period = 'week' ) {
	if ( 'week' === $period && (int) get_post_meta( $id, '_wd_score_week', true ) <= 0 ) {
		return 0;
	}
	$r = wd_ranks();
	return isset( $r[ $period ][ $id ] ) ? (int) $r[ $period ][ $id ] : 0;
}

/**
 * Top weddings.
 *
 * @param string $period all|week.
 * @param int    $n      How many.
 * @return int[]
 */
function wd_top( $period = 'week', $n = 10 ) {
	$r = wd_ranks();
	return array_slice( array_keys( $r[ $period ] ), 0, $n );
}

/* Hourly refresh of every wedding (week scores fall as activity gets older). */
add_action(
	'init',
	static function () {
		if ( ! wp_next_scheduled( 'wd_hourly' ) ) {
			wp_schedule_event( time() + 300, 'hourly', 'wd_hourly' );
		}
	}
);

add_action(
	'wd_hourly',
	static function () {
		$ids = get_posts(
			array(
				'post_type'      => 'wd_wedding',
				'post_status'    => 'publish',
				'posts_per_page' => 500,
				'fields'         => 'ids',
			)
		);
		foreach ( $ids as $id ) {
			wd_refresh( $id );
		}
	}
);

/**
 * Count a page visit (one per visitor per wedding per day, owners not counted).
 *
 * @param int $id Post ID.
 */
function wd_count_view( $id ) {
	global $wpdb;
	if ( wd_owns( $id ) ) {
		return;
	}
	// Skip bots & link previews (WhatsApp, Instagram, Facebook...).
	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) ) : '';
	if ( '' === $ua || preg_match( '/bot|crawl|spider|slurp|preview|facebookexternalhit|whatsapp|headless|curl|wget|python|okhttp/', $ua ) ) {
		return;
	}
	// One visit per browser per wedding per day.
	$ck = 'wd_seen_' . $id;
	if ( isset( $_COOKIE[ $ck ] ) && wp_date( 'Ymd' ) === $_COOKIE[ $ck ] ) {
		return;
	}
	if ( ! headers_sent() ) {
		setcookie( $ck, wp_date( 'Ymd' ), time() + DAY_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
	}
	$t   = wd_t( 'views' );
	$day = gmdate( 'Y-m-d' );
	$row = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $t WHERE wedding_id = %d AND day = %s", $id, $day ) ); // phpcs:ignore WordPress.DB
	if ( $row ) {
		$wpdb->query( $wpdb->prepare( "UPDATE $t SET views = views + 1 WHERE id = %d", $row ) ); // phpcs:ignore WordPress.DB
	} else {
		$wpdb->insert(
			$t,
			array(
				'wedding_id' => $id,
				'day'        => $day,
				'views'      => 1,
			)
		);
	}
	// Refresh the score at most every 5 minutes for visits.
	if ( ! get_transient( 'wd_vr_' . $id ) ) {
		set_transient( 'wd_vr_' . $id, 1, 5 * MINUTE_IN_SECONDS );
		wd_refresh( $id );
	}
}

/* -------------------------------------------------------------------------
 * The weekly chart, Billboard style: rank, movement, last week, peak & weeks on chart.
 * Each ISO week the hourly job saves the week's final ranks to every charting wedding (_wd_chart).
 * ---------------------------------------------------------------------- */

/**
 * Chart week key, e.g. 2026-W45.
 *
 * @param int|null $ts Timestamp.
 * @return string
 */
function wd_chart_key( $ts = null ) {
	return gmdate( 'o-\WW', null === $ts ? time() : $ts );
}

/**
 * This week's chart: weddings with activity in the last 7 days, best first.
 *
 * @param int $n How many.
 * @return int[]
 */
function wd_chart_ids( $n = 50 ) {
	$out = array();
	foreach ( wd_top( 'week', 500 ) as $id ) {
		if ( (int) get_post_meta( $id, '_wd_score_week', true ) > 0 ) {
			$out[] = (int) $id;
		}
		if ( count( $out ) >= $n ) {
			break;
		}
	}
	return $out;
}

/**
 * Save last week's chart when a new week starts.
 */
function wd_chart_rollover() {
	$now = wd_chart_key();
	$was = get_option( 'wd_chart_week' );
	if ( $was === $now ) {
		return;
	}
	if ( $was ) {
		$last = array();
		foreach ( wd_chart_ids( 100 ) as $i => $id ) {
			$h          = (array) get_post_meta( $id, '_wd_chart', true );
			$h[ $was ]  = $i + 1;
			$last[ $id ] = $i + 1;
			update_post_meta( $id, '_wd_chart', array_slice( array_filter( $h ), -52, null, true ) );
		}
		update_option( 'wd_chart_last', $last, false );
	}
	update_option( 'wd_chart_week', $now, false );
}
add_action( 'wd_hourly', 'wd_chart_rollover', 20 );

/**
 * One wedding's chart line.
 *
 * @param int $id   Wedding.
 * @param int $rank This week's rank (0 when not charting).
 * @return array rank, last, peak, weeks, at1, move (up|down|same|new|re), delta
 */
function wd_chart_line( $id, $rank ) {
	$hist = array_filter( (array) get_post_meta( $id, '_wd_chart', true ) );
	$last = (array) get_option( 'wd_chart_last', array() );
	$lw   = isset( $last[ $id ] ) ? (int) $last[ $id ] : 0;
	$all  = $rank ? array_merge( array_values( $hist ), array( $rank ) ) : array_values( $hist );
	$move = 'same';
	if ( ! $hist ) {
		$move = 'new';
	} elseif ( ! $lw ) {
		$move = 're';
	} elseif ( $rank < $lw ) {
		$move = 'up';
	} elseif ( $rank > $lw ) {
		$move = 'down';
	}
	return array(
		'rank'  => (int) $rank,
		'last'  => $lw,
		'peak'  => $all ? (int) min( $all ) : 0,
		'weeks' => count( $all ),
		'at1'   => count( array_keys( $all, 1, false ) ),
		'move'  => $move,
		'delta' => $lw ? abs( $lw - $rank ) : 0,
		'hist'  => $hist,
	);
}

/**
 * The chart with each line & this week's awards (Hot debut, Biggest gain).
 *
 * @param int $n How many.
 * @return array id => line
 */
function wd_chart( $n = 50 ) {
	$rows  = array();
	$debut = 0;
	$gain  = array( 0, 0 );
	foreach ( wd_chart_ids( $n ) as $i => $id ) {
		$l           = wd_chart_line( $id, $i + 1 );
		$l['award']  = '';
		$rows[ $id ] = $l;
		if ( 'new' === $l['move'] && ! $debut ) {
			$debut = $id;
		}
		if ( 'up' === $l['move'] && $l['delta'] > $gain[1] ) {
			$gain = array( $id, $l['delta'] );
		}
	}
	if ( $debut ) {
		$rows[ $debut ]['award'] = 'debut';
	}
	if ( $gain[0] && $gain[1] >= 2 ) {
		$rows[ $gain[0] ]['award'] = 'gain';
	}
	return $rows;
}

/**
 * Movement label.
 *
 * @param array $l Chart line.
 * @return string HTML.
 */
function wd_chart_move( $l ) {
	switch ( $l['move'] ) {
		case 'up':
			/* translators: %d places */
			return '<span class="move move--up" title="' . esc_attr( sprintf( _n( 'Up %d place', 'Up %d places', $l['delta'], 'wwmd' ), $l['delta'] ) ) . '"><i aria-hidden="true">▲</i>' . (int) $l['delta'] . '</span>';
		case 'down':
			/* translators: %d places */
			return '<span class="move move--down" title="' . esc_attr( sprintf( _n( 'Down %d place', 'Down %d places', $l['delta'], 'wwmd' ), $l['delta'] ) ) . '"><i aria-hidden="true">▼</i>' . (int) $l['delta'] . '</span>';
		case 'new':
			return '<span class="move move--new">' . esc_html__( 'New', 'wwmd' ) . '</span>';
		case 're':
			return '<span class="move move--re">' . esc_html__( 'Back', 'wwmd' ) . '</span>';
	}
	return '<span class="move move--same" title="' . esc_attr__( 'Same place as last week', 'wwmd' ) . '"><i aria-hidden="true">=</i></span>';
}

/**
 * Tiny chart-history line (SVG) of a wedding's past ranks.
 *
 * @param array $l Chart line.
 * @return string
 */
function wd_chart_spark( $l ) {
	$pts = array_values( $l['hist'] );
	if ( $l['rank'] ) {
		$pts[] = $l['rank'];
	}
	$pts = array_slice( $pts, -10 );
	if ( count( $pts ) < 2 ) {
		return '';
	}
	$max = max( 10, max( $pts ) );
	$w   = 120;
	$h   = 36;
	$xy  = array();
	foreach ( $pts as $i => $r ) {
		$xy[] = round( $i * $w / ( count( $pts ) - 1 ), 1 ) . ',' . round( 4 + ( $r - 1 ) * ( $h - 8 ) / max( 1, $max - 1 ), 1 );
	}
	$end = explode( ',', end( $xy ) );
	return '<svg class="spark" viewBox="-4 0 128 36" width="128" height="36" role="img" aria-label="' . esc_attr__( 'Chart history: higher is better', 'wwmd' ) . '"><polyline points="' . esc_attr( implode( ' ', $xy ) ) . '" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/><circle cx="' . esc_attr( $end[0] ) . '" cy="' . esc_attr( $end[1] ) . '" r="4" fill="currentColor"/></svg>';
}
