<?php
/**
 * Sample content: 4 free & 4 Premium weddings with guests, vendors, payments, reviews & chart history,
 * so the site looks alive from day one. Dates are set relative to the day the theme is switched on.
 * Everything made here carries _wd_sample & is removed in one click from HQ → Overview.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add one of the theme's photos to the media library (once).
 *
 * @param string $name File name without .jpg (assets/img/seed or assets/img/photos).
 * @return int Attachment ID.
 */
function wd_seed_img( $name ) {
	static $made = array();
	if ( isset( $made[ $name ] ) ) {
		return $made[ $name ];
	}
	$path = WD_DIR . '/assets/img/seed/' . $name . '.jpg';
	if ( ! file_exists( $path ) ) {
		$path = WD_DIR . '/assets/img/photos/' . $name . '.jpg';
	}
	if ( ! file_exists( $path ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$up = wp_upload_bits( 'wwm-' . $name . '.jpg', null, file_get_contents( $path ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( ! empty( $up['error'] ) ) {
		return 0;
	}
	$id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/jpeg',
			'post_title'     => ucfirst( str_replace( '-', ' ', $name ) ),
			'post_status'    => 'inherit',
		),
		$up['file']
	);
	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $up['file'] ) );
	update_post_meta( $id, '_wd_sample', 1 );
	$made[ $name ] = (int) $id;
	return $made[ $name ];
}

/**
 * Sample vendors: name => [type, city, country, about, price, photo, verified].
 *
 * @return array
 */
function wd_seed_vendors() {
	return array(
		'Ìmọ́lẹ̀ Frames'          => array( 'Photographers', 'Lagos', 'Nigeria', 'Wedding photographers who chase light, laughter & the moment the aunties start dancing. We travel across Africa & the UK.', 'From ₦850,000', 'photographer', 1 ),
		'Idunnu Kitchen'         => array( 'Caterers', 'Lagos', 'Nigeria', 'Party jollof, ofada, asun & a small chops tray that disappears in five minutes. Owambe catering for 100 to 1,500 guests.', 'From ₦7,500 a guest', 'caterer', 0 ),
		'Adùn Bakehouse'         => array( 'Bakers', 'Lagos', 'Nigeria', 'Wedding cakes, dessert tables & cupcakes baked fresh in Surulere. Tastings every Saturday.', 'Cakes from ₦350,000', 'baker', 1 ),
		'Ẹwà Glam Studio'        => array( 'Makeup artists', 'Lagos', 'Nigeria', 'Soft glam that lasts from the church to the after-party. Bridal makeup, gele & bridesmaids’ looks.', 'Bridal from ₦250,000', 'makeup', 1 ),
		'Àríyá Sounds'           => array( 'DJs & live bands', 'Lagos', 'Nigeria', 'DJ, talking drum & live band in one booking. Afrobeats, fuji, highlife & the old school your parents love.', 'From ₦400,000', 'dj', 0 ),
		'The Lagoon Hall'        => array( 'Venues', 'Lagos', 'Nigeria', 'A lagoon-view hall on Victoria Island for 300 to 800 guests, with parking for 150 cars & a bridal suite.', 'Ask for a quote', 'venue-host', 0 ),
		'Ọ̀ṣọ́ Decor & Rentals'    => array( 'Decorators', 'Lagos', 'Nigeria', 'Stage decor, centrepieces, chairs, tables & lighting. Bold colour, no stress.', 'From ₦1,200,000', 'rentals', 0 ),
		'Ụ̀tọ́ Events'             => array( 'Planners', 'Enugu', 'Nigeria', 'Igba nkwu & white wedding planners in Enugu, Awka & Owerri. We handle the list, the vendors & the in-laws.', 'From ₦1,500,000', '', 1 ),
		'Coal City Films'        => array( 'Videographers', 'Enugu', 'Nigeria', 'Wedding films, same-day edits & drone shots across the South East.', 'From ₦700,000', '', 0 ),
		'Zuma Gele & Henna'      => array( 'Gele & henna', 'Abuja', 'Nigeria', 'Gele, henna (lalle) & bridal styling for Nikkah, Kamu & Walima. Home service across Abuja & Kano.', 'From ₦120,000', '', 0 ),
		'Pwani Beach Events'     => array( 'Planners', 'Diani', 'Kenya', 'Beach weddings on the Kenyan coast: permits, florals, dhow rides & a sunset you will never forget.', 'From KES 450,000', '', 1 ),
		'Mapishi Catering'       => array( 'Caterers', 'Nairobi', 'Kenya', 'Nyama choma, pilau, biryani & Swahili coast favourites for weddings of every size.', 'From KES 2,500 a guest', '', 0 ),
		'Safari Lens Kenya'      => array( 'Photographers', 'Nairobi', 'Kenya', 'Photos & films for ruracio, church & beach weddings across Kenya.', 'From KES 180,000', '', 0 ),
		'Akwaaba Kente House'    => array( 'Fashion & fabrics', 'Accra', 'Ghana', 'Hand-woven kente for engagements & weddings, with matching outfits for the whole family.', 'From GH₵ 2,500', '', 0 ),
		'Kelewele & Co.'         => array( 'Caterers', 'Accra', 'Ghana', 'Jollof, waakye, kelewele & grills for engagements & receptions.', 'From GH₵ 180 a guest', '', 0 ),
		'Jollof & Lace Events'   => array( 'Planners', 'Houston', 'United States', 'Nigerian weddings in Texas, planned by people who understand both the traditional & the white wedding.', 'From $6,500', '', 0 ),
		'Ubuntu Sounds'          => array( 'DJs & live bands', 'Johannesburg', 'South Africa', 'Amapiano, gqom, maskandi & the classics. DJ & live band for umembeso, lobola celebrations & receptions.', 'From R18,000', '', 0 ),
		'Shisa Nyama Feasts'     => array( 'Caterers', 'Johannesburg', 'South Africa', 'Braai, pap, chakalaka & dessert tables for weddings across Gauteng.', 'From R350 a guest', '', 0 ),
		'Peckham Gele Studio'    => array( 'Gele & henna', 'London', 'United Kingdom', 'Gele tying for brides, mums & the whole bridal party across London & the South East.', 'From £150', '', 0 ),
	);
}

/**
 * The 8 sample weddings. "day" is days from the coming Saturday, "made" is days since the page was made.
 *
 * @return array
 */
