<?php
/**
 * Chat between customers & partners, on the site.
 *
 * One conversation per customer & seller; each message can name the listing it is about. Nobody books
 * directly: the seller sends an invoice or a payment link in the chat (cost lines, a date & a note),
 * each with its own page at /pay/{code}/. A payment link is paid on the site & the seller's share goes
 * straight to them. An invoice shows how to pay the seller (bank details & so on); the seller marks it
 * paid. Paid ones become receipts. Messages hold at most 4 numbers; invoices can hold any.
 * HQ can read every chat & answer as "Our team" (that is how chats with starter sellers get answered).
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

/**
 * The off-platform payment warning (removed in 1.5.1; kept so older custom code does not break).
 *
 * @return string
 */
function wd_offsite_warning() {
	return ''; // 1.5.1: removed. We are not an escrow; buyers & sellers agree how to pay.
}

/**
 * Whether a chat offer is an invoice (paid to the seller directly) or a payment link (paid on the site).
 *
 * @param object $row Message with an offer.
 * @return bool
 */
function wd_offer_is_invoice( $row ) {
	return 'invoice' === ( $row->offer_kind ?? '' );
}

/**
 * The signed-in person's part in a chat: user (the customer), partner, hq, or '' (no access).
 *
 * @param int $pid Partner user ID.
 * @param int $uid Customer user ID.
 * @return string
 */
function wd_chat_role( $pid, $uid ) {
	$me = get_current_user_id();
	if ( ! $me || ! $pid ) {
		return '';
	}
	if ( $me === (int) $uid && $me !== (int) $pid ) {
		return 'user';
	}
	if ( $me === (int) $pid ) {
		return 'partner';
	}
	return current_user_can( 'manage_options' ) ? 'hq' : '';
}

/**
 * Messages in one conversation, oldest first.
 *
 * @param int $pid   Partner.
 * @param int $uid   Customer.
 * @param int $after Only messages after this ID.
 * @return object[]
 */
function wd_chat_rows( $pid, $uid, $after = 0 ) {
	global $wpdb;
	return (array) $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . wd_t( 'chats' ) . ' WHERE partner_id = %d AND user_id = %d AND id > %d ORDER BY id ASC LIMIT 300', $pid, $uid, $after ) ); // phpcs:ignore WordPress.DB
}

/**
 * Mark the other side's messages as read.
 *
 * @param int    $pid  Partner.
 * @param int    $uid  Customer.
 * @param string $role Who is reading.
 */
function wd_chat_seen( $pid, $uid, $role ) {
	global $wpdb;
	if ( 'user' === $role ) {
		$wpdb->query( $wpdb->prepare( 'UPDATE ' . wd_t( 'chats' ) . " SET seen = 1 WHERE partner_id = %d AND user_id = %d AND sender <> 'user' AND seen = 0", $pid, $uid ) ); // phpcs:ignore WordPress.DB
	} elseif ( 'partner' === $role ) {
		$wpdb->query( $wpdb->prepare( 'UPDATE ' . wd_t( 'chats' ) . " SET seen = 1 WHERE partner_id = %d AND user_id = %d AND sender = 'user' AND seen = 0", $pid, $uid ) ); // phpcs:ignore WordPress.DB
	}
}

/**
 * Conversations, newest first, with the last message & unread count.
 *
 * @param string $by    partner|user|hq.
 * @param int    $id    The partner's or customer's user ID (not used for hq).
 * @param int    $limit How many.
 * @return object[] partner_id, user_id, last_id, unread, msg (last row).
 */
function wd_chat_threads( $by, $id = 0, $limit = 40 ) {
	global $wpdb;
	$t = wd_t( 'chats' );
	if ( 'partner' === $by ) {
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT partner_id, user_id, MAX(id) last_id, SUM(CASE WHEN sender = 'user' AND seen = 0 THEN 1 ELSE 0 END) unread FROM $t WHERE partner_id = %d GROUP BY partner_id, user_id ORDER BY last_id DESC LIMIT %d", $id, $limit ) ); // phpcs:ignore WordPress.DB
	} elseif ( 'user' === $by ) {
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT partner_id, user_id, MAX(id) last_id, SUM(CASE WHEN sender <> 'user' AND seen = 0 THEN 1 ELSE 0 END) unread FROM $t WHERE user_id = %d GROUP BY partner_id, user_id ORDER BY last_id DESC LIMIT %d", $id, $limit ) ); // phpcs:ignore WordPress.DB
	} else {
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT partner_id, user_id, MAX(id) last_id, SUM(CASE WHEN sender = 'user' AND seen = 0 THEN 1 ELSE 0 END) unread FROM $t GROUP BY partner_id, user_id ORDER BY last_id DESC LIMIT %d", $limit ) ); // phpcs:ignore WordPress.DB
	}
	$rows = (array) $rows;
	if ( $rows ) {
		$last = $wpdb->get_results( 'SELECT * FROM ' . $t . ' WHERE id IN (' . implode( ',', array_map( 'intval', wp_list_pluck( $rows, 'last_id' ) ) ) . ')', OBJECT_K ); // phpcs:ignore WordPress.DB
		foreach ( $rows as $r ) {
			$r->msg = $last[ $r->last_id ] ?? null;
		}
	}
	return $rows;
}

/**
 * Unread messages waiting for someone.
 *
 * @param string $by  partner|user.
 * @param int    $id  User ID.
 * @return int
 */
function wd_chat_unread( $by, $id ) {
	global $wpdb;
	$t = wd_t( 'chats' );
	if ( 'partner' === $by ) {
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $t WHERE partner_id = %d AND sender = 'user' AND seen = 0", $id ) ); // phpcs:ignore WordPress.DB
	}
	return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $t WHERE user_id = %d AND sender <> 'user' AND seen = 0", $id ) ); // phpcs:ignore WordPress.DB
}

