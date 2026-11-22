<?php
/**
 * HQ: settings. Prices, fees, plan features, ranking coins, payment & Instagram keys.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_s   = wd_settings( true );
?>
<div class="app-head"><div><p class="eyebrow"><?php esc_html_e( 'HQ', 'wwmd' ); ?></p><h1><?php esc_html_e( 'Settings', 'wwmd' ); ?></h1></div></div>
<?php wd_hq_form( 'settings', 'settings-form' ); ?>
	<div class="panel form-grid">
		<h2><?php esc_html_e( 'Appearance', 'wwmd' ); ?></h2>
		<label class="field">
			<span><?php esc_html_e( 'Colour scheme the site opens in', 'wwmd' ); ?></span>
			<select name="theme_mode">
				<?php
				foreach ( array(
					'auto'  => __( 'Match each visitor’s device', 'wwmd' ),
					'light' => __( 'Always light', 'wwmd' ),
					'dark'  => __( 'Always dark', 'wwmd' ),
				) as $wd_k => $wd_l ) :
					?>
					<option value="<?php echo esc_attr( $wd_k ); ?>" <?php selected( wd_theme_default(), $wd_k ); ?>><?php echo esc_html( $wd_l ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<label class="check"><input type="checkbox" name="glass" value="1" <?php checked( wd_opt( 'glass' ) ); ?>> <?php esc_html_e( 'Frosted glass surfaces', 'wwmd' ); ?></label>
		<p class="hint span-all"><?php esc_html_e( 'Whatever you choose here, every visitor can still switch between light and dark from the header, and their choice is remembered on their own device. Visitors who have asked their system for less transparency or less motion always get the plain, still version.', 'wwmd' ); ?></p>
	</div>

	<div class="panel form-grid">
		<h2><?php esc_html_e( 'Notifications', 'wwmd' ); ?></h2>
		<p class="hint span-all"><?php esc_html_e( 'Email is always on. WhatsApp and SMS are off, and nothing is ever sent on them — and nobody is offered them anywhere on the site — until you switch a channel on here AND fill in everything it needs below. Switch a channel off again and it stops immediately, even for people who had chosen it.', 'wwmd' ); ?></p>
		<?php foreach ( wd_notify_channels() as $wd_ck => $wd_c ) : ?>
			<?php if ( ! empty( $wd_c['always'] ) ) : ?>
				<p class="span-all"><b><?php echo esc_html( $wd_c['label'] ); ?></b> — <?php esc_html_e( 'always on.', 'wwmd' ); ?></p>
			<?php else : ?>
				<div class="span-all">
					<label class="check">
						<input type="checkbox" name="notify_<?php echo esc_attr( $wd_ck ); ?>" value="1" <?php checked( wd_opt( 'notify_' . $wd_ck ) ); ?>>
						<?php /* translators: %s channel */ printf( esc_html__( 'Send on %s', 'wwmd' ), esc_html( $wd_c['label'] ) ); ?>
						<?php if ( wd_opt( 'notify_' . $wd_ck ) && ! wd_notify_on( $wd_ck ) ) : ?>
							<em class="tag"><?php esc_html_e( 'Not set up yet — nothing is being sent', 'wwmd' ); ?></em>
						<?php elseif ( wd_notify_on( $wd_ck ) ) : ?>
							<em class="tag tag--done"><?php esc_html_e( 'Live', 'wwmd' ); ?></em>
						<?php endif; ?>
					</label>
					<?php if ( ! empty( $wd_c['note'] ) ) : ?><p class="hint"><?php echo esc_html( $wd_c['note'] ); ?></p><?php endif; ?>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
		<?php
		wd_field( 'wa_phone_id', __( 'WhatsApp phone number ID', 'wwmd' ), wd_opt( 'wa_phone_id' ), 'text', 'autocomplete="off"' );
		wd_field( 'wa_token', __( 'WhatsApp permanent token', 'wwmd' ), '', 'password', 'autocomplete="new-password" placeholder="' . esc_attr( wd_opt( 'wa_token' ) ? __( 'Saved — leave blank to keep it', 'wwmd' ) : '' ) . '"' );
		?>
		<?php if ( wd_opt( 'wa_token' ) ) : ?><label class="check"><input type="checkbox" name="clear_wa_token" value="1"> <?php esc_html_e( 'Remove the saved WhatsApp token', 'wwmd' ); ?></label><?php endif; ?>
		<?php
		wd_field( 'sms_sender', __( 'SMS sender ID', 'wwmd' ), wd_opt( 'sms_sender' ), 'text', 'autocomplete="off" maxlength="11"' );
		wd_field( 'sms_key', __( 'SMS gateway API key', 'wwmd' ), '', 'password', 'autocomplete="new-password" placeholder="' . esc_attr( wd_opt( 'sms_key' ) ? __( 'Saved — leave blank to keep it', 'wwmd' ) : '' ) . '"' );
		?>
		<?php if ( wd_opt( 'sms_key' ) ) : ?><label class="check"><input type="checkbox" name="clear_sms_key" value="1"> <?php esc_html_e( 'Remove the saved SMS key', 'wwmd' ); ?></label><?php endif; ?>
		<p class="hint span-all"><?php esc_html_e( 'Keys are stored with your other settings and are never shown again once saved. A channel also needs a gateway connected in code before a message can leave the site, so switching one on by itself is safe.', 'wwmd' ); ?></p>
	</div>

	<div class="panel form-grid">
		<h2><?php esc_html_e( 'Anonymous community', 'wwmd' ); ?></h2>
		<?php wd_field( 'talk_mark', __( 'Watermark on every post', 'wwmd' ), wd_opt( 'talk_mark' ), 'text', 'maxlength="40" placeholder="weddingwebsites.io"' ); ?>
		<p class="hint span-all"><?php esc_html_e( 'The watermark is drawn twice on each post: small in the corner, and running corner to corner behind the words, light enough to read straight through. It is also burned into the picture when somebody shares a post.', 'wwmd' ); ?></p>
	</div>

	<div class="panel form-grid">
		<h2><?php esc_html_e( 'Contact', 'wwmd' ); ?></h2>
		<?php
		wd_field( 'whatsapp', __( 'WhatsApp number (with country code, digits only)', 'wwmd' ), $wd_s['whatsapp'], 'tel' );
		wd_field( 'email', __( 'Email', 'wwmd' ), $wd_s['email'], 'email' );
		?>
	</div>

	<div class="panel form-grid">
		<h2><?php esc_html_e( 'Prices (₦)', 'wwmd' ); ?></h2>
		<?php
		wd_field( 'pro_price', __( 'Pro page', 'wwmd' ), $wd_s['pro_price'], 'number' );
		wd_field( 'premium_price', __( 'Premium page', 'wwmd' ), $wd_s['premium_price'], 'number' );
		wd_field( 'premium_qr_cards', __( 'Table QR cards included with Premium', 'wwmd' ), $wd_s['premium_qr_cards'], 'number' );
		wd_field( 'instalments', __( 'Instalments allowed for Pro & Premium', 'wwmd' ), $wd_s['instalments'], 'number', 'min="1" max="6"' );
		wd_field( 'extra_qr_price', __( 'Extra QR cards, price per pack', 'wwmd' ), $wd_s['extra_qr_price'], 'number' );
		wd_field( 'extra_qr_qty', __( 'Cards per extra pack', 'wwmd' ), $wd_s['extra_qr_qty'], 'number' );
		wd_field( 'spotlight_price', __( 'Homepage spotlight, per week', 'wwmd' ), $wd_s['spotlight_price'], 'number' );
		wd_field( 'vendor_pro_price', __( 'Verified vendor badge, per month', 'wwmd' ), $wd_s['vendor_pro_price'], 'number' );
		wd_field( 'seller_premium_price', __( 'Premium seller (own colours, font & logo on invoices), per month. 0 = ask us on WhatsApp', 'wwmd' ), $wd_s['seller_premium_price'], 'number' );
		wd_field( 'ad_week_price', __( 'Ad slot on free pages, per week', 'wwmd' ), $wd_s['ad_week_price'], 'number' );
		wd_field( 'custom_design_price', __( 'Custom design for a Premium page', 'wwmd' ), $wd_s['custom_design_price'], 'number' );
		?>
		<p class="hint span-all"><?php esc_html_e( 'High Society has no fixed price: couples ask for a quote on WhatsApp & you switch it on in HQ › Weddings.', 'wwmd' ); ?></p>
	</div>

	<div class="panel form-grid">
		<h2><?php esc_html_e( 'Service fees', 'wwmd' ); ?></h2>
		<p class="hint span-all"><?php esc_html_e( 'Guests see the fee before they pay & can choose to cover it for the couple. Plans with “No transaction charges” pay no fees.', 'wwmd' ); ?></p>
		<?php
		wd_field( 'fee_gift', __( 'Cash gifts & registry (%)', 'wwmd' ), $wd_s['fee_gift'], 'number', 'step="0.1" min="0" max="30"' );
		wd_field( 'fee_spray', __( 'Spray money (%)', 'wwmd' ), $wd_s['fee_spray'], 'number', 'step="0.1" min="0" max="30"' );
		wd_field( 'fee_cap', __( 'Fee cap per payment in ₦ (0 = no cap)', 'wwmd' ), $wd_s['fee_cap'], 'number' );
		wd_field( 'payout_min', __( 'Smallest payout (₦)', 'wwmd' ), $wd_s['payout_min'], 'number' );
		?>
	</div>

	<div class="panel">
		<h2><?php esc_html_e( 'What each plan includes', 'wwmd' ); ?></h2>
		<p class="hint"><?php esc_html_e( 'Tick a feature to give it to that plan. For example, tick “Spray money” under Free to let free pages take sprays (with the spray fee above). A plan only earns chart coins from the features it has.', 'wwmd' ); ?></p>
		<div class="table-wrap"><table class="table table--matrix">
			<thead><tr><th><?php esc_html_e( 'Feature', 'wwmd' ); ?></th><?php foreach ( wd_plan_keys() as $wd_p ) : ?><th><?php echo esc_html( wd_plan_name( $wd_p ) ); ?></th><?php endforeach; ?></tr></thead>
			<tbody>
			<?php foreach ( wd_features() as $wd_k => $wd_l ) : ?>
				<tr><td><?php echo esc_html( $wd_l ); ?></td>
				<?php foreach ( wd_plan_keys() as $wd_p ) : ?>
					<td><input type="checkbox" name="features_<?php echo esc_attr( $wd_p ); ?>[]" value="<?php echo esc_attr( $wd_k ); ?>" <?php checked( in_array( $wd_k, (array) $wd_s[ 'features_' . $wd_p ], true ) ); ?> aria-label="<?php echo esc_attr( $wd_l . ' · ' . wd_plan_name( $wd_p ) ); ?>"></td>
				<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table></div>
	</div>

	<div class="panel form-grid">
		<h2><?php esc_html_e( 'Ranking coins', 'wwmd' ); ?></h2>
		<p class="hint span-all"><?php esc_html_e( 'How many Buzz coins each figure earns. 0 switches a figure off. A figure only earns coins on plans that have the feature behind it, so Free & Pro pages have fewer ways to earn.', 'wwmd' ); ?></p>
		<?php foreach ( wd_coin_figures() as $wd_k => $wd_x ) : ?>
			<?php wd_field( 'coins[' . $wd_k . ']', $wd_x['label'] . ' · ' . $wd_x['per'], wd_coin_rate( $wd_k ), 'number', 'step="1" min="0" max="1000"' ); ?>
		<?php endforeach; ?>
		<h3 class="span-all"><?php esc_html_e( 'Exchange rates, for ranking only (₦ per 1 unit)', 'wwmd' ); ?></h3>
		<?php foreach ( wd_currencies() as $wd_c => $wd_x ) : ?>
			<?php
			if ( 'NGN' === $wd_c ) {
				continue;
			}
			wd_field( 'rates[' . $wd_c . ']', $wd_c, $wd_s['rates'][ $wd_c ] ?? 1, 'number', 'step="0.01" min="0"' );
			?>
		<?php endforeach; ?>
	</div>

	<div class="panel form-grid">
		<h2><?php esc_html_e( 'Services marketplace', 'wwmd' ); ?></h2>
		<?php
		wd_field( 'market_fee', __( 'Our commission on payment links paid on the site (%). Invoices sellers are paid directly carry no commission.', 'wwmd' ), $wd_s['market_fee'], 'number', 'step="0.5" min="0" max="50"' );
		?>
	</div>

	<div class="panel form-grid">
		<h2><?php esc_html_e( 'AI help chat', 'wwmd' ); ?></h2>
		<p class="hint span-all"><?php esc_html_e( 'The help chat answers questions about the site. With no API key it still answers from the built-in help topics. Add an Anthropic API key from console.anthropic.com for full answers.', 'wwmd' ); ?></p>
		<label class="check span-all"><input type="checkbox" name="ai_on" value="1" <?php checked( $wd_s['ai_on'] ); ?>> <?php esc_html_e( 'Show the help chat on the site', 'wwmd' ); ?></label>
		<label class="field"><span><?php esc_html_e( 'Model', 'wwmd' ); ?></span><select name="ai_model"><?php foreach ( wd_ai_models() as $wd_k => $wd_l ) : ?><option value="<?php echo esc_attr( $wd_k ); ?>" <?php selected( $wd_s['ai_model'], $wd_k ); ?>><?php echo esc_html( $wd_l ); ?></option><?php endforeach; ?></select></label>
		<label class="field"><span><?php esc_html_e( 'Anthropic API key', 'wwmd' ); ?></span><input type="password" name="ai_key" value="" autocomplete="new-password" placeholder="<?php echo $wd_s['ai_key'] ? esc_attr__( 'Saved. Leave blank to keep it', 'wwmd' ) : 'sk-ant-…'; ?>"></label>
		<?php if ( $wd_s['ai_key'] ) : ?><label class="check"><input type="checkbox" name="clear_ai_key" value="1"> <?php esc_html_e( 'Remove the saved key', 'wwmd' ); ?></label><?php endif; ?>
	</div>

	<div class="panel form-grid">
		<h2><?php esc_html_e( 'Payments', 'wwmd' ); ?></h2>
		<p class="hint span-all"><?php echo esc_html( sprintf( /* translators: %s webhook URL */ __( 'Payments run on Bachs (bachs.io), with instant settlement. Paste your secret key from the Bachs dashboard: sk_sandbox_ keys use the sandbox, sk_live_ keys take real money. Then add a webhook for checkout.completed & collection.succeeded that points to %s & paste its signing secret here.', 'wwmd' ), rest_url( 'wd/v1/pay/hook' ) ) ); ?></p>
		<?php foreach ( array( 'bachs_secret' => __( 'Bachs secret key', 'wwmd' ), 'bachs_webhook_secret' => __( 'Webhook signing secret', 'wwmd' ) ) as $wd_k => $wd_l ) : ?>
			<label class="field"><span><?php echo esc_html( $wd_l ); ?></span><input type="password" name="<?php echo esc_attr( $wd_k ); ?>" value="" autocomplete="new-password" placeholder="<?php echo $wd_s[ $wd_k ] ? esc_attr__( 'Saved. Leave blank to keep it', 'wwmd' ) : ''; ?>"></label>
			<?php if ( $wd_s[ $wd_k ] ) : ?><label class="check"><input type="checkbox" name="clear_<?php echo esc_attr( $wd_k ); ?>" value="1"> <?php esc_html_e( 'Remove the saved value', 'wwmd' ); ?></label><?php endif; ?>
		<?php endforeach; ?>
		<p class="hint span-all"><?php echo wd_bachs_key() ? esc_html( 0 === strpos( wd_bachs_key(), 'sk_live_' ) ? __( 'Live: real payments are on.', 'wwmd' ) : __( 'Sandbox: payments go through the Bachs sandbox, no real money.', 'wwmd' ) ) : esc_html__( 'No key saved yet.', 'wwmd' ); ?></p>
		<label class="check span-all"><input type="checkbox" name="test_payments" value="1" <?php checked( $wd_s['test_payments'] ); ?>> <?php esc_html_e( 'Test mode when no secret key is saved (payments are recorded but no money moves)', 'wwmd' ); ?></label>
	</div>

	<div class="panel form-grid">
		<h2><?php esc_html_e( 'Instagram', 'wwmd' ); ?></h2>
		<p class="hint span-all"><?php echo esc_html( sprintf( /* translators: %s redirect URL */ __( 'With a Meta app (Instagram API with Instagram Login), couples connect their wedding account in one tap & we read likes & comments for ranking. OAuth redirect URL: %s', 'wwmd' ), wd_ig_redirect() ) ); ?></p>
		<?php wd_field( 'ig_app_id', __( 'Instagram app ID', 'wwmd' ), $wd_s['ig_app_id'] ); ?>
		<label class="field"><span><?php esc_html_e( 'Instagram app secret', 'wwmd' ); ?></span><input type="password" name="ig_app_secret" value="" autocomplete="new-password" placeholder="<?php echo $wd_s['ig_app_secret'] ? esc_attr__( 'Saved. Leave blank to keep it', 'wwmd' ) : ''; ?>"></label>
		<?php if ( $wd_s['ig_app_secret'] ) : ?><label class="check"><input type="checkbox" name="clear_ig_app_secret" value="1"> <?php esc_html_e( 'Remove the saved app secret', 'wwmd' ); ?></label><?php endif; ?>
	</div>

	<div class="panel form-grid">
		<h2><?php esc_html_e( 'Moderation', 'wwmd' ); ?></h2>
		<label class="check span-all"><input type="checkbox" name="moderate_wall" value="1" <?php checked( $wd_s['moderate_wall'] ); ?>> <?php esc_html_e( 'Hold new love notes until approved', 'wwmd' ); ?></label>
		<label class="check span-all"><input type="checkbox" name="moderate_photos" value="1" <?php checked( $wd_s['moderate_photos'] ); ?>> <?php esc_html_e( 'Hold new guest photos until approved', 'wwmd' ); ?></label>
		<label class="check span-all"><input type="checkbox" name="moderate_talk" value="1" <?php checked( $wd_s['moderate_talk'] ); ?>> <?php esc_html_e( 'Hold new anonymous community posts & replies until approved (posts reported by 3 people are always held)', 'wwmd' ); ?></label>
	</div>

	<div class="panel form-grid">
		<h2><?php esc_html_e( 'Motion', 'wwmd' ); ?></h2>
		<label class="check span-all"><input type="checkbox" name="preloader" value="1" <?php checked( $wd_s['preloader'] ); ?>> <?php esc_html_e( 'Show a short loading screen with the logo while pages open', 'wwmd' ); ?></label>
		<label class="check span-all"><input type="checkbox" name="home_fx" value="1" <?php checked( $wd_s['home_fx'] ); ?>> <?php esc_html_e( 'Confetti & balloons when the homepage opens', 'wwmd' ); ?></label>
	</div>

	<div class="panel form-grid" id="news">
		<h2><?php esc_html_e( 'Wedding news on the blog', 'wwmd' ); ?></h2>
		<p class="hint span-all"><?php esc_html_e( 'Headlines from other wedding sites, shown under your own posts with a link back to each full story. Add the RSS feed of each site, one per line (most WordPress sites have one at /feed/). Headlines update every hour.', 'wwmd' ); ?></p>
		<label class="check span-all"><input type="checkbox" name="news_on" value="1" <?php checked( $wd_s['news_on'] ); ?>> <?php esc_html_e( 'Show wedding news on the blog', 'wwmd' ); ?></label>
		<div class="span-all"><?php wd_field( 'news_feeds', __( 'Feeds', 'wwmd' ), $wd_s['news_feeds'], 'textarea', 'rows="4" placeholder="https://example.com/feed/"' ); ?></div>
		<?php $wd_nc = count( (array) get_option( 'wd_news', array() ) ); ?>
		<p class="hint span-all"><?php echo esc_html( $wd_nc ? sprintf( /* translators: %d headlines */ _n( '%d headline ready to show.', '%d headlines ready to show.', $wd_nc, 'wwmd' ), $wd_nc ) : __( 'No headlines yet. If this stays empty after you save, check the feed links open in your browser.', 'wwmd' ) ); ?></p>
	</div>

	<div class="panel form-grid" id="news-import">
		<h2><?php esc_html_e( 'Full stories to rewrite', 'wwmd' ); ?></h2>
		<p class="hint span-all"><?php esc_html_e( 'Every hour, new stories from these sites come in with all their pictures, as drafts in Posts. Nothing goes live by itself: these sites own their words & photos, & copied stories can hurt your place on Google. Rewrite each draft in your own words, keep or change the pictures, then publish it. Each draft keeps a "Source" link at the bottom.', 'wwmd' ); ?></p>
		<label class="check span-all"><input type="checkbox" name="news_import" value="1" <?php checked( $wd_s['news_import'] ); ?>> <?php esc_html_e( 'Bring in full stories as drafts', 'wwmd' ); ?></label>
		<div class="span-all"><?php wd_field( 'news_import_feeds', __( 'Sites (RSS feeds)', 'wwmd' ), $wd_s['news_import_feeds'], 'textarea', 'rows="6" placeholder="https://example.com/feed/"' ); ?></div>
		<?php
		$wd_nd   = (int) ( new WP_Query(
			array(
				'post_type'      => 'post',
				'post_status'    => 'draft',
				'meta_key'       => '_wd_news_src', // phpcs:ignore WordPress.DB.SlowDBQuery
				'fields'         => 'ids',
				'posts_per_page' => 1,
			)
		) )->found_posts;
		$wd_last = (array) get_option( 'wd_news_import_last', array() );
		?>
		<p class="hint span-all"><?php echo esc_html( sprintf( /* translators: 1: drafts waiting 2: last time */ __( 'Drafts waiting for you: %1$d. Last check: %2$s.', 'wwmd' ), $wd_nd, $wd_last ? wd_ago( gmdate( 'Y-m-d H:i:s', (int) $wd_last[0] ) ) : __( 'not yet', 'wwmd' ) ) ); ?> <a href="<?php echo esc_url( admin_url( 'edit.php?post_status=draft&category_name=wedding-news' ) ); ?>"><?php esc_html_e( 'Open the drafts', 'wwmd' ); ?></a></p>
		<p class="span-all"><button class="btn btn--ghost btn--sm" type="submit" name="news_pull" value="1"><?php esc_html_e( 'Save & bring in stories now', 'wwmd' ); ?></button></p>
	</div>

	<div class="panel form-grid">
		<h2><?php esc_html_e( 'Sign in & sign up page', 'wwmd' ); ?></h2>
		<p class="hint span-all"><?php esc_html_e( 'The short header above the sign-in box, telling people what a wedding page gives them.', 'wwmd' ); ?></p>
		<div class="span-all"><?php wd_field( 'auth_title', __( 'Heading', 'wwmd' ), $wd_s['auth_title'] ); ?></div>
		<div class="span-all"><?php wd_field( 'auth_text', __( 'Description', 'wwmd' ), $wd_s['auth_text'], 'textarea', 'rows="5"' ); ?></div>
	</div>

	<div class="panel panel--soft">
		<h2><?php esc_html_e( 'Menus & site text', 'wwmd' ); ?></h2>
		<p><?php esc_html_e( 'Edit the header, footer & legal links from the Menus tab. Theme updates never overwrite your menus, settings or page text.', 'wwmd' ); ?></p>
		<a class="btn btn--ghost btn--sm" href="<?php echo esc_url( wd_url( 'hq', array( 'tab' => 'menus' ) ) ); ?>"><?php esc_html_e( 'Edit menus', 'wwmd' ); ?></a>
	</div>

	<div class="form-actions form-actions--sticky"><button class="btn btn--brand" type="submit"><?php esc_html_e( 'Save settings', 'wwmd' ); ?></button></div>
</form>
