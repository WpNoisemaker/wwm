<?php
/**
 * Anonymous community (1.5.1): signed-in people post wedding questions, stories & confessions, shown only
 * under a made-up name like "Golden Gele". Replies, likes & reports; HQ moderates. Only HQ can see who
 * wrote what. The same person keeps the same name inside one thread, & gets a different one in the next.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

/**
 * Topics.
 *
 * @return array key => [label, icon]
 */
function wd_talk_topics() {
	return array(
		'love'    => array( __( 'Love & marriage', 'wwmd' ), 'heart' ),
		'family'  => array( __( 'Family & in-laws', 'wwmd' ), 'users' ),
		'money'   => array( __( 'Money & pressure', 'wwmd' ), 'cash' ),
		'faith'   => array( __( 'Faith & culture', 'wwmd' ), 'church' ),
		'mind'    => array( __( 'Mind & body', 'wwmd' ), 'spark' ),
		'leaving' => array( __( 'Leaving & starting over', 'wwmd' ), 'plane' ),
		'work'    => array( __( 'Work & ambition', 'wwmd' ), 'briefcase' ),
		'wedding' => array( __( 'The wedding itself', 'wwmd' ), 'rings' ),
		'confess' => array( __( 'Confessions', 'wwmd' ), 'lock' ),
	);
}

/**
 * Old topic keys, so posts written before this version keep a home instead of falling off the feed.
 *
 * @return array old key => new key
 */
function wd_talk_topic_map() {
	return array(
		'planning' => 'wedding',
		'guests'   => 'wedding',
		'sellers'  => 'money',
	);
}

/**
 * A post's topic, with the old keys quietly translated.
 *
 * @param int $pid Post.
 * @return string
 */
function wd_talk_topic_of( $pid ) {
	$k   = (string) get_post_meta( $pid, '_wd_topic', true );
	$map = wd_talk_topic_map();
	return $map[ $k ] ?? $k;
}

add_action(
	'init',
	static function () {
		register_post_type(
			'wd_talk',
			array(
				'labels'              => array(
					'name'          => __( 'Community posts', 'wwmd' ),
					'singular_name' => __( 'Community post', 'wwmd' ),
				),
				'public'              => true,
				'exclude_from_search' => true,
				'show_in_nav_menus'   => false,
				'show_in_rest'        => false,
				'has_archive'         => 'community',
				'rewrite'             => array(
					'slug'       => 'community',
					'with_front' => false,
					'feeds'      => false,
				),
				'supports'            => array( 'title', 'editor', 'author', 'comments' ),
				'menu_icon'           => 'dashicons-format-chat',
			)
		);
	}
);

/* ---------------------------------------------------------------------------------------------
 * Keep posters anonymous: no feeds, no author details in embeds or the users sitemap, no reply
 * emails to the poster (WordPress puts the replier's IP address in them), no replies over the API.
 * ------------------------------------------------------------------------------------------ */

add_action(
	'template_redirect',
	static function () {
		if ( is_feed() && ( is_post_type_archive( 'wd_talk' ) || is_singular( 'wd_talk' ) ) ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			nocache_headers();
		}
	},
	1
);
add_filter(
	'oembed_response_data',
	static function ( $data, $post ) {
		if ( 'wd_talk' === get_post_type( $post ) ) {
			unset( $data['author_name'], $data['author_url'] );
		}
		return $data;
	},
	10,
	2
);
add_filter(
	'wp_sitemaps_users_query_args',
	static function ( $args ) {
		$types                       = array_diff( get_post_types( array( 'public' => true ) ), array( 'wd_talk', 'attachment' ) );
		$args['has_published_posts'] = array_values( $types );
		return $args;
	}
);
add_filter(
	'notify_post_author',
	static function ( $maybe, $comment_id ) {
		$c = get_comment( $comment_id );
		return ( $c && 'wd_talk' === get_post_type( (int) $c->comment_post_ID ) ) ? false : $maybe;
	},
	10,
	2
);
add_filter(
	'rest_comment_query',
	static function ( $args ) {
		if ( ! current_user_can( 'moderate_comments' ) ) {
			$args['type__not_in'] = array_merge( (array) ( $args['type__not_in'] ?? array() ), array( 'wd_reply' ) );
		}
		return $args;
	}
);
add_filter(
	'comments_open',
	static function ( $open, $pid ) {
		// Replies only go through our own form.
		return 'wd_talk' === get_post_type( $pid ) ? false : $open;
	},
	10,
	2
);

/**
 * Posts written by the site team (conversation starters) show the team's name instead of a made-up one.
 *
 * @param int $pid Post.
 * @return bool
 */
function wd_talk_is_team( $pid ) {
	return (bool) get_post_meta( (int) $pid, '_wd_team', true );
}

/**
 * The five backgrounds a post can sit on, like a WhatsApp status: our brand colours.
 *
 * @return array key => [label, CSS background, text colour]
 */
function wd_talk_bgs() {
	return array(
		'paper'  => array( __( 'White', 'wwmd' ), '#FFFFFF', '#160E22' ),
		'ink'    => array( __( 'Black', 'wwmd' ), '#160E22', '#F6F3FA' ),
		'sun'    => array( __( 'Brand yellow', 'wwmd' ), 'var(--mesh-sun)', '#160E22' ),
		'citrus' => array( __( 'Citrus', 'wwmd' ), 'var(--mesh-citrus)', '#160E22' ),
		'blush'  => array( __( 'Blush', 'wwmd' ), 'var(--mesh-blush)', '#3A0F22' ),
		'dusk'   => array( __( 'Dusk', 'wwmd' ), 'var(--mesh-dusk)', '#FFFFFF' ),
		'noir'   => array( __( 'Midnight', 'wwmd' ), 'var(--mesh-noir)', '#F6F3FA' ),
	);
}

