<?php
/**
 * Template Name: Blog
 *
 * Wedding tips & how-tos, newest first, with category tabs.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
get_header();
$wd_pg   = max( 1, (int) get_query_var( 'paged' ) );
$wd_cat  = is_category() ? get_queried_object() : null;
$wd_list = $wd_cat ? $GLOBALS['wp_query'] : new WP_Query(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 9,
		'paged'          => $wd_pg,
	)
);
$wd_cats = get_categories(
	array(
		'hide_empty' => true,
		'exclude'    => array( (int) get_option( 'default_category' ) ),
	)
);
?>
<section class="page-hero page-hero--blog">
	<div class="wrap">
		<p class="eyebrow"><?php echo wd_icon( 'book', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Blog', 'wwmd' ); ?></p>
		<h1><?php echo $wd_cat ? esc_html( $wd_cat->name ) : esc_html__( 'Wedding tips & how-tos', 'wwmd' ); ?></h1>
		<p class="lead"><?php echo $wd_cat && $wd_cat->description ? esc_html( $wd_cat->description ) : esc_html__( 'Plain advice for Nigerian & African couples at home & abroad: planning every program, guests, gifts & getting the most out of your wedding page.', 'wwmd' ); ?></p>
	</div>
</section>
<section class="section section--tight">
	<div class="wrap">
		<?php if ( $wd_cats ) : ?>
			<nav class="tabs tabs--wrap" aria-label="<?php esc_attr_e( 'Topics', 'wwmd' ); ?>">
				<a class="tab<?php echo $wd_cat ? '' : ' is-on'; ?>" href="<?php echo esc_url( wd_url( 'blog' ) ); ?>"><?php esc_html_e( 'All', 'wwmd' ); ?></a>
				<?php foreach ( $wd_cats as $wd_c ) : ?>
					<a class="tab<?php echo $wd_cat && $wd_cat->term_id === $wd_c->term_id ? ' is-on' : ''; ?>" href="<?php echo esc_url( get_category_link( $wd_c ) ); ?>"><?php echo esc_html( $wd_c->name ); ?> <small><?php echo (int) $wd_c->count; ?></small></a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>
		<?php if ( $wd_list->have_posts() ) : ?>
			<div class="b-grid" data-grid>
				<?php
				foreach ( $wd_list->posts as $wd_p ) {
					get_template_part( 'template-parts/card-post', null, array( 'id' => $wd_p->ID ) );
				}
				?>
			</div>
			<?php if ( $wd_list->max_num_pages > $wd_pg ) : ?>
				<div class="center load-more"><a class="btn btn--ghost" href="<?php echo esc_url( $wd_cat ? get_pagenum_link( $wd_pg + 1 ) : trailingslashit( wd_url( 'blog' ) ) . 'page/' . ( $wd_pg + 1 ) . '/' ); ?>" data-load-more><?php esc_html_e( 'Show more', 'wwmd' ); ?></a></div>
			<?php endif; ?>
		<?php else : ?>
			<div class="empty"><?php echo wd_art( 'notes' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><h2><?php esc_html_e( 'No posts yet', 'wwmd' ); ?></h2></div>
		<?php endif; ?>
	</div>
</section>
<?php $wd_news = ( $wd_cat || $wd_pg > 1 ) ? array() : wd_news( 6 ); ?>
<?php if ( $wd_news ) : ?>
<section class="section section--alt news" id="news">
	<div class="wrap">
		<div class="section-head"><div><p class="eyebrow"><?php echo wd_icon( 'feed', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'From around the web', 'wwmd' ); ?></p><h2><?php esc_html_e( 'Wedding news', 'wwmd' ); ?></h2></div></div>
		<div class="news__list">
			<?php foreach ( $wd_news as $wd_n ) : ?>
				<a class="news__item" href="<?php echo esc_url( $wd_n['link'] ); ?>" target="_blank" rel="noopener nofollow">
					<?php if ( $wd_n['image'] ) : ?><span class="news__pic"><img src="<?php echo esc_url( $wd_n['image'] ); ?>" alt="" loading="lazy" referrerpolicy="no-referrer"></span><?php endif; ?>
					<span class="news__body">
						<b class="news__title"><?php echo esc_html( $wd_n['title'] ); ?></b>
						<?php if ( $wd_n['text'] ) : ?><span class="news__text"><?php echo esc_html( $wd_n['text'] ); ?></span><?php endif; ?>
						<small class="news__src"><?php echo esc_html( sprintf( /* translators: %s site name */ __( 'Read on %s', 'wwmd' ), $wd_n['source'] ) ); ?><?php echo $wd_n['time'] ? ' · ' . esc_html( wp_date( 'j M', $wd_n['time'] ) ) : ''; ?><?php echo wd_icon( 'arrow', 12 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></small>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>
<?php get_template_part( 'template-parts/excerpt-community' ); ?>
<?php
get_footer();
