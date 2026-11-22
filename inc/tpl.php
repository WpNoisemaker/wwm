<?php
/**
 * The Premium designs: Royal, Pearl & Garden with their own parts, & the seven Studio designs (1.6) that share one base.
 *
 * Each design has its own top of the page, invitation, story & sign-off (template-parts/tpl/{key}/)
 * & its own stylesheet (assets/tpl/{key}.css). Every other section uses the standard part, dressed
 * by that stylesheet, so every feature keeps working. The couple's own colours & fonts apply on top.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

/**
 * Own parts & extra fonts of each Premium design.
 *
 * @return array key => [parts, fonts]
 */
function wd_tpl_extras() {
	return array(
		'royal'     => array(
			'parts' => array( 'hero', 'invitation', 'story', 'sign' ),
			'fonts' => array( 'Pinyon+Script' ),
		),
		'pearl'     => array(
			'parts' => array( 'hero', 'invitation', 'story', 'sign' ),
			'fonts' => array(),
		),
		'garden'    => array(
			'parts' => array( 'hero', 'invitation', 'story', 'sign' ),
			'fonts' => array( 'Great+Vibes' ),
		),
		'noir'     => array(
			'parts' => array( 'hero', 'sign' ),
			'fonts' => array(),
			'base'  => 'studio',
		),
		'coral'    => array(
			'parts' => array( 'hero', 'sign' ),
			'fonts' => array(),
			'base'  => 'studio',
		),
		'indigo'   => array(
			'parts' => array( 'hero', 'sign' ),
			'fonts' => array(),
			'base'  => 'studio',
		),
		'asooke'   => array(
			'parts' => array( 'hero', 'sign' ),
			'fonts' => array(),
			'base'  => 'studio',
		),
		'keepsake' => array(
			'parts' => array( 'hero', 'sign' ),
			'fonts' => array( 'Great+Vibes' ),
			'base'  => 'studio',
		),
		'savannah' => array(
			'parts' => array( 'hero', 'sign' ),
			'fonts' => array(),
			'base'  => 'studio',
		),
		'owambe'   => array(
			'parts' => array( 'hero', 'sign' ),
			'fonts' => array(),
			'base'  => 'studio',
		),
	);
}

/**
 * Where the countdown points: the next program, or the wedding day. Empty once it has all passed.
 *
 * @param array $d Wedding data.
 * @return array [time for data-countdown, the next program when there are several, or null]
 */
function wd_tpl_next( $d ) {
	$next = wd_next_program( $d );
	if ( $next ) {
		return array( str_replace( ' ', 'T', $next['when'] ) . ':00', count( wd_programs( $d ) ) > 1 ? $next : null );
	}
	if ( $d['date'] && wd_days_to( $d['date'] ) >= 0 ) {
		return array( $d['date'] . 'T' . ( $d['time'] ? $d['time'] : '12:00' ) . ':00', null );
	}
	return array( '', null );
}

/**
 * Countdown boxes (wedding.js fills in the numbers).
 *
 * @param string $dt    Time to count down to.
 * @param string $class Extra class.
 * @return string HTML.
 */
function wd_tpl_countdown( $dt, $class = '' ) {
	if ( ! $dt ) {
		return '';
	}
	$units = array(
		'd' => __( 'Days', 'wwmd' ),
		'h' => __( 'Hours', 'wwmd' ),
		'm' => __( 'Mins', 'wwmd' ),
		's' => __( 'Secs', 'wwmd' ),
	);
	$out = '<div class="countdown ' . esc_attr( $class ) . '" data-countdown="' . esc_attr( $dt ) . '" aria-label="' . esc_attr__( 'Countdown', 'wwmd' ) . '">';
	foreach ( $units as $k => $label ) {
		$out .= '<span><b data-cd="' . esc_attr( $k ) . '">' . ( 'd' === $k ? '0' : '00' ) . '</b>' . esc_html( $label ) . '</span>';
	}
	return $out . '</div>';
}

/**
 * The buttons at the top of a page: RSVP, spray or gift, livestream & share.
 *
 * @param array  $on  Sections on the page.
 * @param array  $d   Wedding data.
 * @param string $url Page link.
 * @return string HTML.
 */
