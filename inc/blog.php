<?php
/**
 * Blog: wedding tips & how-tos. Starter posts are made once & are the owner's to edit or delete.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cover for a post: the featured image, or an illustration picked for the post.
 *
 * @param int    $id   Post ID.
 * @param string $size Image size.
 * @return string HTML.
 */
function wd_post_cover( $id, $size = 'wd-card' ) {
	if ( has_post_thumbnail( $id ) ) {
		return get_the_post_thumbnail( $id, $size, array( 'class' => 'cover-img', 'loading' => 'lazy' ) );
	}
	$art  = get_post_meta( $id, '_wd_art', true );
	$tone = get_post_meta( $id, '_wd_tone', true );
	$arts = array( 'rings', 'calabash', 'drum', 'lantern', 'gift', 'notes' );
	$art  = $art ? $art : $arts[ $id % count( $arts ) ];
	$tone = $tone ? $tone : array( 'butter', 'lavender', 'mint', 'sky', 'coral' )[ $id % 5 ];
	return wd_listing_scene(
		array(
			'sector' => 'stays',
			'art'    => $art,
			'tone'   => $tone,
		)
	);
}

/**
 * Minutes to read.
 *
 * @param int $id Post ID.
 * @return int
 */
function wd_read_mins( $id ) {
	return max( 1, (int) round( str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $id ) ) ) / 220 ) );
}

/**
 * Move WordPress's untouched "Hello world!" post & "Sample Page" to the bin (both can be restored from there).
 */
function wd_trash_hello() {
	foreach ( array( 'hello-world' => 'post', 'sample-page' => 'page' ) as $slug => $type ) {
		$p = get_page_by_path( $slug, OBJECT, $type );
		if ( $p && 'publish' === $p->post_status && $p->post_modified_gmt === $p->post_date_gmt && (int) $p->comment_count <= 1 ) {
			wp_trash_post( $p->ID );
		}
	}
}

/**
 * Starter posts, made once.
 */