/**
 * How many digits a message may hold. More than this & it is not sent, so phone & account numbers
 * (& prices meant to be paid elsewhere) stay out of chats. Prices go in a payment link instead.
 *
 * @return int
 */
function wd_chat_max_digits() {
	return (int) apply_filters( 'wd_chat_max_digits', 4 );
}

/**
 * Whether a message has more digits than allowed (any script's digits count).
 *
 * @param string $text Message.
 * @return bool
 */
function wd_chat_too_many_digits( $text ) {
	return preg_match_all( '/\p{Nd}/u', (string) $text ) > wd_chat_max_digits();
}

/**
 * The message shown when a chat message has too many numbers.
 *
 * @return string
 */
function wd_chat_digits_msg() {
	/* translators: %d max digits */
	return sprintf( __( 'Messages can have at most %d numbers, so phone & account numbers stay off the chat. Write dates in words (like 12 December) & put prices in a payment link.', 'wwmd' ), wd_chat_max_digits() );
}

/**
 * Whether a message looks like phone numbers, bank details, an email or a push to pay outside.
 *
 * @param string $text Message.
 * @return bool
 */
function wd_chat_risky( $text ) {
	$text = (string) $text;
	if ( preg_match( '/\d{10,}/', preg_replace( '/[\s().+-]/', '', $text ) ) || preg_match( '/[^\s@]+@[^\s@]+\.[a-z]{2,}/i', $text ) ) {
		return true;
	}
	return (bool) preg_match( '/\b(transfer|acct|account (no|num|number|details)|bank details|opay|palmpay|moniepoint|kuda|pay me direct|pay direct|outside (the|this) (site|app|platform)|whatsapp me|call me on)\b/i', $text );
}

/**
 * The name shown on a message.
 *
 * @param object $row Message.
 * @return string
 */
function wd_chat_name( $row ) {
	if ( 'hq' === $row->sender ) {
		return __( 'Our team', 'wwmd' );
	}
	if ( 'partner' === $row->sender ) {
		$p = wd_partner( (int) $row->partner_id );
		return $p ? $p['business'] : __( 'Seller', 'wwmd' );
	}
	$u = get_userdata( (int) $row->user_id );
	return $u ? $u->display_name : __( 'Customer', 'wwmd' );
}

/**
 * Messages as list items for the chat log.
 *
 * @param object[] $rows Messages, oldest first.
 * @param string   $role Who is reading.
 * @param int      $prev Listing the message before these was about (to label topic changes).
 * @return string HTML.
 */
function wd_chat_render( $rows, $role, $prev = -1 ) {
	$out = '';
	foreach ( $rows as $row ) {
		$lid  = (int) $row->listing_id;
		$mine = $row->sender === $role;
		$out .= '<li class="msg msg--' . esc_attr( $row->sender ) . ( $mine ? ' is-mine' : '' ) . '" data-id="' . (int) $row->id . '">';
		if ( $lid && $lid !== $prev && ! $row->offer_status && 'wd_listing' === get_post_type( $lid ) ) {
			$out .= '<a class="msg__about" href="' . esc_url( get_permalink( $lid ) ) . '">' . wd_icon( 'link', 13 ) . esc_html( get_the_title( $lid ) ) . '</a>';
		}
		$out .= '<p class="msg__who"><b>' . esc_html( $mine ? __( 'You', 'wwmd' ) : wd_chat_name( $row ) ) . '</b> <time datetime="' . esc_attr( mysql2date( 'c', $row->created . ' UTC' ) ) . '">' . esc_html( wd_chat_when( $row->created ) ) . '</time></p>';
		if ( '' !== trim( (string) $row->body ) ) {
			$out .= '<div class="msg__text">' . nl2br( esc_html( $row->body ) ) . '</div>';
		}
		if ( $row->offer_status ) {
			$out .= wd_chat_offer_html( $row, $role );
		}
		if ( wd_chat_risky( $row->body ) ) {
			$out .= '<p class="msg__warn">' . wd_icon( 'shield', 14 ) . esc_html__( 'This message may have phone, bank or payment details. Never pay outside this site: we cannot protect that money.', 'wwmd' ) . '</p>';
		}
		$out .= '</li>';
		if ( $lid ) {
			$prev = $lid;
		}
	}
	return $out;
}

/**
 * "Today 14:05", "Mon 9:30" or "3 Nov".
 *
 * @param string $mysql UTC time.
 * @return string
 */
function wd_chat_when( $mysql ) {
	$t = strtotime( $mysql . ' UTC' );
	if ( ! $t ) {
		return '';
	}
	if ( wp_date( 'Y-m-d', $t ) === wp_date( 'Y-m-d' ) ) {
		return wp_date( 'H:i', $t );
	}
	return time() - $t < 6 * DAY_IN_SECONDS ? wp_date( 'D H:i', $t ) : wp_date( 'j M', $t );
}

/**
 * An offer card inside a message.
 *
 * @param object $row  Message with an offer.
 * @param string $role Who is reading.
 * @return string HTML.
 */
