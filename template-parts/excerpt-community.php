<?php
/**
 * "From the community": the latest guest photos & love notes from public wedding pages (1.5).
 * Shows nothing until there are some. Args: title (optional).
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_cm = wd_community( 3, 6 );
if ( ! $wd_cm['notes'] && ! $wd_cm['photos'] ) {
	return;
}
?>
<section class="section section--community">
	<div class="wrap">
		<div class="section-head">
			<div>
				<p class="eyebrow"><?php echo wd_icon( 'heart', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'From the community', 'wwmd' ); ?></p>
				<h2><?php echo esc_html( $args['title'] ?? __( 'Love notes & photos from guests', 'wwmd' ) ); ?></h2>
				<p class="section-head__sub"><?php esc_html_e( 'Shared by guests on couples’ wedding pages, at home & abroad.', 'wwmd' ); ?></p>
			</div>
			<a class="link-arrow" href="<?php echo esc_url( wd_url( 'explore' ) ); ?>"><?php esc_html_e( 'Explore weddings', 'wwmd' ); ?><?php echo wd_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		</div>
		<div class="cm<?php echo $wd_cm['photos'] && $wd_cm['notes'] ? '' : ' cm--one'; ?>">
			<?php if ( $wd_cm['photos'] ) : ?>
				<ul class="cm__photos">
					<?php
					foreach ( $wd_cm['photos'] as $wd_ph ) :
						$wd_d = wd_get( (int) $wd_ph->wedding_id );
						?>
						<li><a href="<?php echo esc_url( get_permalink( (int) $wd_ph->wedding_id ) . '#gallery' ); ?>"><img src="<?php echo esc_url( (string) wp_get_attachment_image_url( (int) $wd_ph->attachment_id, 'wd-square' ) ); ?>" alt="<?php echo esc_attr( $wd_ph->caption ? $wd_ph->caption : sprintf( /* translators: %s couple */ __( 'A guest photo from %s’s wedding', 'wwmd' ), wd_names( $wd_d ) ) ); ?>" loading="lazy"><span class="cm__by"><?php echo esc_html( wd_names( $wd_d ) ); ?></span></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<?php if ( $wd_cm['notes'] ) : ?>
				<ul class="cm__notes">
					<?php
					foreach ( $wd_cm['notes'] as $wd_n ) :
						$wd_d = wd_get( (int) $wd_n->wedding_id );
						?>
						<li>
							<blockquote><?php echo esc_html( $wd_n->message ); ?></blockquote>
							<p class="cm__who"><?php echo wd_avatar( $wd_n->name ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><b><?php echo esc_html( $wd_n->name ); ?></b><a href="<?php echo esc_url( get_permalink( (int) $wd_n->wedding_id ) . '#wall' ); ?>"><?php echo esc_html( sprintf( /* translators: %s couple */ __( 'to %s', 'wwmd' ), wd_names( $wd_d ) ) ); ?></a></span></p>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
</section>