function wd_tpl_buttons( $on, $d, $url ) {
	$out = '<div class="btn-row">';
	if ( isset( $on['rsvp'] ) && wd_days_to( $d['date'] ) >= 0 ) {
		$out .= '<a class="btn w-btn" href="#rsvp">' . esc_html__( 'RSVP', 'wwmd' ) . '</a>';
	}
	if ( isset( $on['spray'] ) ) {
		$out .= '<a class="btn w-btn w-btn--accent" href="#spray">' . wd_icon( 'cash', 18 ) . esc_html__( 'Spray', 'wwmd' ) . '</a>';
	} elseif ( isset( $on['registry'] ) ) {
		$out .= '<a class="btn w-btn w-btn--accent" href="#registry">' . wd_icon( 'gift', 18 ) . esc_html__( 'Send a gift', 'wwmd' ) . '</a>';
	}
	if ( isset( $on['livestream'] ) ) {
		$out .= '<a class="btn w-btn w-btn--live" href="#livestream">' . wd_icon( 'live', 18 ) . esc_html__( 'Watch live', 'wwmd' ) . '</a>';
	}
	$out .= '<button class="btn w-btn w-btn--ghost" type="button" data-share data-url="' . esc_url( $url ) . '" data-title="' . esc_attr( wd_names( $d ) ) . '">' . wd_icon( 'share', 18 ) . esc_html__( 'Share', 'wwmd' ) . '</button>';
	return $out . '</div>';
}

/**
 * "This Premium page is a gift from …" (verified pages only).
 *
 * @param int   $id Wedding.
 * @param array $d  Wedding data.
 * @return string HTML.
 */
function wd_tpl_gift( $id, $d ) {
	if ( ! wd_verified( $id ) || empty( $d['gift']['by'] ) ) {
		return '';
	}
	$out = '<p class="w-gift">' . wd_icon( 'gift', 16 ) . '<span>' . sprintf( /* translators: %s gifter */ esc_html__( 'This Premium page is a gift from %s', 'wwmd' ), '<b>' . esc_html( $d['gift']['by'] ) . '</b>' );
	if ( ! empty( $d['gift']['link'] ) ) {
		$out .= ' <a href="' . esc_url( $d['gift']['link'] ) . '" target="_blank" rel="noopener nofollow ugc">' . esc_html( wd_social_label( $d['gift']['link'] ) ) . '</a>';
	}
	return $out . '</span></p>';
}

/**
 * Buzz coins, the chart place, visits & the story card button.
 *
 * @param int $id   Wedding.
 * @param int $size Icon size.
 * @return string HTML.
 */
function wd_tpl_buzz( $id, $size = 16 ) {
	$rank = wd_rank( $id, 'week' );
	$m    = (array) get_post_meta( $id, '_wd_m', true );
	$out  = '<ul class="w-buzz">';
	$out .= '<li>' . wd_icon( 'coin', $size ) . '<b>' . esc_html( wd_short( wd_coin_total( $id ) ) ) . '</b> ' . esc_html__( 'Buzz coins', 'wwmd' ) . '</li>';
	if ( $rank ) {
		/* translators: %d rank */
		$out .= '<li><a href="' . esc_url( wd_url( 'rankings' ) ) . '">' . wd_icon( 'trophy', $size ) . '<b>' . esc_html( sprintf( __( 'No. %d', 'wwmd' ), $rank ) ) . '</b> ' . esc_html__( 'on the Buzz Chart', 'wwmd' ) . '</a></li>';
	}
	$out .= '<li>' . wd_icon( 'eye', $size ) . '<b>' . esc_html( wd_short( $m['views'] ?? 0 ) ) . '</b> ' . esc_html__( 'visits', 'wwmd' ) . '</li>';
	$out .= '<li><button type="button" class="linkish" data-story-card>' . wd_icon( 'insta', $size ) . esc_html__( 'Make a story card', 'wwmd' ) . '</button></li>';
	return $out . '</ul>';
}

/**
 * The share sheet & the canvas for the Instagram story card.
 *
 * @param int   $id Wedding.
 * @param array $d  Wedding data.
 */
