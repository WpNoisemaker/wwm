<?php
/**
 * One blog post.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
get_header();
the_post();
$wd_pid  = get_the_ID();
$wd_cat  = get_the_category( $wd_pid );
$wd_more = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => 3,
		'post__not_in'        => array( $wd_pid ),
		'ignore_sticky_posts' => true,
	)
);
?>
<article class="post">
	<header class="post__head">
		<div class="wrap wrap--post">
			<nav class="crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'wwmd' ); ?>"><a href="<?php echo esc_url( wd_url( 'blog' ) ); ?>"><?php esc_html_e( 'Blog', 'wwmd' ); ?></a><?php if ( $wd_cat ) : ?><span aria-hidden="true">/</span><a href="<?php echo esc_url( get_category_link( $wd_cat[0] ) ); ?>"><?php echo esc_html( $wd_cat[0]->name ); ?></a><?php endif; ?></nav>
			<h1 class="post__title"><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?><p class="lead"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
			<p class="post__meta"><span><?php echo wd_icon( 'calendar', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( get_the_date() ); ?></span><span><?php echo esc_html( sprintf( /* translators: %d minutes */ __( '%d min read', 'wwmd' ), wd_read_mins( $wd_pid ) ) ); ?></span></p>
		</div>
	</header>
	<div class="wrap wrap--post">
		<div class="post__cover"><?php echo wd_post_cover( $wd_pid, 'wd-cover' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		<?php $wd_cr = has_post_thumbnail( $wd_pid ) ? wp_get_attachment_caption( get_post_thumbnail_id( $wd_pid ) ) : ''; ?>
		<?php if ( $wd_cr ) : ?><p class="wd-credit post__credit"><?php echo esc_html( $wd_cr ); ?></p><?php endif; ?>
		<div class="prose post__body"><?php the_content(); ?></div>
		<div class="post__share">
			<b><?php esc_html_e( 'Share this', 'wwmd' ); ?></b>
			<a class="btn btn--wa btn--sm" href="<?php echo esc_url( 'https://wa.me/?text=' . rawurlencode( get_the_title() . ' ' . get_permalink() ) ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'whatsapp', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'WhatsApp', 'wwmd' ); ?></a>
			<button class="btn btn--ghost btn--sm" type="button" data-copy="<?php echo esc_url( get_permalink() ); ?>"><?php echo wd_icon( 'link', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Copy link', 'wwmd' ); ?></button>
		</div>
		<aside class="post__cta">
			<span class="post__cta-art" aria-hidden="true"><?php echo wd_art( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			<div>
				<h2><?php esc_html_e( 'Make your wedding page free', 'wwmd' ); ?></h2>
				<p><?php esc_html_e( 'RSVPs for every program, a cash & gift registry, love notes & photos, all on one link.', 'wwmd' ); ?></p>
				<a class="btn btn--marigold" href="<?php echo esc_url( is_user_logged_in() ? wd_account_url( 'new' ) : wd_url( 'sign-in', array( 'tab' => 'join' ) ) ); ?>"><?php esc_html_e( 'Create a free page', 'wwmd' ); ?></a>
			</div>
		</aside>
	</div>
</article>
<?php if ( $wd_more->have_posts() ) : ?>
<section class="section section--alt">
	<div class="wrap">
		<div class="section-head"><div><h2><?php esc_html_e( 'Keep reading', 'wwmd' ); ?></h2></div><a class="link-arrow" href="<?php echo esc_url( wd_url( 'blog' ) ); ?>"><?php esc_html_e( 'All posts', 'wwmd' ); ?><?php echo wd_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></div>
		<div class="b-grid">
			<?php
			foreach ( $wd_more->posts as $wd_p ) {
				get_template_part( 'template-parts/card-post', null, array( 'id' => $wd_p->ID ) );
			}
			?>
		</div>
	</div>
</section>
<?php endif; ?>
<?php get_template_part( 'template-parts/excerpt-market', null, array( 'view' => 'tile', 'class' => '' ) ); ?>
<?php
get_footer();
