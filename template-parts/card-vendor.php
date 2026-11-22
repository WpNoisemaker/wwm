<?php
/**
 * Vendor card.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_vid = (int) $args['id'];
$wd_v   = wd_vendor( $wd_vid );
$wd_r   = (float) get_post_meta( $wd_vid, '_wd_rating', true );
$wd_n   = (int) get_post_meta( $wd_vid, '_wd_reviews', true );
$wd_img = get_the_post_thumbnail_url( $wd_vid, 'wd-square' );
?>
<article class="v-card tone-<?php echo esc_attr( $wd_v['colour'] ); ?>">
	<a href="<?php echo esc_url( get_permalink( $wd_vid ) ); ?>" class="v-card__link">
		<span class="v-card__pic">
			<?php if ( $wd_img ) : ?>
				<img src="<?php echo esc_url( $wd_img ); ?>" alt="" loading="lazy">
			<?php else : ?>
				<?php echo wd_avatar( get_the_title( $wd_vid ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php endif; ?>
		</span>
		<span class="v-card__body">
			<span class="v-card__type"><?php echo esc_html( wd_vendor_type( $wd_vid ) ); ?></span>
			<span class="v-card__name"><?php echo esc_html( get_the_title( $wd_vid ) ); ?><?php if ( $wd_v['verified'] ) : ?><span class="verified" title="<?php esc_attr_e( 'Verified vendor', 'wwmd' ); ?>"><?php echo wd_icon( 'shield', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><?php endif; ?></span>
			<span class="v-card__meta"><?php echo wd_stars( $wd_r ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <b><?php echo esc_html( $wd_r ? number_format_i18n( $wd_r, 1 ) : '–' ); ?></b> <span>(<?php echo esc_html( sprintf( /* translators: %d reviews */ _n( '%d review', '%d reviews', $wd_n, 'wwmd' ), $wd_n ) ); ?>)</span></span>
			<span class="v-card__city"><?php echo wd_icon( 'pin', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( trim( $wd_v['city'] . ', ' . $wd_v['country'], ', ' ) ); ?></span>
		</span>
	</a>
</article>