function wd_tpl_share( $id, $d ) {
	$url  = get_permalink( $id );
	$rank = wd_rank( $id, 'week' );
	?>
	<div class="share-sheet" hidden data-share-sheet>
		<a href="<?php echo esc_url( 'https://wa.me/?text=' . rawurlencode( wd_names( $d ) . ' ' . __( 'are getting married! See their wedding page:', 'wwmd' ) . ' ' . $url ) ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'whatsapp', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>WhatsApp</a>
		<a href="<?php echo esc_url( 'https://twitter.com/intent/tweet?url=' . rawurlencode( $url ) . '&text=' . rawurlencode( wd_names( $d ) . ( $d['hashtag'] ? ' #' . $d['hashtag'] : '' ) ) ); ?>" target="_blank" rel="noopener">X</a>
		<a href="<?php echo esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $url ) ); ?>" target="_blank" rel="noopener">Facebook</a>
		<button type="button" data-copy="<?php echo esc_url( $url ); ?>"><?php echo wd_icon( 'link', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Copy link', 'wwmd' ); ?></button>
	</div>
	<canvas hidden data-story-canvas width="1080" height="1920" data-names="<?php echo esc_attr( wd_names( $d ) ); ?>" data-date="<?php echo esc_attr( wd_date( $d['date'], 'j F Y' ) ); ?>" data-city="<?php echo esc_attr( $d['city'] ); ?>" data-hash="<?php echo esc_attr( $d['hashtag'] ); ?>" data-link="<?php echo esc_attr( preg_replace( '#^https?://#', '', untrailingslashit( $url ) ) ); ?>" data-rank="<?php echo (int) $rank; ?>" data-cover="<?php echo esc_url( $d['cover'] ? wd_img_url( $d['cover'], 'wd-cover' ) : '' ); ?>"></canvas>
	<?php
}

/**
 * Venue, city & country without repeats.
 *
 * @param array $d Wedding data.
 * @return string
 */
function wd_tpl_place( $d ) {
	$place = (string) $d['venue'];
	foreach ( array( $d['city'], $d['country'] ) as $bit ) {
		if ( $bit && false === stripos( $place, $bit ) ) {
			$place .= ( $place ? ', ' : '' ) . $bit;
		}
	}
	return $place;
}

/**
 * The cover photo (or the cover art) for the top of a page, loaded first.
 *
 * @param int   $id Wedding.
 * @param array $d  Wedding data.
 * @return string HTML.
 */
function wd_tpl_cover( $id, $d ) {
	return str_replace( ' loading="lazy"', ' fetchpriority="high"', wd_cover( $id, $d, 'wd-cover' ) );
}

/**
 * A program's time, like "2:00 pm".
 *
 * @param array $e Program.
 * @return string
 */
function wd_prog_time( $e ) {
	return $e['time'] ? gmdate( 'g:i a', strtotime( '2000-01-01 ' . $e['time'] ) ) : '';
}

/**
 * Where a program is (or a note that the couple shares it with invited guests).
 *
 * @param array $e Program.
 * @return string
 */
function wd_prog_place( $e ) {
	if ( ! empty( $e['private'] ) ) {
		return __( 'The couple shares the venue with invited guests.', 'wwmd' );
	}
	$place = (string) $e['venue'];
	foreach ( array_map( 'trim', explode( ',', (string) $e['address'] ) ) as $bit ) {
		if ( '' !== $bit && false === stripos( $place, $bit ) ) {
			$place .= ( '' !== $place ? ', ' : '' ) . $bit;
		}
	}
	return $place;
}

/**
 * Directions link for a program, or ''.
 *
 * @param array $e Program.
 * @return string
 */
function wd_prog_map( $e ) {
	if ( ! empty( $e['private'] ) || ! trim( $e['venue'] . $e['address'] ) ) {
		return '';
	}
	return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( wd_prog_place( $e ) );
}

/**
 * Directions & calendar links for a program.
 *
 * @param array  $e     Program.
 * @param array  $d     Wedding data.
 * @param string $class Class for the paragraph.
 * @return string HTML.
 */
function wd_prog_links( $e, $d, $class ) {
	$map = wd_prog_map( $e );
	$cal = wd_prog_gcal( $e, $d );
	if ( ! $map && ! $cal ) {
		return '';
	}
	$out = '<p class="' . esc_attr( $class ) . '">';
	if ( $map ) {
		$out .= '<a href="' . esc_url( $map ) . '" target="_blank" rel="noopener">' . wd_icon( 'pin', 15 ) . esc_html__( 'Directions', 'wwmd' ) . '</a>';
	}
	if ( $cal ) {
		$out .= '<a href="' . esc_url( $cal ) . '" target="_blank" rel="noopener">' . wd_icon( 'calendar', 15 ) . esc_html__( 'Add to calendar', 'wwmd' ) . '</a>';
	}
	return $out . '</p>';
}

/**
 * A program's dress code with its colours.
 *
 * @param array  $e     Program.
 * @param string $class Class for the paragraph.
 * @return string HTML.
 */
function wd_prog_dress( $e, $class ) {
	if ( ! $e['dress'] ) {
		return '';
	}
	$out = '<p class="' . esc_attr( $class ) . '"><span>' . esc_html__( 'Dress code', 'wwmd' ) . '</span> ' . esc_html( $e['dress'] );
	foreach ( array( $e['c1'], $e['c2'] ) as $c ) {
		if ( $c ) {
			$out .= '<i class="swatch" style="background:' . esc_attr( $c ) . '"></i>';
		}
	}
	return $out . '</p>';
}

/**
 * A program's order of the day.
 *
 * @param array  $e     Program.
 * @param string $class Class for the list.
 * @return string HTML.
 */
function wd_prog_plan( $e, $class ) {
	$lines = wd_plan_lines( $e['plan'] );
	if ( ! $lines ) {
		return '';
	}
	$out = '<ol class="' . esc_attr( $class ) . '" aria-label="' . esc_attr__( 'Order of the day', 'wwmd' ) . '">';
	foreach ( $lines as $l ) {
		$out .= '<li>' . ( $l['time'] ? '<b>' . esc_html( $l['time'] ) . '</b>' : '' ) . '<span>' . esc_html( $l['title'] ) . ( $l['note'] ? '<small>' . esc_html( $l['note'] ) . '</small>' : '' ) . '</span></li>';
	}
	return $out . '</ol>';
}

/**
 * Roman numerals, for Royal: 14 => XIV.
 *
 * @param int $n Number (1 to 3999).
 * @return string
 */
function wd_roman( $n ) {
	$n   = (int) $n;
	$out = '';
	foreach ( array( 1000 => 'M', 900 => 'CM', 500 => 'D', 400 => 'CD', 100 => 'C', 90 => 'XC', 50 => 'L', 40 => 'XL', 10 => 'X', 9 => 'IX', 5 => 'V', 4 => 'IV', 1 => 'I' ) as $v => $r ) {
		while ( $n >= $v ) {
			$out .= $r;
			$n   -= $v;
		}
	}
	return $out;
}

/**
 * A leafy sprig drawn in fine line, for Garden. Drawn in currentColor.
 *
 * @param string $class Extra class.
 * @return string SVG.
 */
function wd_ga_sprig( $class = '' ) {
	$leaves = '';
	$pts    = array( array( 30, 150, -38 ), array( 46, 128, 30 ), array( 44, 104, -42 ), array( 62, 86, 26 ), array( 62, 62, -48 ), array( 80, 48, 18 ), array( 84, 26, -30 ) );
	foreach ( $pts as $i => $p ) {
		$s       = 1 - $i * 0.06;
		$leaves .= sprintf( '<path d="M0 0C6 -9 20 -10 28 0C20 9 6 9 0 0Z" transform="translate(%1$d %2$d) rotate(%3$d) scale(%4$.2f)"/><path d="M0 0H24" transform="translate(%1$d %2$d) rotate(%3$d) scale(%4$.2f)" stroke-width=".8"/>', $p[0], $p[1], $p[2] + ( $i % 2 ? 0 : 180 ), $s );
	}
	return '<svg class="ga-sprig ' . esc_attr( $class ) . '" viewBox="0 0 120 180" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 176C26 140 40 120 52 96S74 50 92 18"/>' . $leaves . '<circle cx="94" cy="14" r="5"/><circle cx="94" cy="14" r="1.6" fill="currentColor"/></svg>';
}

/**
 * A wreath of leaves around the couple's initials, for Garden's sign-off.
 *
 * @param string $text Initials.
 * @return string SVG.
 */
function wd_ga_wreath( $text ) {
	$leaves = '';
	for ( $i = 0; $i < 28; $i++ ) {
		if ( $i > 11 && $i < 16 ) {
			continue; // A gap at the bottom for the bow.
		}
		$a       = $i * ( 360 / 28 ) - 90;
		$r       = deg2rad( $a );
		$x       = 100 + cos( $r ) * 74;
		$y       = 100 + sin( $r ) * 74;
		$leaves .= sprintf( '<path d="M0 0C5 -7 15 -8 22 0C15 7 5 7 0 0Z" transform="translate(%.1f %.1f) rotate(%.1f)"/>', $x, $y, $a + ( $i % 2 ? 128 : 52 ) );
	}
	return '<svg class="ga-wreath" viewBox="0 0 200 200" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round"><circle cx="100" cy="100" r="74" stroke-width=".7" stroke-dasharray="2 5"/>' . $leaves . '<path d="M86 172c6-6 22-6 28 0M100 168l-8 18M100 168l8 18"/><text x="100" y="112" text-anchor="middle" class="ga-wreath__text" fill="currentColor" stroke="none">' . esc_html( $text ) . '</text></svg>';
}

/**
 * Small ornaments for the Studio designs, drawn in the couple's own colours (via currentColor & CSS).
 *
 * @param string $key   Design.
 * @param string $where hero, rule or sign.
 * @return string HTML.
 */
function wd_st_ornament( $key, $where ) {
	$acacia = '<svg class="st-land" viewBox="0 0 1200 160" preserveAspectRatio="xMidYMax slice" aria-hidden="true"><circle class="st-land__sun" cx="900" cy="54" r="38"/><path class="st-land__hill" d="M0 132c160-34 320-40 470-20s300 30 450 4 200-30 280-12v56H0z"/><g class="st-land__tree"><path d="M226 128c2-24 3-44 0-62M226 92c-14-8-30-12-46-12M228 82c14-10 28-14 44-14"/><path d="M150 74c30-18 70-24 110-18 26 4 44 12 58 22-34 6-74 6-112 2-22-2-40-4-56-6z"/></g><g class="st-land__tree st-land__tree--small"><path d="M1040 132c1-14 2-26 0-36M1040 106c-8-5-17-7-26-7M1041 100c8-6 16-8 25-8"/><path d="M996 96c17-10 40-14 62-10 15 2 25 7 33 12-19 4-42 4-63 1-12-1-23-2-32-3z"/></g></svg>';
	$heart  = '<svg class="st-heart" viewBox="0 0 40 34" aria-hidden="true"><path d="M20 31C9 23 3 17 3 10.5 3 6 6.5 3 10.6 3c3.7 0 6.6 2.3 9.4 6 2.8-3.7 5.7-6 9.4-6C33.5 3 37 6 37 10.5 37 17 31 23 20 31z"/></svg>';
	$adire  = '<svg class="st-adire" viewBox="0 0 120 120" aria-hidden="true"><circle cx="60" cy="60" r="54"/><circle cx="60" cy="60" r="40"/><circle cx="60" cy="60" r="26"/><circle cx="60" cy="60" r="12"/></svg>';
	switch ( $key . ':' . $where ) {
		case 'noir:hero':
			return '<div class="st-orn" aria-hidden="true"><i></i><i></i><i></i></div>';
		case 'noir:rule':
		case 'noir:sign':
			return '<p class="st-rule" aria-hidden="true"><i></i><svg viewBox="0 0 12 12"><path d="M6 0l6 6-6 6-6-6z"/></svg><i></i></p>';
		case 'coral:hero':
		case 'coral:sign':
			return '<div class="st-beads st-beads--band" aria-hidden="true">' . str_repeat( '<i></i>', 60 ) . '</div>';
		case 'coral:rule':
			return '<p class="st-beads" aria-hidden="true">' . str_repeat( '<i></i>', 9 ) . '</p>';
		case 'indigo:hero':
			return '<div class="st-orn" aria-hidden="true">' . $adire . $adire . '</div>';
		case 'indigo:sign':
			return '<div class="st-orn" aria-hidden="true">' . $adire . '</div>';
		case 'asooke:hero':
		case 'asooke:sign':
			return '<div class="st-strip" aria-hidden="true"></div>';
		case 'asooke:rule':
			return '<p class="st-rule" aria-hidden="true"><i></i></p>';
		case 'keepsake:rule':
		case 'keepsake:sign':
			return '<p class="st-rule" aria-hidden="true">' . $heart . '</p>';
		case 'savannah:hero':
		case 'savannah:sign':
			return $acacia;
		case 'savannah:rule':
			return '<p class="st-rule" aria-hidden="true"><i></i><span></span><i></i></p>';
		case 'owambe:hero':
		case 'owambe:sign':
			return '<div class="st-confetti" aria-hidden="true">' . str_repeat( '<i></i>', 18 ) . '</div>';
		case 'owambe:rule':
			return '<p class="st-rule" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></p>';
	}
	return '';
}
