<?php
/**
 * HQ overview.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_t   = wd_platform_totals();
$wd_rev = 0;
$wd_gmv = 0;
foreach ( $wd_t['money'] as $wd_m ) {
	if ( in_array( $wd_m->kind, array( 'spray', 'gift' ), true ) ) {
		$wd_gmv += wd_to_ngn( $wd_m->amount, $wd_m->currency );
		$wd_rev += wd_to_ngn( $wd_m->fee, $wd_m->currency );
	} elseif ( 'booking' !== $wd_m->kind ) {
		$wd_rev += wd_to_ngn( $wd_m->amount, $wd_m->currency );
	}
}
$wd_rev += $wd_t['commission'];
?>
<div class="app-head"><div><p class="eyebrow"><?php esc_html_e( 'HQ', 'wwmd' ); ?></p><h1><?php esc_html_e( 'How the platform is doing', 'wwmd' ); ?></h1></div></div>
<div class="stat-grid">
	<div class="stat tone-butter"><span><?php esc_html_e( 'Wedding pages', 'wwmd' ); ?></span><b><?php echo esc_html( number_format_i18n( $wd_t['weddings'] ) ); ?></b><small><?php echo esc_html( sprintf( /* translators: 1: Pro pages 2: Premium pages 3: High Society pages */ __( '%1$d Pro · %2$d Premium · %3$d High Society', 'wwmd' ), $wd_t['pro'], $wd_t['premium'], $wd_t['society'] ) ); ?></small></div>
	<div class="stat tone-lavender"><span><?php esc_html_e( 'Couples', 'wwmd' ); ?></span><b><?php echo esc_html( number_format_i18n( $wd_t['couples'] ) ); ?></b></div>
	<div class="stat tone-sky"><span><?php esc_html_e( 'Page visits', 'wwmd' ); ?></span><b><?php echo esc_html( wd_short( $wd_t['views'] ) ); ?></b></div>
	<div class="stat tone-mint"><span><?php esc_html_e( 'RSVPs', 'wwmd' ); ?></span><b><?php echo esc_html( number_format_i18n( $wd_t['rsvps'] ) ); ?></b></div>
	<div class="stat tone-coral"><span><?php esc_html_e( 'Gifts & sprays (₦ equivalent)', 'wwmd' ); ?></span><b><?php echo esc_html( '₦' . wd_short( $wd_gmv ) ); ?></b></div>
	<div class="stat tone-butter"><span><?php esc_html_e( 'Our revenue (fees, plans & commission)', 'wwmd' ); ?></span><b><?php echo esc_html( '₦' . wd_short( $wd_rev ) ); ?></b></div>
</div>
<div class="app-cols">
	<div class="panel">
		<h2><?php esc_html_e( 'Needs you', 'wwmd' ); ?></h2>
		<ul class="mini-list">
			<li><span><?php esc_html_e( 'Payout requests waiting', 'wwmd' ); ?></span><a class="pill<?php echo $wd_t['payouts'] ? ' pill--warn' : ''; ?>" href="<?php echo esc_url( wd_url( 'hq', array( 'tab' => 'money' ) ) ); ?>"><?php echo (int) $wd_t['payouts']; ?></a></li>
			<li><span><?php esc_html_e( 'Marketplace: sellers, listings, problems & payouts to check', 'wwmd' ); ?></span><a class="pill<?php echo $wd_t['market'] ? ' pill--warn' : ''; ?>" href="<?php echo esc_url( wd_url( 'hq', array( 'tab' => 'market' ) ) ); ?>"><?php echo (int) $wd_t['market']; ?></a></li>
			<li><span><?php esc_html_e( 'New service requests', 'wwmd' ); ?></span><a class="pill<?php echo $wd_t['requests'] ? ' pill--warn' : ''; ?>" href="<?php echo esc_url( wd_url( 'hq', array( 'tab' => 'requests' ) ) ); ?>"><?php echo (int) $wd_t['requests']; ?></a></li>
			<li><span><?php esc_html_e( 'Vendors listed', 'wwmd' ); ?></span><a class="pill" href="<?php echo esc_url( wd_url( 'hq', array( 'tab' => 'vendors' ) ) ); ?>"><?php echo (int) $wd_t['vendors']; ?></a></li>
		</ul>
		<?php if ( wd_pay_test_mode() ) : ?><p class="hint"><?php esc_html_e( 'Payments are in test mode. Add your payment keys in Settings to take real money.', 'wwmd' ); ?></p><?php endif; ?>
	</div>
	<div class="panel">
		<h2><?php esc_html_e( 'Top this week', 'wwmd' ); ?></h2>
		<ol class="mini-rank">
			<?php foreach ( wd_top( 'week', 5 ) as $wd_id ) : ?>
				<li><a href="<?php echo esc_url( get_permalink( $wd_id ) ); ?>"><?php echo esc_html( get_the_title( $wd_id ) ); ?></a><b><?php echo esc_html( number_format_i18n( (int) get_post_meta( $wd_id, '_wd_score_week', true ) ) ); ?></b></li>
			<?php endforeach; ?>
		</ol>
		<?php wd_hq_form( 'recount' ); ?><button class="btn btn--ghost btn--sm" type="submit"><?php esc_html_e( 'Recalculate all scores now', 'wwmd' ); ?></button></form>
	</div>
</div>
<?php if ( get_posts( array( 'post_type' => 'wd_wedding', 'meta_key' => '_wd_sample', 'posts_per_page' => 1, 'fields' => 'ids' ) ) ) : // phpcs:ignore WordPress.DB.SlowDBQuery ?>
	<div class="panel panel--soft">
		<h2><?php esc_html_e( 'Starter content', 'wwmd' ); ?></h2>
		<p><?php esc_html_e( 'The site came with 8 starter weddings & their guests’ activity, 19 vendors, plus marketplace sellers with their listings & test bookings, so it looks alive from day one. Visitors see them like any other page. Remove them when real couples & sellers join, so nobody competes on the Buzz Chart with made-up numbers. This also removes the AbujaCarhire & ViaskyNigeria accounts we set up, so invite them to sign up as sellers themselves.', 'wwmd' ); ?></p>
		<?php wd_hq_form( 'samples' ); ?><button class="btn btn--danger btn--sm" type="submit" data-confirm="<?php esc_attr_e( 'Delete all starter weddings, vendors, sellers, listings & test payments for good?', 'wwmd' ); ?>"><?php esc_html_e( 'Remove all starter content', 'wwmd' ); ?></button></form>
	</div>
<?php endif; ?>
