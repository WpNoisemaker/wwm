<?php
/**
 * Guest photo gallery: upload & like. Most-liked photos first.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
global $wpdb;
$wd_id     = $args['id'];
$wd_photos = $wpdb->get_results( $wpdb->prepare( 'SELECT id, attachment_id, name, caption, likes FROM ' . wd_t( 'photos' ) . " WHERE wedding_id = %d AND status = 'approved' ORDER BY likes DESC, id DESC LIMIT 24", $wd_id ) ); // phpcs:ignore WordPress.DB
$wd_total  = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . wd_t( 'photos' ) . " WHERE wedding_id = %d AND status = 'approved'", $wd_id ) ); // phpcs:ignore WordPress.DB
?>
<section class="w-sec w-sec--tint" id="gallery">
	<div class="wrap">
		<div class="w-head w-head--row">
			<div>
				<p class="w-eyebrow"><?php echo wd_icon( 'camera', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( sprintf( /* translators: %d photos */ _n( '%d guest photo', '%d guest photos', $wd_total, 'wwmd' ), $wd_total ) ); ?></p>
				<h2 class="w-title"><?php esc_html_e( 'Through our guests’ eyes', 'wwmd' ); ?></h2>
			</div>
			<button class="btn w-btn" type="button" data-open="#photo-form"><?php echo wd_icon( 'upload', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Add your photos', 'wwmd' ); ?></button>
		</div>
		<form class="form card-form upload-form" id="photo-form" hidden data-photo-form enctype="multipart/form-data">
			<input type="hidden" name="wedding" value="<?php echo (int) $wd_id; ?>">
			<label class="drop"><input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required><span><?php echo wd_icon( 'camera', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Choose a photo (up to 8 MB)', 'wwmd' ); ?></span></label>
			<div class="field-row">
				<label class="field"><span><?php esc_html_e( 'Your name', 'wwmd' ); ?></span><input name="name" maxlength="60"></label>
				<label class="field"><span><?php esc_html_e( 'Caption (optional)', 'wwmd' ); ?></span><input name="caption" maxlength="200"></label>
			</div>
			<button class="btn w-btn" type="submit"><?php esc_html_e( 'Upload', 'wwmd' ); ?></button>
			<p class="form__msg" data-msg role="status"></p>
		</form>
		<?php if ( $wd_photos ) : ?>
			<div class="masonry" data-photos data-c="<?php echo (int) wd_cols( count( $wd_photos ), 4 ); ?>">
				<?php
				foreach ( $wd_photos as $wd_p ) :
					$wd_src = wp_get_attachment_image_url( (int) $wd_p->attachment_id, 'wd-card' );
					$wd_big = wp_get_attachment_image_url( (int) $wd_p->attachment_id, 'large' );
					if ( ! $wd_src ) {
						continue;
					}
					?>
					<figure class="photo">
						<a href="<?php echo esc_url( $wd_big ); ?>" data-lightbox><img src="<?php echo esc_url( $wd_src ); ?>" alt="<?php echo esc_attr( $wd_p->caption ? $wd_p->caption : sprintf( /* translators: %s name */ __( 'Photo by %s', 'wwmd' ), $wd_p->name ) ); ?>" loading="lazy"></a>
						<figcaption><span><?php echo esc_html( $wd_p->caption ? $wd_p->caption : '' ); ?><small><?php echo esc_html( $wd_p->name ); ?></small></span><button type="button" class="like" data-like="photo" data-id="<?php echo (int) $wd_p->id; ?>" aria-pressed="false" aria-label="<?php esc_attr_e( 'Like this photo', 'wwmd' ); ?>"><?php echo wd_icon( 'heart', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><b><?php echo (int) $wd_p->likes; ?></b></button></figcaption>
					</figure>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<p class="empty-note"><?php esc_html_e( 'No photos yet. Be the first to share one!', 'wwmd' ); ?></p>
		<?php endif; ?>
	</div>
	<dialog class="lightbox" data-lightbox-dialog><img alt=""><button type="button" class="lightbox__x" data-close aria-label="<?php esc_attr_e( 'Close', 'wwmd' ); ?>"><?php echo wd_icon( 'close', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button></dialog>
</section>
