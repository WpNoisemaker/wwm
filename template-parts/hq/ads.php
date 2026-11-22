<?php
/**
 * HQ: the two sponsored slots on free wedding pages.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_ads = array_values( (array) wd_opt( 'ads' ) );
?>
<div class="app-head"><div><p class="eyebrow"><?php esc_html_e( 'HQ', 'wwmd' ); ?></p><h1><?php esc_html_e( 'Ads on free pages', 'wwmd' ); ?></h1><p><?php echo esc_html( sprintf( /* translators: %s price */ __( 'Free wedding pages show at most two sponsored cards. Premium pages never show ads. Suggested price to vendors: %s a week.', 'wwmd' ), wd_money( wd_opt( 'ad_week_price' ) ) ) ); ?></p></div></div>
<?php wd_hq_form( 'ads', 'panel form-grid' ); ?>
	<?php for ( $wd_i = 0; $wd_i < 2; $wd_i++ ) : $wd_a = wp_parse_args( $wd_ads[ $wd_i ] ?? array(), array( 'on' => 0, 'title' => '', 'text' => '', 'url' => '', 'cta' => '', 'image' => '', 'tone' => 'butter' ) ); ?>
		<fieldset class="ad-slot">
			<legend><?php echo esc_html( sprintf( /* translators: %d */ __( 'Slot %d', 'wwmd' ), $wd_i + 1 ) ); ?></legend>
			<label class="check"><input type="checkbox" name="ads[<?php echo (int) $wd_i; ?>][on]" value="1" <?php checked( $wd_a['on'] ); ?>> <?php esc_html_e( 'Show this ad', 'wwmd' ); ?></label>
			<?php
			wd_field( 'ads[' . $wd_i . '][title]', __( 'Headline', 'wwmd' ), $wd_a['title'] );
			wd_field( 'ads[' . $wd_i . '][text]', __( 'Short line', 'wwmd' ), $wd_a['text'] );
			wd_field( 'ads[' . $wd_i . '][url]', __( 'Link', 'wwmd' ), $wd_a['url'], 'url' );
			wd_field( 'ads[' . $wd_i . '][cta]', __( 'Button text', 'wwmd' ), $wd_a['cta'] );
			wd_field( 'ads[' . $wd_i . '][image]', __( 'Image link (optional)', 'wwmd' ), $wd_a['image'], 'url' );
			?>
			<label class="field"><span><?php esc_html_e( 'Card colour', 'wwmd' ); ?></span><select name="ads[<?php echo (int) $wd_i; ?>][tone]"><?php foreach ( array( 'butter', 'lavender', 'mint', 'coral', 'sky' ) as $wd_t ) : ?><option value="<?php echo esc_attr( $wd_t ); ?>" <?php selected( $wd_a['tone'], $wd_t ); ?>><?php echo esc_html( ucfirst( $wd_t ) ); ?></option><?php endforeach; ?></select></label>
		</fieldset>
	<?php endfor; ?>
	<div class="form-actions"><button class="btn btn--brand" type="submit"><?php esc_html_e( 'Save ads', 'wwmd' ); ?></button></div>
</form>
