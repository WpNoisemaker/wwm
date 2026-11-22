<?php
/**
 * Pearl: the couple's note in a card with a fine double line, then one clean card for each program,
 * in a centred row.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_d     = $args['d'];
$wd_progs = wd_programs( $wd_d );
?>
<section class="w-sec pe-sec pe-invite" id="invitation">
	<div class="wrap">
		<div class="w-head pe-head">
			<p class="w-eyebrow"><?php esc_html_e( 'The invitation', 'wwmd' ); ?></p>
			<h2 class="w-title"><?php echo esc_html( count( $wd_progs ) > 1 ? __( 'Our wedding days', 'wwmd' ) : __( 'Our wedding day', 'wwmd' ) ); ?></h2>
		</div>
		<?php if ( $wd_d['invite']['message'] ) : ?>
			<div class="pe-letter">
				<p><?php echo nl2br( esc_html( $wd_d['invite']['message'] ) ); ?></p>
				<?php echo wd_pe_pearls(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		<?php endif; ?>
		<?php if ( $wd_progs ) : ?>
			<ol class="pe-days">
				<?php foreach ( $wd_progs as $wd_e ) : ?>
					<?php $wd_time = wd_prog_time( $wd_e ); ?>
					<li class="pe-day">
						<span class="pe-day__icon"><?php echo wd_icon( $wd_e['icon'], 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<p class="pe-day__type"><?php echo esc_html( $wd_e['label'] ); ?></p>
						<h3><?php echo esc_html( $wd_e['title'] ); ?></h3>
						<?php if ( ! empty( $wd_e['private'] ) ) : ?><p class="pe-private"><?php esc_html_e( 'By invitation only', 'wwmd' ); ?></p><?php endif; ?>
						<?php if ( $wd_e['date'] ) : ?>
							<p class="pe-day__date"><b><?php echo esc_html( wd_date( $wd_e['date'], 'j' ) ); ?></b><span><?php echo esc_html( wd_date( $wd_e['date'], 'F Y' ) ); ?><small><?php echo esc_html( wd_date( $wd_e['date'], 'l' ) . ( $wd_time ? ' · ' . $wd_time : '' ) ); ?></small></span></p>
						<?php endif; ?>
						<p class="pe-day__place"><?php echo esc_html( wd_prog_place( $wd_e ) ); ?></p>
						<?php if ( $wd_e['note'] ) : ?><p class="pe-day__note"><?php echo esc_html( $wd_e['note'] ); ?></p><?php endif; ?>
						<?php echo wd_prog_dress( $wd_e, 'pe-day__dress' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php echo wd_prog_plan( $wd_e, 'pe-plan' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php echo wd_prog_links( $wd_e, $wd_d, 'pe-day__links' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>
	</div>
</section>
