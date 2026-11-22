<?php
/**
 * Garden: a wide, soft photo with fine-line sprigs, & the invitation card resting over its edge.
 * Pages with no cover photo get a drawn wreath instead.
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
<section class="ga-hero ga-hero--<?php echo esc_attr( $wd_lay ); ?>" id="top">
	<div class="wrap ga-hero__in">
		<?php if ( 'names' === $wd_lay ) : ?>
			<div class="ga-crest"><?php echo wd_ga_wreath( wd_initials( $wd_d ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		<?php else : ?>
			<figure class="ga-photo<?php echo $wd_d['cover'] ? '' : ' ga-photo--art'; ?>">
				<?php echo $wd_d['cover'] ? wd_tpl_cover( $wd_id, $wd_d ) : wd_ga_wreath( wd_initials( $wd_d ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php echo wd_ga_sprig( 'ga-photo__sprig ga-photo__sprig--l' ) . wd_ga_sprig( 'ga-photo__sprig ga-photo__sprig--r' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</figure>
		<?php endif; ?>
		<div class="ga-card">
			<?php if ( $wd_st[1] ) : ?><p class="ga-status"><?php echo esc_html( $wd_st[1] ); ?></p><?php endif; ?>
			<p class="ga-kicker"><?php echo esc_html( $wd_d['invite']['hosts'] ? $wd_d['invite']['hosts'] : __( 'Together with their families', 'wwmd' ) ); ?></p>
			<p class="ga-invite"><?php esc_html_e( 'joyfully invite you to the wedding of', 'wwmd' ); ?></p>
			<h1 class="ga-names">
				<span><?php echo esc_html( $wd_d['p1'] ); ?></span>
				<span class="ga-amp" aria-label="<?php esc_attr_e( 'and', 'wwmd' ); ?>">&amp;</span>
				<span><?php echo esc_html( $wd_d['p2'] ); ?><?php echo wd_vbadge( $wd_id, 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			</h1>
			<?php if ( $wd_d['date'] ) : ?><p class="ga-date"><span aria-hidden="true"></span><?php echo esc_html( wd_date( $wd_d['date'], 'l, j F Y' ) ); ?><span aria-hidden="true"></span></p><?php endif; ?>
			<?php if ( $wd_place ) : ?><p class="ga-place"><?php echo esc_html( $wd_place ); ?></p><?php endif; ?>
			<?php if ( $wd_d['tagline'] ) : ?><p class="ga-tagline"><?php echo esc_html( $wd_d['tagline'] ); ?></p><?php endif; ?>
			<?php if ( $wd_next[0] ) : ?>
				<p class="ga-next"><?php echo esc_html( $wd_next[1] ? sprintf( /* translators: 1: program 2: date */ __( 'Next: %1$s, %2$s', 'wwmd' ), $wd_next[1]['title'], wd_date( $wd_next[1]['date'], 'D j M' ) ) : __( 'Counting down', 'wwmd' ) ); ?></p>
				<?php echo wd_tpl_countdown( $wd_next[0], 'ga-count' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php endif; ?>
			<?php echo wd_tpl_buttons( $wd_on, $wd_d, get_permalink( $wd_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php echo wd_tpl_gift( $wd_id, $wd_d ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( $wd_d['hashtag'] ) : ?><p class="ga-hash">#<?php echo esc_html( $wd_d['hashtag'] ); ?></p><?php endif; ?>
		</div>
	</div>
	<div class="wrap ga-hero__foot"><?php echo wd_tpl_buzz( $wd_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
	<?php wd_tpl_share( $wd_id, $wd_d ); ?>
</section>
