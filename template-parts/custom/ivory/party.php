<?php
/**
 * Ivory & Gold: the bridal party in two tabs (bridesmaids & groomsmen). Each row is centred, so a
 * short row never leaves a gap on one side.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_id     = $args['id'];
$wd_d      = $args['d'];
$wd_sample = (bool) get_post_meta( $wd_id, '_wd_sample', true );
$wd_rel    = wd_rel_statuses();
$wd_sides  = array(
	'bride' => array(),
	'groom' => array(),
);
foreach ( $wd_d['party'] as $wd_p ) {
	$wd_sides[ 'groom' === $wd_p['side'] ? 'groom' : 'bride' ][] = $wd_p;
}
$wd_sides  = array_filter( $wd_sides );
$wd_tabs   = array(
	'bride' => __( 'Bridesmaids', 'wwmd' ),
	'groom' => __( 'Groomsmen', 'wwmd' ),
);
$wd_first  = (string) key( $wd_sides );
?>
<section class="w-sec iv-sec iv-party" id="party">
	<div class="wrap">
		<div class="iv-head">
			<p class="iv-kicker"><?php esc_html_e( 'Standing with us', 'wwmd' ); ?></p>
			<h2 class="iv-title"><?php esc_html_e( 'Bridesmaids & Groomsmen Line Up', 'wwmd' ); ?></h2>
			<p class="iv-sub"><?php esc_html_e( 'The friends & family who have carried us here. You will see them in matching colours all day.', 'wwmd' ); ?></p>
			<span class="iv-rule" aria-hidden="true"><i class="iv-gem"></i></span>
		</div>
		<?php if ( count( $wd_sides ) > 1 ) : ?>
			<div class="iv-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Bridesmaids & groomsmen line up', 'wwmd' ); ?>" hidden data-iv-tabs>
				<?php foreach ( $wd_sides as $wd_side => $wd_people ) : ?>
					<button type="button" role="tab" class="iv-tab" id="iv-tab-<?php echo esc_attr( $wd_side ); ?>" aria-controls="iv-side-<?php echo esc_attr( $wd_side ); ?>" aria-selected="<?php echo $wd_first === $wd_side ? 'true' : 'false'; ?>"<?php echo $wd_first === $wd_side ? '' : ' tabindex="-1"'; ?>><?php echo esc_html( $wd_tabs[ $wd_side ] ); ?></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php foreach ( $wd_sides as $wd_side => $wd_people ) : ?>
			<div class="iv-side" id="iv-side-<?php echo esc_attr( $wd_side ); ?>" role="tabpanel" aria-labelledby="iv-tab-<?php echo esc_attr( $wd_side ); ?>" data-iv-panel>
				<h3 class="iv-side__h"><?php echo esc_html( sprintf( /* translators: %s name */ __( 'Team %s', 'wwmd' ), strtok( wd_side_name( $wd_d, $wd_side ), ' ' ) ) ); ?></h3>
				<div class="iv-squad">
					<?php foreach ( $wd_people as $wd_p ) : ?>
						<?php
						$wd_links = wd_squad_links( $wd_p );
						$wd_plain = $wd_sample || ! empty( $wd_p['plain'] );
						$wd_likes = wd_squad_likes( $wd_p );
						$wd_ini   = implode( '', array_map( static function ( $w ) { return mb_strtoupper( mb_substr( $w, 0, 1 ) ); }, array_slice( preg_split( '/\s+/', trim( $wd_p['name'] ) ), 0, 2 ) ) );
						?>
						<article class="iv-card iv-person">
							<span class="iv-person__pic">
								<?php if ( ! empty( $wd_p['image'] ) ) : ?>
									<img src="<?php echo esc_url( wd_img_url( $wd_p['image'], 'wd-square' ) ); ?>" alt="" loading="lazy">
								<?php else : ?>
									<span aria-hidden="true"><?php echo esc_html( $wd_ini ); ?></span>
								<?php endif; ?>
							</span>
							<p class="iv-kicker"><?php echo esc_html( $wd_p['role'] ); ?></p>
							<h4 class="iv-person__name"><?php echo esc_html( $wd_p['name'] ); ?></h4>
							<?php if ( ! empty( $wd_p['nick'] ) ) : ?><p class="iv-person__nick">“<?php echo esc_html( $wd_p['nick'] ); ?>”</p><?php endif; ?>
							<?php if ( $wd_links ) : ?>
								<p class="iv-person__handles">
									<?php foreach ( $wd_links as $wd_s ) : ?>
										<?php if ( $wd_plain ) : ?>
											<span title="<?php echo esc_attr( $wd_s[0] ); ?>">@<?php echo esc_html( $wd_s[3] ); ?></span>
										<?php else : ?>
											<a href="<?php echo esc_url( $wd_s[1] ); ?>" target="_blank" rel="noopener nofollow ugc" title="<?php echo esc_attr( $wd_s[0] ); ?>">@<?php echo esc_html( $wd_s[3] ); ?></a>
										<?php endif; ?>
									<?php endforeach; ?>
								</p>
							<?php endif; ?>
							<dl class="iv-person__dl">
								<?php if ( ! empty( $wd_p['career'] ) ) : ?><div><dt><?php esc_html_e( 'Work', 'wwmd' ); ?></dt><dd><?php echo esc_html( $wd_p['career'] ); ?></dd></div><?php endif; ?>
								<?php if ( ! empty( $wd_p['status'] ) && isset( $wd_rel[ $wd_p['status'] ] ) ) : ?><div><dt><?php esc_html_e( 'Status', 'wwmd' ); ?></dt><dd><?php echo esc_html( $wd_rel[ $wd_p['status'] ] ); ?></dd></div><?php endif; ?>
							</dl>
							<?php if ( $wd_likes ) : ?>
								<div class="iv-person__likes"><span><?php esc_html_e( 'Loves', 'wwmd' ); ?></span><ul><?php foreach ( $wd_likes as $wd_l ) : ?><li><?php echo esc_html( $wd_l ); ?></li><?php endforeach; ?></ul></div>
							<?php endif; ?>
							<?php if ( $wd_p['line'] ) : ?><p class="iv-person__line">“<?php echo esc_html( $wd_p['line'] ); ?>”</p><?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
