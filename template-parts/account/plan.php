<?php
/**
 * Plan: see your plan, move up to Pro or Premium (online in full or in instalments, or on WhatsApp),
 * or ask for a High Society quote. Moving up only costs the difference.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_id   = $args['id'];
$wd_d    = $args['d'];
$wd_inst = max( 1, (int) wd_opt( 'instalments' ) );
$wd_ps   = wd_plans();
$wd_note = '';

// phpcs:disable WordPress.Security.NonceVerification
// The plan to pay for: ?tier=pro|premium (a higher plan, or the rest of the plan they are on).
$wd_want = isset( $_GET['tier'] ) ? sanitize_key( wp_unslash( $_GET['tier'] ) ) : '';
if ( ! wd_plan_payable( $wd_id, $wd_want ) ) {
	$wd_want = '';
	// The rest of their own plan first (paid in instalments), else the next plan up, Premium first.
	foreach ( array( wd_plan( $wd_id ), 'premium', 'pro' ) as $wd_p ) {
		if ( ! $wd_want && wd_plan_payable( $wd_id, $wd_p ) ) {
			$wd_want = $wd_p;
		}
	}
}

// Start a plan payment (?pay=full|part&tier=…) from this tab.
if ( isset( $_GET['pay'], $_GET['_wpnonce'] ) && $wd_want && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'wd_premium_' . $wd_id ) ) {
	$wd_amt = 'part' === $_GET['pay'] ? wd_plan_instalment( $wd_id, $wd_want ) : wd_plan_left( $wd_id, $wd_want );
	$wd_u   = wp_get_current_user();
	$wd_res = wd_pay_start(
		array(
			'wedding_id' => $wd_id,
			'kind'       => 'premium',
			'item'       => $wd_want,
			'amount'     => $wd_amt,
			'name'       => $wd_u->display_name,
			'email'      => $wd_u->user_email,
			'message'    => '',
			'return'     => wd_account_url( 'plan', array( 'wid' => $wd_id ) ),
		)
	);
	if ( ! is_wp_error( $wd_res ) && ! empty( $wd_res['url'] ) ) {
		echo '<script>location.href=' . wp_json_encode( esc_url_raw( $wd_res['url'] ) ) . ';</script>';
	} elseif ( ! is_wp_error( $wd_res ) ) {
		/* translators: %s plan name */
		$wd_note = '<div class="notice notice--ok" role="status">' . wd_icon( 'check', 18 ) . '<span>' . esc_html( sprintf( __( '%s is on. Enjoy your new features!', 'wwmd' ), wd_plan_name( wd_plan( $wd_id ) ) ) ) . '</span></div>';
	} else {
		$wd_note = '<div class="notice notice--err" role="alert"><span>' . esc_html( $wd_res->get_error_message() ) . '</span></div>';
	}
}
if ( isset( $_GET['wdpay'] ) ) {
	wd_pay_verify( wd_pay_ref_from_request() );
}
// phpcs:enable
$wd_plan  = wd_plan( $wd_id );
$wd_paid  = wd_plan_paid( $wd_id );
$wd_price = (int) ( $wd_ps[ $wd_plan ]['price'] ?? 0 );
$wd_left  = wd_plan_left( $wd_id, $wd_plan );
$wd_nonce = wp_create_nonce( 'wd_premium_' . $wd_id );
if ( $wd_want && ! wd_plan_payable( $wd_id, $wd_want ) ) {
	$wd_want = '';
	foreach ( array( 'premium', 'pro' ) as $wd_p ) {
		if ( ! $wd_want && wd_plan_payable( $wd_id, $wd_p ) ) {
			$wd_want = $wd_p;
		}
	}
}
$wd_pay_url = static function ( $how, $tier ) use ( $wd_id, $wd_nonce ) {
	return add_query_arg( array( 'tab' => 'plan', 'wid' => $wd_id, 'tier' => $tier, 'pay' => $how, '_wpnonce' => $wd_nonce ), wd_url( 'account' ) );
};
echo $wd_note; // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts.
?>
<div class="app-head"><div><p class="eyebrow"><?php esc_html_e( 'Plan', 'wwmd' ); ?></p><h1><?php echo esc_html( sprintf( /* translators: %s plan */ __( 'You are on %s', 'wwmd' ), wd_plan_name( $wd_plan ) ) ); ?></h1></div><?php echo wd_plan_tag( $wd_plan ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>

<?php if ( 'free' !== $wd_plan ) : ?>
	<div class="panel">
		<?php if ( 'society' === $wd_plan ) : ?>
			<p><?php esc_html_e( 'Your event manager plans your branding, prints & livestream coverage with you on WhatsApp.', 'wwmd' ); ?></p>
			<a class="btn btn--ink" href="<?php echo esc_url( wd_society_quote_url( $wd_id ) ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'whatsapp', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Message your event manager', 'wwmd' ); ?></a>
		<?php elseif ( $wd_paid > 0 && $wd_price ) : ?>
			<p><?php echo esc_html( sprintf( /* translators: 1 paid 2 price 3 plan */ __( 'Paid %1$s of %2$s for %3$s.', 'wwmd' ), wd_money( min( $wd_paid, $wd_price ) ), wd_money( $wd_price ), wd_plan_name( $wd_plan ) ) ); ?></p>
			<div class="meter meter--lg"><i style="width:<?php echo (int) min( 100, round( $wd_paid / $wd_price * 100 ) ); ?>%"></i></div>
			<?php if ( $wd_left > 0 ) : ?>
				<p class="hint"><?php echo esc_html( 'premium' === $wd_plan ? sprintf( /* translators: %s amount */ __( '%s left to pay. Your table cards are printed once Premium is paid in full.', 'wwmd' ), wd_money( $wd_left ) ) : sprintf( /* translators: %s amount */ __( '%s left to pay.', 'wwmd' ), wd_money( $wd_left ) ) ); ?></p>
				<a class="btn btn--ink" href="<?php echo esc_url( $wd_pay_url( 'part', $wd_plan ) ); ?>"><?php echo esc_html( sprintf( /* translators: %s amount */ __( 'Pay the next instalment (%s)', 'wwmd' ), wd_money( wd_plan_instalment( $wd_id, $wd_plan ) ) ) ); ?></a>
			<?php endif; ?>
		<?php else : ?>
			<p class="muted"><?php esc_html_e( 'Our team switched this plan on for you.', 'wwmd' ); ?></p>
		<?php endif; ?>
		<ul class="checklist">
			<?php foreach ( wd_features() as $wd_k => $wd_label ) : ?>
				<?php if ( wd_can( $wd_id, $wd_k ) ) : ?><li class="is-done"><?php echo wd_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $wd_label ); ?></li><?php endif; ?>
			<?php endforeach; ?>
			<?php if ( 'society' === $wd_plan ) : ?>
				<?php foreach ( wd_society_perks() as $wd_perk ) : ?><li class="is-done"><?php echo wd_icon( 'gem', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $wd_perk ); ?></li><?php endforeach; ?>
			<?php endif; ?>
		</ul>
	</div>
