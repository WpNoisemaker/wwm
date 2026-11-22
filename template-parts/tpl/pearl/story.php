<?php
/**
 * Pearl: the love story on one fine rail down the left margin, strung with pearls. Chapters hang off
 * it in order; nothing alternates, so the spine never moves.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_d = $args['d'];
?>
<section class="w-sec pe-sec pe-story" id="story">
	<div class="wrap">
		<div class="w-head pe-head">
			<p class="w-eyebrow"><?php esc_html_e( 'Our story', 'wwmd' ); ?></p>
			<h2 class="w-title"><?php esc_html_e( 'How we found each other', 'wwmd' ); ?></h2>
		</div>
		<ol class="pe-line">
			<?php foreach ( $wd_d['story'] as $wd_s ) : ?>
				<li class="pe-chapter<?php echo $wd_s['image'] ? '' : ' is-text'; ?>">
					<?php if ( $wd_s['image'] ) : ?>
						<figure class="pe-chapter__pic"><img src="<?php echo esc_url( wd_img_url( $wd_s['image'], 'wd-square' ) ); ?>" alt="" loading="lazy"></figure>
					<?php endif; ?>
					<div class="pe-chapter__text">
						<?php if ( $wd_s['when'] ) : ?><p class="pe-chapter__when"><?php echo esc_html( $wd_s['when'] ); ?></p><?php endif; ?>
						<h3><?php echo esc_html( $wd_s['title'] ); ?></h3>
						<p><?php echo nl2br( esc_html( $wd_s['text'] ) ); ?></p>
					</div>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
