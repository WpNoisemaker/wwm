<?php
/**
 * Profile & password.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_u = $args['user'];
?>
<div class="app-head"><div><p class="eyebrow"><?php esc_html_e( 'Account', 'wwmd' ); ?></p><h1><?php esc_html_e( 'My profile', 'wwmd' ); ?></h1></div></div>
<div class="app-cols">
	<?php wd_form( 'profile', 0 ); ?>
		<h2><?php esc_html_e( 'Your details', 'wwmd' ); ?></h2>
		<div class="field-row">
			<?php wd_field( 'first_name', __( 'First name', 'wwmd' ), $wd_u->first_name, 'text', 'required' ); ?>
			<?php wd_field( 'last_name', __( 'Last name', 'wwmd' ), $wd_u->last_name ); ?>
		</div>
		<?php wd_field( 'email', __( 'Email (you sign in with it)', 'wwmd' ), $wd_u->user_email, 'email', 'required' ); ?>
		<?php wd_field( 'current', __( 'Current password (only needed to change your email)', 'wwmd' ), '', 'password', 'autocomplete="current-password"' ); ?>
		<?php wd_field( 'phone', __( 'WhatsApp number', 'wwmd' ), get_user_meta( $wd_u->ID, 'wd_phone', true ), 'tel' ); ?>
		<?php wd_field( 'country', __( 'Country you live in', 'wwmd' ), get_user_meta( $wd_u->ID, 'wd_country', true ) ); ?>
		<button class="btn btn--ink" type="submit"><?php esc_html_e( 'Save', 'wwmd' ); ?></button>
		<?php
		$wd_links = array();
		if ( wd_user_weddings( $wd_u->ID ) ) {
			$wd_links[] = array( wd_account_url( 'page' ), 'edit', __( 'Wedding page details: names, dates, venues & more', 'wwmd' ) );
		}
		if ( wd_partner( $wd_u->ID ) ) {
			$wd_links[] = array( wd_account_url( 'partner', array( 'part' => 'profile' ) ), 'store', __( 'Business details: name, phone, city, photos & more', 'wwmd' ) );
		}
		?>
		<?php if ( $wd_links ) : ?>
			<ul class="mini-list mini-list--links">
				<?php foreach ( $wd_links as $wd_l ) : ?>
					<li><a href="<?php echo esc_url( $wd_l[0] ); ?>"><?php echo wd_icon( $wd_l[1], 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $wd_l[2] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</form>
	<?php wd_form( 'password', 0 ); ?>
		<h2><?php esc_html_e( 'Change password', 'wwmd' ); ?></h2>
		<?php wd_field( 'current', __( 'Current password', 'wwmd' ), '', 'password', 'required autocomplete="current-password"' ); ?>
		<?php wd_field( 'new', __( 'New password (8+ characters)', 'wwmd' ), '', 'password', 'required minlength="8" autocomplete="new-password"' ); ?>
		<?php wd_field( 'again', __( 'New password again', 'wwmd' ), '', 'password', 'required minlength="8" autocomplete="new-password"' ); ?>
		<button class="btn btn--ink" type="submit"><?php esc_html_e( 'Change password', 'wwmd' ); ?></button>
	</form>
</div>
