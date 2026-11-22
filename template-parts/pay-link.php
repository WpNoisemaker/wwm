<?php
/**
 * An invoice or payment link's own page, at /pay/{code}/. Once paid, the same page is the receipt.
 * A payment link is paid here (only by the customer in the chat). An invoice shows how to pay the
 * seller directly (only to the customer, the seller & HQ); the seller marks it paid in the chat.
 *
 * Args: offer (the chat message with the invoice or payment link).
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_o    = $args['offer'];
$wd_pid  = (int) $wd_o->partner_id;
$wd_pp   = wd_partner( $wd_pid );
$wd_me   = wp_get_current_user();
$wd_role = $wd_me->ID ? wd_chat_role( $wd_pid, (int) $wd_o->user_id ) : '';
$wd_cust = get_userdata( (int) $wd_o->user_id );
$wd_st   = wd_offer_states();
$wd_url  = wd_offer_url( $wd_o );
$wd_inv  = wd_offer_is_invoice( $wd_o );
$wd_paid = 'paid' === $wd_o->offer_status;
$wd_in   = in_array( $wd_role, array( 'user', 'partner', 'hq' ), true );
$wd_bk   = $wd_paid && $wd_o->booking_id ? wd_booking( (int) $wd_o->booking_id ) : null;
$wd_chat = 'partner' === $wd_role ? wd_account_url( 'partner', array( 'part' => 'chat', 'user' => (int) $wd_o->user_id ) ) : wd_account_url( 'services', array( 'chat' => $wd_pid ) ) . '#chat';
$wd_bd   = wd_seller_brand( $wd_pp ); // Premium sellers' own colours, font & logo.
$wd_kind = $wd_paid ? __( 'Receipt', 'wwmd' ) : ( $wd_inv ? __( 'Invoice', 'wwmd' ) : __( 'Payment link', 'wwmd' ) );
?>
<main id="main" class="pay-page">
	<?php if ( $wd_bd && $wd_bd['gf'] ) : ?><link rel="stylesheet" href="<?php echo esc_url( 'https://fonts.googleapis.com/css2?family=' . $wd_bd['gf'] . '&display=swap' ); ?>"><?php endif; ?>
	<div class="wrap">
		<article class="invoice<?php echo $wd_paid ? ' invoice--paid' : ''; ?><?php echo $wd_bd ? ' invoice--brand' : ''; ?>"<?php echo $wd_bd ? ' style="' . esc_attr( wd_seller_brand_style( $wd_bd ) ) . '"' : ''; ?>>
			<header class="invoice__head">
				<?php if ( $wd_pp ) : ?>
					<a class="invoice__seller" href="<?php echo esc_url( wd_partner_url( $wd_pid ) ); ?>">
						<?php echo wd_partner_logo( $wd_bd ? array_merge( $wd_pp, array( 'logo' => $wd_bd['img'] ) ) : $wd_pp, $wd_bd ? 56 : 48 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<span><b><?php echo esc_html( $wd_pp['business'] ); ?></b><small><?php echo esc_html( trim( $wd_pp['city'] . ', ' . $wd_pp['country'], ', ' ) ); ?></small></span>
					</a>
				<?php endif; ?>
				<p class="invoice__no"><span><?php echo esc_html( $wd_kind ); ?></span><b>#<?php echo esc_html( strtoupper( $wd_o->offer_code ) ); ?></b><small><?php echo esc_html( wd_date( get_date_from_gmt( $wd_o->created, 'Y-m-d' ), 'j M Y' ) ); ?></small></p>
			</header>

			<dl class="invoice__meta">
				<?php if ( $wd_cust ) : ?><div><dt><?php esc_html_e( 'For', 'wwmd' ); ?></dt><dd><?php echo esc_html( $wd_cust->display_name ); ?></dd></div><?php endif; ?>
				<div><dt><?php esc_html_e( 'Service', 'wwmd' ); ?></dt><dd><a href="<?php echo esc_url( get_permalink( (int) $wd_o->listing_id ) ); ?>"><?php echo esc_html( get_the_title( (int) $wd_o->listing_id ) ); ?></a></dd></div>
				<div><dt><?php esc_html_e( 'Date', 'wwmd' ); ?></dt><dd><?php echo esc_html( wd_offer_when( $wd_o ) ); ?></dd></div>
				<?php if ( $wd_paid ) : ?><div><dt><?php esc_html_e( 'Paid', 'wwmd' ); ?></dt><dd><?php echo esc_html( ( $wd_bk && $wd_bk->paid_at ? wd_date( get_date_from_gmt( $wd_bk->paid_at, 'Y-m-d' ), 'j M Y' ) : __( 'Yes', 'wwmd' ) ) . ' · ' . ( $wd_inv ? __( 'to the seller', 'wwmd' ) : __( 'on this site', 'wwmd' ) ) ); ?></dd></div><?php endif; ?>
			</dl>

			<div class="invoice__lines"><?php echo wd_offer_lines_html( $wd_o ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<?php if ( '' !== trim( (string) $wd_o->body ) ) : ?><p class="invoice__note"><?php echo esc_html( $wd_o->body ); ?></p><?php endif; ?>

			<?php if ( $wd_paid ) : ?>
				<p class="invoice__state invoice__state--paid"><?php echo wd_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Paid. Thank you!', 'wwmd' ); ?></p>
			<?php elseif ( isset( $wd_st[ $wd_o->offer_status ] ) ) : ?>
				<p class="invoice__state invoice__state--<?php echo esc_attr( $wd_o->offer_status ); ?>"><?php echo wd_icon( 'close', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $wd_st[ $wd_o->offer_status ] ); ?></p>
			<?php elseif ( $wd_inv ) : ?>
				<?php if ( $wd_in ) : ?>
					<div class="invoice__how">
						<h2><?php echo wd_icon( 'cash', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'How to pay', 'wwmd' ); ?></h2>
						<p><?php echo nl2br( esc_html( (string) $wd_o->offer_info ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
						<small><?php echo esc_html( sprintf( /* translators: %s seller */ __( 'Pay %s directly, then tell them in the chat. They mark it paid & this page becomes your receipt.', 'wwmd' ), $wd_pp ? $wd_pp['business'] : __( 'the seller', 'wwmd' ) ) ); ?></small>
					</div>
				<?php elseif ( ! $wd_me->ID ) : ?>
					<a class="btn btn--marigold btn--block" href="<?php echo esc_url( wd_url( 'sign-in', array( 'redirect_to' => rawurlencode( $wd_url ) ) ) ); ?>"><?php esc_html_e( 'Sign in to see how to pay', 'wwmd' ); ?></a>
				<?php else : ?>
					<p class="invoice__state"><?php echo wd_icon( 'clock', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'This invoice is for another customer. Chat with the seller to get your own.', 'wwmd' ); ?></p>
				<?php endif; ?>
			<?php elseif ( ! $wd_me->ID ) : ?>
				<a class="btn btn--marigold btn--block" href="<?php echo esc_url( wd_url( 'sign-in', array( 'redirect_to' => rawurlencode( $wd_url ) ) ) ); ?>"><?php echo wd_icon( 'lock', 17 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Sign in to pay', 'wwmd' ); ?></a>
			<?php elseif ( 'user' === $wd_role && (int) $wd_o->user_id === $wd_me->ID ) : ?>
				<form class="invoice__pay" data-offer-pay>
					<input type="hidden" name="offer" value="<?php echo (int) $wd_o->id; ?>">
					<div class="field-row">
						<label class="field"><span><?php esc_html_e( 'Your name', 'wwmd' ); ?></span><input name="name" required value="<?php echo esc_attr( $wd_me->display_name ); ?>"></label>
						<label class="field"><span><?php esc_html_e( 'Phone (WhatsApp)', 'wwmd' ); ?></span><input name="phone" type="tel" required value="<?php echo esc_attr( get_user_meta( $wd_me->ID, 'wd_phone', true ) ); ?>"></label>
					</div>
					<button class="btn btn--marigold btn--block btn--lg" type="submit"><?php echo wd_icon( 'lock', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( sprintf( /* translators: %s amount */ __( 'Pay %s', 'wwmd' ), wd_money( $wd_o->offer_amount ) ) ); ?></button>
					<p class="form__msg" data-msg role="status"></p>
				</form>
			<?php elseif ( in_array( $wd_role, array( 'partner', 'hq' ), true ) ) : ?>
				<p class="invoice__state"><?php echo wd_icon( 'clock', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Waiting for the customer to pay. Only they can pay this link.', 'wwmd' ); ?></p>
			<?php else : ?>
				<p class="invoice__state"><?php echo wd_icon( 'clock', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'This payment link is for another customer. Chat with the seller to get your own.', 'wwmd' ); ?></p>
			<?php endif; ?>

			<footer class="invoice__foot">
				<?php if ( ! $wd_paid && ! $wd_inv && ! isset( $wd_st[ $wd_o->offer_status ] ) ) : ?>
					<p class="invoice__safe"><?php echo wd_icon( 'lock', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Pay by card, bank transfer or mobile money on a secure payment page.', 'wwmd' ); ?></span></p>
				<?php endif; ?>
				<p class="invoice__acts">
					<?php if ( $wd_paid ) : ?><button class="linkish" type="button" data-print><?php echo wd_icon( 'feed', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Print or save as PDF', 'wwmd' ); ?></button><?php endif; ?>
					<button class="linkish" type="button" data-copy="<?php echo esc_url( $wd_url ); ?>"><?php echo wd_icon( 'link', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Copy link', 'wwmd' ); ?></button>
					<?php if ( in_array( $wd_role, array( 'user', 'partner' ), true ) ) : ?><a class="linkish" href="<?php echo esc_url( $wd_chat ); ?>"><?php echo wd_icon( 'chat', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Back to the chat', 'wwmd' ); ?></a><?php endif; ?>
				</p>
			</footer>
		</article>
	</div>
</main>
