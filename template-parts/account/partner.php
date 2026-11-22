<?php
/**
 * Partner dashboard: apply, list apartments, cars, trips & asoebi, chat with customers, see bookings,
 * edit the public profile & withdraw.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
global $wpdb;
$wd_uid  = get_current_user_id();
$wd_pp   = wd_partner( $wd_uid );
$wd_secs = wd_sectors();
$wd_us   = wd_units();
$wd_fee  = rtrim( rtrim( number_format( (float) wd_opt( 'market_fee' ), 1 ), '0' ), '.' ) . '%';
// phpcs:disable WordPress.Security.NonceVerification
$wd_part = isset( $_GET['part'] ) ? sanitize_key( wp_unslash( $_GET['part'] ) ) : '';
$wd_lid  = isset( $_GET['lid'] ) ? (int) $_GET['lid'] : 0;
// phpcs:enable
$wd_pf = wp_parse_args(
	$wd_pp,
	array(
		'business' => '',
		'sector'   => 'stays',
		'city'     => '',
		'country'  => 'Nigeria',
		'phone'    => get_user_meta( $wd_uid, 'wd_phone', true ),
		'about'    => '',
		'website'  => '',
	)
);

/* Partner details form, used to apply & to update details later. */
$wd_profile_form = static function ( $button ) use ( $wd_pf, $wd_secs ) {
	wd_market_form( 'apply', array(), false, 'form' );
	echo '<div class="field-row">';
	wd_field( 'business', __( 'Business name', 'wwmd' ), $wd_pf['business'], 'text', 'required maxlength="80"' );
	echo '<label class="field"><span>' . esc_html__( 'What you offer', 'wwmd' ) . '</span><select name="sector">';
	foreach ( $wd_secs as $k => $s ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $k ), selected( $wd_pf['sector'], $k, false ), esc_html( $s[0] ) );
	}
	echo '</select></label></div><div class="field-row">';
	wd_field( 'city', __( 'City', 'wwmd' ), $wd_pf['city'], 'text', 'required maxlength="60"' );
	wd_field( 'country', __( 'Country', 'wwmd' ), $wd_pf['country'], 'text', 'maxlength="60"' );
	echo '</div><div class="field-row">';
	wd_field( 'phone', __( 'WhatsApp number', 'wwmd' ), $wd_pf['phone'], 'tel', 'required maxlength="40"' );
	wd_field( 'website', __( 'Website or Instagram link (optional)', 'wwmd' ), $wd_pf['website'], 'url', 'placeholder="https://"' );
	echo '</div>';
	wd_field( 'about', __( 'About your business', 'wwmd' ), $wd_pf['about'], 'textarea', 'maxlength="800" placeholder="' . esc_attr__( 'How long you have been running, what you offer & the areas you cover.', 'wwmd' ) . '"' );
	echo '<button class="btn btn--ink" type="submit">' . esc_html( $button ) . '</button></form>';
};
?>

<?php if ( ! $wd_pp ) : ?>
	<div class="app-head"><div><p class="eyebrow"><?php esc_html_e( 'Sellers', 'wwmd' ); ?></p><h1><?php esc_html_e( 'Become a seller', 'wwmd' ); ?></h1><p><?php esc_html_e( 'List your apartments, cars, trips or asoebi & get booked by couples & their guests.', 'wwmd' ); ?></p></div></div>
	<div class="stat-grid">
		<div class="stat tone-butter"><span><?php esc_html_e( 'Listing', 'wwmd' ); ?></span><b><?php esc_html_e( 'Free', 'wwmd' ); ?></b><small><?php esc_html_e( 'List as many as you like', 'wwmd' ); ?></small></div>
		<div class="stat tone-lavender"><span><?php esc_html_e( 'Our commission', 'wwmd' ); ?></span><b><?php echo esc_html( $wd_fee ); ?></b><small><?php esc_html_e( 'Only on payment links paid on the site', 'wwmd' ); ?></small></div>
		<div class="stat tone-mint"><span><?php esc_html_e( 'You get paid', 'wwmd' ); ?></span><b><?php esc_html_e( 'After the service', 'wwmd' ); ?></b><small><?php esc_html_e( 'Customers pay upfront, so no more no-shows', 'wwmd' ); ?></small></div>
	</div>
	<div class="panel">
		<h2><?php esc_html_e( 'Tell us about your business', 'wwmd' ); ?></h2>
		<p class="hint"><?php esc_html_e( 'We check every seller before they go live. We may ask for photos, your CAC number or a quick video call.', 'wwmd' ); ?></p>
		<?php $wd_profile_form( __( 'Send application', 'wwmd' ) ); ?>
	</div>