/**
 * Backgrounds that older posts were written on, mapped onto the new set so nothing loses its look.
 *
 * @return array
 */
function wd_talk_bg_map() {
	return array(
		'grad'      => 'sun',
		'aubergine' => 'noir',
		'purple'    => 'dusk',
		'lilac'     => 'dusk',
		'marigold'  => 'sun',
	);
}

/**
 * The watermark on every post card & shared picture.
 *
 * @return string
 */
function wd_talk_mark() {
	$m = trim( (string) wd_opt( 'talk_mark' ) );
	return $m ? $m : 'weddingwebsites.io';
}

/**
 * Who is posting: a signed-in person, or a visitor (kept apart by a private key, never shown).
 *
 * @return array [user ID, visitor key]
 */
function wd_talk_me() {
	$uid = get_current_user_id();
	return array( $uid, $uid ? '' : wd_voter() );
}

/**
 * A visitor who posts gets their own private key in a cookie (if the page script has not set one yet),
 * so two people on the same network are never mixed up & only the writer can delete their words.
 */
function wd_talk_guest_cookie() {
	if ( is_user_logged_in() || headers_sent() ) {
		return;
	}
	$c = isset( $_COOKIE['wd_vid'] ) ? preg_replace( '/[^a-z0-9]/i', '', sanitize_text_field( wp_unslash( $_COOKIE['wd_vid'] ) ) ) : '';
	if ( strlen( $c ) >= 12 ) {
		return;
	}
	$id = strtolower( wp_generate_password( 24, false, false ) );
	setcookie(
		'wd_vid',
		$id,
		array(
			'expires'  => time() + YEAR_IN_SECONDS,
			'path'     => '/',
			'domain'   => COOKIE_DOMAIN,
			'secure'   => is_ssl(),
			'httponly' => false,
			'samesite' => 'Lax',
		)
	);
	$_COOKIE['wd_vid'] = $id;
}

/**
 * Whether the current visitor wrote this post or reply.
 *
 * @param int    $uid   Author user ID.
 * @param string $guest Author visitor key.
 * @return bool
 */
function wd_talk_mine( $uid, $guest ) {
	$me = wd_talk_me();
	return $me[0] ? (int) $uid === $me[0] : ( '' !== (string) $guest && hash_equals( (string) $guest, $me[1] ) );
}

/**
 * The made-up name a person uses inside one thread.
 *
 * @param int|string $who User ID, or a visitor key.
 * @param int        $pid Thread.
 * @return string
 */
function wd_talk_alias( $who, $pid ) {
	return __( 'Someone', 'wwmd' );
}

/**
 * Clean a name a person typed. Anything goes — a first name, a nickname, two words — as long as it
 * is short, has no links and no @handle, so nobody can sign a post as a brand or an account.
 *
 * @param string $raw What they typed.
 * @return string Clean name, or '' when nothing usable is left.
 */
function wd_talk_clean_name( $raw ) {
	$n = sanitize_text_field( (string) $raw );
	$n = preg_replace( '~https?://\\S+|www\\.\\S+|[<>@]~iu', '', $n );
	$n = trim( preg_replace( '~\\s+~u', ' ', (string) $n ) );
	if ( mb_strlen( $n ) < 2 ) {
		return '';
	}
	return mb_substr( $n, 0, 28 );
}

/**
 * The name shown against a post or a comment: whatever the writer typed, and nothing else.
 *
 * Nobody's account name, email or generated alias is ever shown, and no post is ever signed with
 * our own name, so a reader cannot tell a couple, a guest or our team apart from each other.
 *
 * @param int    $uid   User (0 for a visitor).
 * @param int    $pid   Thread.
 * @param string $guest Visitor key, for visitors.
 * @param string $typed The name saved with this post or comment.
 * @return array [name, avatar HTML, is team]
 */
function wd_talk_who( $uid, $pid, $guest = '', $typed = '' ) {
	$name = wd_talk_clean_name( $typed );
	if ( '' === $name ) {
		$name = __( 'Someone', 'wwmd' );
	}
	return array( $name, '<span class="talk-av talk-av--anon" aria-hidden="true">' . esc_html( mb_strtoupper( mb_substr( $name, 0, 1 ) ) ) . '</span>', false );
}

/**
 * The name saved against a post.
 *
 * @param int $pid Post.
 * @return string
 */
function wd_talk_name( $pid ) {
	return (string) get_post_meta( $pid, '_wd_name', true );
}

/**
 * Posts for the feed.
 *
 * @param array $a topic, sort (new|top), paged, per.
 * @return WP_Query
 */
function wd_talk_query( $a = array() ) {
	$a    = wp_parse_args(
		$a,
		array(
			'topic' => '',
			'sort'  => 'new',
			'paged' => 1,
			'per'   => 10,
		)
	);
	$args = array(
		'post_type'           => 'wd_talk',
		'post_status'         => 'publish',
		'posts_per_page'      => (int) $a['per'],
		'paged'               => max( 1, (int) $a['paged'] ),
		'ignore_sticky_posts' => true,
	);
	if ( $a['topic'] && isset( wd_talk_topics()[ $a['topic'] ] ) ) {
		$args['meta_query'] = array( array( 'key' => '_wd_topic', 'value' => $a['topic'] ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	}
	if ( 'top' === $a['sort'] ) {
		$args['meta_key'] = '_wd_likes'; // phpcs:ignore WordPress.DB.SlowDBQuery
		$args['orderby']  = array(
			'meta_value_num' => 'DESC',
			'comment_count'  => 'DESC',
			'date'           => 'DESC',
		);
	}
	return new WP_Query( $args );
}

/**
 * Whether this visitor liked a post.
 *
 * @param int $pid Post.
 * @return bool
 */
function wd_talk_liked( $pid ) {
	global $wpdb;
	return (bool) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . wd_t( 'likes' ) . " WHERE obj_type = 'talk' AND obj_id = %d AND voter = %s", $pid, wd_voter() ) ); // phpcs:ignore WordPress.DB
}