function wd_seed_weddings() {
	return array(
		'tolani-kayode'     => array(
			'plan'     => 'premium',
			'culture'  => array( 'yo', 'pcm', 'ng' ),
			'made'     => 44,
			'p1'       => 'Tolani',
			'p2'       => 'Kayode',
			'day'      => 0,
			'time'     => '10:00',
			'city'     => 'Lagos',
			'country'  => 'Nigeria',
			'venue'    => 'The Lagoon Hall, Victoria Island',
			'currency' => 'NGN',
			'styles'   => array( 'traditional', 'white-wedding' ),
			'cover'    => 'couple-dance',
			'tagline'  => 'Two Lagos hearts, one big owambe. Join us as we say “I do” the traditional way & the church way.',
			'hashtag'  => 'TolaKay26',
			'hosts'    => 'The families of Mr & Mrs Adebayo Ogunleye & Chief & Mrs Olumide Balogun',
			'message'  => 'With hearts full of thanks to God, we invite you to celebrate the union of our children, Tolani & Kayode. Come hungry, come dressed to slay & come ready to dance.',
			'events'   => array(
				array( 'Traditional engagement', -2, '12:00', 'Eko Pearl Gardens, Lekki', 'Admiralty Way, Lekki Phase 1, Lagos', 'Aso-oke in coral & gold', '#E2725B', '#D4A017' ),
				array( 'Church wedding', 0, '10:00', 'Glory Chapel, Ikoyi', 'Kingsway Road, Ikoyi, Lagos', 'Formal: champagne & emerald', '#F3E5C8', '#0F6B4F' ),
				array( 'Reception', 0, '14:00', 'The Lagoon Hall, Victoria Island', 'Ozumba Mbadiwe Avenue, Victoria Island, Lagos', 'Owambe ready: emerald & gold', '#0F6B4F', '#D4A017' ),
			),
			'story'    => array(
				array( '2018', 'A borrowed charger', 'Final year at UNILAG. Tolani’s phone died in the library & Kayode had the only charger on the table. He made her promise to bring it back. She did, with puff-puff.', '' ),
				array( '2020', 'The lockdown calls', 'Lockdown turned short chats into four-hour video calls. Kayode learnt to cook her mum’s ofada stew over FaceTime. It was not good, but he tried.', '' ),
				array( 'December 2025', 'The question', 'Kayode proposed at Tolani’s birthday dinner, in front of both families. Her sister knew for three months & said nothing. A miracle.', 'couple-dance-close' ),
				array( 'This November', 'Forever starts here', 'Two families, one owambe & a lifetime of borrowed chargers. We can’t wait to celebrate with you.', '' ),
			),
			'party'    => array(
				array( 'Funmilayo Ogunleye', 'Maid of honour', 'bride', 'Tolani’s sister & keeper of every secret. Will cry first.' ),
				array( 'Ijeoma Nwosu', 'Bridesmaid', 'bride', 'Best friend since JSS1. In charge of the dance floor.' ),
				array( 'Temitope Akinola', 'Bridesmaid', 'bride', 'Cousin, planner-in-chief & owner of the group chat.' ),
				array( 'Zainab Bello', 'Bridesmaid', 'bride', 'Flatmate from UNILAG. Knows the charger story by heart.' ),
				array( 'Segun Oladipo', 'Best man', 'groom', 'Kayode’s brother from another mother. Holds the rings & the jokes.' ),
				array( 'Emeka Okafor', 'Groomsman', 'groom', 'Football Sundays since 2015.' ),
				array( 'Tunde Balogun', 'Groomsman', 'groom', 'Kayode’s younger brother. Hype man for life.' ),
				array( 'Damilola Adeyemi', 'Groomsman', 'groom', 'Will spray the most. You heard it here first.' ),
			),
			'quiz'     => array(
				array( 'Where did Tolani & Kayode meet?', array( 'At a wedding', 'In the UNILAG library', 'On Instagram', 'At church' ), 1 ),
				array( 'What did Kayode lend Tolani on day one?', array( 'His umbrella', 'A pen', 'His phone charger', 'His notes' ), 2 ),
				array( 'What did Tolani bring back with the charger?', array( 'Puff-puff', 'Chin chin', 'Meat pie', 'Zobo' ), 0 ),
				array( 'Which dish did Kayode try to learn during lockdown?', array( 'Jollof rice', 'Ofada stew', 'Egusi soup', 'Moi moi' ), 1 ),
				array( 'Who kept the proposal secret for three months?', array( 'Her mum', 'Her best friend', 'Her sister', 'Nobody' ), 2 ),
			),
			'registry' => array(
				array( 'fund', 'Honeymoon in Zanzibar', 'Flights, a beach villa & a sunset dhow cruise.', 2500000, 'art:plane' ),
				array( 'fund', 'Our first home', 'Help us furnish our new apartment in Yaba.', 5000000, 'art:house' ),
				array( 'gift', 'Inverter & solar panels', 'Light, even when the power goes off.', 1200000, 'art:gift' ),
				array( 'gift', 'Chest freezer', 'For all the leftover jollof.', 450000, 'art:gift' ),
				array( 'gift', 'Dinner set for 12', 'Sunday lunch at ours. Everyone is invited.', 180000, 'art:cake' ),
				array( 'gift', 'Air fryer', 'Plantain in 8 minutes. Life changing.', 95000, 'art:gift' ),
			),
			'vendors'  => array(
				'Ìmọ́lẹ̀ Frames'       => 'Photography',
				'Idunnu Kitchen'      => 'Food & small chops',
				'Adùn Bakehouse'      => 'Wedding cake',
				'Ẹwà Glam Studio'     => 'Bridal makeup & gele',
				'Àríyá Sounds'        => 'DJ & live band',
				'The Lagoon Hall'     => 'Reception venue',
				'Ọ̀ṣọ́ Decor & Rentals' => 'Decor',
			),
			'ig'       => 'tolakay26',
			'design'   => array( '#9B1B4A', '#E8A317', '#FFF4EA', '#2A0E1A', 'playfair', 'jakarta', 'adire' ),
			'template' => 'royal',
			'live'     => array( 1, 'https://www.youtube.com/live/', 'Can’t make it to Lagos? Watch the church service & the first dance live.' ),
			'spray'    => array( 1, 1 ),
			'pass'     => array( 1, '2611' ),
			'cards'    => array( 30, 'printing', 'Ogunleye residence, 14 Admiralty Way, Lekki Phase 1, Lagos', '', '' ),
			'paid'     => array( 450000 ),
			'act'      => array(
				'views_base' => 24000,
				'bumps'      => array( array( 6, 900, 8 ) ),
				'floor'      => 180,
				'ig'         => array( 18400, 6900 ),
				'wall'       => array( 18, 10, 90 ),
				'photos'     => array(
					array( 'couple-dance', 'Pre-wedding shoot magic ✨', 2, 214 ),
					array( 'couple-dance-close', 'This smile though', 2, 167 ),
					array( 'dessert-table', 'Dessert table preview from Adùn Bakehouse', 4, 95 ),
					array( 'makeup-close', 'Bridesmaids’ makeup trial', 6, 73 ),
					array( 'small-chops', 'Small chops tasting with Idunnu Kitchen', 20, 61 ),
					array( 'table-setting', 'Setting up for the introduction', 21, 48 ),
				),
				'reviews'    => array( array( 'Idunnu Kitchen', 1, 19 ), array( 'Àríyá Sounds', 1, 19 ), array( 'Ìmọ́lẹ̀ Frames', 1, 18 ), array( 'Ẹwà Glam Studio', 1, 18 ) ),
				'sprays'     => array( 14, 0, 9 ),
				'gifts'      => array( 22, 0, 30 ),
				'rsvps'      => array( 186, 0.9 ),
				'quiz'       => 64,
				'chart'      => array( 2, 6, array() ),
			),
		),
		'chiamaka-obinna'   => array(
			'plan'     => 'pro',
			'culture'  => array( 'ig', 'pcm', 'ng' ),
			'made'     => 70,
			'p1'       => 'Chiamaka',
			'p2'       => 'Obinna',
			'day'      => -21,
			'time'     => '10:00',
			'city'     => 'Enugu',
			'country'  => 'Nigeria',
			'venue'    => 'Coal City Event Centre, Independence Layout',
			'currency' => 'NGN',
			'styles'   => array( 'traditional', 'white-wedding' ),
			'cover'    => 'couple-field',
			'tagline'  => 'Igba nkwu in Enugu, then the white wedding. Two families, one cup of palm wine & a whole lot of dancing.',
			'hashtag'  => 'ChiObi2026',
			'hosts'    => 'The families of Chief & Lolo Emmanuel Okeke of Nsukka & Sir & Lady Ifeanyi Eze of Awka',
			'message'  => 'Our hearts are full. Join us in Enugu as Chiamaka carries the cup of palm wine to Obinna, then as we bless their union in church the next day.',
			'events'   => array(
				array( 'Igba nkwu (traditional wedding)', -1, '12:00', 'Okeke Family Compound', 'Independence Layout, Enugu', 'George wrappers & isiagu. Emerald & gold', '#0F6B4F', '#D4A017' ),
				array( 'Church wedding', 0, '10:00', 'Holy Family Chapel, GRA', 'GRA, Enugu', 'Formal: emerald, gold & ivory', '#0F6B4F', '#F5F0E1' ),
				array( 'Reception', 0, '14:00', 'Coal City Event Centre', 'Independence Layout, Enugu', 'Owambe ready', '#D4A017', '#0F6B4F' ),
			),
			'story'    => array(
				array( '2016', 'Choir practice', 'Chiamaka sang alto. Obinna played the keyboard, badly, until she started sitting next to him to help with the chords.', '' ),
				array( '2019', 'Long distance', 'Obinna moved to Port Harcourt for work. Two years of Friday night buses & voice notes longer than sermons.', '' ),
				array( 'Easter 2025', 'He asked her papa first', 'Obinna came with palm wine & his uncles to ask for her hand. Papa made him wait one full hour. Then he said yes.', 'couple-field-bouquet' ),
				array( 'October 2026', 'Nwanyị ọma, welcome home', 'Thank you for every prayer, every bus ticket & every bowl of ofe onugbu along the way.', '' ),
			),
			'party'    => array(
				array( 'Adaeze Okeke', 'Chief bridesmaid', 'bride', 'Chiamaka’s little sister & the loudest voice in the choir.' ),
				array( 'Nkechi Obi', 'Bridesmaid', 'bride', 'Roommate at UNN. Owns more gele than anyone alive.' ),
				array( 'Chidinma Nwosu', 'Bridesmaid', 'bride', 'Cousin, makeup critic & dance captain.' ),
				array( 'Uchenna Eze', 'Best man', 'groom', 'Obinna’s elder brother. Calm, until the music starts.' ),
				array( 'Obiora Okafor', 'Groomsman', 'groom', 'Childhood friend from Awka. In charge of the drinks table.' ),
				array( 'Kelechi Onu', 'Groomsman', 'groom', 'Obinna’s keyboard partner at choir. Knew before anyone.' ),
			),
			'quiz'     => array(
				array( 'Where did Chiamaka & Obinna meet?', array( 'Choir practice', 'A wedding', 'A lecture at UNN', 'Online' ), 0 ),
				array( 'Which instrument does Obinna play?', array( 'Drums', 'Guitar', 'Keyboard', 'Saxophone' ), 2 ),
				array( 'Which city did Obinna move to for work?', array( 'Lagos', 'Port Harcourt', 'Abuja', 'Owerri' ), 1 ),
				array( 'How long did Papa make Obinna wait?', array( 'Ten minutes', 'One full hour', 'A whole day', 'He said yes at once' ), 1 ),
				array( 'Which soup does Chiamaka cook best?', array( 'Ofe onugbu', 'Egusi', 'Okra', 'Oha' ), 0 ),
			),
			'registry' => array(
				array( 'fund', 'Honeymoon in Mauritius', 'Seven days of sun, sea & no phone calls.', 2000000, 'art:plane' ),
				array( 'fund', 'Land in Enugu', 'Saving for our own plot in Trans-Ekulu.', 6000000, 'art:house' ),
				array( 'gift', 'Generator (8 kVA)', 'For the nights the light refuses to stay.', 950000, 'art:gift' ),
				array( 'gift', 'Washing machine', 'Goodbye, Saturday hand washing.', 520000, 'art:gift' ),
				array( 'gift', 'Cookware set', 'For many pots of ofe onugbu.', 160000, 'art:cake' ),
			),
			'vendors'  => array(
				'Ụ̀tọ́ Events'     => 'Wedding planner',
				'Coal City Films' => 'Wedding film',
				'Ìmọ́lẹ̀ Frames'   => 'Photography',
				'Adùn Bakehouse'  => 'Wedding cake',
				'Ẹwà Glam Studio' => 'Bridal makeup',
				'Àríyá Sounds'    => 'Live band',
			),
			'ig'       => 'chiobi2026',
			'design'   => array( '#0F6B4F', '#D4A017', '#F6F1E4', '#10261D', 'cinzel', 'lora', 'ankara' ),
			'pass'     => array( 1, '2410' ),
			'paid'     => array( 99000, 99000 ),
			'act'      => array(
				'views_base' => 0,
				'bumps'      => array( array( -15, 1000, 3 ) ),
				'floor'      => 140,
				'ig'         => array( 24800, 2100 ),
				'wall'       => array( 24, 3, 120 ),
				'photos'     => array(
					array( 'couple-field', 'Mr & Mrs Eze 💚', 15, 388 ),
					array( 'dessert-table', 'The dessert table did not survive', 15, 175 ),
					array( 'couple-field-bouquet', 'That bouquet!', 15, 142 ),
					array( 'dj-deck', 'Àríyá Sounds kept us dancing', 15, 133 ),
					array( 'bridal-bouquet', 'Ready to walk down the aisle', 15, 120 ),
					array( 'getting-ready-details', 'Shoes, bouquet & bow tie ready', 15, 96 ),
					array( 'small-chops', 'Small chops on point', 15, 84 ),
					array( 'makeup-close', 'Bridesmaids getting ready', 16, 79 ),
					array( 'table-setting', 'Igba nkwu setup', 16, 66 ),
					array( 'reception-hall', 'The hall before the guests arrived', 15, 57 ),
				),
				'reviews'    => array( array( 'Ụ̀tọ́ Events', 3, 13 ), array( 'Coal City Films', 2, 4 ), array( 'Ìmọ́lẹ̀ Frames', 3, 12 ), array( 'Adùn Bakehouse', 2, 13 ), array( 'Ẹwà Glam Studio', 2, 12 ), array( 'Àríyá Sounds', 2, 3 ) ),
				'sprays'     => array( 46, 14, 15 ),
				'gifts'      => array( 31, 13, 40 ),
				'rsvps'      => array( 240, 0.88, 'checkin' ),
				'quiz'       => 120,
				'chart'      => array( -4, 7, array() ),
			),
		),
		'amina-yusuf'       => array(
			'plan'     => 'premium',
			'culture'  => array( 'ha', 'ng' ),
			'made'     => 30,
			'p1'       => 'Amina',
			'p2'       => 'Yusuf',
			'day'      => 28,
			'time'     => '19:00',
			'city'     => 'Abuja',
			'country'  => 'Nigeria',
			'venue'    => 'The Zuma Ballroom, Maitama, Abuja',
			'currency' => 'NGN',
			'styles'   => array( 'nikkah', 'traditional' ),
			'cover'    => '',
			'tagline'  => 'Fatiha in Kano, dinner in Abuja. Come celebrate with us, insha Allah.',
			'hashtag'  => 'AmYus2026',
			'hosts'    => 'The families of Alhaji Sani Abubakar & Alhaji Musa Bello',
			'message'  => 'With thanks to Allah, we invite you to the Fatiha of our children Amina & Yusuf, & to the dinner that follows in Abuja.',
			'events'   => array(
				array( 'Kamu', -2, '16:00', 'Abubakar family house', 'Nassarawa GRA, Kano', 'Atamfa & lace in teal & gold', '#0E5E6F', '#D4A017' ),
				array( 'Fatiha', -1, '10:00', 'Juma’at Mosque, Nassarawa GRA', 'Nassarawa GRA, Kano', 'Kaftan & babban riga', '#FFFFFF', '#0E5E6F' ),
				array( 'Wedding dinner', 0, '19:00', 'The Zuma Ballroom', 'Maitama, Abuja', 'Black tie with a touch of gold', '#111111', '#D4A017' ),
			),
			'story'    => array(
				array( '2017', 'Same street, different gates', 'Our families lived on the same street in Nassarawa GRA for ten years. We said salaam every morning & nothing more.', '' ),
				array( '2021', 'The dinner where we finally talked', 'At a cousin’s dinner in Abuja, Yusuf asked Amina to save him a seat. She saved him two, just in case.', '' ),
				array( '2025', 'The families met', 'The mothers had already decided. We were the last to know.', '' ),
			),
			'party'    => array(
				array( 'Hauwa Sani', 'Amarya’s friend', 'bride', 'Amina’s cousin & the planner of every girls’ trip.' ),
				array( 'Maryam Lawal', 'Amarya’s friend', 'bride', 'Best friend from Bayero University. Henna expert.' ),
				array( 'Fatima Garba', 'Amarya’s friend', 'bride', 'Sister-in-law to be. Already in the family group chat.' ),
				array( 'Abdullahi Bello', 'Abokin ango', 'groom', 'Yusuf’s brother & official photographer of family events.' ),
				array( 'Ibrahim Umar', 'Abokin ango', 'groom', 'Best friend since primary school in Kano.' ),
				array( 'Umar Garba', 'Abokin ango', 'groom', 'Polo team captain & chief hype man.' ),
			),
			'quiz'     => array(
				array( 'Where did Amina & Yusuf grow up?', array( 'On the same street in Kano', 'At the same school in Abuja', 'In different countries', 'At the same university' ), 0 ),
				array( 'Where did they finally have a real conversation?', array( 'At the mosque', 'At a cousin’s dinner in Abuja', 'At the airport', 'On Instagram' ), 1 ),
				array( 'How many seats did Amina save?', array( 'One', 'Two', 'Three', 'None' ), 1 ),
				array( 'Who decided first that they should marry?', array( 'Amina', 'Yusuf', 'The mothers', 'The fathers' ), 2 ),
				array( 'Which event comes first?', array( 'Dinner', 'Fatiha', 'Kamu', 'Walima' ), 2 ),
			),
			'registry' => array(
				array( 'fund', 'Umrah together', 'Our first trip as husband & wife.', 3500000, 'art:plane' ),
				array( 'fund', 'Our home in Abuja', 'Help us furnish our new home.', 4000000, 'art:house' ),
				array( 'gift', 'Air conditioners (2)', 'Abuja heat is no joke.', 900000, 'art:gift' ),
				array( 'gift', 'Dinner set for 12', 'For every Sallah lunch to come.', 200000, 'art:cake' ),
				array( 'gift', 'Prayer rugs & Quran stands', 'A peaceful corner for our home.', 85000, 'art:lantern' ),
			),
			'vendors'  => array(
				'Zuma Gele & Henna'    => 'Henna & gele',
				'Ọ̀ṣọ́ Decor & Rentals' => 'Dinner decor',
				'Idunnu Kitchen'       => 'Dinner catering',
				'Ìmọ́lẹ̀ Frames'        => 'Photography',
			),
			'ig'       => 'amyus2026',
			'design'   => array( '#0E5E6F', '#D4A017', '#F7F3EC', '#0B2A30', 'cormorant', 'jakarta', 'mudcloth' ),
			'gift'     => array( 'Hauwa, Amina’s big sister', '' ),
			'template' => 'garden',
			'live'     => array( 1, 'https://www.youtube.com/live/', 'The Fatiha & the dinner will be streamed for family abroad.' ),
			'spray'    => array( 0, 0 ),
			'pass'     => array( 1, '1212' ),
			'cards'    => array( 25, '', '', '', '' ),
			'paid'     => array( 150000 ),
			'act'      => array(
				'views_base' => 30000,
				'bumps'      => array( array( 28, 320, 10 ) ),
				'floor'      => 110,
				'ig'         => array( 7300, 1400 ),
				'wall'       => array( 10, 3, 45 ),
				'photos'     => array(
					array( 'table-setting', 'Kayan lefe display ✨', 9, 74 ),
					array( 'small-chops', 'Food tasting for the dinner', 5, 41 ),
				),
				'reviews'    => array(),
				'sprays'     => array( 0, 0, 0 ),
				'gifts'      => array( 12, 0, 25 ),
				'rsvps'      => array( 95, 0.92 ),
				'quiz'       => 31,
				'chart'      => array( 1, 4, array() ),
			),
		),
		'wanjiru-brian'     => array(
			'plan'     => 'premium',
			'culture'  => array( 'ke' ),
			'made'     => 52,
			'p1'       => 'Wanjiru',
			'p2'       => 'Brian',
			'day'      => -7,
			'time'     => '16:00',
			'city'     => 'Diani',
			'country'  => 'Kenya',
			'venue'    => 'Mwezi Beach Garden, Diani',
			'currency' => 'KES',
			'styles'   => array( 'destination', 'white-wedding' ),
			'cover'    => 'couple-beach',
			'tagline'  => 'Ruracio in Kiambu, vows on Diani beach. Karibu, family!',
			'hashtag'  => 'WanjiruNaBrian',
			'hosts'    => 'The families of Mr & Mrs Kamau Mwangi & Mr & Mrs Otieno Odhiambo',
			'message'  => 'Pack light, bring sunscreen & your dancing shoes. We are getting married on the beach!',
			'events'   => array(
				array( 'Ruracio (dowry ceremony)', -42, '11:00', 'Mwangi family home', 'Kiambu, Kenya', 'Kitenge in blue & orange', '#1F6FB2', '#F28C28' ),
				array( 'Beach ceremony', 0, '16:00', 'Mwezi Beach Garden', 'Diani Beach Road, Kwale', 'Beach formal: white & sky blue', '#FFFFFF', '#87CEEB' ),
				array( 'Sunset reception', 0, '19:00', 'Mwezi Beach Garden', 'Diani Beach Road, Kwale', 'Dancing shoes. No heels on the sand!', '#1F6FB2', '#F28C28' ),
			),
			'story'    => array(
				array( '2019', 'Matatu to Westlands', 'Brian gave up his seat on a packed matatu. Wanjiru gave him her number before her stop. The tout clapped.', '' ),
				array( '2022', 'Two cities, one plan', 'Wanjiru got a job in Mombasa. Brian visited every month & fell in love with the coast too.', '' ),
				array( 'This year', 'Ruracio day', 'Brian’s family brought the goats, the tea leaves & a very long speech. Our families have not stopped laughing since.', 'couple-beach-close' ),
			),
			'party'    => array(
				array( 'Njeri Kamau', 'Maid of honour', 'bride', 'Wanjiru’s sister & our official sunscreen supplier.' ),
				array( 'Achieng Otieno', 'Bridesmaid', 'bride', 'Best friend since high school in Nairobi.' ),
				array( 'Wambui Kariuki', 'Bridesmaid', 'bride', 'Cousin, dancer & keeper of the playlist.' ),
				array( 'Kiprono Langat', 'Best man', 'groom', 'Marathon runner. Will outrun you on the dance floor.' ),
				array( 'Otieno Omondi', 'Groomsman', 'groom', 'Brian’s brother & the family comedian.' ),
			),
			'quiz'     => array(
				array( 'Where did Wanjiru & Brian meet?', array( 'On a matatu', 'At church', 'At work', 'At a wedding' ), 0 ),
				array( 'Which city did Wanjiru move to?', array( 'Kisumu', 'Mombasa', 'Nakuru', 'Eldoret' ), 1 ),
				array( 'What did Brian’s family bring to the ruracio?', array( 'Goats & tea leaves', 'Only cows', 'A car', 'Nothing' ), 0 ),
				array( 'Where was the beach ceremony?', array( 'Lamu', 'Watamu', 'Diani', 'Malindi' ), 2 ),
				array( 'Who clapped when Wanjiru gave Brian her number?', array( 'The driver', 'The tout', 'Her friend', 'Nobody' ), 1 ),
			),
			'registry' => array(
				array( 'fund', 'Honeymoon in Zanzibar', 'A week of spice tours & sunsets, just across the water.', 350000, 'art:plane' ),
				array( 'fund', 'Our home in Kilimani', 'Help us build our first home together.', 600000, 'art:house' ),
				array( 'gift', 'Fridge', 'For the nyama choma leftovers.', 85000, 'art:gift' ),
				array( 'gift', 'Coffee machine', 'Kenyan coffee, every morning.', 30000, 'art:gift' ),
				array( 'gift', 'Bedding set', 'Soft, white & ours.', 18000, 'art:gift' ),
			),
			'vendors'  => array(
				'Pwani Beach Events' => 'Wedding planner',
				'Mapishi Catering'   => 'Catering',
				'Safari Lens Kenya'  => 'Photography',
			),
			'ig'       => 'wanjirunabrian',
			'design'   => array( '#2F6E8E', '#E6D3B3', '#FBFCFC', '#1D2B33', 'marcellus', 'jost', 'none' ),
			'template' => 'pearl',
			'live'     => array( 1, 'https://www.youtube.com/live/', 'Thank you for watching from Nairobi, Kisumu, London & everywhere in between.' ),
			'spray'    => array( 1, 1 ),
			'pass'     => array( 1, '0711' ),
			'cards'    => array( 15, 'delivered', 'Mwezi Beach Garden, Diani Beach Road, Kwale, Kenya', '', 'WWM-2610-0517' ),
			'paid'     => array( 450000 ),
			'act'      => array(
				'views_base' => 16000,
				'bumps'      => array( array( -1, 1900, 2.5 ) ),
				'floor'      => 90,
				'ig'         => array( 9600, 5200 ),
				'wall'       => array( 14, 10, 80 ),
				'photos'     => array(
					array( 'couple-beach', 'Mr & Mrs on Diani beach 🌊', 1, 296 ),
					array( 'couple-beach-close', 'The kiss!', 1, 241 ),
					array( 'dj-deck', 'The DJ booth by the ocean', 1, 102 ),
					array( 'dessert-table', 'Dessert table at sunset', 1, 88 ),
					array( 'getting-ready-details', 'Getting ready details', 1, 64 ),
					array( 'small-chops', 'Swahili bites at the reception', 0, 51 ),
					array( 'table-setting', 'Reception tables', 0, 37 ),
				),
				'reviews'    => array( array( 'Pwani Beach Events', 2, 1 ), array( 'Mapishi Catering', 2, 1 ), array( 'Safari Lens Kenya', 2, 0 ) ),
				'sprays'     => array( 38, 1, 1 ),
				'gifts'      => array( 18, 0, 20 ),
				'rsvps'      => array( 140, 0.9, 'checkin' ),
				'quiz'       => 58,
				'chart'      => array( 5, 5, array() ),
			),
		),
		'efua-kojo'         => array(
			'plan'     => 'pro',
			'culture'  => array( 'gh' ),
			'made'     => 33,
			'p1'       => 'Efua',
			'p2'       => 'Kojo',
			'day'      => 7,
			'time'     => '11:00',
			'city'     => 'Accra',
			'country'  => 'Ghana',
			'venue'    => 'Akwaaba Gardens, East Legon',
			'currency' => 'GHS',
			'styles'   => array( 'traditional' ),
			'cover'    => '',
			'design'   => array( '#B07A12', '#0F6B4F', '#FFF8EC', '#22180A', 'playfair', 'jakarta', 'kente' ),
			'pass'     => array( 1, '0726' ),
			'paid'     => array( 198000 ),
			'tagline'  => 'Kente, kelewele & a lot of love. Our traditional marriage in Accra.',
			'hashtag'  => 'KoFu26',
			'hosts'    => 'The families of Mr & Mrs Kwame Mensah & Mr & Mrs Yaw Owusu',
			'message'  => 'Akwaaba! Join us as our families come together for our traditional marriage ceremony in East Legon.',
			'events'   => array(
				array( 'Knocking (kokooko)', -28, '15:00', 'Mensah family home', 'Dansoman, Accra', 'Smart casual', '', '' ),
				array( 'Traditional marriage', 0, '11:00', 'Akwaaba Gardens', 'East Legon, Accra', 'Kente in gold & green', '#D4A017', '#0F6B4F' ),
			),
			'story'    => array(
				array( '2020', 'A wrong number', 'Kojo called the wrong number looking for his barber. Efua told him the barber had retired. They talked for an hour.', '' ),
				array( '2023', 'Waakye dates', 'Every Saturday, the same waakye spot in Osu. Kojo still thinks his order is better.', '' ),
				array( '2026', 'The knocking', 'Kojo’s family knocked with schnapps, cloth & a lot of nervous smiles. Efua’s family said: come back with more drinks. They did.', '' ),
			),
			'party'    => array(
				array( 'Ama Boateng', 'Bridesmaid', 'bride', 'Efua’s cousin & our MC in training.' ),
				array( 'Akosua Asante', 'Bridesmaid', 'bride', 'Best friend from secondary school in Cape Coast.' ),
				array( 'Kwabena Appiah', 'Best man', 'groom', 'Has known Kojo since nursery school.' ),
				array( 'Yaw Mensah', 'Groomsman', 'groom', 'Efua’s brother & official kelewele taster.' ),
			),
			'quiz'     => array(
				array( 'How did Efua & Kojo meet?', array( 'A wrong number', 'At church', 'At school', 'At a wedding' ), 0 ),
				array( 'Who was Kojo trying to call?', array( 'His mum', 'His barber', 'His boss', 'A taxi' ), 1 ),
				array( 'What is their Saturday meal?', array( 'Banku', 'Waakye', 'Fufu', 'Kenkey' ), 1 ),
				array( 'What did Kojo’s family bring to the knocking?', array( 'Schnapps & cloth', 'A goat', 'Flowers', 'Nothing' ), 0 ),
			),
			'registry' => array(
				array( 'fund', 'Honeymoon in Cape Coast', 'Beach, castle tours & fresh fish.', 15000, 'art:plane' ),
				array( 'fund', 'Our first apartment', 'Rent advance for our first home together.', 40000, 'art:house' ),
				array( 'gift', 'Blender & food processor', 'Shito in bulk.', 2200, 'art:gift' ),
				array( 'gift', 'Microwave', 'For the leftover jollof. Yes, Ghana jollof.', 1800, 'art:gift' ),
			),
			'vendors'  => array(
				'Akwaaba Kente House' => 'Kente for both families',
				'Kelewele & Co.'      => 'Catering',
			),
			'ig'       => 'kofu26',
			'act'      => array(
				'views_base' => 12000,
				'bumps'      => array( array( 13, 300, 6 ), array( -1, 420, 2.5 ) ),
				'floor'      => 60,
				'ig'         => array( 3900, 1100 ),
				'wall'       => array( 9, 4, 30 ),
				'photos'     => array(
					array( 'table-setting', 'Knocking day setup', 27, 39 ),
					array( 'reception-hall', 'Venue visit at Akwaaba Gardens', 12, 22 ),
				),
				'reviews'    => array( array( 'Kelewele & Co.', 2, 26 ) ),
				'sprays'     => array( 0, 0, 0 ),
				'gifts'      => array( 9, 0, 28 ),
				'rsvps'      => array( 78, 0.9 ),
				'quiz'       => 25,
				'chart'      => array( -1, 3, array() ),
				'spotlight'  => 5,
			),
		),
		'ifeoma-daniel'     => array(
			'plan'     => 'free',
			'culture'  => array( 'ig', 'yo', 'us' ),
			'made'     => 40,
			'p1'       => 'Ifeoma',
			'p2'       => 'Daniel',
			'day'      => 35,
			'time'     => '15:00',
			'city'     => 'Houston',
			'country'  => 'United States',
			'venue'    => 'The Bayou Loft, Downtown Houston',
			'currency' => 'USD',
			'styles'   => array( 'diaspora', 'white-wedding' ),
			'cover'    => 'bride-white',
			'tagline'  => 'From Owerri & Ibadan to Houston. A Naija wedding with Texas-size love.',
			'hashtag'  => 'IfeDan2026',
			'hosts'    => 'The families of Dr & Mrs Chukwuemeka Nnaji & Mr & Mrs Adewale Adeyemi',
			'message'  => 'Y’all are invited! Join us in Houston as we bring Owerri & Ibadan together, Texas style.',
			'events'   => array(
				array( 'Traditional ceremony', -1, '13:00', 'The Magnolia Room', 'Sugar Land, Texas', 'Igbo & Yoruba attire. Wine & gold', '#722F37', '#D4A017' ),
				array( 'Wedding & reception', 0, '15:00', 'The Bayou Loft', 'Downtown Houston, Texas', 'Black tie', '#111111', '#FFFFFF' ),
			),
			'story'    => array(
				array( '2021', 'The group chat', 'A friend added Ifeoma to the wrong group chat: “Naija Houston Hoopers”. Daniel was the only one who welcomed her.', '' ),
				array( '2023', 'Thanksgiving with jollof', 'Daniel brought jollof to Ifeoma’s family Thanksgiving. Her mum asked who made it. He lied. It was his mum.', '' ),
				array( '2025', 'Yes, in Lagos', 'Daniel proposed on the Lekki-Ikoyi bridge during a Christmas trip home. Ifeoma said yes before he finished kneeling.', 'bride-blazer' ),
			),
			'party'    => array(
				array( 'Chinwe Nnaji', 'Maid of honour', 'bride', 'Ifeoma’s sister & our wedding planner without pay.' ),
				array( 'Kemi Adeyemi', 'Bridesmaid', 'bride', 'Daniel’s sister. Already Ifeoma’s favourite.' ),
				array( 'Chuka Nnaji', 'Best man', 'groom', 'Ifeoma’s brother & Daniel’s gym partner.' ),
				array( 'Tobi Adeyemi', 'Groomsman', 'groom', 'Daniel’s cousin & the best dancer in Katy.' ),
			),
			'quiz'     => array(
				array( 'How did Ifeoma & Daniel meet?', array( 'A basketball group chat', 'At church', 'At work', 'On a flight' ), 0 ),
				array( 'Who really made the Thanksgiving jollof?', array( 'Daniel', 'Daniel’s mum', 'Ifeoma', 'A caterer' ), 1 ),
				array( 'Where did Daniel propose?', array( 'The Lekki-Ikoyi bridge', 'Galveston beach', 'Times Square', 'At home' ), 0 ),
				array( 'Which two cities are coming together?', array( 'Owerri & Ibadan', 'Enugu & Lagos', 'Kano & Jos', 'Accra & Lagos' ), 0 ),
			),
			'registry' => array(
				array( 'fund', 'Honeymoon in Bali', 'Ten days of rice fields & beaches.', 4000, 'art:plane' ),
				array( 'fund', 'House down payment', 'Our first home in Katy.', 10000, 'art:house' ),
				array( 'gift', 'Stand mixer', 'For Ifeoma’s chin chin business.', 450, 'art:gift' ),
				array( 'gift', 'Dining chairs (set of 6)', 'For Sunday rice at ours.', 900, 'art:gift' ),
			),
			'vendors'  => array(
				'Jollof & Lace Events' => 'Wedding planner',
				'Ẹwà Glam Studio'      => 'Bridal makeup (flying in)',
			),
			'ig'       => 'ifedan2026',
			'act'      => array(
				'views_base' => 900,
				'bumps'      => array( array( -24, 160, 3 ), array( -2, 90, 2 ) ),
				'floor'      => 30,
				'ig'         => array( 2600, 380 ),
				'wall'       => array( 6, 2, 20 ),
				'photos'     => array(
					array( 'bride-white', 'Pre-wedding shoot 🤍', 26, 152 ),
					array( 'groom-suit', 'Daniel cleans up nicely', 25, 118 ),
					array( 'bride-blazer', 'She said yes to the blazer', 26, 97 ),
					array( 'groom-smile', 'Can’t stop smiling', 25, 86 ),
				),
				'reviews'    => array(),
				'sprays'     => array( 0, 0, 0 ),
				'gifts'      => array( 7, 8, 26 ),
				'rsvps'      => array( 44, 0.86 ),
				'quiz'       => 18,
				'chart'      => array( 0, 4, array( 1, 2 ) ),
			),
		),
		'thandi-sipho'      => array(
			'plan'     => 'free',
			'culture'  => array( 'za' ),
			'made'     => 58,
			'p1'       => 'Thandi',
			'p2'       => 'Sipho',
			'day'      => -14,
			'time'     => '11:00',
			'city'     => 'Johannesburg',
			'country'  => 'South Africa',
			'venue'    => 'The Jacaranda Room, Sandton',
			'currency' => 'ZAR',
			'styles'   => array( 'traditional', 'white-wedding' ),
			'cover'    => '',
			'tagline'  => 'Umembeso done, lobola paid & now the big day. Halala!',
			'hashtag'  => 'SiThandi2026',
			'hosts'    => 'The Dlamini & Nkosi families',
			'message'  => 'Come celebrate with us in Soweto & Sandton. Bring your dancing feet, there will be a lot of singing.',
			'events'   => array(
				array( 'Umembeso', -1, '10:00', 'Dlamini family home', 'Orlando West, Soweto', 'Traditional Zulu attire', '#B22222', '#FFFFFF' ),
				array( 'White wedding', 0, '11:00', 'The Jacaranda Room', 'Sandton, Johannesburg', 'Formal: navy & gold', '#1B2A49', '#D4A017' ),
			),
			'story'    => array(
				array( '2018', 'Wits library', 'Sipho asked Thandi to watch his laptop while he got coffee. He came back with two coffees.', '' ),
				array( '2022', 'Lobola talks', 'The uncles negotiated for six hours. Sipho waited outside with a cooler box & a lot of hope.', '' ),
				array( '2026', 'Our day', 'Thank you to everyone who sang, danced & ate with us. Ngiyabonga!', '' ),
			),
			'party'    => array(
				array( 'Lerato Mokoena', 'Maid of honour', 'bride', 'Thandi’s best friend since primary school.' ),
				array( 'Zanele Dlamini', 'Bridesmaid', 'bride', 'Thandi’s sister & our choir leader.' ),
				array( 'Bongani Khumalo', 'Best man', 'groom', 'Braai master. Do not touch his tongs.' ),
				array( 'Mandla Ndlovu', 'Groomsman', 'groom', 'Sipho’s cousin & the best dancer in Soweto (his words).' ),
			),
			'quiz'     => array(
				array( 'Where did Thandi & Sipho meet?', array( 'Wits library', 'A taxi rank', 'Church', 'A braai' ), 0 ),
				array( 'What did Sipho come back with?', array( 'A book', 'Two coffees', 'Flowers', 'Nothing' ), 1 ),
				array( 'How long did the lobola talks take?', array( 'One hour', 'Three hours', 'Six hours', 'Two days' ), 2 ),
				array( 'What did Sipho wait outside with?', array( 'A cooler box', 'A guitar', 'His mum', 'A goat' ), 0 ),
			),
			'registry' => array(
				array( 'fund', 'Honeymoon in Mauritius', 'Island time, finally.', 40000, 'art:plane' ),
				array( 'fund', 'Our first home', 'Deposit for our place in Midrand.', 80000, 'art:house' ),
				array( 'gift', 'Braai stand', 'Bongani says we need a proper one.', 4500, 'art:gift' ),
				array( 'gift', 'Cast iron potjie pot', 'Sunday potjie for the whole family.', 1200, 'art:cake' ),
			),
			'vendors'  => array(
				'Ubuntu Sounds'      => 'DJ & live band',
				'Shisa Nyama Feasts' => 'Catering',
			),
			'ig'       => 'sithandi2026',
			'act'      => array(
				'views_base' => 1000,
				'bumps'      => array( array( -14, 900, 2.5 ) ),
				'floor'      => 70,
				'ig'         => array( 5400, 620 ),
				'wall'       => array( 12, 3, 40 ),
				'photos'     => array(
					array( 'bridal-bouquet', 'The bouquet 💐', 14, 121 ),
					array( 'dj-deck', 'Ubuntu Sounds on the decks', 14, 93 ),
					array( 'getting-ready-details', 'Getting ready', 14, 77 ),
					array( 'dessert-table', 'Dessert table', 14, 64 ),
					array( 'small-chops', 'Shisa nyama feast', 14, 58 ),
					array( 'reception-hall', 'Reception', 13, 41 ),
					array( 'makeup-close', 'Bridal party glam', 5, 35 ),
				),
				'reviews'    => array( array( 'Ubuntu Sounds', 3, 6 ), array( 'Shisa Nyama Feasts', 2, 6 ) ),
				'sprays'     => array( 0, 0, 0 ),
				'gifts'      => array( 16, 10, 30 ),
				'rsvps'      => array( 120, 0.88 ),
				'quiz'       => 40,
				'chart'      => array( -3, 6, array() ),
				'payout'     => array( 8000, 'requested' ),
			),
		),
		'bisola-femi'       => array(
			'plan'     => 'free',
			'culture'  => array( 'yo', 'uk' ),
			'made'     => 3,
			'p1'       => 'Bisola',
			'p2'       => 'Femi',
			'day'      => 56,
			'time'     => '12:00',
			'city'     => 'London',
			'country'  => 'United Kingdom',
			'venue'    => 'The Pearl Suite, Canary Wharf',
			'currency' => 'NGN',
			'styles'   => array( 'diaspora', 'traditional' ),
			'cover'    => '',
			'tagline'  => 'A Yoruba wedding in London, with the whole of Ibadan watching online.',
			'hashtag'  => 'BisFem2027',
			'hosts'    => 'The families of Mr & Mrs Adeyemi & Pastor & Mrs Oladipo',
			'message'  => 'We are getting married! Save the date & keep an eye on this page for the details.',
			'events'   => array(
				array( 'Engagement (Yoruba traditional)', 0, '12:00', 'The Pearl Suite', 'Canary Wharf, London', 'Aso-oke: wine & silver', '#722F37', '#C0C0C0' ),
			),
			'story'    => array(
				array( '2022', 'Freshers’ week', 'Femi tried to sell Bisola a ticket to a party she was hosting. She gave him a discount anyway.', '' ),
				array( '2025', 'Proposal in Ibadan', 'Femi proposed at Bisola’s grandma’s house in Ibadan, with the whole family hiding in the kitchen.', '' ),
			),
			'party'    => array(
				array( 'Yewande Akinola', 'Maid of honour', 'bride', 'Bisola’s best friend & co-host of every party.' ),
				array( 'Olumide Oladipo', 'Best man', 'groom', 'Femi’s brother & our London tour guide.' ),
			),
			'quiz'     => array(
				array( 'Where did Bisola & Femi meet?', array( 'Freshers’ week', 'At church', 'On a plane', 'At a wedding' ), 0 ),
				array( 'What was Femi selling?', array( 'Shoes', 'A party ticket', 'Jollof', 'Phone cases' ), 1 ),
				array( 'Where did Femi propose?', array( 'The London Eye', 'Grandma’s house in Ibadan', 'A beach in Lagos', 'Paris' ), 1 ),
			),
			'registry' => array(
				array( 'fund', 'Honeymoon in Santorini', 'Blue domes & sunsets.', 3000000, 'art:plane' ),
				array( 'gift', 'Home starter pack', 'Pots, plates & everything a new home needs.', 250000, 'art:gift' ),
			),
			'vendors'  => array(
				'Peckham Gele Studio' => 'Gele',
				'Ìmọ́lẹ̀ Frames'       => 'Photography (travelling to London)',
			),
			'ig'       => '',
			'act'      => array(
				'views_base' => 0,
				'bumps'      => array( array( 0, 140, 2 ) ),
				'floor'      => 60,
				'ig'         => array( 1200, 260 ),
				'wall'       => array( 5, 5, 15 ),
				'photos'     => array(),
				'reviews'    => array(),
				'sprays'     => array( 0, 0, 0 ),
				'gifts'      => array( 2, 0, 2 ),
				'rsvps'      => array( 22, 0.95 ),
				'quiz'       => 9,
				'chart'      => array( 0, 0, array() ),
			),
		),
	);
}

