<?php
/**
 * Footer: menus (Appearance → Menus), WhatsApp & email, & the brand seal turning like a coin.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_links = wd_menu( 'footer' );
$wd_legal = has_nav_menu( 'legal' ) ? wd_menu( 'legal' ) : array( array( __( 'Terms', 'wwmd' ), wd_url( 'terms' ) ), array( __( 'Privacy', 'wwmd' ), wd_url( 'privacy' ) ) );
?>
</main>
<footer class="site-footer">
	<div class="kente-strip" aria-hidden="true"></div>
	<div class="wrap site-footer__grid">
		<div class="site-footer__brand">
			<a class="footer-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				<?php /* The seal turns slowly on its spot like a coin, with a light passing over it. Both faces are the seal. */ ?>
				<span class="coin" aria-hidden="true"><span class="coin__body"><span class="coin__face"><?php wd_seal( 180 ); ?></span><span class="coin__edge"><?php echo str_repeat( '<i></i>', 7 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><span class="coin__face coin__face--back"><?php wd_seal( 180 ); ?></span></span></span>
				<?php wd_logo( true, 'footer-logo__text' ); ?>
			</a>
			<p class="site-footer__tag"><?php esc_html_e( 'Where Nigerian & African weddings everywhere come to be seen, celebrated & ranked.', 'wwmd' ); ?></p>
			<div class="site-footer__contact">
				<a class="btn btn--marigold btn--sm" href="<?php echo esc_url( wd_whatsapp( __( 'Hello! I have a question about wedding pages.', 'wwmd' ) ) ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'whatsapp', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( wd_whatsapp_display() ); ?></a>
				<a class="site-footer__mail" href="mailto:<?php echo esc_attr( wd_opt( 'email' ) ); ?>"><?php echo esc_html( wd_opt( 'email' ) ); ?></a>
			</div>
		</div>
		<nav class="site-footer__nav" aria-label="<?php esc_attr_e( 'Footer', 'wwmd' ); ?>">
			<ul>
				<?php foreach ( $wd_links as $wd_l ) : ?>
					<li><a href="<?php echo esc_url( $wd_l[1] ); ?>"><?php echo esc_html( $wd_l[0] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<div class="site-footer__places">
			<p class="eyebrow"><?php esc_html_e( 'Weddings in', 'wwmd' ); ?></p>
			<ul>
				<?php
				$wd_places = get_terms( array( 'taxonomy' => 'wd_place', 'hide_empty' => true, 'parent' => 0, 'number' => 8 ) );
				foreach ( is_array( $wd_places ) ? $wd_places : array() as $wd_pl ) :
					?>
					<li><a href="<?php echo esc_url( get_term_link( $wd_pl ) ); ?>"><?php echo esc_html( $wd_pl->name ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
	<div class="wrap site-footer__bottom">
		<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
		<ul>
			<?php foreach ( $wd_legal as $wd_l ) : ?>
				<li><a href="<?php echo esc_url( $wd_l[1] ); ?>"><?php echo esc_html( $wd_l[0] ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
</footer>
<?php
if ( wd_has_tabbar() ) {
	get_template_part( 'template-parts/tabbar' );
}
wp_footer();
?>
</body>
</html>
