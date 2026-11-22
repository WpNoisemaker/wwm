<?php
/**
 * Ivory & Gold: the top of the page as three ivory cards. The invitation (names, city & the days),
 * the countdown & "Kindly respond".
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_id   = $args['id'];
$wd_d    = $args['d'];
$wd_on   = $args['on'];
$wd_url  = get_permalink( $wd_id );
$wd_rank = wd_rank( $wd_id, 'week' );
$wd_m    = (array) get_post_meta( $wd_id, '_wd_m', true );
$wd_prog = wd_programs( $wd_d );
$wd_city = $wd_d['city'];

// The main days are the programs from the wedding date on; earlier ones are listed as "preceded by".
$wd_main = array();
$wd_pre  = array();
foreach ( $wd_prog as $wd_e ) {
	if ( $wd_e['date'] && $wd_d['date'] && $wd_e['date'] < $wd_d['date'] ) {
		$wd_pre[] = $wd_e;
	} else {
		$wd_main[] = $wd_e;
	}
}
if ( ! $wd_main && $wd_d['date'] ) {
	$wd_main[] = array(
		'title'   => __( 'Our wedding', 'wwmd' ),
		'date'    => $wd_d['date'],
		'time'    => $wd_d['time'],
		'venue'   => $wd_d['venue'],
		'address' => $wd_d['city'],
		'private' => 0,
		'when'    => $wd_d['date'] . ' ' . ( $wd_d['time'] ? $wd_d['time'] : '12:00' ),
	);
}
$wd_pre_city = '';
if ( $wd_pre ) {
	$wd_cities   = array_unique( array_map( static function ( $e ) use ( $wd_d ) { return wd_prog_city( $e, $wd_d ); }, $wd_pre ) );
	$wd_pre_city = 1 === count( $wd_cities ) && 0 !== strcasecmp( (string) reset( $wd_cities ), (string) $wd_city ) ? reset( $wd_cities ) : '';
}

// Count down to the next of the main days (or the next program of all, once those are under way).
$wd_now  = wp_date( 'Y-m-d H:i' );
$wd_next = null;
foreach ( array_merge( $wd_main, $wd_pre ) as $wd_e ) {
	if ( $wd_e['when'] && $wd_e['when'] >= $wd_now ) {
		$wd_next = $wd_e;
		break;
	}
}
$wd_first = $wd_main ? $wd_main[0] : null;
$wd_badge = wd_vbadge( $wd_id, 24 );
$wd_gift  = ( wd_verified( $wd_id ) && ! empty( $wd_d['gift']['by'] ) ) ? $wd_d['gift'] : null;
$wd_req   = $wd_d['invite']['message'] ? $wd_d['invite']['message'] : __( 'request the honour of your presence at the wedding celebrations of', 'wwmd' );
?>
<section class="iv-hero" id="top">
	<div class="iv-stack">
		<article class="iv-card iv-invite">
			<?php if ( $wd_d['invite']['hosts'] ) : ?><p class="iv-kicker"><?php echo esc_html( $wd_d['invite']['hosts'] ); ?></p><?php endif; ?>
			<p class="iv-request"><?php echo nl2br( esc_html( $wd_req ) ); ?></p>
			<h1 class="iv-names">
				<span class="iv-foil"><?php echo esc_html( $wd_d['p1'] ); ?></span>
				<span class="iv-amp" aria-label="<?php esc_attr_e( 'and', 'wwmd' ); ?>">&amp;</span>
				<span class="iv-foil"><?php echo esc_html( $wd_d['p2'] ); ?></span><?php echo $wd_badge; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</h1>
			<span class="iv-rule" aria-hidden="true"><i class="iv-gem"></i></span>
			<?php if ( $wd_city ) : ?><p class="iv-city"><?php echo esc_html( $wd_city ); ?></p><?php endif; ?>
			<?php if ( $wd_d['tagline'] ) : ?><p class="iv-tagline"><?php echo esc_html( $wd_d['tagline'] ); ?></p><?php endif; ?>
			<?php if ( $wd_main ) : ?>
				<ul class="iv-days">
					<?php foreach ( $wd_main as $wd_e ) : ?>
						<?php $wd_cal = wd_prog_gcal( $wd_e, $wd_d ); ?>
						<li>
							<a href="<?php echo esc_url( $wd_cal ); ?>" target="_blank" rel="noopener" title="<?php echo esc_attr( sprintf( /* translators: %s program */ __( 'Save %s to your calendar', 'wwmd' ), $wd_e['title'] ) ); ?>">
								<span class="iv-days__wd"><?php echo esc_html( wd_date( $wd_e['date'], 'l' ) ); ?></span>
								<b><?php echo esc_html( wd_date( $wd_e['date'], 'j' ) ); ?></b>
								<span class="iv-days__t"><?php echo esc_html( $wd_e['title'] ); ?></span>
								<?php if ( $wd_e['venue'] && empty( $wd_e['private'] ) ) : ?><small><?php echo esc_html( $wd_e['venue'] ); ?></small><?php endif; ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php if ( $wd_first && $wd_first['date'] ) : ?>
					<p class="iv-month"><?php echo esc_html( wd_date( $wd_first['date'], 'F' ) . ', ' . wd_year_words( wd_date( $wd_first['date'], 'Y' ) ) ); ?></p>
				<?php endif; ?>
			<?php endif; ?>
			<?php if ( $wd_pre ) : ?>
				<div class="iv-before">
					<p class="iv-kicker"><?php echo esc_html( $wd_pre_city ? sprintf( /* translators: %s city */ __( 'Preceded in %s by', 'wwmd' ), $wd_pre_city ) : __( 'Preceded by', 'wwmd' ) ); ?></p>
					<ul>
						<?php foreach ( $wd_pre as $wd_e ) : ?>
							<li><a href="<?php echo esc_url( wd_prog_gcal( $wd_e, $wd_d ) ); ?>" target="_blank" rel="noopener"><b><?php echo esc_html( $wd_e['title'] ); ?></b><small><?php echo esc_html( wd_date( $wd_e['date'], 'l j F' ) ); ?></small></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
			<?php if ( $wd_prog ) : ?><p class="iv-hint"><?php esc_html_e( 'Tap a celebration to save the date', 'wwmd' ); ?></p><?php endif; ?>
			<?php if ( $wd_gift ) : ?>
				<p class="iv-gift"><?php echo wd_icon( 'gift', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( sprintf( /* translators: %s gifter */ __( 'This page is a gift from %s', 'wwmd' ), $wd_gift['by'] ) ); ?></span></p>
			<?php endif; ?>
		</article>

		<article class="iv-card iv-count">
			<?php if ( $wd_next ) : ?>
				<p class="iv-kicker"><?php echo esc_html( $wd_city && ! in_array( $wd_next, $wd_pre, true ) ? sprintf( /* translators: %s city */ __( 'Counting down to %s', 'wwmd' ), $wd_city ) : __( 'Counting down', 'wwmd' ) ); ?></p>
				<div class="countdown iv-countdown" data-countdown="<?php echo esc_attr( str_replace( ' ', 'T', $wd_next['when'] ) . ':00' ); ?>" aria-label="<?php esc_attr_e( 'Countdown', 'wwmd' ); ?>">
					<span><b data-cd="d">0</b><?php esc_html_e( 'Days', 'wwmd' ); ?></span>
					<span><b data-cd="h">00</b><?php esc_html_e( 'Hours', 'wwmd' ); ?></span>
					<span><b data-cd="m">00</b><?php esc_html_e( 'Minutes', 'wwmd' ); ?></span>
					<span><b data-cd="s">00</b><?php esc_html_e( 'Seconds', 'wwmd' ); ?></span>
				</div>
				<p class="iv-until"><?php echo esc_html( sprintf( /* translators: 1: program 2: date */ __( 'until the %1$s · %2$s', 'wwmd' ), $wd_next['title'], wd_date( $wd_next['date'], 'l j F' ) ) ); ?></p>
			<?php else : ?>
				<p class="iv-kicker"><?php esc_html_e( 'Thank you', 'wwmd' ); ?></p>
				<p class="iv-until"><?php esc_html_e( 'Thank you for celebrating with us. The photos & love notes stay right here.', 'wwmd' ); ?></p>
			<?php endif; ?>
			<div class="iv-btns">
				<?php if ( $wd_prog ) : ?>
					<a class="btn iv-btn iv-btn--ghost" href="<?php echo esc_attr( wd_prog_ics( $wd_prog, $wd_d ) ); ?>" download="<?php echo esc_attr( sanitize_title( wd_names( $wd_d ) ) ); ?>.ics"><?php echo wd_icon( 'calendar', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Add all dates to calendar', 'wwmd' ); ?></a>
				<?php endif; ?>
				<button class="btn iv-btn iv-btn--ghost" type="button" data-share data-url="<?php echo esc_url( $wd_url ); ?>" data-title="<?php echo esc_attr( wd_names( $wd_d ) ); ?>"><?php echo wd_icon( 'share', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Share', 'wwmd' ); ?></button>
			</div>
		</article>

		<?php if ( isset( $wd_on['rsvp'] ) && wd_days_to( $wd_d['date'] ) >= 0 ) : ?>
			<article class="iv-card iv-respond">
				<p class="iv-kicker"><?php esc_html_e( 'Kindly respond', 'wwmd' ); ?></p>
				<h2 class="iv-h"><?php esc_html_e( 'Will you join us?', 'wwmd' ); ?></h2>
				<p><?php echo esc_html( ! empty( $wd_d['pass']['on'] ) && wd_can( $wd_id, 'entry_pass' ) ? __( 'Reply once & receive your personal entry pass for the celebrations.', 'wwmd' ) : __( 'Reply once & let us know which celebrations you can make.', 'wwmd' ) ); ?></p>
				<div class="iv-respond__btns">
					<a class="btn iv-btn iv-btn--foil" href="#rsvp"><?php esc_html_e( 'Send response', 'wwmd' ); ?></a>
					<?php if ( ! empty( $wd_d['pass']['on'] ) && wd_can( $wd_id, 'entry_pass' ) ) : ?>
						<details class="iv-find">
							<summary class="btn iv-btn iv-btn--ghost"><?php esc_html_e( 'Find my entry pass', 'wwmd' ); ?></summary>
							<form class="iv-find__form" data-iv-find action="<?php echo esc_url( home_url( '/pass/' ) ); ?>">
								<label class="field"><span><?php esc_html_e( 'The pass code from your RSVP', 'wwmd' ); ?></span><input name="code" required autocomplete="off" autocapitalize="characters" spellcheck="false" maxlength="24"></label>
								<button class="btn iv-btn iv-btn--foil" type="submit"><?php esc_html_e( 'Open my pass', 'wwmd' ); ?></button>
							</form>
						</details>
					<?php endif; ?>
				</div>
				<?php if ( isset( $wd_on['quiz'] ) ) : ?><a class="iv-link" href="#quiz"><?php esc_html_e( 'Take the couple quiz', 'wwmd' ); ?></a><?php endif; ?>
			</article>
		<?php endif; ?>

		<ul class="w-buzz iv-buzz">
			<li><?php echo wd_icon( 'coin', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><b><?php echo esc_html( wd_short( wd_coin_total( $wd_id ) ) ); ?></b> <?php esc_html_e( 'Buzz coins', 'wwmd' ); ?></li>
			<?php if ( $wd_rank ) : ?><li><a href="<?php echo esc_url( wd_url( 'rankings' ) ); ?>"><?php echo wd_icon( 'trophy', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><b><?php echo esc_html( sprintf( /* translators: %d rank */ __( 'No. %d', 'wwmd' ), $wd_rank ) ); ?></b> <?php esc_html_e( 'on the Buzz Chart', 'wwmd' ); ?></a></li><?php endif; ?>
			<li><?php echo wd_icon( 'eye', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><b><?php echo esc_html( wd_short( $wd_m['views'] ?? 0 ) ); ?></b> <?php esc_html_e( 'visits', 'wwmd' ); ?></li>
			<li><button type="button" class="linkish" data-story-card><?php echo wd_icon( 'insta', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Make a story card', 'wwmd' ); ?></button></li>
		</ul>
	</div>
	<div class="share-sheet" hidden data-share-sheet>
		<a href="<?php echo esc_url( 'https://wa.me/?text=' . rawurlencode( wd_names( $wd_d ) . ' ' . __( 'are getting married! See their wedding page:', 'wwmd' ) . ' ' . $wd_url ) ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'whatsapp', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>WhatsApp</a>
		<a href="<?php echo esc_url( 'https://twitter.com/intent/tweet?url=' . rawurlencode( $wd_url ) . '&text=' . rawurlencode( wd_names( $wd_d ) . ( $wd_d['hashtag'] ? ' #' . $wd_d['hashtag'] : '' ) ) ); ?>" target="_blank" rel="noopener">X</a>
		<a href="<?php echo esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $wd_url ) ); ?>" target="_blank" rel="noopener">Facebook</a>
		<button type="button" data-copy="<?php echo esc_url( $wd_url ); ?>"><?php echo wd_icon( 'link', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Copy link', 'wwmd' ); ?></button>
	</div>
	<canvas hidden data-story-canvas width="1080" height="1920" data-names="<?php echo esc_attr( wd_names( $wd_d ) ); ?>" data-date="<?php echo esc_attr( wd_date( $wd_d['date'], 'j F Y' ) ); ?>" data-city="<?php echo esc_attr( $wd_d['city'] ); ?>" data-hash="<?php echo esc_attr( $wd_d['hashtag'] ); ?>" data-link="<?php echo esc_attr( preg_replace( '#^https?://#', '', untrailingslashit( $wd_url ) ) ); ?>" data-rank="<?php echo (int) $wd_rank; ?>" data-cover="<?php echo esc_url( $wd_d['cover'] ? wd_img_url( $wd_d['cover'], 'wd-cover' ) : '' ); ?>"></canvas>
</section>