/**
 * Guest names by community.
 *
 * @return array
 */
function wd_seed_names() {
	return array(
		'yo'  => array( 'Adebayo Ogunleye', 'Funke Adeyemi', 'Temitope Akinola', 'Olumide Bakare', 'Bukola Oladipo', 'Segun Ajayi', 'Kemi Ogunbiyi', 'Damilola Akande', 'Yewande Adebisi', 'Tunde Alabi', 'Aunty Bisi', 'Uncle Femi', 'Moyo Adekunle', 'Tobi Ajayi', 'Lola Fashola', 'Seun Ogundipe', 'Ronke Alabi', 'Wale Oyelaran', 'Mummy Tolu', 'Dayo Akinwale' ),
		'ig'  => array( 'Chinedu Okafor', 'Ngozi Eze', 'Ifeanyi Nwosu', 'Adaeze Okeke', 'Emeka Obi', 'Nkechi Umeh', 'Uchenna Agu', 'Chidinma Okoro', 'Obiora Nnaji', 'Amaka Ibe', 'Aunty Ngozi', 'Uncle Chike', 'Ebuka Mba', 'Ozioma Eze', 'Somto Okonkwo', 'Chioma Udeh', 'Nnamdi Ani' ),
		'ha'  => array( 'Aisha Bello', 'Abdullahi Sani', 'Zainab Lawal', 'Musa Garba', 'Hauwa Ibrahim', 'Ibrahim Yusuf', 'Fatima Abubakar', 'Sani Umar', 'Maryam Danjuma', 'Umar Shehu', 'Hajiya Asabe', 'Khadija Musa', 'Bilkisu Aliyu', 'Nasir Isa', 'Halima Tanko' ),
		'ke'  => array( 'Njeri Kamau', 'Achieng Otieno', 'Wambui Kariuki', 'Kiprono Langat', 'Mumbi Njoroge', 'Omondi Ouma', 'Akinyi Odhiambo', 'Kamau Mwangi', 'Wanjiku Ndungu', 'Chebet Kirui', 'Mama Njeri', 'Brayo Kiptoo', 'Atieno Owino', 'Mwende Musyoka', 'Baraka Mutua' ),
		'gh'  => array( 'Ama Boateng', 'Kwame Mensah', 'Akosua Asante', 'Yaw Owusu', 'Abena Appiah', 'Kofi Annor', 'Esi Quaye', 'Kwabena Darko', 'Adwoa Sarpong', 'Auntie Akos', 'Nana Ama', 'Kojo Antwi', 'Efya Ofori' ),
		'za'  => array( 'Lerato Mokoena', 'Thabo Nkosi', 'Nomvula Khumalo', 'Sibusiso Ndlovu', 'Zanele Dube', 'Bongani Zulu', 'Palesa Molefe', 'Mandla Mthembu', 'Ayanda Cele', 'Lindiwe Mahlangu', 'Gogo Thoko', 'Sizwe Ngcobo', 'Naledi Sithole' ),
		'us'  => array( 'Tiffany Okoro', 'Jordan Adeyemi', 'Chioma (Dallas)', 'Ken Nwankwo', 'Tolu from Katy' ),
		'uk'  => array( 'Jide (Peckham)', 'Ope Adeleke', 'Shola Bankole', 'Tayo from Croydon' ),
		'pcm' => array(),
		'ng'  => array(),
	);
}

