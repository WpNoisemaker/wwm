<?php
/**
 * Wedding news from around the web: headlines from other wedding sites' RSS feeds, shown on the blog
 * with a link back to the full story. HQ chooses the feeds. Headlines are fetched in the background
 * (hourly) & stored, so the blog never waits on another site.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wd_hourly', 'wd_news_refresh' );

/**
 * Feeds to read, from HQ → Settings.
 *
 * @return string[]
 */
function wd_news_feeds() {
	$s = wd_settings();
	if ( empty( $s['news_on'] ) ) {
		return array();
	}
	return array_values( array_filter( array_map( 'trim', preg_split( '/[\r\n]+/', (string) $s['news_feeds'] ) ) ) );
}

/**
 * Fetch every feed & store the newest headlines.
 *
 * @return int Headlines stored.
 */
function wd_news_refresh() {
	$feeds = wd_news_feeds();
	if ( ! $feeds ) {
		update_option( 'wd_news', array(), false );
		return 0;
	}
	require_once ABSPATH . WPINC . '/feed.php';
	$life = static function () {
		return 2 * HOUR_IN_SECONDS;
	};
	add_filter( 'wp_feed_cache_transient_lifetime', $life );
	$items = array();
	$seen  = array();
	foreach ( array_slice( $feeds, 0, 10 ) as $url ) {
		$feed = fetch_feed( $url );
		if ( is_wp_error( $feed ) ) {
			continue;
		}
		$host   = preg_replace( '/^www\./', '', (string) wp_parse_url( $url, PHP_URL_HOST ) );
		$source = trim( wp_strip_all_tags( html_entity_decode( (string) $feed->get_title(), ENT_QUOTES, 'UTF-8' ) ) );
		$source = $source ? $source : $host;
		foreach ( $feed->get_items( 0, 8 ) as $it ) {
			$link = esc_url_raw( (string) $it->get_permalink() );
			if ( ! $link || isset( $seen[ $link ] ) || ! in_array( wp_parse_url( $link, PHP_URL_SCHEME ), array( 'http', 'https' ), true ) ) {
				continue;
			}
			$seen[ $link ] = 1;
			$desc          = (string) $it->get_description();
			$items[]       = array(
				'title'  => mb_substr( trim( wp_strip_all_tags( html_entity_decode( (string) $it->get_title(), ENT_QUOTES, 'UTF-8' ) ) ), 0, 160 ),
				'link'   => $link,
				'source' => mb_substr( $source, 0, 60 ),
				'time'   => (int) $it->get_date( 'U' ),
				'text'   => wp_trim_words( wp_strip_all_tags( html_entity_decode( $desc, ENT_QUOTES, 'UTF-8' ) ), 26 ),
				'image'  => wd_news_image( $it, $desc . (string) $it->get_content() ),
			);
		}
	}
	remove_filter( 'wp_feed_cache_transient_lifetime', $life );
	usort(
		$items,
		static function ( $a, $b ) {
			return $b['time'] <=> $a['time'];
		}
	);
	$items = array_slice( array_filter( $items, static function ( $i ) {
		return '' !== $i['title'];
	} ), 0, 12 );
	update_option( 'wd_news', array_values( $items ), false );
	return count( $items );
}

/**
 * The story's picture: the feed's thumbnail or enclosure, else the first image in the story.
 *
 * @param SimplePie_Item $it   Feed item.
 * @param string         $html Description & content.
 * @return string URL or ''.
 */
function wd_news_image( $it, $html ) {
	$url = '';
	$enc = $it->get_enclosure();
	if ( $enc ) {
		$url = (string) $enc->get_thumbnail();
		if ( ! $url && $enc->get_link() && 0 === strpos( (string) $enc->get_type(), 'image' ) ) {
			$url = (string) $enc->get_link();
		}
	}
	if ( ! $url && preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $m ) ) {
		$url = html_entity_decode( $m[1], ENT_QUOTES, 'UTF-8' );
	}
	$url = esc_url_raw( $url );
	return 'https' === wp_parse_url( $url, PHP_URL_SCHEME ) || 'http' === wp_parse_url( $url, PHP_URL_SCHEME ) ? $url : '';
}

/**
 * Stored headlines.
 *
 * @param int $n How many.
 * @return array
 */
function wd_news( $n = 6 ) {
	if ( ! wd_news_feeds() ) {
		return array();
	}
	return array_slice( (array) get_option( 'wd_news', array() ), 0, $n );
}

