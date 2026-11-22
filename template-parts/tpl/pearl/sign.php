<?php
/**
 * Pearl: the sign-off. The pearl-ringed monogram, a thank-you, the names & the date, left-aligned on
 * a slow champagne wash.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_d = $args['d'];
?>
<section class="pe-sign">
	<div class="wrap pe-sign__in">
		<?php echo wd_pe_mono( wd_initials( $wd_d ), 'pe-mono pe-mono--sign' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<p class="pe-sign__kicker"><?php esc_html_e( 'Thank you for loving us', 'wwmd' ); ?></p>
		<p class="pe-sign__names"><?php echo esc_html( $wd_d['p1'] ); ?> <span class="pe-amp">&amp;</span> <?php echo esc_html( $wd_d['p2'] ); ?></p>
		<?php if ( $wd_d['date'] ) : ?><p class="pe-sign__date"><?php echo esc_html( wd_date( $wd_d['date'], 'j F Y' ) ); ?></p><?php endif; ?>
	</div>
</section>