/**
 * Love notes by community. {a} & {b} are the couple's first names, {city} the wedding city.
 * A note starting with ^ is only used after the wedding, one starting with > only before it.
 *
 * @return array
 */
function wd_seed_notes() {
	return array(
		'en'    => array(
			'Congratulations {a} & {b}! May your home be full of laughter, good food & peace.',
			'{a}, you light up every room you walk into. {b}, take good care of our sister! Happy married life.',
			'Watching your love grow has been a joy. God bless this union.',
			'Two of my favourite people! Wishing you a lifetime of inside jokes & lazy Sunday mornings.',
			'May you never lack, may you never fight over the remote & may your love only grow. Congrats!',
			'So happy for you both. Your story is proof that good things are worth waiting for.',
			'{b}, you finally did it! Proud of you. {a}, welcome to the family.',
			'Wishing you patience, kindness & a love that keeps choosing each other every day.',
			'>Can’t be there in person, but I’ll be watching from home with a full plate. Love you two!',
			'Your quiz is too hard o! I got 3 out of 5. Congrats anyway, {a} & {b}!',
			'From the first “hello” to forever. So proud of you two.',
			'May your marriage be sweeter than the small chops & longer than the speeches!',
			'{a}, thank you for letting us be part of this. {b}, you are a lucky man.',
			'Praying for a home filled with love, laughter & good health. Congrats!',
			'Cheers to love, to family & to many more years of dancing together.',
		),
		'after' => array(
			'^Best wedding of the year, no contest. The food, the music, the love!',
			'^I’m still dancing in my head. Thank you for having us!',
			'^That entrance dance will be on my phone forever. Congrats {a} & {b}!',
		),
		'pcm'   => array(
			'Una go enjoy this marriage die! God don butter una bread. Congrats {a} & {b} 🎉',
			'{b}, you don catch big fish o! Happy married life to una.',
			'As una don marry so, make una no forget us for the dance floor. We dey come with full force!',
			'{a} my person! Your husband fine o. Congrats sis ❤️',
			'This one na love wey no get expiry date. Congrats!',
		),
		'ng'    => array(
			'>The aso-ebi was worth every naira. Can’t wait for the owambe!',
			'>I’m bringing my dancing shoes & a fresh bundle of naira for spraying. See you there!',
		),
		'yo'    => array(
			'Ẹ kú oríire o! Ilé yín á tòrò. Congratulations {a} & {b}!',
			'Olúwa á bùkún ilé yín. We love you both!',
			'Ayọ̀ ni o! Wishing you both endless joy, {a} & {b}.',
		),
		'ig'    => array(
			'Ndewo nu! May your home be full of joy & children. Congrats {a} & {b}!',
			'Obi m dị ụtọ! So proud of you both.',
			'^Nwanne m! Chukwu gozie unu. Igba nkwu of the year, no contest.',
		),
		'ha'    => array(
			'Allah ya sanya alheri! Barka da aure, {a} & {b}.',
			'Allah ya ba ku zaman lafiya. So happy for you both!',
			'Masha Allah! Wishing you a home full of peace & barakah.',
		),
		'ke'    => array(
			'Hongera sana {a} & {b}! Mungu awabariki katika ndoa yenu.',
			'^Tunawapenda sana! Diani will never be the same after this party.',
			'^Karibu kwa familia, {b}! {a}, you looked like a queen.',
		),
		'gh'    => array(
			'Ayekoo! Nyame nhyira mo aware no. Congrats {a} & {b}!',
			'>Akwaaba to the family, {b}! {a}, the kente is going to be fire.',
			'>Ei, {a} is getting married! Can’t wait to dance with you both.',
		),
		'za'    => array(
			'Halala {a} no {b}! Sinifisela uthando nenjabulo.',
			'^Siyabonga for having us! Best umembeso I have ever been to.',
			'^Makoti, you looked stunning! Congratulations to the Dlamini & Nkosi families.',
		),
		'us'    => array(
			'From back home to {city}, the love is loud! Congrats {a} & {b}.',
			'>Y’all better save me a seat by the dance floor! Congrats!',
		),
		'uk'    => array(
			'Watching from Ibadan with the whole family. We are so proud of you!',
			'>London is not ready for this owambe. Congrats {a} & {b}!',
		),
	);
}