<?php elseif ( 'approved' !== $wd_pp['status'] ) : ?>
	<div class="app-head"><div><p class="eyebrow"><?php esc_html_e( 'Sellers', 'wwmd' ); ?></p><h1><?php echo esc_html( $wd_pp['business'] ); ?></h1></div></div>
	<?php if ( 'pending' === $wd_pp['status'] ) : ?>
		<div class="notice notice--warn" role="status"><?php echo wd_icon( 'bell', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'We are checking your application & will reply on WhatsApp within 2 working days. You can still update your details below.', 'wwmd' ); ?></span></div>
		<div class="panel"><h2><?php esc_html_e( 'Your details', 'wwmd' ); ?></h2><?php $wd_profile_form( __( 'Save details', 'wwmd' ) ); ?></div>
	<?php else : ?>
		<div class="notice notice--err" role="status"><?php echo wd_icon( 'close', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( 'paused' === $wd_pp['status'] ? __( 'Your seller account is paused, so your listings are hidden for now.', 'wwmd' ) : __( 'We could not approve your application this time.', 'wwmd' ) ); ?></span></div>
		<a class="btn btn--wa" href="<?php echo esc_url( wd_whatsapp( __( 'Hello! I would like to talk about my seller account.', 'wwmd' ) ) ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'whatsapp', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Talk to us on WhatsApp', 'wwmd' ); ?></a>
	<?php endif; ?>

<?php elseif ( 'listing' === $wd_part ) : ?>
	<?php
	$wd_post = $wd_lid ? get_post( $wd_lid ) : null;
	if ( $wd_post && ( 'wd_listing' !== $wd_post->post_type || (int) $wd_post->post_author !== $wd_uid ) ) {
		$wd_post = null;
		$wd_lid  = 0;
	}
	$wd_l = $wd_lid ? wd_listing( $wd_lid ) : wp_parse_args(
		array(
			'sector'  => $wd_pp['sector'],
			'city'    => $wd_pp['city'],
			'country' => $wd_pp['country'],
			'unit'    => array( 'rides' => 'day', 'travel' => 'package', 'asoebi' => 'person' )[ $wd_pp['sector'] ] ?? 'night',
		),
		wd_listing( 0 )
	);
	?>
	<div class="app-head"><div><p class="eyebrow"><a href="<?php echo esc_url( wd_account_url( 'partner' ) ); ?>"><?php esc_html_e( 'Seller dashboard', 'wwmd' ); ?></a></p><h1><?php echo $wd_post ? esc_html__( 'Edit listing', 'wwmd' ) : esc_html__( 'New listing', 'wwmd' ); ?></h1></div><?php if ( $wd_post && 'publish' === $wd_post->post_status ) : ?><a class="btn btn--ghost" href="<?php echo esc_url( get_permalink( $wd_lid ) ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'eye', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'View', 'wwmd' ); ?></a><?php endif; ?></div>
	<?php wd_market_form( 'listing', array( 'lid' => $wd_lid ), true ); ?>
		<?php wd_field( 'title', __( 'Title', 'wwmd' ), $wd_post ? $wd_post->post_title : '', 'text', 'required maxlength="100" placeholder="' . esc_attr__( 'e.g. 3-bed apartment in Lekki Phase 1 or Toyota Prado with driver', 'wwmd' ) . '"' ); ?>
		<div class="field-row field-row--4">
			<label class="field"><span><?php esc_html_e( 'Type', 'wwmd' ); ?></span><select name="sector"><?php foreach ( $wd_secs as $wd_k => $wd_s ) : ?><option value="<?php echo esc_attr( $wd_k ); ?>" <?php selected( $wd_l['sector'], $wd_k ); ?>><?php echo esc_html( $wd_s[0] ); ?></option><?php endforeach; ?></select></label>
			<?php wd_field( 'price', __( 'Price (₦)', 'wwmd' ), $wd_l['price'] ? $wd_l['price'] : '', 'number', 'min="1" inputmode="numeric" data-price-input' . ( wd_listing_asks( $wd_l ) ? '' : ' required' ) ); ?>
			<label class="field"><span><?php esc_html_e( 'Per', 'wwmd' ); ?></span><select name="unit"><?php foreach ( $wd_us as $wd_k => $wd_u ) : ?><option value="<?php echo esc_attr( $wd_k ); ?>" <?php selected( $wd_l['unit'], $wd_k ); ?>><?php echo esc_html( $wd_u[0] ); ?></option><?php endforeach; ?></select></label>
			<?php wd_field( 'capacity', __( 'Up to how many people', 'wwmd' ), $wd_l['capacity'] ? $wd_l['capacity'] : '', 'number', 'min="0" max="500"' ); ?>
		</div>
		<fieldset class="field price-mode" data-price-mode>
			<legend><?php esc_html_e( 'Show the price?', 'wwmd' ); ?></legend>
			<label class="check"><input type="radio" name="price_mode" value="fixed" <?php checked( ! wd_listing_asks( $wd_l ) ); ?>><span><b><?php esc_html_e( 'Show my price', 'wwmd' ); ?></b> <?php esc_html_e( 'Customers pick dates & pay right away.', 'wwmd' ); ?></span></label>
			<label class="check"><input type="radio" name="price_mode" value="ask" <?php checked( wd_listing_asks( $wd_l ) ); ?>><span><b><?php esc_html_e( 'Contact for price', 'wwmd' ); ?></b> <?php esc_html_e( 'Customers chat with you here, you send them a payment link & they pay it on the site.', 'wwmd' ); ?></span></label>
		</fieldset>
		<div class="field-row">
			<?php wd_field( 'city', __( 'City', 'wwmd' ), $wd_l['city'], 'text', 'required maxlength="60"' ); ?>
			<?php wd_field( 'country', __( 'Country', 'wwmd' ), $wd_l['country'], 'text', 'maxlength="60"' ); ?>
		</div>
		<?php wd_field( 'about', __( 'Description', 'wwmd' ), $wd_l['about'], 'textarea', 'rows="5" maxlength="2000" placeholder="' . esc_attr__( 'What it is, where it is & what makes it good for a wedding.', 'wwmd' ) . '"' ); ?>
		<?php wd_field( 'features', __( 'What is included (one per line)', 'wwmd' ), implode( "\n", (array) $wd_l['features'] ), 'textarea', 'rows="4" placeholder="' . esc_attr__( "Driver & fuel\n24-hour power\nAirport pick-up", 'wwmd' ) . '"' ); ?>
		<div class="field-row">
			<label class="field file-field"><span><?php esc_html_e( 'Add a photo (JPG or PNG, up to 6)', 'wwmd' ); ?></span><input type="file" name="photo_file" accept="image/jpeg,image/png,image/webp"></label>
			<?php if ( $wd_l['photos'] ) : ?><label class="check"><input type="checkbox" name="remove_photos" value="1"><span><?php echo esc_html( sprintf( /* translators: %d photos */ _n( 'Remove the %d photo', 'Remove all %d photos', count( $wd_l['photos'] ), 'wwmd' ), count( $wd_l['photos'] ) ) ); ?></span></label><?php endif; ?>
		</div>
		<p class="hint"><?php esc_html_e( 'Use real photos of the exact apartment, car, trip or fabric. New listings are checked before they go live.', 'wwmd' ); ?></p>
		<div class="form-actions"><button class="btn btn--ink" type="submit"><?php esc_html_e( 'Save listing', 'wwmd' ); ?></button><a class="btn btn--ghost" href="<?php echo esc_url( wd_account_url( 'partner' ) ); ?>"><?php esc_html_e( 'Cancel', 'wwmd' ); ?></a></div>
	</form>

<?php elseif ( 'chat' === $wd_part ) : ?>
	<?php
	// phpcs:ignore WordPress.Security.NonceVerification
	$wd_cu = get_userdata( isset( $_GET['user'] ) ? (int) $_GET['user'] : 0 );
	?>
	<div class="app-head"><div><p class="eyebrow"><a href="<?php echo esc_url( wd_account_url( 'partner' ) . '#messages' ); ?>"><?php esc_html_e( 'Messages', 'wwmd' ); ?></a></p><h1><?php echo esc_html( $wd_cu ? $wd_cu->display_name : __( 'Chat', 'wwmd' ) ); ?></h1></div></div>
	<?php if ( $wd_cu && wd_chat_started( $wd_uid, $wd_cu->ID ) ) : ?>
		<div class="panel panel--chat"><?php get_template_part( 'template-parts/chat', null, array( 'partner' => $wd_uid, 'user' => $wd_cu->ID, 'role' => 'partner', 'back' => wd_account_url( 'partner' ) . '#messages' ) ); ?></div>
	<?php else : ?>
		<p class="empty-note"><?php esc_html_e( 'This chat is not here any more.', 'wwmd' ); ?></p>
	<?php endif; ?>

<?php elseif ( 'profile' === $wd_part ) : ?>
	<?php
	$wd_folio = (array) $wd_pp['portfolio'];
	$wd_revs  = wd_partner_reviews( $wd_uid, 'approved', 50 );
	?>
	<div class="app-head"><div><p class="eyebrow"><a href="<?php echo esc_url( wd_account_url( 'partner' ) ); ?>"><?php esc_html_e( 'Seller dashboard', 'wwmd' ); ?></a></p><h1><?php esc_html_e( 'Your public profile', 'wwmd' ); ?></h1><p><?php esc_html_e( 'Customers open it from the About box on any of your listings: your story, photos of your work, all your listings & your reviews.', 'wwmd' ); ?></p></div><a class="btn btn--ghost" href="<?php echo esc_url( wd_partner_url( $wd_uid ) ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'eye', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'View profile', 'wwmd' ); ?></a></div>
	<?php wd_market_form( 'profile', array(), true ); ?>
		<div class="field-row">
			<label class="field file-field"><span><?php esc_html_e( 'Logo (square)', 'wwmd' ); ?></span><input type="file" name="logo_file" accept="image/jpeg,image/png,image/webp"></label>
			<label class="field file-field"><span><?php esc_html_e( 'Cover photo (wide)', 'wwmd' ); ?></span><input type="file" name="cover_file" accept="image/jpeg,image/png,image/webp"></label>
		</div>
		<div class="field-row">
			<?php if ( $wd_pp['logo'] ) : ?><label class="check"><input type="checkbox" name="remove_logo" value="1"><span><?php esc_html_e( 'Remove my logo', 'wwmd' ); ?></span></label><?php endif; ?>
			<?php if ( $wd_pp['cover'] ) : ?><label class="check"><input type="checkbox" name="remove_cover" value="1"><span><?php esc_html_e( 'Remove my cover photo', 'wwmd' ); ?></span></label><?php endif; ?>
		</div>
		<h2><?php esc_html_e( 'Business details', 'wwmd' ); ?></h2>
		<div class="field-row">
			<?php wd_field( 'business', __( 'Business name', 'wwmd' ), $wd_pp['business'], 'text', 'required maxlength="80"' ); ?>
			<?php wd_field( 'phone', __( 'Phone or WhatsApp (only our team sees it)', 'wwmd' ), $wd_pp['phone'], 'tel', 'maxlength="40"' ); ?>
		</div>
		<div class="field-row">
			<?php wd_field( 'city', __( 'City', 'wwmd' ), $wd_pp['city'], 'text', 'required maxlength="60"' ); ?>
			<?php wd_field( 'country', __( 'Country', 'wwmd' ), $wd_pp['country'], 'text', 'maxlength="60"' ); ?>
		</div>
		<?php wd_field( 'website', __( 'Website (optional)', 'wwmd' ), $wd_pp['website'], 'url', 'placeholder="https://"' ); ?>
		<p class="hint"><?php echo wp_kses( sprintf( /* translators: %s link */ __( 'Your sign-in email & password are in <a href="%s">My profile</a>.', 'wwmd' ), esc_url( wd_account_url( 'profile' ) ) ), array( 'a' => array( 'href' => array() ) ) ); ?></p>
		<?php wd_field( 'about', __( 'About your business', 'wwmd' ), $wd_pp['about'], 'textarea', 'rows="4" maxlength="800"' ); ?>
		<div class="field-row">
			<?php wd_field( 'hours', __( 'Opening hours', 'wwmd' ), $wd_pp['hours'], 'text', 'maxlength="120" placeholder="' . esc_attr__( 'e.g. Mon–Sat, 9am–7pm', 'wwmd' ) . '"' ); ?>
			<?php wd_field( 'areas', __( 'Areas you cover', 'wwmd' ), $wd_pp['areas'], 'text', 'maxlength="160" placeholder="' . esc_attr__( 'e.g. All of Lagos; we ship to the UK, US & Canada', 'wwmd' ) . '"' ); ?>
		</div>
		<h2><?php esc_html_e( 'Portfolio', 'wwmd' ); ?></h2>
		<p class="hint"><?php esc_html_e( 'Up to 12 photos of real work you did. Add one at a time.', 'wwmd' ); ?></p>
		<?php if ( $wd_folio ) : ?>
			<ul class="folio-edit">
				<?php foreach ( array_values( $wd_folio ) as $wd_i => $wd_ph ) : ?>
					<li>
						<img src="<?php echo esc_url( wd_img_url( $wd_ph['img'], 'thumbnail' ) ); ?>" alt="">
						<input name="cap[<?php echo (int) $wd_i; ?>]" value="<?php echo esc_attr( $wd_ph['cap'] ?? '' ); ?>" maxlength="80" placeholder="<?php esc_attr_e( 'Caption', 'wwmd' ); ?>" aria-label="<?php esc_attr_e( 'Caption', 'wwmd' ); ?>">
						<label class="check"><input type="checkbox" name="remove_photo[<?php echo (int) $wd_i; ?>]" value="1"><span><?php esc_html_e( 'Remove', 'wwmd' ); ?></span></label>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<?php if ( count( $wd_folio ) < 12 ) : ?>
			<div class="field-row">
				<label class="field file-field"><span><?php esc_html_e( 'Add a photo', 'wwmd' ); ?></span><input type="file" name="photo_file" accept="image/jpeg,image/png,image/webp"></label>
				<?php wd_field( 'new_cap', __( 'Its caption (optional)', 'wwmd' ), '', 'text', 'maxlength="80"' ); ?>
			</div>
		<?php endif; ?>
		<button class="btn btn--ink" type="submit"><?php esc_html_e( 'Save profile', 'wwmd' ); ?></button>
	</form>
	<div class="panel" id="reviews">
		<h2><?php esc_html_e( 'Your reviews', 'wwmd' ); ?></h2>
		<p class="hint"><?php esc_html_e( 'Customers can review you after a booking paid on the site is done. We check each review first. You can answer each one once, in public.', 'wwmd' ); ?></p>
		<?php if ( $wd_revs ) : ?>
			<ul class="p-reviews">
				<?php foreach ( $wd_revs as $wd_r ) : ?>
					<li>
						<p class="p-reviews__top"><b><?php echo esc_html( $wd_r->name ); ?></b><?php echo wd_stars( (int) $wd_r->rating ); // phpcs:ignore WordPress.Security.EscapeOutput ?><time><?php echo esc_html( wd_ago( $wd_r->created ) ); ?></time></p>
						<p><?php echo esc_html( $wd_r->text ); ?></p>
						<?php if ( $wd_r->reply ) : ?>
							<blockquote class="p-reviews__reply"><b><?php esc_html_e( 'Your reply', 'wwmd' ); ?></b><?php echo esc_html( $wd_r->reply ); ?></blockquote>
						<?php else : ?>
							<?php wd_market_form( 'review_reply', array( 'rid' => $wd_r->id ), false, 'form form--inline' ); ?>
								<label class="field"><span><?php esc_html_e( 'Reply in public', 'wwmd' ); ?></span><textarea name="reply" rows="2" maxlength="400" required></textarea></label>
								<button class="btn btn--ghost btn--sm" type="submit"><?php esc_html_e( 'Post reply', 'wwmd' ); ?></button>
							</form>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<p class="empty-note"><?php esc_html_e( 'No reviews yet.', 'wwmd' ); ?></p>
		<?php endif; ?>
	</div>

<?php else : ?>
	<?php
	$wd_m    = wd_partner_money( $wd_uid );
	$wd_bks  = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . wd_t( 'bookings' ) . " WHERE partner_id = %d AND status IN ('held','disputed','released','direct','refunded') ORDER BY FIELD(status,'held','disputed','released','refunded'), start_date ASC, id DESC LIMIT 60", $wd_uid ) ); // phpcs:ignore WordPress.DB
	$wd_ls   = get_posts(
		array(
			'post_type'   => 'wd_listing',
			'post_status' => array( 'publish', 'pending', 'draft' ),
			'author'      => $wd_uid,
			'numberposts' => 50,
		)
	);
	$wd_outs = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . wd_t( 'payouts' ) . ' WHERE user_id = %d AND wedding_id = 0 ORDER BY id DESC LIMIT 20', $wd_uid ) ); // phpcs:ignore WordPress.DB
	$wd_bank = wp_parse_args( (array) get_user_meta( $wd_uid, 'wd_bank', true ), array( 'bank' => '', 'account_no' => '', 'account_name' => '' ) );
	$wd_lst  = array(
		'publish' => array( __( 'Live', 'wwmd' ), 'pill--ok' ),
		'pending' => array( __( 'Waiting for approval', 'wwmd' ), 'pill--warn' ),
		'draft'   => array( __( 'Paused', 'wwmd' ), '' ),
	);
	?>
	<div class="app-head"><div><p class="eyebrow"><?php esc_html_e( 'Seller dashboard', 'wwmd' ); ?></p><h1><?php echo esc_html( $wd_pp['business'] ); ?> <?php echo wd_partner_badge( $wd_uid ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1></div><div class="btn-row"><a class="btn btn--ghost" href="<?php echo esc_url( wd_account_url( 'partner', array( 'part' => 'profile' ) ) ); ?>"><?php echo wd_icon( 'store', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Profile & reviews', 'wwmd' ); ?></a><a class="btn btn--ink" href="<?php echo esc_url( wd_account_url( 'partner', array( 'part' => 'listing' ) ) ); ?>"><?php echo wd_icon( 'plus', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Add a listing', 'wwmd' ); ?></a></div></div>
	<div class="stat-grid stat-grid--4">
		<div class="stat tone-mint"><span><?php esc_html_e( 'Ready to withdraw', 'wwmd' ); ?></span><b><?php echo esc_html( wd_money( $wd_m['balance'] ) ); ?></b><small><?php echo esc_html( sprintf( /* translators: %s */ __( '%s paid out or on its way', 'wwmd' ), wd_money( $wd_m['paid_out'] ) ) ); ?></small></div>
		<div class="stat tone-butter"><span><?php esc_html_e( 'Paid through the site', 'wwmd' ); ?></span><b><?php echo esc_html( wd_money( $wd_m['released'] + $wd_m['held'] ) ); ?></b><small><?php echo esc_html( sprintf( /* translators: %s commission */ __( 'Payment links, after our %s commission', 'wwmd' ), $wd_fee ) ); ?></small></div>
		<div class="stat tone-sky"><span><?php esc_html_e( 'Invoices paid to you', 'wwmd' ); ?></span><b><?php echo esc_html( wd_money( (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COALESCE(SUM(amount),0) FROM ' . wd_t( 'bookings' ) . " WHERE partner_id = %d AND status = 'direct'", $wd_uid ) ) ) ); // phpcs:ignore WordPress.DB ?></b><small><?php esc_html_e( 'Paid to you directly, no commission', 'wwmd' ); ?></small></div>
		<div class="stat tone-lavender"><span><?php esc_html_e( 'Live listings', 'wwmd' ); ?></span><b><?php echo (int) count( wp_list_filter( $wd_ls, array( 'post_status' => 'publish' ) ) ); ?></b><small><?php echo esc_html( sprintf( /* translators: %d */ _n( '%d listing in total', '%d listings in total', count( $wd_ls ), 'wwmd' ), count( $wd_ls ) ) ); ?></small></div>
	</div>

	<?php $wd_threads = wd_chat_threads( 'partner', $wd_uid ); ?>
	<div class="panel" id="messages">
		<div class="panel__head"><h2><?php esc_html_e( 'Messages', 'wwmd' ); ?><?php $wd_un = wd_chat_unread( 'partner', $wd_uid ); ?><?php if ( $wd_un ) : ?> <span class="chat-list__n"><?php echo (int) $wd_un; ?></span><?php endif; ?></h2></div>
		<?php if ( $wd_threads ) : ?>
			<?php
			wd_chat_list(
				$wd_threads,
				'partner',
				static function ( $t ) {
					return wd_account_url( 'partner', array( 'part' => 'chat', 'user' => (int) $t->user_id ) );
				}
			);
			?>
		<?php else : ?>
			<p class="empty-note"><?php esc_html_e( 'No messages yet. Customers can chat with you from your listings & your profile. Agree the details, then send a payment link right in the chat.', 'wwmd' ); ?></p>
		<?php endif; ?>
	</div>

	<div class="panel">
		<div class="panel__head"><h2><?php esc_html_e( 'Bookings', 'wwmd' ); ?></h2></div>
		<?php if ( $wd_bks ) : ?>
			<div class="table-wrap"><table class="table table--compact table--stack">
				<thead><tr><th><?php esc_html_e( 'Customer', 'wwmd' ); ?></th><th><?php esc_html_e( 'Listing & dates', 'wwmd' ); ?></th><th><?php esc_html_e( 'You get', 'wwmd' ); ?></th><th><?php esc_html_e( 'Status', 'wwmd' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( $wd_bks as $wd_b ) : ?>
					<?php $wd_ph = preg_replace( '/\D/', '', $wd_b->phone ); ?>
					<tr>
						<td data-label="<?php esc_attr_e( 'Customer', 'wwmd' ); ?>"><b><?php echo esc_html( $wd_b->name ); ?></b><br><?php if ( $wd_ph ) : ?><a class="linkish" href="<?php echo esc_url( 'https://wa.me/' . ( 0 === strpos( $wd_ph, '0' ) ? '234' . substr( $wd_ph, 1 ) : $wd_ph ) ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'whatsapp', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $wd_b->phone ); ?></a><?php endif; ?></td>
						<td data-label="<?php esc_attr_e( 'Listing & dates', 'wwmd' ); ?>"><?php echo esc_html( get_the_title( (int) $wd_b->listing_id ) ); ?><br><small><?php echo esc_html( wd_booking_when( $wd_b ) ); ?> · <?php echo esc_html( sprintf( /* translators: %d */ __( 'x%d', 'wwmd' ), (int) $wd_b->qty ) ); ?></small><?php if ( $wd_b->note ) : ?><br><small class="muted"><?php echo esc_html( $wd_b->note ); ?></small><?php endif; ?></td>
						<td data-label="<?php esc_attr_e( 'You get', 'wwmd' ); ?>"><b><?php echo esc_html( wd_money( $wd_b->net, $wd_b->currency ) ); ?></b><br><small class="muted"><?php echo esc_html( sprintf( /* translators: %s total */ __( 'of %s', 'wwmd' ), wd_money( $wd_b->amount, $wd_b->currency ) ) ); ?></small></td>
						<td data-label="<?php esc_attr_e( 'Status', 'wwmd' ); ?>"><?php echo wd_booking_pill( $wd_b->status ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php if ( $wd_b->test ) : ?> <span class="tag tag--sample"><?php esc_html_e( 'Test', 'wwmd' ); ?></span><?php endif; ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table></div>
		<?php else : ?>
			<p class="empty-note"><?php esc_html_e( 'No orders yet. Share your listing links on WhatsApp & Instagram to get your first one.', 'wwmd' ); ?></p>
		<?php endif; ?>
	</div>

	<div class="panel">
		<div class="panel__head"><h2><?php esc_html_e( 'Your listings', 'wwmd' ); ?></h2></div>
		<?php if ( $wd_ls ) : ?>
			<ul class="mini-list mini-list--listings">
				<?php foreach ( $wd_ls as $wd_p ) : ?>
					<?php $wd_x = wd_listing( $wd_p->ID ); ?>
					<li>
						<span><b><?php echo esc_html( $wd_p->post_title ); ?></b> <small><?php echo esc_html( wd_listing_price( $wd_x ) ); ?> · <?php echo esc_html( $wd_x['city'] ); ?></small></span>
						<span class="row-actions">
							<span class="pill <?php echo esc_attr( $wd_lst[ $wd_p->post_status ][1] ); ?>"><?php echo esc_html( $wd_lst[ $wd_p->post_status ][0] ); ?></span>
							<a class="btn btn--ghost btn--sm" href="<?php echo esc_url( wd_account_url( 'partner', array( 'part' => 'listing', 'lid' => $wd_p->ID ) ) ); ?>"><?php esc_html_e( 'Edit', 'wwmd' ); ?></a>
							<?php if ( 'pending' !== $wd_p->post_status ) : ?><?php wd_market_form( 'listing_pause', array( 'lid' => $wd_p->ID ), false, 'inline' ); ?><button class="btn btn--ghost btn--sm" type="submit"><?php echo 'publish' === $wd_p->post_status ? esc_html__( 'Pause', 'wwmd' ) : esc_html__( 'Go live', 'wwmd' ); ?></button></form><?php endif; ?>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<p class="empty-note"><?php esc_html_e( 'No listings yet. Add your first apartment, car or trip.', 'wwmd' ); ?></p>
		<?php endif; ?>
	</div>

	<div class="app-cols">
		<div class="panel" id="payouts">
			<?php
			$wd_acct = isset( $_GET['payouts'] ) ? wd_seller_acct_refresh( $wd_uid ) : wd_seller_acct( $wd_uid ); // phpcs:ignore WordPress.Security.NonceVerification
			?>
			<?php if ( wd_bachs_key() ) : ?>
				<div class="payout-acct<?php echo $wd_acct['ready'] ? ' is-ready' : ''; ?>">
					<span class="payout-acct__icon"><?php echo wd_icon( $wd_acct['ready'] ? 'check' : 'payout', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<div>
						<h3><?php echo $wd_acct['ready'] ? esc_html__( 'Automatic payouts are on', 'wwmd' ) : esc_html__( 'Get paid automatically', 'wwmd' ); ?></h3>
						<p><?php echo $wd_acct['ready'] ? esc_html__( 'When a booking is done, your share goes straight to your payout account. No need to request it.', 'wwmd' ) : esc_html__( 'Set up your payout account once (your bank & ID, on a secure payment page). Then your share of every finished booking is sent to you automatically.', 'wwmd' ); ?></p>
						<?php if ( ! $wd_acct['ready'] ) : ?>
							<?php wd_market_form( 'payout_setup', array(), false, 'inline' ); ?><button class="btn btn--marigold btn--sm" type="submit"><?php echo $wd_acct['id'] ? esc_html__( 'Finish setting up payouts', 'wwmd' ) : esc_html__( 'Set up payouts', 'wwmd' ); ?></button></form>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
			<h2><?php esc_html_e( 'Withdraw', 'wwmd' ); ?></h2>
			<?php wd_market_form( 'payout', array(), false, 'form' ); ?>
				<?php wd_field( 'amount', __( 'Amount (₦)', 'wwmd' ), $wd_m['balance'] ? $wd_m['balance'] : '', 'number', 'min="' . (int) wd_opt( 'payout_min' ) . '" max="' . (int) $wd_m['balance'] . '" required' ); ?>
				<?php wd_field( 'bank', __( 'Bank', 'wwmd' ), $wd_bank['bank'], 'text', 'required' ); ?>
				<div class="field-row">
					<?php wd_field( 'account_no', __( 'Account number', 'wwmd' ), $wd_bank['account_no'], 'text', 'required inputmode="numeric"' ); ?>
					<?php wd_field( 'account_name', __( 'Account name', 'wwmd' ), $wd_bank['account_name'], 'text', 'required' ); ?>
				</div>
				<p class="hint"><?php echo esc_html( sprintf( /* translators: %s min */ __( 'Minimum %s. We pay into your account within one working day.', 'wwmd' ), wd_money( wd_opt( 'payout_min' ) ) ) ); ?></p>
				<button class="btn btn--ink" type="submit" <?php disabled( $wd_m['balance'] < (int) wd_opt( 'payout_min' ) ); ?>><?php esc_html_e( 'Request payout', 'wwmd' ); ?></button>
			</form>
			<?php if ( $wd_outs ) : ?>
				<h3><?php esc_html_e( 'Payouts', 'wwmd' ); ?></h3>
				<ul class="mini-list">
					<?php foreach ( $wd_outs as $wd_o ) : ?>
						<li><span><b><?php echo esc_html( wd_money( $wd_o->amount ) ); ?></b> <small><?php echo esc_html( wd_ago( $wd_o->created ) ); ?></small></span><span class="pill<?php echo 'paid' === $wd_o->status ? ' pill--ok' : ''; ?>"><?php echo esc_html( ( ( 0 === strpos( $wd_o->note, 'auto' ) ? array( 'paid' => __( 'Sent automatically', 'wwmd' ) ) : array() ) + array( 'requested' => __( 'Requested', 'wwmd' ), 'paid' => __( 'Paid', 'wwmd' ), 'rejected' => __( 'Declined', 'wwmd' ) ) )[ $wd_o->status ] ?? $wd_o->status ); ?></span></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<div class="panel">
			<h2><?php esc_html_e( 'Business details', 'wwmd' ); ?></h2>
			<?php $wd_profile_form( __( 'Save details', 'wwmd' ) ); ?>
		</div>
	</div>
	<?php get_template_part( 'template-parts/account/invoice-look' ); ?>
<?php endif; ?>
