<?php
/**
 * Branded emails & guest receipts (1.5.1).
 *
 * Every email the site sends (ours & WordPress's own, like password resets) goes out in our branded
 * HTML layout, with a plain-text copy for old mail apps. Emails sent for a Premium or High Society
 * couple (gift & spray receipts) carry the couple's names, colour & logo instead.
 *
 * Guests who send a gift or spray get a receipt by email & on its own page at /receipt/{reference}/.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

/**
 * Who the next email is from: '' (the site) or a wedding ID whose plan brands its receipts.
 *
 * @param int $id Wedding, or 0 to go back to the site's branding.
 * @return int
 */
function wd_mail_brand( $id = null ) {
	static $brand = 0;
	if ( null !== $id ) {
		$brand = (int) $id;
	}
	return $brand;
}

/**
 * Whether a wedding brands its receipts (Premium & High Society by default).
 *
 * @param int $id Wedding.
 * @return bool
 */
function wd_receipt_branded( $id ) {
	return $id && 'wd_wedding' === get_post_type( $id ) && wd_can( $id, 'receipts' );
}

/**
 * The look for a wedding's receipts & emails: names, colours, logo or initials, note.
 *
 * @param int $id Wedding (0 for the site).
 * @return array names, primary, accent, ink, logo (URL), initials, note, couple (bool)
 */
function wd_receipt_look( $id ) {
	if ( ! wd_receipt_branded( $id ) ) {
		return array(
			'couple'   => false,
			'names'    => get_bloginfo( 'name' ),
			'primary'  => '#6B2BD9',
			'accent'   => '#FFC21A',
			'ink'      => '#1C0B33',
			'logo'     => get_template_directory_uri() . '/assets/img/brand/icon-192.png',
			'initials' => '',
			'note'     => '',
		);
	}
	$d   = wd_get( $id );
	$des = $d['design'];
	$r   = wp_parse_args( (array) ( $d['receipt'] ?? array() ), array( 'logo' => '', 'note' => '' ) );
	return array(
		'couple'   => true,
		'names'    => wd_names( $d ),
		'primary'  => sanitize_hex_color( $des['primary'] ) ? $des['primary'] : '#6B2BD9',
		'accent'   => sanitize_hex_color( $des['accent'] ) ? $des['accent'] : '#FFC21A',
		'ink'      => sanitize_hex_color( $des['ink'] ) ? $des['ink'] : '#1C0B33',
		'logo'     => $r['logo'] ? (string) wp_get_attachment_image_url( (int) $r['logo'], 'medium' ) : '',
		'initials' => wd_initials( $d ),
		'note'     => (string) $r['note'],
	);
}

/**
 * The branded HTML for one email.
 *
 * @param string $subject Subject.
 * @param string $text    Plain text body.
 * @param int    $wid     Wedding whose branding to use (0 for the site).
 * @return string
 */
