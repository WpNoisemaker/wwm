<?php
/**
 * Template Name: Rankings
 *
 * The Buzz Chart, Billboard style: this week's top 50 with movement, last week, peak & weeks on chart.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
get_header();
$wd_period = isset( $_GET['period'] ) && 'all' === $_GET['period'] ? 'all' : 'week'; // phpcs:ignore WordPress.Security.NonceVerification
?>
<section class="page-hero page-hero--chart">
	<div class="wrap">
		<p class="eyebrow"><?php echo wd_icon( 'trophy', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'The Buzz Chart', 'wwmd' ); ?></p>
		<h1><?php echo 'all' === $wd_period ? esc_html__( 'The all-time chart', 'wwmd' ) : esc_html__( 'This week’s top 50 weddings', 'wwmd' ); ?></h1>
		<p class="lead"><?php echo esc_html( sprintf( /* translators: %s date */ __( 'Week of %s. The weddings guests are visiting, liking, spraying & writing about the most. A new chart every Monday, with live moves every hour.', 'wwmd' ), wp_date( 'j F Y', strtotime( 'monday this week' ) ) ) ); ?></p>
		<nav class="tabs" aria-label="<?php esc_attr_e( 'Chart', 'wwmd' ); ?>">
			<a class="tab<?php echo 'week' === $wd_period ? ' is-on' : ''; ?>" href="<?php echo esc_url( remove_query_arg( 'period' ) ); ?>"<?php echo 'week' === $wd_period ? ' aria-current="page"' : ''; ?>><?php echo wd_icon( 'fire', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'This week', 'wwmd' ); ?></a>
			<a class="tab<?php echo 'all' === $wd_period ? ' is-on' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'period', 'all' ) ); ?>"<?php echo 'all' === $wd_period ? ' aria-current="page"' : ''; ?>><?php echo wd_icon( 'trophy', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'All time', 'wwmd' ); ?></a>
		</nav>
	</div>
</section>
<section class="section section--tight">
	<div class="wrap rank-layout">
		<div class="billboard billboard--page">
			<?php get_template_part( 'template-parts/chart', null, array( 'n' => 50, 'full' => true, 'period' => $wd_period ) ); ?>
		</div>
		<aside class="rank-how">
			<h2><?php esc_html_e( 'How the chart works', 'wwmd' ); ?></h2>
			<p><?php esc_html_e( 'Every wedding earns Buzz coins from what guests do on its page & on Instagram. The more coins a wedding earns in the last 7 days, the higher it sits on this week’s chart.', 'wwmd' ); ?></p>
			<?php
			$wd_pl = array_values( array_diff( wd_plan_keys(), array( 'society' ) ) );
			$wd_pf = array();
			foreach ( $wd_pl as $wd_p ) {
				$wd_pf[ $wd_p ] = wd_plan_figures( $wd_p );
			}
			?>
			<div class="coin-table" role="table" aria-label="<?php esc_attr_e( 'Coins for each figure & the plans that earn them', 'wwmd' ); ?>">
				<div class="coin-table__row coin-table__row--head" role="row">
					<span role="columnheader"><?php esc_html_e( 'Guests’ activity', 'wwmd' ); ?></span>
					<span role="columnheader"><?php esc_html_e( 'Coins', 'wwmd' ); ?></span>
					<?php foreach ( $wd_pl as $wd_p ) : ?><span role="columnheader"><?php echo esc_html( wd_plan_name( $wd_p ) ); ?></span><?php endforeach; ?>
				</div>
				<?php foreach ( wd_coin_figures() as $wd_k => $wd_s ) : ?>
					<?php if ( wd_coin_rate( $wd_k ) < 1 ) { continue; } ?>
					<div class="coin-table__row" role="row">
						<span role="rowheader"><?php echo wd_icon( $wd_s['icon'], 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $wd_s['label'] ); ?></span>
						<span role="cell"><b><?php echo esc_html( number_format_i18n( wd_coin_rate( $wd_k ) ) ); ?></b> <small><?php echo esc_html( $wd_s['per'] ); ?></small></span>
						<?php foreach ( $wd_pl as $wd_p ) : ?>
							<?php $wd_ok = in_array( $wd_k, $wd_pf[ $wd_p ], true ); ?>
							<span role="cell" class="<?php echo $wd_ok ? 'is-yes' : 'is-no'; ?>"><?php echo wd_icon( $wd_ok ? 'check' : 'lock', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span class="screen-reader-text"><?php echo $wd_ok ? esc_html__( 'Earns coins', 'wwmd' ) : esc_html__( 'Does not earn coins', 'wwmd' ); ?></span></span>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
			</div>
			<p class="hint"><?php esc_html_e( 'A figure only earns coins when the page’s plan has the feature behind it. High Society earns everything Premium does. So a Free or Pro page can reach the top, but it needs a lot more guest activity to get there.', 'wwmd' ); ?></p>
			<dl class="legend">
				<div><dt><?php echo wd_chart_move( array( 'move' => 'up', 'delta' => 3 ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></dt><dd><?php esc_html_e( 'Places up since last week', 'wwmd' ); ?></dd></div>
				<div><dt><?php echo wd_chart_move( array( 'move' => 'down', 'delta' => 2 ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></dt><dd><?php esc_html_e( 'Places down since last week', 'wwmd' ); ?></dd></div>
				<div><dt><?php echo wd_chart_move( array( 'move' => 'new', 'delta' => 0 ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></dt><dd><?php esc_html_e( 'First week on the chart', 'wwmd' ); ?></dd></div>
				<div><dt><?php echo wd_chart_move( array( 'move' => 're', 'delta' => 0 ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></dt><dd><?php esc_html_e( 'Back on the chart after a break', 'wwmd' ); ?></dd></div>
			</dl>
			<p class="fine"><?php esc_html_e( 'This week’s chart counts the last 7 days. Private pages are not ranked. Sponsored spots are always labelled & never change a wedding’s place. We remove weddings that fake visits, likes or payments.', 'wwmd' ); ?></p>
			<a class="btn btn--ink btn--block" href="<?php echo esc_url( wd_url( 'sign-in', array( 'tab' => 'join' ) ) ); ?>"><?php esc_html_e( 'Get your wedding on the chart', 'wwmd' ); ?></a>
			<a class="link-arrow" href="<?php echo esc_url( wd_url( 'pricing' ) ); ?>"><?php esc_html_e( 'Compare the plans', 'wwmd' ); ?><?php echo wd_icon( 'arrow', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		</aside>
	</div>
</section>
<?php
get_footer();
