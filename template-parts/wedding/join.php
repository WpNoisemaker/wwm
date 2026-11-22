<?php
/**
 * Squad invite page: a bridesmaid or groomsman adds their own profile. It shows in the line-up
 * once the couple approves it.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_id    = $args['id'];
$wd_d     = $args['d'];
$wd_key   = (string) $args['key'];
$wd_ok    = wd_squad_valid( $wd_id, $wd_key );
$wd_names = wd_names( $wd_d );
$wd_sides = array(
	'bride' => wd_side_name( $wd_d, 'bride' ),
	'groom' => wd_side_name( $wd_d, 'groom' ),
);
$wd_roles = wd_squad_roles();
$wd_side  = array( 'bride', 'bride', 'bride', 'groom', 'groom', '', '', '', 'bride', 'groom' );
?>
<section class="w-sec join">
	<div class="wrap join__wrap">
		<div class="join__intro">
			<p class="w-eyebrow"><?php echo wd_icon( 'users', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Join the squad', 'wwmd' ); ?></p>
			<h1 class="w-title"><?php echo esc_html( $wd_d['p1'] ); ?> <span class="w-amp">&amp;</span> <?php echo esc_html( $wd_d['p2'] ); ?></h1>
			<p class="join__when"><?php echo esc_html( trim( wd_date( $wd_d['date'], 'l j F Y' ) . ( $wd_d['city'] ? ' · ' . $wd_d['city'] : '' ), ' ·' ) ); ?></p>
			<?php if ( $wd_ok ) : ?>
				<p><?php echo esc_html( sprintf( /* translators: %s couple */ __( 'You are invited to the bridesmaids & groomsmen line-up on %s’s wedding page. Add your details below. Your profile shows on the page once the couple approves it.', 'wwmd' ), $wd_names ) ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( ! $wd_ok ) : ?>
			<div class="card-form join__note">
				<h2><?php esc_html_e( 'This invite link no longer works', 'wwmd' ); ?></h2>
				<p><?php esc_html_e( 'The couple may have made a new link. Ask them to send it again.', 'wwmd' ); ?></p>
				<a class="btn w-btn" href="<?php echo esc_url( get_permalink( $wd_id ) ); ?>"><?php esc_html_e( 'See the wedding page', 'wwmd' ); ?></a>
			</div>
		<?php else : ?>
			<form class="form card-form join__form" data-squad-form enctype="multipart/form-data" novalidate>
				<input type="hidden" name="wedding" value="<?php echo (int) $wd_id; ?>">
				<input type="hidden" name="key" value="<?php echo esc_attr( $wd_key ); ?>">
				<div class="hp" aria-hidden="true"><label><?php esc_html_e( 'Website', 'wwmd' ); ?> <input name="website" tabindex="-1" autocomplete="off"></label></div>
				<label class="drop drop--face"><input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required><span><?php echo wd_icon( 'camera', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Add a clear photo of you', 'wwmd' ); ?><small><?php esc_html_e( 'JPG, PNG or WebP, up to 8 MB', 'wwmd' ); ?></small></span></label>
				<div class="field-row">
					<label class="field"><span><?php esc_html_e( 'Your full name', 'wwmd' ); ?></span><input name="name" maxlength="60" required autocomplete="name"></label>
					<label class="field"><span><?php esc_html_e( 'Career or job title', 'wwmd' ); ?></span><input name="career" maxlength="60" required placeholder="<?php esc_attr_e( 'e.g. Lawyer, nurse or fashion designer', 'wwmd' ); ?>"></label>
				</div>
				<fieldset class="pick-side">
					<legend><?php esc_html_e( 'Whose side are you on?', 'wwmd' ); ?></legend>
					<?php foreach ( $wd_sides as $wd_k => $wd_n ) : ?>
						<label><input type="radio" name="side" value="<?php echo esc_attr( $wd_k ); ?>" <?php checked( 'bride', $wd_k ); ?>><span><?php echo esc_html( sprintf( /* translators: %s name */ __( 'Team %s', 'wwmd' ), $wd_n ) ); ?></span></label>
					<?php endforeach; ?>
				</fieldset>
				<div class="field-row">
					<label class="field"><span><?php esc_html_e( 'Your role', 'wwmd' ); ?></span><select name="role" data-role>
						<?php foreach ( $wd_roles as $wd_i => $wd_r ) : ?>
							<option value="<?php echo esc_attr( $wd_r ); ?>" data-side="<?php echo esc_attr( $wd_side[ $wd_i ] ); ?>"<?php echo 2 === $wd_i ? ' selected data-default="bride"' : ( 4 === $wd_i ? ' data-default="groom"' : '' ); ?>><?php echo esc_html( $wd_r ); ?></option>
						<?php endforeach; ?>
					</select></label>
					<label class="field"><span><?php esc_html_e( 'Relationship status', 'wwmd' ); ?></span><select name="status">
						<?php foreach ( wd_rel_statuses() as $wd_k => $wd_l ) : ?>
							<option value="<?php echo esc_attr( $wd_k ); ?>"><?php echo esc_html( $wd_l ); ?></option>
						<?php endforeach; ?>
					</select></label>
				</div>
				<div class="field-row">
					<label class="field"><span><?php esc_html_e( 'Nickname (optional)', 'wwmd' ); ?></span><input name="nick" maxlength="30" placeholder="<?php esc_attr_e( 'e.g. Timah', 'wwmd' ); ?>"></label>
					<label class="field"><span><?php esc_html_e( 'Things you love (optional)', 'wwmd' ); ?></span><input name="likes" maxlength="120" placeholder="<?php esc_attr_e( 'e.g. Baking, football & long voice notes', 'wwmd' ); ?>"></label>
				</div>
				<label class="field"><span><?php esc_html_e( 'A line about you (optional)', 'wwmd' ); ?></span><input name="line" maxlength="160" placeholder="<?php echo esc_attr( sprintf( /* translators: %s name */ __( 'e.g. %s’s cousin & the loudest dancer in the family', 'wwmd' ), wd_side_name( $wd_d, 'bride' ) ) ); ?>"></label>
				<p class="field-label"><?php esc_html_e( 'Your social handles (add at least one)', 'wwmd' ); ?></p>
				<div class="field-row field-row--socials">
					<?php foreach ( wd_socials() as $wd_k => $wd_s ) : ?>
						<label class="field field--social"><span><?php echo wd_icon( $wd_s[2], 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $wd_s[0] ); ?></span><input name="<?php echo esc_attr( $wd_k ); ?>" placeholder="@handle" autocapitalize="off" autocorrect="off" spellcheck="false" maxlength="80"></label>
					<?php endforeach; ?>
				</div>
				<label class="check"><input type="checkbox" name="agree" value="1" required><span><?php echo esc_html( sprintf( /* translators: %s couple */ __( 'I am happy for my photo & these details to show on %s’s wedding page once they approve it.', 'wwmd' ), $wd_names ) ); ?></span></label>
				<button class="btn w-btn btn--lg" type="submit"><?php echo wd_icon( 'send', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Send for approval', 'wwmd' ); ?></button>
				<p class="form__msg" data-msg role="status"></p>
			</form>
			<div class="card-form join__note" data-squad-done hidden>
				<span class="join__tick"><?php echo wd_icon( 'check', 30 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				<h2><?php esc_html_e( 'Sent!', 'wwmd' ); ?></h2>
				<p data-squad-done-msg></p>
				<a class="btn w-btn" href="<?php echo esc_url( get_permalink( $wd_id ) ); ?>"><?php esc_html_e( 'See the wedding page', 'wwmd' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</section>
