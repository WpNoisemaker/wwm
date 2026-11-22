<?php
/**
 * Blog post card.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_pid = (int) $args['id'];
$wd_cat = get_the_category( $wd_pid );
?>
<article class="b-card">
	<a class="b-card__link" href="<?php echo esc_url( get_permalink( $wd_pid ) ); ?>">
		<span class="b-card__media"><?php echo wd_post_cover( $wd_pid ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		<span class="b-card__body">
			<span class="b-card__meta"><?php if ( $wd_cat ) : ?><span class="chip"><?php echo esc_html( $wd_cat[0]->name ); ?></span><?php endif; ?><span><?php echo esc_html( sprintf( /* translators: %d minutes */ __( '%d min read', 'wwmd' ), wd_read_mins( $wd_pid ) ) ); ?></span></span>
			<span class="b-card__title"><?php echo esc_html( get_the_title( $wd_pid ) ); ?></span>
			<span class="b-card__text"><?php echo esc_html( wp_trim_words( get_the_excerpt( $wd_pid ), 24 ) ); ?></span>
		</span>
	</a>
</article>
