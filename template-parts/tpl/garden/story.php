<?php
/**
 * Garden: the love story as pressed memories. Taped prints, the year in script & a sprig where a
 * chapter has no photo. Swipes sideways on phones.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_d = $args['d'];
?>
<section class="w-sec ga-sec ga-story" id="story">
	<div class="wrap">
		<div class="w-head ga-head">
			<p class="w-eyebrow"><?php esc_html_e( 'Our story', 'wwmd' ); ?></p>
			<h2 class="w-title"><?php esc_html_e( 'Pressed memories', 'wwmd' ); ?></h2>
		</div>
		<ol class="ga-polas">
			<?php foreach ( $wd_d['story'] as $wd_s ) : ?>
				<li class="ga-pola">
					<figure class="ga-pola__pic<?php echo $wd_s['image'] ? '' : ' is-art'; ?>">
						<?php if ( $wd_s['image'] ) : ?>
							<img src="<?php echo esc_url( wd_img_url( $wd_s['image'], 'wd-square' ) ); ?>" alt="" loading="lazy">
						<?php else : ?>
							<?php echo wd_ga_sprig(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php endif; ?>
					</figure>
					<?php if ( $wd_s['when'] ) : ?><p class="ga-pola__when"><?php echo esc_html( $wd_s['when'] ); ?></p><?php endif; ?>
					<h3><?php echo esc_html( $wd_s['title'] ); ?></h3>
					<p class="ga-pola__text"><?php echo nl2br( esc_html( $wd_s['text'] ) ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
