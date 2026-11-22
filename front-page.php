<?php
/**
 * Home: greet, search, the Buzz Chart (Billboard style), fresh pages, wedding styles, features, the wedding map,
 * plans, vendors, services & FAQs.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
get_header();

$wd_greets = array(
	array( 'Barka da aure!', __( 'Hausa', 'wwmd' ), __( 'Congratulations on your marriage', 'wwmd' ) ),
	array( 'Ẹ kú ayẹyẹ!', __( 'Yoruba', 'wwmd' ), __( 'Well done on the celebration', 'wwmd' ) ),
	array( 'Hongera kwa harusi!', __( 'Swahili', 'wwmd' ), __( 'Congratulations on the wedding', 'wwmd' ) ),
	array( 'Ayekoo!', __( 'Twi', 'wwmd' ), __( 'Well done!', 'wwmd' ) ),
	array( 'Nnọọ!', __( 'Igbo', 'wwmd' ), __( 'Welcome!', 'wwmd' ) ),
	array( 'Halala!', __( 'Zulu', 'wwmd' ), __( 'Celebrate!', 'wwmd' ) ),
	array( 'Una go last o!', __( 'Pidgin', 'wwmd' ), __( 'Your love will last!', 'wwmd' ) ),
);
$wd_top_week = wd_chart_ids( 10 );
$wd_lead     = $wd_top_week ? wd_get( $wd_top_week[0] ) : null;
$wd_spot     = get_posts(
	array(
		'post_type'      => 'wd_wedding',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_query'     => array( array( 'key' => '_wd_spotlight', 'value' => time(), 'compare' => '>', 'type' => 'NUMERIC' ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
	)
);
?>
<section class="hero">
	<div class="wrap hero__inner">
		<p class="hero__greet" data-greet aria-live="polite">
			<?php foreach ( $wd_greets as $wd_i => $wd_g ) : ?>
				<span class="hero__word<?php echo 0 === $wd_i ? ' is-on' : ''; ?>" lang="<?php echo esc_attr( array( 'ha', 'yo', 'sw', 'tw', 'ig', 'zu', 'pcm' )[ $wd_i ] ); ?>"><?php echo esc_html( $wd_g[0] ); ?><small><?php echo esc_html( $wd_g[1] . ' · ' . $wd_g[2] ); ?></small></span>
			<?php endforeach; ?>
		</p>
		<h1 class="hero__title"><?php esc_html_e( 'Be the wedding everyone is talking about', 'wwmd' ); ?></h1>
		<p class="hero__lead"><?php esc_html_e( 'Don’t just watch other weddings trend. Make a free page for yours: guests RSVP, send cash gifts & love notes, & your wedding climbs the chart with Nigerian & African weddings at home & abroad.', 'wwmd' ); ?></p>
		<p class="hero__cta"><a class="btn btn--marigold btn--lg" href="<?php echo esc_url( is_user_logged_in() ? wd_account_url( 'new' ) : wd_url( 'sign-in', array( 'tab' => 'join' ) ) ); ?>"><?php esc_html_e( 'List your wedding', 'wwmd' ); ?></a></p>
		<form class="filters filters--one filters--hero" action="<?php echo esc_url( wd_url( 'explore' ) ); ?>" method="get" role="search">
			<div class="filters__search"><?php echo wd_icon( 'search', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><label class="screen-reader-text" for="hero-q"><?php esc_html_e( 'Search weddings', 'wwmd' ); ?></label><input id="hero-q" type="search" name="q" placeholder="<?php esc_attr_e( 'Names, #hashtags or cities', 'wwmd' ); ?>"></div>
			<button class="btn btn--ink" type="submit"><?php echo wd_icon( 'search', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Find weddings', 'wwmd' ); ?></span></button>
		</form>
		<ul class="hero__chips">
			<li><a class="chip chip--live" href="<?php echo esc_url( wd_url( 'explore', array( 'sort' => 'soon' ) ) ); ?>"><?php esc_html_e( 'Coming up', 'wwmd' ); ?></a></li>
			<?php
			foreach ( array( 'lagos', 'abuja', 'accra', 'nairobi', 'london', 'houston' ) as $wd_slug ) :
				$wd_term = get_term_by( 'slug', $wd_slug, 'wd_place' );
				if ( $wd_term ) :
					?>
					<li><a class="chip" href="<?php echo esc_url( get_term_link( $wd_term ) ); ?>"><?php echo esc_html( $wd_term->name ); ?></a></li>
					<?php
				endif;
			endforeach;
			?>
			<li><a class="chip" href="<?php echo esc_url( wd_url( 'rankings' ) ); ?>"><?php esc_html_e( 'Top 10 this week', 'wwmd' ); ?></a></li>
		</ul>
		<?php if ( $wd_lead ) : ?>
			<a class="sticker sticker--trend" href="<?php echo esc_url( get_permalink( $wd_top_week[0] ) ); ?>"><?php echo wd_icon( 'fire', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><b><?php echo esc_html( $wd_lead['hashtag'] ? '#' . $wd_lead['hashtag'] : wd_names( $wd_lead ) ); ?></b> <?php esc_html_e( 'is No. 1 on the Buzz Chart', 'wwmd' ); ?></span></a>
		<?php endif; ?>
		<span class="sticker sticker--love" aria-hidden="true"><?php echo wd_icon( 'heart', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Una go last o!', 'wwmd' ); ?></span>
	</div>
	<div class="hero__city" aria-hidden="true">
		<?php echo wd_art( 'skyline' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<div class="hero__convoy">
			<span class="convoy__car convoy__car--suv convoy__car--last"><?php echo wd_art( 'suv' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			<span class="convoy__car convoy__car--suv"><?php echo wd_art( 'suv' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			<span class="convoy__car convoy__car--limo"><?php echo wd_art( 'limo' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			<span class="convoy__car convoy__car--bride"><?php echo wd_art( 'bridal' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		</div>
	</div>
</section>

<?php if ( $wd_top_week ) : ?>
<section class="section section--chart" id="chart">
	<div class="wrap">
		<div class="billboard">
			<header class="billboard__head">
				<div>
					<p class="billboard__kicker"><?php echo wd_icon( 'trophy', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'The Buzz Chart', 'wwmd' ); ?></p>
					<h2><?php esc_html_e( 'This week’s top 10 weddings', 'wwmd' ); ?></h2>
					<p class="billboard__date"><?php echo esc_html( sprintf( /* translators: %s date */ __( 'Week of %s · ranked by Buzz coins from guest activity', 'wwmd' ), wp_date( 'j F Y', strtotime( 'monday this week' ) ) ) ); ?></p>
				</div>
				<a class="btn btn--ink" href="<?php echo esc_url( wd_url( 'rankings' ) ); ?>"><?php esc_html_e( 'See the full chart', 'wwmd' ); ?><?php echo wd_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			</header>
			<?php get_template_part( 'template-parts/chart', null, array( 'n' => 10 ) ); ?>
			<footer class="billboard__foot">
				<span><?php esc_html_e( 'Coins for', 'wwmd' ); ?></span>
				<?php foreach ( wd_coin_figures() as $wd_k => $wd_sig ) : ?>
					<?php if ( wd_coin_rate( $wd_k ) < 1 ) { continue; } ?>
					<span class="chip"><?php echo wd_icon( $wd_sig['icon'], 15 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $wd_sig['short'] ); ?></span>
				<?php endforeach; ?>
			</footer>
		</div>
		<?php if ( $wd_spot ) : ?>
			<div class="spot-strip"><span class="tag tag--sponsored"><?php esc_html_e( 'Sponsored', 'wwmd' ); ?></span><?php get_template_part( 'template-parts/card-wedding', null, array( 'id' => $wd_spot[0], 'wide' => true ) ); ?></div>
		<?php endif; ?>
	</div>
