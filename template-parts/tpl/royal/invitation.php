<?php
/**
 * Royal: the order of ceremonies. The couple's message, then each program numbered in Roman
 * numerals like an engraved programme, with its own order of the day.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_d     = $args['d'];
$wd_progs = wd_programs( $wd_d );
?>
<section class="w-sec ro-sec ro-orders" id="invitation">
	<div class="wrap">
		<div class="ro-head">
			<p class="ro-kicker"><?php esc_html_e( 'The invitation', 'wwmd' ); ?></p>
			<h2 class="ro-title"><?php echo esc_html( count( $wd_progs ) > 1 ? __( 'The order of ceremonies', 'wwmd' ) : __( 'The ceremony', 'wwmd' ) ); ?></h2>
			<?php if ( $wd_d['invite']['message'] ) : ?><p class="ro-decree"><?php echo nl2br( esc_html( $wd_d['invite']['message'] ) ); ?></p><?php endif; ?>
		</div>
		<?php if ( $wd_progs ) : ?>
			<ol class="ro-ledger">
				<?php foreach ( $wd_progs as $wd_n => $wd_e ) : ?>
					<?php $wd_time = wd_prog_time( $wd_e ); ?>
					<li class="ro-ledger__item">
						<span class="ro-ledger__no" aria-hidden="true"><?php echo esc_html( wd_roman( $wd_n + 1 ) ); ?></span>
						<div class="ro-ledger__main">
							<p class="ro-ledger__type"><?php echo wd_icon( $wd_e['icon'], 15 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $wd_e['label'] ); ?></p>
							<h3><?php echo esc_html( $wd_e['title'] ); ?></h3>
							<?php if ( ! empty( $wd_e['private'] ) ) : ?><p class="ro-private"><?php esc_html_e( 'By invitation only', 'wwmd' ); ?></p><?php endif; ?>
							<p class="ro-ledger__place"><?php echo esc_html( wd_prog_place( $wd_e ) ); ?></p>
							<?php if ( $wd_e['note'] ) : ?><p class="ro-ledger__note"><?php echo esc_html( $wd_e['note'] ); ?></p><?php endif; ?>
							<?php echo wd_prog_dress( $wd_e, 'ro-ledger__dress' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<?php echo wd_prog_plan( $wd_e, 'ro-plan' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</div>
						<div class="ro-ledger__when">
							<?php if ( $wd_e['date'] ) : ?>
								<b><?php echo esc_html( wd_date( $wd_e['date'], 'j' ) ); ?></b>
								<span><?php echo esc_html( wd_date( $wd_e['date'], 'F Y' ) ); ?></span>
								<small><?php echo esc_html( trim( wd_date( $wd_e['date'], 'l' ) . ( $wd_time ? ' · ' . $wd_time : '' ) ) ); ?></small>
							<?php endif; ?>
							<?php echo wd_prog_links( $wd_e, $wd_d, 'ro-ledger__links' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>
	</div>
</section>
