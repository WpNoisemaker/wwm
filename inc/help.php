<?php
/**
 * Help chat: a small assistant that answers questions about the site & how to do things on it.
 *
 * With a Claude API key (HQ → Settings → AI help chat) answers come from Claude, grounded in the
 * guide below. Without a key, or if the API is down, it answers from the built-in FAQ. Calls use
 * plain HTTP (wp_remote_post) because a theme cannot ship the Composer SDK.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

/**
 * Models HQ can pick.
 *
 * @return array id => label
 */
function wd_ai_models() {
	return array(
		'claude-opus-5-5'   => __( 'Claude Opus 5.5 (best answers)', 'wwmd' ),
		'claude-sonnet-5-5' => __( 'Claude Sonnet 5.5 (fast, lower cost)', 'wwmd' ),
		'claude-haiku-5-5'  => __( 'Claude Haiku 5.5 (fastest, lowest cost)', 'wwmd' ),
	);
}

/**
 * Pages the assistant can point to: key => [label, url].
 *
 * @return array
 */
function wd_help_links() {
	$in = is_user_logged_in();
	return array(
		'start'    => array( __( 'Create a free wedding page', 'wwmd' ), $in ? wd_account_url( 'new' ) : wd_url( 'sign-in', array( 'tab' => 'join' ) ) ),
		'pricing'  => array( __( 'Pricing', 'wwmd' ), wd_url( 'pricing' ) ),
		'page'     => array( __( 'Edit my wedding page', 'wwmd' ), wd_account_url( 'page' ) ),
		'guests'   => array( __( 'Guests & RSVPs', 'wwmd' ), wd_account_url( 'guests' ) ),
		'money'    => array( __( 'Money & payouts', 'wwmd' ), wd_account_url( 'money' ) ),
		'cards'    => array( __( 'Table QR cards', 'wwmd' ), wd_account_url( 'cards' ) ),
		'plan'     => array( __( 'See the plans', 'wwmd' ), wd_account_url( 'plan' ) ),
		'services' => array( __( 'Services marketplace', 'wwmd' ), wd_url( 'services' ) ),
		'bookings' => array( __( 'My bookings', 'wwmd' ), wd_account_url( 'services' ) ),
		'partner'  => array( __( 'Become a seller', 'wwmd' ), $in ? wd_account_url( 'partner' ) : wd_url( 'sign-in', array( 'tab' => 'join', 'redirect_to' => wd_account_url( 'partner' ) ) ) ),
		'rankings' => array( __( 'Rankings', 'wwmd' ), wd_url( 'rankings' ) ),
		'explore'  => array( __( 'Explore weddings', 'wwmd' ), wd_url( 'explore' ) ),
		'vendors'  => array( __( 'Vendors', 'wwmd' ), wd_url( 'vendors' ) ),
		'blog'     => array( __( 'Blog', 'wwmd' ), wd_url( 'blog' ) ),
		'whatsapp' => array( __( 'Chat with us on WhatsApp', 'wwmd' ), wd_whatsapp( __( 'Hello! I have a question about Wedding Websites & More.', 'wwmd' ) ) ),
	);
}

/**
 * Built-in answers: [keywords, answer, link keys]. Used without an API key & as a backup.
 *
 * @return array
 */