function wd_mail_html( $subject, $text, $wid = 0 ) {
	$lk    = wd_receipt_look( $wid );
	$site  = esc_url( home_url( '/' ) );
	$ink   = '#1C0B33';
	$body  = make_clickable( nl2br( esc_html( trim( $text ) ) ) );
	$body  = str_replace( '<a href=', '<a style="color:#6B2BD9;font-weight:700;word-break:break-all;" href=', $body );
	$font  = "'Plus Jakarta Sans',Arial,Helvetica,sans-serif";
	$head  = "'ITC Avant Garde Gothic','Century Gothic','Avenir Next',Arial,sans-serif";
	if ( $lk['couple'] ) {
		$mark = $lk['logo']
			? '<img src="' . esc_url( $lk['logo'] ) . '" width="56" height="56" alt="" style="display:block;width:56px;height:56px;border-radius:50%;object-fit:cover;border:2px solid #fff;">'
			: '<div style="width:56px;height:56px;line-height:56px;border-radius:50%;background:' . esc_attr( $lk['accent'] ) . ';color:' . esc_attr( $lk['ink'] ) . ';font:700 18px/56px ' . $head . ';text-align:center;">' . esc_html( $lk['initials'] ) . '</div>';
		$top  = '<td bgcolor="' . esc_attr( $lk['primary'] ) . '" style="background:' . esc_attr( $lk['primary'] ) . ';padding:22px 26px;border-radius:22px 22px 0 0;"><table role="presentation" cellpadding="0" cellspacing="0"><tr><td style="padding-right:14px;">' . $mark . '</td><td style="color:#fff;font:700 20px/1.2 ' . $head . ';">' . esc_html( $lk['names'] ) . '</td></tr></table></td>';
		$foot = esc_html( sprintf( /* translators: %s couple */ __( 'Sent for %s by Wedding Websites & More', 'wwmd' ), $lk['names'] ) );
	} else {
		$top  = '<td bgcolor="#23094B" style="background:#23094B;background-image:linear-gradient(45deg,#23094B 0%,#6B2BD9 60%,#A65BF2 100%);padding:20px 26px;border-radius:22px 22px 0 0;"><a href="' . $site . '" style="text-decoration:none;"><table role="presentation" cellpadding="0" cellspacing="0"><tr><td style="padding-right:12px;"><img src="' . esc_url( $lk['logo'] ) . '" width="44" height="44" alt="" style="display:block;width:44px;height:44px;"></td><td style="color:#fff;font:700 15px/1.25 ' . $head . ';letter-spacing:.06em;text-transform:uppercase;">' . esc_html__( 'Wedding Websites', 'wwmd' ) . '<br><span style="color:#E2C26B;">&amp;</span> ' . esc_html__( 'More', 'wwmd' ) . '</td></tr></table></a></td>';
		$foot = esc_html__( 'Premium Wedding Branding & Technology Solutions for High Class Weddings & Vendors', 'wwmd' );
	}
	$wa   = wd_whatsapp( __( 'Hello! I got an email from you & have a question.', 'wwmd' ) );
	$html = '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . esc_html( $subject ) . '</title></head>'
		. '<body style="margin:0;padding:0;background:#FFF6E5;">'
		. '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" bgcolor="#FFF6E5" style="background:#FFF6E5;"><tr><td align="center" style="padding:28px 12px;">'
		. '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;">'
		. '<tr>' . $top . '</tr>'
		. '<tr><td bgcolor="#ffffff" style="background:#ffffff;padding:26px;border-radius:0 0 22px 22px;color:' . $ink . ';font:400 15px/1.6 ' . $font . ';">'
		. '<h1 style="margin:0 0 14px;color:' . $ink . ';font:700 20px/1.3 ' . $head . ';">' . esc_html( $subject ) . '</h1>'
		. '<div>' . $body . '</div>'
		. '</td></tr>'
		. '<tr><td align="center" style="padding:18px 10px 0;color:#5E5272;font:400 12px/1.6 ' . $font . ';">' . $foot . '<br>'
		. '<a href="' . esc_url( $wa ) . '" style="color:#6B2BD9;font-weight:700;text-decoration:none;">' . esc_html__( 'WhatsApp', 'wwmd' ) . ' ' . esc_html( wd_whatsapp_display() ) . '</a> · '
		. '<a href="mailto:' . esc_attr( wd_opt( 'email' ) ) . '" style="color:#6B2BD9;font-weight:700;text-decoration:none;">' . esc_html( wd_opt( 'email' ) ) . '</a> · '
		. '<a href="' . $site . '" style="color:#6B2BD9;font-weight:700;text-decoration:none;">' . esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ) . '</a></td></tr>'
		. '</table></td></tr></table></body></html>';
	return $html;
}

/* Wrap every email in the branded layout. Emails that are already HTML are left alone. */
add_filter(
	'wp_mail',
	static function ( $a ) {
		$msg = (string) ( $a['message'] ?? '' );
		$hdr = $a['headers'] ?? array();
		$hdr = is_array( $hdr ) ? $hdr : preg_split( '/\r?\n/', (string) $hdr );
		$hdr = array_filter( array_map( 'trim', $hdr ) );
		foreach ( $hdr as $h ) {
			if ( preg_match( '#^content-type:\s*text/html#i', $h ) ) {
				return $a;
			}
		}
		if ( '' === trim( $msg ) || preg_match( '/<html|<body|<table/i', $msg ) ) {
			return $a;
		}
		$GLOBALS['wd_mail_plain'] = wp_strip_all_tags( $msg );
		$hdr = array_values(
			array_filter(
				$hdr,
				static function ( $h ) {
					return ! preg_match( '/^content-type:/i', $h );
				}
			)
		);
		$hdr[]        = 'Content-Type: text/html; charset=UTF-8';
		$a['headers'] = $hdr;
		$a['message'] = wd_mail_html( (string) $a['subject'], $msg, wd_mail_brand() );
		return $a;
	},
	20
);

/* A plain-text copy alongside the HTML. */
add_action(
	'phpmailer_init',
	static function ( $m ) {
		if ( ! empty( $GLOBALS['wd_mail_plain'] ) && 'text/html' === $m->ContentType ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName
			$m->AltBody = $GLOBALS['wd_mail_plain']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		}
		unset( $GLOBALS['wd_mail_plain'] );
	}
);

/* Our name on emails, not "WordPress". */
add_filter(
	'wp_mail_from_name',
	static function ( $name ) {
		return 'WordPress' === $name ? wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) : $name;
	}
);

