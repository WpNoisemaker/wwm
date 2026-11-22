<?php
/**
 * Not found.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<section class="page-hero page-hero--plain center"><div class="wrap">
	<div class="nf-art"><?php echo wd_art( 'bridal' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
	<h1><?php esc_html_e( 'This wedding car took a wrong turn', 'wwmd' ); ?></h1>
	<p class="lead"><?php esc_html_e( 'We could not find that page. The wedding may have moved or been made private.', 'wwmd' ); ?></p>
	<p><a class="btn btn--ink" href="<?php echo esc_url( wd_url( 'explore' ) ); ?>"><?php esc_html_e( 'Explore weddings', 'wwmd' ); ?></a></p>
</div></section>
<?php
get_footer();
