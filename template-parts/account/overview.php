<?php
/**
 * Account overview: link, numbers, rank & set-up checklist.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_id  = $args['id'];
$wd_d   = $args['d'];
$wd_m   = (array) get_post_meta( $wd_id, '_wd_m', true );
$wd_r   = wd_rsvp_counts( $wd_id );
$wd_t   = wd_money_totals( $wd_id );
$wd_url = get_permalink( $wd_id );
$wd_cur = $wd_d['currency'];
$wd_steps = array(
	array( (bool) $wd_d['cover'], __( 'Add a cover photo', 'wwmd' ), 'basics' ),
	array( (bool) $wd_d['events'], __( 'Add your events', 'wwmd' ), 'events' ),
	array( (bool) $wd_d['story'], __( 'Tell your love story', 'wwmd' ), 'story' ),
	array( (bool) $wd_d['party'], __( 'Introduce your squad', 'wwmd' ), 'party' ),
	array( (bool) $wd_d['quiz'], __( 'Write the couple quiz', 'wwmd' ), 'quiz' ),
	array( (bool) $wd_d['registry'], __( 'Set up your registry', 'wwmd' ), 'registry' ),
	array( (bool) $wd_d['vendors'], __( 'List your vendors', 'wwmd' ), 'vendors' ),
	array( (bool) ( $wd_d['ig']['handle'] || $wd_d['ig']['posts'] ), __( 'Add your Instagram', 'wwmd' ), 'instagram' ),
);
// Only the steps for features on this page's plan.
$wd_need  = array( 'story' => 'story', 'party' => 'party', 'quiz' => 'quiz', 'registry' => 'registry', 'vendors' => 'vendors', 'instagram' => 'instagram' );
$wd_steps = array_values( array_filter( $wd_steps, static function ( $s ) use ( $wd_id, $wd_need ) { return empty( $wd_need[ $s[2] ] ) || wd_can( $wd_id, $wd_need[ $s[2] ] ); } ) );
$wd_done  = count( array_filter( wp_list_pluck( $wd_steps, 0 ) ) );
$wd_plan  = wd_plan( $wd_id );
$wd_week  = wd_coins( $wd_id, (array) get_post_meta( $wd_id, '_wd_m_week', true ) );
$wd_figs  = wd_coin_figures();
$wd_rank  = wd_rank( $wd_id, 'week' );
?>
<div class="app-head">
	<div>
		<p class="eyebrow"><?php echo esc_html( sprintf( /* translators: %s plan */ __( '%s page', 'wwmd' ), wd_plan_name( $wd_plan ) ) ); ?></p>
		<h1><?php echo esc_html( wd_names( $wd_d ) ); ?></h1>
		<p class="muted"><?php echo esc_html( wd_date( $wd_d['date'], 'l j F Y' ) . ' · ' . $wd_d['city'] ); ?></p>
	</div>
	<a class="btn btn--ink" href="<?php echo esc_url( $wd_url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View my page', 'wwmd' ); ?></a>
