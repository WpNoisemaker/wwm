<?php
/**
 * Spray money (Premium): tap to throw notes, then pay once.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
global $wpdb;
$wd_id    = $args['id'];
$wd_d     = $args['d'];
$wd_cur   = $wd_d['currency'];
$wd_notes = array(
	'NGN' => array( 500, 1000, 5000, 10000 ),
	'GHS' => array( 5, 10, 20, 50 ),
	'KES' => array( 100, 200, 500, 1000 ),
	'ZAR' => array( 20, 50, 100, 200 ),
	'USD' => array( 1, 5, 10, 20 ),
);
$wd_set   = isset( $wd_notes[ $wd_cur ] ) ? $wd_notes[ $wd_cur ] : $wd_notes['NGN'];
$wd_feed  = $wpdb->get_results( $wpdb->prepare( 'SELECT name, amount, currency, message, hide_amount, created FROM ' . wd_t( 'tx' ) . " WHERE wedding_id = %d AND kind = 'spray' AND status = 'paid' ORDER BY id DESC LIMIT 8", $wd_id ) ); // phpcs:ignore WordPress.DB
$wd_tot   = wd_money_totals( $wd_id );
$wd_pic   = $wd_d['photo'] ? $wd_d['photo'] : $wd_d['cover'];
$wd_open  = (string) ( $wd_d['spray']['opens'] ?? '' );
if ( wd_opens_wait( $wd_open ) ) :
	?>
<section class="w-sec" id="spray">
	<div class="wrap spray spray--soon">
		<div class="w-head">
			<p class="w-eyebrow"><?php esc_html_e( 'Spray the couple', 'wwmd' ); ?></p>
			<h2 class="w-title"><?php esc_html_e( 'Make it rain, from anywhere', 'wwmd' ); ?></h2>
		</div>
		<?php get_template_part( 'template-parts/wedding/opens', null, array( 'kind' => 'spray', 'ymd' => $wd_open ) ); ?>
	</div>
</section>
	<?php
	return;
endif;
?>
<section class="w-sec" id="spray">
	<div class="wrap spray">
		<div class="spray__stage" data-spray-stage>
			<div class="spray__pic">
				<?php if ( $wd_pic ) : ?>
					<img src="<?php echo esc_url( wd_img_url( $wd_pic, 'wd-card' ) ); ?>" alt="<?php echo esc_attr( wd_names( $wd_d ) ); ?>">
				<?php else : ?>
					<?php echo wd_cover_art( $wd_d, (string) $wd_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php endif; ?>
				<div class="spray__rain" data-spray-rain aria-hidden="true"></div>
			</div>
			<div class="spray__counter"><span><?php esc_html_e( 'Your spray', 'wwmd' ); ?></span><b data-spray-total><?php echo esc_html( wd_money( 0, $wd_cur ) ); ?></b><small data-spray-count></small></div>
		</div>
		<div class="spray__panel">
			<p class="w-eyebrow"><?php esc_html_e( 'Spray the couple', 'wwmd' ); ?></p>
			<h2 class="w-title"><?php esc_html_e( 'Make it rain, from anywhere', 'wwmd' ); ?></h2>
			<p><?php esc_html_e( 'Pick a note & tap the button to spray. Tap as many times as you like, then pay once.', 'wwmd' ); ?></p>
			<div class="notes" role="radiogroup" aria-label="<?php esc_attr_e( 'Note', 'wwmd' ); ?>">
				<?php foreach ( $wd_set as $wd_i => $wd_n ) : ?>
					<label class="note-pick"><input type="radio" name="note" value="<?php echo (int) $wd_n; ?>" <?php checked( 1, $wd_i ); ?> data-note><span><?php echo esc_html( wd_money( $wd_n, $wd_cur ) ); ?></span></label>
				<?php endforeach; ?>
			</div>
			<div class="btn-row">
				<button class="btn w-btn w-btn--accent btn--lg spray__tap" type="button" data-spray-tap><?php echo wd_icon( 'cash', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Tap to spray', 'wwmd' ); ?></button>
				<button class="btn w-btn btn--lg" type="button" data-spray-pay disabled><?php esc_html_e( 'Send my spray', 'wwmd' ); ?></button>
				<button class="linkish" type="button" data-spray-reset hidden><?php esc_html_e( 'Start again', 'wwmd' ); ?></button>
			</div>
			<?php if ( ! empty( $wd_d['spray']['show_total'] ) && $wd_tot['spray'] ) : ?>
				<p class="spray__sum"><?php echo wd_icon( 'fire', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( sprintf( /* translators: 1: amount 2: count */ __( '%1$s sprayed by %2$s guests so far', 'wwmd' ), wd_money( $wd_tot['spray'], $wd_cur ), number_format_i18n( $wd_tot['spray_n'] ) ) ); ?></p>
			<?php endif; ?>
			<ul class="feed" data-spray-feed>
				<?php foreach ( (array) $wd_feed as $wd_f ) : ?>
					<li><span class="feed__av"><?php echo wd_avatar( $wd_f->name ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><span><b><?php echo esc_html( $wd_f->name ); ?></b> <?php echo $wd_f->hide_amount ? esc_html__( 'sprayed the couple', 'wwmd' ) : esc_html( sprintf( /* translators: %s amount */ __( 'sprayed %s', 'wwmd' ), wd_money( $wd_f->amount, $wd_f->currency ) ) ); ?><?php if ( $wd_f->message ) : ?><em>“<?php echo esc_html( $wd_f->message ); ?>”</em><?php endif; ?></span><time><?php echo esc_html( wd_ago( $wd_f->created ) ); ?></time></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>
