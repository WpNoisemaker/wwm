<?php
/**
 * One marketplace listing as a post (1.5): the seller, swipeable photos, heart, chat, share & save,
 * then the details, the seller & the chat (no direct booking: the seller sends an invoice or payment link).
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
get_header();
the_post();
$wd_lid  = get_the_ID();
$wd_l    = wd_listing( $wd_lid );
$wd_secs = wd_sectors();
$wd_s    = isset( $wd_secs[ $wd_l['sector'] ] ) ? $wd_secs[ $wd_l['sector'] ] : $wd_secs['stays'];
$wd_pid  = (int) get_post_field( 'post_author', $wd_lid );
$wd_pp   = wd_partner( $wd_pid );
$wd_us   = wd_units();
$wd_u    = isset( $wd_us[ $wd_l['unit'] ] ) ? $wd_us[ $wd_l['unit'] ] : $wd_us['night'];
$wd_rng  = 'range' === $wd_u[2];
$wd_live = 'publish' === get_post_status( $wd_lid ) && wd_is_partner( $wd_pid );
$wd_me   = wp_get_current_user();
$wd_own  = $wd_me->exists() && $wd_me->ID === $wd_pid;
$wd_ask  = wd_listing_asks( $wd_l );
$wd_pst  = $wd_pp ? wd_partner_stats( $wd_pid ) : array();
$wd_chat = $wd_live && $wd_me->exists() && ! $wd_own ? array(
	'partner' => $wd_pid,
	'user'    => $wd_me->ID,
	'role'    => 'user',
	'listing' => $wd_lid,
) : null;

// Back from the payment page: confirm the payment, then show the booking.
$wd_ref = wd_pay_ref_from_request();
if ( $wd_ref ) {
	wd_pay_verify( $wd_ref );
}
// phpcs:ignore WordPress.Security.NonceVerification
$wd_bk = isset( $_GET['booked'] ) ? wd_booking( (int) $_GET['booked'] ) : null;
if ( $wd_bk && ( ! $wd_me->exists() || (int) $wd_bk->user_id !== $wd_me->ID ) ) {
	$wd_bk = null;
}
$wd_more = wd_listing_query(
	array(
		'sector'  => $wd_l['sector'],
		'per'     => 3,
		'exclude' => array( $wd_lid ),
	)
);
$wd_today = wp_date( 'Y-m-d' );
?>
<section class="section listing">
	<div class="wrap">
		<nav class="crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'wwmd' ); ?>">
			<a href="<?php echo esc_url( wd_url( 'services' ) ); ?>"><?php esc_html_e( 'Services', 'wwmd' ); ?></a>
			<span aria-hidden="true">/</span>
			<a href="<?php echo esc_url( wd_url( 'services', array( 'sector' => $wd_l['sector'] ) ) . '#listings' ); ?>"><?php echo esc_html( $wd_s[0] ); ?></a>
		</nav>
		<div class="listing__grid">
			<div class="listing__main">
				<article class="ig-detail">
					<?php if ( $wd_pp ) : ?>
						<header class="ig-post__head">
							<?php echo wd_seller_ring( $wd_pid, 42, 'a' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<span class="ig-post__who">
								<a href="<?php echo esc_url( wd_partner_url( $wd_pid ) ); ?>"><b><?php echo esc_html( $wd_pp['business'] ); ?></b><?php echo wd_partner_badge( $wd_pid, false ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
								<small><?php echo esc_html( trim( $wd_l['city'] . ', ' . $wd_l['country'], ', ' ) ); ?></small>
							</span>
							<span class="chip chip--sm"><?php echo wd_icon( $wd_s[1], 13 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $wd_s[0] ); ?></span>
						</header>
					<?php endif; ?>
					<?php echo wd_listing_carousel( $wd_lid, $wd_l, 'wd-cover', 10, 'ig-media--wide' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php echo wd_listing_actions( $wd_lid ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<div class="ig-detail__cap">
						<h1 class="listing__title"><?php the_title(); ?></h1>
						<p class="listing__meta">
							<span><?php echo wd_icon( 'pin', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( trim( $wd_l['city'] . ', ' . $wd_l['country'], ', ' ) ); ?></span>
							<?php if ( $wd_l['capacity'] ) : ?><span><?php echo wd_icon( 'users', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( sprintf( /* translators: %d people */ _n( 'Up to %d person', 'Up to %d people', (int) $wd_l['capacity'], 'wwmd' ), (int) $wd_l['capacity'] ) ); ?></span><?php endif; ?>
							<span class="ig-post__price<?php echo $wd_ask ? ' is-ask' : ''; ?>"><?php echo esc_html( wd_listing_price_line( $wd_l ) ); ?></span>
						</p>
					</div>
				</article>
				<?php if ( $wd_l['about'] ) : ?><div class="prose"><?php echo wp_kses_post( wpautop( esc_html( $wd_l['about'] ) ) ); ?></div><?php endif; ?>
				<?php if ( $wd_l['features'] ) : ?>
					<h2 class="listing__h"><?php esc_html_e( 'What you get', 'wwmd' ); ?></h2>
					<ul class="tick-list">
						<?php foreach ( (array) $wd_l['features'] as $wd_f ) : ?><li><?php echo wd_icon( 'check', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $wd_f ); ?></li><?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<?php if ( $wd_pp ) : ?>
					<h2 class="listing__h"><?php esc_html_e( 'About the seller', 'wwmd' ); ?></h2>
					<a class="partner-card" href="<?php echo esc_url( wd_partner_url( $wd_pid ) ); ?>">
						<span class="partner-card__av"><?php echo wd_partner_logo( $wd_pp, 56 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<span class="partner-card__body">
							<span class="partner-card__name"><b><?php echo esc_html( $wd_pp['business'] ); ?></b><?php echo wd_partner_badge( $wd_pid ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
							<span class="partner-card__meta"><?php echo esc_html( $wd_secs[ $wd_pp['sector'] ][0] ?? '' ); ?> · <?php echo esc_html( trim( $wd_pp['city'] . ', ' . $wd_pp['country'], ', ' ) ); ?><?php if ( $wd_pp['approved'] ) : ?> · <?php echo esc_html( sprintf( /* translators: %s month & year */ __( 'Seller since %s', 'wwmd' ), wp_date( 'F Y', strtotime( $wd_pp['approved'] ) ) ) ); ?><?php endif; ?></span>
							<span class="partner-card__stats"><?php if ( $wd_pst['reviews'] ) : ?><?php echo wd_stars( $wd_pst['rating'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( sprintf( /* translators: 1 rating 2 reviews */ _n( '%1$s · %2$d review', '%1$s · %2$d reviews', $wd_pst['reviews'], 'wwmd' ), number_format_i18n( $wd_pst['rating'], 1 ), $wd_pst['reviews'] ) ); ?><?php else : ?><?php esc_html_e( 'No reviews yet', 'wwmd' ); ?><?php endif; ?> · <?php echo esc_html( sprintf( /* translators: %d listings */ _n( '%d listing', '%d listings', $wd_pst['listings'], 'wwmd' ), $wd_pst['listings'] ) ); ?></span>
							<?php if ( $wd_pp['about'] ) : ?><span class="partner-card__about"><?php echo esc_html( wp_trim_words( $wd_pp['about'], 30, '…' ) ); ?></span><?php endif; ?>
							<span class="partner-card__go"><?php esc_html_e( 'See profile, portfolio & reviews', 'wwmd' ); ?><?php echo wd_icon( 'arrow', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						</span>
					</a>
				<?php endif; ?>
				<div class="safe-note">
					<span class="safe-note__icon"><?php echo wd_icon( 'chat', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<div>
						<h2><?php esc_html_e( 'How buying works', 'wwmd' ); ?></h2>
						<p><?php echo esc_html( sprintf( /* translators: %s seller */ __( 'Chat with %s about your date & needs. They send you an invoice or a payment link in the chat. Pay the link on the site, or pay the invoice the way it says. Your receipt stays in the chat.', 'wwmd' ), $wd_pp ? $wd_pp['business'] : __( 'the seller', 'wwmd' ) ) ); ?></p>
					</div>
				</div>
			</div>
			<aside class="listing__side">
				<div class="book-box" id="book">
					<?php if ( $wd_ask ) : ?>
						<p class="book-box__price book-box__price--ask"><b><?php esc_html_e( 'Contact for price', 'wwmd' ); ?></b></p>
					<?php else : ?>
						<p class="book-box__price"><small><?php esc_html_e( 'From', 'wwmd' ); ?></small> <b><?php echo esc_html( wd_money( $wd_l['price'] ) ); ?></b> / <?php echo esc_html( $wd_u[0] ); ?></p>
					<?php endif; ?>
					<p class="book-box__how"><?php echo esc_html( sprintf( /* translators: %s partner */ __( 'Chat with %s about your date & needs. They send you an invoice or a payment link.', 'wwmd' ), $wd_pp ? $wd_pp['business'] : __( 'the seller', 'wwmd' ) ) ); ?></p>
					<?php if ( $wd_bk ) : ?>
						<div class="notice notice--ok" role="status"><?php echo wd_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( 'pending' === $wd_bk->status ? __( 'We are still waiting for your payment to come through. This page updates once it does.', 'wwmd' ) : __( 'Paid! Your receipt is in the chat & in your dashboard.', 'wwmd' ) ); ?></span></div>
						<a class="btn btn--ink btn--block" href="<?php echo esc_url( wd_account_url( 'services' ) ); ?>"><?php esc_html_e( 'See my orders', 'wwmd' ); ?></a>
					<?php elseif ( ! $wd_live ) : ?>
						<p class="notice notice--warn"><?php esc_html_e( 'This listing is paused right now.', 'wwmd' ); ?></p>
					<?php elseif ( ! $wd_me->exists() ) : ?>
						<p class="muted"><?php esc_html_e( 'Create a free account to chat with the seller. It takes a minute.', 'wwmd' ); ?></p>
						<a class="btn btn--marigold btn--block" href="<?php echo esc_url( wd_url( 'sign-in', array( 'tab' => 'join', 'as' => 'user', 'redirect_to' => rawurlencode( get_permalink( $wd_lid ) ) ) ) ); ?>"><?php echo wd_icon( 'chat', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Create an account to chat', 'wwmd' ); ?></a>
						<p class="muted center"><?php esc_html_e( 'Have an account?', 'wwmd' ); ?> <a href="<?php echo esc_url( wd_url( 'sign-in', array( 'redirect_to' => rawurlencode( get_permalink( $wd_lid ) ) ) ) ); ?>"><?php esc_html_e( 'Sign in', 'wwmd' ); ?></a></p>
					<?php elseif ( $wd_own ) : ?>
						<p class="muted"><?php esc_html_e( 'This is your listing. Edit it from your seller dashboard.', 'wwmd' ); ?></p>
						<a class="btn btn--ink btn--block" href="<?php echo esc_url( wd_account_url( 'partner', array( 'part' => 'listing', 'lid' => $wd_lid ) ) ); ?>"><?php esc_html_e( 'Edit listing', 'wwmd' ); ?></a>
					<?php else : ?>
						<?php get_template_part( 'template-parts/chat', null, $wd_chat ); ?>
					<?php endif; ?>
					<a class="linkish book-box__help" href="<?php echo esc_url( wd_whatsapp( sprintf( /* translators: %s listing */ __( 'Hello! I have a question about “%s” on the services page.', 'wwmd' ), get_the_title( $wd_lid ) ) ) ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'whatsapp', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Questions? Chat with us', 'wwmd' ); ?></a>
				</div>
			</aside>
		</div>
	</div>
</section>
<?php if ( $wd_live && ! $wd_own && ! $wd_bk ) : ?>
<div class="book-bar" data-book-bar>
	<?php if ( $wd_ask ) : ?>
		<p><b><?php esc_html_e( 'Contact for price', 'wwmd' ); ?></b></p>
	<?php else : ?>
		<p><small><?php esc_html_e( 'From', 'wwmd' ); ?></small> <b><?php echo esc_html( wd_money( $wd_l['price'] ) ); ?></b> / <?php echo esc_html( $wd_u[0] ); ?></p>
	<?php endif; ?>
	<a class="btn btn--marigold" href="#book"><?php echo wd_icon( 'chat', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Chat with seller', 'wwmd' ); ?></a>
</div>
<?php endif; ?>
<?php if ( $wd_more->have_posts() ) : ?>
<section class="section section--alt">
	<div class="wrap">
		<div class="section-head"><div><h2><?php esc_html_e( 'More like this', 'wwmd' ); ?></h2></div><a class="link-arrow" href="<?php echo esc_url( wd_url( 'services', array( 'sector' => $wd_l['sector'] ) ) . '#listings' ); ?>"><?php esc_html_e( 'See all', 'wwmd' ); ?><?php echo wd_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></div>
		<div class="ig-grid">
			<?php
			foreach ( $wd_more->posts as $wd_p ) {
				get_template_part(
					'template-parts/card-listing',
					null,
					array(
						'id'   => $wd_p->ID,
						'view' => 'tile',
					)
				);
			}
			?>
		</div>
	</div>
</section>
<?php endif; ?>
<?php
get_footer();