/**
 * Text as paragraphs, safely.
 *
 * @param string $t Plain text.
 * @return string HTML.
 */
function wd_talk_text( $t ) {
	return wpautop( esc_html( $t ) );
}

/**
 * Sign-in link (kept for older links; posting & replying no longer need an account).
 *
 * @param string $back Return to.
 * @return string
 */
function wd_talk_signin( $back ) {
	return $back;
}

/**
 * What a post says on its coloured card, & the caption under it. Posts from before 1.7.0 had a
 * title: the title goes on the card & the rest becomes the caption.
 *
 * @param WP_Post $p Post.
 * @return array [card text, caption]
 */
function wd_talk_words( $p ) {
	if ( get_post_meta( $p->ID, '_wd_bg', true ) || get_post_meta( $p->ID, '_wd_notitle', true ) ) {
		return array( (string) $p->post_content, '' );
	}
	return array( get_the_title( $p ), (string) $p->post_content );
}

/**
 * A post's background key.
 *
 * @param WP_Post $p Post.
 * @return string
 */
function wd_talk_bg( $p ) {
	$bg  = (string) get_post_meta( $p->ID, '_wd_bg', true );
	$map = wd_talk_bg_map();
	$bg  = $map[ $bg ] ?? $bg;
	$all = wd_talk_bgs();
	if ( isset( $all[ $bg ] ) ) {
		return $bg;
	}
	$keys = array_keys( $all );
	return $keys[ $p->ID % count( $keys ) ];
}

/**
 * Font size step for the card, so short posts are big & long ones still fit, like a status.
 *
 * @param string $t Text.
 * @return string s|m|l
 */
function wd_talk_size( $t ) {
	$n = mb_strlen( trim( $t ) );
	return $n <= 90 ? 'l' : ( $n <= 220 ? 'm' : 's' );
}

/**
 * The coloured card itself (also used for the composer preview).
 *
 * @param string $text Words.
 * @param string $bg   Background key.
 * @param bool   $full Whole text (else cut at about 320 characters).
 * @param string $link Read-more link when cut.
 * @return string HTML.
 */
function wd_talk_status( $text, $bg, $full = true, $link = '' ) {
	$all  = wd_talk_bgs();
	$key  = isset( $all[ $bg ] ) ? $bg : 'paper';
	$b    = $all[ $key ];
	$cut  = ! $full && mb_strlen( $text ) > 320;
	$show = $cut ? rtrim( mb_substr( $text, 0, 300 ) ) . '…' : $text;
	$mark = wd_talk_mark();
	$o    = '<div class="talk-status talk-status--bg-' . esc_attr( $key ) . ' talk-status--' . esc_attr( wd_talk_size( $show ) ) . '" style="--st-bg:' . esc_attr( $b[1] ) . ';--st-on:' . esc_attr( $b[2] ) . '" data-bg="' . esc_attr( $key ) . '">';
	// The diagonal mark: it runs corner to corner under the words, light enough to read straight through.
	$o   .= '<span class="talk-status__wash" aria-hidden="true"><i>' . esc_html( $mark ) . '</i><i>' . esc_html( $mark ) . '</i><i>' . esc_html( $mark ) . '</i></span>';
	$o   .= '<div class="talk-status__text">' . wd_talk_text( $show ) . '</div>';
	if ( $cut && $link ) {
		$o .= '<a class="talk-status__more" href="' . esc_url( $link ) . '">' . esc_html__( 'Read all', 'wwmd' ) . '</a>';
	}
	$o .= '<span class="talk-status__mark" aria-hidden="true">' . esc_html( $mark ) . '</span></div>';
	return $o;
}

/**
 * One post, Instagram style: who posted, the coloured card, likes, comments & share.
 *
 * @param WP_Post $p    Post.
 * @param bool    $full Whole text & no "View comments".
 * @return string HTML.
 */