function wd_help_faq() {
	$m   = 'wd_money';
	$fee = static function ( $k ) {
		return rtrim( rtrim( number_format( (float) wd_opt( $k ), 1 ), '0' ), '.' ) . '%';
	};
	return array(
		array( array( 'create', 'start', 'new page', 'make a page', 'sign up', 'register', 'open a page', 'get started' ), __( 'Create a free account, then pick “New wedding page”. Add your names, date & city, & your page is live straight away. You can add programs, photos, your story & a registry later from My account → Wedding page.', 'wwmd' ), array( 'start' ) ),
		array( array( 'premium', 'pro', 'upgrade', 'price', 'cost', 'how much', 'instalment', 'pay for', 'plan', 'high society' ), sprintf( /* translators: 1: Pro price 2: Premium price 3: instalments 4: cards */ __( 'There are four plans. Free has the essentials. Pro is %1$s & adds the squad line-up, guest photos, quiz, Instagram, entry passes, your own colours & fonts, a verified badge & no ads. Premium is %2$s & adds a Premium design, livestream, spraying, %4$d printed table QR cards & no transaction charges. Both are paid once per wedding, in up to %3$d instalments, & moving up only costs the difference. High Society is quoted on WhatsApp & covers branding for the whole event, prints & livestream coverage.', 'wwmd' ), $m( wd_opt( 'pro_price' ) ), $m( wd_opt( 'premium_price' ) ), (int) wd_opt( 'instalments' ), (int) wd_opt( 'premium_qr_cards' ) ), array( 'pricing', 'plan' ) ),
		array( array( 'template', 'design', 'colour', 'color', 'font', 'custom', 'bespoke', 'look' ), sprintf( /* translators: %s price */ __( 'Pro pages pick their own colours & fonts. Premium pages also pick one of 10 Premium designs (Royal, Pearl, Garden, Noir, Coral, Indigo, Aso-oke, Keepsake, Savannah or Owambe) & their own layout in Wedding page → Template, colours & fonts. Want something made just for you? Ask for a custom design for %s, or go High Society.', 'wwmd' ), $m( wd_opt( 'custom_design_price' ) ) ), array( 'page', 'plan' ) ),
		array( array( 'traditional', 'nikkah', 'church', 'program', 'introduction', 'engagement', 'court', 'reception', 'events', 'henna', 'kamu' ), __( 'Add each ceremony as its own program in Wedding page → Invitation & programs: introduction, traditional wedding, nikkah, church, court, reception & more. Each one has its own date, venue, dress code & colours, guests RSVP to the ones they will attend, & you can keep family-only programs private.', 'wwmd' ), array( 'page', 'guests' ) ),
		array( array( 'rsvp', 'guest list', 'guests', 'invite', 'attend' ), __( 'Guests RSVP on your page & tick the programs they are coming to. See everyone in My account → Guests & RSVPs, with numbers for each program, & download the list whenever you like.', 'wwmd' ), array( 'guests' ) ),
		array( array( 'gift', 'registry', 'cash', 'fund', 'present' ), sprintf( /* translators: %s fee */ __( 'Add cash funds & gift items in Wedding page → Registry. Guests pay by card, bank or mobile money from anywhere. Free & Pro pages carry a %s service fee on gifts; Premium & High Society pages carry none.', 'wwmd' ), $fee( 'fee_gift' ) ), array( 'page', 'money' ) ),
		array( array( 'spray', 'spraying' ), __( 'Spraying is a Premium feature. Guests spray from their phone during the party, & every spray shows up live on your page.', 'wwmd' ), array( 'plan' ) ),
		array( array( 'payout', 'withdraw', 'bank', 'get my money', 'receive' ), sprintf( /* translators: %s minimum */ __( 'Go to My account → Money, enter your bank or mobile money details & ask for a payout. The minimum is %s & we check & pay within one working day.', 'wwmd' ), $m( wd_opt( 'payout_min' ) ) ), array( 'money' ) ),
		array( array( 'qr', 'table card', 'cards', 'print' ), sprintf( /* translators: 1: cards 2: price 3: qty */ __( 'Premium includes %1$d printed table QR cards. Guests scan them to open your page, spray, upload photos & leave love notes. Need more? Extra cards cost %2$s for %3$d.', 'wwmd' ), (int) wd_opt( 'premium_qr_cards' ), $m( wd_opt( 'extra_qr_price' ) ), (int) wd_opt( 'extra_qr_qty' ) ), array( 'cards' ) ),
		array( array( 'pass', 'gate', 'usher', 'check in', 'check-in', 'crash' ), __( 'On Pro & Premium, each confirmed guest gets an entry pass with a QR code. Set an usher PIN in Wedding page → Live, spray & passes, & your ushers scan passes at the gate to check guests in.', 'wwmd' ), array( 'page', 'guests' ) ),
		array( array( 'livestream', 'live stream', 'stream', 'watch online', 'youtube' ), __( 'Premium pages can show a livestream so family abroad can watch, comment & spray live. Add your stream link in Wedding page → Live, spray & passes, or ask us for a livestream crew.', 'wwmd' ), array( 'page', 'services' ) ),
		array( array( 'section', 'hide', 'turn off', 'remove', 'switch off' ), __( 'Open Wedding page → Sections on/off & switch off anything you do not want on your page, like the quiz or the squad. This works on every plan.', 'wwmd' ), array( 'page' ) ),
		array( array( 'book', 'apartment', 'short-let', 'shortlet', 'car', 'ride', 'bus', 'honeymoon', 'asoebi', 'aso ebi', 'fabric', 'lace', 'gele', 'invoice', 'payment link', 'refund', 'cancel' ), __( 'Find stays, cars, honeymoon trips & asoebi on the Services page. Open a listing & chat with the seller about your date & needs. They send you an invoice or a payment link in the chat. Pay the link on the site, or pay the invoice the way it says. Your receipt stays in the chat & in My account → Orders. Changes or refunds are agreed with the seller.', 'wwmd' ), array( 'services', 'bookings' ) ),
		array( array( 'partner', 'seller', 'list my', 'host', 'car hire', 'travel agent', 'sell', 'vendor account' ), sprintf( /* translators: %s commission */ __( 'Hosts, car hire firms, travel agents & asoebi sellers can apply to become sellers. We check every application by hand. Listing is free. Customers chat with you & you send them an invoice (they pay you directly, no commission) or a payment link (paid on the site; we take %s & your share goes to you straight away). Apply from My account → Seller dashboard.', 'wwmd' ), $fee( 'market_fee' ) ), array( 'partner' ) ),
		array( array( 'rank', 'chart', 'buzz', 'trending', 'top', 'number one' ), __( 'The Buzz Chart ranks weddings by Buzz coins. Visits, RSVPs, love notes, likes, reviews, gifts, guest photos, quiz plays, check-ins, livestream viewers & sprays all earn coins, but a page only earns from the features on its plan, so Free & Pro pages have fewer ways to earn. We cannot promise a position, & we remove pages that fake activity.', 'wwmd' ), array( 'rankings' ) ),
		array( array( 'verified', 'badge', 'tick', 'free tag' ), __( 'Pro & Premium pages get a purple verified badge on their page & everywhere they appear in lists; High Society pages get a gold one. Free pages are tagged “Free”.', 'wwmd' ), array( 'plan' ) ),
		array( array( 'gifted', 'gift premium', 'credit', 'paid for us', 'sponsor' ), __( 'If someone paid for your Pro or Premium page, you can thank them on the page. Add their name & one social link when you create the page or later in Wedding page → Basics. Both are optional.', 'wwmd' ), array( 'page' ) ),
		array( array( 'vendor', 'review', 'photographer', 'caterer', 'baker', 'planner' ), __( 'Add the vendors who worked on your wedding in Wedding page → Vendors. Guests can then review them, & the reviews show on the vendor’s profile.', 'wwmd' ), array( 'vendors', 'page' ) ),
		array( array( 'whatsapp', 'contact', 'human', 'call', 'phone', 'email', 'talk to' ), __( 'You can reach a person on WhatsApp on +234 907 898 7201, or email more@weddingwebsites.io.', 'wwmd' ), array( 'whatsapp' ) ),
	);
}

