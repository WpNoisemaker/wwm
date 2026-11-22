<?php
/**
 * Phone tab bar, like an app: up to 4 links from HQ › Menus › Phone tab bar, then "You" (account or sign in).
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_items = array_slice( wd_menu( 'tabbar' ), 0, 4 );
$wd_now   = trailingslashit( (string) wp_parse_url( add_query_arg( array() ), PHP_URL_PATH ) );
$wd_home  = trailingslashit( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ) );
$wd_me    = wp_get_current_user();
$wd_acc   = $wd_me->exists() ? wd_account_url() : wd_url( 'sign-in' );
?>
<nav class="tabbar" aria-label="<?php esc_attr_e( 'Quick links', 'wwmd' ); ?>">
	<ul>
		<?php
		foreach ( $wd_items as $wd_it ) :
			$wd_path = trailingslashit( (string) wp_parse_url( $wd_it[1], PHP_URL_PATH ) );
			$wd_on   = $wd_path === $wd_home ? $wd_now === $wd_home : ( $wd_path !== $wd_home && 0 === strpos( $wd_now, $wd_path ) );
			?>
			<li><a href="<?php echo esc_url( $wd_it[1] ); ?>"<?php echo $wd_on ? ' class="is-on" aria-current="page"' : ''; ?>><?php echo wd_icon( wd_tab_icon( $wd_it[1], $wd_it[0] ), 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $wd_it[0] ); ?></span></a></li>
		<?php endforeach; ?>
		<?php $wd_on = 0 === strpos( $wd_now, trailingslashit( (string) wp_parse_url( $wd_acc, PHP_URL_PATH ) ) ); ?>
		<li><a href="<?php echo esc_url( $wd_acc ); ?>"<?php echo $wd_on ? ' class="is-on" aria-current="page"' : ''; ?>>
			<?php if ( $wd_me->exists() ) : ?>
				<span class="tabbar__me"><?php echo wd_avatar( $wd_me->display_name ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><span><?php esc_html_e( 'You', 'wwmd' ); ?></span>
			<?php else : ?>
				<?php echo wd_icon( 'users', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Sign in', 'wwmd' ); ?></span>
			<?php endif; ?>
		</a></li>
	</ul>
</nav>
