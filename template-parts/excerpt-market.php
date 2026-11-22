<?php
/**
 * A marketplace excerpt (1.5): the sellers' stories row & a few posts, for the home page, blog
 * posts & explore. Args: posts (how many, default 4), view (post|tile), class (extra section class).
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_n  = (int) ( $args['posts'] ?? 4 );
$wd_vw = $args['view'] ?? 'post';
// Mix stays, rides, travel & asoebi.
$wd_by = array();
foreach ( wd_listing_query( array( 'per' => 24 ) )->posts as $wd_p ) {
	$wd_by[ wd_listing( $wd_p->ID )['sector'] ][] = $wd_p->ID;
}
$wd_lp = array();
for ( $wd_i = 0; $wd_i < 24 && count( $wd_lp ) < $wd_n; $wd_i++ ) {
	foreach ( $wd_by as $wd_g ) {
		if ( isset( $wd_g[ $wd_i ] ) && count( $wd_lp ) < $wd_n ) {
			$wd_lp[] = $wd_g[ $wd_i ];
		}
	}
}
?>
<section class="section section--services <?php echo esc_attr( $args['class'] ?? '' ); ?>">
	<div class="wrap">
		<div class="section-head">
			<div>
				<p class="eyebrow"><?php echo wd_icon( 'shield', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Services marketplace', 'wwmd' ); ?></p>
				<h2><?php esc_html_e( 'Stays, rides, honeymoons & asoebi', 'wwmd' ); ?></h2>
				<p class="section-head__sub"><?php esc_html_e( 'Sellers we check. Chat with them on the site & they send you an invoice or a payment link.', 'wwmd' ); ?></p>
			</div>
			<a class="link-arrow" href="<?php echo esc_url( wd_url( 'services' ) ); ?>"><?php esc_html_e( 'Open the marketplace', 'wwmd' ); ?><?php echo wd_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		</div>
		<?php if ( $wd_lp ) : ?>
			<?php echo wd_stories_html( wd_story_sellers(), true ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="<?php echo 'tile' === $wd_vw ? 'ig-grid ig-grid--4' : 'l-grid l-grid--4 ig-row'; ?>">
				<?php
				foreach ( $wd_lp as $wd_id ) {
					get_template_part(
						'template-parts/card-listing',
						null,
						array(
							'id'   => $wd_id,
							'view' => $wd_vw,
						)
					);
				}
				?>
			</div>
		<?php else : ?>
			<div class="svc-strip">
				<?php foreach ( wd_sectors() as $wd_k => $wd_s ) : ?>
					<a class="svc tone-<?php echo esc_attr( $wd_s[3] ); ?>" href="<?php echo esc_url( wd_url( 'services', array( 'sector' => $wd_k ) ) ); ?>">
						<span class="svc__art"><?php echo wd_art( $wd_s[2] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<span class="svc__title"><?php echo esc_html( $wd_s[0] ); ?></span>
						<span class="svc__from"><?php esc_html_e( 'Chat with sellers', 'wwmd' ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
