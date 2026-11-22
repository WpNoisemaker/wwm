<?php
/**
 * Ivory & Gold: the story as a leather diary. The pages are plain HTML; ivory.js turns them into a
 * book you can page through (two pages at a time on wide screens, one at a time on phones).
 * Without JavaScript the pages sit side by side & scroll.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_d     = $args['d'];
$wd_story = array_values( (array) $wd_d['story'] );
$wd_n     = count( $wd_story );
$wd_f1    = strtok( (string) $wd_d['p1'], ' ' );
$wd_f2    = strtok( (string) $wd_d['p2'], ' ' );
$wd_mono  = wd_initials( $wd_d );
$wd_seal  = '<span class="d-seal"><span>' . esc_html( $wd_mono ) . '</span></span>';
$wd_monog = '<span class="d-mono">' . esc_html( $wd_mono ) . '</span>';
?>
<section class="w-sec iv-sec iv-story" id="story">
	<div class="wrap">
		<div class="iv-head">
			<p class="iv-kicker"><?php esc_html_e( 'Our story', 'wwmd' ); ?></p>
			<h2 class="iv-title"><?php esc_html_e( 'How We Met', 'wwmd' ); ?></h2>
			<p class="iv-sub"><?php esc_html_e( 'A few pages from our diary', 'wwmd' ); ?></p>
			<span class="iv-rule" aria-hidden="true"><i class="iv-gem"></i></span>
		</div>
		<div class="diary" data-diary data-entries="<?php echo (int) $wd_n; ?>" data-l-pages="<?php /* translators: 1: first page 2: second page 3: pages in all */ esc_attr_e( 'Pages %1$s–%2$s of %3$s', 'wwmd' ); ?>" data-l-page="<?php /* translators: 1: page 2: pages in all */ esc_attr_e( 'Page %1$s of %2$s', 'wwmd' ); ?>">
			<div class="diary__stage">
				<div class="diary__book" data-diary-book tabindex="0" aria-roledescription="<?php esc_attr_e( 'book', 'wwmd' ); ?>" aria-label="<?php esc_attr_e( 'Our diary. Use the arrow keys to turn the pages.', 'wwmd' ); ?>">
					<div class="diary__pages" data-diary-pages>
						<div class="dpage dpage--cover" data-label="<?php esc_attr_e( 'Front cover', 'wwmd' ); ?>">
							<div class="d-leather">
								<p class="d-eyebrow"><?php esc_html_e( 'Our diary', 'wwmd' ); ?></p>
								<p class="d-cover-title"><?php esc_html_e( 'How we met', 'wwmd' ); ?></p>
								<span class="d-orn" aria-hidden="true"></span>
								<p class="d-cover-names"><?php echo esc_html( $wd_f1 . ' & ' . $wd_f2 ); ?></p>
								<?php echo $wd_seal; // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</div>
						</div>
						<div class="dpage dpage--endpaper" data-spread-only>
							<div class="d-plate">
								<p class="d-eyebrow"><?php esc_html_e( 'This diary belongs to', 'wwmd' ); ?></p>
								<p class="d-plate-names"><?php echo esc_html( $wd_f1 ); ?> <span>&amp;</span> <?php echo esc_html( $wd_f2 ); ?></p>
								<?php echo $wd_monog; // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</div>
						</div>
						<div class="dpage dpage--paper dpage--title" data-label="<?php esc_attr_e( 'Title page', 'wwmd' ); ?>">
							<p class="d-eyebrow"><?php esc_html_e( 'Chapter one', 'wwmd' ); ?></p>
							<p class="d-title"><?php esc_html_e( 'How we met', 'wwmd' ); ?></p>
							<p class="d-title-sub"><?php esc_html_e( 'A few pages from our diary', 'wwmd' ); ?></p>
							<?php echo $wd_monog; // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</div>
						<?php
						foreach ( $wd_story as $wd_i => $wd_s ) :
							$wd_lines = array_values( array_filter( array_map( 'trim', preg_split( '/\R/', (string) $wd_s['text'] ) ), 'strlen' ) );
							$wd_sign  = '';
							if ( $wd_lines && preg_match( '/^[—–-]\s*(.+)$/u', end( $wd_lines ), $wd_mm ) ) {
								$wd_sign = $wd_mm[1];
								array_pop( $wd_lines );
							}
							$wd_long = mb_strlen( implode( ' ', $wd_lines ) ) > ( $wd_s['image'] ? 190 : 320 );
							?>
							<div class="dpage dpage--paper dpage--entry<?php echo ( $wd_i % 2 ) ? ' is-even' : ''; ?><?php echo $wd_long ? ' is-long' : ''; ?><?php echo $wd_s['image'] ? ' has-photo' : ''; ?>" data-entry="<?php echo (int) $wd_i + 1; ?>">
								<?php if ( $wd_s['when'] ) : ?><p class="entry-date"><?php echo esc_html( $wd_s['when'] ); ?></p><?php endif; ?>
								<?php if ( $wd_s['title'] ) : ?><h3 class="entry-place"><?php echo esc_html( $wd_s['title'] ); ?></h3><?php endif; ?>
								<?php if ( $wd_s['image'] ) : ?>
									<figure class="entry-photo"><span class="entry-tape" aria-hidden="true"></span><img src="<?php echo esc_url( wd_img_url( $wd_s['image'], 'wd-card' ) ); ?>" alt="<?php echo esc_attr( $wd_s['title'] ); ?>" loading="lazy"></figure>
								<?php endif; ?>
								<div class="entry-lines">
									<?php foreach ( $wd_lines as $wd_l ) : ?><p><?php echo esc_html( $wd_l ); ?></p><?php endforeach; ?>
								</div>
								<?php if ( $wd_sign ) : ?><p class="entry-sign">— <?php echo esc_html( $wd_sign ); ?></p><?php endif; ?>
								<span class="entry-num"><?php echo (int) $wd_i + 1; ?></span>
							</div>
						<?php endforeach; ?>
						<?php if ( $wd_n % 2 ) : ?>
							<div class="dpage dpage--paper dpage--blank" data-spread-only></div>
						<?php endif; ?>
						<div class="dpage dpage--paper dpage--end" data-label="<?php esc_attr_e( 'To be continued', 'wwmd' ); ?>">
							<p class="d-continued"><?php esc_html_e( 'to be continued…', 'wwmd' ); ?></p>
							<?php echo $wd_monog; // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<p class="d-end-names"><?php echo esc_html( $wd_f1 . ' & ' . $wd_f2 ); ?></p>
							<?php if ( $wd_d['date'] ) : ?><p class="d-eyebrow"><?php esc_html_e( 'Our next chapter begins', 'wwmd' ); ?></p><p class="d-end-date"><?php echo esc_html( wd_date( $wd_d['date'], 'F j, Y' ) ); ?></p><?php endif; ?>
						</div>
						<div class="dpage dpage--endpaper" data-spread-only>
							<div class="d-plate d-plate--back">
								<?php echo $wd_monog; // phpcs:ignore WordPress.Security.EscapeOutput ?>
								<p class="d-eyebrow"><?php echo esc_html( wd_names( $wd_d ) ); ?></p>
							</div>
						</div>
						<div class="dpage dpage--back" data-spread-only data-label="<?php esc_attr_e( 'Back cover', 'wwmd' ); ?>">
							<div class="d-leather d-leather--back"><?php echo $wd_seal; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
						</div>
					</div>
				</div>
			</div>
			<div class="diary__nav" hidden data-diary-nav>
				<button type="button" class="diary__btn" data-diary-prev><span aria-hidden="true">‹</span> <?php esc_html_e( 'Previous', 'wwmd' ); ?></button>
				<span class="diary__where" data-diary-where aria-live="polite"></span>
				<button type="button" class="diary__btn" data-diary-next><?php esc_html_e( 'Next', 'wwmd' ); ?> <span aria-hidden="true">›</span></button>
			</div>
			<p class="diary__hint" hidden data-diary-hint><?php esc_html_e( 'Tap the page or swipe to turn it', 'wwmd' ); ?></p>
		</div>
	</div>
</section>
