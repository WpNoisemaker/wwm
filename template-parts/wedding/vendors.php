<?php
/**
 * The couple's vendors, with public reviews from guests.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_id = $args['id'];
$wd_d  = $args['d'];
?>
<section class="w-sec w-sec--tint" id="vendors">
	<div class="wrap">
		<div class="w-head w-head--row">
			<div>
				<p class="w-eyebrow"><?php echo wd_icon( 'star', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'The vendors', 'wwmd' ); ?></p>
				<h2 class="w-title"><?php esc_html_e( 'The people who made it happen', 'wwmd' ); ?></h2>
			</div>
			<p class="w-head__aside"><?php esc_html_e( 'Were you there? Rate the vendors. Your review shows on their profile for every couple to see.', 'wwmd' ); ?></p>
		</div>
		<div class="vend-list">
			<?php
			foreach ( $wd_d['vendors'] as $wd_v ) :
				$wd_vid = (int) $wd_v['id'];
				if ( 'publish' !== get_post_status( $wd_vid ) ) {
					continue;
				}
				$wd_meta = wd_vendor( $wd_vid );
				$wd_r    = (float) get_post_meta( $wd_vid, '_wd_rating', true );
				$wd_n    = (int) get_post_meta( $wd_vid, '_wd_reviews', true );
				?>
				<article class="vend">
					<span class="vend__pic tone-<?php echo esc_attr( $wd_meta['colour'] ); ?>"><?php echo has_post_thumbnail( $wd_vid ) ? get_the_post_thumbnail( $wd_vid, 'wd-square' ) : wd_avatar( get_the_title( $wd_vid ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<div class="vend__body">
						<span class="vend__type"><?php echo esc_html( $wd_v['role'] ? $wd_v['role'] : wd_vendor_type( $wd_vid ) ); ?></span>
						<h3><a href="<?php echo esc_url( get_permalink( $wd_vid ) ); ?>"><?php echo esc_html( get_the_title( $wd_vid ) ); ?></a><?php if ( $wd_meta['verified'] ) : ?><span class="verified" title="<?php esc_attr_e( 'Verified vendor', 'wwmd' ); ?>"><?php echo wd_icon( 'shield', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><?php endif; ?></h3>
						<p class="vend__rating" data-vend-rating><?php echo wd_stars( $wd_r ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <b><?php echo esc_html( $wd_r ? number_format_i18n( $wd_r, 1 ) : '–' ); ?></b> <span><?php echo esc_html( sprintf( /* translators: %d reviews */ _n( '%d review', '%d reviews', $wd_n, 'wwmd' ), $wd_n ) ); ?></span></p>
						<?php if ( $wd_meta['ig'] ) : ?><a class="vend__ig" href="<?php echo esc_url( 'https://instagram.com/' . $wd_meta['ig'] ); ?>" target="_blank" rel="noopener">@<?php echo esc_html( $wd_meta['ig'] ); ?></a><?php endif; ?>
					</div>
					<button class="btn w-btn w-btn--ghost btn--sm" type="button" data-toggle-next><?php esc_html_e( 'Review', 'wwmd' ); ?></button>
					<form class="form review-form" hidden data-review>
						<input type="hidden" name="wedding" value="<?php echo (int) $wd_id; ?>">
						<input type="hidden" name="vendor" value="<?php echo (int) $wd_vid; ?>">
						<fieldset class="rate"><legend><?php esc_html_e( 'Your rating', 'wwmd' ); ?></legend>
							<?php for ( $wd_s = 5; $wd_s >= 1; $wd_s-- ) : ?>
								<input type="radio" id="r<?php echo (int) $wd_vid . '-' . (int) $wd_s; ?>" name="rating" value="<?php echo (int) $wd_s; ?>" <?php checked( 5, $wd_s ); ?>><label for="r<?php echo (int) $wd_vid . '-' . (int) $wd_s; ?>" title="<?php echo (int) $wd_s; ?>">★</label>
							<?php endfor; ?>
						</fieldset>
						<label class="field"><span><?php esc_html_e( 'Your name', 'wwmd' ); ?></span><input name="name" maxlength="60" required></label>
						<label class="field"><span><?php esc_html_e( 'What was it like?', 'wwmd' ); ?></span><textarea name="text" rows="3" maxlength="800" required></textarea></label>
						<button class="btn w-btn btn--sm" type="submit"><?php esc_html_e( 'Post review', 'wwmd' ); ?></button>
						<p class="form__msg" data-msg role="status"></p>
					</form>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