function wd_chat_offer_html( $row, $role ) {
	$url   = wd_offer_url( $row );
	$state = wd_offer_states();
	$out   = '<div class="offer offer--' . esc_attr( $row->offer_status ) . '" data-offer="' . (int) $row->id . '">';
	$inv   = wd_offer_is_invoice( $row );
	$out  .= '<p class="offer__tag">' . wd_icon( $inv ? 'feed' : 'cash', 15 ) . ( 'paid' === $row->offer_status ? esc_html__( 'Receipt', 'wwmd' ) : ( $inv ? esc_html__( 'Invoice', 'wwmd' ) : esc_html__( 'Payment link', 'wwmd' ) ) ) . ( $row->offer_code ? ' <span class="offer__no">#' . esc_html( strtoupper( $row->offer_code ) ) . '</span>' : '' ) . '</p>';
	$out  .= wd_offer_lines_html( $row );
	$out  .= '<p class="offer__for">' . esc_html( get_the_title( (int) $row->listing_id ) ) . ' · ' . esc_html( wd_offer_when( $row ) ) . '</p>';
	if ( isset( $state[ $row->offer_status ] ) ) {
		$out .= '<p class="offer__state">' . esc_html( $state[ $row->offer_status ] ) . '</p>';
		if ( 'paid' === $row->offer_status && $url ) {
			$out .= '<a class="btn btn--ghost btn--sm btn--block" href="' . esc_url( $url ) . '">' . esc_html__( 'View receipt', 'wwmd' ) . '</a>';
		}
	} else {
		if ( 'user' === $role && $url ) {
			$out .= $inv
				? '<a class="btn btn--marigold btn--block" href="' . esc_url( $url ) . '">' . wd_icon( 'feed', 17 ) . esc_html__( 'View invoice & how to pay', 'wwmd' ) . '</a>'
				/* translators: %s amount */
				: '<a class="btn btn--marigold btn--block" href="' . esc_url( $url ) . '">' . wd_icon( 'lock', 17 ) . esc_html( sprintf( __( 'Pay %s', 'wwmd' ), wd_money( $row->offer_amount ) ) ) . '</a>';
		} else {
			$out .= '<p class="offer__state">' . esc_html__( 'Waiting for the customer to pay', 'wwmd' ) . '</p>';
		}
		$out .= '<p class="offer__acts">';
		if ( $url ) {
			$out .= '<a class="linkish" href="' . esc_url( $url ) . '">' . wd_icon( 'link', 14 ) . esc_html__( 'Open', 'wwmd' ) . '</a>';
		}
		if ( 'partner' === $role && $inv ) {
			$out .= '<button class="linkish" type="button" data-offer-paid="' . (int) $row->id . '">' . wd_icon( 'check', 14 ) . esc_html__( 'Mark as paid', 'wwmd' ) . '</button>';
		}
		if ( 'partner' === $role ) {
			$out .= '<button class="linkish" type="button" data-offer-cancel="' . (int) $row->id . '">' . esc_html__( 'Cancel', 'wwmd' ) . '</button>';
		}
		$out .= '</p>';
	}
	return $out . '</div>';
}

/**
 * What a payment link's status means.
 *
 * @return array
 */
function wd_offer_states() {
	return array(
		'paid'     => __( 'Paid', 'wwmd' ),
		'replaced' => __( 'Replaced by a newer one', 'wwmd' ),
		'closed'   => __( 'Cancelled by the seller', 'wwmd' ),
	);
}

/**
 * A payment link's cost lines. Links from 1.4 have one line: the listing.
 *
 * @param object $row Message with a payment link.
 * @return array[] Each t (what for) & a (naira).
 */
function wd_offer_items( $row ) {
	$items = json_decode( (string) ( $row->offer_items ?? '' ), true );
	if ( ! is_array( $items ) || ! $items ) {
		return array(
			array(
				't' => get_the_title( (int) $row->listing_id ),
				'a' => (int) $row->offer_amount,
			),
		);
	}
	return $items;
}

/**
 * The cost lines & total, as HTML.
 *
 * @param object $row Message with a payment link.
 * @return string
 */
function wd_offer_lines_html( $row ) {
	$items = wd_offer_items( $row );
	$out   = '<ul class="offer__lines">';
	foreach ( $items as $it ) {
		$out .= '<li><span>' . esc_html( $it['t'] ) . '</span><b>' . esc_html( wd_money( $it['a'] ) ) . '</b></li>';
	}
	$out .= '</ul><p class="offer__total"><span>' . esc_html__( 'Total', 'wwmd' ) . '</span><b>' . esc_html( wd_money( $row->offer_amount ) ) . '</b></p>';
	return $out;
}

/**
 * The service date(s) on a payment link.
 *
 * @param object $row Message with a payment link.
 * @return string
 */
function wd_offer_when( $row ) {
	if ( $row->offer_end && $row->offer_end !== $row->offer_start ) {
		return wd_date( $row->offer_start, 'j M' ) . ' – ' . wd_date( $row->offer_end, 'j M Y' );
	}
	return wd_date( $row->offer_start, 'D j M Y' );
}

/**
 * A payment link's own page.
 *
 * @param object $row Message with a payment link.
 * @return string
 */
function wd_offer_url( $row ) {
	return empty( $row->offer_code ) ? '' : home_url( '/pay/' . $row->offer_code . '/' );
}

/**
 * Find a payment link by its code.
 *
 * @param string $code Code from the URL.
 * @return object|null
 */
function wd_offer_by_code( $code ) {
	global $wpdb;
	$code = strtolower( preg_replace( '/[^a-zA-Z0-9]/', '', (string) $code ) );
	if ( strlen( $code ) < 6 ) {
		return null;
	}
	return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . wd_t( 'chats' ) . " WHERE offer_code = %s AND offer_status <> '' LIMIT 1", $code ) ); // phpcs:ignore WordPress.DB
}

/**
 * A new, unused payment link code.
 *
 * @return string
 */
function wd_offer_code() {
	do {
		$code = strtolower( wp_generate_password( 8, false, false ) );
	} while ( wd_offer_by_code( $code ) );
	return $code;
}

