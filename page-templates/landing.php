<?php
/**
 * Template Name: Landing page (for ads)
 *
 * One page that explains the three sides of the site: wedding pages, the marketplace & the community.
 * Made for sharing & ad campaigns: it is not in any menu, & search engines are asked not to list it.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
get_header();
$wd_join  = is_user_logged_in() ? wd_account_url( 'new' ) : wd_url( 'sign-in', array( 'tab' => 'join' ) );
$wd_feats = array(
	array( 'calendar', 'butter', __( 'Invitation & programs', 'wwmd' ), __( 'Introduction, traditional, nikkah, white wedding & after party, each with its own date, venue & dress code.', 'wwmd' ) ),
	array( 'qr', 'mint', __( 'RSVP & entry passes', 'wwmd' ), __( 'Guests reply in one tap & get a QR pass for the gate, so only invited guests come in.', 'wwmd' ) ),
	array( 'gift', 'coral', __( 'Cash & gift registry', 'wwmd' ), __( 'Guests send cash gifts or buy from your wish list, from anywhere in the world.', 'wwmd' ) ),
	array( 'cash', 'lavender', __( 'Spray money online', 'wwmd' ), __( 'Guests spray you during the party, even from London, Houston or Toronto.', 'wwmd' ) ),
	array( 'live', 'sky', __( 'Livestream', 'wwmd' ), __( 'Family who can’t travel watch the day live, right on your page.', 'wwmd' ) ),
	array( 'camera', 'butter', __( 'Photos & love notes', 'wwmd' ), __( 'Guests share their photos & leave love notes on your wall. You keep every one.', 'wwmd' ) ),
	array( 'heart', 'mint', __( 'Love story & bridal party', 'wwmd' ), __( 'Tell how you met, show off your squad & play a fun quiz with your guests.', 'wwmd' ) ),
	array( 'chart', 'coral', __( 'The Buzz Chart', 'wwmd' ), __( 'Pages climb the weekly chart as guests visit, RSVP & send love. Can yours reach No. 1?', 'wwmd' ) ),
	array( 'crown', 'lavender', __( 'Beautiful designs', 'wwmd' ), __( 'Start free in our colours, or pick a Premium design with your own colours & fonts.', 'wwmd' ) ),
);
?>
<section class="page-hero lp-hero">
	<div class="wrap lp-hero__grid">
		<div class="lp-hero__text">
			<p class="eyebrow"><?php esc_html_e( 'For African weddings, at home & abroad', 'wwmd' ); ?></p>
			<h1><?php esc_html_e( 'Your wedding page, a marketplace & a community, in one place', 'wwmd' ); ?></h1>
			<p class="lead"><?php esc_html_e( 'Make a beautiful page for your wedding, book stays, rides, trips & asoebi safely, & share the love with guests everywhere. Start free in five minutes.', 'wwmd' ); ?></p>
			<div class="btn-row">
				<a class="btn btn--brand btn--lg" href="<?php echo esc_url( $wd_join ); ?>"><?php esc_html_e( 'Create a free wedding page', 'wwmd' ); ?></a>
				<a class="btn btn--ghost btn--lg" href="<?php echo esc_url( wd_url( 'services' ) ); ?>"><?php esc_html_e( 'Browse the marketplace', 'wwmd' ); ?></a>
			</div>
			<nav class="lp-jump" aria-label="<?php esc_attr_e( 'On this page', 'wwmd' ); ?>">
				<a href="#pages"><?php echo wd_icon( 'heart', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Wedding pages', 'wwmd' ); ?></a>
				<a href="#market"><?php echo wd_icon( 'store', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Marketplace', 'wwmd' ); ?></a>
				<a href="#community"><?php echo wd_icon( 'users', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Community', 'wwmd' ); ?></a>
			</nav>
		</div>
		<ul class="lp-trio" aria-label="<?php esc_attr_e( 'The three sides of Wedding Websites & More', 'wwmd' ); ?>">
			<li class="tone-lavender"><a href="#pages"><span class="lp-trio__art"><?php echo wd_art( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><b><?php esc_html_e( 'Wedding pages', 'wwmd' ); ?></b><small><?php esc_html_e( 'Invite, RSVP, gifts & spraying', 'wwmd' ); ?></small></a></li>
			<li class="tone-butter"><a href="#market"><span class="lp-trio__art"><?php echo wd_art( 'car' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><b><?php esc_html_e( 'Marketplace', 'wwmd' ); ?></b><small><?php esc_html_e( 'Stays, rides, trips & asoebi', 'wwmd' ); ?></small></a></li>
			<li class="tone-mint"><a href="#community"><span class="lp-trio__art"><?php echo wd_art( 'notes' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><b><?php esc_html_e( 'Community', 'wwmd' ); ?></b><small><?php esc_html_e( 'Anonymous posts, love notes & the Buzz Chart', 'wwmd' ); ?></small></a></li>
		</ul>
	</div>
</section>

<section class="section" id="pages">
	<div class="wrap">
		<div class="section-head section-head--center">
			<p class="eyebrow"><?php echo wd_icon( 'heart', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Wedding pages', 'wwmd' ); ?></p>
			<h2><?php esc_html_e( 'One link for every part of your wedding', 'wwmd' ); ?></h2>
			<p class="section-head__sub"><?php esc_html_e( 'Put it in your bio, your WhatsApp status & on your invitation card. Everything guests need is there.', 'wwmd' ); ?></p>
		</div>
		<div class="feature-grid lp-feats">
			<?php foreach ( $wd_feats as $wd_f ) : ?>
				<div class="feature tone-<?php echo esc_attr( $wd_f[1] ); ?>">
					<span class="feature__icon"><?php echo wd_icon( $wd_f[0], 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<h3><?php echo esc_html( $wd_f[2] ); ?></h3>
					<p><?php echo esc_html( $wd_f[3] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section section--plans section--tight">
	<div class="wrap">
		<div class="section-head section-head--center">
			<p class="eyebrow"><?php esc_html_e( 'Plans', 'wwmd' ); ?></p>
			<h2><?php esc_html_e( 'Start free. Move up when you are ready', 'wwmd' ); ?></h2>
		</div>
		<?php get_template_part( 'template-parts/plans' ); ?>
	</div>
</section>

<section class="section section--alt" id="market">
	<div class="wrap">
		<div class="section-head section-head--center">
			<p class="eyebrow"><?php echo wd_icon( 'store', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Marketplace', 'wwmd' ); ?></p>
			<h2><?php esc_html_e( 'Wedding services, one chat away', 'wwmd' ); ?></h2>
			<p class="section-head__sub"><?php esc_html_e( 'Apartments for out-of-town guests, cars for the convoy, honeymoon trips & asoebi for the whole family, from sellers we check.', 'wwmd' ); ?></p>
		</div>
		<ol class="escrow-steps lp-steps" aria-label="<?php esc_attr_e( 'How buying works', 'wwmd' ); ?>">
			<li><span><?php echo wd_icon( 'chat', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><b><?php esc_html_e( 'Chat with the seller', 'wwmd' ); ?></b><small><?php esc_html_e( 'Ask about your date, the price & anything else, right here on the site.', 'wwmd' ); ?></small></li>
			<li><span><?php echo wd_icon( 'feed', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><b><?php esc_html_e( 'Get an invoice or payment link', 'wwmd' ); ?></b><small><?php esc_html_e( 'The seller sends it in the chat, with every cost listed.', 'wwmd' ); ?></small></li>
			<li><span><?php echo wd_icon( 'cash', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><b><?php esc_html_e( 'Pay the seller', 'wwmd' ); ?></b><small><?php esc_html_e( 'Pay the link by card, bank or mobile money, or pay the invoice the way it says.', 'wwmd' ); ?></small></li>
			<li><span><?php echo wd_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><b><?php esc_html_e( 'Get your receipt', 'wwmd' ); ?></b><small><?php esc_html_e( 'It stays in the chat. After the service, leave a review.', 'wwmd' ); ?></small></li>
		</ol>
		<div class="btn-row lp-center">
			<a class="btn btn--ink btn--lg" href="<?php echo esc_url( wd_url( 'services' ) ); ?>"><?php esc_html_e( 'Browse the marketplace', 'wwmd' ); ?></a>
			<a class="btn btn--brand btn--lg" href="<?php echo esc_url( wd_seller_join_url() ); ?>"><?php echo wd_icon( 'store', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Become a seller', 'wwmd' ); ?></a>
		</div>
	</div>
</section>
<?php get_template_part( 'template-parts/excerpt-market' ); ?>

<div id="community" class="lp-anchor"></div>
<section class="section section--chart">
	<div class="wrap">
		<div class="section-head section-head--center">
			<p class="eyebrow"><?php echo wd_icon( 'users', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Community', 'wwmd' ); ?></p>
			<h2><?php esc_html_e( 'Where African weddings get talked about', 'wwmd' ); ?></h2>
			<p class="section-head__sub"><?php esc_html_e( 'Ask & share anonymously in the community, & every week the Buzz Chart shows the weddings everyone is talking about.', 'wwmd' ); ?></p>
		</div>
		<?php get_template_part( 'template-parts/chart', null, array( 'n' => 5 ) ); ?>
		<div class="btn-row lp-center">
			<a class="btn btn--ghost btn--lg" href="<?php echo esc_url( wd_url( 'rankings' ) ); ?>"><?php esc_html_e( 'See the full Buzz Chart', 'wwmd' ); ?></a>
		</div>
	</div>
</section>
<?php get_template_part( 'template-parts/excerpt-talk' ); ?>
<?php get_template_part( 'template-parts/excerpt-community', null, array( 'title' => __( 'Love notes & photos from guests', 'wwmd' ) ) ); ?>

<?php get_template_part( 'template-parts/faq' ); ?>

<section class="section section--cta">
	<div class="wrap cta-band">
		<div class="cta-band__text">
			<h2><?php esc_html_e( 'Your wedding deserves its own page', 'wwmd' ); ?></h2>
			<p><?php esc_html_e( 'Make it free in five minutes. Share the link, & let the love (& the spraying) begin.', 'wwmd' ); ?></p>
			<div class="btn-row">
				<a class="btn btn--marigold btn--lg" href="<?php echo esc_url( $wd_join ); ?>"><?php esc_html_e( 'Create a free wedding page', 'wwmd' ); ?></a>
				<a class="btn btn--ghost-light btn--lg" href="<?php echo esc_url( wd_whatsapp( __( 'Hello! I saw your ad & I have a question about wedding pages.', 'wwmd' ) ) ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'whatsapp', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Ask us on WhatsApp', 'wwmd' ); ?></a>
			</div>
		</div>
		<div class="cta-band__art" aria-hidden="true"><?php echo wd_art( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo wd_art( 'notes', 'cta-band__notes' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
	</div>
</section>
<?php
get_footer();
