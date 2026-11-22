<?php
/**
 * Livestream (Premium): YouTube plays in the page once a guest presses play (so the page stays fast);
 * other links open in a new tab. Each guest who presses play counts once as a viewer for Buzz coins.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_d   = $args['d'];
$wd_url = $wd_d['live']['url'];
$wd_day = wd_days_to( $wd_d['date'] );
$wd_open = (string) ( $wd_d['live']['opens'] ?? '' );
// Until the opening day the couple picked, show a countdown. Without a link yet, wait through that day too.
$wd_soon = wd_opens_wait( $wd_open, ! $wd_url );
// Before the day (or on a starter page) show when it starts instead of the player.
$wd_wait = ( ! $wd_open && null !== $wd_day && $wd_day > 0 ) || get_post_meta( $args['id'], '_wd_sample', true );
$wd_yt  = '';
if ( preg_match( '#(?:youtube\.com/(?:watch\?v=|live/|embed/)|youtu\.be/)([A-Za-z0-9_-]{6,})#', $wd_url, $wd_m ) ) {
	$wd_yt = 'https://www.youtube-nocookie.com/embed/' . $wd_m[1];
}
?>
<section class="w-sec w-sec--dark" id="livestream">
	<div class="wrap live">
		<div class="live__head">
			<p class="w-eyebrow"><span class="live-dot"></span><?php esc_html_e( 'Livestream', 'wwmd' ); ?></p>
			<h2 class="w-title"><?php esc_html_e( 'Watch with us, wherever you are', 'wwmd' ); ?></h2>
			<?php if ( $wd_d['live']['note'] ) : ?><p><?php echo esc_html( $wd_d['live']['note'] ); ?></p><?php endif; ?>
		</div>
		<?php if ( $wd_soon ) : ?>
			<?php get_template_part( 'template-parts/wedding/opens', null, array( 'kind' => 'live', 'ymd' => $wd_open ) ); ?>
		<?php elseif ( $wd_wait ) : ?>
			<div class="live__card live__card--soon"><?php echo wd_art( 'camera' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span>
				<?php
				if ( null !== $wd_day && $wd_day < 0 ) {
					esc_html_e( 'The livestream has ended. Thank you for watching with us!', 'wwmd' );
				} else {
					/* translators: 1: date 2: time */
					echo esc_html( sprintf( __( 'We go live on %1$s%2$s. The player shows up right here.', 'wwmd' ), wd_date( $wd_d['date'], 'l j F' ), $wd_d['time'] ? ' ' . sprintf( /* translators: %s time */ __( 'at %s', 'wwmd' ), $wd_d['time'] ) : '' ) );
				}
				?>
			</span></div>
		<?php elseif ( $wd_yt ) : ?>
			<div class="live__frame" data-live-frame data-src="<?php echo esc_url( $wd_yt . '?autoplay=1&rel=0' ); ?>" data-title="<?php esc_attr_e( 'Wedding livestream', 'wwmd' ); ?>">
				<button class="live__play" type="button" data-live-play>
					<span class="live__btn"><?php echo wd_icon( 'play', 34 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<span class="live__cta"><span class="live-dot"></span><?php esc_html_e( 'Watch the livestream', 'wwmd' ); ?></span>
				</button>
			</div>
		<?php else : ?>
			<a class="live__card" href="<?php echo esc_url( $wd_url ); ?>" target="_blank" rel="noopener" data-live-link><?php echo wd_art( 'camera' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Open the livestream', 'wwmd' ); ?><?php echo wd_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span></a>
		<?php endif; ?>
	</div>
</section>
