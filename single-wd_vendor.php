<?php
/**
 * Vendor profile & guest reviews.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
get_header();
the_post();
global $wpdb;
$wd_vid  = get_the_ID();
$wd_v    = wd_vendor( $wd_vid );
$wd_r    = (float) get_post_meta( $wd_vid, '_wd_rating', true );
$wd_n    = (int) get_post_meta( $wd_vid, '_wd_reviews', true );
$wd_revs = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . wd_t( 'reviews' ) . " WHERE vendor_id = %d AND status = 'approved' ORDER BY id DESC LIMIT 50", $wd_vid ) ); // phpcs:ignore WordPress.DB
$wd_wed  = array_unique( array_filter( wp_list_pluck( (array) $wd_revs, 'wedding_id' ) ) );
$wd_dist = array_fill( 1, 5, 0 );
foreach ( (array) $wd_revs as $wd_x ) {
	$wd_dist[ (int) $wd_x->rating ]++;
}
?>
<section class="page-hero page-hero--vendor tone-<?php echo esc_attr( $wd_v['colour'] ); ?>">
	<div class="wrap vendor-head">
		<span class="vendor-head__pic"><?php echo has_post_thumbnail() ? get_the_post_thumbnail( $wd_vid, 'wd-square' ) : wd_avatar( get_the_title() ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		<div>
			<p class="eyebrow"><?php echo esc_html( wd_vendor_type( $wd_vid ) ); ?> · <?php echo esc_html( trim( $wd_v['city'] . ', ' . $wd_v['country'], ', ' ) ); ?></p>
			<h1><?php the_title(); ?><?php if ( $wd_v['verified'] ) : ?><span class="verified verified--lg" title="<?php esc_attr_e( 'Verified vendor', 'wwmd' ); ?>"><?php echo wd_icon( 'shield', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><?php endif; ?></h1>
			<p class="vendor-head__rating"><?php echo wd_stars( $wd_r ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <b><?php echo esc_html( $wd_r ? number_format_i18n( $wd_r, 1 ) : '–' ); ?></b> <?php echo esc_html( sprintf( /* translators: 1: reviews 2: weddings */ __( '%1$s from %2$s', 'wwmd' ), sprintf( _n( '%d review', '%d reviews', $wd_n, 'wwmd' ), $wd_n ), sprintf( _n( '%d wedding', '%d weddings', count( $wd_wed ), 'wwmd' ), count( $wd_wed ) ) ) ); ?></p>
			<div class="btn-row">
				<?php if ( $wd_v['ig'] ) : ?><a class="btn btn--ink" href="<?php echo esc_url( 'https://instagram.com/' . $wd_v['ig'] ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'insta', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>@<?php echo esc_html( $wd_v['ig'] ); ?></a><?php endif; ?>
				<?php if ( $wd_v['verified'] && $wd_v['phone'] ) : ?><a class="btn btn--marigold" href="<?php echo esc_url( 'https://wa.me/' . preg_replace( '/\D/', '', $wd_v['phone'] ) ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'whatsapp', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Chat on WhatsApp', 'wwmd' ); ?></a><?php endif; ?>
			</div>
		</div>
	</div>
</section>
<section class="section">
	<div class="wrap vendor-body">
		<aside class="vendor-side">
			<?php if ( get_the_content() ) : ?><div class="prose"><?php the_content(); ?></div><?php endif; ?>
			<?php if ( $wd_v['price'] ) : ?><p class="vendor-price"><?php echo wd_icon( 'cash', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $wd_v['price'] ); ?></p><?php endif; ?>
			<div class="dist">
				<?php for ( $wd_s = 5; $wd_s >= 1; $wd_s-- ) : ?>
					<p><span><?php echo (int) $wd_s; ?>★</span><i><b style="width:<?php echo $wd_n ? (int) round( $wd_dist[ $wd_s ] / $wd_n * 100 ) : 0; ?>%"></b></i><small><?php echo (int) $wd_dist[ $wd_s ]; ?></small></p>
				<?php endfor; ?>
			</div>
		</aside>
		<div class="reviews">
			<h2><?php esc_html_e( 'What guests said', 'wwmd' ); ?></h2>
			<?php foreach ( (array) $wd_revs as $wd_x ) : ?>
				<?php $wd_wd = $wd_x->wedding_id && 'publish' === get_post_status( $wd_x->wedding_id ) ? wd_get( (int) $wd_x->wedding_id ) : null; ?>
				<article class="review">
					<header><span class="review__av"><?php echo wd_avatar( $wd_x->name ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><span><b><?php echo esc_html( $wd_x->name ); ?></b><small><?php echo esc_html( wd_ago( $wd_x->created ) ); ?></small></span><?php echo wd_stars( (float) $wd_x->rating ); // phpcs:ignore WordPress.Security.EscapeOutput ?></header>
					<p><?php echo esc_html( $wd_x->text ); ?></p>
					<?php if ( $wd_wd ) : ?><a class="review__at" href="<?php echo esc_url( get_permalink( $wd_x->wedding_id ) ); ?>"><?php echo wd_icon( 'heart', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( sprintf( /* translators: %s names */ __( 'Guest at %s’s wedding', 'wwmd' ), wd_names( $wd_wd ) ) ); ?></a><?php endif; ?>
				</article>
			<?php endforeach; ?>
			<?php if ( ! $wd_revs ) : ?><p class="empty-note"><?php esc_html_e( 'No reviews yet. Reviews come from guests on the wedding pages this vendor worked on.', 'wwmd' ); ?></p><?php endif; ?>
		</div>
	</div>
</section>
<?php
get_footer();
