<?php
/**
 * Invitation & programs (a wedding can have several: traditional, nikkah, church, reception…).
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_d = $args['d'];
?>
<section class="w-sec" id="invitation">
	<div class="wrap">
		<div class="invite">
			<div class="invite__card">
				<?php if ( $wd_d['invite']['hosts'] ) : ?><p class="invite__hosts"><?php echo esc_html( $wd_d['invite']['hosts'] ); ?></p><?php endif; ?>
				<p class="invite__kicker"><?php esc_html_e( 'request the pleasure of your company at the wedding of', 'wwmd' ); ?></p>
				<p class="invite__names"><?php echo esc_html( $wd_d['p1'] ); ?> <span>&amp;</span> <?php echo esc_html( $wd_d['p2'] ); ?></p>
				<?php if ( $wd_d['invite']['message'] ) : ?><p class="invite__msg"><?php echo nl2br( esc_html( $wd_d['invite']['message'] ) ); ?></p><?php endif; ?>
				<p class="invite__date"><?php echo esc_html( wd_date( $wd_d['date'], 'l, j F Y' ) ); ?></p>
			</div>
			<?php $wd_progs = wd_programs( $wd_d ); ?>
			<?php if ( $wd_progs ) : ?>
				<div class="events">
					<h2 class="w-title"><?php echo esc_html( count( $wd_progs ) > 1 ? __( 'The programs', 'wwmd' ) : __( 'The day', 'wwmd' ) ); ?></h2>
					<?php
					foreach ( $wd_progs as $wd_e ) :
						$wd_priv  = ! empty( $wd_e['private'] );
						$wd_start = $wd_e['date'] ? gmdate( 'Ymd', strtotime( $wd_e['date'] ) ) . ( $wd_e['time'] ? 'T' . str_replace( ':', '', $wd_e['time'] ) . '00' : '' ) : '';
						$wd_cal   = $wd_start ? add_query_arg(
							array(
								'action'   => 'TEMPLATE',
								'text'     => rawurlencode( $wd_e['title'] . ' · ' . wd_names( $wd_d ) ),
								'dates'    => $wd_start . '/' . $wd_start,
								'location' => $wd_priv ? '' : rawurlencode( $wd_e['venue'] . ' ' . $wd_e['address'] ),
							),
							'https://calendar.google.com/calendar/render'
						) : '';
						?>
						<article class="event">
							<div class="event__when"><b><?php echo esc_html( wd_date( $wd_e['date'], 'j' ) ); ?></b><span><?php echo esc_html( wd_date( $wd_e['date'], 'M' ) ); ?></span></div>
							<div class="event__body">
								<p class="event__type"><?php echo wd_icon( $wd_e['icon'], 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $wd_e['label'] ); ?></p>
								<h3><?php echo esc_html( $wd_e['title'] ); ?><?php if ( $wd_priv ) : ?><span class="event__private"><?php esc_html_e( 'By invitation only', 'wwmd' ); ?></span><?php endif; ?></h3>
								<p class="event__meta"><?php echo esc_html( trim( wd_date( $wd_e['date'], 'l' ) . ( $wd_e['time'] ? ', ' . gmdate( 'g:i a', strtotime( '2000-01-01 ' . $wd_e['time'] ) ) : '' ) ) ); ?></p>
								<?php if ( $wd_e['note'] ) : ?><p class="event__note"><?php echo esc_html( $wd_e['note'] ); ?></p><?php endif; ?>
								<p class="event__venue"><?php echo wd_icon( 'pin', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $wd_priv ? __( 'The couple shares the venue with invited guests.', 'wwmd' ) : wd_prog_place( $wd_e ) ); ?></p>
								<?php if ( $wd_e['dress'] ) : ?>
									<p class="event__dress"><span><?php esc_html_e( 'Dress code', 'wwmd' ); ?></span> <?php echo esc_html( $wd_e['dress'] ); ?>
									<?php foreach ( array( $wd_e['c1'], $wd_e['c2'] ) as $wd_c ) : ?><?php if ( $wd_c ) : ?><i class="swatch" style="background:<?php echo esc_attr( $wd_c ); ?>"></i><?php endif; ?><?php endforeach; ?></p>
								<?php endif; ?>
								<?php $wd_plan = wd_plan_lines( $wd_e['plan'] ); ?>
								<?php if ( $wd_plan ) : ?>
									<ol class="event__plan" aria-label="<?php esc_attr_e( 'Order of the day', 'wwmd' ); ?>">
										<?php foreach ( $wd_plan as $wd_pl ) : ?>
											<li><?php if ( $wd_pl['time'] ) : ?><b><?php echo esc_html( $wd_pl['time'] ); ?></b><?php endif; ?><span><?php echo esc_html( $wd_pl['title'] ); ?><?php if ( $wd_pl['note'] ) : ?><small><?php echo esc_html( $wd_pl['note'] ); ?></small><?php endif; ?></span></li>
										<?php endforeach; ?>
									</ol>
								<?php endif; ?>
								<p class="event__links">
									<?php if ( ! $wd_priv && ( $wd_e['venue'] || $wd_e['address'] ) ) : ?><a href="<?php echo esc_url( 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $wd_e['venue'] . ' ' . $wd_e['address'] ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Directions', 'wwmd' ); ?></a><?php endif; ?>
									<?php if ( $wd_cal ) : ?><a href="<?php echo esc_url( $wd_cal ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Add to calendar', 'wwmd' ); ?></a><?php endif; ?>
								</p>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