/**
 * Guest reviews by vendor type: [rating, text].
 *
 * @return array
 */
function wd_seed_review_text() {
	return array(
		'Photographers'     => array( array( 5, 'Every photo tells a story. They caught moments we did not even see on the day.' ), array( 5, 'Calm, quick & so good with the aunties. The pictures came out in two weeks.' ), array( 4, 'Beautiful photos. Delivery took a bit longer than promised, but worth the wait.' ) ),
		'Videographers'     => array( array( 5, 'The same-day edit had the whole hall in tears.' ), array( 4, 'Lovely film & great drone shots.' ) ),
		'Caterers'          => array( array( 5, 'The jollof finished before the speeches. That tells you everything.' ), array( 5, 'Food was hot, on time & enough for everyone, even the people who came without an invite.' ), array( 4, 'Great small chops. The food queue was long but it moved fast.' ) ),
		'Bakers'            => array( array( 5, 'The cake was as tasty as it was beautiful. Guests were asking for seconds.' ), array( 5, 'Five tiers & not one crumb out of place. The dessert table was gone in minutes.' ) ),
		'Makeup artists'    => array( array( 5, 'The bride’s face was still perfect at the after-party. Magic!' ), array( 5, 'Soft glam, exactly as asked. Patient with all the bridesmaids too.' ), array( 4, 'Beautiful work. Arrived 20 minutes late but finished on time.' ) ),
		'DJs & live bands'  => array( array( 5, 'Nobody sat down all night. The talking drum at the entrance was everything.' ), array( 5, 'They read the room perfectly, from the old school to amapiano.' ), array( 4, 'Great music. A little loud near the high table.' ) ),
		'Venues'            => array( array( 5, 'Clean, spacious & the staff were so helpful. Parking was easy.' ), array( 4, 'Beautiful hall with a lovely view. The AC struggled a bit in the afternoon.' ) ),
		'Decorators'        => array( array( 5, 'The stage looked like a dream. Everyone took pictures there.' ), array( 5, 'They turned a plain hall into a palace overnight.' ) ),
		'Planners'          => array( array( 5, 'They handled everything, even the in-laws. The couple just showed up & enjoyed the day.' ), array( 5, 'Calm, organised & always one step ahead. We would book them again.' ), array( 4, 'Very organised. Replies on WhatsApp could be faster.' ) ),
		'Gele & henna'      => array( array( 5, 'The gele stood tall from morning till night!' ), array( 5, 'Neat, fast & so gentle with the henna.' ) ),
		'Fashion & fabrics' => array( array( 5, 'The kente was stunning & ready a week early.' ), array( 4, 'Beautiful cloth. A little pricey, but worth it.' ) ),
	);
}

