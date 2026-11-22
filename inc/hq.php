<?php
/**
 * HQ: the platform owner's front-end admin (admin-post.php?action=wd_hq).
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_post_wd_hq', 'wd_hq_save' );

/**
 * Redirect back to HQ.
 *
 * @param string $tab  Tab.
 * @param string $note Notice.
 * @param array  $args Args.
 */
function wd_hq_back( $tab, $note = 'saved', $args = array() ) {
	wp_safe_redirect( wd_url( 'hq', array_merge( array( 'tab' => $tab, 'wdn' => $note ), $args ) ) );
	exit;
}

/**
 * HQ save handler.
 */
function wd_hq_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have access to that.', 'wwmd' ) );
	}
	$p  = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification
	$op = isset( $p['op'] ) ? sanitize_key( $p['op'] ) : '';
	if ( ! isset( $p['_wpnonce'] ) || ! wp_verify_nonce( $p['_wpnonce'], 'wd_hq_' . $op ) ) {
		wd_hq_back( '', 'denied' );
	}
	global $wpdb;
	$back = sanitize_key( $p['back'] ?? '' );

	switch ( $op ) {
		case 'settings':
			$s    = wd_settings( true );
			$nums = array( 'pro_price', 'premium_price', 'premium_qr_cards', 'instalments', 'extra_qr_price', 'extra_qr_qty', 'spotlight_price', 'vendor_pro_price', 'seller_premium_price', 'ad_week_price', 'fee_cap', 'payout_min', 'custom_design_price', 'release_days' );
			foreach ( $nums as $k ) {
				if ( isset( $p[ $k ] ) ) {
					$s[ $k ] = max( 0, (int) preg_replace( '/[^\d]/', '', $p[ $k ] ) );
				}
			}
			foreach ( array( 'fee_gift', 'fee_spray' ) as $k ) {
				if ( isset( $p[ $k ] ) ) {
					$s[ $k ] = min( 30, max( 0, (float) $p[ $k ] ) );
				}
			}
			// Appearance & the notification channels.
			if ( isset( $p['theme_mode'] ) && in_array( $p['theme_mode'], array( 'auto', 'light', 'dark' ), true ) ) {
				$s['theme_mode'] = $p['theme_mode'];
			}
			foreach ( array( 'wa_phone_id', 'sms_sender', 'talk_mark' ) as $k ) {
				if ( isset( $p[ $k ] ) ) {
					$s[ $k ] = sanitize_text_field( $p[ $k ] );
				}
			}
			foreach ( array( 'whatsapp', 'email', 'ig_app_id', 'auth_title' ) as $k ) {
				if ( isset( $p[ $k ] ) ) {
					$s[ $k ] = sanitize_text_field( $p[ $k ] );
				}
			}
			if ( isset( $p['auth_text'] ) ) {
				$s['auth_text'] = sanitize_textarea_field( $p['auth_text'] );
			}
			foreach ( array( 'news_feeds', 'news_import_feeds' ) as $k ) {
				if ( isset( $p[ $k ] ) ) {
					$urls = array();
					foreach ( preg_split( '/[\s,]+/', (string) $p[ $k ] ) as $u ) {
						$u = esc_url_raw( trim( $u ), array( 'http', 'https' ) );
						if ( $u ) {
							$urls[] = $u;
						}
					}
					$s[ $k ] = implode( "\n", array_slice( array_unique( $urls ), 0, 10 ) );
				}
			}
			// Secrets: keep the saved value when the field is left blank.
			foreach ( array( 'bachs_secret', 'bachs_webhook_secret', 'ig_app_secret', 'ai_key', 'wa_token', 'sms_key' ) as $k ) {
				if ( ! empty( $p[ $k ] ) ) {
					$s[ $k ] = sanitize_text_field( $p[ $k ] );
				}
				if ( ! empty( $p[ 'clear_' . $k ] ) ) {
					$s[ $k ] = '';
				}
			}
			if ( isset( $p['market_fee'] ) ) {
				$s['market_fee'] = min( 50, max( 0, (float) $p['market_fee'] ) );
			}
			if ( isset( $p['ai_model'] ) && array_key_exists( $p['ai_model'], wd_ai_models() ) ) {
				$s['ai_model'] = $p['ai_model'];
			}
			foreach ( array( 'test_payments', 'moderate_photos', 'moderate_wall', 'moderate_talk', 'ai_on', 'preloader', 'home_fx', 'news_on', 'news_import', 'glass', 'talk_names', 'notify_whatsapp', 'notify_sms' ) as $k ) {
				$s[ $k ] = empty( $p[ $k ] ) ? 0 : 1;
			}
			$all = array_keys( wd_features() );
			foreach ( wd_plan_keys() as $plan ) {
				$s[ 'features_' . $plan ] = array_values( array_intersect( $all, array_map( 'sanitize_key', (array) ( $p[ 'features_' . $plan ] ?? array() ) ) ) );
			}
			foreach ( array_keys( wd_coin_figures() ) as $k ) {
				$s['coins'][ $k ] = isset( $p['coins'][ $k ] ) ? min( 1000, max( 0, (int) $p['coins'][ $k ] ) ) : wd_coin_rate( $k );
			}
			foreach ( array_keys( wd_currencies() ) as $c ) {
				if ( isset( $p['rates'][ $c ] ) && 'NGN' !== $c ) {
					$s['rates'][ $c ] = max( 0.0001, (float) $p['rates'][ $c ] );
				}
			}
			update_option( 'wd_settings', $s );
			wd_settings( true );
			if ( ! empty( $p['news_pull'] ) ) {
				// "Bring in stories now": only the import, so the rest of the settings save stays quick.
				delete_transient( 'wd_news_import_lock' );
				$n = wd_news_import();
				wd_hq_back( 'settings', 'news_pulled', array( 'n' => $n ) );
			}
			do_action( 'wd_hourly' );
			wd_hq_back( 'settings' );
			break;

		case 'ads':
			$s        = wd_settings( true );
			$s['ads'] = array();
			for ( $i = 0; $i < 2; $i++ ) {
				$a = isset( $p['ads'][ $i ] ) ? (array) $p['ads'][ $i ] : array();
				$s['ads'][] = array(
					'on'    => empty( $a['on'] ) ? 0 : 1,
					'title' => sanitize_text_field( $a['title'] ?? '' ),
					'text'  => sanitize_text_field( $a['text'] ?? '' ),
					'url'   => esc_url_raw( $a['url'] ?? '' ),
					'cta'   => sanitize_text_field( $a['cta'] ?? '' ),
					'image' => esc_url_raw( $a['image'] ?? '' ),
					'tone'  => sanitize_key( $a['tone'] ?? 'butter' ),
				);
			}
			update_option( 'wd_settings', $s );
			wd_hq_back( 'ads' );
			break;

		case 'wedding':
			$id = (int) ( $p['id'] ?? 0 );
			if ( 'wd_wedding' !== get_post_type( $id ) ) {
				wd_hq_back( 'weddings', 'denied' );
			}
			$do = sanitize_key( $p['do'] ?? '' );
			if ( 'plan' === $do ) {
				update_post_meta( $id, '_wd_plan', in_array( $p['plan'] ?? '', wd_plan_keys(), true ) ? $p['plan'] : 'free' );
				wd_refresh( $id );
			} elseif ( 'custom' === $do ) {
				$ct = sanitize_key( $p['tpl'] ?? '' );
				if ( $ct && isset( wd_custom_templates()[ $ct ] ) ) {
					update_post_meta( $id, '_wd_custom_tpl', $ct );
					if ( ! get_post_meta( $id, '_wd_custom', true ) ) {
						update_post_meta( $id, '_wd_custom', time() );
					}
				} else {
					delete_post_meta( $id, '_wd_custom_tpl' );
				}
			} elseif ( 'spotlight' === $do ) {
				$days = max( 0, (int) ( $p['days'] ?? 7 ) );
				if ( $days ) {
					update_post_meta( $id, '_wd_spotlight', time() + $days * DAY_IN_SECONDS );
				} else {
					delete_post_meta( $id, '_wd_spotlight' );
				}
			} elseif ( 'ig' === $do ) {
				update_post_meta( $id, '_wd_ig_eng', max( 0, (int) ( $p['ig'] ?? 0 ) ) );
				update_post_meta( $id, '_wd_ig_eng_week', max( 0, (int) ( $p['ig_week'] ?? 0 ) ) );
				update_post_meta( $id, '_wd_ig_source', 'hq' );
			} elseif ( 'hide' === $do ) {
				wp_update_post( array( 'ID' => $id, 'post_status' => 'private' ) );
			} elseif ( 'show' === $do ) {
				wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) );
			} elseif ( 'cards' === $do ) {
				$d                     = wd_get( $id );
				$d['cards']['status']   = sanitize_key( $p['status'] ?? '' );
				$d['cards']['tracking'] = sanitize_text_field( $p['tracking'] ?? '' );
				update_post_meta( $id, '_wd', $d );
			} elseif ( 'delete' === $do ) {
				wd_delete_wedding( $id );
				wd_hq_back( $back ? $back : 'weddings', 'deleted' );
			}
			wd_refresh( $id );
			wd_hq_back( $back ? $back : 'weddings', 'done' );
			break;

		case 'payout':
			$pid = (int) ( $p['id'] ?? 0 );
			$st  = in_array( $p['status'] ?? '', array( 'paid', 'rejected', 'requested' ), true ) ? $p['status'] : 'requested';
			$wpdb->update(
				wd_t( 'payouts' ),
				array(
					'status'  => $st,
					'note'    => sanitize_text_field( $p['note'] ?? '' ),
					'paid_at' => 'paid' === $st ? current_time( 'mysql', true ) : null,
				),
				array( 'id' => $pid )
			);
			wd_hq_back( 'money', 'done' );
			break;

		case 'moderate_talk':
			$do  = sanitize_key( $p['do'] ?? '' );
			$oid = (int) ( $p['oid'] ?? 0 );
			if ( 'reply' === ( $p['type'] ?? '' ) ) {
				$c = get_comment( $oid );
				if ( $c && 'wd_reply' === $c->comment_type ) {
					if ( 'delete' === $do ) {
						wp_delete_comment( $oid, true );
					} else {
						wp_set_comment_status( $oid, 'hide' === $do ? 'hold' : 'approve' );
						if ( 'show' === $do ) {
							delete_comment_meta( $oid, '_wd_reports' );
						}
					}
				}
			} elseif ( 'wd_talk' === get_post_type( $oid ) ) {
				if ( 'delete' === $do ) {
					wp_delete_post( $oid, true );
				} else {
					wp_update_post(
						array(
							'ID'          => $oid,
							'post_status' => 'hide' === $do ? 'pending' : 'publish',
						)
					);
					if ( 'show' === $do ) {
						delete_post_meta( $oid, '_wd_reports' );
					}
				}
			}
			wd_hq_back( 'moderation', 'done', array( 'part' => 'talk' ) );
			break;

		case 'moderate':
			$map = array(
				'wall'   => 'wall',
				'photo'  => 'photos',
				'review' => 'reviews',
			);
			$type = sanitize_key( $p['type'] ?? '' );
			$oid  = (int) ( $p['oid'] ?? 0 );
			if ( isset( $map[ $type ] ) && $oid ) {
				$table = wd_t( $map[ $type ] );
				$row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $oid ) ); // phpcs:ignore WordPress.DB
				$do    = sanitize_key( $p['do'] ?? '' );
				if ( $row ) {
					if ( 'delete' === $do ) {
						$wpdb->delete( $table, array( 'id' => $oid ) );
					} else {
						$wpdb->update( $table, array( 'status' => 'hide' === $do ? 'hidden' : 'approved' ), array( 'id' => $oid ) );
					}
					if ( 'review' === $type ) {
						wd_vendor_recount( (int) $row->vendor_id );
					}
					if ( ! empty( $row->wedding_id ) ) {
						wd_refresh( (int) $row->wedding_id );
					}
				}
			}
			wd_hq_back( 'moderation', 'done' );
			break;

		case 'vendor':
			$vid = (int) ( $p['id'] ?? 0 );
			if ( 'wd_vendor' === get_post_type( $vid ) ) {
				$v = wd_vendor( $vid );
				if ( 'verify' === ( $p['do'] ?? '' ) ) {
					$v['verified'] = empty( $v['verified'] ) ? 1 : 0;
					update_post_meta( $vid, '_wdv', $v );
				} elseif ( 'delete' === ( $p['do'] ?? '' ) ) {
					wp_trash_post( $vid );
				}
			}
			wd_hq_back( 'vendors', 'done' );
			break;

		case 'request':
			$wpdb->update( wd_t( 'requests' ), array( 'status' => sanitize_key( $p['status'] ?? 'new' ) ), array( 'id' => (int) ( $p['id'] ?? 0 ) ) );
			wd_hq_back( 'requests', 'done' );
			break;

		case 'login':
			$uid   = (int) ( $p['id'] ?? 0 );
			$u     = get_userdata( $uid );
			$email = sanitize_email( $p['email'] ?? '' );
			$pass  = (string) ( $p['pass'] ?? '' );
			$owner = $email ? email_exists( $email ) : false;
			if ( ! $u || $uid === get_current_user_id() || ! is_email( $email ) || ( $owner && (int) $owner !== $uid ) || ( '' !== $pass && strlen( $pass ) < 8 ) ) {
				wd_hq_back( 'people', 'login_bad' );
			}
			wp_update_user(
				array(
					'ID'         => $uid,
					'user_email' => $email,
				)
			);
			if ( '' !== $pass ) {
				wp_set_password( $pass, $uid );
			}
			wd_hq_back( 'people', 'login_saved' );
			break;

		case 'role':
			$uid = (int) ( $p['id'] ?? 0 );
			$u   = get_userdata( $uid );
			if ( $u && $uid !== get_current_user_id() ) {
				$u->set_role( in_array( $p['role'] ?? '', array( 'administrator', 'member' ), true ) ? $p['role'] : 'couple' );
			}
			wd_hq_back( 'people', 'done' );
			break;

		case 'menus':
			$locations = (array) get_theme_mod( 'nav_menu_locations', array() );
			$names     = array(
				'primary' => __( 'Header menu', 'wwmd' ),
				'footer'  => __( 'Footer menu', 'wwmd' ),
				'legal'   => __( 'Footer bottom links', 'wwmd' ),
				'tabbar'  => __( 'Phone tab bar', 'wwmd' ),
			);
			foreach ( $names as $loc => $name ) {
				$rows = wd_rows( $p, 'menu_' . $loc, array( 'title', 'url' ), 'tabbar' === $loc ? 4 : 20 );
				$mid  = ! empty( $locations[ $loc ] ) ? (int) $locations[ $loc ] : 0;
				if ( ! $mid || ! wp_get_nav_menu_object( $mid ) ) {
					$obj = wp_get_nav_menu_object( $name );
					$mid = $obj ? $obj->term_id : wp_create_nav_menu( $name );
					if ( is_wp_error( $mid ) ) {
						continue;
					}
					$locations[ $loc ] = $mid;
				}
				foreach ( (array) wp_get_nav_menu_items( $mid, array( 'post_status' => 'any' ) ) as $old ) {
					wp_delete_post( $old->ID, true );
				}
				$i = 0;
				foreach ( $rows as $r ) {
					if ( '' === trim( $r['title'] ) || '' === trim( $r['url'] ) ) {
						continue;
					}
					$url = 0 === strpos( $r['url'], '/' ) ? home_url( $r['url'] ) : esc_url_raw( $r['url'] );
					wp_update_nav_menu_item(
						$mid,
						0,
						array(
							'menu-item-title'    => $r['title'],
							'menu-item-url'      => $url,
							'menu-item-status'   => 'publish',
							'menu-item-type'     => 'custom',
							'menu-item-position' => ++$i,
						)
					);
				}
			}
			set_theme_mod( 'nav_menu_locations', $locations );
			wd_hq_back( 'menus' );
			break;

		case 'samples':
			wd_delete_samples();
			wd_hq_back( '', 'deleted' );
			break;

		case 'recount':
			do_action( 'wd_hourly' );
			wd_hq_back( '', 'done' );
			break;
	}
	wd_hq_back( '' );
}

