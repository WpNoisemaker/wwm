<?php
/**
 * FAQs: questions on the left, the answer on the right (an accordion on phones).
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_faq = array(
	array( __( 'Is the free wedding page really free?', 'wwmd' ), __( 'Yes. Your page, invitation & programs, RSVP, love story, wall of love, vendor list & cash & gift registry are free for as long as you want them. When guests send cash gifts through a free page, a small service fee is added, which guests can choose to cover. Free pages also show two small sponsored slots & earn chart coins in fewer ways.', 'wwmd' ) ),
	array( __( 'What are the four plans?', 'wwmd' ), sprintf( /* translators: 1: Pro price 2: Premium price 3: cards */ __( 'Free has the essentials. Pro (%1$s) adds your bridesmaids & groomsmen line-up, guest photos, the quiz, Instagram, entry passes, your own colours & fonts, a verified badge & no ads. Premium (%2$s) adds a Premium design, livestream, spraying, %3$d printed table QR cards & no transaction charges. High Society is priced for your wedding & brands the whole event: prints, signage & livestream coverage by our crew.', 'wwmd' ), wd_money( wd_opt( 'pro_price' ) ), wd_money( wd_opt( 'premium_price' ) ), (int) wd_opt( 'premium_qr_cards' ) ) ),
	array( __( 'Can I start on Pro & move up later?', 'wwmd' ), __( 'Yes. Pro & Premium are paid once per wedding, in one go or in instalments, & moving up only costs the difference. Your content stays exactly as it is.', 'wwmd' ) ),
	array( __( 'How does spraying work?', 'wwmd' ), __( 'Guests choose a note, tap to spray as many as they like & pay by card, bank transfer or USSD. Family abroad pay with their card. The couple sees every spray on their dashboard & withdraws to their bank or mobile money account.', 'wwmd' ) ),
	array( __( 'How are weddings ranked?', 'wwmd' ), __( 'By Buzz coins. Every visit, RSVP, love note, like, review, gift, guest photo, quiz play, check-in, livestream viewer & spray earns the couple coins. A page only earns coins from the features on its plan, so Free & Pro pages have fewer ways to earn. Coins only come from real guest activity, & sponsored spots never change the chart.', 'wwmd' ) ),
	array( __( 'Can people outside Nigeria use it?', 'wwmd' ), __( 'Yes. Couples in Ghana, Kenya, South Africa & the diaspora make pages too, & guests anywhere can RSVP, give & watch. Pages can take gifts in naira, cedis, shillings, rand or US dollars.', 'wwmd' ) ),
	array( __( 'Can I hide my wedding page?', 'wwmd' ), __( 'Yes. You can make your page private at any time from your dashboard. Private pages are not listed, ranked or searchable, & only you can see them.', 'wwmd' ) ),
);
?>?>
<section class="section section--faq">
	<div class="wrap">
		<div class="qa" data-qa>
			<div class="qa__q">
				<h2 class="qa__big"><?php esc_html_e( 'FAQs.', 'wwmd' ); ?></h2>
				<ul>
					<?php foreach ( $wd_faq as $wd_i => $wd_q ) : ?>
						<li>
							<button type="button" class="qa__btn<?php echo 0 === $wd_i ? ' is-on' : ''; ?>" aria-expanded="<?php echo 0 === $wd_i ? 'true' : 'false'; ?>" aria-controls="qa-<?php echo (int) $wd_i; ?>" data-qa-btn><?php echo esc_html( $wd_q[0] ); ?><?php echo wd_icon( 'plus', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
							<div class="qa__inline" id="qa-<?php echo (int) $wd_i; ?>" <?php echo 0 === $wd_i ? '' : 'hidden'; ?>><p><?php echo esc_html( $wd_q[1] ); ?></p></div>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="qa__a" aria-hidden="true">
				<p class="qa__big"><?php esc_html_e( 'Ans.', 'wwmd' ); ?></p>
				<div class="qa__panel" data-qa-panel>
					<span class="qa__dot"></span>
					<p><?php echo esc_html( $wd_faq[0][1] ); ?></p>
				</div>
			</div>
		</div>
	</div>
</section>