/**
 * Add the payment link columns to chats from before 1.5, & give older offers a code.
 */
function wd_chat_columns() {
	global $wpdb;
	$t = wd_t( 'chats' );
	$s = $wpdb->suppress_errors( true );
	$ok = false !== $wpdb->query( "SELECT offer_code FROM $t LIMIT 1" ); // phpcs:ignore WordPress.DB
	$wpdb->suppress_errors( $s );
	if ( ! $ok ) {
		$wpdb->query( "ALTER TABLE $t ADD COLUMN offer_items text" ); // phpcs:ignore WordPress.DB
		$wpdb->query( "ALTER TABLE $t ADD COLUMN offer_code varchar(16) NOT NULL DEFAULT ''" ); // phpcs:ignore WordPress.DB
	}
	// 1.5.1: invoices (paid to the seller directly) next to payment links.
	$s  = $wpdb->suppress_errors( true );
	$ok = false !== $wpdb->query( "SELECT offer_kind FROM $t LIMIT 1" ); // phpcs:ignore WordPress.DB
	$wpdb->suppress_errors( $s );
	if ( ! $ok ) {
		$wpdb->query( "ALTER TABLE $t ADD COLUMN offer_kind varchar(10) NOT NULL DEFAULT ''" ); // phpcs:ignore WordPress.DB
		$wpdb->query( "ALTER TABLE $t ADD COLUMN offer_info text" ); // phpcs:ignore WordPress.DB
	}
	foreach ( (array) $wpdb->get_col( "SELECT id FROM $t WHERE offer_status <> '' AND offer_code = ''" ) as $id ) { // phpcs:ignore WordPress.DB
		$wpdb->update( $t, array( 'offer_code' => wd_offer_code() ), array( 'id' => (int) $id ) );
	}
}

/**
 * A conversation's new messages for the browser.
 *
 * @param int    $pid   Partner.
 * @param int    $uid   Customer.
 * @param string $role  Reader.
 * @param int    $after Last message the browser has.
 * @return array html, last
 */
function wd_chat_payload( $pid, $uid, $role, $after ) {
	global $wpdb;
	$rows = wd_chat_rows( $pid, $uid, $after );
	wd_chat_seen( $pid, $uid, $role );
	$prev = $after ? (int) $wpdb->get_var( $wpdb->prepare( 'SELECT listing_id FROM ' . wd_t( 'chats' ) . ' WHERE partner_id = %d AND user_id = %d AND id <= %d AND listing_id > 0 ORDER BY id DESC LIMIT 1', $pid, $uid, $after ) ) : -1; // phpcs:ignore WordPress.DB
	return array(
		'ok'   => true,
		'html' => wd_chat_render( $rows, $role, $prev ? $prev : -1 ),
		'last' => $rows ? (int) end( $rows )->id : (int) $after,
	);
}

/**
 * Tell the other side about a new message (at most once every 20 minutes per conversation).
 *
 * @param int    $pid    Partner.
 * @param int    $uid    Customer.
 * @param string $sender Who wrote.
 * @param string $text   Message.
 * @param int    $lid    Listing.
 * @param bool   $always Send even if we emailed in the last 20 minutes (payment links).
 */
function wd_chat_notify( $pid, $uid, $sender, $text, $lid, $always = false ) {
	$hq = false;
	if ( 'user' === $sender ) {
		$to   = get_userdata( $pid );
		$link = wd_account_url( 'partner', array( 'part' => 'chat', 'user' => $uid ) );
		// Nobody reads a starter partner's inbox, so HQ answers for them.
		if ( ! $to || get_user_meta( $pid, '_wd_sample', true ) ) {
			$hq   = true;
			$link = add_query_arg( array( 'tab' => 'market', 'chat' => $pid . '-' . $uid ), wd_url( 'hq' ) ) . '#chats';
		}
	} else {
		$to   = get_userdata( $uid );
		$link = wd_account_url( 'services', array( 'chat' => $pid ) ) . '#chat';
		if ( $to && get_user_meta( $uid, '_wd_sample', true ) ) {
			return;
		}
	}
	$email = $hq ? get_option( 'admin_email' ) : ( $to ? $to->user_email : '' );
	$key   = 'wd_cn_' . md5( $pid . '-' . $uid . '-' . $sender );
	if ( ! is_email( $email ) || ( get_transient( $key ) && ! $always ) ) {
		return;
	}
	set_transient( $key, 1, 20 * MINUTE_IN_SECONDS );
	$p    = wd_partner( $pid );
	$from = 'user' === $sender ? ( get_userdata( $uid ) ? get_userdata( $uid )->display_name : __( 'A customer', 'wwmd' ) ) : ( 'hq' === $sender ? __( 'Our team', 'wwmd' ) : ( $p ? $p['business'] : __( 'A seller', 'wwmd' ) ) );
	wp_mail(
		$email,
		/* translators: %s sender */
		sprintf( __( 'New message from %s', 'wwmd' ), $from ),
		sprintf(
			/* translators: 1 sender 2 listing 3 message 4 link */
			__( "%1\$s sent a message%2\$s:\n\n“%3\$s”\n\nReply here: %4\$s", 'wwmd' ),
			$from,
			$lid ? ' ' . sprintf( /* translators: %s listing */ __( 'about %s', 'wwmd' ), get_the_title( $lid ) ) : '',
			wp_trim_words( $text, 40, '…' ),
			$link
		)
	);
}

/**
 * Who a chat request is about: partner, customer & the reader's role.
 *
 * @param WP_REST_Request $r Request.
 * @return array|WP_Error [pid, uid, role]
 */
