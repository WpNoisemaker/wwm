<?php
/**
 * Front-end page editor (1.7.0). HQ admins switch on "Edit page" on any page of the site & change
 * words, buttons, links & pictures right where they are. Changes are kept as small "this → that"
 * swaps in one option (never in the theme files), so theme updates keep them.
 *
 * Swaps: text (a run of words between two tags), link (an <a> address) & img (a picture address).
 * Each swap belongs to one page address, or to every page ('*') when it sits in the header, footer,
 * menu or phone tab bar.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

/**
 * Who can edit pages from the front.
 *
 * @return bool
 */
function wd_fe_can() {
	return current_user_can( 'edit_theme_options' );
}

/**
 * All saved swaps.
 *
 * @return array
 */
function wd_fe_all() {
	$all = get_option( 'wd_fe_edits', array() );
	return is_array( $all ) ? array_values( $all ) : array();
}

/**
 * The address of the page being shown, without the site folder & query: "/", "/services/" …
 *
 * @param string $uri Request address (defaults to this request).
 * @return string
 */
function wd_fe_path( $uri = '' ) {
	$uri  = $uri ? $uri : ( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
	$base = untrailingslashit( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ) );
	if ( $base && 0 === strpos( $path, $base ) ) {
		$path = substr( $path, strlen( $base ) );
	}
	$path = '/' . trim( preg_replace( '#/page/\d+/?$#', '/', $path ), '/' );
	return '/' === $path ? '/' : $path . '/';
}

/**
 * A pattern that finds plain words in HTML, however WordPress wrote them (&amp;, &#8217; …).
 *
 * @param string $text Words as people see them.
 * @return string Regex fragment (no delimiters).
 */
function wd_fe_text_rx( $text ) {
	$alts  = array(
		'&'  => '(?:&amp;|&\#0?38;|&)',
		'’'  => '(?:’|&\#8217;|&rsquo;|\'|&\#0?39;)',
		'‘'  => '(?:‘|&\#8216;|&lsquo;|\'|&\#0?39;)',
		"'"  => '(?:\'|&\#0?39;|&\#8217;|’|&apos;)',
		'"'  => '(?:"|&quot;|&\#0?34;|“|”|&\#822[01];)',
		'“'  => '(?:“|&\#8220;|&ldquo;|"|&quot;)',
		'”'  => '(?:”|&\#8221;|&rdquo;|"|&quot;)',
		'…'  => '(?:…|&\#8230;|&hellip;|\.\.\.)',
		'–'  => '(?:–|&\#8211;|&ndash;)',
		'—'  => '(?:—|&\#8212;|&mdash;)',
		'<'  => '(?:&lt;|&\#0?60;)',
		'>'  => '(?:&gt;|&\#0?62;)',
		' ' => '(?:\x{00A0}|&nbsp;|&\#160;| )',
	);
	$out   = '';
	$chars = preg_split( '//u', $text, -1, PREG_SPLIT_NO_EMPTY );
	$space = false;
	foreach ( $chars as $c ) {
		if ( preg_match( '/\s/u', $c ) ) {
			if ( ! $space ) {
				$out  .= '\s+';
				$space = true;
			}
			continue;
		}
		$space = false;
		$out  .= $alts[ $c ] ?? preg_quote( $c, '~' );
	}
	return $out;
}

/**
 * Apply the swaps to a page's HTML.
 *
 * @param string $html Whole page.
 * @param array  $list Swaps for this page.
 * @return string
 */