/**
 * Daily visits for a sample wedding, d days from today (negative = past).
 *
 * @param array $a Activity settings.
 * @param int   $d Day offset from today.
 * @return int
 */
function wd_seed_views_on( $a, $d ) {
	$v = $a['floor'];
	foreach ( $a['bumps'] as $b ) {
		$v += $b[1] * exp( -abs( $d - $b[0] ) / $b[2] );
	}
	return (int) round( $v * ( 0.85 + 0.3 * ( ( abs( crc32( (string) $d . $a['floor'] ) ) % 100 ) / 100 ) ) );
}

/**
 * Pick from a list with weights.
 *
 * @param array $vals    Values.
 * @param array $weights Weights.
 * @return mixed
 */
function wd_seed_pick( $vals, $weights ) {
	$r = wp_rand( 1, array_sum( $weights ) );
	foreach ( $vals as $i => $v ) {
		$r -= $weights[ $i ];
		if ( $r <= 0 ) {
			return $v;
		}
	}
	return end( $vals );
}

/**
 * A UTC time some days ago (fractions allowed), never in the future.
 *
 * @param float $days Days ago.
 * @return string
 */
function wd_seed_ago( $days ) {
	return gmdate( 'Y-m-d H:i:s', (int) min( time() - 60, time() - $days * DAY_IN_SECONDS ) );
}

/**
 * Make the sample content (once).
 */