function wd_chat_req( WP_REST_Request $r ) {
	$pid  = (int) $r->get_param( 'partner' );
	$me   = get_current_user_id();
	$uid  = $me === $pid ? (int) $r->get_param( 'user' ) : (int) ( $r->get_param( 'user' ) ? $r->get_param( 'user' ) : $me );
	$role = wd_chat_role( $pid, $uid );
	if ( ! $role || ! $uid ) {
		return new WP_Error( 'denied', __( 'You cannot open this chat.', 'wwmd' ), array( 'status' => 403 ) );
	}
	if ( 'user' === $role && ! wd_is_partner( $pid ) ) {
		return new WP_Error( 'paused', __( 'This seller is not taking messages right now.', 'wwmd' ), array( 'status' => 400 ) );
	}
	return array( $pid, $uid, $role );
}

/**
 * Whether the customer has written in this conversation (partners & HQ only answer, they never start one).
 *
 * @param int $pid Partner.
 * @param int $uid Customer.
 * @return bool
 */
function wd_chat_started( $pid, $uid ) {
	global $wpdb;
	return (bool) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . wd_t( 'chats' ) . " WHERE partner_id = %d AND user_id = %d AND sender = 'user' LIMIT 1", $pid, $uid ) ); // phpcs:ignore WordPress.DB
}

add_action(
	'rest_api_init',
	static function () {
		$in = array(
			'methods'             => 'POST',
			'permission_callback' => 'is_user_logged_in',
		);
		register_rest_route( 'wd/v1', '/chat/thread', $in + array( 'callback' => 'wd_rest_chat_thread' ) );
		register_rest_route( 'wd/v1', '/chat/send', $in + array( 'callback' => 'wd_rest_chat_send' ) );
		register_rest_route( 'wd/v1', '/chat/offer', $in + array( 'callback' => 'wd_rest_chat_offer' ) );
		register_rest_route( 'wd/v1', '/chat/pay', $in + array( 'callback' => 'wd_rest_chat_pay' ) );
		register_rest_route( 'wd/v1', '/chat/cancel', $in + array( 'callback' => 'wd_rest_chat_cancel' ) );
		register_rest_route( 'wd/v1', '/chat/paid', $in + array( 'callback' => 'wd_rest_chat_paid' ) );
	}
);

/**
 * New messages in a conversation.
 *
 * @param WP_REST_Request $r Request.
 * @return array|WP_Error
 */
function wd_rest_chat_thread( WP_REST_Request $r ) {
	$q = wd_chat_req( $r );
	if ( is_wp_error( $q ) ) {
		return $q;
	}
	if ( ! wd_rate_ok( 'chatpoll', 120, 600 ) ) {
		return wd_slow_down();
	}
	return wd_chat_payload( $q[0], $q[1], $q[2], max( 0, (int) $r->get_param( 'after' ) ) );
}

/**
 * Send a message.
 *
 * @param WP_REST_Request $r Request.
 * @return array|WP_Error
 */
function wd_rest_chat_send( WP_REST_Request $r ) {
	global $wpdb;
	$q = wd_chat_req( $r );
	if ( is_wp_error( $q ) ) {
		return $q;
	}
	list( $pid, $uid, $role ) = $q;
	$text = trim( mb_substr( sanitize_textarea_field( (string) $r->get_param( 'text' ) ), 0, 1000 ) );
	if ( '' === $text ) {
		return new WP_Error( 'empty', __( 'Write a message first.', 'wwmd' ), array( 'status' => 400 ) );
	}
	if ( 'user' !== $role && ! wd_chat_started( $pid, $uid ) ) {
		return new WP_Error( 'denied', __( 'You can only answer customers who wrote to you.', 'wwmd' ), array( 'status' => 403 ) );
	}
	if ( 'hq' !== $role && wd_chat_too_many_digits( $text ) ) {
		return new WP_Error( 'digits', wd_chat_digits_msg(), array( 'status' => 400 ) );
	}
	if ( ! wd_rate_ok( 'chat', 30, 600 ) ) {
		return wd_slow_down();
	}
	$lid = (int) $r->get_param( 'listing' );
	if ( $lid && ( 'wd_listing' !== get_post_type( $lid ) || (int) get_post_field( 'post_author', $lid ) !== $pid ) ) {
		$lid = 0;
	}
	$wpdb->insert(
		wd_t( 'chats' ),
		array(
			'partner_id' => $pid,
			'user_id'    => $uid,
			'sender'     => $role,
			'author_id'  => get_current_user_id(),
			'listing_id' => $lid,
			'body'       => $text,
			'created'    => current_time( 'mysql', true ),
		)
	);
	wd_chat_notify( $pid, $uid, $role, $text, $lid );
	return wd_chat_payload( $pid, $uid, $role, max( 0, (int) $r->get_param( 'after' ) ) );
}

/**
 * The partner sends an offer: a price & a date for one of their listings.
 *
 * @param WP_REST_Request $r Request.
 * @return array|WP_Error
 */
