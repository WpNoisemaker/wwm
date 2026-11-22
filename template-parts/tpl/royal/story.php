<?php
/**
 * Royal: the love story as a chronicle. A gold line down the middle capped at both ends with a
 * foil lozenge, a medallion for each chapter & the chapters set left & right of it.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_d = $args['d'];
?>
<section class="w-sec ro-sec ro-chron" id="story">
	<div class="wrap">
		<div class="ro-head">
			<p class="ro-kicker"><?php esc_html_e( 'Our story', 'wwmd' ); ?></p>
			<h2 class="ro-title"><?php esc_html_e( 'The chronicle of us', 'wwmd' ); ?></h2>
		</div>
		<div class="ro-chron__roll">
		<span class="ro-chron__cap ro-chron__cap--top" aria-hidden="true"></span>
		<ol class="ro-chron__list">
			<?php foreach ( $wd_d['story'] as $wd_i => $wd_s ) : ?>
				<li class="ro-chron__item">
					<span class="ro-chron__medal" aria-hidden="true"><?php echo esc_html( wd_roman( $wd_i + 1 ) ); ?></span>
					<article class="ro-chron__card">
						<?php if ( $wd_s['image'] ) : ?><figure class="ro-chron__pic"><img src="<?php echo esc_url( wd_img_url( $wd_s['image'], 'wd-card' ) ); ?>" alt="" loading="lazy"></figure><?php endif; ?>
						<p class="ro-kicker"><?php echo esc_html( trim( sprintf( /* translators: %s chapter number */ __( 'Chapter %s', 'wwmd' ), wd_roman( $wd_i + 1 ) ) . ( $wd_s['when'] ? ' · ' . $wd_s['when'] : '' ) ) ); ?></p>
						<h3><?php echo esc_html( $wd_s['title'] ); ?></h3>
						<p><?php echo nl2br( esc_html( $wd_s['text'] ) ); ?></p>
					</article>
				</li>
			<?php endforeach; ?>
		</ol>
		<span class="ro-chron__cap ro-chron__cap--end" aria-hidden="true"></span>
		</div>
	</div>
</section>