function wd_seed() {
	if ( get_option( 'wd_seeded' ) ) {
		return;
	}
	update_option( 'wd_seeded', 1 );
	if ( function_exists( 'set_time_limit' ) ) {
		set_time_limit( 300 ); // phpcs:ignore Squiz.PHP.DiscouragedFunctions
	}
	global $wpdb;
	wp_defer_term_counting( true );
	$owner = get_current_user_id() ? get_current_user_id() : 1;
	$tones = array( 'lavender', 'butter', 'sky', 'mint', 'coral' );

	foreach ( wd_styles() as $slug => $s ) {
		if ( ! term_exists( $slug, 'wd_style' ) ) {
			wp_insert_term( $s[0], 'wd_style', array( 'slug' => $slug ) );
		}
	}

	// Vendors.
	$vids = array();
	$vtyp = array();
	$i    = 0;
	foreach ( wd_seed_vendors() as $name => $v ) {
		$vid = wp_insert_post(
			array(
				'post_type'    => 'wd_vendor',
				'post_status'  => 'publish',
				'post_title'   => $name,
				'post_content' => $v[3],
				'post_author'  => $owner,
			)
		);
		if ( ! $vid || is_wp_error( $vid ) ) {
			continue;
		}
		wp_set_object_terms( $vid, $v[0], 'wd_vtype' );
		update_post_meta(
			$vid,
			'_wdv',
			array(
				'city'     => $v[1],
				'country'  => $v[2],
				'ig'       => '',
				'phone'    => '',
				'price'    => $v[4],
				'verified' => 0, // Only HQ verifies a business.
				'colour'   => $tones[ $i++ % 5 ],
			)
		);
		update_post_meta( $vid, '_wd_sample', 1 );
		if ( $v[5] ) {
			$att = wd_seed_img( $v[5] );
			if ( $att ) {
				set_post_thumbnail( $vid, $att );
			}
		}
		$vids[ $name ] = (int) $vid;
		$vtyp[ $vid ]  = $v[0];
	}

	$names  = wd_seed_names();
	$notes  = wd_seed_notes();
	$sat    = new DateTime( 'today', wp_timezone() );
	$sat->modify( 'next saturday' );
	$made   = array();
	$denoms = array(
		'NGN' => array( array( 2000, 5000, 10000, 20000, 50000, 100000, 200000 ), array( 10, 25, 25, 18, 12, 7, 3 ) ),
		'KES' => array( array( 200, 500, 1000, 2000, 5000, 10000, 20000 ), array( 10, 25, 25, 18, 12, 7, 3 ) ),
	);
	$funds  = array(
		'NGN' => array( 10000, 20000, 25000, 50000, 100000, 150000, 250000 ),
		'KES' => array( 1000, 2000, 2500, 5000, 10000, 20000, 30000 ),
		'GHS' => array( 100, 150, 200, 300, 500, 800, 1000 ),
		'USD' => array( 50, 75, 100, 150, 200, 250, 500 ),
		'ZAR' => array( 200, 300, 500, 750, 1000, 1500, 2000 ),
	);
	$spray_msgs = array( 'Spraying from London! 💸', 'Una go enjoy!', 'Dance, my sister!', 'Long life & prosperity', 'For the aso-ebi crew 🙌', 'Money no dey finish!', '', '', '' );
	$gift_msgs  = array( 'Enjoy every minute of the honeymoon!', 'A small something from us. Congrats!', 'With love from the whole family', 'Buy something nice for the house', 'God bless your home', '', '', '' );
	$rsvp_notes = array( 'Can’t wait!', 'Coming with my wife', 'Travelling in from Abuja', 'We will be there early', 'So excited for you both', '', '', '', '', '' );

	foreach ( wd_seed_weddings() as $slug => $w ) {
		$a     = $w['act'];
		$prem  = in_array( $w['plan'], array( 'premium', 'society' ), true );
		$paidp = 'free' !== $w['plan'];
		$feat  = (array) wd_opt( 'features_' . $w['plan'] );
		$date  = ( clone $sat )->modify( ( $w['day'] >= 0 ? '+' : '' ) . $w['day'] . ' days' )->format( 'Y-m-d' );
		$wago  = -1 * (int) wd_days_to( $date ); // Days since the wedding (negative before it).
		$cult  = $w['culture'];
		$pool  = array();
		foreach ( $cult as $c ) {
			$pool = array_merge( $pool, $names[ $c ] );
		}
		$first = array();
		$last  = array();
		foreach ( $pool as $n ) {
			$parts = explode( ' ', $n );
			if ( 2 === count( $parts ) && ! preg_match( '/^(Aunty|Auntie|Uncle|Mummy|Mama|Gogo|Hajiya|Nana)$/', $parts[0] ) && false === strpos( $n, '(' ) ) {
				$first[] = $parts[0];
				$last[]  = $parts[1];
			}
		}
		$guest = static function () use ( $pool, $first, $last ) {
			return wp_rand( 1, 4 ) === 1 ? $pool[ array_rand( $pool ) ] : $first[ array_rand( $first ) ] . ' ' . $last[ array_rand( $last ) ];
		};

		// The couple's account.
		$uid = wp_insert_user(
			array(
				'user_login'   => 'sample-' . $slug,
				'user_nicename' => $slug,
				'user_email'   => $slug . '@example.com',
				'user_pass'    => wp_generate_password( 24 ),
				'display_name' => $w['p1'] . ' & ' . $w['p2'],
				'first_name'   => $w['p1'],
				'role'         => 'couple',
			)
		);
		if ( is_wp_error( $uid ) ) {
			$uid = $owner;
		} else {
			update_user_meta( $uid, '_wd_sample', 1 );
		}
		$created = time() - $w['made'] * DAY_IN_SECONDS;
		$id      = wp_insert_post(
			array(
				'post_type'     => 'wd_wedding',
				'post_status'   => 'publish',
				'post_title'    => $w['p1'] . ' & ' . $w['p2'],
				'post_name'     => $slug,
				'post_author'   => $uid,
				'post_date'     => wp_date( 'Y-m-d H:i:s', $created ),
				'post_date_gmt' => gmdate( 'Y-m-d H:i:s', $created ),
			)
		);
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		$made[ $slug ] = (int) $id;
		update_post_meta( $id, '_wd_sample', 1 );
		update_post_meta( $id, '_wd_plan', $w['plan'] );

		$d             = wd_blank();
		$d['p1']       = $w['p1'];
		$d['p2']       = $w['p2'];
		$d['tagline']  = $w['tagline'];
		$d['hashtag']  = $w['hashtag'];
		$d['date']     = $date;
		$d['time']     = $w['time'];
		$d['city']     = $w['city'];
		$d['country']  = $w['country'];
		$d['venue']    = $w['venue'];
		$d['currency'] = $w['currency'];
		$d['invite']   = array(
			'hosts'   => $w['hosts'],
			'message' => $w['message'],
		);
		foreach ( $w['events'] as $e ) {
			$d['events'][] = array(
				'title'   => $e[0],
				'date'    => ( clone $sat )->modify( ( $w['day'] + $e[1] >= 0 ? '+' : '' ) . ( $w['day'] + $e[1] ) . ' days' )->format( 'Y-m-d' ),
				'time'    => $e[2],
				'venue'   => $e[3],
				'address' => $e[4],
				'dress'   => $e[5],
				'c1'      => $e[6],
				'c2'      => $e[7],
			);
		}
		foreach ( $w['story'] as $s ) {
			$d['story'][] = array(
				'when'  => $s[0],
				'title' => $s[1],
				'text'  => $s[2],
				'image' => $s[3] ? wd_seed_img( $s[3] ) : '',
			);
		}
		foreach ( $w['party'] as $p ) {
			$d['party'][] = wd_seed_squad_row( $p );
		}
		foreach ( $w['quiz'] as $q ) {
			$d['quiz'][] = array(
				'q' => $q[0],
				'a' => $q[1],
				'c' => $q[2],
			);
		}
		foreach ( $w['registry'] as $k => $r ) {
			$d['registry'][] = array(
				'id'    => 'r' . $k . substr( md5( $slug . $r[1] ), 0, 5 ),
				'type'  => $r[0],
				'title' => $r[1],
				'note'  => $r[2],
				'price' => $r[3],
				'url'   => '',
				'store' => '',
				'image' => $r[4],
			);
		}
		foreach ( $w['vendors'] as $vn => $role ) {
			if ( isset( $vids[ $vn ] ) ) {
				$d['vendors'][] = array(
					'id'   => $vids[ $vn ],
					'role' => $role,
				);
			}
		}
		$d['ig']['handle'] = $w['ig'];
		if ( $paidp && ! empty( $w['design'] ) ) {
			$d['design'] = array_combine( array( 'primary', 'accent', 'bg', 'ink', 'head', 'body', 'pattern' ), $w['design'] );
			if ( $prem ) {
				$d['design']['template'] = isset( $w['template'] ) ? $w['template'] : 'royal';
			}
		}
		if ( $paidp && ! empty( $w['gift'] ) ) {
			$d['gift'] = array( 'by' => $w['gift'][0], 'link' => $w['gift'][1] );
		}
		if ( in_array( 'entry_pass', $feat, true ) && ! empty( $w['pass'] ) ) {
			$d['pass'] = array(
				'on'  => $w['pass'][0],
				'pin' => $w['pass'][1],
			);
		}
		if ( $prem ) {
			$d['live']   = array(
				'on'   => $w['live'][0],
				'url'  => $w['live'][1],
				'note' => $w['live'][2],
			);
			$d['spray']  = array(
				'on'         => $w['spray'][0],
				'show_total' => $w['spray'][1],
			);
			$d['cards']  = array_combine( array( 'tables', 'status', 'address', 'phone', 'tracking' ), $w['cards'] );
		}
		$d['rsvp'] = array(
			'deadline' => $wago < -14 ? gmdate( 'Y-m-d', strtotime( $date . ' -10 days' ) ) : '',
			'max'      => 2,
		);
		if ( $w['cover'] ) {
			$d['cover'] = wd_seed_img( $w['cover'] );
			if ( $d['cover'] ) {
				set_post_thumbnail( $id, $d['cover'] );
			}
		}
		wd_put( $id, $d );
		wp_set_object_terms( $id, $w['styles'], 'wd_style' );
		update_post_meta( $id, '_wd_views_base', $a['views_base'] );
		update_post_meta( $id, '_wd_ig_eng', $a['ig'][0] );
		update_post_meta( $id, '_wd_ig_eng_week', $a['ig'][1] );
		update_post_meta( $id, '_wd_ig_source', 'hq' );
		if ( ! empty( $a['spotlight'] ) ) {
			update_post_meta( $id, '_wd_spotlight', time() + $a['spotlight'] * DAY_IN_SECONDS );
		}

		// Instagram tiles (sample pages never link out).
		if ( $w['ig'] ) {
			$tiles = array();
			$used  = array();
			foreach ( $a['photos'] as $ph ) {
				$tiles[] = array( $ph[0], $ph[1] );
				$used[]  = $ph[0];
			}
			foreach ( array( 'reception-hall', 'dessert-table', 'small-chops', 'getting-ready-details', 'table-setting' ) as $extra ) {
				if ( count( $tiles ) >= 6 ) {
					break;
				}
				if ( ! in_array( $extra, $used, true ) ) {
					$tiles[] = array( $extra, __( 'Behind the scenes ✨', 'wwmd' ) );
				}
			}
			$media = array();
			foreach ( array_slice( $tiles, 0, 6 ) as $k => $t ) {
				$media[] = array(
					'url' => '',
					'img' => wd_seed_img( $t[0] ),
					'cap' => $t[1],
					'eng' => (int) round( $a['ig'][0] / ( 5 + $k * 2 ) ),
				);
			}
			update_post_meta( $id, '_wd_ig_media', $media );
		}

		// Plan payments.
		if ( ! empty( $w['paid'] ) ) {
			foreach ( $w['paid'] as $k => $amt ) {
				$wpdb->insert(
					wd_t( 'tx' ),
					array(
						'wedding_id' => $id,
						'kind'       => 'premium',
						'item'       => $w['plan'],
						'name'       => $w['p1'] . ' & ' . $w['p2'],
						'email'      => $slug . '@example.com',
						'amount'     => $amt,
						'charged'    => $amt,
						'currency'   => 'NGN',
						'reference'  => 'WDS' . strtoupper( wp_generate_password( 9, false ) ),
						'status'     => 'paid',
						'test'       => 1,
						'created'    => wd_seed_ago( max( 1, $w['made'] - 1 - $k * 14 ) ),
						'paid_at'    => wd_seed_ago( max( 1, $w['made'] - 1 - $k * 14 ) ),
					)
				);
			}
			update_post_meta( $id, '_wd_premium_paid', array_sum( $w['paid'] ) );
		}

		// Page visits for the last 30 days.
		for ( $k = -29; $k <= 0; $k++ ) {
			if ( -$k > $w['made'] ) {
				continue;
			}
			$wpdb->insert(
				wd_t( 'views' ),
				array(
					'wedding_id' => $id,
					'day'        => gmdate( 'Y-m-d', time() + $k * DAY_IN_SECONDS ),
					'views'      => wd_seed_views_on( $a, $k ),
				)
			);
		}

		// Wall of love.
		$np = array_merge( $notes['en'], $notes['after'] );
		foreach ( $cult as $c ) {
			$np = array_merge( $np, $notes[ $c ] ?? array() );
		}
		$skip = $wago > 0 ? '>' : '^';
		$np   = array_values(
			array_filter(
				$np,
				static function ( $n ) use ( $skip ) {
					return $skip !== $n[0];
				}
			)
		);
		shuffle( $np );
		for ( $k = 0; $k < $a['wall'][0]; $k++ ) {
			$raw  = $np[ $k % count( $np ) ];
			$aft  = '^' === $raw[0];
			$text = strtr(
				ltrim( $raw, '^>' ),
				array(
					'{a}'    => $w['p1'],
					'{b}'    => $w['p2'],
					'{city}' => $w['city'],
				)
			);
			if ( $aft ) {
				$ago = wp_rand( 1, (int) max( 2, ( $k < $a['wall'][1] ? min( $wago, 6.9 ) : $wago ) * 24 - 1 ) ) / 24;
			} elseif ( $k < $a['wall'][1] ) {
				$ago = wp_rand( 1, 150 ) / 24;
			} elseif ( $wago > 0 ) {
				$ago = max( 7.2, $wago + wp_rand( -24, 36 ) / 24 );
			} else {
				$ago = wp_rand( 7 * 24 + 4, max( 7 * 24 + 5, $w['made'] * 24 ) ) / 24;
			}
			$wpdb->insert(
				wd_t( 'wall' ),
				array(
					'wedding_id' => $id,
					'name'       => $guest(),
					'message'    => $text,
					'likes'      => (int) floor( $a['wall'][2] * pow( wp_rand( 0, 1000 ) / 1000, 2.2 ) ),
					'status'     => 'approved',
					'created'    => wd_seed_ago( $ago ),
				)
			);
		}

		// Guest photos.
		foreach ( $a['photos'] as $ph ) {
			$att = wd_seed_img( $ph[0] );
			if ( ! $att ) {
				continue;
			}
			$wpdb->insert(
				wd_t( 'photos' ),
				array(
					'wedding_id'    => $id,
					'attachment_id' => $att,
					'name'          => $guest(),
					'caption'       => $ph[1],
					'likes'         => $ph[3],
					'status'        => 'approved',
					'created'       => wd_seed_ago( $ph[2] + wp_rand( 1, 20 ) / 24 ),
				)
			);
		}

		// No made-up vendor reviews: reviews only ever come from real guests.

		// Money: sprays & registry gifts.
		$cur = $w['currency'];
		for ( $k = 0; $k < $a['sprays'][0]; $k++ ) {
			$amt = wd_seed_pick( $denoms[ $cur ][0] ?? $denoms['NGN'][0], $denoms[ $cur ][1] ?? $denoms['NGN'][1] );
			$amt = $amt * wp_rand( 1, 3 );
			$t   = wd_seed_ago( $a['sprays'][1] + wp_rand( 0, max( 1, ( $a['sprays'][2] - $a['sprays'][1] ) * 24 ) ) / 24 + wp_rand( 1, 9 ) / 24 );
			$wpdb->insert(
				wd_t( 'tx' ),
				array(
					'wedding_id' => $id,
					'kind'       => 'spray',
					'name'       => $guest(),
					'email'      => 'guest@example.com',
					'message'    => $spray_msgs[ array_rand( $spray_msgs ) ],
					'amount'     => $amt,
					'fee'        => 0,
					'net'        => $amt,
					'charged'    => $amt,
					'currency'   => $cur,
					'reference'  => 'WDS' . strtoupper( wp_generate_password( 9, false ) ),
					'status'     => 'paid',
					'test'       => 1,
					'created'    => $t,
					'paid_at'    => $t,
				)
			);
		}
		$items = $d['registry'];
		$rate  = wd_fee_rate_for( $w['plan'], 'gift' );
		$bought = array();
		for ( $k = 0; $k < $a['gifts'][0]; $k++ ) {
			$it = $items[ array_rand( $items ) ];
			if ( 'gift' === $it['type'] ) {
				if ( isset( $bought[ $it['id'] ] ) || wp_rand( 1, 3 ) > 1 ) {
					$it = $items[0];
				} else {
					$bought[ $it['id'] ] = 1;
				}
			}
			$amt   = 'gift' === $it['type'] ? (int) $it['price'] : $funds[ $cur ][ array_rand( $funds[ $cur ] ) ];
			$fee   = (int) ceil( $amt * $rate / 100 );
			$cover = $fee && wp_rand( 1, 10 ) <= 7;
			$t     = wd_seed_ago( $a['gifts'][1] + wp_rand( 0, max( 1, ( $a['gifts'][2] - $a['gifts'][1] ) * 24 ) ) / 24 );
			$wpdb->insert(
				wd_t( 'tx' ),
				array(
					'wedding_id'  => $id,
					'kind'        => 'gift',
					'item'        => $it['id'],
					'name'        => $guest(),
					'email'       => 'guest@example.com',
					'message'     => $gift_msgs[ array_rand( $gift_msgs ) ],
					'amount'      => $amt,
					'fee'         => $fee,
					'net'         => $cover ? $amt : $amt - $fee,
					'charged'     => $cover ? $amt + $fee : $amt,
					'currency'    => $cur,
					'cover_fee'   => $cover ? 1 : 0,
					'hide_amount' => wp_rand( 1, 5 ) === 1 ? 1 : 0,
					'reference'   => 'WDS' . strtoupper( wp_generate_password( 9, false ) ),
					'status'      => 'paid',
					'test'        => 1,
					'created'     => $t,
					'paid_at'     => $t,
				)
			);
		}

		// RSVPs (with entry passes & check-ins on Premium pages).
		// Most guests come to every program; some only to a few, so the per-program counts differ.
		$titles = wp_list_pluck(
			array_filter(
				$d['events'],
				static function ( $e ) {
					return empty( $e['private'] );
				}
			),
			'title'
		);
		$pick   = static function () use ( $titles ) {
			if ( count( $titles ) < 2 || wp_rand( 1, 100 ) <= 55 ) {
				return implode( ', ', $titles );
			}
			$sub = array_filter(
				$titles,
				static function () {
					return wp_rand( 0, 1 );
				}
			);
			return implode( ', ', $sub ? $sub : array( end( $titles ) ) );
		};
		$span   = max( 2, $w['made'] - max( 0, $wago ) );
		for ( $k = 0; $k < $a['rsvps'][0]; $k++ ) {
			$yes = wp_rand( 1, 100 ) <= $a['rsvps'][1] * 100;
			$t   = max( 0, $wago ) + wp_rand( 6, $span * 24 ) / 24;
			$row = array(
				'wedding_id' => $id,
				'name'       => $guest(),
				'attending'  => $yes ? 'yes' : 'no',
				'guests'     => $yes ? ( wp_rand( 1, 10 ) <= 4 ? 2 : 1 ) : 0,
				'events'     => $yes ? $pick() : '',
				'note'       => $rsvp_notes[ array_rand( $rsvp_notes ) ],
				'side'       => array( 'bride', 'groom', 'both' )[ wp_rand( 0, 2 ) ],
				'created'    => wd_seed_ago( $t ),
			);
			if ( $yes && in_array( 'entry_pass', $feat, true ) && ! empty( $d['pass']['on'] ) ) {
				$row['pass_code'] = wd_new_pass();
				$row['table_no']  = (string) wp_rand( 1, max( 1, (int) $d['cards']['tables'] ) );
				if ( ! empty( $a['rsvps'][2] ) && wp_rand( 1, 100 ) <= 86 ) {
					$row['checked_in'] = gmdate( 'Y-m-d H:i:s', strtotime( $date . ' 14:00:00' ) + wp_rand( -7200, 10800 ) );
				}
			}
			$wpdb->insert( wd_t( 'rsvps' ), $row );
		}

		// Quiz plays.
		$total = count( $d['quiz'] );
		for ( $k = 0; $k < $a['quiz']; $k++ ) {
			$wpdb->insert(
				wd_t( 'quiz' ),
				array(
					'wedding_id' => $id,
					'name'       => $guest(),
					'score'      => min( $total, max( 1, (int) round( $total * pow( wp_rand( 300, 1000 ) / 1000, 0.6 ) ) ) ),
					'total'      => $total,
					'seconds'    => wp_rand( 18, 140 ),
					'created'    => wd_seed_ago( wp_rand( 2, max( 3, $w['made'] * 24 ) ) / 24 ),
				)
			);
		}

		if ( ! empty( $a['payout'] ) ) {
			$wpdb->insert(
				wd_t( 'payouts' ),
				array(
					'user_id'      => $uid,
					'wedding_id'   => $id,
					'amount'       => $a['payout'][0],
					'currency'     => $cur,
					'bank'         => 'Capitec',
					'account_no'   => '****4821',
					'account_name' => $w['p1'] . ' & ' . $w['p2'],
					'status'       => $a['payout'][1],
					'created'      => wd_seed_ago( 1.2 ),
				)
			);
		}
	}

	// A paid payout, so HQ & the couple dashboard show the full flow.
	if ( isset( $made['chiamaka-obinna'] ) ) {
		$wpdb->insert(
			wd_t( 'payouts' ),
			array(
				'user_id'      => (int) get_post_field( 'post_author', $made['chiamaka-obinna'] ),
				'wedding_id'   => $made['chiamaka-obinna'],
				'amount'       => 1500000,
				'currency'     => 'NGN',
				'bank'         => 'Access Bank',
				'account_no'   => '****0937',
				'account_name' => 'Chiamaka Eze',
				'status'       => 'paid',
				'note'         => __( 'Paid. Congratulations!', 'wwmd' ),
				'created'      => wd_seed_ago( 10 ),
				'paid_at'      => wd_seed_ago( 9 ),
			)
		);
	}

	// Service requests from couples.
	$reqs = array(
		array( 'tolani-kayode', 'apartments', 'talking', 'Block of 10 short-let apartments in Lekki for family flying in from London & Houston, for the wedding weekend.' ),
		array( 'amina-yusuf', 'rides', 'new', 'Four cars with drivers for the Abuja dinner, plus airport pickups for guests coming from Kano.' ),
		array( 'ifeoma-daniel', 'honeymoon', 'booked', 'Bali, 8 nights, right after the wedding. Budget about $6,000 for two.' ),
	);
	foreach ( $reqs as $r ) {
		if ( ! isset( $made[ $r[0] ] ) ) {
			continue;
		}
		$wid = $made[ $r[0] ];
		$u   = get_userdata( (int) get_post_field( 'post_author', $wid ) );
		$wpdb->insert(
			wd_t( 'requests' ),
			array(
				'user_id'    => $u ? $u->ID : 0,
				'wedding_id' => $wid,
				'service'    => $r[1],
				'name'       => $u ? $u->display_name : '',
				'phone'      => '',
				'email'      => $u ? $u->user_email : '',
				'details'    => $r[3],
				'status'     => $r[2],
				'created'    => wd_seed_ago( wp_rand( 2, 9 ) ),
			)
		);
	}

	// The two ad slots on free pages start with our own offers.
	$s        = wd_settings( true );
	$s['ads'] = array(
		array(
			'on'    => 1,
			'title' => __( 'Guests flying in for your wedding?', 'wwmd' ),
			'text'  => __( 'Short-let apartments near your venue, booked in one WhatsApp chat.', 'wwmd' ),
			'url'   => wd_url( 'services' ),
			'cta'   => __( 'See apartments', 'wwmd' ),
			'image' => '',
			'tone'  => 'butter',
		),
		array(
			'on'    => 1,
			'title' => __( 'Your colours. Your fonts. No ads.', 'wwmd' ),
			/* translators: %s price */
			'text'  => sprintf( __( 'Go Pro for %s: your squad, guest photos, the couple quiz & entry passes, with no ads.', 'wwmd' ), wd_money( (int) $s['pro_price'] ) ),
			'url'   => wd_url( 'pricing' ),
			'cta'   => __( 'Compare plans', 'wwmd' ),
			'image' => '',
			'tone'  => 'lavender',
		),
	);
	update_option( 'wd_settings', $s );
	wd_settings( true );

	wp_defer_term_counting( false );
	foreach ( $vids as $vid ) {
		wd_vendor_recount( $vid );
		$vv = wd_vendor( $vid );
		$t  = get_term_by( 'name', $vv['city'], 'wd_place' );
		if ( ! $t ) {
			$parent = term_exists( $vv['country'], 'wd_place', 0 );
			if ( ! $parent ) {
				$parent = wp_insert_term( $vv['country'], 'wd_place' );
			}
			$pid = is_array( $parent ) ? (int) $parent['term_id'] : (int) $parent;
			$t   = $pid ? wp_insert_term( $vv['city'], 'wd_place', array( 'parent' => $pid ) ) : null;
			$t   = is_array( $t ) ? get_term( (int) $t['term_id'], 'wd_place' ) : null;
		}
		if ( $t && ! is_wp_error( $t ) ) {
			wp_set_object_terms( $vid, array( (int) $t->term_id ), 'wd_place' );
		}
	}
	foreach ( $made as $id ) {
		wd_refresh( $id );
	}
	wd_seed_chart( $made );
	wd_seed_market();
	if ( function_exists( 'wd_seed_posts' ) ) {
		wd_seed_posts();
	}
	wd_talk_seed();
	update_option( 'wd_menu_talk', 1 );
}