function wd_rest_chat_offer( WP_REST_Request $r ) {
	global $wpdb;
	$q = wd_chat_req( $r );
	if ( is_wp_error( $q ) ) {
		return $q;
	}
	list( $pid, $uid, $role ) = $q;
	if ( 'partner' !== $role || ! wd_is_partner( $pid ) || ! wd_chat_started( $pid, $uid ) ) {
		return new WP_Error( 'denied', __( 'Only the seller can send an invoice or payment link, once the customer has written.', 'wwmd' ), array( 'status' => 403 ) );
	}
	$kind = 'invoice' === $r->get_param( 'kind' ) ? 'invoice' : 'link';
	$info = '';
	if ( 'invoice' === $kind ) {
		$info = trim( mb_substr( sanitize_textarea_field( (string) $r->get_param( 'info' ) ), 0, 600 ) );
		if ( mb_strlen( $info ) < 6 ) {
			return new WP_Error( 'info', __( 'Say how the customer should pay you, e.g. your bank, account name & number.', 'wwmd' ), array( 'status' => 400 ) );
		}
		update_user_meta( $pid, 'wd_pay_info', $info );
	}
	$lid = (int) $r->get_param( 'listing' );
	if ( 'wd_listing' !== get_post_type( $lid ) || 'publish' !== get_post_status( $lid ) || (int) get_post_field( 'post_author', $lid ) !== $pid ) {
		return new WP_Error( 'listing', __( 'Pick one of your live listings.', 'wwmd' ), array( 'status' => 400 ) );
	}
	// Cost lines: item_t[] (what for) & item_a[] (naira), up to 12. A single "amount" still works.
	$ts    = (array) $r->get_param( 'item_t' );
	$as    = (array) $r->get_param( 'item_a' );
	$items = array();
	if ( ! $ts && $r->get_param( 'amount' ) ) {
		$ts = array( get_the_title( $lid ) );
		$as = array( $r->get_param( 'amount' ) );
	}
	foreach ( array_slice( $ts, 0, 12 ) as $i => $t ) {
		$t = trim( mb_substr( sanitize_text_field( (string) $t ), 0, 80 ) );
		$a = (int) preg_replace( '/\D/', '', (string) ( $as[ $i ] ?? '' ) );
		if ( '' === $t && ! $a ) {
			continue;
		}
		if ( '' === $t || $a < 100 ) {
			return new WP_Error( 'amount', __( 'Each cost needs what it is for & a price of at least ₦100.', 'wwmd' ), array( 'status' => 400 ) );
		}
		if ( wd_chat_too_many_digits( $t ) ) {
			return new WP_Error( 'digits', wd_chat_digits_msg(), array( 'status' => 400 ) );
		}
		$items[] = array(
			't' => $t,
			'a' => $a,
		);
	}
	$amt   = array_sum( wp_list_pluck( $items, 'a' ) );
	$start = sanitize_text_field( (string) $r->get_param( 'start' ) );
	$end   = sanitize_text_field( (string) $r->get_param( 'end' ) );
	$today = wp_date( 'Y-m-d' );
	if ( ! $items ) {
		return new WP_Error( 'amount', __( 'Add at least one cost.', 'wwmd' ), array( 'status' => 400 ) );
	}
	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $start ) || $start < $today ) {
		return new WP_Error( 'date', __( 'Pick the date of the service, from today on.', 'wwmd' ), array( 'status' => 400 ) );
	}
	if ( $end && ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $end ) || $end < $start ) ) {
		return new WP_Error( 'date', __( 'The end date is before the start date.', 'wwmd' ), array( 'status' => 400 ) );
	}
	if ( ! wd_rate_ok( 'offer', 20, 600 ) ) {
		return wd_slow_down();
	}
	$text = trim( mb_substr( sanitize_textarea_field( (string) $r->get_param( 'text' ) ), 0, 300 ) );
	if ( wd_chat_too_many_digits( $text ) ) {
		return new WP_Error( 'digits', wd_chat_digits_msg(), array( 'status' => 400 ) );
	}
	$code = wd_offer_code();
	$wpdb->insert(
		wd_t( 'chats' ),
		array(
			'partner_id'   => $pid,
			'user_id'      => $uid,
			'sender'       => 'partner',
			'author_id'    => get_current_user_id(),
			'listing_id'   => $lid,
			'body'         => $text,
			'offer_amount' => $amt,
			'offer_start'  => $start,
			'offer_end'    => $end && $end !== $start ? $end : '',
			'offer_status' => 'open',
			'offer_items'  => wp_json_encode( $items ),
			'offer_code'   => $code,
			'offer_kind'   => $kind,
			'offer_info'   => $info,
			'created'      => current_time( 'mysql', true ),
		)
	);
	wd_chat_notify(
		$pid,
		$uid,
		'partner',
		'invoice' === $kind
			/* translators: 1 amount 2 listing 3 link */
			? sprintf( __( 'Invoice: %1$s for %2$s. See it & how to pay: %3$s', 'wwmd' ), wd_money( $amt ), get_the_title( $lid ), home_url( '/pay/' . $code . '/' ) )
			/* translators: 1 amount 2 listing 3 link */
			: sprintf( __( 'Payment link: %1$s for %2$s. Pay here: %3$s', 'wwmd' ), wd_money( $amt ), get_the_title( $lid ), home_url( '/pay/' . $code . '/' ) ),
		$lid,
		true
	);
	return wd_chat_payload( $pid, $uid, $role, max( 0, (int) $r->get_param( 'after' ) ) );
}

/**
 * The customer pays a payment link on the site. The seller's share goes straight to them.
 *
 * @param WP_REST_Request $r Request.
 * @return array|WP_Error
 */
