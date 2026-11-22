<?php
/**
 * Royal: the sign-off. The crest again, the names & the date in Roman numerals, between kente bands.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_d = $args['d'];
?>
<section class="ro-sign">
	<div class="ro-band" aria-hidden="true"></div>
	<div class="wrap ro-sign__in">
		<span class="ro-crest"><?php echo wd_orn( 'crest', wd_initials( $wd_d ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		<p class="ro-kicker"><?php esc_html_e( 'With love & gratitude', 'wwmd' ); ?></p>
		<p class="ro-sign__names"><span class="ro-foil"><?php echo esc_html( $wd_d['p1'] ); ?></span> <span class="ro-amp">&amp;</span> <span class="ro-foil"><?php echo esc_html( $wd_d['p2'] ); ?></span></p>
		<?php if ( $wd_d['date'] ) : ?>
			<p class="ro-sign__date" title="<?php echo esc_attr( wd_date( $wd_d['date'], 'j F Y' ) ); ?>"><?php echo esc_html( wd_roman( (int) wd_date( $wd_d['date'], 'j' ) ) . ' · ' . wd_roman( (int) wd_date( $wd_d['date'], 'n' ) ) . ' · ' . wd_roman( (int) wd_date( $wd_d['date'], 'Y' ) ) ); ?></p>
		<?php endif; ?>
	</div>
</section>
