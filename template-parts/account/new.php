<?php
/**
 * New wedding page.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_pick = in_array( $_GET['plan'] ?? '', array( 'pro', 'premium' ), true ) ? sanitize_key( $_GET['plan'] ) : 'free'; // phpcs:ignore WordPress.Security.NonceVerification
?>
<div class="app-head"><div><p class="eyebrow"><?php esc_html_e( 'New wedding page', 'wwmd' ); ?></p><h1><?php esc_html_e( 'Let’s make your page', 'wwmd' ); ?></h1></div></div>
<?php wd_form( 'new', 0 ); ?>
	<div class="field-row">
		<?php wd_field( 'p1', __( 'First partner’s first name', 'wwmd' ), '', 'text', 'required placeholder="Tolani"' ); ?>
		<?php wd_field( 'p2', __( 'Second partner’s first name', 'wwmd' ), '', 'text', 'required placeholder="Kayode"' ); ?>
	</div>
	<div class="field-row">
		<?php wd_field( 'date', __( 'Wedding date', 'wwmd' ), '', 'date', 'required' ); ?>
		<?php wd_field( 'hashtag', __( 'Wedding hashtag (becomes your link)', 'wwmd' ), '', 'text', 'placeholder="TolaKay26"' ); ?>
	</div>
	<div class="field-row">
		<?php wd_field( 'city', __( 'City', 'wwmd' ), '', 'text', 'required placeholder="Lagos"' ); ?>
		<?php wd_field( 'country', __( 'Country', 'wwmd' ), '', 'text', 'required placeholder="Nigeria"' ); ?>
		<label class="field"><span><?php esc_html_e( 'Gifts paid in', 'wwmd' ); ?></span><select name="currency"><?php foreach ( wd_currencies() as $wd_c => $wd_cn ) : ?><option value="<?php echo esc_attr( $wd_c ); ?>"><?php echo esc_html( $wd_cn[1] . ' (' . $wd_cn[0] . ')' ); ?></option><?php endforeach; ?></select></label>
	</div>
	<label class="field"><span><?php esc_html_e( 'Wedding style', 'wwmd' ); ?></span><select name="style"><?php foreach ( wd_styles() as $wd_slug => $wd_s ) : ?><option value="<?php echo esc_attr( $wd_slug ); ?>"><?php echo esc_html( $wd_s[0] ); ?></option><?php endforeach; ?></select></label>
	<fieldset class="plan-pick plan-pick--3"><legend><?php esc_html_e( 'Choose your page', 'wwmd' ); ?></legend>
		<label><input type="radio" name="plan" value="free" <?php checked( $wd_pick, 'free' ); ?>><span><b><?php esc_html_e( 'Free', 'wwmd' ); ?></b><small><?php esc_html_e( 'Invitation, RSVP, love story, wall of love, vendors & cash registry in our colours.', 'wwmd' ); ?></small></span></label>
		<label><input type="radio" name="plan" value="pro" <?php checked( $wd_pick, 'pro' ); ?>><span><b><?php echo esc_html( sprintf( /* translators: %s price */ __( 'Pro · %s', 'wwmd' ), wd_money( wd_opt( 'pro_price' ) ) ) ); ?></b><small><?php esc_html_e( 'Everything in Free, plus your squad, guest photos, quiz, Instagram, entry passes, your own colours & no ads.', 'wwmd' ); ?></small></span></label>
		<label><input type="radio" name="plan" value="premium" <?php checked( $wd_pick, 'premium' ); ?>><span><b><?php echo esc_html( sprintf( /* translators: %s price */ __( 'Premium · %s', 'wwmd' ), wd_money( wd_opt( 'premium_price' ) ) ) ); ?></b><small><?php esc_html_e( 'Everything in Pro, plus a Premium design, livestream, spraying, printed table QR cards & no fees.', 'wwmd' ); ?></small></span></label>
	</fieldset>
	<div class="tpl-step" data-if-premium>
		<p class="form__h"><?php echo wd_icon( 'spark', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Choose your design', 'wwmd' ); ?></p>
		<p class="muted" data-plan-note="pro"><?php esc_html_e( 'On Pro, your design sets your colours & fonts on our Classic layout. Premium gives you the whole design: its own layout, ornaments & motion.', 'wwmd' ); ?></p>
		<p class="muted" data-plan-note="premium"><?php esc_html_e( 'Your page gets the whole design: its own layout, ornaments & motion. You can switch designs & change colours any time.', 'wwmd' ); ?></p>
		<div class="tpl-pick">
			<?php $wd_first = true; ?>
			<?php foreach ( wd_templates() as $wd_k => $wd_t ) : ?>
				<label class="tpl-card">
					<input type="radio" name="template" value="<?php echo esc_attr( $wd_k ); ?>" <?php checked( $wd_first ); ?>>
					<span class="tpl-card__pic"><?php echo wd_tpl_thumb( $wd_k ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<b><?php echo esc_html( $wd_t[0] ); ?></b>
					<small><?php echo esc_html( $wd_t[1] ); ?></small>
				</label>
				<?php $wd_first = false; ?>
			<?php endforeach; ?>
		</div>
	</div>
	<p class="hint"><?php echo wd_icon( 'gem', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Planning something grand? High Society brands your whole event, from the prints to livestream coverage.', 'wwmd' ); ?> <a href="<?php echo esc_url( wd_society_quote_url() ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Get a quote on WhatsApp', 'wwmd' ); ?></a></p>
	<div class="gift-credit" data-if-premium>
		<p class="form__h"><?php echo wd_icon( 'gift', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Is someone gifting you this plan? (optional)', 'wwmd' ); ?></p>
		<p class="muted"><?php esc_html_e( 'We show a small thank-you on your page with their name & a link to one of their social pages.', 'wwmd' ); ?></p>
		<div class="field-row">
			<?php wd_field( 'gift_by', __( 'Gift from', 'wwmd' ), '', 'text', 'maxlength="80" placeholder="' . esc_attr__( 'Aunty Funmi, the bride’s godmother', 'wwmd' ) . '"' ); ?>
			<?php wd_field( 'gift_link', __( 'Link to one of their social pages', 'wwmd' ), '', 'url', 'placeholder="https://instagram.com/…"' ); ?>
		</div>
	</div>
	<button class="btn btn--ink btn--lg" type="submit"><?php esc_html_e( 'Create my wedding page', 'wwmd' ); ?></button>
</form>
