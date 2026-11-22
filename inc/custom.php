<?php
/**
 * Custom templates: a one-of-a-kind look made for one couple (the Premium custom design add-on).
 *
 * HQ gives a custom template to a wedding & only that wedding can pick it. A custom template brings
 * its own parts (for example the hero or the story), styles & fonts. Every other section uses the
 * standard part with the template's styles on top, so all features keep working.
 *
 * A couple pack (inc/custom/{key}.php) sets up a couple's page & account once. It never changes a
 * page or account that already exists.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

/**
 * The custom templates in this theme.
 *
 * @return array key => name, desc, start (colours, fonts, pattern, hero, corners), fonts, parts, fields
 */
function wd_custom_templates() {
	return array(
		'ivory' => array(
			'name'   => __( 'Ivory & Gold', 'wwmd' ),
			'desc'   => __( 'Made for Ibrahim & Ummi: an ivory invitation card, shining gold, a leather diary for your story & small emerald touches.', 'wwmd' ),
			'start'  => array( '#7A5C3A', '#C3A46A', '#EEE4D3', '#3A2E23', 'cormorant', 'jost', 'none', 'names', 'square' ),
			'fonts'  => array( 'Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500', 'Jost:wght@300;400;500', 'Italianno', 'Caveat:wght@400;500' ),
			'parts'  => array( 'hero', 'invitation', 'story', 'party', 'sign' ),
			'fields' => array(
				'colours'   => array( 'textarea', __( 'Colours of the day (one a line: a name & a colour code)', 'wwmd' ), "Ivory #FBF7EF\nBeige #EEE4D3\nCarton #A9845A\nGold #C3A46A\nChampagne #E9D4A7" ),
				'sign'      => array( 'text', __( 'Sign-off at the bottom of your page', 'wwmd' ), __( 'With love & gratitude', 'wwmd' ) ),
				'venue_pic' => array( 'image', __( 'A photo of your venue (shows on the venue card)', 'wwmd' ), '' ),
			),
		),
	);
}

/**
 * The custom template HQ gave this wedding, or ''.
 *
 * @param int $id Wedding.
 * @return string
 */
function wd_custom_tpl( $id ) {
	$k = $id ? (string) get_post_meta( $id, '_wd_custom_tpl', true ) : '';
	return isset( wd_custom_templates()[ $k ] ) ? $k : '';
}

/**
 * One template's details, for a standard or a custom template.
 *
 * @param string $key Template.
 * @return array name, desc, start, fonts, parts, fields, custom
 */
function wd_tpl_info( $key ) {
	$c = wd_custom_templates();
	if ( isset( $c[ $key ] ) ) {
		return $c[ $key ] + array( 'custom' => true );
	}
	$all = wd_templates();
	$k   = isset( $all[ $key ] ) ? $key : 'royal';
	$x   = wd_tpl_extras()[ $k ] ?? array(
		'parts' => array(),
		'fonts' => array(),
	);
	return array(
		'name'   => $all[ $k ][0],
		'desc'   => $all[ $k ][1],
		'start'  => $all[ $k ][2],
		'fonts'  => $x['fonts'],
		'parts'  => $x['parts'],
		'base'   => $x['base'] ?? '',
		'fields' => array(),
		'custom' => false,
	);
}

/**
 * The template part for a page section: the design's own part if it has one (a custom template's
 * in template-parts/custom/, a Premium design's in template-parts/tpl/), else the standard part.
 *
 * @param string $tpl  Template.
 * @param string $part Section, e.g. hero or story.
 * @param bool   $only Return '' when the design has no such part.
 * @return string Path for get_template_part().
 */
function wd_part_path( $tpl, $part, $only = false ) {
	if ( 'classic' !== $tpl ) {
		$info = wd_tpl_info( $tpl );
		$path = 'template-parts/' . ( $info['custom'] ? 'custom' : 'tpl' ) . '/' . $tpl . '/' . $part;
		if ( in_array( $part, $info['parts'], true ) && is_readable( WD_DIR . '/' . $path . '.php' ) ) {
			return $path;
		}
	}
	return $only ? '' : 'template-parts/wedding/' . $part;
}

