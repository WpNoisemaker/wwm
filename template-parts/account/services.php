<?php
/**
 * Orders & services: what the customer paid sellers for (payment links & invoices) & other service requests.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
global $wpdb;
$wd_uid  = get_current_user_id();
$wd_bks  = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . wd_t( 'bookings' ) . " WHERE user_id = %d AND status <> 'cancelled' AND ( status <> 'pending' OR created > %s ) ORDER BY id DESC LIMIT 50", $wd_uid, gmdate( 'Y-m-d H:i:s', time() - DAY_IN_SECONDS ) ) ); // phpcs:ignore WordPress.DB
$wd_reqs = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . wd_t( 'requests' ) . " WHERE user_id = %d AND service <> 'custom_design' ORDER BY id DESC LIMIT 30", $wd_uid ) ); // phpcs:ignore WordPress.DB
$wd_svc  = wd_services();
$wd_secs = wd_sectors();
$wd_us   = wd_units();
// phpcs:ignore WordPress.Security.NonceVerification
$wd_cp      = isset( $_GET['chat'] ) ? (int) $_GET['chat'] : 0;
$wd_cpp     = $wd_cp && $wd_cp !== $wd_uid ? wd_partner( $wd_cp ) : array();
$wd_threads = wd_chat_threads( 'user', $wd_uid );
$wd_rvs     = array();
if ( $wd_bks ) {
	foreach ( (array) $wpdb->get_results( 'SELECT * FROM ' . wd_t( 'previews' ) . ' WHERE booking_id IN (' . implode( ',', array_map( 'intval', wp_list_pluck( $wd_bks, 'id' ) ) ) . ')' ) as $wd_rv ) { // phpcs:ignore WordPress.DB
		$wd_rvs[ (int) $wd_rv->booking_id ] = $wd_rv;
	}
}
?>
<div class="app-head"><div><p class="eyebrow"><?php esc_html_e( 'Orders & services', 'wwmd' ); ?></p><h1><?php esc_html_e( 'Your orders', 'wwmd' ); ?></h1></div><a class="btn btn--ink" href="<?php echo esc_url( wd_url( 'services' ) ); ?>"><?php esc_html_e( 'Browse services', 'wwmd' ); ?></a></div>

<?php if ( $wd_cpp ) : ?>
	<div class="panel panel--chat" id="chat">
		<?php
		get_template_part(
			'template-parts/chat',
			null,
			array(
				'partner' => $wd_cp,
				'user'    => $wd_uid,
				'role'    => 'user',
				'back'    => wd_account_url( 'services' ) . '#messages',
				/* translators: %s business */
				'title'   => sprintf( __( 'Chat with %s', 'wwmd' ), $wd_cpp['business'] ),
			)
		);
		?>
		<a class="link-arrow" href="<?php echo esc_url( wd_partner_url( $wd_cp ) ); ?>"><?php esc_html_e( 'See their profile & listings', 'wwmd' ); ?><?php echo wd_icon( 'arrow', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
	</div>
<?php endif; ?>

<?php if ( $wd_threads ) : ?>
	<div class="panel" id="messages">
		<h2><?php esc_html_e( 'Messages', 'wwmd' ); ?></h2>
		<?php
		wd_chat_list(
			$wd_threads,
			'user',
			static function ( $t ) {
				return wd_account_url( 'services', array( 'chat' => (int) $t->partner_id ) ) . '#chat';
			}
		);
		?>
	</div>
<?php endif; ?>

<?php
$wd_saved = array_values( array_filter( wd_saved_listings(), static function ( $id ) {
	return 'publish' === get_post_status( $id ) && 'wd_listing' === get_post_type( $id );
} ) );
if ( $wd_saved ) :
	?>
	<div class="panel" id="saved">
		<h2><?php echo wd_icon( 'bookmark', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( 'Saved', 'wwmd' ); ?></h2>
		<p class="hint"><?php esc_html_e( 'Only you can see what you save.', 'wwmd' ); ?></p>
		<div class="ig-grid ig-grid--4">
			<?php
			foreach ( array_slice( $wd_saved, 0, 24 ) as $wd_sid ) {
				get_template_part(
					'template-parts/card-listing',
					null,
					array(
						'id'   => $wd_sid,
						'view' => 'tile',
					)
				);
			}
			?>
		</div>
	</div>
<?php endif; ?>

<?php if ( $wd_bks ) : ?>
	<ul class="bk-list">
		<?php foreach ( $wd_bks as $wd_b ) : ?>
			<?php
			$wd_l  = wd_listing( (int) $wd_b->listing_id );
			$wd_pp = wd_partner( (int) $wd_b->partner_id );
			$wd_u  = $wd_us[ $wd_l['unit'] ] ?? $wd_us['night'];
			?>
			<li class="panel bk is-<?php echo esc_attr( $wd_b->status ); ?>">
				<a class="bk__pic" href="<?php echo esc_url( get_permalink( (int) $wd_b->listing_id ) ); ?>" tabindex="-1" aria-hidden="true"><?php echo wd_listing_cover( (int) $wd_b->listing_id, $wd_l ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
				<div class="bk__body">
					<div class="bk__top">
						<a class="bk__title" href="<?php echo esc_url( get_permalink( (int) $wd_b->listing_id ) ); ?>"><?php echo esc_html( get_the_title( (int) $wd_b->listing_id ) ); ?></a>
						<?php echo wd_booking_pill( $wd_b->status ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php if ( $wd_b->test ) : ?><span class="tag tag--sample"><?php esc_html_e( 'Test', 'wwmd' ); ?></span><?php endif; ?>
					</div>
					<p class="bk__meta">
						<span><?php echo wd_icon( 'calendar', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( wd_booking_when( $wd_b ) ); ?></span>
						<span><?php echo esc_html( sprintf( '%1$d × %2$d %3$s', (int) $wd_b->qty, (int) $wd_b->units, 1 === (int) $wd_b->units ? $wd_u[0] : $wd_u[1] ) ); ?></span>
						<span><b><?php echo esc_html( wd_money( $wd_b->amount, $wd_b->currency ) ); ?></b></span>
						<?php if ( $wd_pp ) : ?><span><?php echo esc_html( $wd_pp['business'] ); ?></span><?php endif; ?>
					</p>
					<?php if ( in_array( $wd_b->status, array( 'released', 'direct', 'held' ), true ) && ! $wd_b->test ) : ?>
						<?php $wd_rv = $wd_rvs[ (int) $wd_b->id ] ?? null; ?>
						<?php if ( $wd_rv ) : ?>
							<p class="hint"><?php echo wd_stars( (int) $wd_rv->rating ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( array( 'pending' => __( 'Thanks for your review! It shows once we check it.', 'wwmd' ), 'approved' => __( 'Your review is live on the seller’s profile.', 'wwmd' ) )[ $wd_rv->status ] ?? __( 'Thanks for your review.', 'wwmd' ) ); ?></p>
						<?php elseif ( $wd_pp ) : ?>
							<details class="bk__review">
								<summary class="linkish"><?php echo wd_icon( 'star', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( sprintf( /* translators: %s partner */ __( 'Review %s', 'wwmd' ), $wd_pp['business'] ) ); ?></summary>
								<?php wd_market_form( 'review', array( 'bid' => $wd_b->id ), false, 'form' ); ?>
									<fieldset class="rate">
										<legend><?php esc_html_e( 'Your rating', 'wwmd' ); ?></legend>
										<?php for ( $wd_i = 5; $wd_i >= 1; $wd_i-- ) : ?>
											<input type="radio" id="rate-<?php echo (int) $wd_b->id . '-' . (int) $wd_i; ?>" name="rating" value="<?php echo (int) $wd_i; ?>" <?php checked( 5, $wd_i ); ?>><label for="rate-<?php echo (int) $wd_b->id . '-' . (int) $wd_i; ?>" title="<?php echo esc_attr( sprintf( /* translators: %d stars */ _n( '%d star', '%d stars', $wd_i, 'wwmd' ), $wd_i ) ); ?>">★</label>
										<?php endfor; ?>
									</fieldset>
									<label class="field"><span><?php esc_html_e( 'How did it go?', 'wwmd' ); ?></span><textarea name="text" rows="3" minlength="10" maxlength="600" required></textarea></label>
									<button class="btn btn--ink btn--sm" type="submit"><?php esc_html_e( 'Send review', 'wwmd' ); ?></button>
								</form>
							</details>
						<?php endif; ?>
					<?php elseif ( 'pending' === $wd_b->status ) : ?>
						<p class="hint"><?php esc_html_e( 'Not paid yet. Open the chat with the seller if the payment did not go through.', 'wwmd' ); ?></p>
					<?php endif; ?>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>
<?php else : ?>
	<div class="panel bk-empty">
		<span class="bk-empty__art"><?php echo wd_art( 'house' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		<div><h2><?php esc_html_e( 'No orders yet', 'wwmd' ); ?></h2><p><?php esc_html_e( 'Find apartments, cars, honeymoon trips & asoebi from our sellers. Chat with a seller & they send you an invoice or a payment link.', 'wwmd' ); ?></p></div>
	</div>
<?php endif; ?>

<div class="svc-strip svc-strip--app">
	<?php foreach ( $wd_secs as $wd_k => $wd_s ) : ?>
		<a class="svc tone-<?php echo esc_attr( $wd_s[3] ); ?>" href="<?php echo esc_url( wd_url( 'services', array( 'sector' => $wd_k ) ) . '#listings' ); ?>"><span class="svc__art"><?php echo wd_art( $wd_s[2] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><span class="svc__title"><?php echo esc_html( $wd_s[0] ); ?></span><span class="svc__from"><?php esc_html_e( 'Chat with sellers', 'wwmd' ); ?></span></a>
	<?php endforeach; ?>
	<a class="svc tone-lavender" href="<?php echo esc_url( wd_url( 'services' ) . '#livestream' ); ?>"><span class="svc__art"><?php echo wd_art( 'camera' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><span class="svc__title"><?php esc_html_e( 'Livestream, QR cards & more', 'wwmd' ); ?></span><span class="svc__from"><?php esc_html_e( 'Ask us', 'wwmd' ); ?></span></a>
</div>

<?php if ( $wd_reqs ) : ?>
	<div class="panel">
		<h2><?php esc_html_e( 'Your requests', 'wwmd' ); ?></h2>
		<ul class="mini-list">
			<?php foreach ( $wd_reqs as $wd_r ) : ?>
				<li><span><b><?php echo esc_html( $wd_svc[ $wd_r->service ]['title'] ?? ucfirst( $wd_r->service ) ); ?></b> <small><?php echo esc_html( wd_ago( $wd_r->created ) ); ?></small></span><span class="pill"><?php echo esc_html( array( 'new' => __( 'Sent', 'wwmd' ), 'talking' => __( 'In talks', 'wwmd' ), 'booked' => __( 'Booked', 'wwmd' ), 'closed' => __( 'Closed', 'wwmd' ) )[ $wd_r->status ] ?? ucfirst( $wd_r->status ) ); ?></span></li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>

<?php if ( ! wd_partner( $wd_uid ) ) : ?>
	<a class="panel panel--soft partner-nudge" href="<?php echo esc_url( wd_account_url( 'partner' ) ); ?>">
		<?php echo wd_icon( 'store', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<span><b><?php esc_html_e( 'Rent out apartments or cars, run a travel business or sell asoebi?', 'wwmd' ); ?></b> <?php esc_html_e( 'Become a seller & sell to couples & their guests.', 'wwmd' ); ?></span>
		<?php echo wd_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</a>
<?php endif; ?>
