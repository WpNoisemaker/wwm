<?php
/**
 * Ivory & Gold: the couple's sign-off at the bottom of the page.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_d  = $args['d'];
$wd_cv = wd_custom_vals( $wd_d, 'ivory' );
if ( '' === trim( (string) ( $wd_cv['sign'] ?? '' ) ) ) {
	return;
}
?>
<section class="iv-sign">
	<p class="iv-sign__mono iv-foil"><?php echo esc_html( wd_initials( $wd_d ) ); ?></p>
	<p class="iv-sign__line"><?php echo esc_html( trim( $wd_cv['sign'] . ( $wd_d['date'] ? ' · ' . wd_date( $wd_d['date'], 'F Y' ) : '' ) ) ); ?></p>
</section>
