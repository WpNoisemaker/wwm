<?php
/**
 * One anonymous community post with its comments, Instagram style. Anyone can comment (1.7.0).
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
get_header();
the_post();
$wd_p     = get_post();
$wd_owner = array( (int) $wd_p->post_author, (string) get_post_meta( $wd_p->ID, '_wd_guest', true ) );
$wd_reps  = get_comments(
	array(
		'post_id' => $wd_p->ID,
		'type'    => 'wd_reply',
		'status'  => 'approve',
		'order'   => 'ASC',
		'number'  => 300,
	)
);
$wd_me    = wd_talk_me();
?>
<section class="section section--talk section--talk-one">
	<div class="wrap wrap--talk">
		<nav class="crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'wwmd' ); ?>"><a href="<?php echo esc_url( get_post_type_archive_link( 'wd_talk' ) ); ?>"><?php esc_html_e( 'Anonymous community', 'wwmd' ); ?></a></nav>
		<?php wd_notice(); ?>
		<?php echo wd_talk_card( $wd_p, true ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

		<section class="talk-replies" id="replies" aria-label="<?php esc_attr_e( 'Comments', 'wwmd' ); ?>">
			<h2><?php echo esc_html( sprintf( /* translators: %s comments */ _n( '%s comment', '%s comments', count( $wd_reps ), 'wwmd' ), number_format_i18n( count( $wd_reps ) ) ) ); ?></h2>
			<?php if ( $wd_reps ) : ?>
				<ol class="talk-replies__list">
					<?php
					foreach ( $wd_reps as $wd_c ) :
						$wd_cg  = (string) get_comment_meta( $wd_c->comment_ID, '_wd_guest', true );
						$wd_who = wd_talk_who( (int) $wd_c->user_id, $wd_p->ID, $wd_cg, (string) get_comment_meta( $wd_c->comment_ID, '_wd_name', true ) );
						$wd_op  = $wd_owner[0] ? (int) $wd_c->user_id === $wd_owner[0] : ( '' !== $wd_cg && $wd_cg === $wd_owner[1] );
						?>
						<li class="talk-reply" id="r<?php echo (int) $wd_c->comment_ID; ?>">
							<?php echo $wd_who[1]; // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<div class="talk-reply__body">
								<div class="talk-reply__text"><b class="talk-reply__name"><?php echo esc_html( $wd_who[0] ); ?></b><?php if ( $wd_op ) : ?><em class="talk__tag talk__tag--op"><?php esc_html_e( 'Posted this', 'wwmd' ); ?></em><?php endif; ?> <?php echo wd_talk_text( $wd_c->comment_content ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
								<div class="talk-reply__acts">
									<small><?php echo esc_html( wd_ago( $wd_c->comment_date_gmt ) ); ?></small>
									<?php
									if ( wd_talk_mine( (int) $wd_c->user_id, $wd_cg ) ) {
										echo wd_talk_form( 'delete', array( 'pid' => $wd_p->ID, 'cid' => $wd_c->comment_ID ), '<button class="linkish linkish--danger" type="submit" data-confirm="' . esc_attr__( 'Delete your comment?', 'wwmd' ) . '">' . esc_html__( 'Delete', 'wwmd' ) . '</button>' ); // phpcs:ignore WordPress.Security.EscapeOutput
									} else {
										echo wd_talk_form( 'report', array( 'pid' => $wd_p->ID, 'cid' => $wd_c->comment_ID ), '<button class="linkish" type="submit">' . esc_html__( 'Report', 'wwmd' ) . '</button>' ); // phpcs:ignore WordPress.Security.EscapeOutput
									}
									?>
								</div>
							</div>
						</li>
					<?php endforeach; ?>
				</ol>
			<?php else : ?>
				<p class="hint"><?php esc_html_e( 'No comments yet. Share what you know.', 'wwmd' ); ?></p>
			<?php endif; ?>

			<?php
			echo wd_talk_form( // phpcs:ignore WordPress.Security.EscapeOutput
				'reply',
				array( 'pid' => $wd_p->ID ),
				'<label class="screen-reader-text" for="talk-reply-name">' . esc_html__( 'Post as', 'wwmd' ) . '</label>'
				. '<input id="talk-reply-name" class="talk-reply__name-in" name="name" type="text" required maxlength="28" autocomplete="off" spellcheck="false" placeholder="' . esc_attr__( 'Any name', 'wwmd' ) . '">'
				. '<label class="screen-reader-text" for="talk-reply">' . esc_html__( 'Your comment', 'wwmd' ) . '</label>'
				. '<textarea id="talk-reply" name="body" rows="1" maxlength="1500" required placeholder="' . esc_attr__( 'Say something kind, or something true…', 'wwmd' ) . '"></textarea>'
				. '<button class="btn btn--brand btn--sm" type="submit">' . esc_html__( 'Post', 'wwmd' ) . '</button>',
				'talk-reply-form'
			);
			?>
		</section>
	</div>
</section>
<?php
get_footer();
