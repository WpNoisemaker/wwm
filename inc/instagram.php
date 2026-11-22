<?php
/**
 * Instagram: couples either paste post links (shown with Instagram's own embed) or connect an Instagram
 * professional account so the page shows their latest posts & the likes & comments count toward the Buzz
 * score. Connecting needs a Meta app (Instagram API with Instagram Login) whose ID & secret go in HQ → Settings.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether connecting is possible.
 *
 * @return bool
 */
function wd_ig_ready() {
	return wd_opt( 'ig_app_id' ) && wd_opt( 'ig_app_secret' );
}

/**
 * Redirect URI registered in the Meta app.
 *
 * @return string
 */
function wd_ig_redirect() {
	return add_query_arg( 'wd_ig', 'callback', home_url( '/' ) );
}

/**
 * "Connect Instagram" URL for a wedding.
 *
 * @param int $id Wedding.
 * @return string
 */
function wd_ig_connect_url( $id ) {
	$state = $id . '.' . wp_create_nonce( 'wd_ig_' . $id );
	return add_query_arg(
		array(
			'client_id'     => wd_opt( 'ig_app_id' ),
			'redirect_uri'  => rawurlencode( wd_ig_redirect() ),
			'response_type' => 'code',
			'scope'         => 'instagram_business_basic',
			'state'         => rawurlencode( $state ),
		),
		'https://www.instagram.com/oauth/authorize'
	);
}

add_action(
	'template_redirect',
	static function () {
		// phpcs:disable WordPress.Security.NonceVerification
		if ( empty( $_GET['wd_ig'] ) || 'callback' !== $_GET['wd_ig'] ) {
			return;
		}
		$state = isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : '';
		$code  = isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : '';
		// phpcs:enable
		list( $id, $nonce ) = array_pad( explode( '.', $state, 2 ), 2, '' );
		$id = (int) $id;
		if ( ! $id || ! wd_owns( $id ) || ! wp_verify_nonce( $nonce, 'wd_ig_' . $id ) || ! $code || ! wd_ig_ready() ) {
			wp_safe_redirect( wd_account_url( 'page', array( 'wdn' => 'denied' ) ) );
			exit;
		}
		$res  = wp_remote_post(
			'https://api.instagram.com/oauth/access_token',
			array(
				'timeout' => 20,
				'body'    => array(
					'client_id'     => wd_opt( 'ig_app_id' ),
					'client_secret' => wd_opt( 'ig_app_secret' ),
					'grant_type'    => 'authorization_code',
					'redirect_uri'  => wd_ig_redirect(),
					'code'          => $code,
				),
			)
		);
		$body = is_wp_error( $res ) ? array() : (array) json_decode( wp_remote_retrieve_body( $res ), true );
		if ( ! empty( $body['access_token'] ) ) {
			$long  = wp_remote_get( add_query_arg( array( 'grant_type' => 'ig_exchange_token', 'client_secret' => wd_opt( 'ig_app_secret' ), 'access_token' => $body['access_token'] ), 'https://graph.instagram.com/access_token' ), array( 'timeout' => 20 ) );
			$lbody = is_wp_error( $long ) ? array() : (array) json_decode( wp_remote_retrieve_body( $long ), true );
			$token = ! empty( $lbody['access_token'] ) ? $lbody['access_token'] : $body['access_token'];
			update_post_meta( $id, '_wd_ig_token', $token );
			update_post_meta( $id, '_wd_ig_token_time', time() );
			wd_ig_fetch( $id );
		}
		wp_safe_redirect( wd_account_url( 'page', array( 'wid' => $id, 'part' => 'instagram', 'wdn' => 'saved' ) ) );
		exit;
	}
);

/**
 * Fetch latest posts & engagement for a connected wedding.
 *
 * @param int $id Wedding.
 */
function wd_ig_fetch( $id ) {
	$token = get_post_meta( $id, '_wd_ig_token', true );
	if ( ! $token ) {
		return;
	}
	// Long-lived tokens last 60 days; refresh after 30.
	if ( time() - (int) get_post_meta( $id, '_wd_ig_token_time', true ) > 30 * DAY_IN_SECONDS ) {
		$r = wp_remote_get( add_query_arg( array( 'grant_type' => 'ig_refresh_token', 'access_token' => $token ), 'https://graph.instagram.com/refresh_access_token' ), array( 'timeout' => 20 ) );
		$b = is_wp_error( $r ) ? array() : (array) json_decode( wp_remote_retrieve_body( $r ), true );
		if ( ! empty( $b['access_token'] ) ) {
			$token = $b['access_token'];
			update_post_meta( $id, '_wd_ig_token', $token );
			update_post_meta( $id, '_wd_ig_token_time', time() );
		}
	}
	$r = wp_remote_get(
		add_query_arg(
			array(
				'fields'       => 'id,caption,media_type,media_url,thumbnail_url,permalink,timestamp,like_count,comments_count',
				'limit'        => 24,
				'access_token' => $token,
			),
			'https://graph.instagram.com/me/media'
		),
		array( 'timeout' => 20 )
	);
	$b = is_wp_error( $r ) ? array() : (array) json_decode( wp_remote_retrieve_body( $r ), true );
	if ( empty( $b['data'] ) ) {
		return;
	}
	$media = array();
	$eng   = 0;
	$week  = 0;
	$cut   = time() - 7 * DAY_IN_SECONDS;
	foreach ( $b['data'] as $m ) {
		$e    = (int) ( $m['like_count'] ?? 0 ) + (int) ( $m['comments_count'] ?? 0 );
		$eng += $e;
		if ( strtotime( $m['timestamp'] ?? '' ) >= $cut ) {
			$week += $e;
		}
		$media[] = array(
			'img'  => esc_url_raw( 'VIDEO' === ( $m['media_type'] ?? '' ) ? ( $m['thumbnail_url'] ?? '' ) : ( $m['media_url'] ?? '' ) ),
			'url'  => esc_url_raw( $m['permalink'] ?? '' ),
			'cap'  => wp_trim_words( sanitize_text_field( $m['caption'] ?? '' ), 18, '…' ),
			'eng'  => $e,
		);
	}
	update_post_meta( $id, '_wd_ig_media', array_slice( $media, 0, 12 ) );
	update_post_meta( $id, '_wd_ig_eng', $eng );
	update_post_meta( $id, '_wd_ig_eng_week', $week );
	update_post_meta( $id, '_wd_ig_source', 'api' );
	wd_refresh( $id );
}

add_action(
	'wd_hourly',
	static function () {
		if ( (int) get_option( 'wd_ig_last' ) > time() - 6 * HOUR_IN_SECONDS ) {
			return;
		}
		update_option( 'wd_ig_last', time() );
		$ids = get_posts(
			array(
				'post_type'      => 'wd_wedding',
				'posts_per_page' => 200,
				'fields'         => 'ids',
				'meta_key'       => '_wd_ig_token', // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
		foreach ( $ids as $id ) {
			wd_ig_fetch( $id );
		}
	}
);

/**
 * Normalise a pasted Instagram post link.
 *
 * @param string $url URL.
 * @return string Empty when not an Instagram post/reel link.
 */
function wd_ig_post_url( $url ) {
	if ( preg_match( '#instagram\.com/(?:[A-Za-z0-9_.]+/)?(p|reel|tv)/([A-Za-z0-9_-]+)#', $url, $m ) ) {
		return 'https://www.instagram.com/' . $m[1] . '/' . $m[2] . '/';
	}
	return '';
}
