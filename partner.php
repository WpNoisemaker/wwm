<?php
/**
 * A seller's public profile (/seller/{name}/), laid out like a photo-app profile (1.5): round picture
 * in the brand ring, numbers, short bio, Message & Share, then listings & portfolio as photo grids,
 * reviews & the full about. People reach it from any of the seller's posts.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_pid   = (int) get_query_var( 'wd_partner_id' );
$wd_pp    = wd_partner( $wd_pid );
$wd_secs  = wd_sectors();
$wd_s     = $wd_secs[ $wd_pp['sector'] ] ?? $wd_secs['stays'];
$wd_st    = wd_partner_stats( $wd_pid );
$wd_revs  = wd_partner_reviews( $wd_pid );
$wd_folio = wd_partner_portfolio( $wd_pid, $wd_pp );
$wd_me    = wp_get_current_user();
$wd_own   = $wd_me->exists() && $wd_me->ID === $wd_pid;
$wd_ls    = get_posts(
	array(
		'post_type'   => 'wd_listing',
		'post_status' => 'publish',
		'author'      => $wd_pid,
		'numberposts' => 24,
		'orderby'     => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
	)
);
get_header();
?>
<section class="section p-page">
	<div class="wrap">
		<nav class="crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'wwmd' ); ?>">
			<a href="<?php echo esc_url( wd_url( 'services' ) ); ?>"><?php esc_html_e( 'Services', 'wwmd' ); ?></a>
			<span aria-hidden="true">/</span>
			<a href="<?php echo esc_url( wd_url( 'services', array( 'sector' => $wd_pp['sector'] ) ) . '#listings' ); ?>"><?php echo esc_html( $wd_s[0] ); ?></a>
		</nav>

		<header class="ig-prof">
			<?php if ( $wd_pp['cover'] ) : ?>
				<div class="ig-prof__cover"><img src="<?php echo esc_url( wd_img_url( $wd_pp['cover'], 'wd-cover' ) ); ?>" alt=""></div>
			<?php endif; ?>
			<div class="ig-prof__top">
				<?php echo wd_seller_ring( $wd_pid, 150 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<div class="ig-prof__id">
					<div class="ig-prof__row">
						<h1><?php echo esc_html( $wd_pp['business'] ); ?><?php echo wd_partner_badge( $wd_pid, false ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
						<p class="ig-prof__btns">
							<?php if ( $wd_own ) : ?>
								<a class="btn btn--ink btn--sm" href="<?php echo esc_url( wd_account_url( 'partner', array( 'part' => 'profile' ) ) ); ?>"><?php esc_html_e( 'Edit profile', 'wwmd' ); ?></a>
							<?php else : ?>
								<a class="btn btn--marigold btn--sm" href="#chat"><?php echo wd_icon( 'chat', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Message', 'wwmd' ); ?></a>
							<?php endif; ?>
							<button class="btn btn--ghost btn--sm" type="button" data-share-url="<?php echo esc_url( wd_partner_url( $wd_pid ) ); ?>" data-share-title="<?php echo esc_attr( $wd_pp['business'] ); ?>"><?php echo wd_icon( 'send', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Share', 'wwmd' ); ?></button>
						</p>
					</div>
					<ul class="ig-prof__stats">
						<li><b><?php echo (int) $wd_st['listings']; ?></b> <?php echo esc_html( _n( 'listing', 'listings', (int) $wd_st['listings'], 'wwmd' ) ); ?></li>
						<li><b><?php echo (int) $wd_st['done']; ?></b> <?php echo esc_html( _n( 'order paid here', 'orders paid here', (int) $wd_st['done'], 'wwmd' ) ); ?></li>
						<li><?php if ( $wd_st['reviews'] ) : ?><b><?php echo esc_html( number_format_i18n( $wd_st['rating'], 1 ) ); ?></b> <?php echo wd_icon( 'star', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( sprintf( /* translators: %d reviews */ _n( '%d review', '%d reviews', $wd_st['reviews'], 'wwmd' ), $wd_st['reviews'] ) ); ?><?php else : ?><?php esc_html_e( 'No reviews yet', 'wwmd' ); ?><?php endif; ?></li>
					</ul>
					<p class="ig-prof__handle"><?php echo esc_html( wd_seller_handle( $wd_pid ) ); ?></p>
					<p class="ig-prof__meta">
						<span><?php echo wd_icon( $wd_s[1], 15 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $wd_s[0] ); ?></span>
						<?php if ( trim( $wd_pp['city'] . $wd_pp['country'] ) ) : ?><span><?php echo wd_icon( 'pin', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( trim( $wd_pp['city'] . ', ' . $wd_pp['country'], ', ' ) ); ?></span><?php endif; ?>
						<?php if ( $wd_pp['approved'] ) : ?><span><?php echo wd_icon( 'calendar', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( sprintf( /* translators: %s month & year */ __( 'Seller since %s', 'wwmd' ), wp_date( 'F Y', strtotime( $wd_pp['approved'] ) ) ) ); ?></span><?php endif; ?>
					</p>
					<?php if ( $wd_pp['about'] ) : ?><p class="ig-prof__bio"><?php echo esc_html( wp_trim_words( $wd_pp['about'], 34, '…' ) ); ?> <a href="#about"><?php esc_html_e( 'more', 'wwmd' ); ?></a></p><?php endif; ?>
				</div>
			</div>
		</header>

		<div class="p-grid">
			<div class="p-main">
				<nav class="ig-tabs" aria-label="<?php esc_attr_e( 'Profile sections', 'wwmd' ); ?>">
					<a href="#listings"><?php echo wd_icon( 'grid', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Listings', 'wwmd' ); ?></span></a>
					<?php if ( $wd_folio ) : ?><a href="#portfolio"><?php echo wd_icon( 'camera', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Portfolio', 'wwmd' ); ?></span></a><?php endif; ?>
					<a href="#reviews"><?php echo wd_icon( 'star', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Reviews', 'wwmd' ); ?></span></a>
					<a href="#about"><?php echo wd_icon( 'info', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'About', 'wwmd' ); ?></span></a>
				</nav>



				<section class="p-sec" id="listings">
					<h2><?php esc_html_e( 'Listings', 'wwmd' ); ?></h2>
					<?php if ( $wd_ls ) : ?>
						<div class="ig-grid">
							<?php
							foreach ( $wd_ls as $wd_p ) {
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
					<?php else : ?>
						<p class="empty-note"><?php esc_html_e( 'No live listings right now.', 'wwmd' ); ?></p>
					<?php endif; ?>
				</section>

				<?php if ( $wd_folio ) : ?>
					<section class="p-sec" id="portfolio">
						<h2><?php esc_html_e( 'Portfolio', 'wwmd' ); ?></h2>
						<div class="p-folio ig-grid">
							<?php foreach ( $wd_folio as $wd_ph ) : ?>
								<figure><img src="<?php echo esc_url( wd_img_url( $wd_ph[0], 'wd-card' ) ); ?>" alt="<?php echo esc_attr( $wd_ph[1] ); ?>" loading="lazy"><?php if ( $wd_ph[1] ) : ?><figcaption><?php echo esc_html( $wd_ph[1] ); ?></figcaption><?php endif; ?></figure>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endif; ?>



				<section class="p-sec" id="reviews">
					<h2><?php esc_html_e( 'Reviews', 'wwmd' ); ?></h2>
					<p class="hint"><?php esc_html_e( 'Only customers who booked & paid on this site can review, after their service is done. We check every review before it shows.', 'wwmd' ); ?></p>
					<?php if ( $wd_revs ) : ?>
						<ul class="p-reviews">
							<?php foreach ( $wd_revs as $wd_r ) : ?>
								<li>
									<p class="p-reviews__top"><?php echo wd_avatar( $wd_r->name ); // phpcs:ignore WordPress.Security.EscapeOutput ?><b><?php echo esc_html( $wd_r->name ); ?></b><?php echo wd_stars( (int) $wd_r->rating ); // phpcs:ignore WordPress.Security.EscapeOutput ?><time><?php echo esc_html( wd_ago( $wd_r->created ) ); ?></time></p>
									<?php if ( $wd_r->listing_id && get_post( (int) $wd_r->listing_id ) ) : ?><p class="p-reviews__for"><?php echo esc_html( get_the_title( (int) $wd_r->listing_id ) ); ?></p><?php endif; ?>
									<p><?php echo esc_html( $wd_r->text ); ?></p>
									<?php if ( $wd_r->reply ) : ?><blockquote class="p-reviews__reply"><b><?php echo esc_html( sprintf( /* translators: %s business */ __( 'Reply from %s', 'wwmd' ), $wd_pp['business'] ) ); ?></b><?php echo esc_html( $wd_r->reply ); ?></blockquote><?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<p class="empty-note"><?php esc_html_e( 'No reviews yet.', 'wwmd' ); ?></p>
					<?php endif; ?>
				</section>

				<section class="p-sec" id="about">
					<h2><?php echo esc_html( sprintf( /* translators: %s business */ __( 'About %s', 'wwmd' ), $wd_pp['business'] ) ); ?></h2>
					<?php if ( $wd_pp['about'] ) : ?><div class="prose"><?php echo wp_kses_post( wpautop( esc_html( $wd_pp['about'] ) ) ); ?></div><?php endif; ?>
					<?php if ( $wd_pp['hours'] || $wd_pp['areas'] ) : ?>
						<ul class="p-facts">
							<?php if ( $wd_pp['hours'] ) : ?><li><?php echo wd_icon( 'clock', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><b><?php esc_html_e( 'Open', 'wwmd' ); ?></b> <?php echo esc_html( $wd_pp['hours'] ); ?></span></li><?php endif; ?>
							<?php if ( $wd_pp['areas'] ) : ?><li><?php echo wd_icon( 'pin', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><b><?php esc_html_e( 'Covers', 'wwmd' ); ?></b> <?php echo esc_html( $wd_pp['areas'] ); ?></span></li><?php endif; ?>
						</ul>
					<?php endif; ?>
				</section>
			</div>

			<aside class="p-side">
				<div class="panel p-chat" id="chat">
					<?php if ( $wd_own ) : ?>
						<h2><?php esc_html_e( 'Your messages', 'wwmd' ); ?></h2>
						<p class="muted"><?php esc_html_e( 'Customers’ messages land in your seller dashboard.', 'wwmd' ); ?></p>
						<a class="btn btn--ink btn--block" href="<?php echo esc_url( wd_account_url( 'partner' ) . '#messages' ); ?>"><?php esc_html_e( 'Open my messages', 'wwmd' ); ?></a>
					<?php elseif ( ! $wd_me->exists() ) : ?>
						<h2><?php echo esc_html( sprintf( /* translators: %s business */ __( 'Chat with %s', 'wwmd' ), $wd_pp['business'] ) ); ?></h2>
						<p class="muted"><?php esc_html_e( 'Create a free account to chat. It takes a minute.', 'wwmd' ); ?></p>
						<a class="btn btn--marigold btn--block" href="<?php echo esc_url( wd_url( 'sign-in', array( 'tab' => 'join', 'as' => 'user', 'redirect_to' => rawurlencode( wd_partner_url( $wd_pid ) . '#chat' ) ) ) ); ?>"><?php esc_html_e( 'Create an account to chat', 'wwmd' ); ?></a>
						<p class="muted center"><?php esc_html_e( 'Have an account?', 'wwmd' ); ?> <a href="<?php echo esc_url( wd_url( 'sign-in', array( 'redirect_to' => rawurlencode( wd_partner_url( $wd_pid ) . '#chat' ) ) ) ); ?>"><?php esc_html_e( 'Sign in', 'wwmd' ); ?></a></p>
					<?php else : ?>
						<?php
						get_template_part(
							'template-parts/chat',
							null,
							array(
								'partner' => $wd_pid,
								'user'    => $wd_me->ID,
								'role'    => wd_chat_role( $wd_pid, $wd_me->ID ),
								/* translators: %s business */
								'title'   => sprintf( __( 'Chat with %s', 'wwmd' ), $wd_pp['business'] ),
							)
						);
						?>
					<?php endif; ?>
				</div>
				<div class="safe-note safe-note--sm">
					<span class="safe-note__icon"><?php echo wd_icon( 'chat', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<div>
						<h2><?php esc_html_e( 'How buying works', 'wwmd' ); ?></h2>
						<p><?php echo esc_html( sprintf( /* translators: %s business */ __( 'Chat with %s here. They send you an invoice or a payment link, & your receipt stays in the chat.', 'wwmd' ), $wd_pp['business'] ) ); ?></p>
					</div>
				</div>
			</aside>
		</div>
	</div>
</section>
<?php
get_footer();
