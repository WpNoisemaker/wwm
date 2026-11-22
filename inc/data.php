<?php
/**
 * Wedding & vendor data: one array per wedding in post meta, plus a few query keys.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

/**
 * Empty wedding.
 *
 * @return array
 */
function wd_blank() {
	return array(
		'p1'       => '',
		'p2'       => '',
		'bride'    => 'p1',
		'tagline'  => '',
		'hashtag'  => '',
		'date'     => '',
		'time'     => '',
		'city'     => '',
		'country'  => '',
		'venue'    => '',
		'currency' => 'NGN',
		'cover'    => '',
		'photo'    => '',
		'invite'   => array(
			'hosts'   => '',
			'message' => '',
		),
		'events'   => array(),
		'story'    => array(),
		'party'    => array(),
		'ig'       => array(
			'handle' => '',
			'posts'  => array(),
		),
		'quiz'     => array(),
		'registry' => array(),
		'vendors'  => array(),
		'sections' => array(),
		'gift'     => array(
			'by'   => '',
			'link' => '',
		),
		'design'   => array(
			'template' => 'royal',
			'hero'     => '',
			'corners'  => '',
			'order'    => array(),
			'primary' => '#6B2BD9',
			'accent'  => '#FFC21A',
			'bg'      => '#FFF6E5',
			'ink'     => '#1C0B33',
			'head'    => 'avantgarde',
			'body'    => 'jakarta',
			'pattern' => 'adire',
		),
		'custom'   => array(),
		'receipt'  => array(
			'logo' => '',
			'note' => '',
		),
		'live'     => array(
			'on'    => 0,
			'url'   => '',
			'note'  => '',
			'opens' => '',
		),
		'spray'    => array(
			'on'         => 1,
			'show_total' => 1,
			'opens'      => '',
		),
		'pass'     => array(
			'on'  => 1,
			'pin' => '',
		),
		'rsvp'     => array(
			'deadline' => '',
			'max'      => 2,
		),
		'cards'    => array(
			'tables'   => 20,
			'status'   => '',
			'address'  => '',
			'phone'    => '',
			'tracking' => '',
		),
	);
}

/**
 * Wedding data.
 *
 * @param int $id Post ID.
 * @return array
 */
function wd_get( $id ) {
	$d = get_post_meta( $id, '_wd', true );
	$d = is_array( $d ) ? $d : array();
	$b = wd_blank();
	foreach ( $b as $k => $v ) {
		if ( ! isset( $d[ $k ] ) ) {
			$d[ $k ] = $v;
		} elseif ( is_array( $v ) && $v && array_keys( $v ) !== range( 0, count( $v ) - 1 ) && is_array( $d[ $k ] ) ) {
			$d[ $k ] = array_merge( $v, $d[ $k ] );
		}
	}
	return $d;
}

/**
 * Save wedding data & the keys used for search & sorting.
 *
 * @param int   $id Post ID.
 * @param array $d  Data.
 */
function wd_put( $id, $d ) {
	update_post_meta( $id, '_wd', $d );
	update_post_meta( $id, '_wd_date', $d['date'] );
	update_post_meta( $id, '_wd_city', $d['city'] );
	update_post_meta( $id, '_wd_prog', ',' . implode( ',', array_unique( wp_list_pluck( wd_programs( $d ), 'type' ) ) ) . ',' );
	$title = trim( $d['p1'] . ' & ' . $d['p2'], ' &' );
	if ( $title && get_the_title( $id ) !== $title ) {
		wp_update_post(
			array(
				'ID'         => $id,
				'post_title' => $title,
			)
		);
	}
	$ex = wp_strip_all_tags( $d['tagline'] ? $d['tagline'] : $d['invite']['message'] );
	if ( $ex ) {
		wp_update_post(
			array(
				'ID'           => $id,
				'post_excerpt' => wp_trim_words( $ex, 30, '…' ),
			)
		);
	}
	// Places: country > city.
	if ( $d['country'] ) {
		$parent = term_exists( $d['country'], 'wd_place', 0 );
		if ( ! $parent ) {
			$parent = wp_insert_term( $d['country'], 'wd_place' );
		}
		$pid   = is_array( $parent ) ? (int) $parent['term_id'] : (int) $parent;
		$terms = array( $pid );
		if ( $d['city'] ) {
			$city = term_exists( $d['city'], 'wd_place', $pid );
			if ( ! $city ) {
				$city = wp_insert_term( $d['city'], 'wd_place', array( 'parent' => $pid ) );
			}
			if ( ! is_wp_error( $city ) ) {
				$terms[] = is_array( $city ) ? (int) $city['term_id'] : (int) $city;
			}
		}
		wp_set_object_terms( $id, $terms, 'wd_place' );
	}
	clean_post_cache( $id );
}