/**
 * The guide Claude answers from.
 *
 * @return string
 */
function wd_help_guide() {
	$m    = 'wd_money';
	$pct  = static function ( $k ) {
		return rtrim( rtrim( number_format( (float) wd_opt( $k ), 1 ), '0' ), '.' ) . '%';
	};
	$free = array_intersect_key( wd_features(), array_flip( (array) wd_opt( 'features_free' ) ) );
	$pro  = wd_plan_adds( 'pro' );
	$prem = wd_plan_adds( 'premium' );
	$out  = 'ABOUT THE SITE' . "\n";
	$out .= '- ' . get_bloginfo( 'name' ) . ' is a wedding page platform & directory for Nigerian & African couples at home & abroad. Couples make a wedding page; guests RSVP, leave love notes, share photos, play the couple quiz, send gifts & review vendors. Weddings appear in Explore, by city & style, & on the weekly Buzz Chart.' . "\n";
	$out .= '- Contact: WhatsApp +234 907 898 7201, email more@weddingwebsites.io.' . "\n\n";
	$out .= 'PLANS (four)' . "\n";
	$out .= '- Free: ' . implode( ', ', $free ) . '. Free pages show a small number of ads & carry service fees: ' . $pct( 'fee_gift' ) . ' on registry gifts. Free pages are tagged "Free".' . "\n";
	$out .= '- Pro: ' . $m( wd_opt( 'pro_price' ) ) . ' per wedding. Adds: ' . implode( ', ', $pro ) . '. Pro pages get a purple verified badge.' . "\n";
	$out .= '- Premium: ' . $m( wd_opt( 'premium_price' ) ) . ' per wedding. Adds: ' . implode( ', ', $prem ) . '. Includes ' . (int) wd_opt( 'premium_qr_cards' ) . ' printed table QR cards (cards ship once Premium is paid in full). Purple verified badge.' . "\n";
	$out .= '- Pro & Premium are payable in up to ' . (int) wd_opt( 'instalments' ) . ' instalments; the plan switches on with the first one. Moving up from Pro to Premium only costs the difference.' . "\n";
	$out .= '- High Society: no fixed price, quoted on WhatsApp. Everything in Premium plus ' . implode( '; ', wd_society_perks() ) . '. Gold verified badge. Our team switches it on.' . "\n";
	$out .= '- Premium templates (10): Royal, Pearl, Garden, Noir, Coral, Indigo, Aso-oke, Keepsake, Savannah & Owambe. Couples change colours & fonts after picking one. A custom design made just for the couple costs ' . $m( wd_opt( 'custom_design_price' ) ) . ' extra (Wedding page → Template, colours & fonts → Request a custom design).' . "\n";
	$out .= '- Extra table QR cards: ' . $m( wd_opt( 'extra_qr_price' ) ) . ' for ' . (int) wd_opt( 'extra_qr_qty' ) . '. Homepage spotlight: ' . $m( wd_opt( 'spotlight_price' ) ) . ' a week (labelled Sponsored, never changes chart position).' . "\n";
	$out .= '- Someone who paid for a couple\'s Pro or Premium page can be credited on the page (name + one optional social link), set when creating the page or in Wedding page → Basics.' . "\n\n";
	$out .= 'HOW TO (dashboard = My account)' . "\n";
	$out .= '- Create a page: create a free account, then My account → New wedding page. Pick Free, Pro or Premium.' . "\n";
	$out .= '- Edit the page: My account → Wedding page. Parts: Basics, Sections on/off, Invitation & programs, Love story, Squad, Quiz, Registry, Vendors, Instagram, Template, colours & fonts (colours & fonts from Pro, Premium designs & layout on Premium), Live, spray & passes (entry passes from Pro, livestream & spraying on Premium).' . "\n";
	$out .= '- Several ceremonies (introduction, traditional wedding, nikkah, church, court, reception, after-party & more): add each as a program in Invitation & programs with its own date, time, venue, dress code & colours. A program can be private (hidden from the public page). The page counts down to the next program. Guests tick which programs they will attend when they RSVP; Guests & RSVPs shows numbers per program.' . "\n";
	$out .= '- Switch sections off: Wedding page → Sections on/off (every plan).' . "\n";
	$out .= '- Guest list: My account → Guests & RSVPs (search, per-program numbers, download as a spreadsheet, check-in on Pro & Premium).' . "\n";
	$out .= '- Entry passes (Pro & Premium): turn on in Live, spray & passes & set an usher PIN; ushers scan the guest\'s pass QR code & tap Check in.' . "\n";
	$out .= '- Money: registry gifts & sprays add up in My account → Money. Payout to a bank or mobile money account, minimum ' . $m( wd_opt( 'payout_min' ) ) . ', checked & paid within one working day.' . "\n";
	$out .= '- Guests can pay by card, bank transfer or mobile money through a secure payment provider. Do not name the provider.' . "\n";
	$out .= '- Table QR cards: My account → Table QR cards (add delivery details for the printed cards, see tracking, open printable cards to print extra ones yourself, or order more on WhatsApp).' . "\n";
	$out .= '- Upgrade: My account → Plan.' . "\n";
	$out .= '- Love notes & photos from guests show on the page straight away; the couple can hide any of them in My account → Love notes & photos.' . "\n\n";
	$out .= 'SERVICES MARKETPLACE' . "\n";
	$out .= '- The Services page lists apartments & stays, cars & rides, honeymoon & travel packages & asoebi sourcing (lace, aso-oke, ankara & gele) from sellers HQ approved. Nobody books directly: the customer chats with the seller on the site, & the seller sends an invoice or a payment link in the chat (either can hold several costs). A payment link is paid on the site by card, bank or mobile money. An invoice says how to pay the seller directly (e.g. bank transfer); the seller marks it paid. Paid ones become receipts in the chat & in My account → Orders. Chat messages can hold at most 4 numbers.' . "\n";
	$out .= '- Every seller has a public profile (about, portfolio, listings & reviews), opened from the About box on any of their listings. Only customers whose paid booking is done can review; HQ checks each review.' . "\n";
	$out .= '- We are not an escrow & do not hold money. Payments on payment links go to the seller straight away (less commission). Changes, cancellations & refunds are agreed between the customer & the seller. Never say we hold money or guarantee a service.' . "\n";
	$out .= '- Sellers: hosts, car hire firms, travel agents & asoebi sellers apply in My account → Seller dashboard. HQ checks each seller & each new listing. Listing is free; commission is ' . $pct( 'market_fee' ) . ' of each payment link paid on the site, nothing on invoices paid to the seller directly. Sellers see orders & customer phone numbers in the dashboard.' . "\n";
	$out .= '- Other services we arrange on request: livestream crew, extra table QR cards & anything else (Services page → Need something else?).' . "\n\n";
	$out .= 'RANKINGS & DIRECTORY' . "\n";
	$out .= '- The Buzz Chart ranks weddings weekly by Buzz coins earned in the last 7 days. Coins come only from real guest activity: ' . implode( ', ', wp_list_pluck( wd_coin_figures(), 'label' ) ) . '. A page only earns coins from the features on its plan, so Free & Pro pages have fewer ways to earn & rarely top the chart. No plan buys a position; faked activity gets a page removed. Never promise a ranking.' . "\n";
	$out .= '- Vendors are listed when couples add them; guests who attended review them. Vendors can get verified or advertise on free pages in their city (talk to us on WhatsApp).' . "\n";
	$out .= '- The blog has wedding tips & how-to guides.' . "\n";
	return $out;
}

