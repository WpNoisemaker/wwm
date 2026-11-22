<?php
/**
 * HQ: services marketplace. Approve partners, listings & reviews, read & answer chats, release or
 * refund held money, pay partners.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
global $wpdb;
$wd_bt    = wd_t( 'bookings' );
$wd_secs  = wd_sectors();
$wd_sum   = static function ( $col, $where ) use ( $wpdb, $wd_bt ) {
	$r = $wpdb->get_results( "SELECT test, COALESCE(SUM($col),0) AS s FROM $wd_bt WHERE $where GROUP BY test", OBJECT_K ); // phpcs:ignore WordPress.DB
	return array( isset( $r[0] ) ? (int) $r[0]->s : 0, isset( $r[1] ) ? (int) $r[1]->s : 0 );
};
$wd_held  = $wd_sum( 'amount', "status IN ('held','disputed')" );
$wd_comm  = $wd_sum( 'fee', "status = 'released'" );
/* translators: %s amount */
$wd_tst   = __( 'Plus %s in test bookings', 'wwmd' );
$wd_disp  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $wd_bt WHERE status = 'disputed'" ); // phpcs:ignore WordPress.DB
$wd_users = get_users(
	array(
		'meta_key' => 'wd_partner_status', // phpcs:ignore WordPress.DB.SlowDBQuery
		'number'   => 200,
	)
);
usort(
	$wd_users,
	static function ( $a, $b ) {
		$o = array( 'pending' => 0, 'approved' => 1, 'paused' => 2, 'rejected' => 3 );
		return ( $o[ get_user_meta( $a->ID, 'wd_partner_status', true ) ] ?? 9 ) <=> ( $o[ get_user_meta( $b->ID, 'wd_partner_status', true ) ] ?? 9 );
	}
);
$wd_wait  = get_posts(
	array(
		'post_type'   => 'wd_listing',
		'post_status' => 'pending',
		'numberposts' => 50,
	)
);
$wd_bks   = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wd_bt WHERE status <> 'cancelled' AND ( status <> 'pending' OR created > %s ) ORDER BY FIELD(status,'disputed','held','pending','released','refunded'), id DESC LIMIT 80", gmdate( 'Y-m-d H:i:s', time() - DAY_IN_SECONDS ) ) ); // phpcs:ignore WordPress.DB
$wd_outs  = $wpdb->get_results( 'SELECT * FROM ' . wd_t( 'payouts' ) . " WHERE wedding_id = 0 ORDER BY FIELD(status,'requested','paid','rejected'), id DESC LIMIT 40" ); // phpcs:ignore WordPress.DB
$wd_pst   = array(
	'pending'  => array( __( 'Applied', 'wwmd' ), 'pill--warn' ),
	'approved' => array( __( 'Approved', 'wwmd' ), 'pill--ok' ),
	'paused'   => array( __( 'Paused', 'wwmd' ), '' ),
	'rejected' => array( __( 'Declined', 'wwmd' ), '' ),
);
$wd_revs  = $wpdb->get_results( 'SELECT * FROM ' . wd_t( 'previews' ) . " ORDER BY FIELD(status,'pending','approved','hidden'), id DESC LIMIT 40" ); // phpcs:ignore WordPress.DB
$wd_chats = wd_chat_threads( 'hq', 0, 30 );
// phpcs:ignore WordPress.Security.NonceVerification
$wd_open  = isset( $_GET['chat'] ) ? array_map( 'intval', explode( '-', sanitize_text_field( wp_unslash( $_GET['chat'] ) ) . '-0' ) ) : array( 0, 0 );
$wd_wa    = static function ( $phone ) {
	$ph = preg_replace( '/\D/', '', (string) $phone );
	return $ph ? 'https://wa.me/' . ( 0 === strpos( $ph, '0' ) ? '234' . substr( $ph, 1 ) : $ph ) : '';
};
?>
<div class="app-head"><div><p class="eyebrow"><?php esc_html_e( 'HQ', 'wwmd' ); ?></p><h1><?php esc_html_e( 'Services marketplace', 'wwmd' ); ?></h1><p><?php echo esc_html( sprintf( /* translators: 1: commission, 2: days */ __( 'Commission %1$s on payment links paid on the site; the seller’s share goes to them straight away. Invoices are paid to sellers directly, with no commission.', 'wwmd' ), rtrim( rtrim( number_format( (float) wd_opt( 'market_fee' ), 1 ), '0' ), '.' ) . '%', (int) wd_opt( 'release_days' ) ) ); ?></p></div></div>
<div class="stat-grid stat-grid--4">
	<div class="stat tone-sky"><span><?php esc_html_e( 'Older payments still held', 'wwmd' ); ?></span><b><?php echo esc_html( wd_money( $wd_held[0] ) ); ?></b><small><?php echo esc_html( $wd_held[1] ? sprintf( $wd_tst, wd_money( $wd_held[1] ) ) : __( 'Paid, service not done yet', 'wwmd' ) ); ?></small></div>
	<div class="stat tone-butter"><span><?php esc_html_e( 'Commission earned', 'wwmd' ); ?></span><b><?php echo esc_html( wd_money( $wd_comm[0] ) ); ?></b><small><?php echo esc_html( $wd_comm[1] ? sprintf( $wd_tst, wd_money( $wd_comm[1] ) ) : __( 'From payment links', 'wwmd' ) ); ?></small></div>
	<div class="stat tone-coral"><span><?php esc_html_e( 'Problems reported', 'wwmd' ); ?></span><b><?php echo (int) $wd_disp; ?></b><small><?php esc_html_e( 'From before 1.5.1', 'wwmd' ); ?></small></div>
	<div class="stat tone-lavender"><span><?php esc_html_e( 'Sellers', 'wwmd' ); ?></span><b><?php echo (int) count( wd_partner_ids() ); ?></b><small><?php echo esc_html( sprintf( /* translators: %d */ __( '%d waiting for approval', 'wwmd' ), count( array_filter( $wd_users, static function ( $u ) { return 'pending' === get_user_meta( $u->ID, 'wd_partner_status', true ); } ) ) ) ); ?></small></div>