function wd_talk_card( $p, $full = false ) {
	$guest = (string) get_post_meta( $p->ID, '_wd_guest', true );
	$who   = wd_talk_who( (int) $p->post_author, $p->ID, $guest, wd_talk_name( $p->ID ) );
	$tkey  = wd_talk_topic_of( $p->ID );
	$topic = wd_talk_topics()[ $tkey ] ?? null;
	$likes = max( 0, (int) get_post_meta( $p->ID, '_wd_likes', true ) );
	$n     = (int) $p->comment_count;
	$link  = get_permalink( $p );
	$words = wd_talk_words( $p );
	$bg    = wd_talk_bg( $p );
	$all   = wd_talk_bgs();
	$b     = $all[ $bg ] ?? reset( $all );
	$liked = wd_talk_liked( $p->ID );
	$mine  = wd_talk_mine( (int) $p->post_author, $guest );
	$mark  = wd_talk_mark();
	$text  = $words[0];
	$cut   = ! $full && mb_strlen( $text ) > 380;
	$show  = $cut ? rtrim( mb_substr( $text, 0, 360 ) ) . "…" : $text;

	$o  = '<article class="talk talk--bg-' . esc_attr( $bg ) . ( $full ? ' talk--full' : '' ) . '"';
	$o .= ' style="--st-bg:' . esc_attr( $b[1] ) . ';--st-on:' . esc_attr( $b[2] ) . '"';
	$o .= ' data-talk="' . (int) $p->ID . '" data-size="' . esc_attr( wd_talk_size( $show ) ) . '"';
	$o .= ' data-like-word="' . esc_attr__( 'Liked', 'wwmd' ) . '" data-save-word="' . esc_attr__( 'Saved', 'wwmd' ) . '">';

	// The mark runs corner to corner behind everything, light enough to read straight through.
	$o .= '<span class="talk__wash" aria-hidden="true"><i>' . esc_html( $mark ) . '</i><i>' . esc_html( $mark ) . '</i><i>' . esc_html( $mark ) . '</i><i>' . esc_html( $mark ) . '</i></span>';

	// Top row: what it is about, and when.
	$o .= '<header class="talk__top">';
	if ( $topic ) {
		$o .= '<a class="talk__topic" href="' . esc_url( add_query_arg( 'topic', $tkey, get_post_type_archive_link( 'wd_talk' ) ) ) . '">' . wd_icon( $topic[1], 14 ) . '<span>' . esc_html( $topic[0] ) . '</span></a>';
	}
	$o .= '<span class="talk__topright">';
	$o .= '<time class="talk__when" datetime="' . esc_attr( get_the_date( 'c', $p ) ) . '">' . esc_html( wd_ago( $p->post_date_gmt ) ) . '</time>';
	$o .= '<button class="talk__share" type="button" data-talk-share="' . esc_attr(
		wp_json_encode(
			array(
				'url'   => $link,
				'text'  => mb_substr( $words[0], 0, 600 ),
				'bg'    => $bg,
				'mark'  => $mark,
				'name'  => $who[0],
				'topic' => $topic ? $topic[0] : '',
			)
		)
	) . '">' . wd_icon( 'send', 17 ) . '<span class="screen-reader-text">' . esc_html__( 'Share this post', 'wwmd' ) . '</span></button>';
	$o .= '</span></header>';

	// The words.
	$o .= $full ? '<h1 class="screen-reader-text">' . esc_html( get_the_title( $p ) ) . '</h1>' : '';
	$o .= '<div class="talk__body">' . wd_talk_text( $show ) . '</div>';

	// Who wrote it, how many answered, and how many felt it.
	$o .= '<footer class="talk__foot">';
	$o .= '<div class="talk__by"><span class="talk-av">' . esc_html( mb_strtoupper( mb_substr( $who[0], 0, 1 ) ) ) . '</span>';
	$o .= '<span class="talk__byname"><b>' . esc_html( $who[0] ) . '</b><small>' . esc_html( sprintf( /* translators: %s replies */ _n( '%s reply', '%s replies', $n, 'wwmd' ), number_format_i18n( $n ) ) ) . '</small></span></div>';
	$o .= '<button class="talk__likes' . ( $liked ? ' is-on' : '' ) . '" type="button" data-talk-like="' . (int) $p->ID . '" aria-pressed="' . ( $liked ? 'true' : 'false' ) . '">';
	$o .= wd_icon( 'heart', 17 ) . '<b data-talk-likes>' . esc_html( number_format_i18n( $likes ) ) . '</b>';
	$o .= '<span class="screen-reader-text">' . esc_html__( 'Like this', 'wwmd' ) . '</span></button>';
	$o .= '</footer>';

	// The way in, and the mark.
	$o .= '<div class="talk__end">';
	$o .= '<a class="talk__read" href="' . esc_url( $link ) . '#replies">' . esc_html( $full ? __( 'Reply to this', 'wwmd' ) : __( 'Read it all and reply', 'wwmd' ) ) . wd_icon( 'arrow', 16 ) . '</a>';
	$o .= '<span class="talk__mark" aria-hidden="true">' . esc_html( $mark ) . ' · #' . (int) $p->ID . '</span>';
	$o .= '</div>';

	if ( $full ) {
		$o .= '<div class="talk__more">';
		if ( $mine ) {
			$o .= wd_talk_form( 'delete', array( 'pid' => $p->ID ), '<button class="linkish linkish--danger" type="submit" data-confirm="' . esc_attr__( 'Delete your post for good?', 'wwmd' ) . '">' . esc_html__( 'Delete', 'wwmd' ) . '</button>' );
		} else {
			$o .= wd_talk_form( 'report', array( 'pid' => $p->ID ), '<button class="linkish" type="submit">' . wd_icon( 'shield', 15 ) . esc_html__( 'Report', 'wwmd' ) . '</button>' );
		}
		$o .= '</div>';
	}
	$o .= '</article>';
	return $o;
}

/**
 * The row of round buttons under the deck: back, like, save & next.
 *
 * @return string HTML.
 */
function wd_talk_deck_acts() {
	$o  = '<div class="talk-acts" data-talk-acts hidden>';
	$o .= '<button class="talk-act talk-act--back" type="button" data-deck="back"><span class="screen-reader-text">' . esc_html__( 'Previous post', 'wwmd' ) . '</span>' . wd_icon( 'arrow', 22 ) . '</button>';
	$o .= '<button class="talk-act talk-act--like" type="button" data-deck="like"><span class="screen-reader-text">' . esc_html__( 'Like', 'wwmd' ) . '</span>' . wd_icon( 'heart', 24 ) . '</button>';
	$o .= '<button class="talk-act talk-act--save" type="button" data-deck="save"><span class="screen-reader-text">' . esc_html__( 'Save for later', 'wwmd' ) . '</span>' . wd_icon( 'bookmark', 22 ) . '</button>';
	$o .= '<button class="talk-act talk-act--next" type="button" data-deck="next"><span class="screen-reader-text">' . esc_html__( 'Next post', 'wwmd' ) . '</span>' . wd_icon( 'arrow', 22 ) . '</button>';
	$o .= '</div>';
	return $o;
}

