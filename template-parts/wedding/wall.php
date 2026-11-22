<?php
/**
 * Wall of love: goodwill messages with likes.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
global $wpdb;
$wd_id   = $args['id'];
$wd_rows = $wpdb->get_results( $wpdb->prepare( 'SELECT id, name, message, likes, created FROM ' . wd_t( 'wall' ) . " WHERE wedding_id = %d AND status = 'approved' ORDER BY id DESC LIMIT 60", $wd_id ) ); // phpcs:ignore WordPress.DB
$wd_n    = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . wd_t( 'wall' ) . " WHERE wedding_id = %d AND status = 'approved'", $wd_id ) ); // phpcs:ignore WordPress.DB
$wd_tone = array( 'butter', 'lavender', 'sky', 'mint', 'coral', 'cream' );
?>
<section class="w-sec" id="wall">
	<div class="wrap">
		<div class="w-head w-head--row">
			<div>
				<p class="w-eyebrow"><?php echo wd_icon( 'heart', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( sprintf( /* translators: %d notes */ _n( '%d love note', '%d love notes', $wd_n, 'wwmd' ), $wd_n ) ); ?></p>
				<h2 class="w-title"><?php esc_html_e( 'Wall of love', 'wwmd' ); ?></h2>
			</div>
			<div class="toggle" role="group" aria-label="<?php esc_attr_e( 'Sort notes', 'wwmd' ); ?>"><button type="button" class="is-on" data-wall-sort="new"><?php esc_html_e( 'Newest', 'wwmd' ); ?></button><button type="button" data-wall-sort="top"><?php esc_html_e( 'Most loved', 'wwmd' ); ?></button></div>
		</div>
		<form class="form wall-form" data-wall-form>
			<input type="hidden" name="wedding" value="<?php echo (int) $wd_id; ?>">
			<label class="field"><span class="screen-reader-text"><?php esc_html_e( 'Your message', 'wwmd' ); ?></span><textarea name="message" rows="2" maxlength="600" required placeholder="<?php esc_attr_e( 'Write a prayer, a wish or a funny memory…', 'wwmd' ); ?>"></textarea></label>
			<div class="wall-form__row">
				<label class="field"><span class="screen-reader-text"><?php esc_html_e( 'Your name', 'wwmd' ); ?></span><input name="name" maxlength="60" required placeholder="<?php esc_attr_e( 'Your name', 'wwmd' ); ?>"></label>
				<button class="btn w-btn" type="submit"><?php esc_html_e( 'Post with love', 'wwmd' ); ?></button>
			</div>
			<p class="form__msg" data-msg role="status"></p>
		</form>
		<div class="wall" data-wall data-c="<?php echo (int) wd_cols( count( (array) $wd_rows ), 3 ); ?>">
			<?php foreach ( (array) $wd_rows as $wd_i => $wd_r ) : ?>
				<article class="note tone-<?php echo esc_attr( $wd_tone[ $wd_r->id % 6 ] ); ?>" data-likes="<?php echo (int) $wd_r->likes; ?>" data-new="<?php echo (int) $wd_r->id; ?>">
					<p><?php echo nl2br( esc_html( $wd_r->message ) ); ?></p>
					<footer><span class="note__av"><?php echo wd_avatar( $wd_r->name ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><span class="note__who"><b><?php echo esc_html( $wd_r->name ); ?></b><small><?php echo esc_html( wd_ago( $wd_r->created ) ); ?></small></span><button type="button" class="like" data-like="wall" data-id="<?php echo (int) $wd_r->id; ?>" aria-pressed="false" aria-label="<?php esc_attr_e( 'Like this note', 'wwmd' ); ?>"><?php echo wd_icon( 'heart', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><b><?php echo (int) $wd_r->likes; ?></b></button></footer>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
