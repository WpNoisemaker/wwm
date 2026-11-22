<?php
/**
 * The demo couples, vendors, marketplace listings and the people who sign in to look around.
 *
 * Wedding dates are written as a number of days from the day Demo::install() runs, so the countdowns
 * are sensible whenever the site goes up. Nothing here is a real person or a real business.
 *
 * Every demo email ends in @demo.test. That domain is reserved and can never receive mail, so a
 * password reset on a demo account cannot land in a stranger's inbox.
 */
return [

    // ------------------------------------------------------------------------------ the couples
    'weddings' => [
        [
            'email' => 'society@demo.test', 'key' => 'yusuf', 'slug' => 'yusuf-halima',
            'a' => 'Yusuf', 'b' => 'Halima', 'surname' => 'Abubakar', 'hashtag' => '#YusufHalima',
            'tagline' => 'Two families, one long table.',
            'story' => "We met at a cousin's naming ceremony in Kaduna, where Halima corrected the way Yusuf was carrying a tray of jollof. He has been taking instructions ever since. Our mothers had been planning this for two years before we admitted it was a good idea.",
            'city' => 'Abuja', 'state' => 'FCT', 'venue' => 'Abuja Event Centre', 'days' => 35, 'time' => '13:00:00',
            'plan' => 'society', 'template' => 'studio', 'colours' => ['#C8A15A', '#E9DCC0'],
            'cover' => 'photos/listing-red-gele.jpg', 'buzz' => 5120, 'views' => 9400, 'culture' => 'hausa', 'guests' => 44,
        ],
        [
            'email' => 'premium@demo.test', 'key' => 'chidi', 'slug' => 'chidi-amaka',
            'a' => 'Chidi', 'b' => 'Amaka', 'surname' => 'Okafor', 'hashtag' => '#ChidiFoundAmaka',
            'tagline' => 'He found her. She let him.',
            'story' => "Chidi asked for directions to a bank in Enugu and walked out with a phone number and a lifelong argument about whose turn it is to cook. Amaka says she knew by the second jollof. He says it was the first.",
            'city' => 'Enugu', 'state' => 'Enugu', 'venue' => 'Golf Club, GRA Enugu', 'days' => 21, 'time' => '11:00:00',
            'plan' => 'premium', 'template' => 'aso', 'colours' => ['#8A1C3B', '#E8B56A'],
            'cover' => 'photos/listing-couple-magenta.jpg', 'buzz' => 4210, 'views' => 7300, 'culture' => 'igbo', 'guests' => 40,
        ],
        [
            'email' => 'musa@demo.test', 'key' => 'musa', 'slug' => 'musa-zainab',
            'a' => 'Musa', 'b' => 'Zainab', 'surname' => 'Danjuma', 'hashtag' => '#MZForever',
            'tagline' => 'Eight years of long distance, ending at last.',
            'story' => "Musa was in Manchester and Zainab was in Kaduna for the first six years of this. They kept a shared note on their phones titled Things To Do When We Are In The Same Country. It is now forty-one items long and the first is a wedding.",
            'city' => 'Kaduna', 'state' => 'Kaduna', 'venue' => 'Umaru Musa Yar\'Adua Hall', 'days' => 60, 'time' => '12:00:00',
            'plan' => 'premium', 'template' => 'classic', 'colours' => ['#2F5D8C', '#9FBCE0'],
            'cover' => 'photos/listing-couple-olive.jpg', 'buzz' => 3240, 'views' => 5100, 'culture' => 'hausa', 'guests' => 36,
        ],
        [
            'email' => 'ibrahim@demo.test', 'key' => 'ibrahim', 'slug' => 'ibrahim-ummi',
            'a' => 'Ibrahim', 'b' => 'Ummi', 'surname' => 'Sani', 'hashtag' => '#IbrahimMeetsUmmi',
            'tagline' => 'Married, and still thanking everyone who came.',
            'story' => "We said yes in Kano on a Saturday, and then spent the evening being told by seventy relatives how we should have done it. We would do all of it again. Thank you for the notes and the photographs — they are all still on this page.",
            'city' => 'Kano', 'state' => 'Kano', 'venue' => 'Coronation Hall, Kano', 'days' => -40, 'time' => '11:00:00',
            'plan' => 'premium', 'template' => 'ivory', 'colours' => ['#7A5C3A', '#C3A46A'],
            'cover' => 'photos/couple-field.jpg', 'buzz' => 4680, 'views' => 8200, 'culture' => 'hausa', 'guests' => 34,
        ],
        [
            'email' => 'pro@demo.test', 'key' => 'seyi', 'slug' => 'seyi-folake',
            'a' => 'Seyi', 'b' => 'Folake', 'surname' => 'Adeyemi', 'hashtag' => '#SeyiWedsFolake',
            'tagline' => 'Aso-oke, gele and a very loud drummer.',
            'story' => "Seyi has been talking about this day since secondary school, according to everyone who was in secondary school with him. Folake took longer to be sure and wants it noted that she was right to.",
            'city' => 'Ibadan', 'state' => 'Oyo', 'venue' => 'Cultural Centre, Mokola', 'days' => 90, 'time' => '11:00:00',
            'plan' => 'pro', 'template' => 'aso', 'colours' => ['#1F7A4D', '#E3C46A'],
            'cover' => 'photos/listing-coral-gele.jpg', 'buzz' => 3880, 'views' => 6100, 'culture' => 'yoruba', 'guests' => 38,
        ],
        [
            'email' => 'tunde@demo.test', 'key' => 'tunde', 'slug' => 'tunde-bisi',
            'a' => 'Tunde', 'b' => 'Bisi', 'surname' => 'Ogunleye', 'hashtag' => '#TundeAndBisi',
            'tagline' => 'Small chops, big love.',
            'story' => "Bisi runs a catering business and Tunde keeps the accounts, so the wedding budget is the most scrutinised document in Abeokuta. We are told the small chops will be the best part. We are choosing to believe that is about the food.",
            'city' => 'Abeokuta', 'state' => 'Ogun', 'venue' => 'Kuto Event Hall', 'days' => 75, 'time' => '12:00:00',
            'plan' => 'pro', 'template' => 'classic', 'colours' => ['#B4571B', '#F0B24A'],
            'cover' => 'photos/listing-green-gele.jpg', 'buzz' => 2980, 'views' => 4300, 'culture' => 'yoruba', 'guests' => 30,
        ],
        [
            'email' => 'emeka@demo.test', 'key' => 'emeka', 'slug' => 'emeka-ngozi',
            'a' => 'Emeka', 'b' => 'Ngozi', 'surname' => 'Nwosu', 'hashtag' => '#EmekaNgozi2026',
            'tagline' => 'Lekki, a church, and a first dance we are still practising.',
            'story' => "Emeka cannot dance. Ngozi has accepted this and booked six lessons anyway. The lessons are going badly and we have decided to make it a feature of the reception.",
            'city' => 'Lagos', 'state' => 'Lagos', 'venue' => 'The Wheatbaker Ballroom, Ikoyi', 'days' => 50, 'time' => '14:00:00',
            'plan' => 'pro', 'template' => 'studio', 'colours' => ['#5B3FA0', '#D9C8F6'],
            'cover' => 'seed/couple-dance-close.jpg', 'buzz' => 2400, 'views' => 3500, 'culture' => 'igbo', 'guests' => 32,
        ],
        [
            'email' => 'free@demo.test', 'key' => 'kola', 'slug' => 'kola-lara',
            'a' => 'Kola', 'b' => 'Lara', 'surname' => 'Balogun', 'hashtag' => '#KolaAndLara',
            'tagline' => 'Beach wedding, bare feet, no agbada.',
            'story' => "We are getting married on a beach because Lara refused to be indoors for it and Kola refused to be in agbada for it. We compromised: it is outdoors, and Kola will be in linen.",
            'city' => 'Lagos', 'state' => 'Lagos', 'venue' => 'Landmark Beach, Victoria Island', 'days' => 120, 'time' => '16:00:00',
            'plan' => 'free', 'template' => 'classic', 'colours' => ['#E2683C', '#FFD426'],
            'cover' => 'photos/couple-beach.jpg', 'buzz' => 1450, 'views' => 2100, 'culture' => 'church', 'guests' => 26,
        ],
        [
            'email' => 'daniel@demo.test', 'key' => 'daniel', 'slug' => 'daniel-ifeoma',
            'a' => 'Daniel', 'b' => 'Ifeoma', 'surname' => 'Chukwu', 'hashtag' => '#DanielAndIfeoma',
            'tagline' => 'From Jos with love and a cold harmattan.',
            'story' => "We are getting married in January, when Jos is cold enough that the photographer has asked everyone to bring a wrapper for the outdoor shots. Our guests are being warned. Our grandmother is being kept warm first.",
            'city' => 'Jos', 'state' => 'Plateau', 'venue' => 'Plateau Hotel Gardens', 'days' => 150, 'time' => '11:00:00',
            'plan' => 'free', 'template' => 'classic', 'colours' => ['#7A8F2E', '#F3E6A0'],
            'cover' => 'seed/couple-field-bouquet.jpg', 'buzz' => 900, 'views' => 1300, 'culture' => 'church', 'guests' => 22,
        ],
        [
            // The one private page, so the pass-code screen can be seen. Code: TOLUAYO
            'email' => 'tolu@demo.test', 'key' => 'tolu', 'slug' => 'tolu-ayo',
            'a' => 'Tolu', 'b' => 'Ayo', 'surname' => 'Afolabi', 'hashtag' => '#ToluAndAyo',
            'tagline' => 'A private celebration, by invitation.',
            'story' => "This page is private. Guests need the code we sent with their invitation, and it does not appear in the directory or on the Buzz Chart.",
            'city' => 'Port Harcourt', 'state' => 'Rivers', 'venue' => 'By invitation', 'days' => 45, 'time' => '13:00:00',
            'plan' => 'free', 'template' => 'classic', 'colours' => ['#3F6F8F', '#B8D4E6'],
            'cover' => 'photos/couple-dance.jpg', 'buzz' => 600, 'views' => 400, 'culture' => 'yoruba', 'guests' => 14,
            'visibility' => 'private', 'pass_code' => 'TOLUAYO',
        ],
    ],

    // ------------------------------------------------------------------------ names for guests
    'first' => ['Adaeze', 'Chinedu', 'Tobi', 'Kemi', 'Femi', 'Bola', 'Sade', 'Aisha', 'Hauwa', 'Fatima', 'Obinna', 'Chioma',
                'Seun', 'Tolani', 'Dayo', 'Funke', 'Gbenga', 'Jide', 'Kunle', 'Mariam', 'Nkechi', 'Olu', 'Peju', 'Remi',
                'Sola', 'Uche', 'Wale', 'Yetunde', 'Zainab', 'Ibrahim', 'Amina', 'Bayo', 'Chika', 'Doyin', 'Efe', 'Folu'],
    'last'  => ['Adeyemi', 'Okafor', 'Bello', 'Eze', 'Balogun', 'Abubakar', 'Okonkwo', 'Ogunleye', 'Mohammed', 'Nwosu', 'Adebayo',
                'Lawal', 'Chukwu', 'Olawale', 'Danjuma', 'Afolabi', 'Obi', 'Sanni', 'Uzor', 'Ojo', 'Musa', 'Yakubu', 'Nnamdi'],

    // ----------------------------------------------------------------- notes left on the walls
    'notes' => [
        'So happy for you both. God has done it.',
        'Congratulations! The two of you together make sense. I said so years ago.',
        'Watching from Houston and crying into my coffee.',
        'May your home be full of laughter and a freezer full of jollof.',
        'We love you both. Send us the aso-ebi details immediately.',
        'This is the wedding I have been waiting for since 2019.',
        'Your story is the best thing on my phone this week.',
        'Wishing you peace, patience, and a very good photographer.',
        'I will be there with my whole family. Please feed us early.',
        'Ten years ago I prayed for this day for you. Congratulations, my children.',
    ],
    'captions' => ['The entrance.', 'Aso-ebi did not come to play.', 'My favourite two people.', 'The small chops queue.', 'Dancing before the DJ even started.', 'Look at that gele.'],
    'wall_photos' => ['photos/listing-couple-magenta.jpg', 'photos/listing-coral-gele.jpg', 'photos/listing-fabric-stall.jpg',
                      'photos/couple-dance.jpg', 'photos/listing-guests-magenta.jpg', 'photos/listing-olive-gele.jpg'],
    'gift_messages' => ['Congratulations!', 'God bless your union.', 'From all of us abroad.', 'For the honeymoon.', 'Do not spend it all on aso-ebi.',
                        'With love.', 'Proud of you both.', 'Enjoy it, you earned it.'],

    // ------------------------------------------------------------------------------ the vendors
    'vendors' => [
        ['email' => 'vendor@demo.test', 'slug' => 'golden-lens', 'name' => 'Golden Lens', 'category' => 'Photography', 'city' => 'Abuja', 'state' => 'FCT',
         'about' => 'Wedding photography and film across Nigeria. We shoot the introductions, the traditional and the white wedding, and we deliver the gallery in two weeks, not two months.',
         'phone' => '0803 555 0101', 'instagram' => 'goldenlensng', 'website' => '', 'logo' => 'photos/photographer.jpg', 'verified' => 1,
         'reviews' => [['Chidi & Amaka', 5, 'They found the moments we did not know we had. Every aunty is in the gallery, and that is the real test.', 'Thank you both. It was a joy.'],
                       ['Musa & Zainab', 5, 'Calm, early, invisible. Exactly what you want.', ''],
                       ['Yusuf & Halima', 5, 'Three ceremonies in two days and not one missed shot.', '']]],
        ['email' => null, 'slug' => 'bloom-co', 'name' => 'Bloom & Co', 'category' => 'Planning', 'city' => 'Lagos', 'state' => 'Lagos',
         'about' => 'Full planning and day-of coordination for weddings of fifty to five hundred. We keep the spreadsheet so you do not have to.',
         'phone' => '0803 555 0102', 'instagram' => 'bloomandcong', 'website' => '', 'logo' => 'photos/planner-phone.jpg', 'verified' => 1,
         'reviews' => [['Emeka & Ngozi', 5, 'The run of show was a work of art. Nobody knew anything was going wrong, and plenty was.', 'We will never tell.'],
                       ['Kola & Lara', 4, 'Brilliant on the day. A little slow replying in the planning weeks, but worth it.', '']]],
        ['email' => null, 'slug' => 'aso-royale', 'name' => 'Aso Royale', 'category' => 'Aso-oke', 'city' => 'Ibadan', 'state' => 'Oyo',
         'about' => 'Hand-woven aso-oke in your colours, made to measure. Sets for the couple, the families and the aso-ebi crowd.',
         'phone' => '0803 555 0103', 'instagram' => 'asoroyale', 'website' => '', 'logo' => 'photos/listing-fabric-stall.jpg', 'verified' => 1,
         'reviews' => [['Seyi & Folake', 5, 'The weave is extraordinary and it arrived a week early.', ''],
                       ['Tunde & Bisi', 5, 'Our mothers cried when they saw it. That is the review.', '']]],
        ['email' => null, 'slug' => 'chop-life', 'name' => 'Chop Life', 'category' => 'Catering', 'city' => 'Lagos', 'state' => 'Lagos',
         'about' => 'Nigerian catering done properly: jollof, small chops, asun, moi moi and a puff-puff station that has ended arguments.',
         'phone' => '0803 555 0104', 'instagram' => 'choplifeng', 'website' => '', 'logo' => 'photos/caterer.jpg', 'verified' => 1,
         'reviews' => [['Tunde & Bisi', 5, 'Bisi is a caterer and she approved. That is all you need to know.', ''],
                       ['Daniel & Ifeoma', 4, 'Excellent food. The queue was long at peak but moved well.', 'We have added a second station since. Thank you.']]],
        ['email' => null, 'slug' => 'gele-by-tope', 'name' => 'Gele by Tope', 'category' => 'Gele & makeup', 'city' => 'Lagos', 'state' => 'Lagos',
         'about' => 'Gele tying and bridal makeup. We come to you, we are early, and we do the aunties too.',
         'phone' => '0803 555 0105', 'instagram' => 'gelebytope', 'website' => '', 'logo' => 'photos/makeup.jpg', 'verified' => 1,
         'reviews' => [['Chidi & Amaka', 5, 'Eleven gele, one morning, zero drama.', ''],
                       ['Kola & Lara', 5, 'Held up in beach wind and a lot of dancing.', '']]],
        ['email' => null, 'slug' => 'drumline', 'name' => 'Drumline', 'category' => 'Live band', 'city' => 'Enugu', 'state' => 'Enugu',
         'about' => 'Eight-piece live band for the reception and the traditional: highlife, afrobeats, gospel and whatever the aunties request.',
         'phone' => '0803 555 0106', 'instagram' => 'drumlineband', 'website' => '', 'logo' => 'photos/dj.jpg', 'verified' => 0,
         'reviews' => [['Chidi & Amaka', 5, 'Nobody sat down for four hours.', '']]],
        ['email' => null, 'slug' => 'sweet-layers', 'name' => 'Sweet Layers', 'category' => 'Cakes', 'city' => 'Abuja', 'state' => 'FCT',
         'about' => 'Wedding cakes and dessert tables. Tell us your colours and your budget and we will tell you what is possible.',
         'phone' => '0803 555 0107', 'instagram' => 'sweetlayersabj', 'website' => '', 'logo' => 'photos/baker.jpg', 'verified' => 1,
         'reviews' => [['Yusuf & Halima', 5, 'Four tiers, no cracks, and tasted better than it looked.', ''],
                       ['Musa & Zainab', 5, 'Delivered in 38 degrees and arrived perfect.', '']]],
        ['email' => null, 'slug' => 'studio-nine', 'name' => 'Studio Nine', 'category' => 'Videography', 'city' => 'Kano', 'state' => 'Kano',
         'about' => 'Cinematic wedding films. A teaser in a week, the full film in a month, and drone shots where the venue allows.',
         'phone' => '0803 555 0108', 'instagram' => 'studionineng', 'website' => '', 'logo' => 'photos/photographer-studio.jpg', 'verified' => 1,
         'reviews' => [['Ibrahim & Ummi', 5, 'We have watched the film more times than we have admitted.', '']]],
    ],

    // ---------------------------------------------------------------------- marketplace listings
    'listings' => [
        ['slug' => 'g-class-airport-transfer-lagos', 'title' => 'Airport pickup, Lagos', 'kind' => 'ride', 'city' => 'Lagos', 'state' => 'Lagos', 'price' => 45000, 'unit' => 'trip', 'photos' => ['photos/listing-g-class.jpg'],
         'about' => 'A Mercedes G-Class and a driver who knows the Lekki-Ikoyi traffic better than the traffic does. Meet-and-greet at arrivals, bags handled.'],
        ['slug' => 'convoy-six-cars-abuja', 'title' => 'Convoy, six cars', 'kind' => 'ride', 'city' => 'Abuja', 'state' => 'FCT', 'price' => 780000, 'unit' => 'day', 'photos' => ['photos/listing-range-rover.jpg'],
         'about' => 'Six matching cars with drivers for the bridal train. Decorated to your colours, fuel included, and nobody is late.'],
        ['slug' => 'bridal-house-lekki', 'title' => 'Bridal house, Lekki', 'kind' => 'stay', 'city' => 'Lagos', 'state' => 'Lagos', 'price' => 320000, 'unit' => 'night', 'photos' => ['photos/listing-apartment.jpg'],
         'about' => 'A four-bedroom apartment for the bridal party to get ready in. Good light, a big mirror, and a kitchen that has seen a lot of morning-of jollof.'],
        ['slug' => 'honeymoon-suite-zanzibar', 'title' => 'Honeymoon suite, Zanzibar', 'kind' => 'honeymoon', 'city' => 'Zanzibar', 'state' => '', 'price' => 1900000, 'unit' => 'night', 'photos' => ['photos/listing-zanzibar.jpg'],
         'about' => 'A beachfront suite with breakfast on the terrace. Airport transfers and a sunset dhow cruise included for stays of five nights or more.'],
        ['slug' => 'obudu-mountain-lodge', 'title' => 'Obudu mountain lodge', 'kind' => 'honeymoon', 'city' => 'Cross River', 'state' => 'Cross River', 'price' => 410000, 'unit' => 'night', 'photos' => ['photos/listing-cape-town.jpg'],
         'about' => 'A cabin on the plateau, above the cloud on a good morning. Quiet, cool and a very long way from your family group chat.'],
        ['slug' => 'photo-booth-and-prints', 'title' => 'Photo booth & prints', 'kind' => 'service', 'city' => 'Lagos', 'state' => 'Lagos', 'price' => 260000, 'unit' => 'event', 'photos' => ['photos/listing-studio.jpg'],
         'about' => 'A photo booth with instant prints, props that have been approved by aunties, and a guest book made of the strips.'],
        ['slug' => 'ankara-and-lace-by-the-yard', 'title' => 'Ankara & lace by the yard', 'kind' => 'asoebi', 'city' => 'Lagos', 'state' => 'Lagos', 'price' => 0, 'unit' => 'yard', 'photos' => ['photos/listing-wax-prints.jpg'], 'on_ask' => 1,
         'about' => 'Wax prints, lace and george sourced for your aso-ebi, matched to your colours and delivered to your guests. Message us your colours and your head count for a price.'],
        ['slug' => 'airport-and-hotel-transfers-abuja', 'title' => 'Airport & hotel transfers, Abuja', 'kind' => 'ride', 'city' => 'Abuja', 'state' => 'FCT', 'price' => 35000, 'unit' => 'trip', 'photos' => ['photos/listing-airport.jpg'],
         'about' => 'Comfortable saloons and minibuses for guests flying in. We track the flight, so a delay is not your problem.'],
    ],

    // --------------------------------------- the people who sign in to look around, besides couples
    'people' => [
        ['email' => 'vendor@demo.test', 'name' => 'Golden Lens (demo vendor)', 'role' => 'member'],
        ['email' => 'seller@demo.test', 'name' => 'Demo Seller',               'role' => 'partner'],
        ['email' => 'staff@demo.test',  'name' => 'Demo Staff',                'role' => 'staff'],
    ],
];
