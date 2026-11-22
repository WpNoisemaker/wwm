<?php
/**
 * A chat with a seller, on the site (direct-message style). Used on listings, seller profiles, dashboards & in HQ.
 *
 * Args: partner, user (the customer), role (user|partner|hq), listing (what it is about, optional),
 * title (optional heading).
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_pid  = (int) $args['partner'];
$wd_uid  = (int) $args['user'];
$wd_role = (string) $args['role'];
$wd_lid  = (int) ( $args['listing'] ?? 0 );
$wd_pp   = wd_partner( $wd_pid );
$wd_rows = $wd_uid ? wd_chat_rows( $wd_pid, $wd_uid ) : array();
if ( $wd_rows ) {
	wd_chat_seen( $wd_pid, $wd_uid, $wd_role );
}
$wd_cid  = 'chat-' . $wd_pid . '-' . $wd_uid;
$wd_mine = array();
if ( 'partner' === $wd_role ) {
	$wd_mine = get_posts(
		array(
			'post_type'   => 'wd_listing',
			'post_status' => 'publish',
			'author'      => $wd_pid,
			'numberposts' => 50,
		)
	);
	if ( ! $wd_lid && $wd_rows ) {
		foreach ( array_reverse( $wd_rows ) as $wd_r ) {
			if ( $wd_r->listing_id ) {
				$wd_lid = (int) $wd_r->listing_id;
				break;
			}
		}
	}
}
?>
<?php $wd_back = (string) ( $args['back'] ?? '' ); ?>
<div class="chat chat--<?php echo esc_attr( $wd_role ); ?><?php echo $wd_back ? ' chat--sheet' : ''; ?>" id="<?php echo esc_attr( $wd_cid ); ?>" data-chat data-partner="<?php echo (int) $wd_pid; ?>" data-user="<?php echo (int) $wd_uid; ?>" data-listing="<?php echo (int) $wd_lid; ?>" data-last="<?php echo $wd_rows ? (int) end( $wd_rows )->id : 0; ?>" data-max-digits="<?php echo 'hq' === $wd_role ? 0 : (int) wd_chat_max_digits(); ?>" data-digits-msg="<?php echo esc_attr( wd_chat_digits_msg() ); ?>">
	<?php if ( ! empty( $args['title'] ) ) : ?><h2 class="chat__title"><?php echo esc_html( $args['title'] ); ?></h2><?php endif; ?>
	<?php if ( 'user' === $wd_role && $wd_pp ) : ?>
		<header class="chat__head"><?php if ( $wd_back ) : ?><a class="chat__back" href="<?php echo esc_url( $wd_back ); ?>" aria-label="<?php esc_attr_e( 'Back to messages', 'wwmd' ); ?>"><?php echo wd_icon( 'arrow', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a><?php endif; ?><span class="chat__head-pic"><?php echo wd_partner_logo( $wd_pp, 44 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><span class="chat__head-id"><b><?php echo esc_html( $wd_pp['business'] ); ?> <?php echo wd_partner_badge( $wd_pid ); // phpcs:ignore WordPress.Security.EscapeOutput ?></b><small><?php echo esc_html( trim( $wd_pp['city'] . ', ' . $wd_pp['country'], ', ' ) ); ?></small></span><a class="linkish" href="<?php echo esc_url( wd_partner_url( $wd_pid ) ); ?>"><?php esc_html_e( 'View profile', 'wwmd' ); ?></a></header>
	<?php elseif ( $wd_uid && get_userdata( $wd_uid ) ) : ?>
		<header class="chat__head"><?php if ( $wd_back ) : ?><a class="chat__back" href="<?php echo esc_url( $wd_back ); ?>" aria-label="<?php esc_attr_e( 'Back to messages', 'wwmd' ); ?>"><?php echo wd_icon( 'arrow', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a><?php endif; ?><span class="chat__head-pic"><?php echo wd_avatar( get_userdata( $wd_uid )->display_name ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><span class="chat__head-id"><b><?php echo esc_html( get_userdata( $wd_uid )->display_name ); ?></b><small><?php echo 'hq' === $wd_role && $wd_pp ? esc_html( sprintf( /* translators: %s seller */ __( 'Chatting with %s', 'wwmd' ), $wd_pp['business'] ) ) : esc_html__( 'Customer', 'wwmd' ); ?></small></span></header>
	<?php endif; ?>
	<ol class="chat__log" data-chat-log aria-live="polite" aria-label="<?php esc_attr_e( 'Messages', 'wwmd' ); ?>"><?php echo wd_chat_render( $wd_rows, $wd_role ); // phpcs:ignore WordPress.Security.EscapeOutput ?></ol>
	<?php if ( ! $wd_rows ) : ?>
		<p class="chat__empty" data-chat-empty><?php echo esc_html( 'user' === $wd_role ? sprintf( /* translators: %s partner */ __( 'Say hello to %s. Ask about dates, prices or anything else. They answer right here & send you an invoice or a payment link.', 'wwmd' ), $wd_pp ? $wd_pp['business'] : __( 'the seller', 'wwmd' ) ) : __( 'No messages yet.', 'wwmd' ) ); ?></p>
	<?php endif; ?>
	<?php if ( 'user' === $wd_role || $wd_rows ) : ?>
		<form class="chat__form" data-chat-form>
			<label class="screen-reader-text" for="<?php echo esc_attr( $wd_cid ); ?>-text"><?php esc_html_e( 'Your message', 'wwmd' ); ?></label>
			<textarea id="<?php echo esc_attr( $wd_cid ); ?>-text" name="text" rows="2" maxlength="1000" required placeholder="<?php echo esc_attr( 'hq' === $wd_role ? __( 'Answer as Our team', 'wwmd' ) : __( 'Write a message', 'wwmd' ) ); ?>"></textarea>
			<button class="btn btn--ink" type="submit"><?php echo wd_icon( 'send', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Send', 'wwmd' ); ?></span></button>
		</form>
		<p class="form__msg" data-msg role="status"></p>
	<?php endif; ?>
	<?php if ( 'partner' === $wd_role && $wd_rows && $wd_mine ) : ?>
		<details class="chat__offer">
			<summary><?php echo wd_icon( 'cash', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Send an invoice or payment link', 'wwmd' ); ?></summary>
			<form class="form" data-chat-offer>
				<fieldset class="seg" data-kind>
					<legend class="screen-reader-text"><?php esc_html_e( 'What to send', 'wwmd' ); ?></legend>
					<label><input type="radio" name="kind" value="link" checked><span><?php echo wd_icon( 'link', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Payment link', 'wwmd' ); ?></span></label>
					<label><input type="radio" name="kind" value="invoice"><span><?php echo wd_icon( 'feed', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Invoice', 'wwmd' ); ?></span></label>
				</fieldset>
				<label class="field"><span><?php esc_html_e( 'For', 'wwmd' ); ?></span><select name="listing">
					<?php foreach ( $wd_mine as $wd_p ) : ?>
						<option value="<?php echo (int) $wd_p->ID; ?>" <?php selected( $wd_lid, $wd_p->ID ); ?>><?php echo esc_html( $wd_p->post_title ); ?></option>
					<?php endforeach; ?>
				</select></label>
				<fieldset class="pl-lines" data-lines>
					<legend><?php esc_html_e( 'Costs', 'wwmd' ); ?></legend>
					<div class="pl-line" data-line>
						<label class="field"><span class="screen-reader-text"><?php esc_html_e( 'What it is for', 'wwmd' ); ?></span><input name="item_t[]" maxlength="80" required placeholder="<?php esc_attr_e( 'What it is for, e.g. Gele tying', 'wwmd' ); ?>"></label>
						<label class="field"><span class="screen-reader-text"><?php esc_html_e( 'Price (₦)', 'wwmd' ); ?></span><input type="number" name="item_a[]" min="100" inputmode="numeric" required placeholder="₦"></label>
						<button class="pl-line__del" type="button" data-line-del aria-label="<?php esc_attr_e( 'Remove this cost', 'wwmd' ); ?>"><?php echo wd_icon( 'close', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
					</div>
				</fieldset>
				<p class="pl-sum"><button class="linkish" type="button" data-line-add><?php echo wd_icon( 'plus', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Add another cost', 'wwmd' ); ?></button><span><?php esc_html_e( 'Total', 'wwmd' ); ?> <b data-lines-total>₦0</b></span></p>
				<div class="field-row">
					<label class="field"><span><?php esc_html_e( 'Date', 'wwmd' ); ?></span><input type="date" name="start" min="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>" required></label>
					<label class="field"><span><?php esc_html_e( 'Until (optional)', 'wwmd' ); ?></span><input type="date" name="end" min="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>"></label>
				</div>
				<label class="field"><span><?php esc_html_e( 'Note (optional)', 'wwmd' ); ?></span><input name="text" maxlength="300" placeholder="<?php esc_attr_e( 'e.g. Includes delivery to your venue', 'wwmd' ); ?>"></label>
				<label class="field" data-kind-invoice hidden><span><?php esc_html_e( 'How to pay you', 'wwmd' ); ?></span><textarea name="info" rows="3" maxlength="600" placeholder="<?php esc_attr_e( 'e.g. Bank, account name & account number, or another way to pay', 'wwmd' ); ?>"><?php echo esc_textarea( (string) get_user_meta( $wd_pid, 'wd_pay_info', true ) ); ?></textarea></label>
				<button class="btn btn--marigold" type="submit"><?php echo wd_icon( 'send', 17 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span data-kind-label data-link="<?php esc_attr_e( 'Send payment link', 'wwmd' ); ?>" data-invoice="<?php esc_attr_e( 'Send invoice', 'wwmd' ); ?>"><?php esc_html_e( 'Send payment link', 'wwmd' ); ?></span></button>
				<p class="hint" data-kind-hint data-link="<?php echo esc_attr( sprintf( /* translators: %s commission */ __( 'The customer pays it on the site by card, bank or mobile money. Your share goes to your payout account straight away, less our %s commission.', 'wwmd' ), rtrim( rtrim( number_format( (float) wd_opt( 'market_fee' ), 1 ), '0' ), '.' ) . '%' ) ); ?>" data-invoice="<?php esc_attr_e( 'The customer pays you directly, the way you say. Tap “Mark as paid” in the chat once the money is in, & it becomes their receipt. No commission.', 'wwmd' ); ?>"><?php echo esc_html( sprintf( /* translators: %s commission */ __( 'The customer pays it on the site by card, bank or mobile money. Your share goes to your payout account straight away, less our %s commission.', 'wwmd' ), rtrim( rtrim( number_format( (float) wd_opt( 'market_fee' ), 1 ), '0' ), '.' ) . '%' ) ); ?></p>
				<p class="form__msg" data-msg role="status"></p>
			</form>
		</details>
	<?php endif; ?>
</div>