function wd_seed_posts() {
	wd_seed_photo_posts();
	if ( get_option( 'wd_posts_seeded' ) ) {
		return;
	}
	update_option( 'wd_posts_seeded', 1 );
	wd_trash_hello();
	$cats = array();
	foreach ( array( 'planning' => __( 'Planning', 'wwmd' ), 'guides' => __( 'How-to guides', 'wwmd' ), 'guests' => __( 'For guests', 'wwmd' ) ) as $slug => $name ) {
		$t = term_exists( $slug, 'category' );
		if ( ! $t ) {
			$t = wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
		}
		$cats[ $slug ] = is_array( $t ) ? (int) $t['term_id'] : (int) $t;
	}
	$i = 0;
	foreach ( wd_starter_posts() as $p ) {
		$when = time() - ( 3 + $i * 5 ) * DAY_IN_SECONDS;
		$id   = wp_insert_post(
			array(
				'post_type'     => 'post',
				'post_status'   => 'publish',
				'post_title'    => $p['title'],
				'post_excerpt'  => $p['excerpt'],
				'post_content'  => $p['content'],
				'post_author'   => get_current_user_id() ? get_current_user_id() : 1,
				'post_category' => array( $cats[ $p['cat'] ] ),
				'post_date'     => wp_date( 'Y-m-d H:i:s', $when ),
				'post_date_gmt' => gmdate( 'Y-m-d H:i:s', $when ),
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_wd_art', $p['art'] );
			update_post_meta( $id, '_wd_tone', $p['tone'] );
		}
		$i++;
	}
}

/**
 * Starter posts: plain English, our features, no promises.
 *
 * @return array
 */
function wd_starter_posts() {
	$p = static function ( $html ) {
		return preg_replace( array( '/<p>/', '/<\/p>/', '/<h2>/', '/<\/h2>/', '/<ul>/', '/<\/ul>/' ), array( "<!-- wp:paragraph -->\n<p>", "</p>\n<!-- /wp:paragraph -->\n\n", "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">", "</h2>\n<!-- /wp:heading -->\n\n", "<!-- wp:list -->\n<ul class=\"wp-block-list\">", "</ul>\n<!-- /wp:list -->\n\n" ), $html );
	};
	return array(
		array(
			'title'   => __( 'Traditional wedding, nikkah & white wedding: one page for every program', 'wwmd' ),
			'excerpt' => __( 'Many couples hold two or three ceremonies. Here is how to put them all on one wedding page, with the right dress code, venue & RSVP for each.', 'wwmd' ),
			'cat'     => 'planning',
			'art'     => 'rings',
			'tone'    => 'lavender',
			'content' => $p( '<p>A lot of African weddings are really two or three celebrations. The introduction, the traditional wedding, the nikkah or church service, the reception & the after-party can fall on different days, in different towns, with different guest lists.</p><p>Your wedding page keeps all of them in one place, so guests always know where to be & what to wear.</p><h2>Add each program</h2><p>In your dashboard, open <strong>Wedding page → Invitation & programs</strong> & add a program for each event. Pick the type (traditional wedding, nikkah, church, reception & more), then add the date, time, venue, dress code & colours. The page shows a countdown to whichever program is next.</p><h2>Ask guests which ones they will attend</h2><p>The RSVP form lists your programs, so guests tick the ones they are coming to. In <strong>Guests & RSVPs</strong> you see the numbers for each program, which helps with food, seats & aso-ebi.</p><h2>Keep some programs private</h2><p>Some events are for close family only. Mark a program as private & the page shows it as by invitation only: the venue stays hidden & it is left out of the public RSVP form. Share the details with your invited guests yourself.</p><ul><li>Use one hashtag for the whole celebration, so photos from every program land in one place.</li><li>Add the colours for each day, so aso-ebi buyers get it right.</li><li>Share the page link on WhatsApp once, then update it as plans change.</li></ul>' ),
		),
		array(
			'title'   => __( 'Cash & gift registry: how guests can send gifts from anywhere', 'wwmd' ),
			'excerpt' => __( 'Family abroad, friends who cannot travel & guests who hate carrying cash can all give from your wedding page. Here is how it works.', 'wwmd' ),
			'cat'     => 'guides',
			'art'     => 'gift',
			'tone'    => 'butter',
			'content' => $p( '<p>Most guests want to give something, but not everyone can be in the hall with cash in hand. A cash & gift registry on your wedding page lets them give from anywhere, in a few taps.</p><h2>Set up your registry</h2><p>In your dashboard, open <strong>Wedding page → Registry</strong>. Add cash funds (the honeymoon, a new home, a first car) & gift items with a price. Guests pick one & pay by card, bank transfer or mobile money.</p><h2>Spraying from the phone</h2><p>On Premium pages, guests can spray from their phone on the day too. The table QR cards open your page, & every spray shows up live, so the joy is shared even by guests watching from abroad.</p><h2>Getting the money</h2><p>Gifts & sprays add up in <strong>Money</strong>. Ask for a payout to your bank or mobile money account whenever you like. Free pages carry a small service fee on each gift. Premium pages carry none.</p><ul><li>Add a short thank-you note to each fund, guests love it.</li><li>Share your registry link with the invitation, not only on the day.</li><li>Guests can choose to hide the amount they gave.</li></ul>' ),
		),
		array(
			'title'   => __( 'RSVPs, entry passes & table cards: how to keep your guest list under control', 'wwmd' ),
			'excerpt' => __( 'Know who is coming, stop gate-crashers & get guests to their tables without a crowd at the door.', 'wwmd' ),
			'cat'     => 'guides',
			'art'     => 'notes',
			'tone'    => 'mint',
			'content' => $p( '<p>A guest list that grows by itself is one of the biggest wedding headaches. Your wedding page helps you keep it in check, from the first RSVP to the last guest at the door.</p><h2>Collect RSVPs</h2><p>Guests RSVP on your page with their name, phone number & how many people are coming. You see every answer in <strong>Guests & RSVPs</strong>, & you can download the list at any time.</p><h2>Entry passes</h2><p>On Premium pages, each confirmed guest gets an entry pass with a QR code. Your ushers scan it at the gate, so only invited guests get in & nobody is let in twice.</p><h2>Table QR cards</h2><p>Premium comes with printed table QR cards. Guests scan them to open your page, leave love notes, upload photos & spray from their seat.</p><ul><li>Set a clear RSVP date & share a reminder a week before.</li><li>Brief your ushers on scanning passes before guests arrive.</li><li>Use private programs for family-only events.</li></ul>' ),
		),
		array(
			'title'   => __( 'Booking apartments & rides for wedding guests without the stress', 'wwmd' ),
			'excerpt' => __( 'Guests flying in need somewhere to stay & a way to get around. Here is how to sort it out with sellers on our marketplace.', 'wwmd' ),
			'cat'     => 'guests',
			'art'     => 'house',
			'tone'    => 'sky',
			'content' => $p( '<p>When family & friends fly in for the wedding, the questions start: where do we stay, who picks us up, how do we get to the venue? Our services marketplace gives you one place to find all of it.</p><h2>Find sellers we have checked</h2><p>On the <strong>Services</strong> page you will find short-let apartments, cars with drivers, buses for the bridal train & honeymoon trips, listed by sellers we have checked.</p><h2>Chat, then pay the seller</h2><p>Open a listing & chat with the seller about your dates & needs. They send you an invoice or a payment link in the chat, with every cost listed. Pay the link on the site by card, bank or mobile money, or pay the invoice the way it says. Your receipt stays in the chat.</p><h2>For hosts & car hire firms</h2><p>If you rent out apartments or cars, or run a travel business, you can apply to become a seller. Listing is free. We only take a small commission on payment links paid on the site.</p><ul><li>Book early for December & Easter, the busy wedding seasons.</li><li>Share listing links with guests in your wedding WhatsApp group.</li><li>Check the pick-up time & place with the driver a day before.</li></ul>' ),
		),
	);
}

/**
 * Bring a bundled photo into the media library once, for a blog post. Kept when sample content is removed.
 *
 * @param string $name File name in assets/img/photos, without .jpg.
 * @param string $alt  Alt text.
 * @return int Attachment ID.
 */
function wd_blog_img( $name, $alt ) {
	$have = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_wd_blog_img', 'meta_value' => $name ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	if ( $have ) {
		return (int) $have[0];
	}
	$path = WD_DIR . '/assets/img/photos/' . $name . '.jpg';
	if ( ! file_exists( $path ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$up = wp_upload_bits( 'wwm-blog-' . str_replace( 'listing-', '', $name ) . '.jpg', null, file_get_contents( $path ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( ! empty( $up['error'] ) ) {
		return 0;
	}
	$id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/jpeg',
			'post_title'     => $alt,
			'post_excerpt'   => __( 'Photo: Unsplash', 'wwmd' ),
			'post_status'    => 'inherit',
		),
		$up['file']
	);
	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $up['file'] ) );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	update_post_meta( $id, '_wd_blog_img', $name );
	return (int) $id;
}