/**
 * Delete a wedding & everything guests added to it.
 *
 * @param int $id Wedding.
 */
function wd_delete_wedding( $id ) {
	global $wpdb;
	foreach ( array( 'rsvps', 'wall', 'photos', 'quiz', 'views' ) as $t ) {
		$wpdb->delete( wd_t( $t ), array( 'wedding_id' => $id ) );
	}
	$vendors = $wpdb->get_col( $wpdb->prepare( 'SELECT DISTINCT vendor_id FROM ' . wd_t( 'reviews' ) . ' WHERE wedding_id = %d', $id ) ); // phpcs:ignore WordPress.DB
	$wpdb->delete( wd_t( 'reviews' ), array( 'wedding_id' => $id ) );
	foreach ( $vendors as $v ) {
		wd_vendor_recount( (int) $v );
	}
	wp_delete_post( $id, true );
	delete_transient( 'wd_ranks' );
}

/**
 * Remove all sample weddings, vendors & their activity.
 */
function wd_delete_samples() {
	global $wpdb;
	$ids = get_posts(
		array(
			'post_type'      => array( 'wd_wedding', 'wd_vendor', 'wd_listing' ),
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => '_wd_sample', // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	foreach ( $ids as $id ) {
		if ( 'wd_wedding' === get_post_type( $id ) ) {
			foreach ( array( 'tx', 'payouts', 'requests' ) as $t ) {
				$wpdb->delete( wd_t( $t ), array( 'wedding_id' => $id ) );
			}
			wd_delete_wedding( $id );
		} else {
			$wpdb->delete( wd_t( 'reviews' ), array( 'vendor_id' => $id ) );
			wp_delete_post( $id, true );
		}
	}
	foreach ( get_posts( array( 'post_type' => 'attachment', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_wd_sample' ) ) as $att ) { // phpcs:ignore WordPress.DB.SlowDBQuery
		wp_delete_attachment( $att, true );
	}
	$users = get_users( array( 'meta_key' => '_wd_sample', 'fields' => 'ID' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	require_once ABSPATH . 'wp-admin/includes/user.php';
	foreach ( $users as $u ) {
		foreach ( array( 'partner_id', 'user_id' ) as $col ) {
			$wpdb->delete( wd_t( 'chats' ), array( $col => (int) $u ) );
			$wpdb->delete( wd_t( 'previews' ), array( $col => (int) $u ) );
		}
		$wpdb->delete( wd_t( 'bookings' ), array( 'partner_id' => (int) $u ) );
		$wpdb->delete( wd_t( 'bookings' ), array( 'user_id' => (int) $u ) );
		$wpdb->delete(
			wd_t( 'payouts' ),
			array(
				'user_id'    => (int) $u,
				'wedding_id' => 0,
			)
		);
		wp_delete_user( (int) $u );
	}
	update_option( 'wd_samples_removed', 1 );
}

/**
 * Platform totals for HQ.
 *
 * @return array
 */
function wd_platform_totals() {
	global $wpdb;
	$tx  = wd_t( 'tx' );
	$out = array(
		'weddings' => (int) wp_count_posts( 'wd_wedding' )->publish,
		'premium'  => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_wd_plan' AND meta_value = 'premium'" ), // phpcs:ignore WordPress.DB
		'pro'      => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_wd_plan' AND meta_value = 'pro'" ), // phpcs:ignore WordPress.DB
		'society'  => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_wd_plan' AND meta_value = 'society'" ), // phpcs:ignore WordPress.DB
		'couples'  => count( get_users( array( 'role' => 'couple', 'fields' => 'ID' ) ) ),
		'vendors'  => (int) wp_count_posts( 'wd_vendor' )->publish,
		'views'    => (int) $wpdb->get_var( 'SELECT COALESCE(SUM(views),0) FROM ' . wd_t( 'views' ) ), // phpcs:ignore WordPress.DB
		'rsvps'    => (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . wd_t( 'rsvps' ) ), // phpcs:ignore WordPress.DB
		'payouts'  => (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . wd_t( 'payouts' ) . " WHERE status = 'requested' AND wedding_id > 0" ), // phpcs:ignore WordPress.DB
		'market'   => (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . wd_t( 'payouts' ) . " WHERE status = 'requested' AND wedding_id = 0" ) + (int) wp_count_posts( 'wd_listing' )->pending + count( get_users( array( 'meta_key' => 'wd_partner_status', 'meta_value' => 'pending', 'fields' => 'ID' ) ) ) + (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . wd_t( 'bookings' ) . " WHERE status = 'disputed'" ) + (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . wd_t( 'previews' ) . " WHERE status = 'pending'" ), // phpcs:ignore WordPress.DB
		'commission' => (int) $wpdb->get_var( 'SELECT COALESCE(SUM(fee),0) FROM ' . wd_t( 'bookings' ) . " WHERE status = 'released' AND test = 0" ), // phpcs:ignore WordPress.DB
		'requests' => (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . wd_t( 'requests' ) . " WHERE status = 'new'" ), // phpcs:ignore WordPress.DB
		'money'    => array(),
	);
	$rows = $wpdb->get_results( "SELECT kind, currency, SUM(amount) amount, SUM(fee) fee, COUNT(*) n FROM $tx WHERE status = 'paid' GROUP BY kind, currency" ); // phpcs:ignore WordPress.DB
	foreach ( (array) $rows as $r ) {
		$out['money'][] = $r;
	}
	return $out;
}

/**
 * Open an HQ form.
 *
 * @param string $op    Operation.
 * @param string $class Class.
 */
function wd_hq_form( $op, $class = 'inline' ) {
	printf( '<form class="%s" method="post" action="%s">', esc_attr( $class ), esc_url( admin_url( 'admin-post.php' ) ) );
	echo '<input type="hidden" name="action" value="wd_hq"><input type="hidden" name="op" value="' . esc_attr( $op ) . '">';
	wp_nonce_field( 'wd_hq_' . $op );
}