/**
 * Rules for the assistant.
 *
 * @return string
 */
function wd_help_rules() {
	return 'You are the help assistant on ' . get_bloginfo( 'name' ) . ', a wedding page platform & directory for Nigerian & African couples at home & abroad. You answer questions about the site & how to do things on it, using only the guide below.' . "\n\n"
		. 'How to answer:' . "\n"
		. '- Plain, friendly, natural English. Short: 1 to 4 sentences, or a few "- " bullet lines for steps. No headings, tables or bold.' . "\n"
		. '- Write "&" instead of the word "and".' . "\n"
		. '- Use the exact menu names from the guide (e.g. My account → Wedding page → Registry). Do not write web addresses; the chat adds links itself.' . "\n"
		. '- Only use prices & facts from the guide. If the guide does not cover it, say you are not sure & suggest WhatsApp on +234 907 898 7201.' . "\n"
		. '- Never promise rankings, chart positions or results. Never name the payment provider. Never ask for card numbers, passwords or bank PINs.' . "\n"
		. '- Stay on topic: weddings on this site, guests, gifts, Premium, bookings & sellers. Politely decline anything else.' . "\n"
		. '- You cannot see or change anyone\'s account. Tell people where to click instead.' . "\n\n"
		. "GUIDE\n" . wd_help_guide();
}

