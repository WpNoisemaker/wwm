<?php
/**
 * Template Name: HQ (platform admin)
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
// phpcs:ignore WordPress.Security.NonceVerification
$wd_tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'overview';
$wd_tabs = array(
	'overview'   => array( __( 'Overview', 'wwmd' ), 'chart' ),
	'weddings'   => array( __( 'Weddings', 'wwmd' ), 'heart' ),
	'money'      => array( __( 'Payments & payouts', 'wwmd' ), 'cash' ),
	'moderation' => array( __( 'Moderation', 'wwmd' ), 'shield' ),
	'vendors'    => array( __( 'Vendors', 'wwmd' ), 'star' ),
	'market'     => array( __( 'Marketplace', 'wwmd' ), 'store' ),
	'requests'   => array( __( 'Service requests', 'wwmd' ), 'plane' ),
	'people'     => array( __( 'People', 'wwmd' ), 'users' ),
	'ads'        => array( __( 'Ads on free pages', 'wwmd' ), 'bell' ),
	'menus'      => array( __( 'Menus', 'wwmd' ), 'menu' ),
	'settings'   => array( __( 'Settings', 'wwmd' ), 'settings' ),
);
$wd_tab = isset( $wd_tabs[ $wd_tab ] ) ? $wd_tab : 'overview';
get_header();
?>
<section class="app app--hq">
	<div class="wrap app__grid">
		<aside class="app__side">
			<div class="app__me"><span class="app__av app__av--seal"><?php wd_seal( 40 ); ?></span><span><b><?php esc_html_e( 'HQ', 'wwmd' ); ?></b><small><?php echo esc_html( wp_get_current_user()->display_name ); ?></small></span></div>
			<nav class="app__nav" aria-label="<?php esc_attr_e( 'HQ', 'wwmd' ); ?>">
				<?php foreach ( $wd_tabs as $wd_k => $wd_t ) : ?>
					<a class="<?php echo $wd_k === $wd_tab ? 'is-on' : ''; ?>" href="<?php echo esc_url( wd_url( 'hq', array( 'tab' => $wd_k ) ) ); ?>"<?php echo $wd_k === $wd_tab ? ' aria-current="page"' : ''; ?>><?php echo wd_icon( $wd_t[1], 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $wd_t[0] ); ?></a>
				<?php endforeach; ?>
				<a href="<?php echo esc_url( wd_account_url() ); ?>"><?php echo wd_icon( 'home', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'My own wedding pages', 'wwmd' ); ?></a>
				<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php echo wd_icon( 'logout', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Sign out', 'wwmd' ); ?></a>
			</nav>
		</aside>
		<div class="app__main">
			<?php wd_notice(); ?>
			<?php get_template_part( 'template-parts/hq/' . $wd_tab ); ?>
		</div>
	</div>
</section>
<?php
get_footer();