</div>

<div class="app-cols" id="chats">
	<div class="panel">
		<h2><?php esc_html_e( 'Chats', 'wwmd' ); ?></h2>
		<p class="hint"><?php esc_html_e( 'Every chat between customers & sellers. Answer as “Our team” when a seller is slow, or for starter sellers nobody else reads. Chats flagged in red mention phone, bank or payment details.', 'wwmd' ); ?></p>
		<?php if ( $wd_chats ) : ?>
			<?php
			wd_chat_list(
				$wd_chats,
				'hq',
				static function ( $t ) {
					return add_query_arg( array( 'tab' => 'market', 'chat' => (int) $t->partner_id . '-' . (int) $t->user_id ), wd_url( 'hq' ) ) . '#chats';
				}
			);
			?>
		<?php else : ?>
			<p class="empty-note"><?php esc_html_e( 'No chats yet.', 'wwmd' ); ?></p>
		<?php endif; ?>
	</div>
	<div class="panel panel--chat">
		<?php if ( $wd_open[0] && $wd_open[1] && wd_chat_rows( $wd_open[0], $wd_open[1] ) ) : ?>
			<?php $wd_cpp = wd_partner( $wd_open[0] ); $wd_cu = get_userdata( $wd_open[1] ); ?>
			<?php
			get_template_part(
				'template-parts/chat',
				null,
				array(
					'partner' => $wd_open[0],
					'user'    => $wd_open[1],
					'role'    => 'hq',
					'title'   => ( $wd_cu ? $wd_cu->display_name : '' ) . ' → ' . ( $wd_cpp ? $wd_cpp['business'] : '' ),
				)
			);
			?>
		<?php else : ?>
			<h2><?php esc_html_e( 'Open a chat', 'wwmd' ); ?></h2>
			<p class="muted"><?php esc_html_e( 'Pick a chat on the left to read it & answer.', 'wwmd' ); ?></p>
		<?php endif; ?>
	</div>
</div>