/**
 * Who is asking (not cached; changes per person).
 *
 * @return string
 */
function wd_help_context() {
	if ( ! is_user_logged_in() ) {
		return 'The person is not signed in.';
	}
	$ids = wd_user_weddings( get_current_user_id() );
	$s   = 'The person is signed in.';
	if ( $ids ) {
		$s .= ' They have ' . count( $ids ) . ' wedding page(s); the first is on the ' . wd_plan_name( wd_plan( (int) $ids[0] ) ) . ' plan.';
	} else {
		$s .= ' They have no wedding page yet.';
	}
	$pp = wd_partner( get_current_user_id() );
	if ( $pp ) {
		$s .= ' They are a marketplace seller (status: ' . $pp['status'] . ').';
	}
	if ( current_user_can( 'manage_options' ) ) {
		$s .= ' They run the site (HQ): HQ has tabs Overview, Weddings, Payments & payouts, Moderation, Vendors, Marketplace, Service requests, People, Ads on free pages, Menus & Settings.';
	}
	return $s;
}

/**
 * Best built-in answer for a question.
 *
 * @param string $q Question.
 * @return array answer, keys.
 */
function wd_help_match( $q ) {
	$q    = ' ' . strtolower( $q ) . ' ';
	$best = null;
	$top  = 0;
	foreach ( wd_help_faq() as $f ) {
		$n = 0;
		foreach ( $f[0] as $k ) {
			if ( false !== strpos( $q, $k ) ) {
				$n += substr_count( $k, ' ' ) + 1;
			}
		}
		if ( $n > $top ) {
			$top  = $n;
			$best = $f;
		}
	}
	if ( ! $best ) {
		return array( __( 'I am not sure about that one. Chat with us on WhatsApp on +234 907 898 7201 & a person will help.', 'wwmd' ), array( 'whatsapp' ) );
	}
	return array( $best[1], $best[2] );
}