/**
 * Plan: free, pro, premium or society (High Society).
 *
 * @param int $id Post ID.
 * @return string
 */
function wd_plan( $id ) {
	$p = (string) get_post_meta( $id, '_wd_plan', true );
	return in_array( $p, wd_plan_keys(), true ) ? $p : 'free';
}

/**
 * Whether a wedding's plan includes a feature.
 *
 * @param int    $id      Post ID.
 * @param string $feature Feature key.
 * @return bool
 */
function wd_can( $id, $feature ) {
	$list = (array) wd_opt( 'features_' . wd_plan( $id ) );
	return in_array( $feature, $list, true );
}

/**
 * Whether a section is turned on by the couple (on unless turned off) & allowed by the plan.
 *
 * @param int    $id  Post ID.
 * @param string $key Section.
 * @param array  $d   Data.
 * @return bool
 */
function wd_show( $id, $key, $d = null ) {
	$d = $d ? $d : wd_get( $id );
	if ( ! wd_can( $id, $key ) ) {
		return false;
	}
	return ! isset( $d['sections'][ $key ] ) || ! empty( $d['sections'][ $key ] );
}

/**
 * Service fee % for a kind of payment on this wedding.
 *
 * @param int    $id   Post ID.
 * @param string $kind spray|gift.
 * @return float
 */
function wd_fee_rate( $id, $kind ) {
	if ( wd_can( $id, 'no_fees' ) ) {
		return 0.0;
	}
	return (float) ( 'spray' === $kind ? wd_opt( 'fee_spray' ) : wd_opt( 'fee_gift' ) );
}

/**
 * Weddings owned by a user (newest first).
 *
 * @param int $uid User.
 * @return int[]
 */