</div>
<div class="panel share-panel">
	<div class="share-panel__qr" data-qr="<?php echo esc_url( $wd_url ); ?>" data-qr-size="5"></div>
	<div>
		<h2><?php esc_html_e( 'Share your link', 'wwmd' ); ?></h2>
		<p class="muted"><?php esc_html_e( 'Put it in your Instagram bio, your IV & your family WhatsApp group.', 'wwmd' ); ?></p>
		<div class="copy-field"><input type="text" readonly value="<?php echo esc_url( $wd_url ); ?>" aria-label="<?php esc_attr_e( 'Your page link', 'wwmd' ); ?>"><button class="btn btn--ink btn--sm" type="button" data-copy="<?php echo esc_url( $wd_url ); ?>"><?php esc_html_e( 'Copy', 'wwmd' ); ?></button></div>
		<div class="btn-row">
			<a class="btn btn--ghost btn--sm" href="<?php echo esc_url( 'https://wa.me/?text=' . rawurlencode( __( 'We are getting married! Our wedding page:', 'wwmd' ) . ' ' . $wd_url ) ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'whatsapp', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>WhatsApp</a>
			<button class="btn btn--ghost btn--sm" type="button" data-qr-download="<?php echo esc_attr( sanitize_title( wd_names( $wd_d ) ) ); ?>"><?php echo wd_icon( 'download', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Download QR code', 'wwmd' ); ?></button>
		</div>
	</div>
</div>
<div class="stat-grid">
	<div class="stat tone-butter"><span><?php echo wd_icon( 'coin', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Buzz coins this week', 'wwmd' ); ?></span><b><?php echo esc_html( number_format_i18n( $wd_week['total'] ) ); ?></b><small><?php echo $wd_rank ? esc_html( sprintf( /* translators: %d rank */ __( 'No. %d on this week’s chart', 'wwmd' ), $wd_rank ) ) : esc_html__( 'Not ranked yet', 'wwmd' ); ?></small></div>
	<div class="stat tone-lavender"><span><?php echo wd_icon( 'eye', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Page visits', 'wwmd' ); ?></span><b><?php echo esc_html( number_format_i18n( (int) ( $wd_m['views'] ?? 0 ) ) ); ?></b></div>
	<div class="stat tone-mint"><span><?php echo wd_icon( 'users', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Coming', 'wwmd' ); ?></span><b><?php echo esc_html( number_format_i18n( $wd_r['guests'] ) ); ?></b><small><?php echo esc_html( sprintf( /* translators: %d */ __( '%1$d RSVPs · %2$d can’t make it', 'wwmd' ), $wd_r['yes'], $wd_r['no'] ) ); ?></small></div>
	<div class="stat tone-coral"><span><?php echo wd_icon( 'heart', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Love notes', 'wwmd' ); ?></span><b><?php echo esc_html( number_format_i18n( (int) ( $wd_m['wall'] ?? 0 ) ) ); ?></b><small><?php echo esc_html( sprintf( /* translators: %d likes */ __( '%d likes', 'wwmd' ), (int) ( $wd_m['wall_likes'] ?? 0 ) ) ); ?></small></div>
	<div class="stat tone-sky"><span><?php echo wd_icon( 'camera', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Photo likes', 'wwmd' ); ?></span><b><?php echo esc_html( number_format_i18n( (int) ( $wd_m['photo_likes'] ?? 0 ) ) ); ?></b></div>
	<div class="stat tone-butter"><span><?php echo wd_icon( 'cash', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Gifts & spraying', 'wwmd' ); ?></span><b><?php echo esc_html( wd_money( $wd_t['spray'] + $wd_t['gift'], $wd_cur ) ); ?></b><small><?php echo esc_html( sprintf( /* translators: %s amount */ __( '%s ready to withdraw', 'wwmd' ), wd_money( $wd_t['available'], $wd_cur ) ) ); ?></small></div>
</div>
<div class="panel coin-panel">
	<div class="panel__head"><h2><?php esc_html_e( 'Your Buzz coins this week', 'wwmd' ); ?></h2><a class="link" href="<?php echo esc_url( wd_url( 'rankings' ) ); ?>"><?php esc_html_e( 'How coins work', 'wwmd' ); ?></a></div>
	<ul class="coin-list">
		<?php foreach ( $wd_week['rows'] as $wd_k => $wd_row ) : ?>
			<?php if ( ! wd_coin_rate( $wd_k ) ) { continue; } ?>
			<li class="<?php echo $wd_row['locked'] ? 'is-locked' : ''; ?>">
				<?php echo wd_icon( $wd_figs[ $wd_k ]['icon'], 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span><?php echo esc_html( $wd_figs[ $wd_k ]['label'] ); ?><small><?php echo esc_html( sprintf( /* translators: 1 coins 2 per */ _n( '%1$s coin %2$s', '%1$s coins %2$s', wd_coin_rate( $wd_k ), 'wwmd' ), number_format_i18n( wd_coin_rate( $wd_k ) ), $wd_figs[ $wd_k ]['per'] ) ); ?></small></span>
				<?php if ( $wd_row['locked'] ) : ?>
					<?php $wd_fp = wd_feature_plan( $wd_figs[ $wd_k ]['feature'] ); ?>
					<a class="coin-list__lock" href="<?php echo esc_url( add_query_arg( array( 'tab' => 'plan', 'wid' => $wd_id, 'tier' => $wd_fp ), wd_url( 'account' ) ) . '#pay' ); ?>"><?php echo wd_icon( 'lock', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( sprintf( /* translators: %s plan */ __( 'On %s', 'wwmd' ), wd_plan_name( $wd_fp ) ) ); ?></a>
				<?php else : ?>
					<b><?php echo esc_html( number_format_i18n( $wd_row['n'] ) ); ?><em>+<?php echo esc_html( number_format_i18n( $wd_row['coins'] ) ); ?></em></b>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php if ( count( array_filter( wp_list_pluck( $wd_week['rows'], 'locked' ) ) ) ) : ?>
		<p class="hint"><?php esc_html_e( 'Locked figures earn nothing on your plan. Moving up unlocks them from the day you pay.', 'wwmd' ); ?></p>
	<?php endif; ?>
</div>
<div class="panel">
	<div class="panel__head"><h2><?php esc_html_e( 'Finish your page', 'wwmd' ); ?></h2><span class="pill"><?php echo (int) $wd_done; ?>/<?php echo count( $wd_steps ); ?></span></div>
	<div class="meter meter--lg"><i style="width:<?php echo (int) round( $wd_done / count( $wd_steps ) * 100 ); ?>%"></i></div>
	<ul class="checklist">
		<?php foreach ( $wd_steps as $wd_s ) : ?>
			<li class="<?php echo $wd_s[0] ? 'is-done' : ''; ?>"><?php echo wd_icon( $wd_s[0] ? 'check' : 'plus', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><a href="<?php echo esc_url( wd_account_url( 'page', array( 'wid' => $wd_id, 'part' => $wd_s[2] ) ) ); ?>"><?php echo esc_html( $wd_s[1] ); ?></a></li>
		<?php endforeach; ?>
	</ul>
</div>