/* -------------------------------------------------------------------------
 * 1.7.0: full stories with all their pictures, saved as DRAFTS for HQ to rewrite before publishing.
 * Other sites own their words & photos, so nothing goes live by itself; each draft keeps a link to
 * where it came from.
 * ---------------------------------------------------------------------- */

add_action(
	'init',
	static function () {
		if ( ! wp_next_scheduled( 'wd_news_import' ) ) {
			wp_schedule_event( time() + 600, 'hourly', 'wd_news_import' );
		}
	}
);
add_action( 'wd_news_import', 'wd_news_import' );

/**
 * Sites to copy full stories from, from HQ → Settings.
 *
 * @return string[]
 */
function wd_news_import_feeds() {
	$s = wd_settings();
	if ( empty( $s['news_import'] ) ) {
		return array();
	}
	return array_values( array_filter( array_map( 'trim', preg_split( '/[\r\n]+/', (string) $s['news_import_feeds'] ) ) ) );
}

/**
 * The "Wedding news" category for imported drafts.
 *
 * @return int
 */
function wd_news_cat() {
	$t = term_exists( 'wedding-news', 'category' );
	if ( ! $t ) {
		$t = wp_insert_term( __( 'Wedding news', 'wwmd' ), 'category', array( 'slug' => 'wedding-news' ) );
	}
	return is_array( $t ) ? (int) $t['term_id'] : (int) $t;
}

/**
 * Copy one picture into the media library.
 *
 * @param string $url  Picture address.
 * @param int    $post Draft it belongs to.
 * @param string $alt  Alt text.
 * @return int Attachment ID or 0.
 */
function wd_news_sideload( $url, $post, $alt ) {
	$url = esc_url_raw( $url, array( 'http', 'https' ) );
	if ( ! $url ) {
		return 0;
	}
	$tmp = download_url( $url, 20 );
	if ( is_wp_error( $tmp ) ) {
		return 0;
	}
	if ( filesize( $tmp ) > 10 * MB_IN_BYTES ) {
		wp_delete_file( $tmp );
		return 0;
	}
	$name = sanitize_file_name( wp_basename( (string) wp_parse_url( $url, PHP_URL_PATH ) ) );
	if ( ! preg_match( '/\.(jpe?g|png|gif|webp)$/i', $name ) ) {
		$info = wp_get_image_mime( $tmp );
		$ext  = array(
			'image/jpeg' => 'jpg',
			'image/png'  => 'png',
			'image/gif'  => 'gif',
			'image/webp' => 'webp',
		);
		if ( ! isset( $ext[ $info ] ) ) {
			wp_delete_file( $tmp );
			return 0;
		}
		$name = ( $name ? $name : 'wedding-news' ) . '.' . $ext[ $info ];
	}
	$id = media_handle_sideload(
		array(
			'name'     => $name,
			'tmp_name' => $tmp,
		),
		$post,
		$alt
	);
	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tmp );
		return 0;
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	update_post_meta( $id, '_wd_news_img', $url );
	return (int) $id;
}

/**
 * Bring in new full stories as drafts.
 *
 * @param int $max Most stories this run.
 * @return int Drafts made.
 */
