<?php
/**
 * Seller dashboard: "Your invoice look". Premium sellers choose their colours, font & logo for every
 * invoice, payment link & receipt they send. Other sellers see what it looks like & how to get it.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_uid  = get_current_user_id();
$wd_pp   = wd_partner( $wd_uid );
$wd_prem = wd_seller_premium( $wd_pp );
$wd_lk   = wd_seller_look( $wd_pp );
$wd_fs   = wd_fonts();
$wd_lg   = $wd_lk['logo'] ? $wd_lk['logo'] : (int) $wd_pp['logo'];
$wd_gf   = array_values( array_filter( wp_list_pluck( $wd_fs, 2 ) ) );
$wd_pr   = (int) wd_opt( 'seller_premium_price' );
if ( ! $wd_prem ) {
	$wd_lk = array_merge( $wd_lk, array( 'primary' => '#0F5C4D', 'accent' => '#E8B04B', 'font' => 'playfair' ) );
}
$wd_sty = wd_seller_brand_style( array( 'primary' => $wd_lk['primary'], 'accent' => $wd_lk['accent'], 'on' => wd_text_on( $wd_lk['primary'] ), 'css' => $wd_fs[ $wd_lk['font'] ][1] ) );
?>
<link rel="stylesheet" href="<?php echo esc_url( 'https://fonts.googleapis.com/css2?family=' . implode( '&family=', $wd_gf ) . '&display=swap' ); ?>">
<div class="panel inv-look<?php echo $wd_prem ? '' : ' inv-look--locked'; ?>" id="invoice-look">
	<div class="panel__head"><h2><?php esc_html_e( 'Your invoice look', 'wwmd' ); ?></h2><?php if ( $wd_prem ) : ?><span class="pill pill--premium"><?php esc_html_e( 'Premium', 'wwmd' ); ?></span><?php endif; ?></div>
	<div class="inv-look__grid">
		<div class="inv-look__preview" aria-hidden="true">
			<div class="inv-mini" data-inv-mini style="<?php echo esc_attr( $wd_sty ); ?>">
				<div class="inv-mini__band">
					<span class="inv-mini__logo"><?php echo $wd_lg ? '<img src="' . esc_url( wd_img_url( $wd_lg, 'thumbnail' ) ) . '" alt="" data-inv-logo>' : wd_avatar( $wd_pp['business'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<b><?php echo esc_html( $wd_pp['business'] ); ?></b>
					<small><?php esc_html_e( 'Invoice', 'wwmd' ); ?> #A1B2C3</small>
				</div>
				<div class="inv-mini__body">
					<p><span><?php esc_html_e( 'Your service', 'wwmd' ); ?></span><b>₦150,000</b></p>
					<p class="inv-mini__total"><span><?php esc_html_e( 'Total', 'wwmd' ); ?></span><b>₦150,000</b></p>
					<span class="inv-mini__btn"><?php esc_html_e( 'Pay now', 'wwmd' ); ?></span>
				</div>
			</div>
		</div>
		<?php if ( $wd_prem ) : ?>
			<?php wd_market_form( 'invoice_look', array(), true, 'form' ); ?>
				<p class="hint"><?php esc_html_e( 'Every invoice, payment link & receipt you send in the chat carries these. Customers see them the moment they open it.', 'wwmd' ); ?></p>
				<div class="field-row">
					<label class="field field--color"><span><?php esc_html_e( 'Main colour', 'wwmd' ); ?></span><input type="color" name="primary" value="<?php echo esc_attr( $wd_lk['primary'] ); ?>" data-inv="primary"></label>
					<label class="field field--color"><span><?php esc_html_e( 'Second colour', 'wwmd' ); ?></span><input type="color" name="accent" value="<?php echo esc_attr( $wd_lk['accent'] ); ?>" data-inv="accent"></label>
				</div>
				<label class="field"><span><?php esc_html_e( 'Font', 'wwmd' ); ?></span>
					<select name="font" data-inv="font">
						<?php foreach ( $wd_fs as $wd_k => $wd_f ) : ?>
							<option value="<?php echo esc_attr( $wd_k ); ?>" data-css="<?php echo esc_attr( $wd_f[1] ); ?>" <?php selected( $wd_lk['font'], $wd_k ); ?>><?php echo esc_html( $wd_f[0] ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label class="field file-field"><span><?php esc_html_e( 'Logo for invoices (square, or leave empty to use your profile logo)', 'wwmd' ); ?></span><input type="file" name="inv_logo_file" accept="image/jpeg,image/png,image/webp" data-inv="logo"></label>
				<?php if ( $wd_lk['logo'] ) : ?><label class="check"><input type="checkbox" name="remove_inv_logo" value="1"><span><?php esc_html_e( 'Use my profile logo instead', 'wwmd' ); ?></span></label><?php endif; ?>
				<div class="btn-row">
					<button class="btn btn--ink" type="submit"><?php esc_html_e( 'Save invoice look', 'wwmd' ); ?></button>
					<button class="btn btn--ghost" type="submit" name="reset" value="1"><?php esc_html_e( 'Back to our purple', 'wwmd' ); ?></button>
				</div>
			</form>
		<?php else : ?>
			<div class="inv-look__pitch">
				<h3><?php esc_html_e( 'Send invoices in your own colours', 'wwmd' ); ?></h3>
				<p><?php esc_html_e( 'Premium sellers pick their colours & font & add their logo. Every invoice, payment link & receipt they send then looks like it came from their own shop.', 'wwmd' ); ?></p>
				<?php if ( $wd_pr ) : ?><p class="inv-look__price"><b><?php echo esc_html( wd_money( $wd_pr ) ); ?></b> <?php esc_html_e( 'a month', 'wwmd' ); ?></p><?php endif; ?>
				<a class="btn btn--wa" href="<?php echo esc_url( wd_whatsapp( sprintf( /* translators: %s business */ __( 'Hello, I sell on your marketplace as %s. I would like Premium for my invoices.', 'wwmd' ), $wd_pp['business'] ) ) ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'whatsapp', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Ask us about Premium', 'wwmd' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</div>
