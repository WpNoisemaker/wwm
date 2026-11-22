<?php
/**
 * A guest's gift or spray receipt, at /receipt/{reference}/. Premium & High Society couples' receipts
 * carry their names, colours, logo & thank-you note.
 *
 * Args: tx (the paid gift or spray).
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_tx  = $args['tx'];
$wd_wid = (int) $wd_tx->wedding_id;
$wd_d   = wd_get( $wd_wid );
$wd_lk  = wd_receipt_look( $wd_wid );
$wd_amt = (int) ( $wd_tx->charged ? $wd_tx->charged : $wd_tx->amount );
$wd_sty = $wd_lk['couple'] ? sprintf( '--r-primary:%1$s;--r-accent:%2$s;--r-ink:%3$s;', esc_attr( $wd_lk['primary'] ), esc_attr( $wd_lk['accent'] ), esc_attr( $wd_lk['ink'] ) ) : '';
?>
<main id="main" class="pay-page">
	<div class="wrap">
		<article class="invoice invoice--paid receipt<?php echo $wd_lk['couple'] ? ' receipt--couple' : ''; ?>" style="<?php echo $wd_sty; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?>">
			<header class="invoice__head receipt__head">
				<?php if ( $wd_lk['couple'] ) : ?>
					<a class="invoice__seller receipt__couple" href="<?php echo esc_url( get_permalink( $wd_wid ) ); ?>">
						<?php if ( $wd_lk['logo'] ) : ?>
							<img src="<?php echo esc_url( $wd_lk['logo'] ); ?>" width="56" height="56" alt="">
						<?php else : ?>
							<span class="receipt__mono"><?php echo esc_html( $wd_lk['initials'] ); ?></span>
						<?php endif; ?>
						<span><b><?php echo esc_html( $wd_lk['names'] ); ?></b><small><?php echo esc_html( trim( wd_date( $wd_d['date'], 'j F Y' ) . ( $wd_d['city'] ? ' · ' . $wd_d['city'] : '' ), ' ·' ) ); ?></small></span>
					</a>
				<?php else : ?>
					<a class="invoice__seller receipt__site" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php wd_logo( false, 'receipt__logo' ); ?></a>
				<?php endif; ?>
				<p class="invoice__no"><span><?php esc_html_e( 'Gift receipt', 'wwmd' ); ?></span><b><?php echo esc_html( $wd_tx->reference ); ?></b><small><?php echo esc_html( wd_date( get_date_from_gmt( $wd_tx->paid_at ? $wd_tx->paid_at : $wd_tx->created, 'Y-m-d' ), 'j M Y' ) ); ?></small></p>
			</header>

			<dl class="invoice__meta">
				<div><dt><?php esc_html_e( 'From', 'wwmd' ); ?></dt><dd><?php echo esc_html( $wd_tx->name ? $wd_tx->name : __( 'A guest', 'wwmd' ) ); ?></dd></div>
				<div><dt><?php esc_html_e( 'To', 'wwmd' ); ?></dt><dd><a href="<?php echo esc_url( get_permalink( $wd_wid ) ); ?>"><?php echo esc_html( wd_names( $wd_d ) ); ?></a></dd></div>
				<div><dt><?php esc_html_e( 'For', 'wwmd' ); ?></dt><dd><?php echo esc_html( wd_receipt_for( $wd_tx ) ); ?></dd></div>
			</dl>

			<div class="invoice__lines">
				<ul class="offer__lines"><li><span><?php echo esc_html( wd_receipt_for( $wd_tx ) ); ?></span><b><?php echo esc_html( wd_money( $wd_tx->amount, $wd_tx->currency ) ); ?></b></li>
				<?php if ( $wd_amt > (int) $wd_tx->amount ) : ?><li><span><?php esc_html_e( 'Service fee you covered', 'wwmd' ); ?></span><b><?php echo esc_html( wd_money( $wd_amt - (int) $wd_tx->amount, $wd_tx->currency ) ); ?></b></li><?php endif; ?></ul>
				<p class="offer__total"><span><?php esc_html_e( 'Total paid', 'wwmd' ); ?></span><b><?php echo esc_html( wd_money( $wd_amt, $wd_tx->currency ) ); ?></b></p>
			</div>
			<?php if ( $wd_tx->message ) : ?><p class="invoice__note">“<?php echo esc_html( $wd_tx->message ); ?>”</p><?php endif; ?>
			<p class="invoice__state invoice__state--paid"><?php echo wd_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $wd_lk['note'] ? $wd_lk['note'] : __( 'Paid. Thank you for the love!', 'wwmd' ) ); ?></p>

			<footer class="invoice__foot">
				<p class="invoice__safe"><?php echo wd_icon( 'lock', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $wd_lk['couple'] ? sprintf( /* translators: %s couple */ __( 'Paid securely on %s’s wedding page, powered by Wedding Websites & More.', 'wwmd' ), wd_names( $wd_d ) ) : __( 'Paid securely through Wedding Websites & More.', 'wwmd' ) ); ?><?php echo $wd_tx->test ? ' ' . esc_html__( '(Test payment.)', 'wwmd' ) : ''; ?></span></p>
				<p class="invoice__acts">
					<button class="linkish" type="button" data-print><?php echo wd_icon( 'feed', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Print or save as PDF', 'wwmd' ); ?></button>
					<a class="linkish" href="<?php echo esc_url( get_permalink( $wd_wid ) ); ?>"><?php echo wd_icon( 'heart', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Back to the wedding page', 'wwmd' ); ?></a>
				</p>
			</footer>
		</article>
	</div>
</main>