/**
 * Seven weeks of chart history for the samples, so the chart shows real moves.
 *
 * @param array $made slug => wedding ID.
 */
function wd_seed_chart( $made ) {
	delete_transient( 'wd_ranks' );
	$cur  = array_flip( wd_chart_ids( 50 ) );
	$plan = array();
	foreach ( wd_seed_weddings() as $slug => $w ) {
		if ( isset( $made[ $slug ] ) ) {
			$plan[ $made[ $slug ] ] = $w['act']['chart']; // [offset, weeks charted before, weeks missed].
		}
	}
	$hist = array();
	$last = array();
	for ( $k = 7; $k >= 1; $k-- ) {
		$key = wd_chart_key( time() - $k * WEEK_IN_SECONDS );
		$in  = array();
		foreach ( $plan as $id => $p ) {
			if ( $k > $p[1] || in_array( $k, $p[2], true ) ) {
				continue;
			}
			$pos       = isset( $cur[ $id ] ) ? $cur[ $id ] + 1 : 9;
			$in[ $id ] = $pos + $p[0] * ( 1 + 0.6 * ( $k - 1 ) ) + $id / 100000;
		}
		asort( $in );
		$r = 1;
		foreach ( array_keys( $in ) as $id ) {
			$hist[ $id ][ $key ] = $r;
			if ( 1 === $k ) {
				$last[ $id ] = $r;
			}
			++$r;
		}
	}
	foreach ( $hist as $id => $h ) {
		update_post_meta( $id, '_wd_chart', $h );
	}
	update_option( 'wd_chart_last', $last, false );
	update_option( 'wd_chart_week', wd_chart_key(), false );
}


/**
 * A sample squad member with a made-up career, relationship status & handles.
 * Sample pages never link these handles anywhere.
 *
 * @param array $p [name, role, side, line].
 * @return array
 */
function wd_seed_squad_row( $p ) {
	$jobs = array( 'Lawyer', 'Software engineer', 'Nurse', 'Fashion designer', 'Banker', 'Doctor', 'Architect', 'Content creator', 'Accountant', 'Pharmacist', 'Product designer', 'Civil engineer' );
	$rels = array( 'single', 'married', 'dating', 'engaged', 'single', 'married', '' );
	$nets = array( 'tiktok', 'li', 'x', 'snap', 'fb' );
	$h    = abs( crc32( $p[0] ) );
	$bits = explode( ' ', strtolower( preg_replace( '/[^A-Za-z ]/', '', $p[0] ) ) );
	$hand = $bits[0] . '.' . substr( (string) end( $bits ), 0, 3 ) . ( $h % 90 + 10 );
	$row  = array(
		'name'   => $p[0],
		'role'   => $p[1],
		'side'   => $p[2],
		'line'   => $p[3],
		'career' => $jobs[ $h % count( $jobs ) ],
		'status' => $rels[ $h % count( $rels ) ],
		'image'  => '',
	);
	foreach ( array_keys( wd_socials() ) as $k ) {
		$row[ $k ] = '';
	}
	$row['ig']                           = $hand;
	$row[ $nets[ $h % count( $nets ) ] ] = $hand;
	return $row;
}
