<?php
/**
 * The sign-off for the Studio designs: initials, a thank-you, the names & the date.
 *
 * Args: id, d, tpl.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_d = $args['d'];
?>
<section class="st-sign">
	<?php echo wd_st_ornament( $args['tpl'], 'sign' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<div class="wrap st-sign__in">
		<p class="st-mono" aria-hidden="true"><?php echo esc_html( wd_initials( $wd_d ) ); ?></p>
		<p class="st-sign__kicker"><?php esc_html_e( 'Thank you for loving us', 'wwmd' ); ?></p>
		<p class="st-sign__names"><?php echo esc_html( $wd_d['p1'] ); ?> <span class="st-amp">&amp;</span> <?php echo esc_html( $wd_d['p2'] ); ?></p>
		<?php if ( $wd_d['date'] ) : ?><p class="st-sign__date"><?php echo esc_html( wd_date( $wd_d['date'], 'j F Y' ) ); ?></p><?php endif; ?>
	</div>
</section>
