<?php
/**
 * The anonymous community feed: posts like a status on a brand colour, laid out like Instagram, with
 * guests' love notes & photos from public wedding pages in between. Nobody needs to sign in (1.7.0).
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
get_header();
// phpcs:disable WordPress.Security.NonceVerification
$wd_g     = wp_unslash( $_GET );
$wd_topic = isset( $wd_g['topic'] ) ? sanitize_key( $wd_g['topic'] ) : '';
$wd_sort  = isset( $wd_g['sort'] ) && 'top' === $wd_g['sort'] ? 'top' : 'new';
$wd_paged = max( 1, (int) get_query_var( 'paged' ) );
// phpcs:enable
$wd_tops  = wd_talk_topics();
$wd_topic = isset( $wd_tops[ $wd_topic ] ) ? $wd_topic : '';
$wd_base  = get_post_type_archive_link( 'wd_talk' );
$wd_q     = wd_talk_query(
	array(
		'topic' => $wd_topic,
		'sort'  => $wd_sort,
		'paged' => $wd_paged,
	)
);
$wd_keep  = array_filter(
	array(
		'topic' => $wd_topic,
		'sort'  => 'top' === $wd_sort ? 'top' : '',
	)
);
// The feed is words only. No photographs, no wedding pages, no couples' names — just what people wrote.
$wd_bgs  = wd_talk_bgs();
$wd_me   = wd_talk_me();
?>
<section class="page-hero page-hero--talk">
	<div class="wrap wrap--talk">
		<p class="eyebrow"><?php echo wd_icon( 'lock', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Anonymous community', 'wwmd' ); ?></p>
		<h1><?php esc_html_e( 'Say the thing you can’t say out loud.', 'wwmd' ); ?></h1>
		<p class="lead"><?php esc_html_e( 'The hard, honest conversations about love, family, money, faith and starting over — the ones that are difficult to have anywhere else. Whatever you are carrying, somebody here has carried it too. Post under any name you like. No account, no profile, nothing to trace back to you.', 'wwmd' ); ?></p>
	</div>
</section>

<section class="section section--talk">
	<div class="wrap wrap--talk">
		<?php wd_notice(); ?>

		<details class="talk-new"<?php echo empty( $wd_g['ask'] ) ? '' : ' open'; ?> id="ask">
			<summary><?php echo wd_icon( 'plus', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Start a discussion', 'wwmd' ); ?></span></summary>
			<?php
			$wd_pick = '';
			foreach ( $wd_tops as $wd_k => $wd_t ) {
				$wd_pick .= '<label class="chip chip--pick"><input type="radio" name="topic" value="' . esc_attr( $wd_k ) . '" required' . checked( $wd_topic, $wd_k, false ) . '>' . wd_icon( $wd_t[1], 15 ) . '<span>' . esc_html( $wd_t[0] ) . '</span></label>';
			}
			$wd_dots = '';
			$wd_i    = 0;
			foreach ( $wd_bgs as $wd_k => $wd_b ) {
				$wd_dots .= '<label class="talk-dot" style="--st-bg:' . esc_attr( $wd_b[1] ) . '"><input type="radio" name="bg" value="' . esc_attr( $wd_k ) . '" data-bg="' . esc_attr( $wd_b[1] ) . '" data-on="' . esc_attr( $wd_b[2] ) . '"' . checked( 0, $wd_i, false ) . '><span class="screen-reader-text">' . esc_html( $wd_b[0] ) . '</span></label>';
				++$wd_i;
			}
			$wd_first = reset( $wd_bgs );
			echo wd_talk_form( // phpcs:ignore WordPress.Security.EscapeOutput
				'post',
				array(),
				'<div class="talk-name"><label class="field" for="talk-name"><span>' . esc_html__( '1. Post as', 'wwmd' ) . '</span>'
				. '<input id="talk-name" name="name" type="text" required maxlength="28" autocomplete="off" spellcheck="false" placeholder="' . esc_attr__( 'Any name you like', 'wwmd' ) . '"></label>'
				. '<p class="hint hint--tight">' . esc_html__( 'Make one up. It is not checked against anything and it is not saved to any account.', 'wwmd' ) . '</p></div>'
				. '<fieldset class="talk-pick"><legend>' . esc_html__( '2. Pick a topic', 'wwmd' ) . '</legend><div class="talk-pick__row">' . $wd_pick . '</div></fieldset>'
				. '<div class="talk-compose" data-talk-compose>'
				. '<p class="talk-compose__label" id="talk-say">' . esc_html__( '3. Write freely', 'wwmd' ) . '</p>'
				. '<div class="talk-status talk-status--l talk-status--edit" style="--st-bg:' . esc_attr( $wd_first[1] ) . ';--st-on:' . esc_attr( $wd_first[2] ) . '">'
				. '<textarea name="body" rows="5" minlength="3" maxlength="2000" required aria-labelledby="talk-say" placeholder="' . esc_attr__( 'Type your question, story or advice…', 'wwmd' ) . '"></textarea>'
				. '<span class="talk-status__mark" aria-hidden="true">' . esc_html( wd_talk_mark() ) . '</span></div>'
				. '<fieldset class="talk-dots"><legend>' . esc_html__( '4. Pick a background', 'wwmd' ) . '</legend>' . $wd_dots . '</fieldset>'
				. '</div>'
				. '<p class="hint">' . wd_icon( 'shield', 15 ) . esc_html__( 'No sign-in, no profile. Only the name you typed is shown with your words. Please do not post phone numbers, bank details or anybody else’s name. Posts reported by three people are hidden until they are checked.', 'wwmd' ) . '</p>'
				. '<div class="form-actions"><button class="btn btn--brand btn--block" type="submit">' . wd_icon( 'lock', 18 ) . esc_html__( 'Post anonymously', 'wwmd' ) . '</button></div>',
				'talk-new__form'
			);
			?>
		</details>

		<nav class="talk-bar" aria-label="<?php esc_attr_e( 'Topics', 'wwmd' ); ?>">
			<ul class="talk-bar__topics">
				<li><a class="chip<?php echo $wd_topic ? '' : ' is-on'; ?>" href="<?php echo esc_url( add_query_arg( array_filter( array( 'sort' => $wd_keep['sort'] ?? '' ) ), $wd_base ) ); ?>"><span><?php esc_html_e( 'All', 'wwmd' ); ?></span></a></li>
				<?php foreach ( $wd_tops as $wd_k => $wd_t ) : ?>
					<li><a class="chip<?php echo $wd_k === $wd_topic ? ' is-on' : ''; ?>" href="<?php echo esc_url( add_query_arg( array_filter( array( 'topic' => $wd_k, 'sort' => $wd_keep['sort'] ?? '' ) ), $wd_base ) ); ?>"><?php echo wd_icon( $wd_t[1], 15 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $wd_t[0] ); ?></span></a></li>
				<?php endforeach; ?>
			</ul>
			<p class="talk-bar__sort">
				<a class="<?php echo 'top' === $wd_sort ? 'is-on' : ''; ?>" href="<?php echo esc_url( add_query_arg( array_filter( array( 'topic' => $wd_topic, 'sort' => 'top' ) ), $wd_base ) ); ?>"><?php echo wd_icon( 'fire', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Trending', 'wwmd' ); ?></span></a>
				<a class="<?php echo 'new' === $wd_sort ? 'is-on' : ''; ?>" href="<?php echo esc_url( add_query_arg( array_filter( array( 'topic' => $wd_topic ) ), $wd_base ) ); ?>"><?php echo wd_icon( 'clock', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Newest', 'wwmd' ); ?></span></a>
				<button class="talk-saved-btn" type="button" data-talk-saved aria-pressed="false"><?php echo wd_icon( 'bookmark', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Saved', 'wwmd' ); ?></span></button>
			</p>
		</nav>

		<?php if ( $wd_q->have_posts() ) : ?>
			<div class="talk-deck" data-talk-deck>
				<div class="talk-feed" data-grid>
					<?php
					foreach ( $wd_q->posts as $wd_p ) {
						echo wd_talk_card( $wd_p ); // phpcs:ignore WordPress.Security.EscapeOutput
					}
					?>
				</div>
			</div>
			<?php echo wd_talk_deck_acts(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( $wd_q->max_num_pages > $wd_paged ) : ?>
				<div class="center load-more"><a class="btn btn--ghost" href="<?php echo esc_url( add_query_arg( $wd_keep, trailingslashit( $wd_base ) . 'page/' . ( $wd_paged + 1 ) . '/' ) ); ?>" data-load-more><?php esc_html_e( 'Show more posts', 'wwmd' ); ?></a></div>
			<?php endif; ?>
			<?php if ( $wd_paged > 1 ) : ?>
				<p class="center"><a class="link-arrow" href="<?php echo esc_url( add_query_arg( $wd_keep, $wd_base ) ); ?>"><?php esc_html_e( 'Back to the newest', 'wwmd' ); ?></a></p>
			<?php endif; ?>
		<?php else : ?>
			<div class="empty card">
				<p><?php esc_html_e( 'Nothing here yet. Be the first to share.', 'wwmd' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