function wd_fe_apply( $html, $list ) {
	$at = stripos( $html, '<body' );
	if ( false === $at || ! $list ) {
		return $html;
	}
	$head = substr( $html, 0, $at );
	$body = substr( $html, $at );
	foreach ( $list as $e ) {
		$n   = isset( $e['n'] ) && '*' !== $e['scope'] ? (int) $e['n'] : -1; // Which match on the page (0 = first); -1 = all.
		$hit = -1;
		if ( 'text' === $e['type'] ) {
			$rx   = '~(?<=>)(\s*)' . wd_fe_text_rx( $e['from'] ) . '(\s*)(?=<)~u';
			$to   = esc_html( $e['to'] );
			$body = (string) preg_replace_callback(
				$rx,
				static function ( $m ) use ( &$hit, $n, $to ) {
					++$hit;
					return ( -1 === $n || $hit === $n ) ? $m[1] . $to . $m[2] : $m[0];
				},
				$body
			);
		} elseif ( 'link' === $e['type'] || 'img' === $e['type'] ) {
			$tag  = 'link' === $e['type'] ? 'a' : 'img';
			$attr = 'link' === $e['type'] ? 'href' : 'src';
			$body = (string) preg_replace_callback(
				'~<' . $tag . '\b[^>]*>~i',
				static function ( $m ) use ( &$hit, $n, $e, $attr ) {
					if ( ! preg_match( '~\s' . $attr . '\s*=\s*(["\'])(.*?)\1~is', $m[0], $a ) || html_entity_decode( $a[2], ENT_QUOTES ) !== $e['from'] ) {
						return $m[0];
					}
					++$hit;
					if ( -1 !== $n && $hit !== $n ) {
						return $m[0];
					}
					$t = str_replace( $a[0], ' ' . $attr . '="' . esc_url( $e['to'] ) . '"', $m[0] );
					if ( 'src' === $attr ) {
						// The new picture wins over the old sizes list.
						$t = (string) preg_replace( '~\s(?:srcset|sizes)\s*=\s*(["\']).*?\1~is', '', $t );
					}
					return $t;
				},
				$body
			);
			// Links can differ for signed-in people (e.g. "Become a seller"), so when no link has the
			// saved address, find it by its words instead.
			if ( -1 === $hit && 'link' === $e['type'] && ! empty( $e['text'] ) ) {
				$want = $e['text'];
				$n    = '*' === $e['scope'] ? -1 : (int) ( $e['tn'] ?? 0 );
				$body = (string) preg_replace_callback(
					'~(<a\b[^>]*>)(.*?)</a>~is',
					static function ( $m ) use ( &$hit, $n, $e, $want ) {
						$words = trim( preg_replace( '/\s+/u', ' ', html_entity_decode( wp_strip_all_tags( $m[2] ), ENT_QUOTES ) ) );
						if ( $words !== $want ) {
							return $m[0];
						}
						++$hit;
						if ( -1 !== $n && $hit !== $n ) {
							return $m[0];
						}
						$open = preg_match( '~\shref\s*=\s*(["\'])(.*?)\1~is', $m[1] ) ? (string) preg_replace( '~\shref\s*=\s*(["\'])(.*?)\1~is', ' href="' . esc_url( $e['to'] ) . '"', $m[1], 1 ) : $m[1];
						return $open . $m[2] . '</a>';
					},
					$body
				);
			}
		}
	}
	return $head . $body;
}

/* Rewrite the page on the way out, only when it has swaps. */
add_action(
	'template_redirect',
	static function () {
		if ( is_admin() || wp_doing_ajax() || is_feed() || is_embed() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}
		$path = wd_fe_path();
		$list = array_values(
			array_filter(
				wd_fe_all(),
				static function ( $e ) use ( $path ) {
					return '*' === $e['scope'] || $path === $e['scope'];
				}
			)
		);
		if ( ! $list ) {
			return;
		}
		// Site-wide swaps first, so a page can still change a word the header also uses.
		// Links & pictures first (they look for the theme's own words), then words; site-wide before page.
		usort(
			$list,
			static function ( $a, $b ) {
				$ra = ( 'text' === $a['type'] ? 2 : 0 ) + ( '*' === $a['scope'] ? 0 : 1 );
				$rb = ( 'text' === $b['type'] ? 2 : 0 ) + ( '*' === $b['scope'] ? 0 : 1 );
				return $ra <=> $rb;
			}
		);
		ob_start(
			static function ( $html ) use ( $list ) {
				return wd_fe_apply( $html, $list );
			}
		);
	},
	99
);

/* The editor itself: only for HQ admins. */
add_action(
	'wp_enqueue_scripts',
	static function () {
		// Public pages only: dashboards (HQ, account, sign-in) have their own forms.
		if ( ! wd_fe_can() || is_admin() || is_page_template( array( 'page-templates/hq.php', 'page-templates/account.php', 'page-templates/sign-in.php' ) ) ) {
			return;
		}
		wp_enqueue_media();
		$u = get_template_directory_uri() . '/assets/';
		wp_enqueue_style( 'wd-fe', $u . 'css/fe.css', array(), WD_VER );
		wp_enqueue_script( 'wd-fe', $u . 'js/fe.js', array( 'wd-main' ), WD_VER, true );
		wp_localize_script(
			'wd-fe',
			'WDFE',
			array(
				'path' => wd_fe_path(),
				'n'    => count(
					array_filter(
						wd_fe_all(),
						static function ( $e ) {
							return wd_fe_path() === $e['scope'];
						}
					)
				),
				'i18n' => array(
					'edit'    => __( 'Edit page', 'wwmd' ),
					'done'    => __( 'Done', 'wwmd' ),
					'save'    => __( 'Save changes', 'wwmd' ),
					'cancel'  => __( 'Cancel', 'wwmd' ),
					'saving'  => __( 'Saving…', 'wwmd' ),
					'saved'   => __( 'Saved. The page is updated.', 'wwmd' ),
					'tip'     => __( 'Click any words to change them. Click a button or link to change where it goes. Click a picture to replace it.', 'wwmd' ),
					'link'    => __( 'Link address', 'wwmd' ),
					'apply'   => __( 'Use this link', 'wwmd' ),
					'image'   => __( 'Change picture', 'wwmd' ),
					'pick'    => __( 'Pick a picture', 'wwmd' ),
					'imgurl'  => __( 'Or paste a picture address', 'wwmd' ),
					'reset'   => __( 'Undo all edits on this page', 'wwmd' ),
					'resetQ'  => __( 'Sure? Click again to undo all edits on this page', 'wwmd' ),
					'nothing' => __( 'Nothing changed yet.', 'wwmd' ),
					'leave'   => __( 'You have changes that are not saved.', 'wwmd' ),
					'all'     => __( 'Shows on every page', 'wwmd' ),
				),
			)
		);
	},
	30
);

