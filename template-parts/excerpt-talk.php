<?php
/**
 * The latest posts from the anonymous community (1.5.1), for the homepage & the landing page.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
if ( ! function_exists( 'wd_talk_query' ) ) {
	return;
}
$wd_tq = wd_talk_query( array( 'per' => 3 ) );
if ( ! $wd_tq->have_posts() ) {
	return;
}
$wd_tb = get_post_type_archive_link( 'wd_talk' );
?>
<section class="section section--talk-home">
	<div class="wrap">
		<div class="section-head">
			<div>
				<p class="eyebrow"><?php echo wd_icon( 'lock', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Anonymous community', 'wwmd' ); ?></p>
				<h2><?php echo esc_html( $args['title'] ?? __( 'What people are saying, anonymously', 'wwmd' ) ); ?></h2>
				<p class="section-head__sub"><?php esc_html_e( 'Love, family, money, faith and starting over. Say it under any name you like.', 'wwmd' ); ?></p>
			</div>
			<a class="link-arrow" href="<?php echo esc_url( $wd_tb ); ?>"><?php esc_html_e( 'Open the community', 'wwmd' ); ?><?php echo wd_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		</div>
		<div class="talk-row">
			<?php
			foreach ( $wd_tq->posts as $wd_tp ) {
				echo wd_talk_card( $wd_tp ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
			?>
		</div>
		<p class="talk-row__cta"><a class="btn btn--brand" href="<?php echo esc_url( $wd_tb . '?ask=1#ask' ); ?>"><?php echo wd_icon( 'lock', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Start a discussion', 'wwmd' ); ?></a></p>
	</div>
</section>