function wd_rest_chat_pay( WP_REST_Request $r ) {
	global $wpdb;
	$o = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . wd_t( 'chats' ) . ' WHERE id = %d', (int) $r->get_param( 'offer' ) ) ); // phpcs:ignore WordPress.DB
	if ( ! $o || ! $o->offer_status || (int) $o->user_id !== get_current_user_id() ) {
		return new WP_Error( 'gone', __( 'This payment link is for another customer.', 'wwmd' ), array( 'status' => 403 ) );
	}
	if ( 'open' !== $o->offer_status ) {
		return new WP_Error( 'closed', 'paid' === $o->offer_status ? __( 'This payment link is already paid.', 'wwmd' ) : __( 'This payment link is no longer open. Ask the seller for a new one in the chat.', 'wwmd' ), array( 'status' => 400 ) );
	}
	if ( wd_offer_is_invoice( $o ) ) {
		return new WP_Error( 'invoice', __( 'Pay this invoice the way it says, then the seller marks it paid.', 'wwmd' ), array( 'status' => 400 ) );
	}
	$lid = (int) $o->listing_id;
	if ( 'publish' !== get_post_status( $lid ) || ! wd_is_partner( (int) $o->partner_id ) ) {
		return new WP_Error( 'paused', __( 'This listing is not taking bookings right now.', 'wwmd' ), array( 'status' => 400 ) );
	}
	if ( $o->offer_start < wp_date( 'Y-m-d' ) ) {
		return new WP_Error( 'date', __( 'The date on this payment link has passed. Ask the seller for a new one.', 'wwmd' ), array( 'status' => 400 ) );
	}
	if ( ! wd_rate_ok( 'book', 12, 600 ) ) {
		return wd_slow_down();
	}
	$who = wd_booking_who( $r );
	if ( is_wp_error( $who ) ) {
		return $who;
	}
	return wd_booking_create(
		array(
			'listing' => $lid,
			'name'    => $who[0],
			'phone'   => $who[1],
			'start'   => $o->offer_start,
			'end'     => $o->offer_end,
			'qty'     => 1,
			'units'   => 1,
			'amount'  => (int) $o->offer_amount,
			/* translators: 1 code 2 cost lines */
			'note'    => sprintf( __( 'Payment link #%1$s: %2$s', 'wwmd' ), strtoupper( $o->offer_code ), implode( '; ', array_map( static function ( $it ) { return $it['t'] . ' ' . wd_money( $it['a'] ); }, wd_offer_items( $o ) ) ) ),
			'offer'   => (int) $o->id,
		)
	);
}

/**
 * The seller marks an invoice paid (money they received directly). It becomes a receipt & the customer
 * can review the seller. No commission: we never touched the money.
 *
 * @param WP_REST_Request $r Request.
 * @return array|WP_Error
 */
function wd_rest_chat_paid( WP_REST_Request $r ) {
	global $wpdb;
	$t = wd_t( 'chats' );
	$o = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $t WHERE id = %d AND offer_status = 'open'", (int) $r->get_param( 'offer' ) ) ); // phpcs:ignore WordPress.DB
	if ( ! $o || ! wd_offer_is_invoice( $o ) || ! in_array( wd_chat_role( (int) $o->partner_id, (int) $o->user_id ), array( 'partner', 'hq' ), true ) ) {
		return new WP_Error( 'gone', __( 'This invoice is already paid or cancelled.', 'wwmd' ), array( 'status' => 400 ) );
	}
	$cust = get_userdata( (int) $o->user_id );
	$now  = current_time( 'mysql', true );
	$wpdb->insert(
		wd_t( 'bookings' ),
		array(
			'listing_id'  => (int) $o->listing_id,
			'partner_id'  => (int) $o->partner_id,
			'user_id'     => (int) $o->user_id,
			'name'        => $cust ? $cust->display_name : '',
			'email'       => $cust ? $cust->user_email : '',
			'phone'       => $cust ? (string) get_user_meta( $cust->ID, 'wd_phone', true ) : '',
			'start_date'  => $o->offer_start,
			'end_date'    => $o->offer_end,
			'amount'      => (int) $o->offer_amount,
			'fee'         => 0,
			'net'         => 0,
			'currency'    => 'NGN',
			'reference'   => 'INV-' . strtoupper( $o->offer_code ),
			'status'      => 'direct',
			/* translators: 1 code 2 cost lines */
			'note'        => sprintf( __( 'Invoice #%1$s, paid to the seller directly: %2$s', 'wwmd' ), strtoupper( $o->offer_code ), implode( '; ', array_map( static function ( $it ) { return $it['t'] . ' ' . wd_money( $it['a'] ); }, wd_offer_items( $o ) ) ) ),
			'log'         => wp_date( 'j M Y H:i' ) . ': marked paid by the seller',
			'created'     => $now,
			'paid_at'     => $now,
			'released_at' => $now,
		)
	);
	$wpdb->update(
		$t,
		array(
			'offer_status' => 'paid',
			'booking_id'   => (int) $wpdb->insert_id,
		),
		array( 'id' => (int) $o->id )
	);
	/* translators: 1 code 2 link */
	wd_chat_notify( (int) $o->partner_id, (int) $o->user_id, 'partner', sprintf( __( 'Invoice #%1$s is marked paid. Thank you! Your receipt: %2$s', 'wwmd' ), strtoupper( $o->offer_code ), wd_offer_url( $o ) ), (int) $o->listing_id, true );
	return array(
		'ok'    => true,
		'state' => wd_offer_states()['paid'],
	);
}

/**
 * The seller cancels an unpaid payment link.
 *
 * @param WP_REST_Request $r Request.
 * @return array|WP_Error
 */
function wd_rest_chat_cancel( WP_REST_Request $r ) {
	global $wpdb;
	$o = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . wd_t( 'chats' ) . " WHERE id = %d AND offer_status = 'open'", (int) $r->get_param( 'offer' ) ) ); // phpcs:ignore WordPress.DB
	if ( ! $o || 'partner' !== wd_chat_role( (int) $o->partner_id, (int) $o->user_id ) ) {
		return new WP_Error( 'gone', __( 'This is already paid or cancelled.', 'wwmd' ), array( 'status' => 400 ) );
	}
	$wpdb->update( wd_t( 'chats' ), array( 'offer_status' => 'closed' ), array( 'id' => (int) $o->id ) );
	return array(
		'ok'    => true,
		'state' => wd_offer_states()['closed'],
	);
}

