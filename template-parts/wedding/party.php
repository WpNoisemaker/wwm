<?php
/**
 * Bridesmaids & groomsmen line-up. Each side is centred, so a short row never leaves a gap on one side.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_id     = $args['id'];
$wd_d      = $args['d'];
$wd_sample = (bool) get_post_meta( $wd_id, '_wd_sample', true );
$wd_rel    = wd_rel_statuses();
$wd_sides  = array(
	'bride' => array(),
	'groom' => array(),
);
foreach ( $wd_d['party'] as $wd_p ) {
	$wd_sides[ 'groom' === $wd_p['side'] ? 'groom' : 'bride' ][] = $wd_p;
}
?>
<section class="w-sec w-sec--pattern" id="party">
	<div class="wrap">
		<div class="w-head w-head--light">
			<p class="w-eyebrow"><?php esc_html_e( 'The squad', 'wwmd' ); ?></p>
			<h2 class="w-title"><?php esc_html_e( 'Meet the bridesmaids & groomsmen', 'wwmd' ); ?></h2>
		</div>
		<?php foreach ( $wd_sides as $wd_side => $wd_people ) : ?>
			<?php if ( ! $wd_people ) { continue; } ?>
			<h3 class="squad__side"><?php echo esc_html( sprintf( /* translators: %s name */ __( 'Team %s', 'wwmd' ), wd_side_name( $wd_d, $wd_side ) ) ); ?></h3>
			<div class="squad">
				<?php foreach ( $wd_people as $wd_p ) : ?>
					<article class="squad__card">
						<span class="squad__pic">
							<?php if ( ! empty( $wd_p['image'] ) ) : ?>
								<img src="<?php echo esc_url( wd_img_url( $wd_p['image'], 'wd-square' ) ); ?>" alt="" loading="lazy">
							<?php else : ?>
								<?php echo wd_avatar( $wd_p['name'], 'bride' === $wd_side ? 'f' : 'm', '', wd_can( $wd_id, 'design' ) ? $wd_d['design']['primary'] : '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<?php endif; ?>
						</span>
						<span class="squad__role"><?php echo esc_html( $wd_p['role'] ); ?></span>
						<h4><?php echo esc_html( $wd_p['name'] ); ?></h4>
						<?php if ( ! empty( $wd_p['nick'] ) ) : ?><p class="squad__nick">“<?php echo esc_html( $wd_p['nick'] ); ?>”</p><?php endif; ?>
						<?php if ( ! empty( $wd_p['career'] ) ) : ?><p class="squad__career"><?php echo wd_icon( 'briefcase', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $wd_p['career'] ); ?></p><?php endif; ?>
						<?php if ( ! empty( $wd_p['status'] ) && isset( $wd_rel[ $wd_p['status'] ] ) ) : ?><p class="squad__status squad__status--<?php echo esc_attr( $wd_p['status'] ); ?>"><?php echo wd_icon( 'heart', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $wd_rel[ $wd_p['status'] ] ); ?></p><?php endif; ?>
						<?php if ( $wd_p['line'] ) : ?><p class="squad__line">“<?php echo esc_html( $wd_p['line'] ); ?>”</p><?php endif; ?>
						<?php $wd_likes = wd_squad_likes( $wd_p ); ?>
						<?php if ( $wd_likes ) : ?><ul class="squad__likes" aria-label="<?php esc_attr_e( 'Loves', 'wwmd' ); ?>"><?php foreach ( $wd_likes as $wd_l ) : ?><li><?php echo esc_html( $wd_l ); ?></li><?php endforeach; ?></ul><?php endif; ?>
						<?php $wd_links = wd_squad_links( $wd_p ); ?>
						<?php if ( $wd_links ) : ?>
							<ul class="squad__social">
								<?php foreach ( $wd_links as $wd_s ) : ?>
									<li>
										<?php if ( $wd_sample || ! empty( $wd_p['plain'] ) ) : ?>
											<span title="<?php echo esc_attr( $wd_s[0] . ': @' . $wd_s[3] ); ?>"><?php echo wd_icon( $wd_s[2], 17 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span class="screen-reader-text"><?php echo esc_html( $wd_s[0] . ': @' . $wd_s[3] ); ?></span></span>
										<?php else : ?>
											<a href="<?php echo esc_url( $wd_s[1] ); ?>" target="_blank" rel="noopener nofollow ugc" title="<?php echo esc_attr( $wd_s[0] . ': @' . $wd_s[3] ); ?>"><?php echo wd_icon( $wd_s[2], 17 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span class="screen-reader-text"><?php echo esc_html( $wd_s[0] . ': @' . $wd_s[3] ); ?></span></a>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
	</div>
</section>
