<?php
/**
 * Ivory & Gold: the celebrations (one card each), the colours of the day, the order of the day
 * & the venue.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_id    = $args['id'];
$wd_d     = $args['d'];
$wd_progs = wd_programs( $wd_d );
$wd_cv    = wd_custom_vals( $wd_d, 'ivory' );
$wd_cols  = wd_colour_lines( $wd_cv['colours'] ?? '' );
$wd_plans = array();
foreach ( $wd_progs as $wd_e ) {
	$wd_lines = wd_plan_lines( $wd_e['plan'] );
	if ( $wd_lines ) {
		$wd_plans[] = array( $wd_e, $wd_lines );
	}
}
// The venue card: the main venue & the address of the program held there.
$wd_vaddr = '';
foreach ( $wd_progs as $wd_e ) {
	if ( $wd_d['venue'] && empty( $wd_e['private'] ) && 0 === strcasecmp( trim( $wd_e['venue'] ), trim( $wd_d['venue'] ) ) ) {
		$wd_vaddr = $wd_e['address'];
		break;
	}
}
if ( ! $wd_vaddr ) {
	$wd_vaddr = trim( $wd_d['city'] . ( $wd_d['country'] ? ', ' . $wd_d['country'] : '' ), ', ' );
}
$wd_map = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( trim( $wd_d['venue'] . ' ' . $wd_vaddr ) );
?>
<section class="w-sec iv-sec" id="invitation">
	<div class="wrap">
		<div class="iv-head">
			<p class="iv-kicker"><?php esc_html_e( 'Save the dates', 'wwmd' ); ?></p>
			<h2 class="iv-title"><?php echo esc_html( count( $wd_progs ) > 1 ? __( 'Our Celebrations', 'wwmd' ) : __( 'Our Celebration', 'wwmd' ) ); ?></h2>
			<span class="iv-rule" aria-hidden="true"><i class="iv-gem"></i></span>
		</div>
		<?php if ( $wd_progs ) : ?>
			<div class="iv-events">
				<?php foreach ( $wd_progs as $wd_e ) : ?>
					<?php
					$wd_priv = ! empty( $wd_e['private'] );
					$wd_cal  = wd_prog_gcal( $wd_e, $wd_d );
					$wd_time = $wd_e['time'] ? gmdate( 'g:i a', strtotime( '2000-01-01 ' . $wd_e['time'] ) ) : '';
					?>
					<article class="iv-card iv-event">
						<?php if ( 'other' !== $wd_e['type'] || $wd_e['kind'] ) : ?><p class="iv-kicker"><?php echo esc_html( $wd_e['label'] ); ?></p><?php endif; ?>
						<h3 class="iv-event__t"><?php echo esc_html( $wd_e['title'] ); ?></h3>
						<p class="iv-event__when"><?php echo esc_html( trim( wd_date( $wd_e['date'], 'l j F Y' ) . ( $wd_time ? ' · ' . $wd_time : '' ), ' ·' ) ); ?></p>
						<?php if ( $wd_e['note'] ) : ?><p class="iv-event__note"><?php echo esc_html( $wd_e['note'] ); ?></p><?php endif; ?>
						<span class="iv-rule iv-rule--sm" aria-hidden="true"><i class="iv-gem"></i></span>
						<dl class="iv-event__dl">
							<div><dt><?php esc_html_e( 'Where', 'wwmd' ); ?></dt><dd><?php echo esc_html( $wd_priv ? __( 'Shared with invited guests', 'wwmd' ) : trim( $wd_e['venue'] . ( $wd_e['address'] && false === stripos( $wd_e['venue'], $wd_e['address'] ) ? ', ' . $wd_e['address'] : '' ), ', ' ) ); ?></dd></div>
							<?php if ( $wd_e['dress'] ) : ?>
								<div><dt><?php esc_html_e( 'Dress code', 'wwmd' ); ?></dt><dd><?php echo esc_html( $wd_e['dress'] ); ?><?php foreach ( array( $wd_e['c1'], $wd_e['c2'] ) as $wd_c ) : ?><?php if ( $wd_c ) : ?><i class="swatch" style="background:<?php echo esc_attr( $wd_c ); ?>"></i><?php endif; ?><?php endforeach; ?></dd></div>
							<?php endif; ?>
						</dl>
						<p class="iv-event__links">
							<?php if ( ! $wd_priv && ( $wd_e['venue'] || $wd_e['address'] ) ) : ?><a href="<?php echo esc_url( 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( trim( $wd_e['venue'] . ' ' . $wd_e['address'] ) ) ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'pin', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Directions', 'wwmd' ); ?></a><?php endif; ?>
							<?php if ( $wd_cal ) : ?><a href="<?php echo esc_url( $wd_cal ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'calendar', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Add to calendar', 'wwmd' ); ?></a><?php endif; ?>
						</p>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( $wd_cols || $wd_plans || $wd_d['venue'] ) : ?>
			<div class="iv-extras">
				<?php if ( $wd_cols ) : ?>
					<article class="iv-card iv-colours">
						<p class="iv-kicker"><?php esc_html_e( 'Colours of the day', 'wwmd' ); ?></p>
						<h3 class="iv-h3"><?php esc_html_e( 'Dress in our palette', 'wwmd' ); ?></h3>
						<ul class="iv-colours__list">
							<?php foreach ( $wd_cols as $wd_c ) : ?>
								<li><i style="background:<?php echo esc_attr( $wd_c[1] ); ?>"></i><b><?php echo esc_html( $wd_c[0] ); ?></b><small><?php echo esc_html( $wd_c[1] ); ?></small></li>
							<?php endforeach; ?>
						</ul>
					</article>
				<?php endif; ?>
				<?php foreach ( $wd_plans as $wd_pp ) : ?>
					<article class="iv-card iv-plan">
						<p class="iv-kicker"><?php echo esc_html( sprintf( /* translators: %s program */ __( 'Order of the day · %s', 'wwmd' ), $wd_pp[0]['title'] ) ); ?></p>
						<h3 class="iv-h3"><?php echo esc_html( wd_date( $wd_pp[0]['date'], 'l j F' ) ); ?></h3>
						<ol class="iv-plan__list">
							<?php foreach ( $wd_pp[1] as $wd_pl ) : ?>
								<li><span class="iv-plan__time"><?php echo esc_html( $wd_pl['time'] ); ?></span><div><b><?php echo esc_html( $wd_pl['title'] ); ?></b><?php if ( $wd_pl['note'] ) : ?><p><?php echo esc_html( $wd_pl['note'] ); ?></p><?php endif; ?></div></li>
							<?php endforeach; ?>
						</ol>
					</article>
				<?php endforeach; ?>
				<?php if ( $wd_d['venue'] ) : ?>
					<article class="iv-card iv-venue">
						<?php if ( ! empty( $wd_cv['venue_pic'] ) ) : ?><figure class="iv-venue__pic"><img src="<?php echo esc_url( wd_img_url( $wd_cv['venue_pic'], 'wd-card' ) ); ?>" alt="<?php echo esc_attr( $wd_d['venue'] ); ?>" loading="lazy"></figure><?php endif; ?>
						<p class="iv-kicker"><?php esc_html_e( 'The venue', 'wwmd' ); ?></p>
						<h3 class="iv-h3"><?php echo esc_html( $wd_d['venue'] ); ?></h3>
						<?php if ( $wd_vaddr ) : ?><p class="iv-venue__addr"><?php echo esc_html( $wd_vaddr ); ?></p><?php endif; ?>
						<a class="btn iv-btn iv-btn--ghost" href="<?php echo esc_url( $wd_map ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'pin', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Open the map', 'wwmd' ); ?></a>
					</article>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