<?php endif; ?>

<?php if ( 'society' !== $wd_plan ) : ?>
	<?php get_template_part( 'template-parts/plans', null, array( 'id' => $wd_id, 'want' => $wd_want ) ); ?>
<?php endif; ?>

<?php if ( $wd_want ) : ?>
	<div class="panel pay-options" id="pay">
		<h2><?php echo esc_html( $wd_want === $wd_plan ? sprintf( /* translators: %s plan */ __( 'Finish paying for %s', 'wwmd' ), wd_plan_name( $wd_want ) ) : sprintf( /* translators: %s plan */ __( 'Move up to %s', 'wwmd' ), wd_plan_name( $wd_want ) ) ); ?></h2>
		<?php $wd_can_pay = array_values( array_filter( wd_paid_plans(), static function ( $p ) use ( $wd_id ) { return wd_plan_payable( $wd_id, $p ); } ) ); ?>
		<?php if ( count( $wd_can_pay ) > 1 ) : ?>
			<div class="seg-links" role="group" aria-label="<?php esc_attr_e( 'Plan to pay for', 'wwmd' ); ?>">
				<?php foreach ( $wd_can_pay as $wd_p ) : ?>
					<a class="chip<?php echo $wd_p === $wd_want ? ' is-on' : ''; ?>" href="<?php echo esc_url( add_query_arg( array( 'tab' => 'plan', 'wid' => $wd_id, 'tier' => $wd_p ), wd_url( 'account' ) ) . '#pay' ); ?>"<?php echo $wd_p === $wd_want ? ' aria-current="true"' : ''; ?>><?php echo esc_html( wd_plan_name( $wd_p ) . ' · ' . wd_money( (int) $wd_ps[ $wd_p ]['price'] ) ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php if ( $wd_paid > 0 && $wd_want !== $wd_plan ) : ?>
			<p class="hint"><?php echo esc_html( sprintf( /* translators: 1 paid 2 plan 3 left */ __( 'You have paid %1$s already, so %2$s costs you %3$s more.', 'wwmd' ), wd_money( $wd_paid ), wd_plan_name( $wd_want ), wd_money( wd_plan_left( $wd_id, $wd_want ) ) ) ); ?></p>
		<?php endif; ?>
		<div class="pay-options__grid">
			<a class="pay-opt" href="<?php echo esc_url( $wd_pay_url( 'full', $wd_want ) ); ?>"><b><?php esc_html_e( 'Pay in full', 'wwmd' ); ?></b><span><?php echo esc_html( wd_money( wd_plan_left( $wd_id, $wd_want ) ) ); ?></span><small><?php esc_html_e( 'Card, transfer or USSD', 'wwmd' ); ?></small></a>
			<?php if ( $wd_inst > 1 && wd_plan_instalment( $wd_id, $wd_want ) < wd_plan_left( $wd_id, $wd_want ) ) : ?>
				<a class="pay-opt" href="<?php echo esc_url( $wd_pay_url( 'part', $wd_want ) ); ?>"><b><?php echo esc_html( sprintf( /* translators: %d */ __( 'Pay in up to %d instalments', 'wwmd' ), $wd_inst ) ); ?></b><span><?php echo esc_html( sprintf( /* translators: %s */ __( '%s today', 'wwmd' ), wd_money( wd_plan_instalment( $wd_id, $wd_want ) ) ) ); ?></span><small><?php echo esc_html( sprintf( /* translators: %s plan */ __( '%s switches on today', 'wwmd' ), wd_plan_name( $wd_want ) ) ); ?></small></a>
			<?php endif; ?>
			<a class="pay-opt" href="<?php echo esc_url( wd_whatsapp( sprintf( /* translators: 1 plan 2 url */ __( 'Hello! I would like to move my wedding page to %1$s: %2$s', 'wwmd' ), wd_plan_name( $wd_want ), get_permalink( $wd_id ) ) ) ); ?>" target="_blank" rel="noopener"><b><?php esc_html_e( 'Pay on WhatsApp', 'wwmd' ); ?></b><span><?php echo esc_html( wd_whatsapp_display() ); ?></span><small><?php esc_html_e( 'Bank transfer, we switch it on', 'wwmd' ); ?></small></a>
		</div>
		<?php if ( wd_pay_test_mode() ) : ?><p class="fine"><?php esc_html_e( 'Test mode: no real money moves.', 'wwmd' ); ?></p><?php endif; ?>
	</div>
<?php endif; ?>
