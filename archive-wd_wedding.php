<?php
/**
 * Explore weddings (also used for "Weddings in {place}" & wedding style pages).
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
get_header();
// phpcs:disable WordPress.Security.NonceVerification
$wd_g     = wp_unslash( $_GET );
$wd_sort  = isset( $wd_g['sort'] ) ? sanitize_key( $wd_g['sort'] ) : 'trending';
$wd_term  = is_tax() ? get_queried_object() : null;
$wd_place = $wd_term && 'wd_place' === $wd_term->taxonomy ? $wd_term->slug : ( isset( $wd_g['place'] ) ? sanitize_title( $wd_g['place'] ) : '' );
$wd_style = $wd_term && 'wd_style' === $wd_term->taxonomy ? $wd_term->slug : ( isset( $wd_g['style'] ) ? sanitize_title( $wd_g['style'] ) : '' );
$wd_base  = $wd_term ? get_term_link( $wd_term ) : wd_url( 'explore' );
$wd_title = $wd_term ? ( 'wd_place' === $wd_term->taxonomy ? sprintf( /* translators: %s place */ __( 'Weddings in %s', 'wwmd' ), $wd_term->name ) : sprintf( /* translators: %s style */ __( '%s weddings', 'wwmd' ), $wd_term->name ) ) : __( 'Explore weddings', 'wwmd' );
$wd_keep  = array_filter( array( 'q' => $wd_g['q'] ?? '', 'plan' => $wd_g['plan'] ?? '', 'when' => $wd_g['when'] ?? '', 'place' => $wd_term ? '' : $wd_place, 'style' => $wd_term ? '' : $wd_style ) );
// phpcs:enable
$wd_places = get_terms( array( 'taxonomy' => 'wd_place', 'hide_empty' => true ) );
$wd_styles = get_terms( array( 'taxonomy' => 'wd_style', 'hide_empty' => true ) );
global $wp_query;
?>
<section class="page-hero page-hero--explore">
	<div class="wrap">
		<p class="eyebrow"><?php echo esc_html( sprintf( /* translators: %d weddings */ _n( '%d wedding', '%d weddings', (int) $wp_query->found_posts, 'wwmd' ), (int) $wp_query->found_posts ) ); ?></p>
		<h1><?php echo esc_html( $wd_title ); ?></h1>
		<p class="lead"><?php esc_html_e( 'Browse like you are scrolling the timeline: trending pages, couples saying “I do” this weekend & the weddings people are still talking about.', 'wwmd' ); ?></p>
		<form class="filters" method="get" action="<?php echo esc_url( $wd_base ); ?>" role="search">
			<div class="filters__search">
				<?php echo wd_icon( 'search', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<label class="screen-reader-text" for="f-q"><?php esc_html_e( 'Search', 'wwmd' ); ?></label>
				<input id="f-q" type="search" name="q" value="<?php echo esc_attr( $wd_g['q'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Names or #hashtag', 'wwmd' ); ?>">
			</div>
			<div class="filters__more">
			<?php if ( ! $wd_term || 'wd_place' !== $wd_term->taxonomy ) : ?>
				<label class="select"><span class="screen-reader-text"><?php esc_html_e( 'Place', 'wwmd' ); ?></span>
					<select name="place">
						<option value=""><?php esc_html_e( 'Anywhere', 'wwmd' ); ?></option>
						<?php foreach ( is_array( $wd_places ) ? $wd_places : array() as $wd_p ) : ?>
							<option value="<?php echo esc_attr( $wd_p->slug ); ?>" <?php selected( $wd_place, $wd_p->slug ); ?>><?php echo esc_html( ( $wd_p->parent ? '— ' : '' ) . $wd_p->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			<?php endif; ?>
			<?php if ( ! $wd_term || 'wd_style' !== $wd_term->taxonomy ) : ?>
				<label class="select"><span class="screen-reader-text"><?php esc_html_e( 'Style', 'wwmd' ); ?></span>
					<select name="style">
						<option value=""><?php esc_html_e( 'Any style', 'wwmd' ); ?></option>
						<?php foreach ( is_array( $wd_styles ) ? $wd_styles : array() as $wd_s ) : ?>
							<option value="<?php echo esc_attr( $wd_s->slug ); ?>" <?php selected( $wd_style, $wd_s->slug ); ?>><?php echo esc_html( $wd_s->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			<?php endif; ?>
			<label class="select"><span class="screen-reader-text"><?php esc_html_e( 'Program', 'wwmd' ); ?></span>
				<select name="program">
					<option value=""><?php esc_html_e( 'Any program', 'wwmd' ); ?></option>
					<?php foreach ( wd_program_types() as $wd_pk => $wd_pt ) : ?>
						<?php if ( in_array( $wd_pk, array( 'other', 'after', 'thanks' ), true ) ) { continue; } ?>
						<option value="<?php echo esc_attr( $wd_pk ); ?>" <?php selected( $wd_g['program'] ?? '', $wd_pk ); ?>><?php echo esc_html( $wd_pt[0] ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label class="select"><span class="screen-reader-text"><?php esc_html_e( 'When', 'wwmd' ); ?></span>
				<select name="when">
					<option value=""><?php esc_html_e( 'Any date', 'wwmd' ); ?></option>
					<option value="upcoming" <?php selected( $wd_g['when'] ?? '', 'upcoming' ); ?>><?php esc_html_e( 'Coming up', 'wwmd' ); ?></option>
					<option value="month" <?php selected( $wd_g['when'] ?? '', 'month' ); ?>><?php esc_html_e( 'This month', 'wwmd' ); ?></option>
					<option value="past" <?php selected( $wd_g['when'] ?? '', 'past' ); ?>><?php esc_html_e( 'Already married', 'wwmd' ); ?></option>
				</select>
			</label>
			<label class="select"><span class="screen-reader-text"><?php esc_html_e( 'Plan', 'wwmd' ); ?></span>
				<select name="plan">
					<option value=""><?php esc_html_e( 'Any plan', 'wwmd' ); ?></option>
					<?php foreach ( wd_plans() as $wd_pk => $wd_pp ) : ?>
						<option value="<?php echo esc_attr( $wd_pk ); ?>" <?php selected( $wd_g['plan'] ?? '', $wd_pk ); ?>><?php echo esc_html( $wd_pp['name'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			</div>
			<input type="hidden" name="sort" value="<?php echo esc_attr( $wd_sort ); ?>">
			<button class="btn btn--ink" type="submit"><?php echo wd_icon( 'search', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Show weddings', 'wwmd' ); ?></span></button>
		</form>
	</div>
</section>
<section class="section section--tight">
	<div class="wrap">
		<nav class="tabs" aria-label="<?php esc_attr_e( 'Sort', 'wwmd' ); ?>">
			<?php
			foreach ( array(
				'trending' => array( __( 'Trending this week', 'wwmd' ), 'fire' ),
				'top'      => array( __( 'Top of all time', 'wwmd' ), 'trophy' ),
				'soon'     => array( __( 'Coming up', 'wwmd' ), 'calendar' ),
				'new'      => array( __( 'Newest pages', 'wwmd' ), 'plus' ),
			) as $wd_k => $wd_t ) :
				?>
				<a class="tab<?php echo $wd_k === $wd_sort ? ' is-on' : ''; ?>" href="<?php echo esc_url( add_query_arg( array_merge( $wd_keep, array( 'sort' => $wd_k ) ), $wd_base ) ); ?>"<?php echo $wd_k === $wd_sort ? ' aria-current="page"' : ''; ?>><?php echo wd_icon( $wd_t[1], 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $wd_t[0] ); ?></a>
			<?php endforeach; ?>
		</nav>
		<?php if ( have_posts() ) : ?>
			<div class="w-grid" data-grid>
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/card-wedding', null, array( 'id' => get_the_ID() ) );
				endwhile;
				?>
			</div>
			<?php
			$wd_next = get_next_posts_page_link();
			if ( $wd_next && (int) get_query_var( 'paged', 1 ) < (int) $wp_query->max_num_pages ) :
				?>
				<div class="center load-more"><a class="btn btn--ghost" href="<?php echo esc_url( $wd_next ); ?>" data-load-more><?php esc_html_e( 'Show more weddings', 'wwmd' ); ?></a></div>
			<?php endif; ?>
		<?php else : ?>
			<div class="empty">
				<?php echo wd_art( 'globe' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<h2><?php esc_html_e( 'No weddings match that yet', 'wwmd' ); ?></h2>
				<p><?php esc_html_e( 'Try another city or date, or be the first & make your own page.', 'wwmd' ); ?></p>
				<a class="btn btn--ink" href="<?php echo esc_url( wd_url( 'sign-in', array( 'tab' => 'join' ) ) ); ?>"><?php esc_html_e( 'Create a free page', 'wwmd' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php if ( ! is_paged() ) : ?>
	<?php get_template_part( 'template-parts/excerpt-community' ); ?>
	<?php get_template_part( 'template-parts/excerpt-market', null, array( 'view' => 'tile', 'class' => 'section--alt' ) ); ?>
<?php endif; ?>
<?php
get_footer();
