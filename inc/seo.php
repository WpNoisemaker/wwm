<?php
/**
 * Search & sharing: descriptions, social cards, canonical links & structured data. Account, HQ, sign-in,
 * entry passes & table cards are kept out of search.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

/* Pass pages & printable table cards use their own templates. */
add_filter(
	'template_include',
	static function ( $tpl ) {
		if ( get_query_var( 'wd_pass' ) ) {
			return WD_DIR . '/pass.php';
		}
		if ( get_query_var( 'wd_cards' ) ) {
			return WD_DIR . '/table-cards.php';
		}
		if ( is_tax( array( 'wd_style', 'wd_place' ) ) ) {
			return WD_DIR . '/archive-wd_wedding.php';
		}
		if ( is_tax( 'wd_vtype' ) ) {
			return WD_DIR . '/archive-wd_vendor.php';
		}
		return $tpl;
	}
);

add_filter(
	'document_title_parts',
	static function ( $parts ) {
		if ( is_singular( 'wd_wedding' ) ) {
			$d = wd_get( get_the_ID() );
			/* translators: 1: names 2: city */
			$parts['title'] = trim( sprintf( __( '%1$s’s wedding %2$s', 'wwmd' ), wd_names( $d ), $d['hashtag'] ? '#' . $d['hashtag'] : '' ) );
		} elseif ( is_post_type_archive( 'wd_wedding' ) ) {
			$parts['title'] = __( 'Explore Nigerian & African weddings', 'wwmd' );
		} elseif ( is_tax( 'wd_place' ) ) {
			/* translators: %s: place */
			$parts['title'] = sprintf( __( 'Weddings in %s', 'wwmd' ), single_term_title( '', false ) );
		} elseif ( is_post_type_archive( 'wd_vendor' ) ) {
			$parts['title'] = __( 'Wedding vendors reviewed by guests', 'wwmd' );
		} elseif ( get_query_var( 'wd_pass' ) ) {
			$parts['title'] = __( 'Entry pass', 'wwmd' );
		} elseif ( get_query_var( 'wd_partner_id' ) ) {
			$p   = wd_partner( (int) get_query_var( 'wd_partner_id' ) );
			$sec = wd_sectors()[ $p['sector'] ][0] ?? '';
			/* translators: 1: business 2: service type 3: city */
			$parts['title'] = trim( sprintf( __( '%1$s · %2$s %3$s', 'wwmd' ), $p['business'], $sec, $p['city'] ? sprintf( /* translators: %s city */ __( 'in %s', 'wwmd' ), $p['city'] ) : '' ) );
		}
		return $parts;
	}
);

/**
 * Description for the current page.
 *
 * @return string
 */
function wd_meta_description() {
	if ( get_query_var( 'wd_partner_id' ) ) {
		$p = wd_partner( (int) get_query_var( 'wd_partner_id' ) );
		return wp_trim_words( $p['about'] ? $p['about'] : $p['business'], 30, '…' );
	}
	if ( is_page_template( 'page-templates/landing.php' ) ) {
		return __( 'Make a free wedding page with RSVP, entry passes, a cash & gift registry & online spraying. Book stays, rides, trips & asoebi safely, & join the community of African weddings at home & abroad.', 'wwmd' );
	}
	if ( is_front_page() ) {
		return __( 'Find the Nigerian & African weddings everyone is talking about, at home & abroad. Make a free wedding page with RSVP, a wall of love, guest photos, a quiz & a cash & gift registry.', 'wwmd' );
	}
	if ( is_singular( 'wd_wedding' ) ) {
		$d = wd_get( get_the_ID() );
		/* translators: 1: names 2: date 3: city */
		return wp_strip_all_tags( sprintf( __( '%1$s are getting married on %2$s in %3$s. RSVP, leave a love note, share your photos, play the couple quiz & send a gift.', 'wwmd' ), wd_names( $d ), wd_date( $d['date'] ), $d['city'] ) );
	}
	if ( is_singular( 'wd_vendor' ) ) {
		/* translators: 1: vendor 2: type */
		return sprintf( __( 'Reviews of %1$s, %2$s, written by guests at the weddings they worked.', 'wwmd' ), get_the_title(), strtolower( wd_vendor_type( get_the_ID() ) ) );
	}
	if ( is_post_type_archive( 'wd_wedding' ) || is_tax( array( 'wd_style', 'wd_place' ) ) ) {
		return __( 'Browse trending, upcoming & recent Nigerian & African weddings. Filter by city, country & wedding style.', 'wwmd' );
	}
	if ( is_post_type_archive( 'wd_vendor' ) ) {
		return __( 'Photographers, makeup artists, caterers, DJs, decorators & bakers, rated by real wedding guests.', 'wwmd' );
	}
	if ( is_singular() ) {
		return wp_trim_words( wp_strip_all_tags( get_the_excerpt() ), 28, '…' );
	}
	return get_bloginfo( 'description' );
}