/**
 * Pick links for an answer from what it talks about.
 *
 * @param string $text  Question & answer.
 * @param array  $keys  Link keys to start with.
 * @return array [ [label, url] ]
 */
function wd_help_pick_links( $text, $keys = array() ) {
	$t   = strtolower( $text );
	$map = array(
		'pricing'  => array( 'premium', 'price', 'cost', 'instalment' ),
		'plan'     => array( 'upgrade', 'my account → plan' ),
		'page'     => array( 'wedding page →', 'sections on/off', 'registry', 'invitation & programs', 'template, colours' ),
		'guests'   => array( 'guests & rsvps', 'guest list' ),
		'money'    => array( 'payout', 'my account → money' ),
		'cards'    => array( 'table qr cards' ),
		'services' => array( 'services page', 'marketplace', 'apartment', 'honeymoon', 'car hire' ),
		'bookings' => array( 'bookings & services', 'report a problem', 'it went well' ),
		'partner'  => array( 'seller dashboard', 'become a seller' ),
		'rankings' => array( 'buzz chart', 'ranking' ),
		'start'    => array( 'new wedding page', 'create a free account' ),
		'whatsapp' => array( 'whatsapp' ),
	);
	foreach ( $map as $k => $words ) {
		foreach ( $words as $w ) {
			if ( false !== strpos( $t, $w ) ) {
				$keys[] = $k;
				break;
			}
		}
	}
	$links = wd_help_links();
	$out   = array();
	foreach ( array_unique( $keys ) as $k ) {
		if ( isset( $links[ $k ] ) ) {
			$out[] = $links[ $k ];
		}
		if ( count( $out ) >= 3 ) {
			break;
		}
	}
	return $out;
}

/**
 * Ask Claude.
 *
 * @param string $q       Question.
 * @param array  $history Earlier turns: [ [role, text] ].
 * @return string|WP_Error
 */
