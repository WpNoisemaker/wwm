<?php
/**
 * Fallback list.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<section class="page-hero page-hero--plain"><div class="wrap"><h1><?php echo esc_html( is_search() ? sprintf( /* translators: %s query */ __( 'Results for “%s”', 'wwmd' ), get_search_query() ) : wp_get_document_title() ); ?></h1></div></section>
<section class="section"><div class="wrap prose">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article class="list-item"><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php the_excerpt(); ?></article>
		<?php endwhile; ?>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Nothing here yet.', 'wwmd' ); ?></p>
	<?php endif; ?>
</div></section>
<?php
get_footer();
