<?php
/**
 * Guest list & RSVPs.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
global $wpdb;
$wd_id   = $args['id'];
$wd_d    = $args['d'];
$wd_c    = wd_rsvp_counts( $wd_id );
$wd_q    = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$wd_sql  = 'SELECT * FROM ' . wd_t( 'rsvps' ) . ' WHERE wedding_id = %d';
$wd_args = array( $wd_id );
if ( $wd_q ) {
	$wd_sql   .= ' AND (name LIKE %s OR phone LIKE %s OR pass_code LIKE %s)';
	$wd_like   = '%' . $wpdb->esc_like( $wd_q ) . '%';
	$wd_args   = array_merge( $wd_args, array( $wd_like, $wd_like, $wd_like ) );
}
$wd_rows = $wpdb->get_results( $wpdb->prepare( $wd_sql . ' ORDER BY id DESC LIMIT 500', $wd_args ) ); // phpcs:ignore WordPress.DB
$wd_pass = wd_can( $wd_id, 'entry_pass' ) && ! empty( $wd_d['pass']['on'] );
?>
<div class="app-head">
	<div><p class="eyebrow"><?php esc_html_e( 'Guests', 'wwmd' ); ?></p><h1><?php esc_html_e( 'Guests & RSVPs', 'wwmd' ); ?></h1></div>
	<a class="btn btn--ghost" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=wd_csv&wid=' . $wd_id ), 'wd_csv_' . $wd_id ) ); ?>"><?php echo wd_icon( 'download', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Download guest list', 'wwmd' ); ?></a>
</div>
<div class="stat-grid stat-grid--4">
	<div class="stat tone-mint"><span><?php esc_html_e( 'RSVPs coming', 'wwmd' ); ?></span><b><?php echo (int) $wd_c['yes']; ?></b></div>
	<div class="stat tone-butter"><span><?php esc_html_e( 'Guests in total', 'wwmd' ); ?></span><b><?php echo (int) $wd_c['guests']; ?></b></div>
	<div class="stat tone-coral"><span><?php esc_html_e( 'Can’t make it', 'wwmd' ); ?></span><b><?php echo (int) $wd_c['no']; ?></b></div>
	<div class="stat tone-sky"><span><?php esc_html_e( 'Checked in', 'wwmd' ); ?></span><b><?php echo (int) $wd_c['checked']; ?></b></div>
</div>
<?php
$wd_pg = wd_programs( $wd_d );
if ( count( $wd_pg ) > 1 ) :
	$wd_bp = wd_rsvp_by_program( $wd_id, $wd_d );
	?>
	<div class="panel prog-counts">
		<h2><?php esc_html_e( 'Guests coming to each program', 'wwmd' ); ?></h2>
		<ul class="prog-count-list">
			<?php foreach ( $wd_pg as $wd_e ) : ?>
				<li>
					<span class="prog-count-list__icon"><?php echo wd_icon( $wd_e['icon'], 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<span><b><?php echo esc_html( $wd_e['title'] ? $wd_e['title'] : $wd_e['label'] ); ?></b><small><?php echo esc_html( trim( $wd_e['label'] . ( $wd_e['date'] ? ' · ' . wd_date( $wd_e['date'], 'D j M' ) : '' ) . ( $wd_e['private'] ? ' · ' . __( 'Private', 'wwmd' ) : '' ) ) ); ?></small></span>
					<b class="prog-count-list__n"><?php echo (int) ( $wd_bp[ $wd_e['title'] ] ?? 0 ); ?></b>
				</li>
			<?php endforeach; ?>
		</ul>
		<p class="hint"><?php esc_html_e( 'Guests who did not tick any program are counted for all of them.', 'wwmd' ); ?></p>
	</div>
<?php endif; ?>
<?php if ( $wd_pass ) : ?>
	<p class="hint"><?php echo wd_icon( 'ticket', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo $wd_d['pass']['pin'] ? esc_html__( 'Ushers: open any guest’s pass by scanning its QR code, type your usher PIN once, then tap Check in.', 'wwmd' ) : esc_html__( 'Set an usher PIN in Wedding page → Live, spray & passes so your ushers can check guests in.', 'wwmd' ); ?></p>
<?php endif; ?>
<form class="filters filters--one filters--sm" method="get" role="search"><input type="hidden" name="tab" value="guests"><input type="hidden" name="wid" value="<?php echo (int) $wd_id; ?>"><div class="filters__search"><?php echo wd_icon( 'search', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><input type="search" name="q" value="<?php echo esc_attr( $wd_q ); ?>" placeholder="<?php esc_attr_e( 'Name, phone or pass code', 'wwmd' ); ?>" aria-label="<?php esc_attr_e( 'Search guests', 'wwmd' ); ?>"></div><button class="btn btn--ink btn--sm" type="submit"><?php echo wd_icon( 'search', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Search', 'wwmd' ); ?></span></button></form>
<?php wd_form( 'moderate', $wd_id, false, 'table-wrap' ); ?><input type="hidden" name="type" value="rsvp"><input type="hidden" name="back" value="guests">
	<table class="table">
		<thead><tr><th><?php esc_html_e( 'Guest', 'wwmd' ); ?></th><th><?php esc_html_e( 'Coming', 'wwmd' ); ?></th><th><?php esc_html_e( 'Events', 'wwmd' ); ?></th><?php if ( $wd_pass ) : ?><th><?php esc_html_e( 'Pass', 'wwmd' ); ?></th><?php endif; ?><th><?php esc_html_e( 'Note', 'wwmd' ); ?></th><th></th></tr></thead>
		<tbody>
			<?php foreach ( (array) $wd_rows as $wd_r ) : ?>
				<tr>
					<td><b><?php echo esc_html( $wd_r->name ); ?></b><small><?php echo esc_html( trim( $wd_r->phone . ' ' . $wd_r->email ) ); ?></small></td>
					<td><?php echo 'yes' === $wd_r->attending ? '<span class="pill pill--ok">' . esc_html( sprintf( /* translators: %d */ __( 'Yes · %d', 'wwmd' ), (int) $wd_r->guests ) ) . '</span>' : '<span class="pill">' . esc_html__( 'No', 'wwmd' ) . '</span>'; ?></td>
					<td><small><?php echo esc_html( $wd_r->events ); ?></small></td>
					<?php if ( $wd_pass ) : ?><td><?php if ( $wd_r->pass_code ) : ?><a href="<?php echo esc_url( home_url( '/pass/' . $wd_r->pass_code . '/' ) ); ?>" target="_blank" rel="noopener"><code><?php echo esc_html( $wd_r->pass_code ); ?></code></a><?php echo $wd_r->checked_in ? ' <span class="pill pill--ok">' . esc_html__( 'In', 'wwmd' ) . '</span>' : ''; ?><?php endif; ?></td><?php endif; ?>
					<td><small><?php echo esc_html( $wd_r->note ); ?></small></td>
					<td class="row-actions">
						<?php if ( $wd_pass && $wd_r->pass_code ) : ?>
							<button class="linkish" type="submit" name="row" value="checkin-<?php echo (int) $wd_r->id; ?>"><?php echo $wd_r->checked_in ? esc_html__( 'Undo check-in', 'wwmd' ) : esc_html__( 'Check in', 'wwmd' ); ?></button>
						<?php endif; ?>
						<button class="linkish linkish--danger" type="submit" name="row" value="delete-<?php echo (int) $wd_r->id; ?>" data-confirm="<?php esc_attr_e( 'Delete this RSVP?', 'wwmd' ); ?>"><?php esc_html_e( 'Delete', 'wwmd' ); ?></button>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php if ( ! $wd_rows ) : ?><tr><td colspan="6" class="empty-note"><?php esc_html_e( 'No RSVPs yet. Share your link to get them coming in.', 'wwmd' ); ?></td></tr><?php endif; ?>
		</tbody>
	</table>
</form>
