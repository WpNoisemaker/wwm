<?php
/**
 * HQ: every wedding page.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
// phpcs:ignore WordPress.Security.NonceVerification
$wd_q   = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
$wd_ids = get_posts( array( 'post_type' => 'wd_wedding', 'post_status' => array( 'publish', 'private', 'draft' ), 'posts_per_page' => 100, 's' => $wd_q, 'fields' => 'ids' ) );
?>
<div class="app-head"><div><p class="eyebrow"><?php esc_html_e( 'HQ', 'wwmd' ); ?></p><h1><?php esc_html_e( 'Weddings', 'wwmd' ); ?></h1></div></div>
<form class="filters filters--one filters--sm" method="get" role="search"><input type="hidden" name="tab" value="weddings"><div class="filters__search"><?php echo wd_icon( 'search', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><input type="search" name="q" value="<?php echo esc_attr( $wd_q ); ?>" placeholder="<?php esc_attr_e( 'Search couples', 'wwmd' ); ?>" aria-label="<?php esc_attr_e( 'Search couples', 'wwmd' ); ?>"></div><button class="btn btn--ink btn--sm" type="submit"><?php echo wd_icon( 'search', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Search', 'wwmd' ); ?></span></button></form>
<div class="table-wrap">
	<table class="table">
		<thead><tr><th><?php esc_html_e( 'Wedding', 'wwmd' ); ?></th><th><?php esc_html_e( 'Plan', 'wwmd' ); ?></th><th><?php esc_html_e( 'Coins', 'wwmd' ); ?></th><th><?php esc_html_e( 'Instagram (all / week)', 'wwmd' ); ?></th><th><?php esc_html_e( 'Cards', 'wwmd' ); ?></th><th></th></tr></thead>
		<tbody>
			<?php
			foreach ( $wd_ids as $wd_id ) :
				$wd_d   = wd_get( $wd_id );
				$wd_own = get_userdata( (int) get_post_field( 'post_author', $wd_id ) );
				$wd_sp  = (int) get_post_meta( $wd_id, '_wd_spotlight', true );
				?>
				<tr>
					<td><a href="<?php echo esc_url( get_permalink( $wd_id ) ); ?>"><b><?php echo esc_html( get_the_title( $wd_id ) ); ?></b></a><small><?php echo esc_html( wd_date( $wd_d['date'], 'j M Y' ) . ' · ' . $wd_d['city'] . ' · ' . ( $wd_own ? $wd_own->user_email : '' ) ); ?></small><?php echo 'private' === get_post_status( $wd_id ) ? '<span class="pill">' . esc_html__( 'Private', 'wwmd' ) . '</span>' : ''; ?><?php echo $wd_sp > time() ? '<span class="pill pill--warn">' . esc_html__( 'Spotlight', 'wwmd' ) . '</span>' : ''; ?><?php echo get_post_meta( $wd_id, '_wd_sample', true ) ? '<span class="pill">' . esc_html__( 'Starter', 'wwmd' ) . '</span>' : ''; ?></td>
					<td><?php wd_hq_form( 'wedding' ); ?><input type="hidden" name="id" value="<?php echo (int) $wd_id; ?>"><input type="hidden" name="do" value="plan"><select name="plan" onchange="this.form.submit()" aria-label="<?php esc_attr_e( 'Plan', 'wwmd' ); ?>"><?php foreach ( wd_plan_keys() as $wd_pk ) : ?><option value="<?php echo esc_attr( $wd_pk ); ?>" <?php selected( wd_plan( $wd_id ), $wd_pk ); ?>><?php echo esc_html( wd_plan_name( $wd_pk ) ); ?></option><?php endforeach; ?></select></form><?php if ( wd_is_paid( $wd_id ) && (int) get_post_meta( $wd_id, '_wd_premium_paid', true ) ) : ?><small><?php echo esc_html( sprintf( /* translators: %s */ __( 'Paid %s', 'wwmd' ), wd_money( (int) get_post_meta( $wd_id, '_wd_premium_paid', true ) ) ) ); ?></small><?php endif; ?>
						<?php if ( wd_can( $wd_id, 'templates' ) ) : ?>
							<?php wd_hq_form( 'wedding' ); ?><input type="hidden" name="id" value="<?php echo (int) $wd_id; ?>"><input type="hidden" name="do" value="custom"><label class="hq-custom"><small><?php esc_html_e( 'Custom design', 'wwmd' ); ?></small><select name="tpl" onchange="this.form.submit()"><option value=""><?php esc_html_e( 'None', 'wwmd' ); ?></option><?php foreach ( wd_custom_templates() as $wd_ck => $wd_ct ) : ?><option value="<?php echo esc_attr( $wd_ck ); ?>" <?php selected( wd_custom_tpl( $wd_id ), $wd_ck ); ?>><?php echo esc_html( $wd_ct['name'] ); ?></option><?php endforeach; ?></select></label></form>
						<?php endif; ?></td>
					<td><b><?php echo esc_html( number_format_i18n( (int) get_post_meta( $wd_id, '_wd_score', true ) ) ); ?></b><small>#<?php echo (int) wd_rank( $wd_id, 'week' ); ?> <?php esc_html_e( 'this week', 'wwmd' ); ?></small></td>
					<td><?php wd_hq_form( 'wedding', 'inline inline--fields' ); ?><input type="hidden" name="id" value="<?php echo (int) $wd_id; ?>"><input type="hidden" name="do" value="ig"><input type="number" name="ig" value="<?php echo (int) get_post_meta( $wd_id, '_wd_ig_eng', true ); ?>" aria-label="<?php esc_attr_e( 'Instagram likes & comments, all time', 'wwmd' ); ?>"><input type="number" name="ig_week" value="<?php echo (int) get_post_meta( $wd_id, '_wd_ig_eng_week', true ); ?>" aria-label="<?php esc_attr_e( 'Instagram likes & comments, last 7 days', 'wwmd' ); ?>"><button class="linkish" type="submit"><?php esc_html_e( 'Save', 'wwmd' ); ?></button></form><small><?php echo 'api' === get_post_meta( $wd_id, '_wd_ig_source', true ) ? esc_html__( 'from Instagram', 'wwmd' ) : esc_html( '@' . $wd_d['ig']['handle'] ); ?></small></td>
					<td><?php if ( wd_can( $wd_id, 'table_qr' ) ) : ?><?php wd_hq_form( 'wedding', 'inline inline--fields' ); ?><input type="hidden" name="id" value="<?php echo (int) $wd_id; ?>"><input type="hidden" name="do" value="cards"><select name="status" aria-label="<?php esc_attr_e( 'Card status', 'wwmd' ); ?>"><?php foreach ( array( '' => '–', 'requested' => __( 'Ordered', 'wwmd' ), 'printing' => __( 'Printing', 'wwmd' ), 'shipped' => __( 'Shipped', 'wwmd' ), 'delivered' => __( 'Delivered', 'wwmd' ) ) as $wd_k => $wd_l ) : ?><option value="<?php echo esc_attr( $wd_k ); ?>" <?php selected( $wd_d['cards']['status'], $wd_k ); ?>><?php echo esc_html( $wd_l ); ?></option><?php endforeach; ?></select><input type="text" name="tracking" value="<?php echo esc_attr( $wd_d['cards']['tracking'] ); ?>" placeholder="<?php esc_attr_e( 'Tracking', 'wwmd' ); ?>" aria-label="<?php esc_attr_e( 'Tracking', 'wwmd' ); ?>"><button class="linkish" type="submit"><?php esc_html_e( 'Save', 'wwmd' ); ?></button></form><?php if ( $wd_d['cards']['address'] ) : ?><small><?php echo esc_html( $wd_d['cards']['address'] . ' · ' . $wd_d['cards']['phone'] ); ?></small><?php endif; ?><?php else : ?>–<?php endif; ?></td>
					<td class="row-actions">
						<a class="linkish" href="<?php echo esc_url( wd_account_url( 'page', array( 'wid' => $wd_id ) ) ); ?>"><?php esc_html_e( 'Edit', 'wwmd' ); ?></a>
						<?php wd_hq_form( 'wedding' ); ?><input type="hidden" name="id" value="<?php echo (int) $wd_id; ?>"><input type="hidden" name="do" value="spotlight"><input type="hidden" name="days" value="<?php echo $wd_sp > time() ? 0 : 7; ?>"><button class="linkish" type="submit"><?php echo $wd_sp > time() ? esc_html__( 'End spotlight', 'wwmd' ) : esc_html__( 'Spotlight 7 days', 'wwmd' ); ?></button></form>
						<?php wd_hq_form( 'wedding' ); ?><input type="hidden" name="id" value="<?php echo (int) $wd_id; ?>"><input type="hidden" name="do" value="<?php echo 'private' === get_post_status( $wd_id ) ? 'show' : 'hide'; ?>"><button class="linkish" type="submit"><?php echo 'private' === get_post_status( $wd_id ) ? esc_html__( 'Publish', 'wwmd' ) : esc_html__( 'Hide', 'wwmd' ); ?></button></form>
						<?php wd_hq_form( 'wedding' ); ?><input type="hidden" name="id" value="<?php echo (int) $wd_id; ?>"><input type="hidden" name="do" value="delete"><button class="linkish linkish--danger" type="submit" data-confirm="<?php esc_attr_e( 'Delete this wedding page & everything guests added?', 'wwmd' ); ?>"><?php esc_html_e( 'Delete', 'wwmd' ); ?></button></form>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
