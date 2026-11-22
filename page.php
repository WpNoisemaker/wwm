<?php
/**
 * Plain page.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	?>
	<section class="page-hero page-hero--plain"><div class="wrap"><h1><?php the_title(); ?></h1></div></section>
	<section class="section"><div class="wrap prose"><?php the_content(); ?></div></section>
	<?php
endwhile;
get_footer();