<div class="panel" id="reviews">
	<h2><?php esc_html_e( 'Seller reviews', 'wwmd' ); ?></h2>
	<p class="hint"><?php esc_html_e( 'Only customers whose paid booking is done can review. New reviews wait here until you approve them.', 'wwmd' ); ?></p>
	<?php if ( $wd_revs ) : ?>
		<ul class="req-list req-list--flat">
			<?php foreach ( $wd_revs as $wd_r ) : ?>
				<?php $wd_rp = wd_partner( (int) $wd_r->partner_id ); ?>
				<li>
					<div class="req-list__top"><b><?php echo esc_html( $wd_r->name ); ?></b> <?php echo wd_stars( (int) $wd_r->rating ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <span class="pill <?php echo 'approved' === $wd_r->status ? 'pill--ok' : ( 'pending' === $wd_r->status ? 'pill--warn' : '' ); ?>"><?php echo esc_html( array( 'pending' => __( 'Waiting', 'wwmd' ), 'approved' => __( 'Live', 'wwmd' ), 'hidden' => __( 'Hidden', 'wwmd' ) )[ $wd_r->status ] ?? $wd_r->status ); ?></span></div>
					<p><?php echo esc_html( ( $wd_rp ? $wd_rp['business'] : '' ) . ' · ' . get_the_title( (int) $wd_r->listing_id ) . ' · ' . wd_ago( $wd_r->created ) ); ?></p>
					<p class="req-list__details"><?php echo esc_html( $wd_r->text ); ?></p>
					<div class="row-actions">
						<?php if ( 'approved' !== $wd_r->status ) : ?><?php wd_market_form( 'hq_review', array( 'rid' => $wd_r->id, 'to' => 'approved' ), false, 'inline' ); ?><button class="btn btn--ink btn--sm" type="submit"><?php esc_html_e( 'Approve', 'wwmd' ); ?></button></form><?php endif; ?>
						<?php if ( 'hidden' !== $wd_r->status ) : ?><?php wd_market_form( 'hq_review', array( 'rid' => $wd_r->id, 'to' => 'hidden' ), false, 'inline' ); ?><button class="btn btn--ghost btn--sm" type="submit"><?php esc_html_e( 'Hide', 'wwmd' ); ?></button></form><?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php else : ?>
		<p class="empty-note"><?php esc_html_e( 'No reviews yet.', 'wwmd' ); ?></p>
	<?php endif; ?>
</div>

<div class="panel">
	<h2><?php esc_html_e( 'Bookings', 'wwmd' ); ?></h2>
	<p class="hint"><?php esc_html_e( 'Refund a booking in your payment dashboard first, then mark it refunded here so the seller is not paid.', 'wwmd' ); ?></p>
	<?php if ( $wd_bks ) : ?>
		<div class="table-wrap"><table class="table table--compact table--stack">
			<thead><tr><th><?php esc_html_e( 'Booking', 'wwmd' ); ?></th><th><?php esc_html_e( 'Customer', 'wwmd' ); ?></th><th><?php esc_html_e( 'Amount', 'wwmd' ); ?></th><th><?php esc_html_e( 'Status', 'wwmd' ); ?></th><th></th></tr></thead>
			<tbody>
			<?php foreach ( $wd_bks as $wd_b ) : ?>
				<?php $wd_pp = wd_partner( (int) $wd_b->partner_id ); ?>
				<tr>
					<td data-label="<?php esc_attr_e( 'Booking', 'wwmd' ); ?>"><a href="<?php echo esc_url( get_permalink( (int) $wd_b->listing_id ) ); ?>"><?php echo esc_html( get_the_title( (int) $wd_b->listing_id ) ); ?></a><br><small><?php echo esc_html( ( $wd_pp ? $wd_pp['business'] . ' · ' : '' ) . wd_booking_when( $wd_b ) ); ?></small></td>
					<td data-label="<?php esc_attr_e( 'Customer', 'wwmd' ); ?>"><?php echo esc_html( $wd_b->name ); ?><br><small><?php if ( $wd_wa( $wd_b->phone ) ) : ?><a class="linkish" href="<?php echo esc_url( $wd_wa( $wd_b->phone ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $wd_b->phone ); ?></a><?php endif; ?></small></td>
					<td data-label="<?php esc_attr_e( 'Amount', 'wwmd' ); ?>"><b><?php echo esc_html( wd_money( $wd_b->amount, $wd_b->currency ) ); ?></b><br><small class="muted"><?php echo esc_html( sprintf( /* translators: 1: fee 2: net */ __( 'Fee %1$s · seller %2$s', 'wwmd' ), wd_money( $wd_b->fee, $wd_b->currency ), wd_money( $wd_b->net, $wd_b->currency ) ) ); ?></small></td>
					<td data-label="<?php esc_attr_e( 'Status', 'wwmd' ); ?>"><?php echo wd_booking_pill( $wd_b->status ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php if ( $wd_b->test ) : ?> <span class="tag tag--sample"><?php esc_html_e( 'Test', 'wwmd' ); ?></span><?php endif; ?><?php if ( $wd_b->log ) : ?><br><small class="muted"><?php echo esc_html( current( array_slice( explode( "\n", trim( $wd_b->log ) ), -1 ) ) ); ?></small><?php endif; ?></td>
					<td class="row-actions">
						<?php if ( in_array( $wd_b->status, array( 'held', 'disputed' ), true ) ) : ?>
							<?php wd_market_form( 'hq_booking', array( 'bid' => $wd_b->id, 'to' => 'released' ), false, 'inline' ); ?><button class="btn btn--ink btn--sm" type="submit"><?php esc_html_e( 'Release', 'wwmd' ); ?></button></form>
							<?php wd_market_form( 'hq_booking', array( 'bid' => $wd_b->id, 'to' => 'refunded' ), false, 'inline' ); ?><button class="btn btn--ghost btn--sm" type="submit"><?php esc_html_e( 'Mark refunded', 'wwmd' ); ?></button></form>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table></div>
	<?php else : ?>
		<p class="empty-note"><?php esc_html_e( 'No bookings yet.', 'wwmd' ); ?></p>
	<?php endif; ?>
</div>

<div class="app-cols">
	<div class="panel">
		<h2><?php esc_html_e( 'Sellers', 'wwmd' ); ?></h2>
		<ul class="req-list req-list--flat">
			<?php foreach ( $wd_users as $wd_u ) : ?>
				<?php $wd_pp = wd_partner( $wd_u->ID ); ?>
				<?php if ( ! $wd_pp ) { continue; } ?>
				<li>
					<div class="req-list__top"><b><?php echo esc_html( $wd_pp['business'] ); ?></b><span class="pill <?php echo esc_attr( $wd_pst[ $wd_pp['status'] ][1] ?? '' ); ?>"><?php echo esc_html( $wd_pst[ $wd_pp['status'] ][0] ?? $wd_pp['status'] ); ?></span><?php if ( wd_seller_premium( $wd_pp ) ) : ?><span class="pill pill--premium"><?php esc_html_e( 'Premium', 'wwmd' ); ?></span><?php endif; ?><?php if ( get_user_meta( $wd_u->ID, '_wd_sample', true ) ) : ?><span class="tag tag--sample"><?php esc_html_e( 'Starter', 'wwmd' ); ?></span><?php endif; ?></div>
					<p><?php echo esc_html( ( $wd_secs[ $wd_pp['sector'] ][0] ?? '' ) . ' · ' . trim( $wd_pp['city'] . ', ' . $wd_pp['country'], ', ' ) ); ?> · <?php echo esc_html( $wd_u->user_email ); ?><?php if ( 'approved' === $wd_pp['status'] ) : ?> · <a href="<?php echo esc_url( wd_partner_url( $wd_u->ID ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Profile', 'wwmd' ); ?></a><?php endif; ?><?php if ( $wd_pp['website'] ) : ?> · <a href="<?php echo esc_url( $wd_pp['website'] ); ?>" target="_blank" rel="noopener nofollow"><?php esc_html_e( 'Website', 'wwmd' ); ?></a><?php endif; ?></p>
					<?php if ( $wd_pp['about'] ) : ?><p class="req-list__details"><?php echo esc_html( $wd_pp['about'] ); ?></p><?php endif; ?>
					<div class="row-actions">
						<?php if ( $wd_wa( $wd_pp['phone'] ) ) : ?><a class="btn btn--wa btn--sm" href="<?php echo esc_url( $wd_wa( $wd_pp['phone'] ) ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'whatsapp', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'WhatsApp', 'wwmd' ); ?></a><?php endif; ?>
						<?php foreach ( array( 'approved' => __( 'Approve', 'wwmd' ), 'paused' => __( 'Pause', 'wwmd' ), 'rejected' => __( 'Decline', 'wwmd' ) ) as $wd_to => $wd_lb ) : ?>
							<?php if ( $wd_to === $wd_pp['status'] || ( 'rejected' === $wd_to && 'approved' === $wd_pp['status'] ) || ( 'paused' === $wd_to && 'approved' !== $wd_pp['status'] ) ) { continue; } ?>
							<?php wd_market_form( 'hq_partner', array( 'uid' => $wd_u->ID, 'to' => $wd_to ), false, 'inline' ); ?><button class="btn btn--sm <?php echo 'approved' === $wd_to ? 'btn--ink' : 'btn--ghost'; ?>" type="submit"><?php echo esc_html( $wd_lb ); ?></button></form>
						<?php endforeach; ?>
						<?php if ( 'approved' === $wd_pp['status'] ) : ?>
							<?php $wd_ver = wd_partner_verified( $wd_u->ID ); ?>
							<?php wd_market_form( 'hq_partner_verify', array( 'uid' => $wd_u->ID, 'on' => $wd_ver ? 0 : 1 ), false, 'inline' ); ?><button class="btn btn--sm btn--ghost" type="submit"><?php echo $wd_ver ? esc_html__( 'Remove verified badge', 'wwmd' ) : esc_html__( 'Give verified badge', 'wwmd' ); ?></button></form>
							<?php wd_market_form( 'hq_partner_premium', array( 'uid' => $wd_u->ID, 'on' => wd_seller_premium( $wd_pp ) ? 0 : 1 ), false, 'inline' ); ?><button class="btn btn--sm <?php echo wd_seller_premium( $wd_pp ) ? 'btn--ghost' : 'btn--marigold'; ?>" type="submit"><?php echo wd_seller_premium( $wd_pp ) ? esc_html__( 'End Premium', 'wwmd' ) : esc_html__( 'Make Premium', 'wwmd' ); ?></button></form>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
			<?php if ( ! $wd_users ) : ?><li class="empty-note"><?php esc_html_e( 'No seller applications yet.', 'wwmd' ); ?></li><?php endif; ?>
		</ul>
	</div>
	<div>
		<div class="panel">
			<h2><?php esc_html_e( 'New listings to check', 'wwmd' ); ?></h2>
			<ul class="mini-list">
				<?php foreach ( $wd_wait as $wd_p ) : ?>
					<?php $wd_x = wd_listing( $wd_p->ID ); $wd_pp = wd_partner( (int) $wd_p->post_author ); ?>
					<li>
						<span><b><?php echo esc_html( $wd_p->post_title ); ?></b> <small><?php echo esc_html( ( $wd_pp ? $wd_pp['business'] . ' · ' : '' ) . wd_listing_price( $wd_x ) . ' · ' . $wd_x['city'] ); ?></small></span>
						<span class="row-actions">
							<a class="btn btn--ghost btn--sm" href="<?php echo esc_url( get_preview_post_link( $wd_p ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Preview', 'wwmd' ); ?></a>
							<?php wd_market_form( 'hq_listing', array( 'lid' => $wd_p->ID, 'to' => 'publish' ), false, 'inline' ); ?><button class="btn btn--ink btn--sm" type="submit"><?php esc_html_e( 'Approve', 'wwmd' ); ?></button></form>
							<?php wd_market_form( 'hq_listing', array( 'lid' => $wd_p->ID, 'to' => 'trash' ), false, 'inline' ); ?><button class="btn btn--ghost btn--sm" type="submit"><?php esc_html_e( 'Remove', 'wwmd' ); ?></button></form>
						</span>
					</li>
				<?php endforeach; ?>
				<?php if ( ! $wd_wait ) : ?><li class="empty-note"><?php esc_html_e( 'Nothing waiting.', 'wwmd' ); ?></li><?php endif; ?>
			</ul>
		</div>
		<div class="panel">
			<h2><?php esc_html_e( 'Seller payouts', 'wwmd' ); ?></h2>
			<ul class="mini-list">
				<?php foreach ( $wd_outs as $wd_o ) : ?>
					<?php $wd_pp = wd_partner( (int) $wd_o->user_id ); ?>
					<li>
						<span><b><?php echo esc_html( wd_money( $wd_o->amount ) ); ?></b> <small><?php echo esc_html( ( $wd_pp ? $wd_pp['business'] : '' ) . ' · ' . $wd_o->bank . ' ' . $wd_o->account_no . ' · ' . $wd_o->account_name ); ?></small></span>
						<?php if ( 'requested' === $wd_o->status ) : ?>
							<span class="row-actions">
								<?php wd_market_form( 'hq_payout', array( 'pid' => $wd_o->id, 'to' => 'paid' ), false, 'inline' ); ?><button class="btn btn--ink btn--sm" type="submit"><?php esc_html_e( 'Mark paid', 'wwmd' ); ?></button></form>
								<?php wd_market_form( 'hq_payout', array( 'pid' => $wd_o->id, 'to' => 'rejected' ), false, 'inline' ); ?><button class="btn btn--ghost btn--sm" type="submit"><?php esc_html_e( 'Decline', 'wwmd' ); ?></button></form>
							</span>
						<?php else : ?>
							<span class="pill<?php echo 'paid' === $wd_o->status ? ' pill--ok' : ''; ?>"><?php echo esc_html( 'paid' === $wd_o->status ? __( 'Paid', 'wwmd' ) : __( 'Declined', 'wwmd' ) ); ?></span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
				<?php if ( ! $wd_outs ) : ?><li class="empty-note"><?php esc_html_e( 'No payout requests yet.', 'wwmd' ); ?></li><?php endif; ?>
			</ul>
		</div>
	</div>
</div>
