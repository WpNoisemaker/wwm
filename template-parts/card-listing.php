<?php
/**
 * Marketplace listing, as a post (1.5): the seller on top, the photos, heart, chat, share & save, then
 * the caption with the price. Args: id, view (post|tile). A tile is the photo-grid version: the
 * square photo with the title & price under it.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_lid  = (int) $args['id'];
$wd_view = $args['view'] ?? 'post';
$wd_l    = wd_listing( $wd_lid );
$wd_sec  = wd_sectors();
$wd_sec  = isset( $wd_sec[ $wd_l['sector'] ] ) ? $wd_sec[ $wd_l['sector'] ] : $wd_sec['stays'];
$wd_pid  = (int) get_post_field( 'post_author', $wd_lid );
$wd_pp   = wd_partner( $wd_pid );
$wd_url  = get_permalink( $wd_lid );
$wd_ask  = wd_listing_asks( $wd_l );
$wd_n    = count( array_filter( (array) $wd_l['photos'] ) );
?>
<?php if ( 'tile' === $wd_view ) : ?>
<article class="ig-tile">
	<a class="ig-tile__link" href="<?php echo esc_url( $wd_url ); ?>">
		<span class="ig-tile__pic tone-<?php echo esc_attr( wd_listing_tone( $wd_lid, $wd_l ) ); ?>"><?php echo wd_listing_cover( $wd_lid, $wd_l, 'wd-square' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php if ( $wd_n > 1 ) : ?><span class="ig-tile__multi" aria-label="<?php esc_attr_e( 'More photos', 'wwmd' ); ?>"><?php echo wd_icon( 'grid', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><?php endif; ?></span>
		<span class="ig-tile__cap"><b><?php echo esc_html( get_the_title( $wd_lid ) ); ?></b><span class="<?php echo $wd_ask ? 'is-ask' : ''; ?>"><?php echo esc_html( wd_listing_price_line( $wd_l ) ); ?></span></span>
	</a>
</article>
<?php else : ?>
<article class="ig-post l-card">
	<header class="ig-post__head">
		<?php if ( $wd_pp ) : ?>
			<?php echo wd_seller_ring( $wd_pid, 38, 'a' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span class="ig-post__who">
				<a href="<?php echo esc_url( wd_partner_url( $wd_pid ) ); ?>"><b><?php echo esc_html( $wd_pp['business'] ); ?></b><?php echo wd_partner_badge( $wd_pid, false ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
				<small><?php echo esc_html( trim( $wd_l['city'] . ', ' . $wd_l['country'], ', ' ) ); ?></small>
			</span>
		<?php endif; ?>
		<span class="chip chip--sm"><?php echo wd_icon( $wd_sec[1], 13 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $wd_sec[0] ); ?></span>
	</header>
	<?php echo wd_listing_carousel( $wd_lid, $wd_l ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<?php echo wd_listing_actions( $wd_lid ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<a class="ig-post__cap l-card__link" href="<?php echo esc_url( $wd_url ); ?>">
		<span class="l-card__title"><?php echo esc_html( get_the_title( $wd_lid ) ); ?></span>
		<span class="ig-post__price<?php echo $wd_ask ? ' is-ask' : ''; ?>"><?php echo esc_html( wd_listing_price_line( $wd_l ) ); ?></span>
		<?php if ( $wd_l['capacity'] ) : ?><span class="ig-post__meta"><?php echo esc_html( sprintf( /* translators: %d people */ _n( 'Up to %d person', 'Up to %d people', (int) $wd_l['capacity'], 'wwmd' ), (int) $wd_l['capacity'] ) ); ?></span><?php endif; ?>
		<span class="ig-post__more"><?php echo esc_html( $wd_ask ? __( 'Chat with the seller', 'wwmd' ) : __( 'See dates & book', 'wwmd' ) ); ?></span>
	</a>
</article>
<?php endif; ?>
