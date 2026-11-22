<?php
/**
 * Upsell box: names the lowest plan with the feature.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_plan = wd_feature_plan( $args['feature'] ?? 'design' );
$wd_ps   = wd_plans();
?>
<div class="panel upsell">
	<span class="upsell__art" aria-hidden="true"><?php echo wd_art( 'rings' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
	<div>
		<p class="eyebrow"><?php echo wd_icon( 'crown', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( wd_plan_name( $wd_plan ) ); ?></p>
		<h2><?php echo esc_html( $args['what'] ); ?></h2>
		<p><?php echo esc_html( $wd_ps[ $wd_plan ]['pitch'] ); ?> <?php echo null === $wd_ps[ $wd_plan ]['price'] ? '' : esc_html( sprintf( /* translators: 1 plan 2 price */ __( '%1$s is %2$s once per wedding.', 'wwmd' ), wd_plan_name( $wd_plan ), wd_money( (int) $wd_ps[ $wd_plan ]['price'] ) ) ); ?></p>
		<a class="btn btn--marigold" href="<?php echo esc_url( add_query_arg( array( 'tab' => 'plan', 'wid' => $args['id'], 'tier' => $wd_plan ), wd_url( 'account' ) ) . '#pay' ); ?>"><?php echo esc_html( sprintf( /* translators: %s plan */ __( 'Go %s', 'wwmd' ), wd_plan_name( $wd_plan ) ) ); ?></a>
	</div>
</div>