/**
 * Photo stories, added in 1.5.1: made once, with their photos & a small credit under each one.
 * Never remade or changed after that, so the owner's edits & deletions stay.
 */
function wd_seed_photo_posts() {
	if ( get_option( 'wd_posts_151' ) ) {
		return;
	}
	update_option( 'wd_posts_151', 1 );
	$cats = array();
	foreach ( array( 'planning' => __( 'Planning', 'wwmd' ), 'guides' => __( 'How-to guides', 'wwmd' ), 'guests' => __( 'For guests', 'wwmd' ) ) as $slug => $name ) {
		$t = term_exists( $slug, 'category' );
		if ( ! $t ) {
			$t = wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
		}
		$cats[ $slug ] = is_array( $t ) ? (int) $t['term_id'] : (int) $t;
	}
	$fig = static function ( $name, $alt ) {
		$id = wd_blog_img( $name, $alt );
		if ( ! $id ) {
			return '';
		}
		$src = wp_get_attachment_image_url( $id, 'large' );
		return sprintf( "<!-- wp:image {\"id\":%1\$d,\"sizeSlug\":\"large\",\"linkDestination\":\"none\"} -->\n<figure class=\"wp-block-image size-large\"><img src=\"%2\$s\" alt=\"%3\$s\" class=\"wp-image-%1\$d\"/><figcaption class=\"wp-element-caption wd-credit\">%4\$s</figcaption></figure>\n<!-- /wp:image -->\n\n", $id, esc_url( $src ), esc_attr( $alt ), esc_html__( 'Photo: Unsplash', 'wwmd' ) );
	};
	$i = 0;
	foreach ( wd_photo_posts( $fig ) as $p ) {
		if ( get_page_by_path( $p['slug'], OBJECT, 'post' ) ) {
			continue;
		}
		$when = time() - ( 1 + $i * 2 ) * DAY_IN_SECONDS;
		$id   = wp_insert_post(
			array(
				'post_type'     => 'post',
				'post_status'   => 'publish',
				'post_name'     => $p['slug'],
				'post_title'    => $p['title'],
				'post_excerpt'  => $p['excerpt'],
				'post_content'  => $p['content'],
				'post_author'   => get_current_user_id() ? get_current_user_id() : 1,
				'post_category' => array( $cats[ $p['cat'] ] ),
				'post_date'     => wp_date( 'Y-m-d H:i:s', $when ),
				'post_date_gmt' => gmdate( 'Y-m-d H:i:s', $when ),
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			$cover = wd_blog_img( $p['cover'][0], $p['cover'][1] );
			if ( $cover ) {
				set_post_thumbnail( $id, $cover );
			}
		}
		$i++;
	}
}

/**
 * The photo stories: plain English, our features, no promises.
 *
 * @param callable $fig Makes an image block from a bundled photo name & its alt text.
 * @return array
 */
function wd_photo_posts( $fig ) {
	$p = static function ( $html ) {
		return preg_replace( array( '/<p>/', '/<\/p>/', '/<h2>/', '/<\/h2>/', '/<ul>/', '/<\/ul>/' ), array( "<!-- wp:paragraph -->\n<p>", "</p>\n<!-- /wp:paragraph -->\n\n", "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">", "</h2>\n<!-- /wp:heading -->\n\n", "<!-- wp:list -->\n<ul class=\"wp-block-list\">", "</ul>\n<!-- /wp:list -->\n\n" ), $html );
	};
	return array(
		array(
			'slug'    => 'aso-ebi-made-simple-fabric-colours-and-sharing-it-with-guests',
			'title'   => __( 'Aso-ebi made simple: fabric, colours & sharing it with guests', 'wwmd' ),
			'excerpt' => __( 'Pick the fabric, set the colours for each program & get it to family & friends at home & abroad without endless WhatsApp messages.', 'wwmd' ),
			'cat'     => 'guides',
			'cover'   => array( 'listing-fabric-stall', __( 'Stacks of colourful ankara fabric at a market stall', 'wwmd' ) ),
			'content' => $p( '<p>Aso-ebi is one of the best parts of an African wedding. When family & friends wear the same fabric, the photos look amazing & everyone can see who came for the couple. It can also turn into weeks of calls, voice notes & payment reminders. A little planning keeps it fun.</p><h2>Start with your colours</h2><p>Pick two or three colours for each program. Many couples use one set for the traditional wedding & another for the reception. Write them down before you visit the market, so you are not swayed by every beautiful print on the stall.</p>' )
				. $fig( 'listing-wax-prints', __( 'Rolls of wax print fabric in bright colours', 'wwmd' ) )
				. $p( '<h2>Choose the fabric & the head ties</h2><p>Ankara is easy to sew & works for most budgets. Lace, damask & aso-oke cost more but look rich in photos. Gele & fila in a matching colour pull the whole look together, even when guests sew different styles.</p><p>Buy a small sample first & look at it in daylight. Colours on a phone screen are rarely the same in real life.</p>' )
				. $fig( 'listing-red-gele', __( 'A woman in a red gele with pearl details', 'wwmd' ) )
				. $p( '<h2>Share it from your wedding page</h2><p>Add the colours & dress code to each program on your wedding page, so guests see exactly what to wear for each day. Then share one link instead of the same message fifty times. Sellers on our <strong>Services</strong> marketplace can sell & ship aso-ebi too. Chat with them, & they send you an invoice or a payment link in the chat.</p>' )
				. $fig( 'listing-guests-magenta', __( 'Wedding guests dancing in matching magenta aso-ebi', 'wwmd' ) )
				. $p( '<ul><li>Set a clear last day for orders & payment.</li><li>Keep a simple list of who paid & who has collected.</li><li>Guests abroad need extra time for shipping, so tell them first.</li></ul>' ),
		),
		array(
			'slug'    => 'planning-a-wedding-back-home-while-you-live-abroad',
			'title'   => __( 'Planning a wedding back home while you live abroad', 'wwmd' ),
			'excerpt' => __( 'Living in London, Houston or Toronto & getting married in Lagos, Accra or Nairobi? Here is how to plan from far away & keep everyone on the same page.', 'wwmd' ),
			'cat'     => 'planning',
			'cover'   => array( 'listing-airport', __( 'A traveller waiting at an airport gate', 'wwmd' ) ),
			'content' => $p( '<p>Many couples in the diaspora still want to get married at home, close to parents, grandparents & the whole extended family. Planning from another time zone takes patience, but it is very doable.</p><h2>Pick your trips early</h2><p>Most couples manage one planning trip & then the wedding trip. Use the first trip to see venues, meet the people you will hire & taste the food. Book flights for December & Easter early, as fares climb fast in those seasons.</p>' )
				. $fig( 'listing-flights', __( 'View of an aeroplane wing above the clouds', 'wwmd' ) )
				. $p( '<h2>Let one page do the talking</h2><p>Guests will come from different countries. Your wedding page shows every program with its date, venue, dress code & countdown, & each guest RSVPs for the ones they will attend. Guests who cannot travel can still send gifts through your cash & gift registry, & on Premium pages they can spray from their phone & watch the day live.</p><h2>Sort out where guests will stay</h2><p>Family flying in will need short-let apartments & rides from the airport. On our <strong>Services</strong> marketplace you can chat with sellers who offer apartments, cars & buses, then pay the invoice or payment link they send you.</p>' )
				. $fig( 'listing-apartment', __( 'A bright, modern living room in a short-let apartment', 'wwmd' ) )
				. $p( '<ul><li>Choose one trusted person at home who can visit places for you.</li><li>Keep every quote & receipt in one folder.</li><li>Agree on a weekly call time that works for both time zones.</li></ul>' ),
		),
		array(
			'slug'    => 'honeymoon-ideas-close-to-home-zanzibar-cape-town-dubai',
			'title'   => __( 'Honeymoon ideas close to home: Zanzibar, Cape Town & Dubai', 'wwmd' ),
			'excerpt' => __( 'Beaches, mountains or city lights: three honeymoon spots that are an easy flight from many African cities.', 'wwmd' ),
			'cat'     => 'planning',
			'cover'   => array( 'listing-zanzibar', __( 'Palm trees on a white sand beach in Zanzibar', 'wwmd' ) ),
			'content' => $p( '<p>After months of planning & a few long days of celebrating, you deserve a proper rest. You do not have to fly across the world for it. These three places are popular with African couples & offer very different trips.</p><h2>Zanzibar for slow beach days</h2><p>White sand, warm sea & quiet resorts make Zanzibar perfect for doing very little. Stone Town is lovely for an evening walk, & the east coast beaches are great for sunrise.</p><h2>Cape Town for views & adventure</h2><p>Table Mountain, the winelands & long coastal drives make Cape Town a good pick for couples who like to explore. The weather changes by season, so check before you pack.</p>' )
				. $fig( 'listing-cape-town', __( 'Cape Town with Table Mountain behind the city', 'wwmd' ) )
				. $p( '<h2>Dubai for city lights & shopping</h2><p>If you want rooftop dinners, malls & a desert trip in one week, Dubai is easy to enjoy. Many African cities have direct flights.</p>' )
				. $fig( 'listing-dubai', __( 'The Dubai skyline at sunset', 'wwmd' ) )
				. $p( '<h2>Let guests chip in</h2><p>Add a honeymoon fund to your cash & gift registry. Guests love giving towards something you will remember. Travel sellers on our <strong>Services</strong> marketplace can also put together flights & hotels; chat with them for an invoice or a payment link.</p><ul><li>Check visa rules for both passports before you book.</li><li>Leave a day or two between the wedding & the flight.</li><li>Travel insurance is worth it.</li></ul>' ),
		),
		array(
			'slug'    => 'getting-the-bridal-train-and-guests-to-the-venue-on-time',
			'title'   => __( 'Getting the bridal train & guests to the venue on time', 'wwmd' ),
			'excerpt' => __( 'Cars for the couple, a bus for the bridal train & airport pick-ups for family: plan the rides so nobody is stuck in traffic.', 'wwmd' ),
			'cat'     => 'guests',
			'cover'   => array( 'listing-range-rover', __( 'A black Range Rover parked outside a house', 'wwmd' ) ),
			'content' => $p( '<p>Late cars are one of the most common wedding-day stresses, especially in big cities with heavy traffic. Plan the rides like any other part of the day & it stops being a worry.</p><h2>Know how many people need to move</h2><p>Count the couple, the parents, the bridal train & any older guests who need a ride. Your RSVP list on your wedding page helps you see who is coming to each program.</p><h2>Pick the right vehicles</h2><p>A smart car for the couple, a bus or coach for the bridal train & a few cars for family usually covers it. Ask whether the price includes a driver, fuel & waiting time.</p>' )
				. $fig( 'listing-coach', __( 'A coach bus on a sunny road', 'wwmd' ) )
				. $fig( 'listing-g-class', __( 'A silver Mercedes G-Class on a cobbled street', 'wwmd' ) )
				. $p( '<h2>Book through a chat</h2><p>On our <strong>Services</strong> marketplace you chat with car hire & bus sellers about your dates & routes. They send you an invoice or a payment link with every cost listed, & your receipt stays in the chat.</p><ul><li>Share a simple timetable with every driver.</li><li>Add a little extra time for traffic & photos.</li><li>Check the pick-up time & place with each driver the day before.</li></ul>' ),
		),
		array(
			'slug'    => 'what-to-wear-to-an-african-traditional-wedding',
			'title'   => __( 'What to wear to an African traditional wedding: a guide for guests', 'wwmd' ),
			'excerpt' => __( 'Gele, agbada, coral beads & more: a friendly guide to dressing well for a traditional wedding, wherever you are coming from.', 'wwmd' ),
			'cat'     => 'guests',
			'cover'   => array( 'listing-couple-olive', __( 'A couple in matching olive green traditional outfits', 'wwmd' ) ),
			'content' => $p( '<p>A traditional wedding is a feast of colour, & guests are part of the show. If you have never been to one, or you are going to a wedding from a culture that is new to you, here is a simple guide.</p><h2>Check the couple\'s wedding page first</h2><p>Most couples now share the colours & dress code for each program on their wedding page. If they have aso-ebi, ask how to buy it. Wearing it shows you came for them.</p><h2>For women</h2><p>Many women wear a wrapper & blouse or a well-cut gown in the day\'s colours. At Yoruba weddings a gele, a tall head tie, finishes the look. Coral beads & gold jewellery are popular at Igbo & Edo weddings.</p>' )
				. $fig( 'listing-olive-gele', __( 'A woman in an olive gele & matching outfit', 'wwmd' ) )
				. $fig( 'listing-green-gele', __( 'A close look at a green gele', 'wwmd' ) )
				. $p( '<h2>For men</h2><p>Agbada, kaftan or a simple buba & sokoto with a cap is always a safe choice. Match the colour of your cap to the couple\'s colours if you can.</p>' )
				. $fig( 'listing-fan-lady', __( 'A smiling guest in a red head tie at an outdoor wedding', 'wwmd' ) )
				. $p( '<ul><li>Comfortable shoes help, as you will dance.</li><li>Bring a hand fan for hot afternoons.</li><li>Keep some cash for spraying, or spray from your phone if the couple\'s page allows it.</li></ul>' ),
		),
	);
}
