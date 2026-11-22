<?php
/**
 * HQ: edit the header, footer & legal menus on the front end.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_locs = array(
	'primary' => array( __( 'Header menu', 'wwmd' ), __( 'Shown in the top bar & the mobile menu.', 'wwmd' ) ),
	'footer'  => array( __( 'Footer menu', 'wwmd' ), __( 'The main links in the footer.', 'wwmd' ) ),
	'legal'   => array( __( 'Footer bottom links', 'wwmd' ), __( 'Small links beside the copyright line.', 'wwmd' ) ),
	'tabbar'  => array( __( 'Phone tab bar', 'wwmd' ), __( 'The app-style bar at the bottom of the screen on phones & tablets. Up to 4 links; the icon is picked from where each link goes. A fifth tab, "You" (or "Sign in"), is always added at the end.', 'wwmd' ) ),
);
$wd_quick = array(
	__( 'Explore weddings', 'wwmd' ) => wd_url( 'explore' ),
	__( 'Rankings', 'wwmd' )         => wd_url( 'rankings' ),
	__( 'Anonymous community', 'wwmd' ) => wd_url( 'community' ),
	__( 'Vendors', 'wwmd' )          => wd_url( 'vendors' ),
	__( 'Landing page for ads', 'wwmd' ) => wd_url( 'discover' ),
	__( 'Pricing', 'wwmd' )          => wd_url( 'pricing' ),
	__( 'Services', 'wwmd' )         => wd_url( 'services' ),
	__( 'About us', 'wwmd' )         => wd_url( 'about' ),
	__( 'Sign in', 'wwmd' )          => wd_url( 'sign-in' ),
);
?>
<div class="app-head"><div><p class="eyebrow"><?php esc_html_e( 'HQ', 'wwmd' ); ?></p><h1><?php esc_html_e( 'Menus', 'wwmd' ); ?></h1><p><?php esc_html_e( 'Rename, reorder, add or remove links. Use a full link (https://…) or a path on this site such as /weddings/.', 'wwmd' ); ?></p></div></div>
<?php wd_hq_form( 'menus', 'settings-form' ); ?>
	<?php foreach ( $wd_locs as $wd_loc => $wd_l ) : ?>
		<div class="panel">
			<h2><?php echo esc_html( $wd_l[0] ); ?></h2>
			<p class="hint"><?php echo esc_html( $wd_l[1] ); ?></p>
			<?php
			$wd_rows = array();
			foreach ( wd_menu( $wd_loc ) as $wd_it ) {
				$wd_rows[] = array( 'title' => $wd_it[0], 'url' => $wd_it[1] );
			}
			wd_repeater(
				'menu_' . $wd_loc,
				$wd_rows,
				static function ( $name, $row ) {
					echo '<div class="rep__fields rep__fields--2">';
					wd_field( $name . '[title]', __( 'Link text', 'wwmd' ), $row['title'] );
					wd_field( $name . '[url]', __( 'Link', 'wwmd' ), $row['url'] );
					echo '</div>';
				},
				__( 'Add link', 'wwmd' ),
				array( 'title' => '', 'url' => '' )
			);
			?>
		</div>
	<?php endforeach; ?>
	<div class="panel panel--soft">
		<h2><?php esc_html_e( 'Handy links', 'wwmd' ); ?></h2>
		<ul class="mini-list">
			<?php foreach ( $wd_quick as $wd_t => $wd_u ) : ?>
				<li><span><?php echo esc_html( $wd_t ); ?></span><code><?php echo esc_html( wp_make_link_relative( $wd_u ) ); ?></code></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<div class="form-actions form-actions--sticky"><button class="btn btn--brand" type="submit"><?php esc_html_e( 'Save menus', 'wwmd' ); ?></button></div>
</form>
