<?php
/**
 * Template Name: Account (couples)
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_user = wp_get_current_user();
$wd_ids  = wd_user_weddings( $wd_user->ID );
$wd_id   = wd_current_wedding();
// phpcs:ignore WordPress.Security.NonceVerification
$wd_tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'overview';
$wd_tabs = array(
	'overview' => array( __( 'Overview', 'wwmd' ), 'home' ),
	'page'     => array( __( 'Wedding page', 'wwmd' ), 'edit' ),
	'guests'   => array( __( 'Guests & RSVPs', 'wwmd' ), 'users' ),
	'messages' => array( __( 'Love notes & photos', 'wwmd' ), 'chat' ),
	'money'    => array( __( 'Money', 'wwmd' ), 'cash' ),
	'cards'    => array( __( 'Table QR cards', 'wwmd' ), 'qr' ),
	'plan'     => array( __( 'Plan', 'wwmd' ), 'crown' ),
	'services' => array( __( 'Orders & services', 'wwmd' ), 'plane' ),
	'partner'  => array( __( 'Seller dashboard', 'wwmd' ), 'store' ),
	'profile'  => array( __( 'My profile', 'wwmd' ), 'settings' ),
);
$wd_pp   = wd_partner( $wd_user->ID );
$wd_open = array( 'profile', 'new', 'services', 'partner' ); // Tabs that work without a wedding page.
if ( ! $wd_pp && 'partner' !== $wd_tab ) {
	unset( $wd_tabs['partner'] );
}
if ( ! $wd_id && ! in_array( $wd_tab, $wd_open, true ) ) {
	// Users who only shop & chat start on their orders & chats; couples start on a new wedding page.
	$wd_tab = $wd_pp ? 'partner' : ( in_array( 'member', (array) $wd_user->roles, true ) ? 'services' : 'new' );
}
if ( 'new' !== $wd_tab && ! isset( $wd_tabs[ $wd_tab ] ) ) {
	$wd_tab = 'overview';
}
$wd_d = $wd_id ? wd_get( $wd_id ) : null;
get_header();
?>
<section class="app">
	<div class="wrap app__grid">
		<aside class="app__side">
			<div class="app__me">
				<span class="app__av"><?php echo wd_avatar( $wd_user->display_name ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				<span><b><?php echo esc_html( $wd_user->display_name ); ?></b><small><?php echo esc_html( $wd_user->user_email ); ?></small></span>
			</div>
			<?php if ( $wd_ids ) : ?>
				<form class="app__switch" method="get" action="<?php echo esc_url( wd_account_url() ); ?>">
					<label class="screen-reader-text" for="w-switch"><?php esc_html_e( 'Wedding page', 'wwmd' ); ?></label>
					<select id="w-switch" name="wid" onchange="this.form.submit()">
						<?php foreach ( $wd_ids as $wd_wid ) : ?>
							<option value="<?php echo (int) $wd_wid; ?>" <?php selected( $wd_id, $wd_wid ); ?>><?php echo esc_html( get_the_title( $wd_wid ) ); ?></option>
						<?php endforeach; ?>
					</select>
					<input type="hidden" name="tab" value="<?php echo esc_attr( 'new' === $wd_tab ? 'overview' : $wd_tab ); ?>">
				</form>
			<?php endif; ?>
			<nav class="app__nav" aria-label="<?php esc_attr_e( 'Account', 'wwmd' ); ?>">
				<?php foreach ( $wd_tabs as $wd_k => $wd_t ) : ?>
					<?php if ( ! $wd_id && ! in_array( $wd_k, $wd_open, true ) ) { continue; } ?>
					<a class="<?php echo $wd_k === $wd_tab ? 'is-on' : ''; ?>" href="<?php echo esc_url( wd_account_url( $wd_k, $wd_id ? array( 'wid' => $wd_id ) : array() ) ); ?>"<?php echo $wd_k === $wd_tab ? ' aria-current="page"' : ''; ?>><?php echo wd_icon( $wd_t[1], 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $wd_t[0] ); ?></a>
				<?php endforeach; ?>
				<a class="<?php echo 'new' === $wd_tab ? 'is-on' : ''; ?>" href="<?php echo esc_url( wd_account_url( 'new' ) ); ?>"><?php echo wd_icon( 'plus', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'New wedding page', 'wwmd' ); ?></a>
				<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php echo wd_icon( 'logout', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Sign out', 'wwmd' ); ?></a>
			</nav>
		</aside>
		<div class="app__main">
			<?php wd_notice(); ?>
			<?php get_template_part( 'template-parts/account/' . $wd_tab, null, array( 'id' => $wd_id, 'd' => $wd_d, 'user' => $wd_user ) ); ?>
		</div>
	</div>
</section>
<?php
get_footer();