/**
 * A guest photo or love note from a public wedding page, shown in the community feed like a post.
 *
 * @param object $r    Row: kind (note|photo), wedding_id, name, message/caption, attachment_id.
 * @return string HTML.
 */
function wd_talk_wedding_card( $r ) {
	$wid  = (int) $r->wedding_id;
	$d    = wd_get( $wid );
	$url  = get_permalink( $wid );
	$name = wd_names( $d );
	$o    = '<article class="talk talk--wedding">';
	$o   .= '<header class="talk__head"><a class="talk__ring" href="' . esc_url( $url ) . '"><span class="talk-av">' . wd_cover( $wid, $d, 'thumbnail' ) . '</span></a><span class="talk__who"><b><a href="' . esc_url( $url ) . '">' . esc_html( $name ) . '</a></b><small>' . esc_html( 'photo' === $r->kind ? __( 'A guest’s photo from their wedding page', 'wwmd' ) : __( 'A love note on their wedding page', 'wwmd' ) ) . '</small></span></header>';
	if ( 'photo' === $r->kind ) {
		$o .= '<a class="talk__card talk__card--photo" href="' . esc_url( $url . '#gallery' ) . '"><img src="' . esc_url( (string) wp_get_attachment_image_url( (int) $r->attachment_id, 'wd-square' ) ) . '" alt="' . esc_attr( $r->caption ? $r->caption : sprintf( /* translators: %s couple */ __( 'A guest photo from %s’s wedding', 'wwmd' ), $name ) ) . '" loading="lazy"><span class="talk-status__mark" aria-hidden="true">' . esc_html( wd_talk_mark() ) . '</span></a>';
		$cap = (string) $r->caption;
	} else {
		$keys = array_keys( wd_talk_bgs() );
		$o   .= '<a class="talk__card" href="' . esc_url( $url . '#wall' ) . '">' . wd_talk_status( (string) $r->message, $keys[ (int) $r->id % count( $keys ) ], false ) . '</a>';
		$cap  = '';
	}
	$o .= '<p class="talk__by">' . ( $r->name ? '<b>' . esc_html( wp_trim_words( (string) $r->name, 3, '' ) ) . '</b> ' : '' ) . esc_html( $cap ) . '</p>';
	$o .= '<a class="talk__read" href="' . esc_url( $url ) . '">' . esc_html( sprintf( /* translators: %s couple */ __( 'Open %s’s wedding page', 'wwmd' ), $name ) ) . '</a>';
	return $o . '</article>';
}

/**
 * Guest photos & love notes from public wedding pages, newest first, for the community feed.
 *
 * @param int $n    How many.
 * @param int $skip How many to skip (for later pages).
 * @return object[]
 */
function wd_talk_wedding_items( $n, $skip = 0 ) {
	global $wpdb;
	$ok   = static function ( $wid, $sec ) {
		static $seen = array();
		$k = $wid . $sec;
		if ( ! isset( $seen[ $k ] ) ) {
			$seen[ $k ] = 'publish' === get_post_status( $wid ) && 'wd_wedding' === get_post_type( $wid ) && wd_show( $wid, $sec );
		}
		return $seen[ $k ];
	};
	$out   = array();
	$take  = ( $n + $skip ) * 3 + 20;
	$notes = (array) $wpdb->get_results( $wpdb->prepare( "SELECT id, wedding_id, name, message, '' AS caption, 0 AS attachment_id, 'note' AS kind FROM " . wd_t( 'wall' ) . " WHERE status = 'approved' AND message <> '' ORDER BY id DESC LIMIT %d", $take ) ); // phpcs:ignore WordPress.DB
	$pics  = (array) $wpdb->get_results( $wpdb->prepare( "SELECT id, wedding_id, name, '' AS message, caption, attachment_id, 'photo' AS kind FROM " . wd_t( 'photos' ) . " WHERE status = 'approved' AND attachment_id > 0 ORDER BY id DESC LIMIT %d", $take ) ); // phpcs:ignore WordPress.DB
	// The same words or the same picture only once.
	$seen  = array();
	$notes = array_values(
		array_filter(
			$notes,
			static function ( $r ) use ( $ok, &$seen ) {
				$k = 'n' . md5( strtolower( trim( $r->message ) ) );
				if ( isset( $seen[ $k ] ) || mb_strlen( $r->message ) > 400 || ! $ok( (int) $r->wedding_id, 'wall' ) ) {
					return false;
				}
				$seen[ $k ] = 1;
				return true;
			}
		)
	);
	$pics  = array_values(
		array_filter(
			$pics,
			static function ( $r ) use ( $ok, &$seen ) {
				$f = (string) get_attached_file( (int) $r->attachment_id );
				$k = 'f' . ( is_file( $f ) ? filesize( $f ) : md5( basename( $f ) ) );
				if ( isset( $seen[ $k ] ) || ! $ok( (int) $r->wedding_id, 'gallery' ) || ! wp_get_attachment_image_url( (int) $r->attachment_id, 'wd-square' ) ) {
					return false;
				}
				$seen[ $k ] = 1;
				return true;
			}
		)
	);
	// Photo, note, photo, note…
	for ( $i = 0; count( $out ) < $n + $skip && ( isset( $pics[ $i ] ) || isset( $notes[ $i ] ) ); $i++ ) {
		if ( isset( $pics[ $i ] ) ) {
			$out[] = $pics[ $i ];
		}
		if ( isset( $notes[ $i ] ) ) {
			$out[] = $notes[ $i ];
		}
	}
	return array_slice( $out, $skip, $n );
}

