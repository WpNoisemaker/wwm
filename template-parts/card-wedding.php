<?php
/**
 * Wedding card for lists.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_id     = (int) $args['id'];
$wd_d      = wd_get( $wd_id );
$wd_m      = (array) get_post_meta( $wd_id, '_wd_m', true );
$wd_st     = wd_status( $wd_id, $wd_d );
$wd_rank   = wd_rank( $wd_id, 'week' );
$wd_plan   = wd_plan( $wd_id );
$wd_tone   = array( 'butter', 'lavender', 'sky', 'mint', 'coral' )[ $wd_id % 5 ];
$wd_styles = get_the_terms( $wd_id, 'wd_style' );
?>
<article class="w-card tone-<?php echo esc_attr( $wd_tone ); ?>">
	<a class="w-card__link" href="<?php echo esc_url( get_permalink( $wd_id ) ); ?>">
		<div class="w-card__media">
			<?php echo wd_cover( $wd_id, $wd_d ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( $wd_st[1] ) : ?><span class="status status--<?php echo esc_attr( $wd_st[0] ); ?>"><?php echo esc_html( $wd_st[1] ); ?></span><?php endif; ?>
			<?php if ( $wd_rank && $wd_rank <= 10 ) : ?><span class="rank-bubble" title="<?php esc_attr_e( 'Place on this week’s Buzz Chart', 'wwmd' ); ?>"><small><?php esc_html_e( 'No.', 'wwmd' ); ?></small><?php echo (int) $wd_rank; ?></span><?php endif; ?>
		</div>
		<div class="w-card__body">
			<div class="w-card__tags">
				<?php echo wd_plan_tag( $wd_plan ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php if ( $wd_styles && ! is_wp_error( $wd_styles ) ) : ?><span class="tag"><?php echo esc_html( $wd_styles[0]->name ); ?></span><?php endif; ?>
			</div>
			<h3 class="w-card__title"><?php echo esc_html( wd_names( $wd_d ) ); ?><?php echo wd_vbadge( $wd_id, 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h3>
			<p class="w-card__meta"><?php echo wd_icon( 'calendar', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( wd_date( $wd_d['date'], 'j M Y' ) ); ?> <span aria-hidden="true">·</span> <?php echo wd_icon( 'pin', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $wd_d['city'] ); ?></p>
			<?php echo wd_program_chips( $wd_d, 3 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<ul class="w-card__stats">
				<li title="<?php esc_attr_e( 'Buzz coins', 'wwmd' ); ?>"><?php echo wd_icon( 'coin', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( wd_short( wd_coin_total( $wd_id ) ) ); ?></li>
				<li title="<?php esc_attr_e( 'Page visits', 'wwmd' ); ?>"><?php echo wd_icon( 'eye', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( wd_short( $wd_m['views'] ?? 0 ) ); ?></li>
				<li title="<?php esc_attr_e( 'Love notes', 'wwmd' ); ?>"><?php echo wd_icon( 'heart', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( wd_short( ( $wd_m['wall'] ?? 0 ) ) ); ?></li>
				<?php if ( $wd_d['hashtag'] ) : ?><li class="w-card__hash">#<?php echo esc_html( $wd_d['hashtag'] ); ?></li><?php endif; ?>
			</ul>
		</div>
	</a>
</article>
