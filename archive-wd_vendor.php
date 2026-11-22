<?php
/**
 * Vendor directory.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
get_header();
$wd_types = get_terms( array( 'taxonomy' => 'wd_vtype', 'hide_empty' => true ) );
$wd_cur   = is_tax( 'wd_vtype' ) ? get_queried_object() : null;
global $wp_query;
?>
<section class="page-hero page-hero--vendors">
	<div class="wrap">
		<p class="eyebrow"><?php echo wd_icon( 'star', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Reviewed by real wedding guests', 'wwmd' ); ?></p>
		<h1><?php echo $wd_cur ? esc_html( $wd_cur->name ) : esc_html__( 'Wedding vendors', 'wwmd' ); ?></h1>
		<p class="lead"><?php esc_html_e( 'Every review here was left by a guest on the wedding page of a couple the vendor worked for. No paid reviews, ever.', 'wwmd' ); ?></p>
		<form class="filters filters--slim filters--one" role="search" method="get" action="<?php echo esc_url( $wd_cur ? get_term_link( $wd_cur ) : wd_url( 'vendors' ) ); ?>">
			<div class="filters__search"><?php echo wd_icon( 'search', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><label class="screen-reader-text" for="v-q"><?php esc_html_e( 'Search vendors', 'wwmd' ); ?></label><input id="v-q" type="search" name="q" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['q'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification ?>" placeholder="<?php esc_attr_e( 'Search vendors', 'wwmd' ); ?>"></div>
			<button class="btn btn--ink" type="submit"><?php echo wd_icon( 'search', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Search', 'wwmd' ); ?></span></button>
		</form>
	</div>
</section>
<section class="section section--tight">
	<div class="wrap">
		<nav class="tabs tabs--wrap" aria-label="<?php esc_attr_e( 'Vendor types', 'wwmd' ); ?>">
			<a class="tab<?php echo $wd_cur ? '' : ' is-on'; ?>" href="<?php echo esc_url( wd_url( 'vendors' ) ); ?>"><?php esc_html_e( 'All', 'wwmd' ); ?></a>
			<?php foreach ( is_array( $wd_types ) ? $wd_types : array() as $wd_t ) : ?>
				<a class="tab<?php echo $wd_cur && $wd_cur->term_id === $wd_t->term_id ? ' is-on' : ''; ?>" href="<?php echo esc_url( get_term_link( $wd_t ) ); ?>"><?php echo esc_html( $wd_t->name ); ?> <small><?php echo (int) $wd_t->count; ?></small></a>
			<?php endforeach; ?>
		</nav>
		<?php if ( have_posts() ) : ?>
			<div class="v-grid" data-grid>
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/card-vendor', null, array( 'id' => get_the_ID() ) );
				endwhile;
				?>
			</div>
			<?php if ( get_next_posts_page_link() && (int) get_query_var( 'paged', 1 ) < (int) $wp_query->max_num_pages ) : ?>
				<div class="center load-more"><a class="btn btn--ghost" href="<?php echo esc_url( get_next_posts_page_link() ); ?>" data-load-more><?php esc_html_e( 'Show more vendors', 'wwmd' ); ?></a></div>
			<?php endif; ?>
		<?php else : ?>
			<div class="empty"><?php echo wd_art( 'camera' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><h2><?php esc_html_e( 'No vendors found', 'wwmd' ); ?></h2></div>
		<?php endif; ?>
		<div class="vendor-cta">
			<div><h2><?php esc_html_e( 'Are you a wedding vendor?', 'wwmd' ); ?></h2><p><?php echo esc_html( sprintf( /* translators: 1: pro price 2: ad price */ __( 'You are listed free when a couple adds you to their page. Get verified for %1$s a month to reply to reviews & get leads on WhatsApp, or advertise on free wedding pages in your city from %2$s a week.', 'wwmd' ), wd_money( wd_opt( 'vendor_pro_price' ) ), wd_money( wd_opt( 'ad_week_price' ) ) ) ); ?></p></div>
			<a class="btn btn--marigold" href="<?php echo esc_url( wd_whatsapp( __( 'Hello! I am a wedding vendor & I would like to get verified or advertise.', 'wwmd' ) ) ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'whatsapp', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Talk to us', 'wwmd' ); ?></a>
		</div>
	</div>
</section>
<?php get_template_part( 'template-parts/excerpt-market', null, array( 'view' => 'tile', 'class' => 'section--alt' ) ); ?>
<?php
get_footer();