/**
 * A small form posting to the community handler.
 *
 * @param string $op     Operation.
 * @param array  $hidden Hidden fields.
 * @param string $inner  Inner HTML.
 * @param string $class  Form class.
 * @return string HTML.
 */
function wd_talk_form( $op, $hidden, $inner, $class = 'talk__form' ) {
	$o = '<form class="' . esc_attr( $class ) . '" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="wd_talk"><input type="hidden" name="op" value="' . esc_attr( $op ) . '">' . ( is_user_logged_in() ? wp_nonce_field( 'wd_talk_' . $op, '_wpnonce', true, false ) : '' ) . '<div class="hp" aria-hidden="true"><label>' . esc_html__( 'Website', 'wwmd' ) . ' <input name="website" tabindex="-1" autocomplete="off"></label></div>';
	foreach ( $hidden as $k => $v ) {
		$o .= '<input type="hidden" name="' . esc_attr( $k ) . '" value="' . esc_attr( $v ) . '">';
	}
	return $o . $inner . '</form>';
}

/**
 * Back to a page with a note.
 *
 * @param string $url  Page.
 * @param string $note Note key.
 */
function wd_talk_back( $url, $note ) {
	wp_safe_redirect( add_query_arg( 'wdn', $note, $url ) );
	exit;
}

/* Anyone can post & comment, signed in or not (Ayoola, 2026-11-17). */
add_action( 'admin_post_nopriv_wd_talk', 'wd_talk_save' );
add_action( 'admin_post_wd_talk', 'wd_talk_save' );

/* The feed pages in tens, so "Show more posts" & /community/page/2/ line up with wd_talk_query(). */
add_action(
	'pre_get_posts',
	static function ( $q ) {
		if ( ! is_admin() && $q->is_main_query() && $q->is_post_type_archive( 'wd_talk' ) ) {
			$q->set( 'posts_per_page', 10 );
		}
	}
);

/**
 * Post, reply, report or delete.
 */
