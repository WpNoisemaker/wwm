<?php
/**
 * Love story timeline.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_d = $args['d'];
?>
<section class="w-sec" id="story">
	<div class="wrap">
		<div class="w-head">
			<p class="w-eyebrow"><?php esc_html_e( 'Our story', 'wwmd' ); ?></p>
			<h2 class="w-title"><?php esc_html_e( 'How we got here', 'wwmd' ); ?></h2>
		</div>
		<ol class="story">
			<?php foreach ( $wd_d['story'] as $wd_i => $wd_s ) : ?>
				<li class="story__item">
					<span class="story__when"><?php echo esc_html( $wd_s['when'] ); ?></span>
					<div class="story__card">
						<?php if ( $wd_s['image'] ) : ?><img src="<?php echo esc_url( wd_img_url( $wd_s['image'], 'wd-card' ) ); ?>" alt="" loading="lazy"><?php endif; ?>
						<h3><?php echo esc_html( $wd_s['title'] ); ?></h3>
						<p><?php echo nl2br( esc_html( $wd_s['text'] ) ); ?></p>
					</div>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
