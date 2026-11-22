<?php
/**
 * Cash & gift registry.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_id     = $args['id'];
$wd_d      = $args['d'];
$wd_raised = wd_registry_raised( $wd_id );
$wd_fee    = wd_fee_rate( $wd_id, 'gift' );
$wd_cur    = $wd_d['currency'];
?>
<section class="w-sec" id="registry">
	<div class="wrap">
		<div class="w-head w-head--row">
			<div>
				<p class="w-eyebrow"><?php echo wd_icon( 'gift', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Cash & gift registry', 'wwmd' ); ?></p>
				<h2 class="w-title"><?php esc_html_e( 'Bless our new home', 'wwmd' ); ?></h2>
			</div>
			<p class="w-head__aside"><?php esc_html_e( 'Your presence is the real gift. If you would like to bless us, here are a few things we are saving for.', 'wwmd' ); ?></p>
		</div>
		<div class="gifts">
			<?php
			foreach ( $wd_d['registry'] as $wd_i => $wd_g ) :
				$wd_r    = isset( $wd_raised[ $wd_g['id'] ] ) ? $wd_raised[ $wd_g['id'] ] : array( 0, 0 );
				$wd_goal = (int) $wd_g['price'];
				$wd_pct  = $wd_goal ? min( 100, round( $wd_r[0] / $wd_goal * 100 ) ) : 0;
				$wd_done = $wd_goal && $wd_r[0] >= $wd_goal;
				$wd_img  = $wd_g['image'] && 0 !== strpos( (string) $wd_g['image'], 'art:' ) ? wd_img_url( $wd_g['image'], 'wd-card' ) : '';
				$wd_art  = 0 === strpos( (string) $wd_g['image'], 'art:' ) ? substr( $wd_g['image'], 4 ) : ( 'fund' === $wd_g['type'] ? 'notes' : 'gift' );
				?>
				<article class="gift tone-<?php echo esc_attr( array( 'butter', 'lavender', 'sky', 'mint', 'coral' )[ $wd_i % 5 ] ); ?><?php echo $wd_done ? ' is-done' : ''; ?>">
					<div class="gift__pic"><?php echo $wd_img ? '<img src="' . esc_url( $wd_img ) . '" alt="" loading="lazy">' : wd_art( $wd_art ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span class="gift__type"><?php echo 'fund' === $wd_g['type'] ? esc_html__( 'Cash fund', 'wwmd' ) : esc_html__( 'Gift', 'wwmd' ); ?></span></div>
					<div class="gift__body">
						<h3><?php echo esc_html( $wd_g['title'] ); ?></h3>
						<?php if ( $wd_g['note'] ) : ?><p><?php echo esc_html( $wd_g['note'] ); ?></p><?php endif; ?>
						<?php if ( $wd_goal ) : ?>
							<div class="meter" role="img" aria-label="<?php echo esc_attr( sprintf( /* translators: %d percent */ __( '%d%% raised', 'wwmd' ), $wd_pct ) ); ?>"><i style="width:<?php echo (int) $wd_pct; ?>%"></i></div>
							<p class="gift__nums"><b><?php echo esc_html( wd_money( $wd_r[0], $wd_cur ) ); ?></b> <?php echo esc_html( sprintf( /* translators: %s goal */ __( 'of %s', 'wwmd' ), wd_money( $wd_goal, $wd_cur ) ) ); ?><?php if ( $wd_r[1] ) : ?> <span>· <?php echo esc_html( sprintf( /* translators: %d people */ _n( '%d giver', '%d givers', $wd_r[1], 'wwmd' ), $wd_r[1] ) ); ?></span><?php endif; ?></p>
						<?php endif; ?>
						<div class="btn-row">
							<?php if ( $wd_done ) : ?>
								<span class="tag tag--done"><?php echo wd_icon( 'check', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Fully gifted. Thank you!', 'wwmd' ); ?></span>
							<?php else : ?>
								<button class="btn w-btn btn--sm" type="button" data-pay="gift" data-item="<?php echo esc_attr( $wd_g['id'] ); ?>" data-title="<?php echo esc_attr( $wd_g['title'] ); ?>" data-suggest="<?php echo (int) ( $wd_goal ? min( $wd_goal - $wd_r[0], 'fund' === $wd_g['type'] ? max( 1, round( $wd_goal / 50 ) ) : $wd_goal - $wd_r[0] ) : 0 ); ?>"><?php echo 'fund' === $wd_g['type'] ? esc_html__( 'Give to this fund', 'wwmd' ) : esc_html__( 'Give the cash for this', 'wwmd' ); ?></button>
							<?php endif; ?>
							<?php if ( $wd_g['url'] ) : ?><a class="btn w-btn w-btn--ghost btn--sm" href="<?php echo esc_url( $wd_g['url'] ); ?>" target="_blank" rel="noopener sponsored"><?php echo esc_html( sprintf( /* translators: %s store */ __( 'Buy at %s', 'wwmd' ), $wd_g['store'] ? $wd_g['store'] : __( 'the store', 'wwmd' ) ) ); ?></a><?php endif; ?>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
		<?php if ( $wd_fee > 0 ) : ?>
			<p class="fine"><?php echo esc_html( sprintf( /* translators: %s percent */ __( 'A %s%% service fee applies to gifts sent online. You can choose to cover it so the couple gets the full amount.', 'wwmd' ), number_format_i18n( $wd_fee, 1 ) ) ); ?></p>
		<?php endif; ?>
	</div>
</section>
