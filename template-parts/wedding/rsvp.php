<?php
/**
 * RSVP (with an entry pass on Premium pages).
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_id   = $args['id'];
$wd_d    = $args['d'];
$wd_pass = wd_can( $wd_id, 'entry_pass' ) && ! empty( $wd_d['pass']['on'] );
$wd_days = wd_days_to( $wd_d['date'] );
$wd_late = $wd_d['rsvp']['deadline'] && wd_days_to( $wd_d['rsvp']['deadline'] ) < 0;
?>
<section class="w-sec w-sec--tint" id="rsvp">
	<div class="wrap rsvp">
		<div class="rsvp__intro">
			<p class="w-eyebrow"><?php esc_html_e( 'RSVP', 'wwmd' ); ?></p>
			<h2 class="w-title"><?php esc_html_e( 'Will you celebrate with us?', 'wwmd' ); ?></h2>
			<?php if ( $wd_pass ) : ?>
				<p><?php esc_html_e( 'Once you RSVP you get an entry pass with a QR code. Show it at the gate, on your phone or printed.', 'wwmd' ); ?></p>
			<?php else : ?>
				<p><?php esc_html_e( 'Let the couple know you are coming so they can save your seat.', 'wwmd' ); ?></p>
			<?php endif; ?>
			<?php if ( $wd_d['rsvp']['deadline'] ) : ?><p class="rsvp__by"><?php echo wd_icon( 'calendar', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( sprintf( /* translators: %s date */ __( 'Please RSVP by %s', 'wwmd' ), wd_date( $wd_d['rsvp']['deadline'], 'j F Y' ) ) ); ?></p><?php endif; ?>
			<div class="rsvp__art" aria-hidden="true"><?php echo wd_art( $wd_pass ? 'qr' : 'calabash' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		</div>
		<?php if ( null !== $wd_days && $wd_days < 0 ) : ?>
			<div class="rsvp__closed"><p><?php esc_html_e( 'This wedding has happened. Thank you to everyone who came!', 'wwmd' ); ?></p></div>
		<?php elseif ( $wd_late ) : ?>
			<div class="rsvp__closed"><p><?php esc_html_e( 'RSVPs are closed. Please reach the couple directly.', 'wwmd' ); ?></p></div>
		<?php else : ?>
			<form class="form card-form" data-rsvp novalidate>
				<input type="hidden" name="wedding" value="<?php echo (int) $wd_id; ?>">
				<div class="seg" role="radiogroup" aria-label="<?php esc_attr_e( 'Are you coming?', 'wwmd' ); ?>">
					<label><input type="radio" name="attending" value="yes" checked><span><?php esc_html_e( 'Joyfully accept', 'wwmd' ); ?></span></label>
					<label><input type="radio" name="attending" value="no"><span><?php esc_html_e( 'Regretfully decline', 'wwmd' ); ?></span></label>
				</div>
				<label class="field"><span><?php esc_html_e( 'Your full name', 'wwmd' ); ?></span><input name="name" required autocomplete="name"></label>
				<div class="field-row">
					<label class="field"><span><?php esc_html_e( 'Phone (WhatsApp)', 'wwmd' ); ?></span><input name="phone" type="tel" autocomplete="tel"></label>
					<label class="field"><span><?php esc_html_e( 'Email (optional)', 'wwmd' ); ?></span><input name="email" type="email" autocomplete="email"></label>
				</div>
				<div class="field-row" data-if-yes>
					<label class="field"><span><?php esc_html_e( 'How many of you?', 'wwmd' ); ?></span>
						<select name="guests"><?php for ( $wd_i = 1; $wd_i <= max( 1, (int) $wd_d['rsvp']['max'] ); $wd_i++ ) : ?><option value="<?php echo (int) $wd_i; ?>"><?php echo (int) $wd_i; ?></option><?php endfor; ?></select>
					</label>
					<label class="field"><span><?php esc_html_e( 'You are a guest of', 'wwmd' ); ?></span>
						<select name="side"><option value="both"><?php esc_html_e( 'Both of them', 'wwmd' ); ?></option><option value="bride"><?php echo esc_html( wd_side_name( $wd_d, 'bride' ) ); ?></option><option value="groom"><?php echo esc_html( wd_side_name( $wd_d, 'groom' ) ); ?></option></select>
					</label>
				</div>
				<?php $wd_open = array_filter( wd_programs( $wd_d ), function ( $e ) { return empty( $e['private'] ); } ); ?>
				<?php if ( count( $wd_open ) > 1 ) : ?>
					<fieldset class="field checks" data-if-yes><legend><?php esc_html_e( 'Which programs are you coming to?', 'wwmd' ); ?></legend>
						<?php foreach ( $wd_open as $wd_e ) : ?>
							<label><input type="checkbox" name="events[]" value="<?php echo esc_attr( $wd_e['title'] ); ?>" checked><span><?php echo esc_html( $wd_e['title'] . ( $wd_e['date'] ? ' · ' . wd_date( $wd_e['date'], 'D j M' ) : '' ) ); ?></span></label>
						<?php endforeach; ?>
					</fieldset>
				<?php endif; ?>
				<label class="field"><span><?php esc_html_e( 'A note for the couple (optional)', 'wwmd' ); ?></span><textarea name="note" rows="2"></textarea></label>
				<button class="btn w-btn btn--block" type="submit"><?php esc_html_e( 'Send my RSVP', 'wwmd' ); ?></button>
				<p class="form__msg" data-msg role="status"></p>
			</form>
			<div class="pass" hidden data-pass>
				<div class="pass__ticket">
					<p class="pass__k"><?php esc_html_e( 'Entry pass', 'wwmd' ); ?></p>
					<p class="pass__names"><?php echo esc_html( wd_names( $wd_d ) ); ?></p>
					<div class="pass__qr" data-pass-qr></div>
					<p class="pass__code" data-pass-code></p>
					<p class="pass__who" data-pass-who></p>
					<p class="pass__progs" data-pass-progs hidden></p>
					<p class="pass__date"><?php echo esc_html( wd_date( $wd_d['date'], 'j F Y' ) . ' · ' . $wd_d['city'] ); ?></p>
				</div>
				<div class="btn-row">
					<a class="btn w-btn" href="#" data-pass-link target="_blank" rel="noopener"><?php esc_html_e( 'Open my pass', 'wwmd' ); ?></a>
					<a class="btn w-btn w-btn--ghost" href="#" data-pass-wa target="_blank" rel="noopener"><?php echo wd_icon( 'whatsapp', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Save to WhatsApp', 'wwmd' ); ?></a>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>
