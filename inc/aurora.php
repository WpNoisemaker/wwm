<?php
/**
 * Aurora: the design layer, the light/dark switch, the notification channels & the safe upgrade.
 *
 * Everything here is additive. Nothing in this file writes over a setting, a user, a page or a
 * post that the live site already has — see wd_aurora_upgrade() at the bottom.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

define( 'WD_AURORA', '2.0.0' );

/* -------------------------------------------------------------------------
 * Assets.
 * ---------------------------------------------------------------------- */

/**
 * Aurora loads after every theme stylesheet, so it restyles main.css, app.css & wedding.css
 * without any of them being rewritten. The WP dashboard is never touched.
 */
add_action(
	'wp_enqueue_scripts',
	static function () {
		$u = get_template_directory_uri() . '/assets/';
		wp_enqueue_style( 'wd-aurora', $u . 'css/aurora.css', array( 'wd-main' ), WD_VER );
		if ( wp_style_is( 'wd-app', 'enqueued' ) ) {
			wp_enqueue_style( 'wd-aurora-app', $u . 'css/aurora-app.css', array( 'wd-aurora', 'wd-app' ), WD_VER );
		}
		if ( wp_style_is( 'wd-wedding', 'enqueued' ) ) {
			wp_enqueue_style( 'wd-aurora-wedding', $u . 'css/aurora-wedding.css', array( 'wd-aurora', 'wd-wedding' ), WD_VER );
		}
		wp_enqueue_script( 'wd-aurora', $u . 'js/aurora.js', array( 'wd-main' ), WD_VER, true );
		if ( is_post_type_archive( 'wd_talk' ) ) {
			wp_enqueue_script( 'wd-talk-deck', $u . 'js/talk-deck.js', array( 'wd-aurora' ), WD_VER, true );
		}
	},
	20
);

/**
 * A body class for the settings that change how the whole site is painted, so CSS can answer them
 * without any inline style: wd-flat turns the frosted glass off site-wide.
 *
 * @param array $c Classes.
 * @return array
 */
function wd_aurora_body_class( $c ) {
	if ( ! wd_opt( 'glass' ) ) {
		$c[] = 'wd-flat';
	}
	return $c;
}
add_filter( 'body_class', 'wd_aurora_body_class' );

/* -------------------------------------------------------------------------
 * Light & dark.
 * ---------------------------------------------------------------------- */

/**
 * How the site picks its colour scheme: auto (follow the device), light, or dark.
 * A visitor's own choice lives in their browser; the site-wide default is an HQ setting.
 *
 * @return string auto|light|dark
 */
function wd_theme_default() {
	$m = (string) wd_opt( 'theme_mode' );
	return in_array( $m, array( 'auto', 'light', 'dark' ), true ) ? $m : 'auto';
}

/**
 * Set data-theme on <html> before the first paint, so a dark-mode visitor never sees a white flash.
 * The script is tiny, inline & runs in <head>; it reads the visitor's saved choice, falls back to
 * the site default, and then to the device.
 */
add_action(
	'wp_head',
	static function () {
		$d = wd_theme_default();
		?>
<script id="wd-theme-boot">(function(){try{var d=<?php echo wp_json_encode( $d ); ?>,s=localStorage.getItem('wd-theme'),m=(s==='light'||s==='dark'||s==='auto')?s:d;document.documentElement.setAttribute('data-theme',m==='auto'?'':m);document.documentElement.dataset.themeMode=m;}catch(e){}})();</script>
		<?php
	},
	1
);

/**
 * The three-way light / auto / dark switch. Drop it in the header, the menu sheet & account settings.
 *
 * @param string $class Extra classes.
 * @return string HTML.
 */
function wd_theme_switch( $class = '' ) {
	$opts = array(
		'light' => array( __( 'Light', 'wwmd' ), 'sun' ),
		'auto'  => array( __( 'Match my device', 'wwmd' ), 'spark' ),
		'dark'  => array( __( 'Dark', 'wwmd' ), 'moon' ),
	);
	$o = '<div class="theme-switch ' . esc_attr( $class ) . '" role="group" aria-label="' . esc_attr__( 'Appearance', 'wwmd' ) . '" data-theme-switch>';
	foreach ( $opts as $k => $v ) {
		$o .= '<button type="button" data-theme-set="' . esc_attr( $k ) . '" aria-pressed="false" title="' . esc_attr( $v[0] ) . '">'
			. wd_icon( $v[1], 17 )
			. '<span class="screen-reader-text">' . esc_html( $v[0] ) . '</span></button>';
	}
	return $o . '</div>';
}

/* -------------------------------------------------------------------------
 * Notification channels: email always; WhatsApp & SMS only once HQ sets them up.
 * ---------------------------------------------------------------------- */

/**
 * The channels a site can send on, and what each needs before it may be switched on.
 *
 * @return array
 */
function wd_notify_channels() {
	return array(
		'email'    => array(
			'label'  => __( 'Email', 'wwmd' ),
			'always' => true,
			'needs'  => array(),
		),
		'whatsapp' => array(
			'label'  => __( 'WhatsApp', 'wwmd' ),
			'always' => false,
			'needs'  => array( 'wa_phone_id', 'wa_token' ),
			'note'   => __( 'Needs a WhatsApp Business account, a phone number ID and a permanent token. Message templates must be approved by Meta before anything can be sent.', 'wwmd' ),
		),
		'sms'      => array(
			'label'  => __( 'SMS', 'wwmd' ),
			'always' => false,
			'needs'  => array( 'sms_sender', 'sms_key' ),
			'note'   => __( 'Needs an SMS gateway account, an approved sender ID and an API key.', 'wwmd' ),
		),
	);
}

