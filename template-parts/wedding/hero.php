<?php
/**
 * Wedding hero. One structure for every look: the template sets the finish (fonts, colours &
 * ornaments) & the page layout sets where the photo goes (left, right, on top, or names only).
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_id    = $args['id'];
$wd_d     = $args['d'];
$wd_on    = $args['on'];
$wd_tpl   = isset( $args['tpl'] ) ? $args['tpl'] : 'classic';
$wd_lay   = wd_layout( $wd_id, $wd_d );
$wd_hero  = $wd_lay['hero'];
$wd_rank  = wd_rank( $wd_id, 'week' );
$wd_st    = wd_status( $wd_id, $wd_d );
$wd_m     = (array) get_post_meta( $wd_id, '_wd_m', true );
$wd_url   = get_permalink( $wd_id );
$wd_prog  = wd_programs( $wd_d );
$wd_next  = count( $wd_prog ) > 1 ? wd_next_program( $wd_d ) : null;
$wd_cdl   = '';
$wd_dt    = '';
if ( $wd_next ) {
	$wd_dt  = str_replace( ' ', 'T', $wd_next['when'] ) . ':00';
	/* translators: 1: program name, 2: its date */
	$wd_cdl = sprintf( __( 'Next up: %1$s, %2$s', 'wwmd' ), $wd_next['title'], wd_date( $wd_next['date'], 'D j M' ) );
} elseif ( $wd_d['date'] && wd_days_to( $wd_d['date'] ) >= 0 ) {
	$wd_dt = $wd_d['date'] . 'T' . ( $wd_d['time'] ? $wd_d['time'] : '12:00' ) . ':00';
}
$wd_place = $wd_d['venue'];
foreach ( array( $wd_d['city'], $wd_d['country'] ) as $wd_bit ) {
	if ( $wd_bit && false === stripos( $wd_place, $wd_bit ) ) {
		$wd_place .= ( $wd_place ? ', ' : '' ) . $wd_bit;
	}
}
$wd_gift  = ( wd_verified( $wd_id ) && ! empty( $wd_d['gift']['by'] ) ) ? $wd_d['gift'] : null;
$wd_badge = wd_vbadge( $wd_id, 28 );
$wd_cd    = $wd_d;
if ( ! wd_can( $wd_id, 'design' ) ) {
	// Free pages are black & white, the cover art too.
	$wd_cd['design'] = array_merge( (array) $wd_d['design'], array( 'primary' => '#111111', 'accent' => '#FFFFFF' ) );
}
$wd_cover = str_replace( ' loading="lazy"', ' fetchpriority="high"', wd_cover( $wd_id, $wd_cd, 'wd-cover' ) );
$wd_stat  = $wd_st[1] ? '<span class="status status--' . esc_attr( $wd_st[0] ) . '">' . esc_html( $wd_st[1] ) . '</span>' : '';
$wd_x_count = function () use ( $wd_dt, $wd_cdl ) {
	if ( ! $wd_dt ) {
		return;
	}
	?>
	<div class="countdown-wrap">
		<?php if ( $wd_cdl ) : ?><p class="countdown__label"><?php echo esc_html( $wd_cdl ); ?></p><?php endif; ?>
		<div class="countdown" data-countdown="<?php echo esc_attr( $wd_dt ); ?>" aria-label="<?php esc_attr_e( 'Countdown', 'wwmd' ); ?>">
			<span><b data-cd="d">0</b><?php esc_html_e( 'days', 'wwmd' ); ?></span>
			<span><b data-cd="h">0</b><?php esc_html_e( 'hours', 'wwmd' ); ?></span>
			<span><b data-cd="m">0</b><?php esc_html_e( 'mins', 'wwmd' ); ?></span>
			<span><b data-cd="s">0</b><?php esc_html_e( 'secs', 'wwmd' ); ?></span>
		</div>
	</div>
	<?php
};
$wd_x_buttons = function () use ( $wd_on, $wd_d, $wd_url ) {
	?>
	<div class="btn-row">
		<?php if ( isset( $wd_on['rsvp'] ) && wd_days_to( $wd_d['date'] ) >= 0 ) : ?><a class="btn w-btn" href="#rsvp"><?php esc_html_e( 'RSVP', 'wwmd' ); ?></a><?php endif; ?>
		<?php if ( isset( $wd_on['spray'] ) ) : ?><a class="btn w-btn w-btn--accent" href="#spray"><?php echo wd_icon( 'cash', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Spray', 'wwmd' ); ?></a><?php endif; ?>
		<?php if ( isset( $wd_on['livestream'] ) ) : ?><a class="btn w-btn w-btn--live" href="#livestream"><?php echo wd_icon( 'live', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Watch live', 'wwmd' ); ?></a><?php endif; ?>
		<?php if ( ! isset( $wd_on['spray'] ) && isset( $wd_on['registry'] ) ) : ?><a class="btn w-btn w-btn--accent" href="#registry"><?php echo wd_icon( 'gift', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Send a gift', 'wwmd' ); ?></a><?php endif; ?>
		<button class="btn w-btn w-btn--ghost" type="button" data-share data-url="<?php echo esc_url( $wd_url ); ?>" data-title="<?php echo esc_attr( wd_names( $wd_d ) ); ?>"><?php echo wd_icon( 'share', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Share', 'wwmd' ); ?></button>
	</div>
	<?php
};
$wd_x_gift = function () use ( $wd_gift ) {
	if ( ! $wd_gift ) {
		return;
	}
	$wd_by = '<b>' . esc_html( $wd_gift['by'] ) . '</b>';
	echo '<p class="w-gift">' . wd_icon( 'gift', 16 ) . '<span>' . sprintf( /* translators: %s gifter */ esc_html__( 'This Premium page is a gift from %s', 'wwmd' ), $wd_by ); // phpcs:ignore WordPress.Security.EscapeOutput
	if ( ! empty( $wd_gift['link'] ) ) {
		echo ' <a href="' . esc_url( $wd_gift['link'] ) . '" target="_blank" rel="noopener nofollow ugc">' . esc_html( wd_social_label( $wd_gift['link'] ) ) . '</a>';
	}
	echo '</span></p>';
};
$wd_x_buzz = function () use ( $wd_id, $wd_rank, $wd_m ) {
	?>
	<ul class="w-buzz">
		<li><?php echo wd_icon( 'coin', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><b><?php echo esc_html( wd_short( wd_coin_total( $wd_id ) ) ); ?></b> <?php esc_html_e( 'Buzz coins', 'wwmd' ); ?></li>
		<?php if ( $wd_rank ) : ?><li><a href="<?php echo esc_url( wd_url( 'rankings' ) ); ?>"><?php echo wd_icon( 'trophy', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><b><?php echo esc_html( sprintf( /* translators: %d rank */ __( 'No. %d', 'wwmd' ), $wd_rank ) ); ?></b> <?php esc_html_e( 'on the Buzz Chart', 'wwmd' ); ?></a></li><?php endif; ?>
		<li><?php echo wd_icon( 'eye', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><b><?php echo esc_html( wd_short( $wd_m['views'] ?? 0 ) ); ?></b> <?php esc_html_e( 'visits', 'wwmd' ); ?></li>
		<li><button type="button" class="linkish" data-story-card><?php echo wd_icon( 'insta', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Make a story card', 'wwmd' ); ?></button></li>
	</ul>
	<?php
};
$wd_rows  = array(
	array( 'calendar', __( 'When', 'wwmd' ), wd_date( $wd_d['date'], 'l j F Y' ) ),
	array( 'pin', __( 'Where', 'wwmd' ), $wd_place ),
);
?>
<section class="w-hero w-hero--<?php echo esc_attr( $wd_tpl ); ?> w-hero--<?php echo esc_attr( $wd_hero ); ?>">
	<div class="wrap w-hero__in">
		<?php if ( 'names' !== $wd_hero ) : ?>
			<figure class="w-hero__pic"><?php echo $wd_cover . $wd_stat; // phpcs:ignore WordPress.Security.EscapeOutput ?></figure>
		<?php endif; ?>
		<div class="w-hero__text">
			<?php if ( 'names' === $wd_hero ) : ?>
				<?php echo $wd_stat; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php endif; ?>
			<?php if ( 'royal' === $wd_tpl ) : ?>
				<span class="w-hero__crest"><?php echo wd_orn( 'crest', wd_initials( $wd_d ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			<?php elseif ( 'garden' === $wd_tpl ) : ?>
				<span class="w-hero__flower" aria-hidden="true"></span>
			<?php endif; ?>
			<?php if ( $wd_d['hashtag'] ) : ?><p class="w-hash">#<?php echo esc_html( $wd_d['hashtag'] ); ?></p><?php endif; ?>
			<h1 class="w-names"><span><?php echo esc_html( $wd_d['p1'] ); ?></span> <span class="w-amp">&amp;</span> <span><?php echo esc_html( $wd_d['p2'] ); ?><?php echo $wd_badge; // phpcs:ignore WordPress.Security.EscapeOutput ?></span></h1>
			<?php
			$wd_x_tag = function () use ( $wd_d ) {
				if ( $wd_d['tagline'] ) {
					echo '<p class="w-tagline">' . esc_html( $wd_d['tagline'] ) . '</p>';
				}
			};
			if ( 'royal' !== $wd_tpl ) {
				$wd_x_tag();
			}
			?>
			<ul class="w-facts">
				<?php foreach ( $wd_rows as $wd_r ) : ?>
					<?php if ( $wd_r[2] ) : ?><li><?php echo wd_icon( $wd_r[0], 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span class="w-facts__k"><?php echo esc_html( $wd_r[1] ); ?></span><span><?php echo esc_html( $wd_r[2] ); ?></span></li><?php endif; ?>
				<?php endforeach; ?>
			</ul>
			<?php
			if ( 'royal' === $wd_tpl ) {
				$wd_x_tag();
			}
			$wd_x_count();
			$wd_x_buttons();
			$wd_x_gift();
			?>
		</div>
	</div>
	<div class="wrap w-hero__foot"><?php $wd_x_buzz(); ?></div>
	<div class="share-sheet" hidden data-share-sheet>
		<a href="<?php echo esc_url( 'https://wa.me/?text=' . rawurlencode( wd_names( $wd_d ) . ' ' . __( 'are getting married! See their wedding page:', 'wwmd' ) . ' ' . $wd_url ) ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'whatsapp', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>WhatsApp</a>
		<a href="<?php echo esc_url( 'https://twitter.com/intent/tweet?url=' . rawurlencode( $wd_url ) . '&text=' . rawurlencode( wd_names( $wd_d ) . ( $wd_d['hashtag'] ? ' #' . $wd_d['hashtag'] : '' ) ) ); ?>" target="_blank" rel="noopener">X</a>
		<a href="<?php echo esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $wd_url ) ); ?>" target="_blank" rel="noopener">Facebook</a>
		<button type="button" data-copy="<?php echo esc_url( $wd_url ); ?>"><?php echo wd_icon( 'link', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Copy link', 'wwmd' ); ?></button>
	</div>
	<canvas hidden data-story-canvas width="1080" height="1920" data-names="<?php echo esc_attr( wd_names( $wd_d ) ); ?>" data-date="<?php echo esc_attr( wd_date( $wd_d['date'], 'j F Y' ) ); ?>" data-city="<?php echo esc_attr( $wd_d['city'] ); ?>" data-hash="<?php echo esc_attr( $wd_d['hashtag'] ); ?>" data-link="<?php echo esc_attr( preg_replace( '#^https?://#', '', untrailingslashit( $wd_url ) ) ); ?>" data-rank="<?php echo (int) $wd_rank; ?>" data-cover="<?php echo esc_url( $wd_d['cover'] ? wd_img_url( $wd_d['cover'], 'wd-cover' ) : '' ); ?>"></canvas>
</section>
