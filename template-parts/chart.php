<?php
/**
 * The Buzz Chart, Billboard style. Used on the homepage (top 10) & the rankings page (top 50).
 *
 * Args: n (rows), full (rankings page: signals & chart history), period (week|all).
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_n      = isset( $args['n'] ) ? (int) $args['n'] : 10;
$wd_full   = ! empty( $args['full'] );
$wd_period = isset( $args['period'] ) && 'all' === $args['period'] ? 'all' : 'week';
if ( 'all' === $wd_period ) {
	$wd_rows = array();
	foreach ( wd_top( 'all', $wd_n ) as $wd_i => $wd_id ) {
		$wd_rows[ $wd_id ]         = wd_chart_line( $wd_id, wd_rank( $wd_id, 'week' ) );
		$wd_rows[ $wd_id ]['pos']  = $wd_i + 1;
		$wd_rows[ $wd_id ]['award'] = '';
	}
} else {
	$wd_rows = wd_chart( $wd_n );
	foreach ( $wd_rows as $wd_id => $wd_l ) {
		$wd_rows[ $wd_id ]['pos'] = $wd_l['rank'];
	}
}
if ( ! $wd_rows ) {
	echo '<p class="empty">' . esc_html__( 'The chart fills up as guests visit, like & spray. Check back soon.', 'wwmd' ) . '</p>';
	return;
}
$wd_awards = array(
	'debut' => __( 'Hot debut', 'wwmd' ),
	'gain'  => __( 'Biggest gain', 'wwmd' ),
);
$wd_first = array_key_first( $wd_rows );
$wd_one   = $wd_rows[ $wd_first ];
$wd_d1    = wd_get( $wd_first );
$wd_style = static function ( $id ) {
	$t = get_the_terms( $id, 'wd_style' );
	return ( $t && ! is_wp_error( $t ) ) ? $t[0]->name : '';
};
/**
 * Stats: last week, peak & weeks on chart (or all-time coins on the all-time chart).
 */