/**
 * The couple's values for their custom template's own fields (the template's defaults until saved).
 *
 * @param array  $d   Wedding data.
 * @param string $tpl Template.
 * @return array
 */
function wd_custom_vals( $d, $tpl ) {
	$out  = array();
	$have = (array) ( $d['custom'] ?? array() );
	foreach ( wd_tpl_info( $tpl )['fields'] as $k => $f ) {
		$out[ $k ] = array_key_exists( $k, $have ) ? (string) $have[ $k ] : $f[2];
	}
	return $out;
}

/**
 * Clean custom field values from a form. A photo field keeps its photo unless a new one is added
 * or the couple ticks "remove".
 *
 * @param string $tpl Template.
 * @param array  $raw Posted values.
 * @param array  $old Saved values.
 * @param int    $id  Wedding (for uploads).
 * @return array
 */
function wd_custom_clean( $tpl, $raw, $old = array(), $id = 0 ) {
	$out = array();
	foreach ( wd_tpl_info( $tpl )['fields'] as $k => $f ) {
		if ( 'image' === $f[0] ) {
			$up        = $id ? wd_upload_field( 'custom_' . $k, $id ) : 0;
			$keep      = isset( $old[ $k ] ) && is_numeric( $old[ $k ] ) ? (int) $old[ $k ] : '';
			$out[ $k ] = $up ? $up : ( empty( $raw[ $k . '_remove' ] ) ? $keep : '' );
			continue;
		}
		$v         = (string) ( $raw[ $k ] ?? '' );
		$out[ $k ] = 'textarea' === $f[0] ? mb_substr( sanitize_textarea_field( $v ), 0, 600 ) : mb_substr( sanitize_text_field( $v ), 0, 120 );
	}
	return $out;
}

/**
 * Colours from lines like "Ivory #FBF7EF".
 *
 * @param string $text Lines.
 * @return array [name, hex]
 */
function wd_colour_lines( $text ) {
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $line ) {
		if ( preg_match( '/^\s*(.*?)[\s:·–-]*(#(?:[0-9a-f]{6}|[0-9a-f]{3}))\s*$/i', $line, $m ) ) {
			$hex   = strtoupper( $m[2] );
			$out[] = array( '' !== trim( $m[1] ) ? trim( $m[1] ) : $hex, $hex );
		}
	}
	return array_slice( $out, 0, 8 );
}

/**
 * A year in words, as on a printed invitation: 2026 => "two thousand twenty-six".
 *
 * @param int $y Year.
 * @return string
 */