add_action(
	'wp_head',
	static function () {
		$private = is_page_template( array( 'page-templates/account.php', 'page-templates/hq.php', 'page-templates/sign-in.php' ) ) || get_query_var( 'wd_pass' ) || get_query_var( 'wd_cards' );
		if ( $private ) {
			echo '<meta name="robots" content="noindex, nofollow">' . "\n";
			return;
		}
		if ( is_page_template( 'page-templates/landing.php' ) ) {
			echo '<meta name="robots" content="noindex, follow">' . "\n";
		}
		$desc  = wd_meta_description();
		$title = wp_get_document_title();
		$url   = is_singular() ? get_permalink() : home_url( add_query_arg( array() ) );
		$img   = get_template_directory_uri() . '/assets/img/brand/icon-512.png';
		if ( get_query_var( 'wd_partner_id' ) ) {
			$pid = (int) get_query_var( 'wd_partner_id' );
			$p   = wd_partner( $pid );
			$url = wd_partner_url( $pid );
			$f   = wd_partner_portfolio( $pid, $p );
			$pic = $p['cover'] ? $p['cover'] : ( $f ? $f[0][0] : '' );
			$img = $pic ? wd_img_url( $pic, 'wd-cover' ) : $img;
			printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );
		}
		if ( is_singular( 'wd_wedding' ) ) {
			$d   = wd_get( get_the_ID() );
			$src = $d['cover'] ? wd_img_url( $d['cover'], 'wd-cover' ) : '';
			$img = $src ? $src : $img;
		}
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
		printf( '<meta property="og:type" content="website"><meta property="og:title" content="%1$s"><meta property="og:description" content="%2$s"><meta property="og:url" content="%3$s"><meta property="og:image" content="%4$s"><meta name="twitter:card" content="summary_large_image">' . "\n", esc_attr( $title ), esc_attr( $desc ), esc_url( $url ), esc_url( $img ) );
		if ( is_singular( 'wd_wedding' ) ) {
			$d      = wd_get( get_the_ID() );
			$schema = array(
				'@context'    => 'https://schema.org',
				'@type'       => 'Event',
				'name'        => wd_names( $d ) . ' ' . __( 'wedding', 'wwmd' ),
				'startDate'   => $d['date'],
				'eventStatus' => 'https://schema.org/EventScheduled',
				'url'         => get_permalink(),
				'image'       => $img,
				'description' => $desc,
				'location'    => array(
					'@type'   => 'Place',
					'name'    => $d['venue'] ? $d['venue'] : $d['city'],
					'address' => trim( $d['city'] . ', ' . $d['country'], ', ' ),
				),
			);
			echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
		}
		if ( is_front_page() ) {
			$schema = array(
				'@context' => 'https://schema.org',
				'@type'    => 'Organization',
				'name'     => get_bloginfo( 'name' ),
				'url'      => home_url( '/' ),
				'logo'     => get_template_directory_uri() . '/assets/img/brand/icon-512.png',
				'email'    => wd_opt( 'email' ),
				'telephone' => '+' . preg_replace( '/\D/', '', wd_opt( 'whatsapp' ) ),
			);
			echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
		}
	},
	3
);

/* Private wedding pages & account pages stay out of the sitemap. */
add_filter(
	'wp_sitemaps_posts_query_args',
	static function ( $args, $type ) {
		if ( 'page' === $type ) {
			$made                   = (array) get_option( 'wd_made_pages', array() );
			$args['post__not_in']   = array_filter( array( $made['account'] ?? 0, $made['hq'] ?? 0, $made['sign-in'] ?? 0, $made['discover'] ?? 0 ) );
		}
		return $args;
	},
	10,
	2
);