function wd_help_claude( $q, $history ) {
	$key = trim( (string) wd_opt( 'ai_key' ) );
	if ( ! $key ) {
		return new WP_Error( 'nokey', 'No API key' );
	}
	$model = (string) wd_opt( 'ai_model' );
	$model = array_key_exists( $model, wd_ai_models() ) ? $model : 'claude-opus-5-5';
	$msgs  = array();
	foreach ( $history as $h ) {
		if ( ! $msgs && 'user' !== $h[0] ) {
			continue;
		}
		if ( $msgs && end( $msgs )['role'] === $h[0] ) {
			$msgs[ count( $msgs ) - 1 ]['content'] .= "\n" . $h[1];
			continue;
		}
		$msgs[] = array(
			'role'    => $h[0],
			'content' => $h[1],
		);
	}
	if ( $msgs && 'user' === end( $msgs )['role'] ) {
		array_pop( $msgs );
	}
	$msgs[] = array(
		'role'    => 'user',
		'content' => $q,
	);
	$res = wp_remote_post(
		'https://api.anthropic.com/v1/messages',
		array(
			'timeout' => 45,
			'headers' => array(
				'x-api-key'         => $key,
				'anthropic-version' => '2026-10-21',
				'content-type'      => 'application/json',
			),
			'body'    => wp_json_encode(
				array(
					'model'         => $model,
					'max_tokens'    => 4000,
					'system'        => array(
						array(
							'type'          => 'text',
							'text'          => wd_help_rules(),
							'cache_control' => array( 'type' => 'ephemeral' ),
						),
						array(
							'type' => 'text',
							'text' => wd_help_context(),
						),
					),
					'messages'      => $msgs,
					'output_config' => array( 'effort' => 'low' ),
				)
			),
		)
	);
	if ( is_wp_error( $res ) ) {
		return $res;
	}
	$code = (int) wp_remote_retrieve_response_code( $res );
	$body = json_decode( wp_remote_retrieve_body( $res ), true );
	if ( $code < 200 || $code > 299 || ! is_array( $body ) ) {
		return new WP_Error( 'api', 'Claude API error ' . $code . ': ' . ( is_array( $body ) && isset( $body['error']['message'] ) ? $body['error']['message'] : '' ) );
	}
	if ( isset( $body['stop_reason'] ) && 'refusal' === $body['stop_reason'] ) {
		return new WP_Error( 'refusal', 'Declined' );
	}
	$text = '';
	foreach ( (array) ( $body['content'] ?? array() ) as $b ) {
		if ( isset( $b['type'], $b['text'] ) && 'text' === $b['type'] ) {
			$text .= $b['text'];
		}
	}
	$text = trim( $text );
	return $text ? $text : new WP_Error( 'empty', 'Empty answer' );
}

add_action(
	'rest_api_init',
	static function () {
		register_rest_route(
			'wd/v1',
			'/ask',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => 'wd_rest_ask',
			)
		);
	}
);

/**
 * Answer a help question.
 *
 * @param WP_REST_Request $r Request.
 * @return array|WP_Error
 */
