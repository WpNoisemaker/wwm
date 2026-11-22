<?php
/**
 * The four plan cards: Free, Pro, Premium & High Society (features follow HQ → Settings).
 *
 * Args (in the account): id (the couple's wedding) & want (the plan being paid for).
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_id   = isset( $args['id'] ) ? (int) $args['id'] : 0;
$wd_want = isset( $args['want'] ) ? (string) $args['want'] : '';
$wd_now  = $wd_id ? wd_plan( $wd_id ) : '';
$wd_ps   = wd_plans();
$wd_figs = count( array_filter( array_keys( wd_coin_figures() ), static function ( $k ) { return wd_coin_rate( $k ) > 0; } ) );
$wd_join = is_user_logged_in() ? wd_account_url( 'new' ) : wd_url( 'sign-in', array( 'tab' => 'join' ) );
$wd_up   = static function ( $plan ) use ( $wd_id ) {
	if ( $wd_id ) {
		return add_query_arg( array( 'tab' => 'plan', 'wid' => $wd_id, 'tier' => $plan ), wd_url( 'account' ) ) . '#pay';
	}
	$new = wd_account_url( 'new', array( 'plan' => $plan ) );
	return is_user_logged_in() ? $new : wd_url( 'sign-in', array( 'tab' => 'join', 'redirect_to' => rawurlencode( $new ) ) );
};
$wd_free = (array) wd_opt( 'features_free' );
?>
<div class="plans-box"><div class="plans plans--4">
	<?php foreach ( $wd_ps as $wd_k => $wd_p ) : ?>
		<?php
		$wd_rank  = wd_plan_rank( $wd_k );
		$wd_mine  = $wd_now === $wd_k;
		$wd_below = $wd_now && wd_plan_rank( $wd_now ) > $wd_rank;
		$wd_earn  = count( wd_plan_figures( 'society' === $wd_k ? 'premium' : $wd_k ) );
		?>
		<div class="plan plan--<?php echo esc_attr( $wd_k ); ?><?php echo $wd_mine ? ' is-mine' : ''; ?><?php echo $wd_want === $wd_k ? ' is-picked' : ''; ?>">
			<?php if ( 'premium' === $wd_k ) : ?><span class="plan__ribbon"><?php echo wd_icon( 'crown', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Full owambe', 'wwmd' ); ?></span><?php endif; ?>
			<?php if ( 'society' === $wd_k ) : ?><span class="plan__ribbon plan__ribbon--gold"><?php echo wd_icon( 'gem', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Beyond the website', 'wwmd' ); ?></span><?php endif; ?>
			<div class="plan__head">
				<span class="plan__name"><?php echo esc_html( $wd_p['name'] ); ?></span>
				<span class="plan__price"><?php echo null === $wd_p['price'] ? esc_html__( 'By quote', 'wwmd' ) : esc_html( wd_money( (int) $wd_p['price'] ) ); ?></span>
				<span class="plan__note">
					<?php
					if ( 'free' === $wd_k ) {
						esc_html_e( 'In our colours. Free forever.', 'wwmd' );
					} elseif ( 'society' === $wd_k ) {
						esc_html_e( 'Priced for your wedding, on WhatsApp.', 'wwmd' );
					} elseif ( 'premium' === $wd_k ) {
						echo esc_html( sprintf( /* translators: 1: cards 2: instalments */ __( 'Once per wedding, or %2$d instalments. Includes %1$d printed table QR cards, shipped to you.', 'wwmd' ), (int) wd_opt( 'premium_qr_cards' ), (int) wd_opt( 'instalments' ) ) );
					} else {
						echo esc_html( sprintf( /* translators: %d instalments */ __( 'Once per wedding, or %d instalments.', 'wwmd' ), (int) wd_opt( 'instalments' ) ) );
					}
					?>
				</span>
			</div>
			<p class="plan__pitch"><?php echo esc_html( $wd_p['pitch'] ); ?></p>
			<ul class="plan__list">
				<?php if ( 'free' === $wd_k ) : ?>
					<?php foreach ( wd_features() as $wd_f => $wd_label ) : ?>
						<?php if ( in_array( $wd_f, array( 'no_ads', 'no_fees', 'templates', 'table_qr' ), true ) ) { continue; } ?>
						<?php $wd_has = in_array( $wd_f, $wd_free, true ); ?>
						<li class="<?php echo $wd_has ? 'yes' : 'no'; ?>"><?php echo wd_icon( $wd_has ? 'check' : 'close', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( 'rsvp' === $wd_f && ! in_array( 'entry_pass', $wd_free, true ) ? __( 'RSVP (without entry pass)', 'wwmd' ) : $wd_label ); ?></li>
					<?php endforeach; ?>
					<?php if ( wd_fee_rate_for( 'free', 'gift' ) > 0 ) : ?><li class="note"><?php echo wd_icon( 'cash', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( sprintf( /* translators: %s percent */ __( '%s%% service fee on cash gifts, which guests can choose to cover', 'wwmd' ), number_format_i18n( wd_fee_rate_for( 'free', 'gift' ), 1 ) ) ); ?></li><?php endif; ?>
					<?php if ( ! in_array( 'no_ads', $wd_free, true ) ) : ?><li class="note"><?php echo wd_icon( 'bell', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Two small sponsored slots on your page', 'wwmd' ); ?></li><?php endif; ?>
				<?php else : ?>
					<li class="yes strong"><?php echo wd_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( sprintf( /* translators: %s plan */ __( 'Everything in %s, plus:', 'wwmd' ), wd_plan_name( wd_plan_keys()[ $wd_rank - 1 ] ) ) ); ?></li>
					<?php foreach ( wd_plan_adds( $wd_k ) as $wd_f => $wd_label ) : ?>
						<li class="yes"><?php echo wd_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( 'table_qr' === $wd_f ? sprintf( /* translators: %d cards */ __( '%d printed table QR cards, shipped to you', 'wwmd' ), (int) wd_opt( 'premium_qr_cards' ) ) : $wd_label ); ?></li>
					<?php endforeach; ?>
					<?php if ( 'society' === $wd_k ) : ?>
						<?php foreach ( wd_society_perks() as $wd_perk ) : ?><li class="yes"><?php echo wd_icon( 'gem', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $wd_perk ); ?></li><?php endforeach; ?>
					<?php endif; ?>
					<?php if ( 'pro' === $wd_k ) : ?>
						<li class="yes"><?php echo wd_icon( 'verified', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Verified badge on your page & in every listing', 'wwmd' ); ?></li>
						<?php if ( wd_fee_rate_for( 'pro', 'gift' ) > 0 ) : ?><li class="note"><?php echo wd_icon( 'cash', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( sprintf( /* translators: %s percent */ __( '%s%% service fee on cash gifts, which guests can choose to cover', 'wwmd' ), number_format_i18n( wd_fee_rate_for( 'pro', 'gift' ), 1 ) ) ); ?></li><?php endif; ?>
					<?php endif; ?>
				<?php endif; ?>
			</ul>
			<p class="plan__coins"><?php echo wd_icon( 'coin', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( sprintf( /* translators: 1: figures this plan earns from 2: all figures */ __( 'Earns Buzz Chart coins from %1$d of %2$d things guests do', 'wwmd' ), $wd_earn, $wd_figs ) ); ?></span></p>
			<?php if ( $wd_mine ) : ?>
				<span class="btn btn--ghost btn--block is-current" aria-current="true"><?php echo wd_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Your plan', 'wwmd' ); ?></span>
			<?php elseif ( $wd_below ) : ?>
				<span class="btn btn--ghost btn--block is-off"><?php esc_html_e( 'Included in your plan', 'wwmd' ); ?></span>
			<?php elseif ( 'free' === $wd_k ) : ?>
				<a class="btn btn--ink btn--block" href="<?php echo esc_url( $wd_join ); ?>"><?php esc_html_e( 'Start free', 'wwmd' ); ?></a>
			<?php elseif ( 'society' === $wd_k ) : ?>
				<a class="btn btn--gold btn--block" href="<?php echo esc_url( wd_society_quote_url( $wd_id ) ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'whatsapp', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Get a quote', 'wwmd' ); ?></a>
			<?php else : ?>
				<a class="btn <?php echo 'premium' === $wd_k ? 'btn--marigold' : 'btn--ink'; ?> btn--block" href="<?php echo esc_url( $wd_up( $wd_k ) ); ?>"><?php echo esc_html( $wd_now && 'free' !== $wd_now ? sprintf( /* translators: %s plan */ __( 'Move up to %s', 'wwmd' ), $wd_p['name'] ) : sprintf( /* translators: %s plan */ __( 'Go %s', 'wwmd' ), $wd_p['name'] ) ); ?></a>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div></div>
