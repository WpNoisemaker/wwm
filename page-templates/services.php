<?php
/**
 * Template Name: Services marketplace
 *
 * Stays, rides, honeymoon trips & asoebi listed by approved sellers, shown like a photo app (1.5):
 * sellers in a stories row, listings as posts (or a photo grid with ?view=grid). Couples & guests book
 * & chat with the seller, who sends an invoice or a payment link (no direct booking, no escrow),
 * then pay the seller.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
get_header();
$wd_u    = wp_get_current_user();
$wd_wid  = is_user_logged_in() ? wd_current_wedding() : 0;
$wd_secs = wd_sectors();
// phpcs:disable WordPress.Security.NonceVerification
$wd_sec  = isset( $_GET['sector'] ) ? sanitize_key( wp_unslash( $_GET['sector'] ) ) : '';
$wd_sec  = isset( $wd_secs[ $wd_sec ] ) ? $wd_sec : '';
$wd_city = isset( $_GET['city'] ) ? sanitize_text_field( wp_unslash( $_GET['city'] ) ) : '';
$wd_q    = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
$wd_view = isset( $_GET['view'] ) && 'grid' === $_GET['view'] ? 'grid' : 'post';
// Searching or filtering shows a photo grid (like an explore page), unless the person picked the feed.
if ( ! isset( $_GET['view'] ) && ( isset( $_GET['q'] ) && '' !== $_GET['q'] || ! empty( $_GET['city'] ) ) ) {
	$wd_view = 'grid';
}
// phpcs:enable
$wd_pg    = max( 1, (int) get_query_var( 'paged' ) );
$wd_list  = wd_listing_query(
	array(
		'sector' => $wd_sec,
		'city'   => $wd_city,
		'q'      => $wd_q,
		'paged'  => $wd_pg,
	)
);
$wd_count = wd_listing_counts();
$wd_base  = wd_url( 'services' );
$wd_keep  = array_filter(
	array(
		'city' => $wd_city,
		'q'    => $wd_q,
		'view' => 'grid' === $wd_view ? 'grid' : '',
	)
);
$wd_here  = array_filter( array_merge( $wd_keep, array( 'sector' => $wd_sec ) ) );
$wd_side  = 'post' === $wd_view ? array_slice( wd_story_sellers( 6 ), 0, 6 ) : array();
$wd_fee   = rtrim( rtrim( number_format( (float) wd_opt( 'market_fee' ), 1 ), '0' ), '.' ) . '%';
?>
<section class="page-hero page-hero--services">
	<div class="wrap market-hero">
		<div>
			<p class="eyebrow"><?php echo wd_icon( 'shield', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Services marketplace', 'wwmd' ); ?></p>
			<h1><?php esc_html_e( 'Stays, rides, honeymoons & asoebi from trusted sellers', 'wwmd' ); ?></h1>
			<p class="lead"><?php esc_html_e( 'Find a seller you like, chat with them right here & they send you an invoice or a payment link.', 'wwmd' ); ?></p>
			<form class="filters filters--slim filters--market" role="search" method="get" action="<?php echo esc_url( $wd_base ); ?>">
				<?php if ( $wd_sec ) : ?><input type="hidden" name="sector" value="<?php echo esc_attr( $wd_sec ); ?>"><?php endif; ?>
				<div class="filters__search"><?php echo wd_icon( 'search', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><label class="screen-reader-text" for="m-q"><?php esc_html_e( 'Search services', 'wwmd' ); ?></label><input id="m-q" type="search" name="q" value="<?php echo esc_attr( $wd_q ); ?>" placeholder="<?php esc_attr_e( 'Search stays, cars, trips & asoebi', 'wwmd' ); ?>"></div>
				<div class="filters__more"><span class="select"><label class="screen-reader-text" for="m-city"><?php esc_html_e( 'City', 'wwmd' ); ?></label><select id="m-city" name="city">
					<option value=""><?php esc_html_e( 'Any city', 'wwmd' ); ?></option>
					<?php foreach ( wd_listing_cities() as $wd_c ) : ?>
						<option value="<?php echo esc_attr( $wd_c ); ?>" <?php selected( $wd_city, $wd_c ); ?>><?php echo esc_html( $wd_c ); ?></option>
					<?php endforeach; ?>
				</select></span></div>
				<button class="btn btn--ink" type="submit"><?php echo wd_icon( 'search', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Search', 'wwmd' ); ?></span></button>
			</form>
			<?php if ( ! wd_is_partner() ) : ?>
				<p class="market-hero__sell"><a class="btn btn--brand" href="<?php echo esc_url( wd_seller_join_url() ); ?>"><?php echo wd_icon( 'store', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Become a seller', 'wwmd' ); ?></a><span><?php esc_html_e( 'Rent out stays & cars, sell trips or asoebi. Listing is free.', 'wwmd' ); ?></span></p>
			<?php endif; ?>
		</div>
		<ol class="escrow-steps" aria-label="<?php esc_attr_e( 'How buying works', 'wwmd' ); ?>">
			<li><span><?php echo wd_icon( 'chat', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><b><?php esc_html_e( 'Chat with the seller', 'wwmd' ); ?></b><small><?php esc_html_e( 'Ask about your date, the price & anything else, right here on the site.', 'wwmd' ); ?></small></li>
			<li><span><?php echo wd_icon( 'feed', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><b><?php esc_html_e( 'Get an invoice or payment link', 'wwmd' ); ?></b><small><?php esc_html_e( 'The seller sends it in the chat, with every cost listed.', 'wwmd' ); ?></small></li>
			<li><span><?php echo wd_icon( 'cash', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><b><?php esc_html_e( 'Pay the seller', 'wwmd' ); ?></b><small><?php esc_html_e( 'Pay the link by card, bank or mobile money, or pay the invoice the way it says.', 'wwmd' ); ?></small></li>
			<li><span><?php echo wd_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><b><?php esc_html_e( 'Get your receipt', 'wwmd' ); ?></b><small><?php esc_html_e( 'It stays in the chat. After the service, leave a review.', 'wwmd' ); ?></small></li>
		</ol>
	</div>
</section>

<section class="section section--tight ig-market" id="listings">
	<div class="wrap">
		<?php // Phones: an app-style search bar with a filter sheet, in place of the big header. ?>
		<div class="ig-top">
			<form class="ig-search" role="search" method="get" action="<?php echo esc_url( $wd_base . '#listings' ); ?>">
				<?php if ( $wd_sec ) : ?><input type="hidden" name="sector" value="<?php echo esc_attr( $wd_sec ); ?>"><?php endif; ?>
				<?php if ( $wd_city ) : ?><input type="hidden" name="city" value="<?php echo esc_attr( $wd_city ); ?>"><?php endif; ?>
				<?php echo wd_icon( 'search', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<label class="screen-reader-text" for="ig-q"><?php esc_html_e( 'Search services', 'wwmd' ); ?></label>
				<input id="ig-q" type="search" name="q" value="<?php echo esc_attr( $wd_q ); ?>" placeholder="<?php esc_attr_e( 'Search', 'wwmd' ); ?>" enterkeyhint="search">
			</form>
			<details class="ig-filter">
				<summary aria-label="<?php esc_attr_e( 'Filters', 'wwmd' ); ?>"><?php echo wd_icon( 'settings', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php if ( $wd_city || $wd_sec ) : ?><i class="ig-filter__dot"></i><?php endif; ?></summary>
				<form class="ig-sheet" method="get" action="<?php echo esc_url( $wd_base . '#listings' ); ?>">
					<p class="ig-sheet__grab" aria-hidden="true"></p>
					<h2><?php esc_html_e( 'Filters', 'wwmd' ); ?></h2>
					<?php if ( $wd_q ) : ?><input type="hidden" name="q" value="<?php echo esc_attr( $wd_q ); ?>"><?php endif; ?>
					<fieldset class="ig-sheet__group">
						<legend><?php esc_html_e( 'Service', 'wwmd' ); ?></legend>
						<label class="ig-pick"><input type="radio" name="sector" value="" <?php checked( $wd_sec, '' ); ?>><span><?php esc_html_e( 'All', 'wwmd' ); ?></span></label>
						<?php foreach ( $wd_secs as $wd_k => $wd_s ) : ?>
							<label class="ig-pick"><input type="radio" name="sector" value="<?php echo esc_attr( $wd_k ); ?>" <?php checked( $wd_sec, $wd_k ); ?>><span><?php echo esc_html( is_array( $wd_s ) ? $wd_s[0] : $wd_s ); ?></span></label>
						<?php endforeach; ?>
					</fieldset>
					<label class="field"><span><?php esc_html_e( 'City', 'wwmd' ); ?></span><span class="select"><select name="city">
						<option value=""><?php esc_html_e( 'Any city', 'wwmd' ); ?></option>
						<?php foreach ( wd_listing_cities() as $wd_c ) : ?>
							<option value="<?php echo esc_attr( $wd_c ); ?>" <?php selected( $wd_city, $wd_c ); ?>><?php echo esc_html( $wd_c ); ?></option>
						<?php endforeach; ?>
					</select></span></label>
					<fieldset class="ig-sheet__group">
						<legend><?php esc_html_e( 'Show as', 'wwmd' ); ?></legend>
						<label class="ig-pick"><input type="radio" name="view" value="grid" <?php checked( $wd_view, 'grid' ); ?>><span><?php echo wd_icon( 'grid', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Photo grid', 'wwmd' ); ?></span></label>
						<label class="ig-pick"><input type="radio" name="view" value="post" <?php checked( $wd_view, 'post' ); ?>><span><?php echo wd_icon( 'feed', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Posts', 'wwmd' ); ?></span></label>
					</fieldset>
					<div class="ig-sheet__acts">
						<a class="btn btn--ghost" href="<?php echo esc_url( $wd_base . '#listings' ); ?>"><?php esc_html_e( 'Clear', 'wwmd' ); ?></a>
						<button class="btn btn--ink" type="submit"><?php esc_html_e( 'Show results', 'wwmd' ); ?></button>
					</div>
				</form>
			</details>
		</div>
		<p class="ig-how"><?php echo wd_icon( 'chat', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Chat with a seller, get an invoice or a payment link, pay the seller & keep your receipt.', 'wwmd' ); ?></span></p>
		<?php echo wd_stories_html( wd_story_sellers(), true ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php if ( ! wd_is_partner() ) : ?>
			<aside class="sell-cta" aria-label="<?php esc_attr_e( 'For sellers', 'wwmd' ); ?>">
				<span class="sell-cta__icon"><?php echo wd_icon( 'store', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				<div class="sell-cta__text">
					<h2><?php esc_html_e( 'Sell to couples & guests planning weddings', 'wwmd' ); ?></h2>
					<p><?php esc_html_e( 'List your stays, cars, trips or asoebi for free. Couples chat with you here & you send them an invoice or a payment link.', 'wwmd' ); ?></p>
				</div>
				<a class="btn btn--marigold sell-cta__btn" href="<?php echo esc_url( wd_seller_join_url() ); ?>"><?php esc_html_e( 'Become a seller', 'wwmd' ); ?><?php echo wd_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			</aside>
		<?php endif; ?>
		<div class="ig-bar">
			<nav class="ig-chips" aria-label="<?php esc_attr_e( 'Service types', 'wwmd' ); ?>">
				<a class="ig-chip<?php echo $wd_sec ? '' : ' is-on'; ?>" href="<?php echo esc_url( add_query_arg( $wd_keep, $wd_base ) . '#listings' ); ?>"><?php esc_html_e( 'All', 'wwmd' ); ?> <small><?php echo (int) array_sum( $wd_count ); ?></small></a>
				<?php foreach ( $wd_secs as $wd_k => $wd_s ) : ?>
					<a class="ig-chip<?php echo $wd_sec === $wd_k ? ' is-on' : ''; ?>" href="<?php echo esc_url( add_query_arg( array_merge( $wd_keep, array( 'sector' => $wd_k ) ), $wd_base ) . '#listings' ); ?>"><?php echo wd_icon( $wd_s[1], 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $wd_s[0] ); ?> <small><?php echo (int) ( $wd_count[ $wd_k ] ?? 0 ); ?></small></a>
				<?php endforeach; ?>
			</nav>
			<p class="ig-views" role="group" aria-label="<?php esc_attr_e( 'Show as', 'wwmd' ); ?>">
				<a class="ig-view<?php echo 'post' === $wd_view ? ' is-on' : ''; ?>" href="<?php echo esc_url( add_query_arg( array_diff_key( $wd_here, array( 'view' => 1 ) ), $wd_base ) . '#listings' ); ?>" aria-label="<?php esc_attr_e( 'Show as posts', 'wwmd' ); ?>"<?php echo 'post' === $wd_view ? ' aria-current="true"' : ''; ?>><?php echo wd_icon( 'feed', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
				<a class="ig-view<?php echo 'grid' === $wd_view ? ' is-on' : ''; ?>" href="<?php echo esc_url( add_query_arg( array_merge( $wd_here, array( 'view' => 'grid' ) ), $wd_base ) . '#listings' ); ?>" aria-label="<?php esc_attr_e( 'Show as a photo grid', 'wwmd' ); ?>"<?php echo 'grid' === $wd_view ? ' aria-current="true"' : ''; ?>><?php echo wd_icon( 'grid', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			</p>
		</div>
		<?php if ( $wd_list->have_posts() ) : ?>
			<div class="ig-layout<?php echo $wd_side ? ' has-side' : ''; ?>">
				<div class="ig-main">
					<div class="<?php echo 'grid' === $wd_view ? 'ig-grid' : 'ig-feed'; ?>" data-grid>
						<?php
						foreach ( $wd_list->posts as $wd_p ) {
							get_template_part(
								'template-parts/card-listing',
								null,
								array(
									'id'   => $wd_p->ID,
									'view' => 'grid' === $wd_view ? 'tile' : 'post',
								)
							);
						}
						?>
					</div>
					<?php if ( $wd_list->max_num_pages > $wd_pg ) : ?>
						<div class="center load-more"><a class="btn btn--ghost" href="<?php echo esc_url( add_query_arg( $wd_here, trailingslashit( $wd_base ) . 'page/' . ( $wd_pg + 1 ) . '/' ) ); ?>" data-load-more><?php esc_html_e( 'Show more', 'wwmd' ); ?></a></div>
					<?php endif; ?>
				</div>
				<?php if ( $wd_side ) : ?>
					<aside class="ig-side" aria-label="<?php esc_attr_e( 'Sellers to follow', 'wwmd' ); ?>">
						<?php if ( $wd_u->exists() ) : ?>
							<div class="ig-side__me"><span class="ig-ring ig-ring--plain" style="--s:44px"><span class="ig-ring__pic"><?php echo wd_avatar( $wd_u->display_name ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span></span><span><b><?php echo esc_html( $wd_u->display_name ); ?></b><a href="<?php echo esc_url( wd_account_url( 'services' ) ); ?>"><?php esc_html_e( 'My bookings & chats', 'wwmd' ); ?></a></span></div>
						<?php endif; ?>
						<h2 class="ig-side__title"><?php esc_html_e( 'Sellers on the marketplace', 'wwmd' ); ?></h2>
						<ul class="ig-side__list">
							<?php
							foreach ( $wd_side as $wd_sp ) :
								$wd_spp = wd_partner( $wd_sp );
								$wd_sps = wd_sectors()[ $wd_spp['sector'] ] ?? array( '' );
								?>
								<li><?php echo wd_seller_ring( $wd_sp, 40, 'a' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><a href="<?php echo esc_url( wd_partner_url( $wd_sp ) ); ?>"><b><?php echo esc_html( $wd_spp['business'] ); ?></b><?php echo wd_partner_badge( $wd_sp, false ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a><small><?php echo esc_html( trim( $wd_sps[0] . ' · ' . $wd_spp['city'], ' ·' ) ); ?></small></span><a class="ig-side__go" href="<?php echo esc_url( wd_partner_url( $wd_sp ) ); ?>"><?php esc_html_e( 'View', 'wwmd' ); ?></a></li>
							<?php endforeach; ?>
						</ul>
						<a class="ig-side__join" href="<?php echo esc_url( is_user_logged_in() ? wd_account_url( 'partner' ) : wd_url( 'sign-in', array( 'tab' => 'join', 'as' => 'seller' ) ) ); ?>"><?php echo wd_icon( 'plus', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Sell on the marketplace', 'wwmd' ); ?></a>
						<?php if ( is_user_logged_in() && wd_saved_listings() ) : ?>
							<a class="ig-side__join" href="<?php echo esc_url( wd_account_url( 'services' ) . '#saved' ); ?>"><?php echo wd_icon( 'bookmark', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Saved', 'wwmd' ); ?></a>
						<?php endif; ?>
					</aside>
				<?php endif; ?>
			</div>
		<?php else : ?>
			<div class="empty"><?php echo wd_art( 'house' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><h2><?php esc_html_e( 'Nothing here yet', 'wwmd' ); ?></h2><p><?php esc_html_e( 'Try another city or type, or ask us below & we will find it for you.', 'wwmd' ); ?></p></div>
		<?php endif; ?>
	</div>
</section>

<section class="section section--alt">
	<div class="wrap">
		<div class="section-head">
			<div>
				<p class="eyebrow"><?php esc_html_e( 'Done for you', 'wwmd' ); ?></p>
				<h2><?php esc_html_e( 'Need something else?', 'wwmd' ); ?></h2>
			</div>
		</div>
		<div class="svc-grid svc-grid--3">
			<?php foreach ( array_intersect_key( wd_services(), array_flip( array( 'livestream', 'qr', 'other' ) ) ) as $wd_k => $wd_s ) : ?>
				<article class="svc-card tone-<?php echo esc_attr( $wd_s['tone'] ); ?>" id="<?php echo esc_attr( $wd_k ); ?>">
					<span class="svc-card__art"><?php echo wd_art( $wd_s['art'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<h3><?php echo esc_html( $wd_s['title'] ); ?></h3>
					<p><?php echo esc_html( $wd_s['text'] ); ?></p>
					<p class="svc-card__from"><?php echo esc_html( $wd_s['from'] ); ?></p>
					<button class="btn btn--ink" type="button" data-service="<?php echo esc_attr( $wd_k ); ?>" data-service-title="<?php echo esc_attr( $wd_s['title'] ); ?>"><?php esc_html_e( 'Ask for this', 'wwmd' ); ?></button>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section section--cta">
	<div class="wrap cta-band">
		<div class="cta-band__text">
			<p class="eyebrow"><?php esc_html_e( 'For hosts, car hire firms, travel agents & asoebi sellers', 'wwmd' ); ?></p>
			<h2><?php esc_html_e( 'List your apartments, cars, trips or asoebi', 'wwmd' ); ?></h2>
			<p><?php echo esc_html( sprintf( /* translators: %s commission */ __( 'Reach couples & guests planning weddings across Africa & abroad, with your own profile page, reviews & chat. Listing is free & we check every seller. We take %s of each booking & pay you once the service is done.', 'wwmd' ), $wd_fee ) ); ?></p>
			<div class="btn-row">
				<a class="btn btn--marigold btn--lg" href="<?php echo esc_url( is_user_logged_in() ? wd_account_url( 'partner' ) : wd_url( 'sign-in', array( 'tab' => 'join', 'as' => 'seller' ) ) ); ?>"><?php esc_html_e( 'Become a seller', 'wwmd' ); ?></a>
				<a class="btn btn--ghost-light btn--lg" href="<?php echo esc_url( wd_whatsapp( __( 'Hello! I would like to list my services on Wedding Websites & More.', 'wwmd' ) ) ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'whatsapp', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Ask us first', 'wwmd' ); ?></a>
			</div>
		</div>
		<span class="cta-band__art" aria-hidden="true"><?php echo wd_art( 'car' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
	</div>
</section>

<dialog class="pay-dialog" data-service-dialog>
	<form class="form" data-service-form>
		<button type="button" class="pay-dialog__x" data-close aria-label="<?php esc_attr_e( 'Close', 'wwmd' ); ?>"><?php echo wd_icon( 'close', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
		<p class="eyebrow"><?php esc_html_e( 'Request', 'wwmd' ); ?></p>
		<h2 class="pay-dialog__title" data-service-name></h2>
		<input type="hidden" name="service" value="">
		<input type="hidden" name="wedding" value="<?php echo (int) $wd_wid; ?>">
		<div class="field-row">
			<label class="field"><span><?php esc_html_e( 'Your name', 'wwmd' ); ?></span><input name="name" required value="<?php echo esc_attr( $wd_u->exists() ? $wd_u->display_name : '' ); ?>"></label>
			<label class="field"><span><?php esc_html_e( 'WhatsApp number', 'wwmd' ); ?></span><input name="phone" type="tel" required value="<?php echo esc_attr( $wd_u->exists() ? get_user_meta( $wd_u->ID, 'wd_phone', true ) : '' ); ?>"></label>
		</div>
		<label class="field"><span><?php esc_html_e( 'Email', 'wwmd' ); ?></span><input name="email" type="email" value="<?php echo esc_attr( $wd_u->exists() ? $wd_u->user_email : '' ); ?>"></label>
		<label class="field"><span><?php esc_html_e( 'Tell us more: dates, city, how many people, budget', 'wwmd' ); ?></span><textarea name="details" rows="4"></textarea></label>
		<button class="btn btn--ink btn--block" type="submit"><?php esc_html_e( 'Send & continue on WhatsApp', 'wwmd' ); ?></button>
		<p class="form__msg" data-msg role="status"></p>
	</form>
</dialog>
<?php
get_footer();
