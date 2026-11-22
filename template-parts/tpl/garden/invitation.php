<?php
/**
 * Garden: the couple's note on a sheet of vellum, then a soft card for each program in a centred row.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_d     = $args['d'];
$wd_progs = wd_programs( $wd_d );
?>
<section class="w-sec ga-sec ga-invite" id="invitation">
	<div class="wrap">
		<div class="w-head ga-head">
			<p class="w-eyebrow"><?php esc_html_e( 'The invitation', 'wwmd' ); ?></p>
			<h2 class="w-title"><?php echo esc_html( count( $wd_progs ) > 1 ? __( 'Our wedding days', 'wwmd' ) : __( 'Our wedding day', 'wwmd' ) ); ?></h2>
		</div>
		<?php if ( $wd_d['invite']['message'] ) : ?>
			<div class="ga-letter">
				<?php echo wd_ga_sprig( 'ga-letter__sprig' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<p><?php echo nl2br( esc_html( $wd_d['invite']['message'] ) ); ?></p>
			</div>
		<?php endif; ?>
		<?php if ( $wd_progs ) : ?>
			<ol class="ga-days">
				<?php foreach ( $wd_progs as $wd_e ) : ?>
					<?php $wd_time = wd_prog_time( $wd_e ); ?>
					<li class="ga-day">
						<span class="ga-day__icon"><?php echo wd_icon( $wd_e['icon'], 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<p class="ga-day__type"><?php echo esc_html( $wd_e['label'] ); ?></p>
						<h3><?php echo esc_html( $wd_e['title'] ); ?></h3>
						<?php if ( ! empty( $wd_e['private'] ) ) : ?><p class="ga-private"><?php esc_html_e( 'By invitation only', 'wwmd' ); ?></p><?php endif; ?>
						<?php if ( $wd_e['date'] ) : ?>
							<p class="ga-day__date"><span class="ga-script"><?php echo esc_html( wd_date( $wd_e['date'], 'l' ) ); ?></span><b><?php echo esc_html( wd_date( $wd_e['date'], 'j F Y' ) ); ?></b><?php if ( $wd_time ) : ?><small><?php echo esc_html( $wd_time ); ?></small><?php endif; ?></p>
						<?php endif; ?>
						<p class="ga-day__place"><?php echo esc_html( wd_prog_place( $wd_e ) ); ?></p>
						<?php if ( $wd_e['note'] ) : ?><p class="ga-day__note"><?php echo esc_html( $wd_e['note'] ); ?></p><?php endif; ?>
						<?php echo wd_prog_dress( $wd_e, 'ga-day__dress' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php echo wd_prog_plan( $wd_e, 'ga-plan' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php echo wd_prog_links( $wd_e, $wd_d, 'ga-day__links' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>
	</div>
</section>