function wd_user_weddings( $uid ) {
	if ( ! $uid ) {
		return array();
	}
	return get_posts(
		array(
			'post_type'      => 'wd_wedding',
			'author'         => $uid,
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => 20,
			'fields'         => 'ids',
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
}

/**
 * Whether the current user may manage this wedding.
 *
 * @param int $id Post ID.
 * @return bool
 */
function wd_owns( $id ) {
	$p = get_post( $id );
	if ( ! $p || 'wd_wedding' !== $p->post_type ) {
		return false;
	}
	return current_user_can( 'manage_options' ) || ( is_user_logged_in() && (int) $p->post_author === get_current_user_id() );
}

/**
 * Display names: "Tolani & Kayode".
 *
 * @param array $d Data.
 * @return string
 */
function wd_names( $d ) {
	return trim( $d['p1'] . ' & ' . $d['p2'], ' &' );
}

/**
 * The partner whose side a squad member or guest is on. Bridesmaids stand with the bride: the first
 * partner unless the couple says the bride is the second.
 *
 * @param array  $d    Data.
 * @param string $side bride|groom.
 * @return string
 */
function wd_side_name( $d, $side ) {
	$bride = 'p2' === ( $d['bride'] ?? 'p1' ) ? 'p2' : 'p1';
	if ( 'groom' === $side ) {
		return 'p1' === $bride ? $d['p2'] : $d['p1'];
	}
	return $d[ $bride ];
}

/**
 * A program's order of the day, from the lines the couple typed: "11:00 am | Arrival | A short note".
 *
 * @param string $plan Lines.
 * @return array[] time, title, note
 */
function wd_plan_lines( $plan ) {
	$out = array();
	foreach ( preg_split( '/\R/', (string) $plan ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$bits = array_map( 'trim', preg_split( '/\s*[|·]\s*|\s+[—–-]\s+/u', $line, 3 ) );
		$time = '';
		if ( preg_match( '/^\d{1,2}([:.]\d{2})?\s*(am|pm)?$/i', $bits[0] ) ) {
			$time = array_shift( $bits );
		}
		$out[] = array(
			'time'  => $time,
			'title' => $bits[0] ?? '',
			'note'  => $bits[1] ?? '',
		);
	}
	return array_values( array_filter( $out, static function ( $r ) { return '' !== $r['title']; } ) );
}

/**
 * Initials for medallions: "T & K".
 *
 * @param array $d Data.
 * @return string
 */
function wd_initials( $d ) {
	$a = $d['p1'] ? mb_strtoupper( mb_substr( $d['p1'], 0, 1 ) ) : '';
	$b = $d['p2'] ? mb_strtoupper( mb_substr( $d['p2'], 0, 1 ) ) : '';
	return trim( $a . ' & ' . $b, ' &' );
}

/**
 * Is a feature still waiting for its opening day?
 *
 * @param string $ymd     Opening day (Y-m-d) or ''.
 * @param bool   $the_day Also count the opening day itself as waiting (for a stream with no link yet).
 * @return bool
 */
function wd_opens_wait( $ymd, $the_day = false ) {
	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $ymd ) ) {
		return false;
	}
	$today = wp_date( 'Y-m-d' );
	return $the_day ? $today <= $ymd : $today < $ymd;
}

/**
 * Date for people: "Sat 14 Nov 2026".
 *
 * @param string $ymd Date.
 * @param string $fmt Format.
 * @return string
 */
function wd_date( $ymd, $fmt = 'D j M Y' ) {
	if ( ! $ymd ) {
		return '';
	}
	$t = strtotime( $ymd . ' 12:00:00' );
	return $t ? wp_date( $fmt, $t, new DateTimeZone( 'UTC' ) ) : '';
}

/**
 * Days until the wedding (negative after it).
 *
 * @param string $ymd Date.
 * @return int|null
 */
function wd_days_to( $ymd ) {
	if ( ! $ymd ) {
		return null;
	}
	$today = new DateTime( wp_date( 'Y-m-d' ) );
	$day   = DateTime::createFromFormat( '!Y-m-d', $ymd );
	if ( ! $day ) {
		return null;
	}
	$diff = $today->diff( $day );
	return (int) $diff->format( '%r%a' );
}

/**
 * Status chip for a wedding: Live now, Today, In 6 days, Just married, Married 3 weeks ago.
 *
 * @param int   $id Post ID.
 * @param array $d  Data.
 * @return array [class, label]
 */
function wd_status( $id, $d ) {
	$days = wd_days_to( $d['date'] );
	if ( null === $days ) {
		return array( '', '' );
	}
	if ( 0 === $days && wd_can( $id, 'livestream' ) && ! empty( $d['live']['on'] ) && $d['live']['url'] ) {
		return array( 'live', __( 'Live now', 'wwmd' ) );
	}
	if ( 0 === $days ) {
		return array( 'today', __( 'Today', 'wwmd' ) );
	}
	if ( $days > 0 ) {
		/* translators: %d: days */
		return array( 'soon', 1 === $days ? __( 'Tomorrow', 'wwmd' ) : sprintf( _n( 'In %d day', 'In %d days', $days, 'wwmd' ), $days ) );
	}
	if ( $days >= -7 ) {
		return array( 'just', __( 'Just married', 'wwmd' ) );
	}
	return array( 'past', __( 'Married', 'wwmd' ) . ' ' . wd_date( $d['date'], 'M Y' ) );
}

/**
 * Wedding styles with the illustration & colour used for them.
 *
 * @return array slug => [name, art, colour, chips]
 */
function wd_styles() {
	return array(
		'traditional'  => array( __( 'Traditional', 'wwmd' ), 'drum', 'butter', array( 'Yoruba engagement', 'Igba nkwu', 'Kamu', 'Knocking', 'Ruracio', 'Lobola', 'Kente engagement' ) ),
		'white-wedding' => array( __( 'White wedding', 'wwmd' ), 'cake', 'lavender', array( 'Church', 'Reception', 'First dance', 'Cake cutting', 'Bouquet toss' ) ),
		'nikkah'       => array( __( 'Nikkah', 'wwmd' ), 'lantern', 'mint', array( 'Fatiha', 'Kamu', 'Sadaki', 'Walima', 'Dinner' ) ),
		'court'        => array( __( 'Court & registry', 'wwmd' ), 'rings', 'sky', array( 'Ikoyi registry', 'City hall', 'Intimate lunch' ) ),
		'destination'  => array( __( 'Destination', 'wwmd' ), 'plane', 'coral', array( 'Zanzibar', 'Diani', 'Cape Town', 'Marrakech', 'Dubai' ) ),
		'diaspora'     => array( __( 'Diaspora', 'wwmd' ), 'globe', 'leaf', array( 'London', 'Houston', 'Toronto', 'Dublin', 'Atlanta' ) ),
	);
}

/**
 * Font choices for premium pages (Google Fonts) & the theme's own pair.
 *
 * @return array key => [label, css family, google family or '']
 */
function wd_fonts() {
	return array(
		'avantgarde' => array( 'Avant Garde (our brand font)', '"Adventor", "Century Gothic", sans-serif', '' ),
		'jakarta'    => array( 'Plus Jakarta Sans', '"Plus Jakarta Sans", system-ui, sans-serif', '' ),
		'jost'       => array( 'Jost', '"Jost", system-ui, sans-serif', 'Jost:wght@300;400;500;600' ),
		'playfair'   => array( 'Playfair Display', '"Playfair Display", Georgia, serif', 'Playfair+Display:wght@500;700' ),
		'cormorant'  => array( 'Cormorant Garamond', '"Cormorant Garamond", Georgia, serif', 'Cormorant+Garamond:wght@500;700' ),
		'cinzel'     => array( 'Cinzel', '"Cinzel", Georgia, serif', 'Cinzel:wght@500;700' ),
		'dmserif'    => array( 'DM Serif Display', '"DM Serif Display", Georgia, serif', 'DM+Serif+Display' ),
		'greatvibes' => array( 'Great Vibes (script)', '"Great Vibes", cursive', 'Great+Vibes' ),
		'marcellus'  => array( 'Marcellus', '"Marcellus", Georgia, serif', 'Marcellus' ),
		'montserrat' => array( 'Montserrat', '"Montserrat", system-ui, sans-serif', 'Montserrat:wght@400;600;700' ),
		'lora'       => array( 'Lora', '"Lora", Georgia, serif', 'Lora:wght@400;600' ),
	);
}

/**
 * Background patterns.
 *
 * @return array
 */
function wd_patterns() {
	return array(
		'adire' => __( 'Adire circles', 'wwmd' ),
		'kente' => __( 'Kente stripes', 'wwmd' ),
		'ankara' => __( 'Ankara blooms', 'wwmd' ),
		'mudcloth' => __( 'Mud cloth', 'wwmd' ),
		'none'  => __( 'Plain', 'wwmd' ),
	);
}

/**
 * The Premium page templates. Each one is a different layout & finish; the couple's colours & fonts
 * still apply on top. Free pages always use the Classic look in the brand colours.
 *
 * @return array key => [name, description, starting colours & fonts]
 */
function wd_templates() {
	return array(
		'royal'     => array(
			__( 'Royal', 'wwmd' ),
			__( 'Gold frames, a family crest & centred, formal type. Feels like a printed royal invitation.', 'wwmd' ),
			array( '#5B1230', '#C9A24D', '#FBF6EC', '#2A0E18', 'cinzel', 'lora', 'kente', 'right', 'soft' ),
		),
		'pearl'     => array(
			__( 'Pearl', 'wwmd' ),
			__( 'Clean & light: lots of air, a framed photo, a pearl-ringed monogram & soft champagne tones. Feels calm & elegant.', 'wwmd' ),
			array( '#8E5F55', '#E3C9AE', '#FFFCF8', '#2E2522', 'marcellus', 'jost', 'none', 'left', 'round' ),
		),
		'garden'    => array(
			__( 'Garden', 'wwmd' ),
			__( 'A wide photo on top, flowers drawn in fine line & a script “&”. Feels romantic & light.', 'wwmd' ),
			array( '#4E6B4A', '#E8B4A6', '#FBF7F2', '#25322A', 'cormorant', 'jakarta', 'ankara', 'top', 'round' ),
		),
		'noir'     => array(
			__( 'Noir', 'wwmd' ),
			__( 'Evening glamour: a deep, dark page, thin gold lines, a tall photo & big, graceful names. Feels like a black-tie night.', 'wwmd' ),
			array( '#C8A15A', '#E9DCC0', '#121114', '#F4EEE3', 'playfair', 'montserrat', 'none', 'right', 'square' ),
		),
		'coral'    => array(
			__( 'Coral', 'wwmd' ),
			__( 'Strings of coral beads, warm terracotta & a round photo. Made for Igbo, Edo & every bead-loving wedding.', 'wwmd' ),
			array( '#B4432D', '#E8A07A', '#FFF6F0', '#2B1611', 'dmserif', 'jakarta', 'none', 'left', 'round' ),
		),
		'indigo'   => array(
			__( 'Indigo', 'wwmd' ),
			__( 'Adire-inspired: indigo blues, tie-dye circles & a wide photo on top. Cool, calm & proudly ours.', 'wwmd' ),
			array( '#22385A', '#8DB2D8', '#F4F7FB', '#121C2B', 'cormorant', 'jost', 'adire', 'top', 'soft' ),
		),
		'asooke'   => array(
			__( 'Aso-oke', 'wwmd' ),
			__( 'Bold & modern: big names in strong letters, a woven aso-oke strip & clean blocks of colour.', 'wwmd' ),
			array( '#7A2E3B', '#D9A441', '#FBF6EE', '#24140F', 'montserrat', 'jakarta', 'none', 'left', 'square' ),
		),
		'keepsake' => array(
			__( 'Keepsake', 'wwmd' ),
			__( 'Like a photo album: your picture as a print with tape, handwritten touches & little hearts. Warm & personal.', 'wwmd' ),
			array( '#3E5C76', '#F2B84B', '#FAF6EF', '#22303C', 'lora', 'jakarta', 'none', 'right', 'square' ),
		),
		'savannah' => array(
			__( 'Savannah', 'wwmd' ),
			__( 'Golden hour across the savannah: warm sunset tones, an acacia tree on the skyline & soft, round photos.', 'wwmd' ),
			array( '#A4552B', '#EBB874', '#FFF8EE', '#33210F', 'marcellus', 'jost', 'none', 'left', 'round' ),
		),
		'owambe'   => array(
			__( 'Owambe', 'wwmd' ),
			__( 'The party page: bright colours, confetti, chunky cards & big buttons. Made for a loud, joyful celebration.', 'wwmd' ),
			array( '#0F7B5F', '#F4B400', '#FFFDF5', '#10241D', 'dmserif', 'montserrat', 'none', 'top', 'round' ),
		),
	);
}

/**
 * Which look a wedding page uses: a Premium template, or Classic (free & Pro pages).
 *
 * @param int   $id Wedding.
 * @param array $d  Data.
 * @return string
 */
function wd_template( $id, $d ) {
	if ( ! wd_can( $id, 'templates' ) ) {
		return 'classic';
	}
	$t = $d['design']['template'] ?? 'royal';
	if ( $t && $t === wd_custom_tpl( $id ) ) {
		return $t;
	}
	return array_key_exists( $t, wd_templates() ) ? $t : 'royal';
}

/**
 * Where the photo sits at the top of a Premium page.
 *
 * @return array key => label
 */
function wd_hero_layouts() {
	return array(
		'left'  => __( 'Photo left', 'wwmd' ),
		'right' => __( 'Photo right', 'wwmd' ),
		'top'   => __( 'Photo on top', 'wwmd' ),
		'names' => __( 'Names only', 'wwmd' ),
	);
}

/**
 * Corner styles for photos & cards on a Premium page.
 *
 * @return array key => label
 */
function wd_corner_styles() {
	return array(
		'square' => __( 'Square', 'wwmd' ),
		'soft'   => __( 'Soft', 'wwmd' ),
		'round'  => __( 'Round', 'wwmd' ),
	);
}

/**
 * The page's layout: the couple's choice, or their template's starting layout.
 * Pages without Premium designs (Free & Pro) always use the classic layout.
 *
 * @param int   $id Wedding.
 * @param array $d  Data.
 * @return array hero, corners
 */
function wd_layout( $id, $d ) {
	$tpl = wd_template( $id, $d );
	if ( 'classic' === $tpl ) {
		return array(
			'hero'    => 'left',
			'corners' => 'soft',
		);
	}
	$t = wd_tpl_info( $tpl )['start'];
	$h = $d['design']['hero'] ?? '';
	$c = $d['design']['corners'] ?? '';
	return array(
		'hero'    => isset( wd_hero_layouts()[ $h ] ) ? $h : $t[7],
		'corners' => isset( wd_corner_styles()[ $c ] ) ? $c : $t[8],
	);
}

/**
 * The sections of a wedding page, in the couple's order. Pages without Premium templates use the standard order.
 *
 * @param int   $id Wedding.
 * @param array $d  Data.
 * @return array key => menu label
 */
function wd_page_sections( $id = 0, $d = array() ) {
	$all = array(
		'invitation' => __( 'Invitation', 'wwmd' ),
		'rsvp'       => __( 'RSVP', 'wwmd' ),
		'livestream' => __( 'Live', 'wwmd' ),
		'spray'      => __( 'Spray', 'wwmd' ),
		'story'      => __( 'Our story', 'wwmd' ),
		'party'      => __( 'The squad', 'wwmd' ),
		'instagram'  => __( 'Instagram', 'wwmd' ),
		'gallery'    => __( 'Photos', 'wwmd' ),
		'wall'       => __( 'Wall of love', 'wwmd' ),
		'quiz'       => __( 'Quiz', 'wwmd' ),
		'registry'   => __( 'Registry', 'wwmd' ),
		'vendors'    => __( 'Vendors', 'wwmd' ),
	);
	$order = ( $id && $d && wd_can( $id, 'templates' ) ) ? (array) ( $d['design']['order'] ?? array() ) : array();
	$out   = array();
	foreach ( $order as $k ) {
		if ( isset( $all[ $k ] ) ) {
			$out[ $k ] = $all[ $k ];
		}
	}
	return $out + $all;
}

/**
 * A short label for a social link, e.g. "@hauwa.k on Instagram".
 *
 * @param string $url Link.
 * @return string
 */
function wd_social_label( $url ) {
	$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
	$path = trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' );
	$user = $path ? '@' . ltrim( strtok( $path, '/' ), '@' ) : '';
	$nets = array(
		'instagram.com' => 'Instagram',
		'tiktok.com'    => 'TikTok',
		'x.com'         => 'X',
		'twitter.com'   => 'X',
		'facebook.com'  => 'Facebook',
		'linkedin.com'  => 'LinkedIn',
		'youtube.com'   => 'YouTube',
		'snapchat.com'  => 'Snapchat',
	);
	foreach ( $nets as $dom => $name ) {
		if ( $host === $dom || substr( $host, -strlen( '.' . $dom ) ) === '.' . $dom ) {
			/* translators: 1: handle 2: network */
			return $user ? sprintf( __( '%1$s on %2$s', 'wwmd' ), $user, $name ) : $name;
		}
	}
	return $host ? preg_replace( '/^www\./', '', $host ) : '';
}

/**
 * Program types, for weddings with several parts (traditional, nikkah, church, reception…).
 * key => array( label, short label, icon, title pattern used to guess the type ).
 *
 * @return array
 */
function wd_program_types() {
	return array(
		'intro'     => array( __( 'Introduction or knocking', 'wwmd' ), __( 'Intro', 'wwmd' ), 'hands', '/introduc|knock|kokooko|\bkola\b/i' ),
		'dowry'     => array( __( 'Dowry & bride price', 'wwmd' ), __( 'Dowry', 'wwmd' ), 'hands', '/dowry|bride ?price|lobola|ruracio|umembeso|roora/i' ),
		'trad'      => array( __( 'Traditional wedding', 'wwmd' ), __( 'Trad', 'wwmd' ), 'drum', '/tradition|igba ?nkwu|engagement|\btrad\b|idana|marriage rite/i' ),
		'henna'     => array( __( 'Henna or Kamu night', 'wwmd' ), __( 'Henna', 'wwmd' ), 'henna', '/henna|kamu|kunshi|lalle|mehndi/i' ),
		'nikkah'    => array( __( 'Nikkah (Fatiha)', 'wwmd' ), __( 'Nikkah', 'wwmd' ), 'moon', '/nikk?ah?\b|fatiha|daurin/i' ),
		'ceremony'  => array( __( 'White wedding or church', 'wwmd' ), __( 'White wedding', 'wwmd' ), 'church', '/church|white wedding|holy|chapel|\bmass\b|ceremony|vows/i' ),
		'court'     => array( __( 'Court or registry', 'wwmd' ), __( 'Court', 'wwmd' ), 'scroll', '/court|registry|civil|register/i' ),
		'reception' => array( __( 'Reception', 'wwmd' ), __( 'Reception', 'wwmd' ), 'glass', '/reception|dinner|walima|banquet|party/i' ),
		'after'     => array( __( 'After-party', 'wwmd' ), __( 'After-party', 'wwmd' ), 'music', '/after ?-?party/i' ),
		'thanks'    => array( __( 'Thanksgiving service', 'wwmd' ), __( 'Thanksgiving', 'wwmd' ), 'church', '/thanksgiving/i' ),
		'other'     => array( __( 'Something else', 'wwmd' ), __( 'Program', 'wwmd' ), 'calendar', '' ),
	);
}

/**
 * The type of one program: the one the couple picked, or a guess from its name.
 *
 * @param array $e Program.
 * @return string
 */
function wd_program_type( $e ) {
	$types = wd_program_types();
	if ( ! empty( $e['type'] ) && isset( $types[ $e['type'] ] ) ) {
		return $e['type'];
	}
	foreach ( array( 'after', 'thanks', 'henna', 'nikkah', 'court', 'intro', 'dowry', 'trad', 'ceremony', 'reception' ) as $k ) {
		if ( preg_match( $types[ $k ][3], (string) ( $e['title'] ?? '' ) ) ) {
			return $k;
		}
	}
	return 'other';
}

/**
 * Programs in date order, each with its type, labels, icon & start.
 *
 * @param array $d Wedding data.
 * @return array
 */
function wd_programs( $d ) {
	$types = wd_program_types();
	$out   = array();
	foreach ( array_values( (array) $d['events'] ) as $i => $e ) {
		$e          = wp_parse_args( (array) $e, array( 'title' => '', 'date' => '', 'time' => '', 'venue' => '', 'address' => '', 'dress' => '', 'c1' => '', 'c2' => '', 'type' => '', 'kind' => '', 'private' => 0, 'note' => '', 'plan' => '' ) );
		$t          = wd_program_type( $e );
		$e['type']  = $t;
		$e['label'] = '' !== trim( (string) $e['kind'] ) ? trim( $e['kind'] ) : $types[ $t ][0];
		$e['short'] = $types[ $t ][1];
		$e['icon']  = $types[ $t ][2];
		$e['when']  = $e['date'] ? $e['date'] . ' ' . ( $e['time'] ? $e['time'] : '12:00' ) : '';
		$e['i']     = $i;
		$out[]      = $e;
	}
	usort(
		$out,
		function ( $a, $b ) {
			$x = $a['when'] ? $a['when'] : '9999';
			$y = $b['when'] ? $b['when'] : '9999';
			return strcmp( $x, $y ) ? strcmp( $x, $y ) : $a['i'] - $b['i'];
		}
	);
	return $out;
}

/**
 * The next program that has not started yet.
 *
 * @param array $d Wedding data.
 * @return array|null
 */
function wd_next_program( $d ) {
	$now = wp_date( 'Y-m-d H:i' );
	foreach ( wd_programs( $d ) as $e ) {
		if ( $e['when'] && $e['when'] >= $now ) {
			return $e;
		}
	}
	return null;
}

/**
 * Small program chips for cards: "Trad · Nikkah · Reception".
 *
 * @param array $d   Wedding data.
 * @param int   $max Most chips to show.
 * @return string HTML.
 */
function wd_program_chips( $d, $max = 4 ) {
	$p = wd_programs( $d );
	if ( count( $p ) < 2 ) {
		return '';
	}
	$out = '';
	foreach ( array_slice( $p, 0, $max ) as $e ) {
		$out .= '<li>' . wd_icon( $e['icon'], 14 ) . esc_html( $e['short'] ) . '</li>';
	}
	if ( count( $p ) > $max ) {
		$out .= '<li>+' . ( count( $p ) - $max ) . '</li>';
	}
	return '<ul class="prog-chips" aria-label="' . esc_attr__( 'Programs', 'wwmd' ) . '">' . $out . '</ul>';
}

/**
 * Guests coming to each program (from RSVPs).
 *
 * @param int   $id Post ID.
 * @param array $d  Wedding data.
 * @return array title => guests.
 */
function wd_rsvp_by_program( $id, $d ) {
	global $wpdb;
	$rows = $wpdb->get_results( $wpdb->prepare( 'SELECT guests, events FROM ' . wd_t( 'rsvps' ) . " WHERE wedding_id = %d AND attending = 'yes'", $id ) ); // phpcs:ignore WordPress.DB
	$out  = array();
	foreach ( wd_programs( $d ) as $e ) {
		$out[ $e['title'] ] = 0;
	}
	foreach ( $rows as $r ) {
		$picked = $r->events ? array_map( 'trim', explode( ',', $r->events ) ) : array_keys( $out );
		foreach ( $picked as $t ) {
			if ( isset( $out[ $t ] ) ) {
				$out[ $t ] += (int) $r->guests;
			}
		}
	}
	return $out;
}

/**
 * Paid pages (Pro, Premium & High Society) are verified: we know who the couple are.
 *
 * @param int $id Post ID.
 * @return bool
 */
function wd_verified( $id ) {
	return wd_is_paid( $id );
}

/**
 * Verified badge for names in lists & on the page. High Society pages get the gold badge.
 *
 * @param int $id   Post ID.
 * @param int $size Icon size.
 * @return string HTML.
 */
function wd_vbadge( $id, $size = 20 ) {
	if ( ! wd_verified( $id ) ) {
		return '';
	}
	$plan = wd_plan( $id );
	/* translators: %s plan name, e.g. Premium */
	$t = sprintf( __( 'Verified %s page', 'wwmd' ), wd_plan_name( $plan ) );
	return '<span class="vbadge vbadge--' . esc_attr( $plan ) . '" title="' . esc_attr( $t ) . '">' . wd_icon( 'verified', $size ) . '<span class="screen-reader-text">' . esc_html( $t ) . '</span></span>';
}

/**
 * Vendor data.
 *
 * @param int $id Vendor post ID.
 * @return array
 */
function wd_vendor( $id ) {
	$d = get_post_meta( $id, '_wdv', true );
	return wp_parse_args(
		is_array( $d ) ? $d : array(),
		array(
			'city'     => '',
			'country'  => '',
			'ig'       => '',
			'phone'    => '',
			'price'    => '',
			'verified' => 0,
			'colour'   => 'lavender',
		)
	);
}

/**
 * Vendor type name.
 *
 * @param int $id Vendor.
 * @return string
 */
function wd_vendor_type( $id ) {
	$t = get_the_terms( $id, 'wd_vtype' );
	return ( $t && ! is_wp_error( $t ) ) ? $t[0]->name : '';
}

/**
 * Recount a vendor's rating.
 *
 * @param int $vid Vendor.
 */
function wd_vendor_recount( $vid ) {
	global $wpdb;
	$row = $wpdb->get_row( $wpdb->prepare( 'SELECT COUNT(*) n, AVG(rating) r FROM ' . wd_t( 'reviews' ) . " WHERE vendor_id = %d AND status = 'approved'", $vid ) ); // phpcs:ignore WordPress.DB
	$n   = $row ? (int) $row->n : 0;
	$r   = $row && $row->r ? round( (float) $row->r, 1 ) : 0;
	update_post_meta( $vid, '_wd_rating', $r );
	update_post_meta( $vid, '_wd_reviews', $n );
	// Sort score: rating weighted by how many reviews there are.
	update_post_meta( $vid, '_wd_rating_score', round( $r * log( 2 + $n ), 3 ) );
}

/**
 * Stars.
 *
 * @param float $r Rating.
 * @return string
 */
function wd_stars( $r ) {
	$out = '<span class="stars" aria-label="' . esc_attr( sprintf( /* translators: %s rating */ __( 'Rated %s out of 5', 'wwmd' ), number_format_i18n( $r, 1 ) ) ) . '">';
	for ( $i = 1; $i <= 5; $i++ ) {
		$out .= '<i class="' . ( $r >= $i - 0.25 ? 'on' : ( $r >= $i - 0.75 ? 'half' : '' ) ) . '">★</i>';
	}
	return $out . '</span>';
}

/**
 * Money totals for a wedding.
 *
 * @param int $id Post ID.
 * @return array
 */
function wd_money_totals( $id ) {
	global $wpdb;
	$tx   = wd_t( 'tx' );
	$rows = $wpdb->get_results( $wpdb->prepare( "SELECT kind, SUM(amount) amount, SUM(net) net, SUM(fee) fee, COUNT(*) n FROM $tx WHERE wedding_id = %d AND status = 'paid' AND kind IN ('spray','gift') GROUP BY kind", $id ) ); // phpcs:ignore WordPress.DB
	$out  = array(
		'spray'     => 0,
		'spray_n'   => 0,
		'gift'      => 0,
		'gift_n'    => 0,
		'net'       => 0,
		'fees'      => 0,
		'paid_out'  => 0,
		'available' => 0,
	);
	foreach ( (array) $rows as $r ) {
		$out[ $r->kind ]        = (int) $r->amount;
		$out[ $r->kind . '_n' ] = (int) $r->n;
		$out['net']            += (int) $r->net;
		$out['fees']           += (int) $r->fee;
	}
	$out['paid_out']  = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COALESCE(SUM(amount),0) FROM ' . wd_t( 'payouts' ) . " WHERE wedding_id = %d AND status IN ('requested','paid')", $id ) ); // phpcs:ignore WordPress.DB
	$out['available'] = max( 0, $out['net'] - $out['paid_out'] );
	return $out;
}

/**
 * Raised per registry item.
 *
 * @param int $id Post ID.
 * @return array item id => [amount, n]
 */
function wd_registry_raised( $id ) {
	global $wpdb;
	$rows = $wpdb->get_results( $wpdb->prepare( 'SELECT item, SUM(amount) a, COUNT(*) n FROM ' . wd_t( 'tx' ) . " WHERE wedding_id = %d AND kind = 'gift' AND status = 'paid' GROUP BY item", $id ) ); // phpcs:ignore WordPress.DB
	$out  = array();
	foreach ( (array) $rows as $r ) {
		$out[ $r->item ] = array( (int) $r->a, (int) $r->n );
	}
	return $out;
}

/**
 * RSVP counts.
 *
 * @param int $id Post ID.
 * @return array
 */
function wd_rsvp_counts( $id ) {
	global $wpdb;
	$t   = wd_t( 'rsvps' );
	$yes = $wpdb->get_row( $wpdb->prepare( "SELECT COUNT(*) n, COALESCE(SUM(guests),0) g, SUM(CASE WHEN checked_in IS NOT NULL THEN 1 ELSE 0 END) c FROM $t WHERE wedding_id = %d AND attending = 'yes'", $id ) ); // phpcs:ignore WordPress.DB
	$no  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $t WHERE wedding_id = %d AND attending <> 'yes'", $id ) ); // phpcs:ignore WordPress.DB
	return array(
		'yes'     => $yes ? (int) $yes->n : 0,
		'guests'  => $yes ? (int) $yes->g : 0,
		'checked' => $yes ? (int) $yes->c : 0,
		'no'      => $no,
	);
}

/**
 * New unique entry pass code.
 *
 * @return string
 */
function wd_new_pass() {
	global $wpdb;
	do {
		$code = strtoupper( substr( str_replace( array( '0', 'O', '1', 'I', 'L' ), '', wp_generate_password( 16, false ) ), 0, 7 ) );
	} while ( strlen( $code ) < 7 || $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . wd_t( 'rsvps' ) . ' WHERE pass_code = %s', $code ) ) ); // phpcs:ignore WordPress.DB
	return $code;
}

/**
 * Ads for free pages (two slots). Falls back to two sponsored vendor cards.
 *
 * @return array
 */
function wd_ads() {
	$ads = array_values( array_filter( (array) wd_opt( 'ads' ), static function ( $a ) {
		return ! empty( $a['title'] ) && ! empty( $a['on'] );
	} ) );
	return array_slice( $ads, 0, 2 );
}
