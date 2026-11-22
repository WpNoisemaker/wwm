<?php
/**
 * Front-end sign in, sign up, forgotten password & reset. wp-login.php is only used for log out.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'login_url',
	static function ( $url, $redirect ) {
		return wd_url( 'sign-in', $redirect ? array( 'redirect_to' => rawurlencode( $redirect ) ) : array() );
	},
	10,
	2
);
add_filter(
	'register_url',
	static function () {
		return wd_url( 'sign-in', array( 'tab' => 'join' ) );
	}
);
add_filter(
	'lostpassword_url',
	static function () {
		return wd_url( 'sign-in', array( 'tab' => 'forgot' ) );
	}
);
add_filter(
	'retrieve_password_message',
	static function ( $message, $key, $login ) {
		$link = wd_url(
			'sign-in',
			array(
				'tab'   => 'reset',
				'key'   => $key,
				'login' => rawurlencode( $login ),
			)
		);
		/* translators: 1: site name 2: link */
		return sprintf( __( "Someone asked to reset the password for your %1\$s account.\n\nIf it was you, open this link to choose a new password:\n%2\$s\n\nIf it was not you, ignore this email.", 'wwmd' ), get_bloginfo( 'name' ), $link );
	},
	10,
	3
);

/* Send people who open wp-login.php to the front-end page (log out still works). */
add_action(
	'login_init',
	static function () {
		// phpcs:ignore WordPress.Security.NonceVerification
		$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : 'login';
		if ( in_array( $action, array( 'logout', 'postpass', 'confirmaction' ), true ) || ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] ) ) {
			return;
		}
		$map = array(
			'register'     => 'join',
			'lostpassword' => 'forgot',
			'rp'           => 'reset',
			'resetpass'    => 'reset',
		);
		$args = isset( $map[ $action ] ) ? array( 'tab' => $map[ $action ] ) : array();
		wp_safe_redirect( wd_url( 'sign-in', $args ) );
		exit;
	}
);

add_filter(
	'logout_redirect',
	static function () {
		return home_url( '/' );
	}
);

/**
 * Handle the sign-in page forms.
 */
