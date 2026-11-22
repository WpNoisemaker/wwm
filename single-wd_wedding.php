<?php
/**
 * A couple's wedding page. Free pages use the brand colours; Premium pages use the couple's own.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

the_post();
$wd_id   = get_the_ID();
$wd_d    = wd_get( $wd_id );
$wd_prem = wd_can( $wd_id, 'design' );
// Free pages are black & white (Ayoola, 2026-11-17); paid pages use the couple's own colours.
$wd_des  = $wd_prem ? $wd_d['design'] : array_merge(
	wd_blank()['design'],
	array(
		'primary' => '#111111',
		'accent'  => '#FFFFFF',
		'bg'      => '#FFFFFF',
		'ink'     => '#111111',
	)
);
$wd_tpl  = wd_template( $wd_id, $wd_d );
// The tier a page is on, so the look can climb: free is plain, Pro is the couple's own colours,
// Premium is a designed page. Each tier is a class, so the ladder is visible in the markup.
$wd_tier = wd_can( $wd_id, 'templates' ) ? 'premium' : ( $wd_prem ? 'pro' : 'free' );
$wd_fon  = wd_fonts();
$wd_head = isset( $wd_fon[ $wd_des['head'] ] ) ? $wd_fon[ $wd_des['head'] ] : $wd_fon['avantgarde'];
$wd_body = isset( $wd_fon[ $wd_des['body'] ] ) ? $wd_fon[ $wd_des['body'] ] : $wd_fon['jakarta'];
$wd_info = wd_tpl_info( $wd_tpl );
$wd_gf   = array();
// One request per family: a custom template's own font settings win over the plain ones.
foreach ( array_merge( array( $wd_head[2], $wd_body[2], 'garden' === $wd_tpl ? $wd_fon['greatvibes'][2] : '' ), 'classic' === $wd_tpl ? array() : $wd_info['fonts'] ) as $wd_f ) {
	if ( $wd_f ) {
		$wd_gf[ strtok( $wd_f, ':' ) ] = $wd_f;
	}
}
if ( $wd_gf ) {
	wp_enqueue_style( 'wd-gfonts', 'https://fonts.googleapis.com/css2?family=' . implode( '&family=', $wd_gf ) . '&display=swap', array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters
}
// A design's own styles (& script): assets/custom/{key}/ for a custom template, assets/tpl/ for a Premium design.
if ( 'classic' !== $wd_tpl ) {
	$wd_asset = $wd_info['custom'] ? 'custom/' . $wd_tpl . '/' . $wd_tpl : 'tpl/' . $wd_tpl;
	// A design's own sheet must load after the Aurora wedding layer, so the design always wins
	// where the two touch the same component. Stated as a dependency rather than left to the
	// order things happen to be enqueued in.
	$wd_dep   = wp_style_is( 'wd-aurora-wedding', 'registered' ) ? array( 'wd-wedding', 'wd-aurora-wedding' ) : array( 'wd-wedding' );
	// Designs that share a base (the Studio designs) load it first, then their own look.
	if ( ! empty( $wd_info['base'] ) && is_readable( WD_DIR . '/assets/tpl/' . $wd_info['base'] . '.css' ) ) {
		wp_enqueue_style( 'wd-tpl-base', get_template_directory_uri() . '/assets/tpl/' . $wd_info['base'] . '.css', $wd_dep, WD_VER );
		$wd_dep = array( 'wd-tpl-base' );
	}
	if ( is_readable( WD_DIR . '/assets/' . $wd_asset . '.css' ) ) {
		wp_enqueue_style( 'wd-custom', get_template_directory_uri() . '/assets/' . $wd_asset . '.css', $wd_dep, WD_VER );
	}
	if ( is_readable( WD_DIR . '/assets/' . $wd_asset . '.js' ) ) {
		wp_enqueue_script( 'wd-custom', get_template_directory_uri() . '/assets/' . $wd_asset . '.js', array( 'wd-wedding' ), WD_VER, true );
	}
}
// phpcs:ignore WordPress.Security.NonceVerification
$wd_join = isset( $_GET['join'] ) ? sanitize_text_field( wp_unslash( $_GET['join'] ) ) : null;
if ( ! wd_owns( $wd_id ) && ! is_preview() && null === $wd_join ) {
	wd_count_view( $wd_id );
}
// phpcs:disable WordPress.Security.NonceVerification
$wd_paid  = isset( $_GET['wdpay'] ) ? wd_pay_verify( wd_pay_ref_from_request() ) : null;
$wd_table = isset( $_GET['table'] ) ? substr( preg_replace( '/[^A-Za-z0-9 -]/', '', sanitize_text_field( wp_unslash( $_GET['table'] ) ) ), 0, 12 ) : '';
// phpcs:enable
$wd_style = sprintf(
	'--w-primary:%1$s;--w-accent:%2$s;--w-bg:%3$s;--w-ink:%4$s;--w-head:%5$s;--w-body:%6$s;--w-pattern:%7$s;',
	esc_attr( $wd_des['primary'] ),
	esc_attr( $wd_des['accent'] ),
	esc_attr( $wd_des['bg'] ),
	esc_attr( $wd_des['ink'] ),
	esc_attr( $wd_head[1] ),
	esc_attr( $wd_body[1] ),
	esc_attr( wd_pattern_url( $wd_des['pattern'], $wd_des['primary'], $wd_des['accent'], $wd_des['bg'] ) )
);
$wd_secs = wd_page_sections( $wd_id, $wd_d );
$wd_lay  = wd_layout( $wd_id, $wd_d );
$wd_on = array();
foreach ( $wd_secs as $wd_k => $wd_label ) {
	$wd_show = wd_show( $wd_id, $wd_k, $wd_d );
	if ( 'livestream' === $wd_k ) {
		$wd_show = $wd_show && ! empty( $wd_d['live']['on'] ) && ( $wd_d['live']['url'] || wd_opens_wait( $wd_d['live']['opens'], true ) );
	} elseif ( 'spray' === $wd_k ) {
		$wd_show = $wd_show && ! empty( $wd_d['spray']['on'] );
	} elseif ( 'story' === $wd_k ) {
		$wd_show = $wd_show && $wd_d['story'];
	} elseif ( 'party' === $wd_k ) {
		$wd_show = $wd_show && $wd_d['party'];
	} elseif ( 'quiz' === $wd_k ) {
		$wd_show = $wd_show && $wd_d['quiz'];
	} elseif ( 'registry' === $wd_k ) {
		$wd_show = $wd_show && $wd_d['registry'];
	} elseif ( 'vendors' === $wd_k ) {
		$wd_show = $wd_show && $wd_d['vendors'];
	} elseif ( 'instagram' === $wd_k ) {
		$wd_show = $wd_show && ( $wd_d['ig']['handle'] || $wd_d['ig']['posts'] || get_post_meta( $wd_id, '_wd_ig_media', true ) );
	}
	if ( $wd_show ) {
		$wd_on[ $wd_k ] = $wd_label;
	}
}
$wd_ads = wd_can( $wd_id, 'no_ads' ) ? array() : wd_ads();
$wd_ctx = array(
	'id' => $wd_id,
	'd'  => $wd_d,
);
get_header();
?>
<div class="wpage wpage--t-<?php echo esc_attr( $wd_tier ); ?><?php echo $wd_prem ? ' wpage--premium' : ' wpage--free'; ?><?php echo 'classic' === $wd_tpl ? '' : ' wpage--lux wpage--c-' . esc_attr( $wd_lay['corners'] ); ?> wpage--tpl-<?php echo esc_attr( $wd_tpl ); ?><?php echo 'classic' !== $wd_tpl && ! empty( $wd_info['base'] ) ? ' st-page' : ''; ?>" style="<?php echo $wd_style; // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts. ?>" data-wedding="<?php echo (int) $wd_id; ?>" data-currency="<?php echo esc_attr( $wd_d['currency'] ); ?>" data-symbol="<?php echo esc_attr( wd_currencies()[ $wd_d['currency'] ][0] ?? '₦' ); ?>">
	<?php if ( 'private' === get_post_status( $wd_id ) ) : ?>
		<p class="sample-bar sample-bar--private"><?php esc_html_e( 'This page is private. Only you can see it.', 'wwmd' ); ?></p>
	<?php endif; ?>
	<?php if ( true === $wd_paid ) : ?>
		<div class="pay-banner" role="status" data-confetti><?php echo wd_icon( 'check', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Payment received. Thank you for the love!', 'wwmd' ); ?><?php if ( wd_receipt_tx( wd_pay_ref_from_request() ) ) : ?> <a href="<?php echo esc_url( wd_receipt_url( wd_pay_ref_from_request() ) ); ?>"><?php esc_html_e( 'See your receipt', 'wwmd' ); ?></a><?php endif; ?></div>
	<?php elseif ( false === $wd_paid ) : ?>
		<div class="pay-banner pay-banner--wait" role="status"><?php esc_html_e( 'We are still confirming your payment. Refresh in a minute.', 'wwmd' ); ?></div>
	<?php endif; ?>
	<?php if ( $wd_table ) : ?>
		<div class="table-banner"><?php echo wd_icon( 'qr', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( sprintf( /* translators: %s table */ __( 'Welcome to table %s! Spray the couple, share your photos & leave a love note.', 'wwmd' ), $wd_table ) ); ?></span></div>
	<?php endif; ?>

	<?php
	if ( null !== $wd_join ) {
		get_template_part( 'template-parts/wedding/join', null, $wd_ctx + array( 'key' => $wd_join ) );
		echo '</div>';
		get_footer();
		return;
	}
	$wd_pl = wd_plan( $wd_id );
	?>
	<p class="w-planbar w-planbar--<?php echo esc_attr( $wd_pl ); ?>"><?php echo wd_plan_tag( $wd_pl ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'wedding page', 'wwmd' ); ?></span></p>
	<?php get_template_part( wd_part_path( $wd_tpl, 'hero' ), null, $wd_ctx + array( 'on' => $wd_on, 'tpl' => $wd_tpl ) ); ?>

	<nav class="w-nav" aria-label="<?php esc_attr_e( 'Wedding page sections', 'wwmd' ); ?>" data-w-nav>
		<?php if ( 'classic' !== $wd_tpl ) : ?><a class="w-nav__mono" href="#top" aria-label="<?php esc_attr_e( 'Back to the top', 'wwmd' ); ?>"><?php echo esc_html( wd_initials( $wd_d ) ); ?></a><?php endif; ?>
		<ul>
			<?php foreach ( $wd_on as $wd_k => $wd_label ) : ?>
				<li><a href="#<?php echo esc_attr( $wd_k ); ?>"><?php echo esc_html( $wd_label ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</nav>

	<?php
	$wd_n = 0;
	foreach ( array_keys( $wd_on ) as $wd_k ) {
		get_template_part( wd_part_path( $wd_tpl, $wd_k ), null, $wd_ctx );
		++$wd_n;
		if ( $wd_ads && ( 2 === $wd_n || 6 === $wd_n ) ) {
			get_template_part( 'template-parts/wedding/ad', null, array( 'ad' => array_shift( $wd_ads ) ) );
		}
	}
	foreach ( $wd_ads as $wd_ad ) {
		get_template_part( 'template-parts/wedding/ad', null, array( 'ad' => $wd_ad ) );
	}
	$wd_sign = wd_part_path( $wd_tpl, 'sign', true );
	if ( $wd_sign ) {
		get_template_part( $wd_sign, null, $wd_ctx + array( 'on' => $wd_on ) );
	}
	?>

	<?php $wd_vp = wd_plan( $wd_id ); ?>
	<section class="w-viral<?php echo 'free' !== $wd_vp ? ' w-viral--paid' : ''; ?>">
		<div class="wrap w-viral__inner">
			<span class="w-viral__art" aria-hidden="true"><?php echo wd_art( 'bridal' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			<div>
				<h2><?php esc_html_e( 'Getting married soon?', 'wwmd' ); ?></h2>
				<?php if ( 'free' !== $wd_vp ) : ?>
					<p><?php echo wd_plan_tag( $wd_vp ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( sprintf( /* translators: %s plan */ __( 'This is a %s page. You can start a free page with fewer features & a lower place on the Buzz Chart, then move up any time.', 'wwmd' ), wd_plan_name( $wd_vp ) ) ); ?></p>
				<?php else : ?>
					<p><?php esc_html_e( 'Make a wedding page like this one, free: your invitation, love story, RSVP, love notes & a cash & gift registry.', 'wwmd' ); ?></p>
				<?php endif; ?>
			</div>
			<div class="w-viral__btns">
				<a class="btn btn--marigold" href="<?php echo esc_url( wd_url( 'sign-in', array( 'tab' => 'join' ) ) ); ?>"><?php esc_html_e( 'Create my free page', 'wwmd' ); ?></a>
				<a class="btn btn--ghost" href="<?php echo esc_url( wd_url( 'pricing' ) ); ?>"><?php esc_html_e( 'Compare plans', 'wwmd' ); ?></a>
			</div>
		</div>
	</section>

	<?php if ( wd_owns( $wd_id ) ) : ?>
		<a class="owner-fab" href="<?php echo esc_url( wd_account_url( 'page', array( 'wid' => $wd_id ) ) ); ?>"><?php echo wd_icon( 'edit', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Edit this page', 'wwmd' ); ?></a>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/wedding/pay-dialog', null, $wd_ctx ); ?>
</div>
<?php
get_footer();