function wd_news_import( $max = 6 ) {
	$feeds = wd_news_import_feeds();
	if ( ! $feeds || get_transient( 'wd_news_import_lock' ) ) {
		return 0;
	}
	set_transient( 'wd_news_import_lock', 1, 15 * MINUTE_IN_SECONDS );
	require_once ABSPATH . WPINC . '/feed.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$admins = get_users(
		array(
			'role'   => 'administrator',
			'number' => 1,
			'fields' => 'ID',
		)
	);
	$owner  = $admins ? (int) $admins[0] : 1;
	$cat    = wd_news_cat();
	$made   = 0;
	foreach ( array_slice( $feeds, 0, 10 ) as $url ) {
		if ( $made >= $max ) {
			break;
		}
		$feed = fetch_feed( $url );
		if ( is_wp_error( $feed ) ) {
			continue;
		}
		$source = trim( wp_strip_all_tags( html_entity_decode( (string) $feed->get_title(), ENT_QUOTES, 'UTF-8' ) ) );
		$source = $source ? $source : preg_replace( '/^www\./', '', (string) wp_parse_url( $url, PHP_URL_HOST ) );
		foreach ( $feed->get_items( 0, 5 ) as $it ) {
			if ( $made >= $max ) {
				break;
			}
			$link = esc_url_raw( (string) $it->get_permalink(), array( 'http', 'https' ) );
			if ( ! $link ) {
				continue;
			}
			$have = get_posts(
				array(
					'post_type'   => 'post',
					'post_status' => 'any',
					'meta_key'    => '_wd_news_src', // phpcs:ignore WordPress.DB.SlowDBQuery
					'meta_value'  => $link, // phpcs:ignore WordPress.DB.SlowDBQuery
					'fields'      => 'ids',
					'numberposts' => 1,
				)
			);
			if ( $have ) {
				continue;
			}
			$title = trim( wp_strip_all_tags( html_entity_decode( (string) $it->get_title(), ENT_QUOTES, 'UTF-8' ) ) );
			$html  = (string) $it->get_content();
			$html  = '' !== trim( wp_strip_all_tags( $html ) ) || false !== stripos( $html, '<img' ) ? $html : (string) $it->get_description();
			if ( '' === $title ) {
				continue;
			}
			// No scripts, styles or frames, with what is inside them.
			$html = (string) preg_replace( '#<(script|style|noscript|iframe|form)\b[^>]*>.*?</\1>#is', '', $html );
			// Lazy-loaded pictures keep the real address in data-src (or similar): use it as the picture.
			$html = (string) preg_replace_callback(
				'/<img\b[^>]*>/i',
				static function ( $m ) {
					if ( ! preg_match( '/\sdata-(?:lazy-src|src|original)=(["\'])([^"\']+)\1/i', $m[0], $d ) ) {
						return $m[0];
					}
					$t = (string) preg_replace( '/\s(?:src|data-(?:lazy-src|src|original)|srcset|data-srcset|sizes)=(["\']).*?\1/i', '', $m[0] );
					return (string) preg_replace( '/^<img\b/i', '<img src="' . esc_url( html_entity_decode( $d[2], ENT_QUOTES ) ) . '"', $t );
				},
				$html
			);
			$html = wp_kses_post( $html );
			$time = (int) $it->get_date( 'U' );
			$pid  = wp_insert_post(
				array(
					'post_type'     => 'post',
					'post_status'   => 'draft',
					'post_title'    => mb_substr( $title, 0, 200 ),
					'post_content'  => $html,
					'post_author'   => $owner,
					'post_category' => array( $cat ),
				)
			);
			if ( ! $pid || is_wp_error( $pid ) ) {
				continue;
			}
			update_post_meta( $pid, '_wd_news_src', $link );
			update_post_meta( $pid, '_wd_news_from', mb_substr( $source, 0, 80 ) );
			if ( $time ) {
				update_post_meta( $pid, '_wd_news_time', $time );
			}
			// Every picture in the story, plus the feed's own picture, copied into the media library.
			$pics = array();
			if ( preg_match_all( '/<img[^>]+src=(["\'])([^"\']+)\1/i', $html, $m ) ) {
				$pics = $m[2];
			}
			$lead = wd_news_image( $it, '' );
			if ( $lead ) {
				array_unshift( $pics, $lead );
			}
			$pics  = array_slice( array_values( array_unique( array_map( 'html_entity_decode', $pics ) ) ), 0, 20 );
			$first = 0;
			foreach ( $pics as $src ) {
				$aid = wd_news_sideload( $src, $pid, $title );
				if ( ! $aid ) {
					continue;
				}
				$first = $first ? $first : $aid;
				$new   = (string) wp_get_attachment_url( $aid );
				$html  = str_replace( array( $src, esc_url( $src ), esc_attr( $src ) ), $new, $html );
			}
			$html  = (string) preg_replace( '/\s(?:srcset|sizes)=(["\']).*?\1/i', '', $html );
			$html .= "\n\n" . '<p class="news-src">' . sprintf(
				/* translators: %s: source site, linked */
				esc_html__( 'Source: %s', 'wwmd' ),
				'<a href="' . esc_url( $link ) . '" rel="nofollow noopener" target="_blank">' . esc_html( $source ) . '</a>'
			) . '</p>';
			wp_update_post(
				array(
					'ID'           => $pid,
					'post_content' => $html,
				)
			);
			if ( $first ) {
				set_post_thumbnail( $pid, $first );
			}
			++$made;
		}
	}
	delete_transient( 'wd_news_import_lock' );
	update_option( 'wd_news_import_last', array( time(), $made ), false );
	return $made;
}
