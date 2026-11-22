<?php
/**
 * "Weddings around the world" map: Africa, Europe & North America drawn simply, a pin per city with weddings,
 * & a list of that city's weddings beside the map.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

$wd_w = 1000;
$wd_h = 588;
$wd_proj = static function ( $lon, $lat ) use ( $wd_w, $wd_h ) {
	return array( round( ( $lon + 110 ) / 170 * $wd_w, 1 ), round( ( 62 - $lat ) / 100 * $wd_h, 1 ) );
};
$wd_path = static function ( $pts ) use ( $wd_proj ) {
	$d = '';
	foreach ( $pts as $i => $p ) {
		$xy = $wd_proj( $p[0], $p[1] );
		$d .= ( $i ? 'L' : 'M' ) . $xy[0] . ' ' . $xy[1];
	}
	return $d . 'Z';
};
$wd_land = array(
	'africa'    => array( array( -6, 36 ), array( 10, 37.3 ), array( 11, 33 ), array( 20, 31 ), array( 25, 31.6 ), array( 32, 31.3 ), array( 34, 28 ), array( 38, 22 ), array( 43, 12.5 ), array( 51, 11.8 ), array( 48, 5 ), array( 42, -1 ), array( 40, -5 ), array( 39.5, -10 ), array( 40.5, -15 ), array( 35.5, -24 ), array( 32.5, -29 ), array( 27, -33.8 ), array( 20, -34.8 ), array( 18.2, -33.8 ), array( 15, -27 ), array( 12, -17 ), array( 13, -9 ), array( 9, -1 ), array( 9.6, 4 ), array( 6, 4.3 ), array( 2, 6.3 ), array( -3, 5 ), array( -8, 4.5 ), array( -13, 8 ), array( -17.2, 14.6 ), array( -17, 21 ), array( -13, 27.5 ), array( -9.8, 30 ) ),
	'madagascar' => array( array( 49.3, -12 ), array( 50.4, -15.8 ), array( 47.2, -25 ), array( 44, -24.5 ), array( 44.2, -17 ) ),
	'europe'    => array( array( -9.5, 43.5 ), array( -9, 37 ), array( -5.6, 36 ), array( 0, 38.5 ), array( 3, 42.5 ), array( 9, 44 ), array( 13, 45.5 ), array( 18, 40 ), array( 24, 38 ), array( 28, 41 ), array( 41, 41 ), array( 48, 45 ), array( 60, 50 ), array( 60, 62 ), array( 26, 62 ), array( 10, 58 ), array( 8, 56 ), array( 8, 54 ), array( 4, 52 ), array( -1.5, 49.5 ), array( -4.5, 48.5 ), array( -1.5, 46 ), array( -1.5, 44 ) ),
	'uk'        => array( array( -5.6, 50 ), array( 1.6, 51.2 ), array( 0.2, 53.2 ), array( -2.2, 56 ), array( -3, 58.6 ), array( -5.2, 58.5 ), array( -6, 56 ), array( -3.2, 54.6 ), array( -4.8, 52.8 ), array( -5.2, 51.6 ) ),
	'ireland'   => array( array( -10, 51.8 ), array( -6.2, 52.2 ), array( -6, 55.2 ), array( -8.2, 55.3 ), array( -10, 54 ) ),
	'arabia'    => array( array( 34.5, 28 ), array( 35.6, 33 ), array( 36.2, 36.5 ), array( 42, 37 ), array( 48, 30 ), array( 51, 24.5 ), array( 56, 26.2 ), array( 60, 25 ), array( 60, 40 ), array( 50, 42 ), array( 41, 41 ), array( 36, 36.6 ), array( 34.8, 32 ), array( 34.3, 31 ), array( 59, 22.5 ), array( 52, 16.5 ), array( 43.4, 12.7 ), array( 39, 20.5 ) ),
	'namerica'  => array( array( -110, 62 ), array( -80, 62 ), array( -64, 60 ), array( -56, 52 ), array( -60, 46 ), array( -66, 44 ), array( -70, 41.6 ), array( -74, 40.4 ), array( -76, 35 ), array( -81, 31 ), array( -80, 25.3 ), array( -82, 27.5 ), array( -83, 29.6 ), array( -89, 30.2 ), array( -94, 29.6 ), array( -97.3, 26 ), array( -97.6, 22 ), array( -96, 19 ), array( -91, 18.6 ), array( -87, 21.4 ), array( -88.3, 16 ), array( -83.3, 9.5 ), array( -77.5, 8.2 ), array( -80, 7.4 ), array( -86, 12 ), array( -92, 14.6 ), array( -105, 20 ), array( -110, 23.5 ) ),
	'samerica'  => array( array( -77.5, 8.2 ), array( -72, 12 ), array( -62, 10.6 ), array( -52, 5 ), array( -35, -5.2 ), array( -39, -14 ), array( -48.5, -26 ), array( -55, -34 ), array( -58, -38 ), array( -73.5, -38 ), array( -71.6, -18 ), array( -76.5, -14 ), array( -81.3, -5 ), array( -80, 1 ), array( -77.4, 4 ) ),
);
// Fix Arabia's outline order (drawn as one loop around the peninsula & the Middle East).
$wd_land['arabia'] = array( array( 34.5, 28 ), array( 39, 20.5 ), array( 43.4, 12.7 ), array( 52, 16.5 ), array( 59, 22.5 ), array( 56, 26.2 ), array( 51, 24.5 ), array( 48, 30 ), array( 60, 32 ), array( 60, 40 ), array( 50, 42 ), array( 41, 41 ), array( 36.2, 36.5 ), array( 35.6, 33 ), array( 34.3, 31 ) );

$wd_cities = array(
	'lagos'         => array( 3.38, 6.52 ),
	'abuja'         => array( 7.49, 9.06 ),
	'enugu'         => array( 7.5, 6.45 ),
	'kano'          => array( 8.52, 12.0 ),
	'port-harcourt' => array( 7.0, 4.8 ),
	'ibadan'        => array( 3.9, 7.4 ),
	'benin-city'    => array( 5.62, 6.34 ),
	'accra'         => array( -0.19, 5.6 ),
	'kumasi'        => array( -1.62, 6.69 ),
	'abidjan'       => array( -4.0, 5.36 ),
	'dakar'         => array( -17.45, 14.7 ),
	'nairobi'       => array( 36.8, -1.29 ),
	'diani'         => array( 39.58, -4.3 ),
	'mombasa'       => array( 39.66, -4.04 ),
	'zanzibar'      => array( 39.2, -6.16 ),
	'kigali'        => array( 30.06, -1.95 ),
	'addis-ababa'   => array( 38.74, 9.03 ),
	'johannesburg'  => array( 28.05, -26.2 ),
	'cape-town'     => array( 18.42, -33.92 ),
	'london'        => array( -0.13, 51.5 ),
	'manchester'    => array( -2.24, 53.48 ),
	'dublin'        => array( -6.26, 53.35 ),
	'houston'       => array( -95.37, 29.76 ),
	'atlanta'       => array( -84.39, 33.75 ),
	'new-york'      => array( -74.0, 40.71 ),
	'toronto'       => array( -79.38, 43.65 ),
	'dubai'         => array( 55.3, 25.2 ),
	'marrakech'     => array( -8.0, 31.63 ),
);
$wd_terms = get_terms( array( 'taxonomy' => 'wd_place', 'hide_empty' => true ) );
$wd_pins  = array();
foreach ( is_array( $wd_terms ) ? $wd_terms : array() as $wd_t ) {
	if ( ! $wd_t->parent || ! isset( $wd_cities[ $wd_t->slug ] ) ) {
		continue;
	}
	$wd_ids = get_posts(
		array(
			'post_type'      => 'wd_wedding',
			'posts_per_page' => 4,
			'fields'         => 'ids',
			'tax_query'      => array( array( 'taxonomy' => 'wd_place', 'terms' => $wd_t->term_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_key'       => '_wd_score', // phpcs:ignore WordPress.DB.SlowDBQuery
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
		)
	);
	if ( $wd_ids ) {
		$wd_pins[] = array( $wd_t, $wd_proj( $wd_cities[ $wd_t->slug ][0], $wd_cities[ $wd_t->slug ][1] ), $wd_ids );
	}
}
if ( ! $wd_pins ) {
	return;
}
usort(
	$wd_pins,
	static function ( $a, $b ) {
		return $b[0]->count <=> $a[0]->count;
	}
);
?>
<section class="section section--map">
	<div class="wrap">
		<div class="section-head">
			<div>
				<p class="eyebrow"><?php echo wd_icon( 'pin', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Home & abroad', 'wwmd' ); ?></p>
				<h2><?php esc_html_e( 'Weddings near you, & near the people you love', 'wwmd' ); ?></h2>
			</div>
			<p class="section-head__aside"><?php esc_html_e( 'Tap a city to see its weddings.', 'wwmd' ); ?></p>
		</div>
		<div class="map-card" data-map>
			<div class="map-card__map">
				<svg viewBox="0 0 <?php echo (int) $wd_w; ?> <?php echo (int) $wd_h; ?>" class="map-svg" aria-hidden="true" focusable="false">
					<rect width="<?php echo (int) $wd_w; ?>" height="<?php echo (int) $wd_h; ?>" fill="#BFE6FC"/>
					<g fill="none" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round" opacity=".8">
						<path d="M30 380q20-10 40 0t40 0M520 520q20-10 40 0t40 0M260 220q20-10 40 0t40 0M820 480q20-10 40 0t40 0M120 80q20-10 40 0t40 0"/>
					</g>
					<?php
					$wd_fill = array(
						'africa'     => '#FFD84D',
						'madagascar' => '#FFD84D',
						'europe'     => '#CDB6FF',
						'uk'         => '#CDB6FF',
						'ireland'    => '#8EE3B8',
						'arabia'     => '#FF8E6E',
						'namerica'   => '#8EE3B8',
						'samerica'   => '#FFF6E5',
					);
					foreach ( $wd_land as $wd_k => $wd_pts ) :
						?>
						<path d="<?php echo esc_attr( $wd_path( $wd_pts ) ); ?>" fill="<?php echo esc_attr( $wd_fill[ $wd_k ] ); ?>" stroke="#1C0B33" stroke-width="3" stroke-linejoin="round"/>
					<?php endforeach; ?>
				</svg>
				<?php foreach ( $wd_pins as $wd_i => $wd_p ) : ?>
					<button type="button" class="map-pin<?php echo 0 === $wd_i ? ' is-on' : ''; ?>" style="left:<?php echo esc_attr( $wd_p[1][0] / $wd_w * 100 ); ?>%;top:<?php echo esc_attr( $wd_p[1][1] / $wd_h * 100 ); ?>%" data-map-pin="<?php echo esc_attr( $wd_p[0]->slug ); ?>" aria-pressed="<?php echo 0 === $wd_i ? 'true' : 'false'; ?>">
						<span class="map-pin__dot"></span><span class="map-pin__label"><?php echo esc_html( $wd_p[0]->name ); ?> <b><?php echo (int) $wd_p[0]->count; ?></b></span>
					</button>
				<?php endforeach; ?>
			</div>
			<div class="map-card__list">
				<?php foreach ( $wd_pins as $wd_i => $wd_p ) : ?>
					<div class="map-list" data-map-list="<?php echo esc_attr( $wd_p[0]->slug ); ?>"<?php echo $wd_i ? ' hidden' : ''; ?>>
						<p class="map-list__head"><?php echo wd_icon( 'pin', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( sprintf( /* translators: %s: city */ __( 'Weddings in %s', 'wwmd' ), $wd_p[0]->name ) ); ?></p>
						<ul>
							<?php
							foreach ( $wd_p[2] as $wd_id ) :
								$wd_d = wd_get( $wd_id );
								?>
								<li><a href="<?php echo esc_url( get_permalink( $wd_id ) ); ?>"><span class="map-list__pic"><?php echo wd_cover( $wd_id, $wd_d, 'wd-square' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><span><b><?php echo esc_html( wd_names( $wd_d ) ); ?></b><small><?php echo esc_html( wd_date( $wd_d['date'], 'j M Y' ) ); ?></small></span><?php echo wd_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
							<?php endforeach; ?>
						</ul>
						<a class="link-arrow" href="<?php echo esc_url( get_term_link( $wd_p[0] ) ); ?>"><?php esc_html_e( 'See all', 'wwmd' ); ?><?php echo wd_icon( 'arrow', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
