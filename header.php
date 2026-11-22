<?php
/**
 * Header: logo, the pill menu (Appearance → Menus → Header menu), account link & the main button.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_nav  = wd_menu( 'primary' );
$wd_user = wp_get_current_user();
$wd_cta  = is_user_logged_in() ? array( __( 'My wedding page', 'wwmd' ), wd_account_url() ) : array( __( 'Create a free page', 'wwmd' ), wd_url( 'sign-in', array( 'tab' => 'join' ) ) );
$wd_req  = isset( $_SERVER['REQUEST_URI'] ) ? untrailingslashit( strtok( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), '?' ) ) : '';
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php if ( wd_opt( 'preloader' ) && ! is_customize_preview() ) : ?>
<div class="preloader" data-preloader aria-hidden="true">
	<div class="preloader__in">
		<?php if ( get_theme_mod( 'custom_logo' ) ) : ?>
			<?php wd_logo( false, 'preloader__custom' ); ?>
		<?php else : ?>
			<span class="coin coin--loader"><span class="coin__body"><span class="coin__face"><?php wd_seal( 120 ); ?></span><span class="coin__edge"><?php echo str_repeat( '<i></i>', 7 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><span class="coin__face coin__face--back"><?php wd_seal( 120 ); ?></span></span></span>
			<span class="logo preloader__name"><span class="logo__text"><span class="logo__line"><?php esc_html_e( 'Wedding Websites', 'wwmd' ); ?></span> <span class="logo__line"><span class="logo__amp">&amp;</span> <?php esc_html_e( 'More', 'wwmd' ); ?></span></span></span>
		<?php endif; ?>
		<span class="preloader__bar"><i></i></span>
	</div>
</div>
<?php endif; ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'wwmd' ); ?></a>
<header class="site-header" data-header>
	<div class="site-header__inner">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"><?php wd_logo( false, 'brand__logo' ); ?></a>
		<nav class="pill-nav" aria-label="<?php esc_attr_e( 'Main', 'wwmd' ); ?>">
			<ul>
				<?php foreach ( $wd_nav as $wd_item ) : ?>
					<?php $wd_here = $wd_req && untrailingslashit( (string) wp_parse_url( $wd_item[1], PHP_URL_PATH ) ) === $wd_req; ?>
					<li><a href="<?php echo esc_url( $wd_item[1] ); ?>"<?php echo $wd_here ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $wd_item[0] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<div class="header-actions">
			<?php echo wd_theme_switch( 'theme-switch--compact' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( is_user_logged_in() ) : ?>
				<?php if ( current_user_can( 'manage_options' ) ) : ?>
					<a class="chip-link" href="<?php echo esc_url( wd_url( 'hq' ) ); ?>"><?php echo wd_icon( 'settings', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'HQ', 'wwmd' ); ?></span></a>
				<?php endif; ?>
			<?php else : ?>
				<a class="chip-link" href="<?php echo esc_url( wd_url( 'sign-in' ) ); ?>"><?php echo wd_icon( 'users', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Sign in', 'wwmd' ); ?></span></a>
			<?php endif; ?>
			<a class="btn btn--ink btn--sm header-cta" href="<?php echo esc_url( $wd_cta[1] ); ?>"><?php echo esc_html( $wd_cta[0] ); ?></a>
			<button class="menu-btn" type="button" aria-expanded="false" aria-controls="menu-sheet" data-menu-toggle>
				<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'wwmd' ); ?></span>
				<span class="menu-btn__bars" aria-hidden="true"><i></i><i></i></span>
			</button>
		</div>
	</div>
</header>
<div class="menu-sheet" id="menu-sheet" hidden data-menu-sheet>
	<nav aria-label="<?php esc_attr_e( 'Menu', 'wwmd' ); ?>">
		<ul class="menu-sheet__links">
			<?php foreach ( $wd_nav as $wd_i => $wd_item ) : ?>
				<li style="--i:<?php echo (int) $wd_i; ?>"><a href="<?php echo esc_url( $wd_item[1] ); ?>"><?php echo esc_html( $wd_item[0] ); ?><?php echo wd_icon( 'arrow', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
			<?php endforeach; ?>
		</ul>
	</nav>
	<div class="menu-sheet__foot">
		<div class="menu-sheet__theme"><span><?php esc_html_e( 'Appearance', 'wwmd' ); ?></span><?php echo wd_theme_switch(); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		<a class="btn btn--marigold" href="<?php echo esc_url( $wd_cta[1] ); ?>"><?php echo esc_html( $wd_cta[0] ); ?></a>
		<?php if ( ! is_user_logged_in() ) : ?>
			<a class="btn btn--ghost" href="<?php echo esc_url( wd_url( 'sign-in' ) ); ?>"><?php esc_html_e( 'Sign in', 'wwmd' ); ?></a>
		<?php elseif ( current_user_can( 'manage_options' ) ) : ?>
			<a class="btn btn--ghost" href="<?php echo esc_url( wd_url( 'hq' ) ); ?>"><?php esc_html_e( 'HQ', 'wwmd' ); ?></a>
		<?php endif; ?>
		<a class="menu-sheet__wa" href="<?php echo esc_url( wd_whatsapp( __( 'Hello! I have a question about wedding pages.', 'wwmd' ) ) ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'whatsapp', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Chat on WhatsApp', 'wwmd' ); ?></span><small><?php echo esc_html( wd_whatsapp_display() ); ?></small></a>
	</div>
</div>
<main id="main" class="site-main">
