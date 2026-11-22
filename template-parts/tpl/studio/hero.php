<?php
/**
 * The top of the page for the Studio designs (Noir, Coral, Indigo, Aso-oke, Keepsake, Savannah &
 * Owambe). One clean structure; each design's own stylesheet & ornaments give it its look.
 *
 * Args: id, d, on, tpl.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_id    = $args['id'];
$wd_d     = $args['d'];
$wd_on    = $args['on'];
$wd_key   = $args['tpl'];
$wd_lay   = wd_layout( $wd_id, $wd_d )['hero'];
$wd_st    = wd_status( $wd_id, $wd_d );
$wd_next  = wd_tpl_next( $wd_d );
$wd_place = wd_tpl_place( $wd_d );
?>
<section class="st-hero st-hero--<?php echo esc_attr( $wd_lay ); ?>" id="top">
	<?php echo wd_st_ornament( $wd_key, 'hero' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<div class="wrap st-hero__in">
		<?php if ( 'names' !== $wd_lay ) : ?>
			<figure class="st-photo">
				<span class="st-photo__frame"><?php echo wd_tpl_cover( $wd_id, $wd_d ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				<?php if ( $wd_st[1] ) : ?><figcaption class="st-status"><?php echo esc_html( $wd_st[1] ); ?></figcaption><?php endif; ?>
			</figure>
		<?php endif; ?>
		<div class="st-hero__text">
			<?php if ( 'names' === $wd_lay && $wd_st[1] ) : ?><p class="st-status st-status--inline"><?php echo esc_html( $wd_st[1] ); ?></p><?php endif; ?>
			<p class="st-kicker"><?php echo esc_html( $wd_d['invite']['hosts'] ? $wd_d['invite']['hosts'] : __( 'Together with their families', 'wwmd' ) ); ?></p>
			<h1 class="st-names">
				<span class="st-names__one"><?php echo esc_html( $wd_d['p1'] ); ?></span>
				<span class="st-amp" aria-label="<?php esc_attr_e( 'and', 'wwmd' ); ?>">&amp;</span>
				<span class="st-names__two"><?php echo esc_html( $wd_d['p2'] ); ?><?php echo wd_vbadge( $wd_id, 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			</h1>
			<?php echo wd_st_ornament( $wd_key, 'rule' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( $wd_d['date'] ) : ?><p class="st-date"><span><?php echo esc_html( wd_date( $wd_d['date'], 'l' ) ); ?></span><b><?php echo esc_html( wd_date( $wd_d['date'], 'j F Y' ) ); ?></b></p><?php endif; ?>
			<?php if ( $wd_place ) : ?><p class="st-place"><?php echo wd_icon( 'pin', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $wd_place ); ?></p><?php endif; ?>
			<?php if ( $wd_d['tagline'] ) : ?><p class="st-tagline"><?php echo esc_html( $wd_d['tagline'] ); ?></p><?php endif; ?>
			<?php if ( $wd_next[0] ) : ?>
				<p class="st-next"><?php echo esc_html( $wd_next[1] ? sprintf( /* translators: 1: program 2: date */ __( 'Next: %1$s, %2$s', 'wwmd' ), $wd_next[1]['title'], wd_date( $wd_next[1]['date'], 'D j M' ) ) : __( 'Counting down', 'wwmd' ) ); ?></p>
				<?php echo wd_tpl_countdown( $wd_next[0], 'st-count' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php endif; ?>
			<?php echo wd_tpl_buttons( $wd_on, $wd_d, get_permalink( $wd_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php echo wd_tpl_gift( $wd_id, $wd_d ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( $wd_d['hashtag'] ) : ?><p class="st-hash">#<?php echo esc_html( $wd_d['hashtag'] ); ?></p><?php endif; ?>
		</div>
	</div>
	<div class="wrap st-hero__foot"><?php echo wd_tpl_buzz( $wd_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
	<?php wd_tpl_share( $wd_id, $wd_d ); ?>
</section>
