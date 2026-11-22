<?php
/**
 * Garden: the sign-off. A wreath around the couple's initials, the names & the date.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_d = $args['d'];
?>
<section class="ga-sign">
	<div class="wrap ga-sign__in">
		<?php echo wd_ga_wreath( wd_initials( $wd_d ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<p class="ga-sign__kicker"><?php esc_html_e( 'Thank you for loving us', 'wwmd' ); ?></p>
		<p class="ga-sign__names"><?php echo esc_html( $wd_d['p1'] ); ?> <span class="ga-amp">&amp;</span> <?php echo esc_html( $wd_d['p2'] ); ?></p>
		<?php if ( $wd_d['date'] ) : ?><p class="ga-sign__date"><?php echo esc_html( wd_date( $wd_d['date'], 'j F Y' ) ); ?></p><?php endif; ?>
	</div>
</section>
