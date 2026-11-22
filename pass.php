<?php
/**
 * Entry pass page (/pass/CODE/). The couple & their ushers see a Check in button.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
global $wpdb;
$wd_code = preg_replace( '/[^A-Z0-9]/', '', strtoupper( (string) get_query_var( 'wd_pass' ) ) );
$wd_row  = $wd_code ? $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . wd_t( 'rsvps' ) . ' WHERE pass_code = %s', $wd_code ) ) : null; // phpcs:ignore WordPress.DB
get_header();
?>
<section class="section pass-page">
	<div class="wrap narrow center">
		<?php
		if ( ! $wd_row ) :
			?>
			<h1><?php esc_html_e( 'Pass not found', 'wwmd' ); ?></h1>
			<p><?php esc_html_e( 'Check the link or ask the couple to resend it.', 'wwmd' ); ?></p>
			<?php
		else :
			$wd_id  = (int) $wd_row->wedding_id;
			$wd_d   = wd_get( $wd_id );
			$wd_ush = wd_is_usher( $wd_id );
			?>
			<div class="pass pass--page" data-pass-page data-code="<?php echo esc_attr( $wd_code ); ?>" data-url="<?php echo esc_url( home_url( '/pass/' . $wd_code . '/' ) ); ?>">
				<div class="pass__ticket">
					<p class="pass__k"><?php esc_html_e( 'Entry pass', 'wwmd' ); ?></p>
					<p class="pass__names"><?php echo esc_html( wd_names( $wd_d ) ); ?></p>
					<div class="pass__qr" data-pass-qr></div>
					<p class="pass__code"><?php echo esc_html( $wd_code ); ?></p>
					<p class="pass__who"><?php echo esc_html( $wd_row->name ); ?> · <?php echo esc_html( sprintf( /* translators: %d guests */ _n( 'admits %d', 'admits %d', (int) $wd_row->guests, 'wwmd' ), (int) $wd_row->guests ) ); ?></p>
					<p class="pass__date"><?php echo esc_html( wd_date( $wd_d['date'], 'j F Y' ) . ' · ' . ( $wd_d['venue'] ? $wd_d['venue'] : $wd_d['city'] ) ); ?></p>
					<?php if ( $wd_row->checked_in ) : ?><p class="pass__in"><?php echo wd_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Checked in', 'wwmd' ); ?></p><?php endif; ?>
				</div>
				<?php if ( $wd_ush ) : ?>
					<button class="btn btn--ink btn--lg" type="button" data-checkin <?php disabled( (bool) $wd_row->checked_in ); ?>><?php echo $wd_row->checked_in ? esc_html__( 'Already checked in', 'wwmd' ) : esc_html__( 'Check this guest in', 'wwmd' ); ?></button>
					<p class="form__msg" data-msg role="status"></p>
				<?php else : ?>
					<p class="fine"><?php esc_html_e( 'Show this at the gate. Ushers scan the code with their phone camera.', 'wwmd' ); ?></p>
					<p><a class="link-arrow" href="<?php echo esc_url( get_permalink( $wd_id ) ); ?>"><?php esc_html_e( 'Back to the wedding page', 'wwmd' ); ?></a></p>
					<?php if ( wd_can( $wd_id, 'entry_pass' ) && $wd_d['pass']['pin'] ) : ?>
						<details class="usher"><summary><?php esc_html_e( 'I am an usher', 'wwmd' ); ?></summary>
							<form class="form" data-usher><input type="hidden" name="wedding" value="<?php echo (int) $wd_id; ?>"><label class="field"><span><?php esc_html_e( 'Usher PIN from the couple', 'wwmd' ); ?></span><input name="pin" inputmode="numeric" maxlength="8" required></label><button class="btn btn--ink" type="submit"><?php esc_html_e( 'Unlock check-in', 'wwmd' ); ?></button><p class="form__msg" data-msg role="status"></p></form>
						</details>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
