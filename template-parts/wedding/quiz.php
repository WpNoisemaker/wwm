<?php
/**
 * Couple quiz & leaderboard.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_id    = $args['id'];
$wd_d     = $args['d'];
$wd_board = wd_quiz_board( $wd_id, 10 );
$wd_qs    = array_map(
	static function ( $q ) {
		return array(
			'q' => $q['q'],
			'a' => array_values( $q['a'] ),
		);
	},
	$wd_d['quiz']
);
?>
<section class="w-sec w-sec--pattern" id="quiz">
	<div class="wrap quiz-wrap">
		<div class="quiz card-form" data-quiz data-questions="<?php echo esc_attr( wp_json_encode( $wd_qs ) ); ?>" data-wedding="<?php echo (int) $wd_id; ?>">
			<div data-quiz-start>
				<p class="w-eyebrow"><?php esc_html_e( 'Couple quiz', 'wwmd' ); ?></p>
				<h2 class="w-title"><?php echo esc_html( sprintf( /* translators: %s names */ __( 'How well do you know %s?', 'wwmd' ), wd_names( $wd_d ) ) ); ?></h2>
				<p><?php echo esc_html( sprintf( /* translators: %d questions */ _n( '%d question. Fastest perfect score tops the board.', '%d questions. Fastest perfect score tops the board.', count( $wd_qs ), 'wwmd' ), count( $wd_qs ) ) ); ?></p>
				<label class="field"><span><?php esc_html_e( 'Your name for the leaderboard', 'wwmd' ); ?></span><input name="name" maxlength="40" data-quiz-name></label>
				<button class="btn w-btn w-btn--accent btn--lg" type="button" data-quiz-go><?php esc_html_e( 'Start the quiz', 'wwmd' ); ?></button>
			</div>
			<div data-quiz-play hidden>
				<p class="quiz__progress"><span data-quiz-n></span><i><b data-quiz-bar></b></i></p>
				<h3 class="quiz__q" data-quiz-q></h3>
				<div class="quiz__opts" data-quiz-opts></div>
			</div>
			<div data-quiz-done hidden>
				<p class="quiz__score"><b data-quiz-score></b></p>
				<p data-quiz-msg></p>
				<button class="btn w-btn" type="button" data-share data-url="<?php echo esc_url( get_permalink( $wd_id ) . '#quiz' ); ?>" data-title="<?php echo esc_attr( sprintf( /* translators: %s names */ __( 'Can you beat my score on the %s quiz?', 'wwmd' ), wd_names( $wd_d ) ) ); ?>"><?php esc_html_e( 'Challenge a friend', 'wwmd' ); ?></button>
				<button class="btn w-btn w-btn--ghost" type="button" data-quiz-again><?php esc_html_e( 'Play again', 'wwmd' ); ?></button>
			</div>
		</div>
		<div class="leader card-form">
			<h3><?php echo wd_icon( 'trophy', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Leaderboard', 'wwmd' ); ?></h3>
			<ol data-quiz-board>
				<?php foreach ( $wd_board as $wd_r ) : ?>
					<li><span><?php echo esc_html( $wd_r['name'] ); ?></span><b><?php echo (int) $wd_r['score']; ?>/<?php echo (int) $wd_r['total']; ?></b><small><?php echo (int) $wd_r['seconds']; ?>s</small></li>
				<?php endforeach; ?>
			</ol>
			<?php if ( ! $wd_board ) : ?><p class="empty-note"><?php esc_html_e( 'Nobody yet. Go first!', 'wwmd' ); ?></p><?php endif; ?>
		</div>
	</div>
</section>
