<?php
/**
 * HQ: vendors.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_ids = get_posts( array( 'post_type' => 'wd_vendor', 'posts_per_page' => 200, 'orderby' => 'title', 'order' => 'ASC', 'fields' => 'ids' ) );
?>
<div class="app-head"><div><p class="eyebrow"><?php esc_html_e( 'HQ', 'wwmd' ); ?></p><h1><?php esc_html_e( 'Vendors', 'wwmd' ); ?></h1><p><?php esc_html_e( 'Vendors are added automatically when couples list them on their page. Verify the ones you have checked (or who pay for a verified badge).', 'wwmd' ); ?></p></div></div>
<div class="table-wrap"><table class="table">
	<thead><tr><th><?php esc_html_e( 'Vendor', 'wwmd' ); ?></th><th><?php esc_html_e( 'Type & city', 'wwmd' ); ?></th><th><?php esc_html_e( 'Rating', 'wwmd' ); ?></th><th></th></tr></thead>
	<tbody>
	<?php foreach ( $wd_ids as $wd_id ) : $wd_v = wd_vendor( $wd_id ); ?>
		<tr>
			<td><a href="<?php echo esc_url( get_permalink( $wd_id ) ); ?>"><b><?php echo esc_html( get_the_title( $wd_id ) ); ?></b></a><?php echo $wd_v['verified'] ? ' <span class="pill pill--ok">' . esc_html__( 'Verified', 'wwmd' ) . '</span>' : ''; ?><small><?php echo esc_html( $wd_v['ig'] ? '@' . ltrim( $wd_v['ig'], '@' ) : '' ); ?></small></td>
			<td><?php echo esc_html( wd_vendor_type( $wd_id ) ); ?><small><?php echo esc_html( trim( $wd_v['city'] . ', ' . $wd_v['country'], ', ' ) ); ?></small></td>
			<td><?php echo wd_stars( (float) get_post_meta( $wd_id, '_wd_rating', true ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?><small><?php echo esc_html( sprintf( /* translators: %d */ _n( '%d review', '%d reviews', (int) get_post_meta( $wd_id, '_wd_reviews', true ), 'wwmd' ), (int) get_post_meta( $wd_id, '_wd_reviews', true ) ) ); ?></small></td>
			<td class="row-actions">
				<?php wd_hq_form( 'vendor' ); ?><input type="hidden" name="id" value="<?php echo (int) $wd_id; ?>"><input type="hidden" name="do" value="verify"><button class="linkish" type="submit"><?php echo $wd_v['verified'] ? esc_html__( 'Remove badge', 'wwmd' ) : esc_html__( 'Verify', 'wwmd' ); ?></button></form>
				<?php wd_hq_form( 'vendor' ); ?><input type="hidden" name="id" value="<?php echo (int) $wd_id; ?>"><input type="hidden" name="do" value="delete"><button class="linkish linkish--danger" type="submit" data-confirm="<?php esc_attr_e( 'Remove this vendor from the directory?', 'wwmd' ); ?>"><?php esc_html_e( 'Remove', 'wwmd' ); ?></button></form>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table></div>
