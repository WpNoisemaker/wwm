<?php
/**
 * Template Name: Pricing
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
get_header();
$wd_svc = wd_services();
?>
<section class="page-hero page-hero--pricing">
	<div class="wrap center">
		<p class="eyebrow"><?php esc_html_e( 'Pricing', 'wwmd' ); ?></p>
		<h1><?php esc_html_e( 'Start free. Go big when you are ready', 'wwmd' ); ?></h1>
		<p class="lead"><?php esc_html_e( 'No monthly fees. Pro & Premium are paid once per wedding in naira, in one go or in instalments, & moving up only costs the difference. High Society is priced for your wedding.', 'wwmd' ); ?></p>
	</div>
</section>
<section class="section section--tight">
	<div class="wrap">
		<?php get_template_part( 'template-parts/plans' ); ?>
	</div>
</section>
<section class="section">
	<div class="wrap">
		<div class="section-head"><h2><?php esc_html_e( 'Fees, in plain words', 'wwmd' ); ?></h2></div>
		<?php
		$wd_pk  = wd_plan_keys();
		$wd_fee = static function ( $plan, $kind ) {
			$r = wd_fee_rate_for( $plan, $kind );
			return $r > 0 ? sprintf( /* translators: %s percent */ __( '%s%% service fee. Guests can cover it.', 'wwmd' ), number_format_i18n( $r, 1 ) ) : __( 'No fee', 'wwmd' );
		};
		$wd_rows = array(
			__( 'Cash gifts & registry funds', 'wwmd' ) => array_map( static function ( $p ) use ( $wd_fee ) { return $wd_fee( $p, 'gift' ); }, $wd_pk ),
			__( 'Spraying', 'wwmd' )                     => array_map( static function ( $p ) use ( $wd_fee ) { return in_array( 'spray', (array) wd_opt( 'features_' . $p ), true ) ? $wd_fee( $p, 'spray' ) : sprintf( /* translators: %s plan */ __( 'From %s', 'wwmd' ), wd_plan_name( wd_feature_plan( 'spray' ) ) ); }, $wd_pk ),
			__( 'Withdrawals', 'wwmd' )                  => array_fill( 0, count( $wd_pk ), __( 'Free to Nigerian banks', 'wwmd' ) ),
			__( 'Sponsored slots on your page', 'wwmd' ) => array_map( static function ( $p ) { return in_array( 'no_ads', (array) wd_opt( 'features_' . $p ), true ) ? __( 'None', 'wwmd' ) : __( 'Two', 'wwmd' ); }, $wd_pk ),
			__( 'Printed table QR cards', 'wwmd' )       => array_map( static function ( $p ) use ( $wd_svc ) { return in_array( 'table_qr', (array) wd_opt( 'features_' . $p ), true ) ? sprintf( /* translators: %d cards */ __( '%d included, shipped', 'wwmd' ), (int) wd_opt( 'premium_qr_cards' ) ) : $wd_svc['qr']['from']; }, $wd_pk ),
			__( 'Verified badge', 'wwmd' )               => array( __( 'No', 'wwmd' ), __( 'Yes', 'wwmd' ), __( 'Yes', 'wwmd' ), __( 'Yes, in gold', 'wwmd' ) ),
			__( 'Buzz Chart coins from', 'wwmd' )        => array_map( static function ( $p ) { /* translators: %d figures */ return sprintf( __( '%d kinds of guest activity', 'wwmd' ), count( wd_plan_figures( 'society' === $p ? 'premium' : $p ) ) ); }, $wd_pk ),
		);
		?>
		<div class="fee-table fee-table--4" role="table" aria-label="<?php esc_attr_e( 'Fees on each plan', 'wwmd' ); ?>">
			<div class="fee-row fee-row--head" role="row"><span role="columnheader"></span><?php foreach ( $wd_pk as $wd_p ) : ?><span role="columnheader"><?php echo esc_html( wd_plan_name( $wd_p ) ); ?></span><?php endforeach; ?></div>
			<?php foreach ( $wd_rows as $wd_label => $wd_cells ) : ?>
				<div class="fee-row" role="row"><span role="rowheader"><?php echo esc_html( $wd_label ); ?></span><?php foreach ( $wd_cells as $wd_i => $wd_c ) : ?><span role="cell" data-plan="<?php echo esc_attr( wd_plan_name( $wd_pk[ $wd_i ] ) ); ?>"><?php echo esc_html( $wd_c ); ?></span><?php endforeach; ?></div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<section class="section section--alt">
	<div class="wrap">
		<div class="section-head"><div><p class="eyebrow"><?php esc_html_e( 'Add-ons', 'wwmd' ); ?></p><h2><?php esc_html_e( 'Extras for any page', 'wwmd' ); ?></h2></div><a class="link-arrow" href="<?php echo esc_url( wd_url( 'services' ) ); ?>"><?php esc_html_e( 'All services', 'wwmd' ); ?><?php echo wd_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></div>
		<div class="addon-grid">
			<div class="addon tone-butter"><h3><?php esc_html_e( 'Homepage spotlight', 'wwmd' ); ?></h3><p><?php esc_html_e( 'Your wedding featured right under the Buzz Chart on the homepage for a week, labelled Sponsored. It never changes your chart position.', 'wwmd' ); ?></p><b><?php echo esc_html( sprintf( /* translators: %s price */ __( '%s a week', 'wwmd' ), wd_money( wd_opt( 'spotlight_price' ) ) ) ); ?></b></div>
			<div class="addon tone-coral"><h3><?php esc_html_e( 'Custom page design', 'wwmd' ); ?></h3><p><?php esc_html_e( 'For Premium pages: our designers make a page just for you, matched to your invitation card, colours & culture. Included with High Society.', 'wwmd' ); ?></p><b><?php echo esc_html( wd_money( wd_opt( 'custom_design_price' ) ) ); ?></b></div>
			<div class="addon tone-mint"><h3><?php echo esc_html( $wd_svc['qr']['title'] ); ?></h3><p><?php echo esc_html( $wd_svc['qr']['text'] ); ?></p><b><?php echo esc_html( $wd_svc['qr']['from'] ); ?></b></div>
			<div class="addon tone-lavender"><h3><?php echo esc_html( $wd_svc['livestream']['title'] ); ?></h3><p><?php echo esc_html( $wd_svc['livestream']['text'] ); ?></p><b><?php echo esc_html( $wd_svc['livestream']['from'] ); ?></b></div>
			<div class="addon tone-sky"><h3><?php esc_html_e( 'For vendors', 'wwmd' ); ?></h3><p><?php echo esc_html( sprintf( /* translators: 1 verified price 2 ad price */ __( 'Verified badge, replies to reviews & WhatsApp leads for %1$s a month. Sponsored slots on free wedding pages in your city from %2$s a week.', 'wwmd' ), wd_money( wd_opt( 'vendor_pro_price' ) ), wd_money( wd_opt( 'ad_week_price' ) ) ) ); ?></p><b><?php esc_html_e( 'From', 'wwmd' ); ?> <?php echo esc_html( wd_money( wd_opt( 'vendor_pro_price' ) ) ); ?></b></div>
		</div>
	</div>
</section>
<?php get_template_part( 'template-parts/faq' ); ?>
<?php
get_footer();