function wd_rest_ask( WP_REST_Request $r ) {
	if ( ! wd_opt( 'ai_on' ) ) {
		return new WP_Error( 'off', __( 'Help chat is switched off.', 'wwmd' ), array( 'status' => 403 ) );
	}
	$q = trim( mb_substr( sanitize_textarea_field( (string) $r->get_param( 'question' ) ), 0, 500 ) );
	if ( mb_strlen( $q ) < 2 ) {
		return new WP_Error( 'empty', __( 'Type a question first.', 'wwmd' ), array( 'status' => 400 ) );
	}
	if ( ! wd_rate_ok( 'ask', 30, HOUR_IN_SECONDS ) ) {
		return new WP_Error( 'slow', __( 'You have asked a lot of questions. Please wait a little, or chat with us on WhatsApp.', 'wwmd' ), array( 'status' => 429 ) );
	}
	$history = array();
	foreach ( array_slice( (array) $r->get_param( 'history' ), -6 ) as $h ) {
		if ( is_array( $h ) && isset( $h['role'], $h['text'] ) && in_array( $h['role'], array( 'user', 'assistant' ), true ) ) {
			$history[] = array( $h['role'], mb_substr( sanitize_textarea_field( (string) $h['text'] ), 0, 1200 ) );
		}
	}
	$ai = wd_help_claude( $q, $history );
	if ( ! is_wp_error( $ai ) ) {
		return array(
			'ok'     => true,
			'answer' => $ai,
			'links'  => wd_help_pick_links( $q . ' ' . $ai ),
			'source' => 'ai',
		);
	}
	if ( 'nokey' !== $ai->get_error_code() ) {
		error_log( 'WWMD help chat: ' . $ai->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
	}
	list( $answer, $keys ) = wd_help_match( $q );
	return array(
		'ok'     => true,
		'answer' => $answer,
		'links'  => wd_help_pick_links( $q . ' ' . $answer, $keys ),
		'source' => 'faq',
	);
}

/* The chat bubble on every page except wedding pages, passes & print sheets. */
add_action(
	'wp_footer',
	static function () {
		if ( ! wd_opt( 'ai_on' ) || is_singular( 'wd_wedding' ) || get_query_var( 'wd_pass' ) || get_query_var( 'wd_cards' ) ) {
			return;
		}
		$chips = array(
			__( 'How do I create a wedding page?', 'wwmd' ),
			__( 'What does Premium include?', 'wwmd' ),
			__( 'Can I add a traditional wedding & nikkah?', 'wwmd' ),
			__( 'How does the cash & gift registry work?', 'wwmd' ),
			__( 'How is my money kept safe when I book?', 'wwmd' ),
		);
		?>
		<div class="help" data-help>
			<button class="help__fab" type="button" aria-expanded="false" aria-controls="wd-help" data-help-toggle><?php echo wd_icon( 'spark', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Ask us', 'wwmd' ); ?></span></button>
			<section class="help__panel" id="wd-help" role="dialog" aria-label="<?php esc_attr_e( 'Help chat', 'wwmd' ); ?>" hidden>
				<header class="help__head">
					<span class="help__av"><?php echo wd_icon( 'spark', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<span><b><?php esc_html_e( 'Help', 'wwmd' ); ?></b><small><?php esc_html_e( 'Questions about the site & how to do things', 'wwmd' ); ?></small></span>
					<button class="help__x" type="button" data-help-toggle aria-label="<?php esc_attr_e( 'Close help', 'wwmd' ); ?>"><?php echo wd_icon( 'close', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
				</header>
				<div class="help__log" data-help-log aria-live="polite">
					<div class="help__msg help__msg--bot"><?php esc_html_e( 'Hi! Ask me anything about wedding pages, RSVPs, gifts, Premium, bookings or becoming a seller.', 'wwmd' ); ?></div>
				</div>
				<div class="help__chips" data-help-chips>
					<?php foreach ( $chips as $wd_c ) : ?><button type="button" class="chip" data-help-chip><?php echo esc_html( $wd_c ); ?></button><?php endforeach; ?>
				</div>
				<form class="help__form" data-help-form>
					<label class="screen-reader-text" for="wd-help-q"><?php esc_html_e( 'Your question', 'wwmd' ); ?></label>
					<textarea id="wd-help-q" name="question" rows="1" maxlength="500" placeholder="<?php esc_attr_e( 'Type your question', 'wwmd' ); ?>" required></textarea>
					<button class="help__send" type="submit" aria-label="<?php esc_attr_e( 'Send', 'wwmd' ); ?>"><?php echo wd_icon( 'send', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
				</form>
				<p class="help__foot"><?php echo esc_html( wd_opt( 'ai_key' ) ? __( 'Answers come from an AI assistant & can be wrong.', 'wwmd' ) : __( 'Quick answers from our help guide.', 'wwmd' ) ); ?> <a href="<?php echo esc_url( wd_whatsapp( __( 'Hello! I have a question about Wedding Websites & More.', 'wwmd' ) ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Chat with a person on WhatsApp', 'wwmd' ); ?></a></p>
			</section>
		</div>
		<?php
	}
);
