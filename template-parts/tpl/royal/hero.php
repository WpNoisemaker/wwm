<?php
/**
 * Royal: the top of the page as a royal proclamation. Kente bands, the crest, the names in gold foil
 * & the date set like an engraved card, beside the couple's portrait in a gold frame.
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
<section class="ro-hero ro-hero--<?php echo esc_attr( $wd_lay ); ?>" id="top">
	<div class="ro-band" aria-hidden="true"></div>
	<div class="wrap ro-hero__in">
		<?php if ( 'names' !== $wd_lay ) : ?>
			<figure class="ro-portrait">
				<span class="ro-portrait__frame"><?php echo wd_tpl_cover( $wd_id, $wd_d ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				<?php if ( $wd_st[1] ) : ?><figcaption class="ro-portrait__status"><?php echo esc_html( $wd_st[1] ); ?></figcaption><?php endif; ?>
			</figure>
		<?php endif; ?>
		<div class="ro-procl">
			<span class="ro-crest"><?php echo wd_orn( 'crest', wd_initials( $wd_d ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			<?php if ( 'names' === $wd_lay && $wd_st[1] ) : ?><p class="ro-status"><?php echo esc_html( $wd_st[1] ); ?></p><?php endif; ?>
			<p class="ro-kicker"><?php echo esc_html( $wd_d['invite']['hosts'] ? $wd_d['invite']['hosts'] : __( 'Together with their families', 'wwmd' ) ); ?></p>
			<p class="ro-request"><?php esc_html_e( 'request the honour of your presence at the marriage of', 'wwmd' ); ?></p>
			<h1 class="ro-names">
				<span class="ro-foil"><?php echo esc_html( $wd_d['p1'] ); ?></span>
				<span class="ro-amp" aria-label="<?php esc_attr_e( 'and', 'wwmd' ); ?>">&amp;</span>
				<span class="ro-names__last"><span class="ro-foil"><?php echo esc_html( $wd_d['p2'] ); ?></span><?php echo wd_vbadge( $wd_id, 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			</h1>
			<?php if ( $wd_d['date'] ) : ?>
				<p class="ro-date">
					<span><?php echo esc_html( wd_date( $wd_d['date'], 'l' ) ); ?></span>
					<b><?php echo esc_html( wd_date( $wd_d['date'], 'j' ) ); ?></b>
					<span><?php echo esc_html( wd_date( $wd_d['date'], 'F Y' ) ); ?></span>
				</p>
			<?php endif; ?>
			<?php if ( $wd_place ) : ?><p class="ro-place"><?php echo esc_html( $wd_place ); ?></p><?php endif; ?>
			<?php if ( $wd_d['tagline'] ) : ?><p class="ro-tagline"><?php echo esc_html( $wd_d['tagline'] ); ?></p><?php endif; ?>
			<?php if ( $wd_d['hashtag'] ) : ?><p class="ro-hash">#<?php echo esc_html( $wd_d['hashtag'] ); ?></p><?php endif; ?>
			<?php if ( $wd_next[0] ) : ?>
				<p class="ro-next"><?php echo esc_html( $wd_next[1] ? sprintf( /* translators: 1: program 2: date */ __( 'Next: %1$s, %2$s', 'wwmd' ), $wd_next[1]['title'], wd_date( $wd_next[1]['date'], 'D j M' ) ) : __( 'Counting down', 'wwmd' ) ); ?></p>
				<?php echo wd_tpl_countdown( $wd_next[0], 'ro-count' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php endif; ?>
			<?php echo wd_tpl_buttons( $wd_on, $wd_d, get_permalink( $wd_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php echo wd_tpl_gift( $wd_id, $wd_d ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	</div>
	<div class="wrap ro-hero__foot"><?php echo wd_tpl_buzz( $wd_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
	<div class="ro-band" aria-hidden="true"></div>
	<?php wd_tpl_share( $wd_id, $wd_d ); ?>
</section>
