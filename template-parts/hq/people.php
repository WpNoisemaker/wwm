<?php
/**
 * HQ: people & roles.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_users = get_users( array( 'number' => 200, 'orderby' => 'registered', 'order' => 'DESC' ) );
?>
<div class="app-head"><div><p class="eyebrow"><?php esc_html_e( 'HQ', 'wwmd' ); ?></p><h1><?php esc_html_e( 'People', 'wwmd' ); ?></h1><p><?php esc_html_e( 'Make a teammate an HQ admin so they can run payouts, moderation & settings. Couples & users only see their own dashboard.', 'wwmd' ); ?></p></div></div>
<div class="table-wrap"><table class="table">
	<thead><tr><th><?php esc_html_e( 'Name', 'wwmd' ); ?></th><th><?php esc_html_e( 'Wedding pages', 'wwmd' ); ?></th><th><?php esc_html_e( 'Joined', 'wwmd' ); ?></th><th><?php esc_html_e( 'Role', 'wwmd' ); ?></th><th><?php esc_html_e( 'Sign-in', 'wwmd' ); ?></th></tr></thead>
	<tbody>
	<?php foreach ( $wd_users as $wd_u ) : $wd_w = wd_user_weddings( $wd_u->ID ); ?>
		<tr>
			<td><b><?php echo esc_html( $wd_u->display_name ); ?></b><small><?php echo esc_html( $wd_u->user_email ); ?></small></td>
			<td><?php foreach ( $wd_w as $wd_id ) : ?><a href="<?php echo esc_url( get_permalink( $wd_id ) ); ?>"><?php echo esc_html( get_the_title( $wd_id ) ); ?></a> <?php endforeach; ?><?php echo $wd_w ? '' : '–'; ?></td>
			<td><?php echo esc_html( wd_ago( $wd_u->user_registered ) ); ?></td>
			<td><?php if ( get_current_user_id() === $wd_u->ID ) : ?><span class="pill"><?php esc_html_e( 'You (HQ admin)', 'wwmd' ); ?></span><?php else : ?><?php wd_hq_form( 'role' ); ?><input type="hidden" name="id" value="<?php echo (int) $wd_u->ID; ?>"><select name="role" onchange="this.form.submit()" aria-label="<?php esc_attr_e( 'Role', 'wwmd' ); ?>"><option value="couple" <?php selected( ! array_intersect( array( 'administrator', 'member' ), (array) $wd_u->roles ) ); ?>><?php esc_html_e( 'Couple', 'wwmd' ); ?></option><option value="member" <?php selected( in_array( 'member', (array) $wd_u->roles, true ) ); ?>><?php esc_html_e( 'User', 'wwmd' ); ?></option><option value="administrator" <?php selected( in_array( 'administrator', (array) $wd_u->roles, true ) ); ?>><?php esc_html_e( 'HQ admin', 'wwmd' ); ?></option></select></form><?php endif; ?></td>
			<td>
				<?php if ( get_current_user_id() !== $wd_u->ID ) : ?>
					<details class="login-edit">
						<summary><?php esc_html_e( 'Change', 'wwmd' ); ?></summary>
						<?php wd_hq_form( 'login', 'login-edit__form' ); ?>
							<input type="hidden" name="id" value="<?php echo (int) $wd_u->ID; ?>">
							<label class="field"><span><?php esc_html_e( 'Sign-in email', 'wwmd' ); ?></span><input type="email" name="email" required value="<?php echo esc_attr( $wd_u->user_email ); ?>"></label>
							<label class="field"><span><?php esc_html_e( 'New password (leave blank to keep theirs)', 'wwmd' ); ?></span><input type="text" name="pass" minlength="8" autocomplete="new-password" spellcheck="false"></label>
							<button class="btn btn--ink btn--sm" type="submit"><?php esc_html_e( 'Save sign-in', 'wwmd' ); ?></button>
						</form>
					</details>
				<?php endif; ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table></div>