function wd_talk_save() {
	$op = sanitize_key( wp_unslash( $_POST['op'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification
	$p  = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification
	wd_talk_guest_cookie();
	$me = wd_talk_me();
	if ( $me[0] ) {
		check_admin_referer( 'wd_talk_' . $op );
	} else {
		// Visitors: no nonce (pages may be cached), so the form must come from this site & bots that
		// fill the hidden field are dropped.
		$ref = (string) wp_get_referer();
		if ( '' !== trim( (string) ( $p['website'] ?? '' ) ) || ! $ref || wp_parse_url( $ref, PHP_URL_HOST ) !== wp_parse_url( home_url(), PHP_URL_HOST ) ) {
			wd_talk_back( get_post_type_archive_link( 'wd_talk' ), 'denied' );
		}
	}
	$uid  = $me[0];
	$home = get_post_type_archive_link( 'wd_talk' );
	$hold = (bool) wd_opt( 'moderate_talk' );

	switch ( $op ) {
		case 'post':
			$body  = trim( sanitize_textarea_field( $p['body'] ?? '' ) );
			$topic = sanitize_key( $p['topic'] ?? '' );
			$bg    = sanitize_key( $p['bg'] ?? '' );
			$bg    = isset( wd_talk_bgs()[ $bg ] ) ? $bg : 'paper';
			$name  = wd_talk_clean_name( $p['name'] ?? '' );
			if ( '' === $name ) {
				wd_talk_back( add_query_arg( 'ask', 1, $home ) . '#ask', 'talk_name' );
			}
			if ( mb_strlen( $body ) < 3 || mb_strlen( $body ) > 2000 || ! isset( wd_talk_topics()[ $topic ] ) ) {
				wd_talk_back( add_query_arg( 'ask', 1, $home ) . '#ask', 'talk_short' );
			}
			if ( ! wd_rate_ok( 'talk_post', 6, DAY_IN_SECONDS ) ) {
				wd_talk_back( $home, 'slow' );
			}
			$pid = wp_insert_post(
				array(
					'post_type'      => 'wd_talk',
					'post_status'    => $hold ? 'pending' : 'publish',
					// No title to fill in: the first words name the post's address & browser tab.
					'post_title'     => wp_trim_words( $body, 10, '…' ),
					'post_content'   => $body,
					'post_author'    => $uid,
					'comment_status' => 'closed',
				)
			);
			if ( ! $pid || is_wp_error( $pid ) ) {
				wd_talk_back( $home, 'talk_short' );
			}
			update_post_meta( $pid, '_wd_topic', $topic );
			update_post_meta( $pid, '_wd_bg', $bg );
			update_post_meta( $pid, '_wd_likes', 0 );
			update_post_meta( $pid, '_wd_name', $name );
			if ( ! $uid ) {
				update_post_meta( $pid, '_wd_guest', $me[1] );
			}
			wd_talk_back( $hold ? $home : get_permalink( $pid ), $hold ? 'talk_held' : 'talk_posted' );
			break;

		case 'reply':
			$pid  = (int) ( $p['pid'] ?? 0 );
			$body = trim( sanitize_textarea_field( $p['body'] ?? '' ) );
			$name = wd_talk_clean_name( $p['name'] ?? '' );
			if ( 'wd_talk' !== get_post_type( $pid ) || 'publish' !== get_post_status( $pid ) ) {
				wd_talk_back( $home, 'denied' );
			}
			$back = get_permalink( $pid ) . '#replies';
			if ( '' === $name ) {
				wd_talk_back( $back, 'talk_name' );
			}
			if ( '' === $body || mb_strlen( $body ) > 1500 ) {
				wd_talk_back( $back, 'talk_short' );
			}
			if ( ! wd_rate_ok( 'talk_reply', 40, HOUR_IN_SECONDS ) ) {
				wd_talk_back( $back, 'slow' );
			}
			$cid = wp_insert_comment(
				array(
					'comment_post_ID'      => $pid,
					'user_id'              => $uid,
					'comment_content'      => $body,
					'comment_type'         => 'wd_reply',
					'comment_approved'     => $hold ? 0 : 1,
					'comment_author'       => '',
					'comment_author_email' => '',
					'comment_author_url'   => '',
					'comment_author_IP'    => '',
					'comment_agent'        => '',
				)
			);
			if ( $cid ) {
				update_comment_meta( $cid, '_wd_name', $name );
				if ( ! $uid ) {
					update_comment_meta( $cid, '_wd_guest', $me[1] );
				}
			}
			wd_talk_back( $back, $hold ? 'talk_held' : 'talk_replied' );
			break;

		case 'report':
			$pid = (int) ( $p['pid'] ?? 0 );
			$cid = (int) ( $p['cid'] ?? 0 );
			$rep = $uid ? $uid : $me[1];
			if ( $cid ) {
				$c = get_comment( $cid );
				if ( $c && 'wd_reply' === $c->comment_type ) {
					$who = array_unique( array_merge( (array) get_comment_meta( $cid, '_wd_reports', true ), array( $rep ) ) );
					update_comment_meta( $cid, '_wd_reports', $who );
					if ( count( $who ) >= 3 ) {
						wp_set_comment_status( $cid, 'hold' );
					}
				}
			} elseif ( 'wd_talk' === get_post_type( $pid ) && ! wd_talk_is_team( $pid ) ) {
				$who = array_unique( array_merge( (array) get_post_meta( $pid, '_wd_reports', true ), array( $rep ) ) );
				update_post_meta( $pid, '_wd_reports', $who );
				if ( count( $who ) >= 3 ) {
					wp_update_post(
						array(
							'ID'          => $pid,
							'post_status' => 'pending',
						)
					);
					wd_talk_back( $home, 'talk_reported' );
				}
			}
			wd_talk_back( $pid ? get_permalink( $pid ) : $home, 'talk_reported' );
			break;

		case 'delete':
			$pid = (int) ( $p['pid'] ?? 0 );
			$cid = (int) ( $p['cid'] ?? 0 );
			if ( $cid ) {
				$c = get_comment( $cid );
				if ( $c && 'wd_reply' === $c->comment_type && wd_talk_mine( (int) $c->user_id, (string) get_comment_meta( $cid, '_wd_guest', true ) ) ) {
					wp_delete_comment( $cid, true );
				}
				wd_talk_back( get_permalink( $pid ) . '#replies', 'deleted' );
			}
			if ( 'wd_talk' === get_post_type( $pid ) && wd_talk_mine( (int) get_post_field( 'post_author', $pid ), (string) get_post_meta( $pid, '_wd_guest', true ) ) ) {
				wp_delete_post( $pid, true );
			}
			wd_talk_back( $home, 'deleted' );
			break;
	}
	wd_talk_back( $home, 'denied' );
}

add_action(
	'rest_api_init',
	static function () {
		register_rest_route(
			'wd/v1',
			'/talk/like',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => static function ( WP_REST_Request $r ) {
					global $wpdb;
					$pid = (int) $r->get_param( 'id' );
					if ( 'wd_talk' !== get_post_type( $pid ) || 'publish' !== get_post_status( $pid ) ) {
						return new WP_Error( 'gone', 'Not found', array( 'status' => 404 ) );
					}
					if ( ! wd_rate_ok( 'like', 120, HOUR_IN_SECONDS ) ) {
						return wd_slow_down();
					}
					$lt    = wd_t( 'likes' );
					$voter = wd_voter();
					$has   = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $lt WHERE obj_type = 'talk' AND obj_id = %d AND voter = %s", $pid, $voter ) ); // phpcs:ignore WordPress.DB
					if ( $has ) {
						$wpdb->delete( $lt, array( 'id' => $has ) );
					} else {
						$wpdb->insert(
							$lt,
							array(
								'obj_type' => 'talk',
								'obj_id'   => $pid,
								'voter'    => $voter,
								'created'  => current_time( 'mysql', true ),
							)
						);
					}
					$n = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $lt WHERE obj_type = 'talk' AND obj_id = %d", $pid ) ); // phpcs:ignore WordPress.DB
					update_post_meta( $pid, '_wd_likes', $n );
					return array(
						'ok'    => true,
						'liked' => ! $has,
						'likes' => $n,
						'label' => number_format_i18n( $n ),
					);
				},
			)
		);
	}
);

/**
 * Three conversation starters from the team, made once. Signed by the team, never as anonymous people.
 */
function wd_talk_seed() {
	// Already done, or this site already has a community of its own: leave it alone.
	if ( get_option( 'wd_talk_seeded' ) ) {
		return;
	}
	update_option( 'wd_talk_seeded', 1 );
	$have = get_posts(
		array(
			'post_type'        => 'wd_talk',
			'post_status'      => 'any',
			'numberposts'      => 1,
			'fields'           => 'ids',
			'suppress_filters' => false,
		)
	);
	if ( $have ) {
		return;
	}

	require_once WD_DIR . '/inc/talk-seed.php';
	$rows = wd_talk_seed_data();
	if ( ! $rows ) {
		return;
	}
	$now   = time();
	$bgs   = wd_talk_bgs();
	$tops  = wd_talk_topics();
	$made  = 0;

	foreach ( $rows as $r ) {
		$topic = isset( $tops[ $r['topic'] ] ) ? $r['topic'] : 'confess';
		$bg    = isset( $bgs[ $r['bg'] ] ) ? $r['bg'] : 'paper';
		$when  = $now - max( 1, (int) $r['hours'] ) * HOUR_IN_SECONDS;
		$pid   = wp_insert_post(
			array(
				'post_type'      => 'wd_talk',
				'post_status'    => 'publish',
				'post_title'     => wp_trim_words( $r['body'], 10, '…' ),
				'post_content'   => $r['body'],
				// No account is attached: a seeded post belongs to nobody, exactly like a visitor's.
				'post_author'    => 0,
				'comment_status' => 'open',
				'post_date'      => wp_date( 'Y-m-d H:i:s', $when ),
				'post_date_gmt'  => gmdate( 'Y-m-d H:i:s', $when ),
			)
		);
		if ( ! $pid || is_wp_error( $pid ) ) {
			continue;
		}
		++$made;
		update_post_meta( $pid, '_wd_topic', $topic );
		update_post_meta( $pid, '_wd_bg', $bg );
		update_post_meta( $pid, '_wd_likes', max( 0, (int) $r['likes'] ) );
		update_post_meta( $pid, '_wd_name', wd_talk_clean_name( $r['name'] ) );
		update_post_meta( $pid, '_wd_notitle', 1 );
		update_post_meta( $pid, '_wd_seed', 1 );

		foreach ( (array) $r['replies'] as $rep ) {
			$rwhen = $now - max( 0, (int) $rep[2] ) * HOUR_IN_SECONDS;
			if ( $rwhen < $when ) {
				$rwhen = $when + HOUR_IN_SECONDS;
			}
			$cid = wp_insert_comment(
				array(
					'comment_post_ID'      => $pid,
					'user_id'              => 0,
					'comment_content'      => $rep[1],
					'comment_type'         => 'wd_reply',
					'comment_approved'     => 1,
					'comment_author'       => '',
					'comment_author_email' => '',
					'comment_author_url'   => '',
					'comment_author_IP'    => '',
					'comment_agent'        => '',
					'comment_date'         => wp_date( 'Y-m-d H:i:s', $rwhen ),
					'comment_date_gmt'     => gmdate( 'Y-m-d H:i:s', $rwhen ),
				)
			);
			if ( $cid ) {
				update_comment_meta( $cid, '_wd_name', wd_talk_clean_name( $rep[0] ) );
				update_comment_meta( $cid, '_wd_seed', 1 );
			}
		}
	}
	update_option( 'wd_talk_seeded_n', $made );
}

/**
 * 1.5.1: the header menu's Vendors link becomes "Anonymous community"; the footer gets the link too.
 * Runs once; a menu the owner already changed away from Vendors keeps its links.
 */
function wd_upgrade_talk_menu() {
	if ( get_option( 'wd_menu_talk' ) ) {
		return;
	}
	update_option( 'wd_menu_talk', 1 );
	$locs = (array) get_theme_mod( 'nav_menu_locations', array() );
	$vend = untrailingslashit( wd_url( 'vendors' ) );
	$talk = get_post_type_archive_link( 'wd_talk' );
	if ( ! empty( $locs['primary'] ) ) {
		foreach ( (array) wp_get_nav_menu_items( $locs['primary'], array( 'post_status' => 'any' ) ) as $it ) {
			if ( untrailingslashit( $it->url ) === $vend ) {
				wp_update_nav_menu_item(
					(int) $locs['primary'],
					$it->ID,
					array(
						'menu-item-title'    => __( 'Anonymous community', 'wwmd' ),
						'menu-item-url'      => $talk,
						'menu-item-status'   => 'publish',
						'menu-item-type'     => 'custom',
						'menu-item-position' => $it->menu_order,
					)
				);
			}
		}
	}
	if ( ! empty( $locs['footer'] ) ) {
		$items = (array) wp_get_nav_menu_items( $locs['footer'], array( 'post_status' => 'any' ) );
		$have  = false;
		$after = count( $items );
		foreach ( $items as $it ) {
			if ( untrailingslashit( $it->url ) === untrailingslashit( $talk ) ) {
				$have = true;
			}
			if ( untrailingslashit( $it->url ) === $vend ) {
				$after = $it->menu_order;
			}
		}
		if ( ! $have ) {
			foreach ( $items as $it ) {
				if ( $it->menu_order > $after ) {
					wp_update_post(
						array(
							'ID'         => $it->ID,
							'menu_order' => $it->menu_order + 1,
						)
					);
				}
			}
			wp_update_nav_menu_item(
				(int) $locs['footer'],
				0,
				array(
					'menu-item-title'    => __( 'Anonymous community', 'wwmd' ),
					'menu-item-url'      => $talk,
					'menu-item-status'   => 'publish',
					'menu-item-type'     => 'custom',
					'menu-item-position' => $after + 1,
				)
			);
		}
	}
}