</section>
<?php endif; ?>

<?php
$wd_new = get_posts(
	array(
		'post_type'      => 'wd_wedding',
		'posts_per_page' => 8,
		'fields'         => 'ids',
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);
if ( $wd_new ) :
	?>
<section class="section section--fresh">
	<div class="wrap">
		<div class="section-head">
			<div>
				<p class="eyebrow"><?php echo wd_icon( 'plus', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Just added', 'wwmd' ); ?></p>
				<h2><?php esc_html_e( 'Fresh wedding pages', 'wwmd' ); ?></h2>
			</div>
			<a class="link-arrow" href="<?php echo esc_url( wd_url( 'explore', array( 'sort' => 'new' ) ) ); ?>"><?php esc_html_e( 'Browse all weddings', 'wwmd' ); ?><?php echo wd_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		</div>
	</div>
	<div class="rail" data-rail>
		<?php foreach ( $wd_new as $wd_id ) : ?>
			<div class="rail__item"><?php get_template_part( 'template-parts/card-wedding', null, array( 'id' => $wd_id ) ); ?></div>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>

<section class="section section--styles">
	<div class="wrap">
		<div class="section-head">
			<div>
				<p class="eyebrow"><?php esc_html_e( 'Every kind of celebration', 'wwmd' ); ?></p>
				<h2><?php esc_html_e( 'Owambe, Igba nkwu, Kamu, white wedding or Nikkah. Pick a style', 'wwmd' ); ?></h2>
			</div>
		</div>
		<div class="fold" data-fold>
			<?php
			$wd_i = 0;
			foreach ( wd_styles() as $wd_slug => $wd_s ) :
				$wd_term  = get_term_by( 'slug', $wd_slug, 'wd_style' );
				$wd_count = $wd_term ? (int) $wd_term->count : 0;
				$wd_link  = $wd_term ? get_term_link( $wd_term ) : wd_url( 'explore' );
				$wd_desc  = array(
					'traditional'   => __( 'Engagements, introductions & the ceremonies that come before the white wedding: gele, agbada, kola, palm wine & the loudest talking drums.', 'wwmd' ),
					'white-wedding' => __( 'Church or garden vows, the reception, first dance & the cake. Often the same weekend as the traditional.', 'wwmd' ),
					'nikkah'        => __( 'Nikkah, Kamu, Walima & dinner. Elegant Northern & Muslim weddings with family at the centre.', 'wwmd' ),
					'court'         => __( 'Registry & city hall weddings, then lunch with the people who matter. Small guest list, big love.', 'wwmd' ),
					'destination'   => __( 'Couples who flew everyone out: beaches, mountains & cities across Africa & beyond.', 'wwmd' ),
					'diaspora'      => __( 'London, Houston, Toronto & Dublin. African weddings abroad, with family at home watching live.', 'wwmd' ),
				)[ $wd_slug ];
				?>
				<a class="fold__panel tone-<?php echo esc_attr( $wd_s[2] ); ?><?php echo 0 === $wd_i ? ' is-open' : ''; ?>" href="<?php echo esc_url( $wd_link ); ?>" data-fold-panel>
					<span class="fold__spine"><?php echo esc_html( $wd_s[0] ); ?></span>
					<span class="fold__body">
						<span class="fold__count"><?php echo esc_html( sprintf( /* translators: %d weddings */ _n( '%d wedding', '%d weddings', $wd_count, 'wwmd' ), $wd_count ) ); ?></span>
						<span class="fold__title"><?php echo esc_html( $wd_s[0] ); ?></span>
						<span class="fold__text"><?php echo esc_html( $wd_desc ); ?></span>
						<span class="fold__chips"><?php foreach ( $wd_s[3] as $wd_c ) : ?><span class="chip chip--solid"><?php echo esc_html( $wd_c ); ?></span><?php endforeach; ?></span>
						<span class="fold__art"><?php echo wd_art( $wd_s[1] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					</span>
				</a>
				<?php
				++$wd_i;
			endforeach;
			?>
		</div>
	</div>
</section>

<section class="section section--dark">
	<div class="wrap">
		<div class="section-head section-head--light">
			<div>
				<p class="eyebrow"><?php esc_html_e( 'For couples', 'wwmd' ); ?></p>
				<h2><?php esc_html_e( 'One link for everything your guests need', 'wwmd' ); ?></h2>
			</div>
			<p class="section-head__aside"><?php esc_html_e( 'Put it in your Instagram bio, your IV & your family WhatsApp group. Guests RSVP, play, give & celebrate on one page.', 'wwmd' ); ?></p>
		</div>
		<div class="feature-grid">
			<?php
			$wd_feats = array(
				array( 'ticket', 'butter', __( 'RSVP & entry passes', 'wwmd' ), __( 'Guests RSVP in seconds. Premium guests get an entry pass with a QR code, & your ushers check them in at the gate.', 'wwmd' ) ),
				array( 'cash', 'mint', __( 'Spray money from anywhere', 'wwmd' ), __( 'Family abroad tap to spray notes on the couple, live, with the naira raining across the screen.', 'wwmd' ) ),
				array( 'chat', 'coral', __( 'Wall of love', 'wwmd' ), __( 'Goodwill messages, prayers & jokes from everyone who loves you, with likes.', 'wwmd' ) ),
				array( 'camera', 'sky', __( 'Guest photo gallery', 'wwmd' ), __( 'Guests upload what they shot on the day. The best ones rise to the top.', 'wwmd' ) ),
				array( 'trophy', 'lavender', __( 'Couple quiz & leaderboard', 'wwmd' ), __( '“Who said I love you first?” Your guests compete to prove who knows you best.', 'wwmd' ) ),
				array( 'gift', 'butter', __( 'Cash & gift registry', 'wwmd' ), __( 'Cash funds for the honeymoon or your first home, plus gifts from any store.', 'wwmd' ) ),
				array( 'users', 'mint', __( 'Bridesmaids & groomsmen', 'wwmd' ), __( 'Introduce your squad with a line about each of them.', 'wwmd' ) ),
				array( 'live', 'coral', __( 'Livestream', 'wwmd' ), __( 'Your stream, right on your page, for everyone who couldn’t make it.', 'wwmd' ) ),
				array( 'star', 'sky', __( 'Vendor reviews', 'wwmd' ), __( 'Guests rate your photographer, caterer & DJ. Great vendors get found.', 'wwmd' ) ),
			);
			foreach ( $wd_feats as $wd_f ) :
				?>
				<div class="feature tone-<?php echo esc_attr( $wd_f[1] ); ?>">
					<span class="feature__icon"><?php echo wd_icon( $wd_f[0], 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<h3><?php echo esc_html( $wd_f[2] ); ?></h3>
					<p><?php echo esc_html( $wd_f[3] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="center"><a class="btn btn--marigold btn--lg" href="<?php echo esc_url( is_user_logged_in() ? wd_account_url( 'new' ) : wd_url( 'sign-in', array( 'tab' => 'join' ) ) ); ?>"><?php esc_html_e( 'Create your free wedding page', 'wwmd' ); ?></a></div>
	</div>
</section>

<?php get_template_part( 'template-parts/map' ); ?>

<section class="section section--plans">
	<div class="wrap">
		<div class="section-head">
			<div>
				<p class="eyebrow"><?php esc_html_e( 'Free or Premium', 'wwmd' ); ?></p>
				<h2><?php esc_html_e( 'Start free. Go Premium when you want the full owambe', 'wwmd' ); ?></h2>
			</div>
		</div>
		<?php get_template_part( 'template-parts/plans' ); ?>
	</div>
</section>

<?php
$wd_vendors = get_posts(
	array(
		'post_type'      => 'wd_vendor',
		'posts_per_page' => 4,
		'fields'         => 'ids',
		'meta_key'       => '_wd_rating_score', // phpcs:ignore WordPress.DB.SlowDBQuery
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
	)
);
if ( $wd_vendors ) :
	?>
<section class="section section--vendors">
	<div class="wrap">
		<div class="section-head">
			<div>
				<p class="eyebrow"><?php echo wd_icon( 'star', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Rated by real guests', 'wwmd' ); ?></p>
				<h2><?php esc_html_e( 'Vendors guests can’t stop talking about', 'wwmd' ); ?></h2>
			</div>
			<a class="link-arrow" href="<?php echo esc_url( wd_url( 'vendors' ) ); ?>"><?php esc_html_e( 'All vendors', 'wwmd' ); ?><?php echo wd_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		</div>
		<div class="v-grid">
			<?php foreach ( $wd_vendors as $wd_vid ) : ?>
				<?php get_template_part( 'template-parts/card-vendor', null, array( 'id' => $wd_vid ) ); ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php get_template_part( 'template-parts/excerpt-market' ); ?>

<?php get_template_part( 'template-parts/excerpt-community' ); ?>

<?php get_template_part( 'template-parts/excerpt-talk' ); ?>

<?php
$wd_posts = get_posts(
	array(
		'post_type'   => 'post',
		'numberposts' => 3,
	)
);
if ( $wd_posts ) :
	?>
<section class="section section--blog">
	<div class="wrap">
		<div class="section-head">
			<div>
				<p class="eyebrow"><?php esc_html_e( 'From the blog', 'wwmd' ); ?></p>
				<h2><?php esc_html_e( 'Wedding tips & how-tos', 'wwmd' ); ?></h2>
			</div>
			<a class="link-arrow" href="<?php echo esc_url( wd_url( 'blog' ) ); ?>"><?php esc_html_e( 'All posts', 'wwmd' ); ?><?php echo wd_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		</div>
		<div class="b-grid">
			<?php
			foreach ( $wd_posts as $wd_p ) {
				get_template_part( 'template-parts/card-post', null, array( 'id' => $wd_p->ID ) );
			}
			?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php get_template_part( 'template-parts/faq' ); ?>

<section class="section section--cta">
	<div class="wrap cta-band">
		<div class="cta-band__text">
			<h2><?php esc_html_e( 'Your wedding deserves its own page', 'wwmd' ); ?></h2>
			<p><?php esc_html_e( 'Make it free in five minutes. Share the link, & let the love (& the spraying) begin.', 'wwmd' ); ?></p>
			<div class="btn-row">
				<a class="btn btn--marigold btn--lg" href="<?php echo esc_url( is_user_logged_in() ? wd_account_url( 'new' ) : wd_url( 'sign-in', array( 'tab' => 'join' ) ) ); ?>"><?php esc_html_e( 'Create a free page', 'wwmd' ); ?></a>
				<a class="btn btn--ghost-light btn--lg" href="<?php echo esc_url( wd_url( 'pricing' ) ); ?>"><?php esc_html_e( 'See Premium', 'wwmd' ); ?></a>
			</div>
		</div>
		<div class="cta-band__art" aria-hidden="true"><?php echo wd_art( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo wd_art( 'notes', 'cta-band__notes' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
	</div>
</section>
<?php
get_footer();
