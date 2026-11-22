<?php
/**
 * Pearl: calm & expensive. One framed photograph with a fine champagne rule stepped out behind it,
 * a pearl-ringed monogram that draws itself, and the names set hard to the left on a single spine
 * with the right-hand margin left deliberately empty.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_id    = $args['id'];
$wd_d     = $args['d'];
$wd_on    = $args['on'];
$wd_lay   = wd_layout( $wd_id, $wd_d )['hero'];
$wd_st    = wd_status( $wd_id, $wd_d );
$wd_next  = wd_tpl_next( $wd_d );
$wd_place = wd_tpl_place( $wd_d );
?>
<section class="pe-hero pe-hero--<?php echo esc_attr( $wd_lay ); ?>" id="top">
	<div class="wrap pe-hero__in">
		<?php if ( 'names' !== $wd_lay ) : ?>
			<figure class="pe-photo">
				<span class="pe-photo__frame"><?php echo wd_tpl_cover( $wd_id, $wd_d ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				<?php if ( $wd_st[1] ) : ?><figcaption class="pe-status"><?php echo esc_html( $wd_st[1] ); ?></figcaption><?php endif; ?>
			</figure>
		<?php endif; ?>
		<div class="pe-hero__text">
			<?php echo wd_pe_mono( wd_initials( $wd_d ), 'pe-mono' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( 'names' === $wd_lay && $wd_st[1] ) : ?><p class="pe-status pe-status--inline"><?php echo esc_html( $wd_st[1] ); ?></p><?php endif; ?>
			<p class="pe-kicker"><?php echo esc_html( $wd_d['invite']['hosts'] ? $wd_d['invite']['hosts'] : __( 'Together with their families', 'wwmd' ) ); ?></p>
			<h1 class="pe-names">
				<span><?php echo esc_html( $wd_d['p1'] ); ?></span>
				<span class="pe-amp" aria-label="<?php esc_attr_e( 'and', 'wwmd' ); ?>">&amp;</span>
				<span><?php echo esc_html( $wd_d['p2'] ); ?><?php echo wd_vbadge( $wd_id, 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			</h1>
			<?php echo wd_pe_pearls(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( $wd_d['date'] || $wd_place ) : ?>
				<div class="pe-hero__meta">
					<?php if ( $wd_d['date'] ) : ?><p class="pe-date"><?php echo esc_html( wd_date( $wd_d['date'], 'l' ) ); ?><span aria-hidden="true">·</span><?php echo esc_html( wd_date( $wd_d['date'], 'j F Y' ) ); ?></p><?php endif; ?>
					<?php if ( $wd_place ) : ?><p class="pe-place"><?php echo esc_html( $wd_place ); ?></p><?php endif; ?>
				</div>
			<?php endif; ?>
			<?php if ( $wd_d['tagline'] ) : ?><p class="pe-tagline"><?php echo esc_html( $wd_d['tagline'] ); ?></p><?php endif; ?>
			<?php if ( $wd_next[0] ) : ?>
				<p class="pe-next"><?php echo esc_html( $wd_next[1] ? sprintf( /* translators: 1: program 2: date */ __( 'Next: %1$s, %2$s', 'wwmd' ), $wd_next[1]['title'], wd_date( $wd_next[1]['date'], 'D j M' ) ) : __( 'Counting down', 'wwmd' ) ); ?></p>
				<?php echo wd_tpl_countdown( $wd_next[0], 'pe-count' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php endif; ?>
			<?php echo wd_tpl_buttons( $wd_on, $wd_d, get_permalink( $wd_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php echo wd_tpl_gift( $wd_id, $wd_d ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( $wd_d['hashtag'] ) : ?><p class="pe-hash">#<?php echo esc_html( $wd_d['hashtag'] ); ?></p><?php endif; ?>
		</div>
	</div>
	<div class="wrap pe-hero__foot"><?php echo wd_tpl_buzz( $wd_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
	<?php wd_tpl_share( $wd_id, $wd_d ); ?>
</section>