/* /pay/{code}/: a payment link's own page (an invoice with a pay button). */
add_action(
	'init',
	static function () {
		add_rewrite_rule( '^pay/([A-Za-z0-9]{6,16})/?$', 'index.php?wd_pay=$matches[1]', 'top' );
	}
);
add_filter(
	'query_vars',
	static function ( $v ) {
		$v[] = 'wd_pay';
		return $v;
	}
);
add_action(
	'template_redirect',
	static function () {
		$code = get_query_var( 'wd_pay' );
		if ( ! $code ) {
			return;
		}
		$o = wd_offer_by_code( $code );
		if ( ! $o ) {
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
			static function () use ( $o ) {
				/* translators: %s code */
				return sprintf( 'paid' === $o->offer_status ? __( 'Receipt #%s', 'wwmd' ) : ( wd_offer_is_invoice( $o ) ? __( 'Invoice #%s', 'wwmd' ) : __( 'Payment link #%s', 'wwmd' ) ), strtoupper( $o->offer_code ) ) . ' · ' . get_bloginfo( 'name' );
			}
		);
		add_filter(
			'body_class',
			static function ( $c ) {
				return array_merge( array_diff( $c, array( 'home', 'blog' ) ), array( 'pay-link' ) );
			}
		);
		if ( ! defined( 'WD_NO_FX' ) ) {
			define( 'WD_NO_FX', true ); // No confetti on invoices & receipts.
		}
		get_header();
		get_template_part( 'template-parts/pay-link', null, array( 'offer' => $o ) );
		get_footer();
		exit;
	}
);

/* Once an offer's booking is paid, the offer shows as paid in the chat. */
add_action(
	'wd_paid',
	static function ( $tx ) {
		global $wpdb;
		if ( 'booking' === $tx->kind && 0 === strpos( $tx->item, 'bk' ) ) {
			$wpdb->query( $wpdb->prepare( 'UPDATE ' . wd_t( 'chats' ) . " SET offer_status = 'paid' WHERE booking_id = %d AND offer_status = 'open'", (int) substr( $tx->item, 2 ) ) ); // phpcs:ignore WordPress.DB
		}
	},
	20
);

/**
 * A conversation list (for dashboards & HQ).
 *
 * @param object[] $threads From wd_chat_threads().
 * @param string   $by      partner|user|hq: who is looking.
 * @param callable $link    Returns the URL for a thread.
 */
function wd_chat_list( $threads, $by, $link ) {
	echo '<ul class="chat-list">';
	foreach ( $threads as $t ) {
		$p    = wd_partner( (int) $t->partner_id );
		$u    = get_userdata( (int) $t->user_id );
		$name = 'user' === $by ? ( $p ? $p['business'] : '' ) : ( $u ? $u->display_name : '' );
		if ( 'hq' === $by ) {
			$name = ( $u ? $u->display_name : '?' ) . ' → ' . ( $p ? $p['business'] : '?' );
		}
		$m    = $t->msg;
		$warn = false;
		if ( 'hq' === $by ) {
			global $wpdb;
			foreach ( (array) $wpdb->get_col( $wpdb->prepare( 'SELECT body FROM ' . wd_t( 'chats' ) . ' WHERE partner_id = %d AND user_id = %d ORDER BY id DESC LIMIT 40', $t->partner_id, $t->user_id ) ) as $body ) { // phpcs:ignore WordPress.DB
				if ( wd_chat_risky( $body ) ) {
					$warn = true;
					break;
				}
			}
		}
		$text = $m ? ( $m->offer_status ? sprintf( /* translators: %s amount */ __( 'Payment link: %s', 'wwmd' ), wd_money( $m->offer_amount ) ) : wp_trim_words( $m->body, 14, '…' ) ) : '';
		$pic  = 'user' === $by && $p ? wd_seller_ring( (int) $t->partner_id, 48 ) : '<span class="ig-ring ig-ring--plain" style="--s:48px"><span class="ig-ring__pic">' . wd_avatar( $name ) . '</span></span>';
		printf(
			'<li><a class="chat-list__row%1$s" href="%2$s"><span class="chat-list__av">%3$s</span><span class="chat-list__main"><b>%4$s</b><small>%5$s</small></span><span class="chat-list__side"><time>%6$s</time>%7$s</span></a></li>',
			( (int) $t->unread ? ' is-unread' : '' ) . ( $warn ? ' is-risky' : '' ),
			esc_url( $link( $t ) ),
			$pic, // phpcs:ignore WordPress.Security.EscapeOutput
			esc_html( $name ),
			esc_html( wd_chat_prefix( $m, $by ) . $text ),
			esc_html( $m ? wd_chat_when( $m->created ) : '' ),
			(int) $t->unread ? '<span class="chat-list__n">' . (int) $t->unread . '</span>' : ''
		);
	}
	echo '</ul>';
}

/**
 * "You: ", "Our team: " or "Seller: " before the last message in a conversation list.
 *
 * @param object|null $m  Last message.
 * @param string      $by Who is looking: partner|user|hq.
 * @return string
 */
function wd_chat_prefix( $m, $by ) {
	if ( ! $m ) {
		return '';
	}
	if ( $m->sender === $by ) {
		return __( 'You: ', 'wwmd' );
	}
	if ( 'hq' === $m->sender ) {
		return __( 'Our team: ', 'wwmd' );
	}
	return ( 'hq' === $by && 'partner' === $m->sender ) ? __( 'Seller: ', 'wwmd' ) : '';
}