function wd_year_words( $y ) {
	$y = (int) $y;
	if ( $y < 2000 || $y > 2099 ) {
		return (string) $y;
	}
	$ones = array( '', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen' );
	$tens = array( '', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety' );
	$n    = $y - 2000;
	$rest = $n < 20 ? $ones[ $n ] : $tens[ intdiv( $n, 10 ) ] . ( $n % 10 ? '-' . $ones[ $n % 10 ] : '' );
	return trim( 'two thousand ' . $rest );
}

/**
 * The town of a program, from its address ("Are Osan, Ilorin, Kwara State" => "Ilorin").
 *
 * @param array $e Program.
 * @param array $d Wedding data.
 * @return string
 */
function wd_prog_city( $e, $d ) {
	$bits = array_values( array_filter( array_map( 'trim', explode( ',', (string) $e['address'] ) ), 'strlen' ) );
	for ( $i = count( $bits ) - 1; $i >= 0; $i-- ) {
		$b = $bits[ $i ];
		if ( preg_match( '/\bstate$|^fct$/i', $b ) || 0 === strcasecmp( $b, (string) $d['country'] ) ) {
			continue;
		}
		return $b;
	}
	return (string) $d['city'];
}

/**
 * A Google Calendar link for one program.
 *
 * @param array $e Program.
 * @param array $d Wedding data.
 * @return string
 */
function wd_prog_gcal( $e, $d ) {
	if ( ! $e['date'] ) {
		return '';
	}
	$start = wd_cal_time( $e, wd_wedding_tz( $d ) );
	$end   = $e['time'] ? $start : gmdate( 'Ymd', strtotime( $e['date'] . ' +1 day' ) );
	return add_query_arg(
		array(
			'action'   => 'TEMPLATE',
			'text'     => rawurlencode( $e['title'] . ' · ' . wd_names( $d ) ),
			'dates'    => $start . '/' . $end,
			'location' => empty( $e['private'] ) ? rawurlencode( trim( $e['venue'] . ' ' . $e['address'] ) ) : '',
			'details'  => rawurlencode( get_permalink() ? get_permalink() : '' ),
		),
		'https://calendar.google.com/calendar/render'
	);
}

/**
 * The time zone of the wedding's country, for countries with one time zone. Guests abroad then see
 * each program at the right time in their own calendar.
 *
 * @param array $d Wedding data.
 * @return DateTimeZone|null Null when we can't tell (the times are then left as written).
 */
function wd_wedding_tz( $d ) {
	$map  = array(
		'nigeria'              => 'Africa/Lagos',
		'ghana'                => 'Africa/Accra',
		'kenya'                => 'Africa/Nairobi',
		'south africa'         => 'Africa/Johannesburg',
		'cameroon'             => 'Africa/Douala',
		'senegal'              => 'Africa/Dakar',
		'uganda'               => 'Africa/Kampala',
		'tanzania'             => 'Africa/Dar_es_Salaam',
		'rwanda'               => 'Africa/Kigali',
		'ethiopia'             => 'Africa/Addis_Ababa',
		'egypt'                => 'Africa/Cairo',
		'zimbabwe'             => 'Africa/Harare',
		'zambia'               => 'Africa/Lusaka',
		'sierra leone'         => 'Africa/Freetown',
		'gambia'               => 'Africa/Banjul',
		'benin'                => 'Africa/Porto-Novo',
		'togo'                 => 'Africa/Lome',
		'united kingdom'       => 'Europe/London',
		'uk'                   => 'Europe/London',
		'ireland'              => 'Europe/Dublin',
		'france'               => 'Europe/Paris',
		'germany'              => 'Europe/Berlin',
		'netherlands'          => 'Europe/Amsterdam',
		'belgium'              => 'Europe/Brussels',
		'italy'                => 'Europe/Rome',
		'united arab emirates' => 'Asia/Dubai',
		'uae'                  => 'Asia/Dubai',
		'qatar'                => 'Asia/Qatar',
	);
	$name = $map[ strtolower( trim( (string) ( $d['country'] ?? '' ) ) ) ] ?? '';
	try {
		return $name ? new DateTimeZone( $name ) : null;
	} catch ( Exception $x ) {
		return null;
	}
}

/**
 * A program's start for a calendar: in UTC when the time zone is known, else as written.
 *
 * @param array             $e  Program.
 * @param DateTimeZone|null $tz Time zone.
 * @return string
 */
function wd_cal_time( $e, $tz ) {
	$day = gmdate( 'Ymd', strtotime( $e['date'] ) );
	if ( ! $e['time'] ) {
		return $day;
	}
	if ( $tz ) {
		try {
			$at = new DateTime( $e['date'] . ' ' . $e['time'], $tz );
			return $at->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Ymd\THis\Z' );
		} catch ( Exception $x ) {
			unset( $x );
		}
	}
	return $day . 'T' . str_replace( ':', '', $e['time'] ) . '00';
}

/**
 * Every program in one calendar file (.ics), as a data link.
 *
 * @param array $progs Programs.
 * @param array $d     Wedding data.
 * @return string
 */
function wd_prog_ics( $progs, $d ) {
	$esc = static function ( $t ) {
		return str_replace( array( '\\', ';', ',', "\n" ), array( '\\\\', '\\;', '\\,', '\\n' ), (string) $t );
	};
	$host = wp_parse_url( home_url(), PHP_URL_HOST );
	$tz   = wd_wedding_tz( $d );
	$out  = array( 'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Wedding Websites & More//Weddings//EN', 'CALSCALE:GREGORIAN' );
	foreach ( $progs as $e ) {
		if ( ! $e['date'] ) {
			continue;
		}
		$day   = gmdate( 'Ymd', strtotime( $e['date'] ) );
		$out[] = 'BEGIN:VEVENT';
		$out[] = 'UID:' . md5( wd_names( $d ) . $e['title'] . $e['date'] ) . '@' . $host;
		$out[] = 'DTSTAMP:' . gmdate( 'Ymd\THis\Z' );
		$out[] = $e['time'] ? 'DTSTART:' . wd_cal_time( $e, $tz ) : 'DTSTART;VALUE=DATE:' . $day;
		$out[] = 'SUMMARY:' . $esc( $e['title'] . ' · ' . wd_names( $d ) );
		if ( empty( $e['private'] ) && trim( $e['venue'] . $e['address'] ) ) {
			$out[] = 'LOCATION:' . $esc( trim( $e['venue'] . ', ' . $e['address'], ', ' ) );
		}
		$out[] = 'END:VEVENT';
	}
	$out[] = 'END:VCALENDAR';
	return 'data:text/calendar;charset=utf-8,' . rawurlencode( implode( "\r\n", $out ) );
}

/**
 * Set up every couple pack once. Runs from the theme update step.
 */
function wd_custom_install() {
	$files = glob( WD_DIR . '/inc/custom/*.php' );
	foreach ( $files ? $files : array() as $file ) {
		$key  = sanitize_key( basename( $file, '.php' ) );
		$done = (array) get_option( 'wd_custom_done', array() );
		if ( ! $key || isset( $done[ $key ] ) ) {
			continue;
		}
		$pack = include $file;
		if ( ! is_array( $pack ) ) {
			continue;
		}
		$id = wd_custom_make( $key, $pack );
		if ( $id ) {
			$done[ $key ] = $id;
			update_option( 'wd_custom_done', $done, false );
		}
	}
}

/**
 * Make one couple's page & account from a pack. Leaves everything alone if the page exists.
 *
 * @param string $key  Pack key.
 * @param array  $pack Pack.
 * @return int Wedding ID.
 */
function wd_custom_make( $key, $pack ) {
	global $wpdb;
	$slug  = sanitize_title( $pack['slug'] ?? $key );
	$found = get_posts(
		array(
			'post_type'   => 'wd_wedding',
			'post_status' => 'any',
			'numberposts' => 1,
			'fields'      => 'ids',
			'meta_key'    => '_wd_custom_key', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'  => $key, // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	if ( ! $found ) {
		$found = get_posts(
			array(
				'post_type'   => 'wd_wedding',
				'post_status' => 'any',
				'numberposts' => 1,
				'fields'      => 'ids',
				'name'        => $slug,
			)
		);
	}
	if ( $found ) {
		return (int) $found[0];
	}

	// The couple's account: an existing account with this email keeps its password & role.
	$u     = (array) ( $pack['user'] ?? array() );
	$email = sanitize_email( $u['email'] ?? '' );
	$uid   = 0;
	if ( $email ) {
		$have = get_user_by( 'email', $email );
		if ( $have ) {
			$uid = (int) $have->ID;
		} else {
			$login = sanitize_user( current( explode( '@', $email ) ), true );
			$base  = $login ? $login : 'couple';
			$i     = 1;
			while ( ! $login || username_exists( $login ) ) {
				$login = $base . ( ++$i );
			}
			$uid = wp_insert_user(
				array(
					'user_login'   => $login,
					'user_email'   => $email,
					'user_pass'    => wp_generate_password( 32, true, true ),
					'display_name' => sanitize_text_field( $u['name'] ?? '' ),
					'first_name'   => sanitize_text_field( $u['first'] ?? '' ),
					'role'         => 'couple',
				)
			);
			if ( is_wp_error( $uid ) ) {
				return 0;
			}
			// The password the couple asked for, stored only as a hash.
			if ( ! empty( $u['hash'] ) && 0 === strpos( $u['hash'], '$2y$' ) ) {
				$wpdb->update( $wpdb->users, array( 'user_pass' => $u['hash'] ), array( 'ID' => $uid ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				clean_user_cache( $uid );
			}
			if ( ! empty( $u['phone'] ) ) {
				update_user_meta( $uid, 'wd_phone', sanitize_text_field( $u['phone'] ) );
			}
		}
	}

	$data = (array) ( $pack['data'] ?? array() );
	$id   = wp_insert_post(
		array(
			'post_type'   => 'wd_wedding',
			'post_status' => 'publish',
			'post_name'   => $slug,
			'post_title'  => trim( ( $data['p1'] ?? '' ) . ' & ' . ( $data['p2'] ?? '' ), ' &' ),
			'post_author' => $uid,
		)
	);
	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}
	update_post_meta( $id, '_wd_custom_key', $key );

	// Photos from assets/custom/{key}/ go into the media library once.
	$imgs = array();
	foreach ( (array) ( $pack['images'] ?? array() ) as $name ) {
		$imgs[ $name ] = wd_custom_img( $key, $name, $id, $uid );
	}
	$pic = static function ( $v ) use ( $imgs ) {
		if ( is_string( $v ) && 0 === strpos( $v, 'img:' ) ) {
			return $imgs[ substr( $v, 4 ) ] ?? '';
		}
		return $v;
	};
	$data['cover'] = $pic( $data['cover'] ?? '' );
	$data['photo'] = $pic( $data['photo'] ?? '' );
	foreach ( (array) ( $data['custom'] ?? array() ) as $ck => $cv ) {
		$data['custom'][ $ck ] = $pic( $cv );
	}
	foreach ( array( 'story', 'registry', 'party' ) as $list ) {
		foreach ( (array) ( $data[ $list ] ?? array() ) as $i => $row ) {
			if ( isset( $row['image'] ) ) {
				$data[ $list ][ $i ]['image'] = $pic( $row['image'] );
			}
		}
	}

	// Vendors the couple named.
	$data['vendors'] = array();
	foreach ( (array) ( $pack['vendors'] ?? array() ) as $v ) {
		$vid = wd_find_or_make_vendor( $v['name'], $v['type'] ?? '', $v['ig'] ?? '', $v['city'] ?? ( $data['city'] ?? '' ), $v['country'] ?? ( $data['country'] ?? '' ) );
		if ( $vid ) {
			$data['vendors'][] = array(
				'id'   => $vid,
				'role' => $v['role'] ?? '',
			);
		}
	}

	$d = array_replace_recursive( wd_blank(), $data );
	foreach ( array( 'events', 'story', 'party', 'quiz', 'registry', 'vendors' ) as $list ) {
		$d[ $list ] = array_values( (array) ( $data[ $list ] ?? array() ) );
	}
	if ( ! empty( $d['pass']['on'] ) && empty( $d['pass']['pin'] ) ) {
		$d['pass']['pin'] = (string) wp_rand( 100000, 999999 );
	}
	update_post_meta( $id, '_wd_plan', 'premium' );
	if ( ! empty( $pack['template'] ) && isset( wd_custom_templates()[ $pack['template'] ] ) ) {
		update_post_meta( $id, '_wd_custom_tpl', $pack['template'] );
		update_post_meta( $id, '_wd_custom', time() );
	}
	if ( ! empty( $pack['styles'] ) ) {
		wp_set_object_terms( $id, array_map( 'sanitize_title', (array) $pack['styles'] ), 'wd_style' );
	}
	wd_put( $id, $d );
	if ( is_numeric( $d['cover'] ) && $d['cover'] ) {
		set_post_thumbnail( $id, (int) $d['cover'] );
	}
	if ( function_exists( 'wd_refresh' ) ) {
		wd_refresh( $id );
	}
	return (int) $id;
}

/**
 * Add a couple pack photo to the media library.
 *
 * @param string $key  Pack key.
 * @param string $name File name without .jpg.
 * @param int    $id   Wedding.
 * @param int    $uid  Owner.
 * @return int Attachment ID or 0.
 */
function wd_custom_img( $key, $name, $id, $uid ) {
	$path = WD_DIR . '/assets/custom/' . sanitize_key( $key ) . '/' . sanitize_file_name( $name ) . '.jpg';
	if ( ! is_readable( $path ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$up = wp_upload_bits( $key . '-' . $name . '.jpg', null, file_get_contents( $path ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( ! empty( $up['error'] ) ) {
		return 0;
	}
	$att = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/jpeg',
			'post_title'     => ucfirst( str_replace( '-', ' ', $name ) ),
			'post_status'    => 'inherit',
			'post_author'    => $uid,
		),
		$up['file'],
		$id
	);
	if ( ! $att || is_wp_error( $att ) ) {
		return 0;
	}
	wp_update_attachment_metadata( $att, wp_generate_attachment_metadata( $att, $up['file'] ) );
	return (int) $att;
}
