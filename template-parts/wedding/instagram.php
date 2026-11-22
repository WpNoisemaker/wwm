<?php
/**
 * Instagram: latest posts from a connected account, or the posts the couple pasted.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_id    = $args['id'];
$wd_d     = $args['d'];
$wd_media = (array) get_post_meta( $wd_id, '_wd_ig_media', true );
$wd_media = array_filter( $wd_media );
$wd_h     = $wd_d['ig']['handle'];
// Sample pages use made-up handles, so they never link out to Instagram.
$wd_link  = $wd_h && ! get_post_meta( $wd_id, '_wd_sample', true );
?>
<section class="w-sec" id="instagram">
	<div class="wrap">
		<div class="w-head w-head--row">
			<div>
				<p class="w-eyebrow"><?php echo wd_icon( 'insta', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'On Instagram', 'wwmd' ); ?></p>
				<h2 class="w-title"><?php echo $wd_h ? '@' . esc_html( $wd_h ) : esc_html__( 'From our Instagram', 'wwmd' ); ?></h2>
			</div>
			<?php if ( $wd_link ) : ?><a class="btn w-btn" href="<?php echo esc_url( 'https://instagram.com/' . $wd_h ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Follow', 'wwmd' ); ?></a><?php endif; ?>
		</div>
		<?php if ( $wd_media ) : ?>
			<div class="ig-grid">
				<?php foreach ( array_slice( $wd_media, 0, 9 ) as $wd_m ) : ?>
					<?php
					$wd_src = is_numeric( $wd_m['img'] ) ? wd_img_url( (int) $wd_m['img'], 'wd-square' ) : $wd_m['img'];
					$wd_tag = $wd_m['url'] ? 'a' : 'div';
					?>
					<<?php echo esc_attr( $wd_tag ); ?> class="ig-tile"<?php echo $wd_m['url'] ? ' href="' . esc_url( $wd_m['url'] ) . '" target="_blank" rel="noopener"' : ''; ?>><img src="<?php echo esc_url( $wd_src ); ?>" alt="<?php echo esc_attr( $wd_m['cap'] ); ?>" loading="lazy"><span><?php echo wd_icon( 'heart', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( wd_short( $wd_m['eng'] ) ); ?></span></<?php echo esc_attr( $wd_tag ); ?>>
				<?php endforeach; ?>
			</div>
		<?php elseif ( $wd_d['ig']['posts'] ) : ?>
			<div class="ig-embeds">
				<?php foreach ( $wd_d['ig']['posts'] as $wd_u ) : ?>
					<blockquote class="instagram-media" data-instgrm-permalink="<?php echo esc_url( $wd_u ); ?>" data-instgrm-version="14"><a href="<?php echo esc_url( $wd_u ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View this post on Instagram', 'wwmd' ); ?></a></blockquote>
				<?php endforeach; ?>
			</div>
			<script async src="https://www.instagram.com/embed.js"></script><?php // phpcs:ignore WordPress.WP.EnqueuedResources ?>
		<?php elseif ( $wd_link ) : ?>
			<a class="ig-follow" href="<?php echo esc_url( 'https://instagram.com/' . $wd_h ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'insta', 28 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'See our photos & reels on Instagram', 'wwmd' ); ?></span></a>
		<?php endif; ?>
	</div>
</section>