function wd_auth_handle() {
	if ( empty( $_POST['wd_auth'] ) ) {
		return;
	}
	$form = sanitize_key( wp_unslash( $_POST['wd_auth'] ) );
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'wd_auth_' . $form ) ) {
		$GLOBALS['wd_auth_error'] = __( 'This form expired. Please try again.', 'wwmd' );
		return;
	}
	$p        = wp_unslash( $_POST );
	$redirect = ! empty( $p['redirect_to'] ) ? wp_validate_redirect( esc_url_raw( $p['redirect_to'] ), wd_account_url() ) : wd_account_url();

	if ( 'login' === $form ) {
		$login = sanitize_text_field( $p['log'] ?? '' );
		if ( is_email( $login ) ) {
			$u = get_user_by( 'email', $login );
			if ( $u ) {
				$login = $u->user_login;
			}
		}
		$user = wp_signon(
			array(
				'user_login'    => $login,
				'user_password' => $p['pwd'] ?? '',
				'remember'      => ! empty( $p['remember'] ),
			),
			is_ssl()
		);
		if ( is_wp_error( $user ) ) {
			$GLOBALS['wd_auth_error'] = __( 'That email & password do not match. Try again or reset your password.', 'wwmd' );
			return;
		}
		if ( user_can( $user, 'manage_options' ) && wd_account_url() === $redirect ) {
			$redirect = wd_url( 'hq' );
		}
		wp_safe_redirect( $redirect );
		exit;
	}

	if ( 'join' === $form ) {
		$email = sanitize_email( $p['email'] ?? '' );
		$first = sanitize_text_field( $p['first_name'] ?? '' );
		$last  = sanitize_text_field( $p['last_name'] ?? '' );
		$phone = sanitize_text_field( $p['phone'] ?? '' );
		$pass  = (string) ( $p['pwd'] ?? '' );
		$as    = sanitize_key( $p['as'] ?? 'couple' );
		$as    = in_array( $as, array( 'couple', 'user', 'seller' ), true ) ? $as : 'couple';
		if ( ! $first || ! is_email( $email ) || strlen( $pass ) < 8 ) {
			$GLOBALS['wd_auth_error'] = __( 'Add your first name, a valid email & a password of at least 8 characters.', 'wwmd' );
			return;
		}
		if ( email_exists( $email ) ) {
			$GLOBALS['wd_auth_error'] = __( 'There is already an account with that email. Sign in instead.', 'wwmd' );
			return;
		}
		$login = sanitize_user( current( explode( '@', $email ) ), true );
		$base  = $login ? $login : 'couple';
		$i     = 1;
		while ( username_exists( $login ) || ! $login ) {
			$login = $base . ( ++$i );
		}
		$uid = wp_insert_user(
			array(
				'user_login'   => $login,
				'user_email'   => $email,
				'user_pass'    => $pass,
				'first_name'   => $first,
				'last_name'    => $last,
				'display_name' => trim( $first . ' ' . $last ),
				'role'         => 'couple' === $as ? 'couple' : 'member',
			)
		);
		if ( is_wp_error( $uid ) ) {
			$GLOBALS['wd_auth_error'] = $uid->get_error_message();
			return;
		}
		update_user_meta( $uid, 'wd_phone', $phone );
		update_user_meta( $uid, 'wd_joined_as', $as );
		wp_set_current_user( $uid );
		wp_set_auth_cookie( $uid, true, is_ssl() );
		$to   = ! empty( $p['redirect_to'] ) ? wp_validate_redirect( esc_url_raw( $p['redirect_to'] ), '' ) : '';
		$land = array(
			'couple' => wd_account_url( 'new', array( 'wdn' => 'welcome' ) ),
			'user'   => wd_account_url( 'services', array( 'wdn' => 'welcome_user' ) ),
			'seller' => wd_account_url( 'partner', array( 'wdn' => 'welcome_seller' ) ),
		);
		wp_safe_redirect( $to ? add_query_arg( 'wdn', 'couple' === $as ? 'welcome' : 'welcome_user', $to ) : $land[ $as ] );
		exit;
	}

	if ( 'forgot' === $form ) {
		$login = sanitize_text_field( $p['log'] ?? '' );
		if ( $login ) {
			retrieve_password( $login );
		}
		// Same message either way, so emails cannot be checked here.
		$GLOBALS['wd_auth_ok'] = __( 'If that email has an account, a reset link is on its way. Check your inbox & spam folder.', 'wwmd' );
		return;
	}

	if ( 'reset' === $form ) {
		$user = check_password_reset_key( sanitize_text_field( $p['key'] ?? '' ), sanitize_user( $p['login'] ?? '' ) );
		if ( is_wp_error( $user ) ) {
			$GLOBALS['wd_auth_error'] = __( 'This reset link has expired. Ask for a new one.', 'wwmd' );
			return;
		}
		$pass = (string) ( $p['pwd'] ?? '' );
		if ( strlen( $pass ) < 8 ) {
			$GLOBALS['wd_auth_error'] = __( 'Use at least 8 characters.', 'wwmd' );
			return;
		}
		reset_password( $user, $pass );
		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID, true, is_ssl() );
		wp_safe_redirect( wd_account_url( '', array( 'wdn' => 'password' ) ) );
		exit;
	}
}
add_action( 'template_redirect', 'wd_auth_handle', 5 );

/* Signed-in people skip the sign-in page; signed-out people are sent to it from the account & HQ. */
add_action(
	'template_redirect',
	static function () {
		if ( is_page_template( 'page-templates/sign-in.php' ) && is_user_logged_in() && empty( $_GET['tab'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			wp_safe_redirect( current_user_can( 'manage_options' ) ? wd_url( 'hq' ) : wd_account_url() );
			exit;
		}
		if ( is_page_template( array( 'page-templates/account.php', 'page-templates/hq.php' ) ) && ! is_user_logged_in() ) {
			$req = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( home_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) : '';
			wp_safe_redirect( wd_url( 'sign-in', array( 'redirect_to' => rawurlencode( $req ) ) ) );
			exit;
		}
		if ( is_page_template( 'page-templates/hq.php' ) && ! current_user_can( 'manage_options' ) ) {
			wp_safe_redirect( wd_account_url( '', array( 'wdn' => 'denied' ) ) );
			exit;
		}
		if ( is_page_template( array( 'page-templates/account.php', 'page-templates/hq.php', 'page-templates/sign-in.php' ) ) ) {
			nocache_headers();
		}
	},
	6
);
