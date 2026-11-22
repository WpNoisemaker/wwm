<?php
/**
 * "Not open just yet": shown in place of a feature (live stream or spraying) until its opening day.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_ymd   = (string) $args['ymd'];
$wd_today = wp_date( 'Y-m-d' ) === $wd_ymd;
$wd_what  = 'spray' === $args['kind'] ? __( 'Spraying', 'wwmd' ) : __( 'The live stream', 'wwmd' );
?>
<div class="opens-card">
	<span class="opens-card__lock"><?php echo wd_icon( 'lock', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
	<p class="opens-card__title"><?php esc_html_e( 'Not open just yet', 'wwmd' ); ?></p>
	<?php if ( $wd_today ) : ?>
		<p><?php echo esc_html( sprintf( /* translators: %s: the live stream or spraying */ __( '%s opens today. Check back here soon.', 'wwmd' ), $wd_what ) ); ?></p>
	<?php else : ?>
		<p><?php echo esc_html( sprintf( /* translators: %s: the live stream or spraying */ __( '%s opens on the day it is needed. Come back here then.', 'wwmd' ), $wd_what ) ); ?></p>
		<p class="opens-card__when"><?php echo esc_html( sprintf( /* translators: %s date */ __( 'Opens %s at 12:00 am', 'wwmd' ), wd_date( $wd_ymd, 'l j F' ) ) ); ?></p>
		<div class="countdown" data-countdown="<?php echo esc_attr( $wd_ymd ); ?>T00:00:00" aria-label="<?php esc_attr_e( 'Countdown', 'wwmd' ); ?>">
			<span><b data-cd="d">0</b><?php esc_html_e( 'days', 'wwmd' ); ?></span>
			<span><b data-cd="h">0</b><?php esc_html_e( 'hours', 'wwmd' ); ?></span>
			<span><b data-cd="m">0</b><?php esc_html_e( 'mins', 'wwmd' ); ?></span>
			<span><b data-cd="s">0</b><?php esc_html_e( 'secs', 'wwmd' ); ?></span>
		</div>
	<?php endif; ?>
</div>