/* -------------------------------------------------------------------------------------------
 * Guest receipts for gifts & sprays.
 * ---------------------------------------------------------------------------------------- */

/**
 * A receipt's own page.
 *
 * @param string $ref Payment reference.
 * @return string
 */
function wd_receipt_url( $ref ) {
	return home_url( '/receipt/' . rawurlencode( strtolower( $ref ) ) . '/' );
}

/**
 * A paid gift or spray by its reference.
 *
 * @param string $ref Reference.
 * @return object|null
 */
function wd_receipt_tx( $ref ) {
	global $wpdb;
	$ref = strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', (string) $ref ) );
	if ( strlen( $ref ) < 8 ) {
		return null;
	}
	return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . wd_t( 'tx' ) . " WHERE reference = %s AND status = 'paid' AND kind IN ('spray','gift') LIMIT 1", $ref ) ); // phpcs:ignore WordPress.DB
}

/**
 * What a gift was for.
 *
 * @param object $tx Payment.
 * @return string
 */
function wd_receipt_for( $tx ) {
	if ( 'spray' === $tx->kind ) {
		return __( 'Spray', 'wwmd' );
	}
	$d = wd_get( (int) $tx->wedding_id );
	foreach ( (array) $d['registry'] as $it ) {
		if ( isset( $it['id'] ) && (string) $it['id'] === (string) $tx->item ) {
			return (string) $it['title'];
		}
	}
	return __( 'Cash gift', 'wwmd' );
}

add_action(
	'init',
	static function () {
		add_rewrite_rule( '^receipt/([A-Za-z0-9]{8,24})/?$', 'index.php?wd_receipt=$matches[1]', 'top' );
	}
);
add_filter(
	'query_vars',
	static function ( $v ) {
		$v[] = 'wd_receipt';
		return $v;
	}
);
add_action(
	'template_redirect',
	static function () {
		$ref = get_query_var( 'wd_receipt' );
		if ( ! $ref ) {
			return;
		}
		$tx = wd_receipt_tx( $ref );
		if ( ! $tx ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			return;
		}
		status_header( 200 );
		nocache_headers();
		add_filter( 'wp_robots', 'wp_robots_no_robots' );
		add_filter(
			'pre_get_document_title',
			static function () use ( $tx ) {
				/* translators: %s reference */
				return sprintf( __( 'Receipt %s', 'wwmd' ), $tx->reference ) . ' · ' . get_bloginfo( 'name' );
			}
		);
		add_filter(
			'body_class',
			static function ( $c ) {
				return array_merge( array_diff( $c, array( 'home', 'blog' ) ), array( 'pay-link' ) );
			}
		);
		if ( ! defined( 'WD_NO_FX' ) ) {
			define( 'WD_NO_FX', true );
		}
		get_header();
		get_template_part( 'template-parts/receipt', null, array( 'tx' => $tx ) );
		get_footer();
		exit;
	}
);

/* Email the guest their receipt (when they gave an email address). */
add_action(
	'wd_paid',
	static function ( $tx ) {
		if ( ! in_array( $tx->kind, array( 'spray', 'gift' ), true ) || ! is_email( $tx->email ) || 0 === strpos( $tx->email, 'guest+' ) ) {
			return;
		}
		$d     = wd_get( (int) $tx->wedding_id );
		$names = wd_names( $d );
		$lk    = wd_receipt_look( (int) $tx->wedding_id );
		$lines = array(
			/* translators: %s guest */
			sprintf( __( 'Hi %s,', 'wwmd' ), $tx->name ? $tx->name : __( 'there', 'wwmd' ) ),
			'',
			/* translators: 1 amount 2 what for 3 couple */
			sprintf( __( 'Thank you! We received %1$s (%2$s) for %3$s.', 'wwmd' ), wd_money( $tx->charged ? $tx->charged : $tx->amount, $tx->currency ), wd_receipt_for( $tx ), $names ),
			'',
		);
		if ( $lk['note'] ) {
			$lines[] = $lk['note'];
			$lines[] = '';
		}
		/* translators: %s reference */
		$lines[] = sprintf( __( 'Reference: %s', 'wwmd' ), $tx->reference );
		/* translators: %s link */
		$lines[] = sprintf( __( 'Your receipt: %s', 'wwmd' ), wd_receipt_url( $tx->reference ) );
		wd_mail_brand( wd_receipt_branded( (int) $tx->wedding_id ) ? (int) $tx->wedding_id : 0 );
		/* translators: %s couple */
		wp_mail( $tx->email, sprintf( __( 'Your gift receipt for %s', 'wwmd' ), $names ), implode( "\n", $lines ) );
		wd_mail_brand( 0 );
	},
	30
);