$wd_stats = static function ( $id, $l ) use ( $wd_period ) {
	echo '<dl class="stats">';
	if ( 'all' === $wd_period ) {
		printf( '<div><dt>%s</dt><dd>%s</dd></div>', esc_html__( 'Coins', 'wwmd' ), esc_html( wd_short( wd_coin_total( $id, 'all' ) ) ) );
	} else {
		printf( '<div><dt>%s</dt><dd>%s</dd></div>', esc_html__( 'Last week', 'wwmd' ), $l['last'] ? (int) $l['last'] : '–' );
	}
	printf( '<div><dt>%s</dt><dd>%s</dd></div>', esc_html__( 'Peak', 'wwmd' ), $l['peak'] ? (int) $l['peak'] : '–' );
	printf( '<div><dt>%s</dt><dd>%d</dd></div>', esc_html__( 'Weeks on chart', 'wwmd' ), (int) $l['weeks'] );
	echo '</dl>';
};
$wd_coin = static function ( $id ) use ( $wd_period ) {
	$n = wd_coin_total( $id, $wd_period );
	/* translators: %s coins */
	return '<span class="coins" title="' . esc_attr( 'week' === $wd_period ? __( 'Buzz coins in the last 7 days', 'wwmd' ) : __( 'Buzz coins, all time', 'wwmd' ) ) . '">' . wd_icon( 'coin', 14 ) . esc_html( sprintf( __( '%s coins', 'wwmd' ), wd_short( $n ) ) ) . '</span>';
};
?>
<div class="chart<?php echo $wd_full ? ' chart--full' : ''; ?>">
	<article class="chart-one">
		<a class="chart-one__pic" href="<?php echo esc_url( get_permalink( $wd_first ) ); ?>" tabindex="-1" aria-hidden="true"><?php echo wd_cover( $wd_first, $wd_d1, 'large' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		<div class="chart-one__body">
			<p class="chart-one__no"><span><?php esc_html_e( 'No.', 'wwmd' ); ?></span>1</p>
			<?php if ( 'week' === $wd_period ) : ?><p class="chart-one__move"><?php echo wd_chart_move( $wd_one ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php if ( $wd_one['at1'] > 1 ) : ?><span class="chart-one__weeks"><?php echo esc_html( sprintf( /* translators: %d weeks */ __( '%d weeks at No. 1', 'wwmd' ), $wd_one['at1'] ) ); ?></span><?php endif; ?></p><?php endif; ?>
			<h3 class="chart-one__name"><a href="<?php echo esc_url( get_permalink( $wd_first ) ); ?>"><?php echo esc_html( wd_names( $wd_d1 ) ); ?></a><?php echo wd_vbadge( $wd_first, 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h3>
			<p class="chart-one__meta"><?php echo esc_html( implode( ' · ', array_filter( array( trim( $wd_d1['city'] . ', ' . $wd_d1['country'], ', ' ), $wd_style( $wd_first ), wd_date( $wd_d1['date'], 'j M Y' ) ) ) ) ); ?><?php echo $wd_d1['hashtag'] ? ' · <b>#' . esc_html( $wd_d1['hashtag'] ) . '</b>' : ''; ?> <?php echo $wd_coin( $wd_first ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<?php $wd_stats( $wd_first, $wd_one ); ?>
			<a class="btn btn--marigold btn--sm" href="<?php echo esc_url( get_permalink( $wd_first ) ); ?>"><?php esc_html_e( 'Visit the page', 'wwmd' ); ?><?php echo wd_icon( 'arrow', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		</div>
	</article>
	<?php if ( count( $wd_rows ) > 1 ) : ?>
	<ol class="chart-list" start="2">
		<?php
		foreach ( array_slice( $wd_rows, 1, null, true ) as $wd_id => $wd_l ) :
			$wd_d = wd_get( $wd_id );
			?>
			<li class="chart-row">
				<span class="chart-row__rank"><?php echo (int) $wd_l['pos']; ?></span>
				<span class="chart-row__move"><?php echo 'week' === $wd_period ? wd_chart_move( $wd_l ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				<a class="chart-row__pic" href="<?php echo esc_url( get_permalink( $wd_id ) ); ?>" tabindex="-1" aria-hidden="true"><?php echo wd_cover( $wd_id, $wd_d, 'wd-square' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
				<div class="chart-row__main">
					<span class="chart-row__title"><a class="chart-row__name" href="<?php echo esc_url( get_permalink( $wd_id ) ); ?>"><?php echo esc_html( wd_names( $wd_d ) ); ?></a><?php echo wd_vbadge( $wd_id, 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<span class="chart-row__meta"><?php echo esc_html( implode( ' · ', array_filter( array( $wd_d['city'], $wd_style( $wd_id ), wd_date( $wd_d['date'], 'j M Y' ) ) ) ) ); ?> <?php echo $wd_coin( $wd_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<?php if ( $wd_l['award'] ) : ?><span class="award award--<?php echo esc_attr( $wd_l['award'] ); ?>"><?php echo wd_icon( 'debut' === $wd_l['award'] ? 'fire' : 'trend', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $wd_awards[ $wd_l['award'] ] ); ?></span><?php endif; ?>
				</div>
				<?php $wd_stats( $wd_id, $wd_l ); ?>
				<?php if ( $wd_full ) : ?>
					<details class="chart-row__more">
						<summary><span class="sr-only"><?php esc_html_e( 'More about this wedding’s chart run', 'wwmd' ); ?></span><?php echo wd_icon( 'plus', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></summary>
						<div class="chart-row__detail">
							<?php echo wd_chart_spark( $wd_l ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<ul class="sig">
								<?php
								$wd_m = (array) get_post_meta( $wd_id, 'week' === $wd_period ? '_wd_m_week' : '_wd_m', true );
								$wd_c = wd_coins( $wd_id, $wd_m );
								foreach ( wd_coin_figures() as $wd_k => $wd_s ) :
									$wd_r = $wd_c['rows'][ $wd_k ];
									if ( $wd_r['locked'] || wd_coin_rate( $wd_k ) < 1 ) {
										continue;
									}
									$wd_hide = 'spray' === $wd_k && empty( $wd_d['spray']['show_total'] );
									if ( in_array( $wd_k, array( 'spray', 'gifts' ), true ) ) {
										$wd_v = $wd_hide ? __( 'hidden', 'wwmd' ) : '₦' . wd_short( $wd_r['n'] );
									} else {
										$wd_v = wd_short( $wd_r['n'] );
									}
									?>
									<li title="<?php echo esc_attr( $wd_s['label'] ); ?>"><?php echo wd_icon( $wd_s['icon'], 15 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><b><?php echo esc_html( $wd_v ); ?></b><span><?php echo esc_html( $wd_s['short'] ); ?></span><?php if ( ! $wd_hide ) : ?><em><?php echo esc_html( '+' . wd_short( $wd_r['coins'] ) ); ?></em><?php endif; ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					</details>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
	<?php endif; ?>
</div>