/**
 * Is a channel live? A channel is live only when HQ has switched it on AND filled in everything
 * it needs. Until then nothing is sent on it and nobody is offered it — which is why WhatsApp and
 * SMS are silent on a fresh install and on every site that has not set them up.
 *
 * @param string $channel email|whatsapp|sms
 * @return bool
 */
function wd_notify_on( $channel ) {
	$all = wd_notify_channels();
	if ( ! isset( $all[ $channel ] ) ) {
		return false;
	}
	if ( ! empty( $all[ $channel ]['always'] ) ) {
		return true;
	}
	if ( ! wd_opt( 'notify_' . $channel ) ) {
		return false;
	}
	foreach ( $all[ $channel ]['needs'] as $key ) {
		if ( '' === trim( (string) wd_opt( $key ) ) ) {
			return false;
		}
	}
	return true;
}

/**
 * Does this site offer the visitor any choice of channel at all? When only email is live there is
 * no choice to show, so no opt-in appears anywhere on the site.
 *
 * @return bool
 */
function wd_notify_has_choice() {
	return wd_notify_on( 'whatsapp' ) || wd_notify_on( 'sms' );
}

/**
 * What a person has asked to receive. Email is always on. A channel that is not live is never
 * returned, even if the person once opted into it, so switching a channel off really stops it.
 *
 * @param int $uid User.
 * @return array Channel keys.
 */
function wd_notify_prefs( $uid ) {
	$out  = array( 'email' );
	$want = (array) get_user_meta( $uid, 'wd_notify', true );
	foreach ( array( 'whatsapp', 'sms' ) as $c ) {
		if ( wd_notify_on( $c ) && in_array( $c, $want, true ) ) {
			$out[] = $c;
		}
	}
	return $out;
}

/**
 * Send a message on every channel this person has, and that this site actually has set up.
 * Email goes through wp_mail as before; WhatsApp & SMS are handed to whatever HQ configured,
 * and a site with nothing configured simply sends the email and stops.
 *
 * @param int    $uid     Who to tell.
 * @param string $subject Subject (email only).
 * @param string $body    The message.
 * @param array  $a       Optional: 'template' for WhatsApp, 'link', 'always'.
 * @return array Channels actually used.
 */
function wd_notify( $uid, $subject, $body, $a = array() ) {
	$user = get_userdata( $uid );
	if ( ! $user ) {
		return array();
	}
	$sent = array();
	if ( is_email( $user->user_email ) ) {
		wp_mail( $user->user_email, $subject, $body );
		$sent[] = 'email';
	}
	$phone = trim( (string) get_user_meta( $uid, 'wd_phone', true ) );
	if ( '' === $phone ) {
		return $sent;
	}
	foreach ( wd_notify_prefs( $uid ) as $channel ) {
		if ( 'email' === $channel ) {
			continue;
		}
		/**
		 * Hand the message to the gateway HQ configured. Nothing is sent unless something is
		 * hooked here, so an un-set-up site can never leak a message to a phone.
		 *
		 * @param bool   $handled Set true once sent.
		 * @param string $phone   The number.
		 * @param string $body    The message.
		 * @param array  $a       Extras.
		 */
		if ( apply_filters( 'wd_notify_' . $channel, false, $phone, $body, $a ) ) {
			$sent[] = $channel;
		}
	}
	return $sent;
}

/* -------------------------------------------------------------------------
 * A safe upgrade: new version, same site.
 * ---------------------------------------------------------------------- */

/**
 * What a brand-new Aurora install adds to wd_settings. Every one of these is a NEW key: an
 * existing site keeps every value it already had, because wd_settings() merges saved values over
 * defaults and this only ever fills gaps.
 *
 * @param array $d Defaults.
 * @return array
 */
function wd_aurora_defaults( $d ) {
	return array_merge(
		$d,
		array(
			'theme_mode'    => 'auto',
			'glass'         => 1,
			'notify_whatsapp' => 0,
			'notify_sms'      => 0,
			'wa_phone_id'   => '',
			'wa_token'      => '',
			'sms_sender'    => '',
			'sms_key'       => '',
			'talk_mark'     => '',
			'talk_names'    => 1,
		)
	);
}
add_filter( 'wd_defaults', 'wd_aurora_defaults' );

/**
 * Run once per version. It only ever ADDS: it never deletes a post, a user, a page, a menu, an
 * order, a payment or a setting, and it never overwrites a value the site already holds. Upload
 * the new theme over a live installation and every couple, guest, wedding page, listing and
 * payment stays exactly where it was.
 */
function wd_aurora_upgrade() {
	$was = (string) get_option( 'wd_aurora_ver', '' );
	if ( WD_AURORA === $was ) {
		return;
	}

	$saved = get_option( 'wd_settings', array() );
	$saved = is_array( $saved ) ? $saved : array();
	$add   = array();
	foreach ( wd_aurora_defaults( array() ) as $k => $v ) {
		if ( ! array_key_exists( $k, $saved ) ) {
			$add[ $k ] = $v;
		}
	}
	if ( $add ) {
		update_option( 'wd_settings', array_merge( $saved, $add ) );
		wd_settings( true );
	}

	update_option( 'wd_aurora_ver', WD_AURORA );
}
add_action( 'init', 'wd_aurora_upgrade', 5 );

/**
 * wd_defaults() is a plain function, so give it the filter the upgrade above relies on.
 * Wrapped so it stays a no-op if core ever grows its own.
 */
if ( ! function_exists( 'wd_defaults_filtered' ) ) {
	/**
	 * Defaults, with Aurora's new keys merged in.
	 *
	 * @return array
	 */
	function wd_defaults_filtered() {
		return apply_filters( 'wd_defaults', wd_defaults() );
	}
}