add_action(
	'rest_api_init',
	static function () {
		register_rest_route(
			'wd/v1',
			'/fe/save',
			array(
				'methods'             => 'POST',
				'permission_callback' => 'wd_fe_can',
				'callback'            => 'wd_fe_rest_save',
			)
		);
		register_rest_route(
			'wd/v1',
			'/fe/reset',
			array(
				'methods'             => 'POST',
				'permission_callback' => 'wd_fe_can',
				'callback'            => static function ( WP_REST_Request $r ) {
					$path = wd_fe_path( (string) $r->get_param( 'path' ) );
					$all  = array_values(
						array_filter(
							wd_fe_all(),
							static function ( $e ) use ( $path ) {
								return $e['scope'] !== $path;
							}
						)
					);
					update_option( 'wd_fe_edits', $all, false );
					return array( 'ok' => true );
				},
			)
		);
	}
);

/**
 * Save a batch of swaps from the editor.
 *
 * @param WP_REST_Request $r Request: path, edits [{type, from, to, n, all}].
 * @return array|WP_Error
 */
function wd_fe_rest_save( WP_REST_Request $r ) {
	$path  = wd_fe_path( (string) $r->get_param( 'path' ) );
	$edits = (array) $r->get_param( 'edits' );
	$all   = wd_fe_all();
	$done  = 0;
	foreach ( array_slice( $edits, 0, 200 ) as $x ) {
		$type  = sanitize_key( $x['type'] ?? '' );
		$scope = ! empty( $x['all'] ) ? '*' : $path;
		$n     = max( 0, (int) ( $x['n'] ?? 0 ) );
		if ( 'text' === $type ) {
			$from = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( (string) ( $x['from'] ?? '' ) ) ) );
			$to   = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( (string) ( $x['to'] ?? '' ) ) ) );
		} elseif ( 'link' === $type || 'img' === $type ) {
			$from  = trim( (string) ( $x['from'] ?? '' ) );
			$to    = esc_url_raw( trim( (string) ( $x['to'] ?? '' ) ), 'img' === $type ? array( 'http', 'https' ) : null );
			$words = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( (string) ( $x['text'] ?? '' ) ) ) );
			foreach ( $all as $e ) {
				if ( 'text' === $e['type'] && $e['to'] === $words && ( '*' === $e['scope'] || $e['scope'] === $scope ) ) {
					$words = $e['from'];
				}
			}
		} else {
			continue;
		}
		if ( '' === $from || mb_strlen( $from ) > 2000 || mb_strlen( $to ) > 2000 || ( '' === $to && 'text' !== $type ) ) {
			continue;
		}
		// Changing words again: keep the theme's own words as "from", so the swap still finds them.
		$found = false;
		foreach ( $all as $i => $e ) {
			if ( $e['type'] === $type && $e['scope'] === $scope && $e['to'] === $from && ( '*' === $scope || (int) ( $e['n'] ?? 0 ) === $n ) ) {
				$found = true;
				if ( $e['from'] === $to ) {
					unset( $all[ $i ] ); // Back to how it was.
				} else {
					$all[ $i ]['to'] = $to;
				}
				break;
			}
		}
		if ( ! $found && $from !== $to ) {
			$all[] = array(
				'scope' => $scope,
				'type'  => $type,
				'from'  => $from,
				'to'    => $to,
				'n'     => '*' === $scope ? 0 : $n,
				'text'  => 'link' === $type ? $words : '',
				'tn'    => 'link' === $type ? max( 0, (int) ( $x['tn'] ?? 0 ) ) : 0,
				'when'  => time(),
			);
		}
		++$done;
	}
	update_option( 'wd_fe_edits', array_values( $all ), false );
	return array(
		'ok'    => true,
		'saved' => $done,
	);
}
