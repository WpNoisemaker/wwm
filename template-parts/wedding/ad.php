<?php
/**
 * Sponsored slot on free pages (HQ → Ads).
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_ad = $args['ad'];
if ( ! $wd_ad ) {
	return;
}
?>
<aside class="ad-slot" aria-label="<?php esc_attr_e( 'Sponsored', 'wwmd' ); ?>">
	<div class="wrap">
		<a class="ad tone-<?php echo esc_attr( $wd_ad['tone'] ? $wd_ad['tone'] : 'butter' ); ?>" href="<?php echo esc_url( $wd_ad['url'] ); ?>" rel="sponsored noopener" target="_blank">
			<span class="ad__label"><?php esc_html_e( 'Sponsored', 'wwmd' ); ?></span>
			<?php if ( $wd_ad['image'] ) : ?><img class="ad__img" src="<?php echo esc_url( $wd_ad['image'] ); ?>" alt="" loading="lazy"><?php endif; ?>
			<span class="ad__text"><b><?php echo esc_html( $wd_ad['title'] ); ?></b><?php echo esc_html( $wd_ad['text'] ); ?></span>
			<span class="btn btn--ink btn--sm"><?php echo esc_html( $wd_ad['cta'] ? $wd_ad['cta'] : __( 'Learn more', 'wwmd' ) ); ?></span>
		</a>
	</div>
</aside>
