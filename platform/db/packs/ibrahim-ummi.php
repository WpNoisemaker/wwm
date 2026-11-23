<?php
/**
 * Couple pack: Ibrahim Bello & Ummahani Musa Tahir ("Ibrahim & Ummi").
 *
 * Their Premium page in the Ivory & Gold design, carried over from the old site with everything
 * they had written: the five events, the diary of their story, the squad, the registry and the
 * quiz. It is added once and never overwrites a page or an account that already exists. The
 * password is kept only as the hash the old site stored, so their existing password still works.
 */
return [
    'user' => [
        'email' => 'kwasunaira@gmail.com',
        'name'  => 'Ibrahim & Ummi',
        'hash'  => '$2y$12$wN22k7gatiEJ3kwZhZdfDuNgkM/p2D2S77ttSBGt5TQxvv3n.YWkW',
    ],
    'wedding' => [
        'slug'      => 'ibrahim-ummi',
        'partner_a' => 'Ibrahim',
        'partner_b' => 'Ummi',
        'hashtag'   => '',
        'tagline'   => 'From a friend’s introduction to a Wolimah in Ilorin.',
        'story'     => "A friend was certain we would suit each other. She was right. The first conversation ran late into the night, and by morning we both knew this was a road worth walking.",
        'wed_date'  => '2026-12-24',
        'wed_time'  => '18:00:00',
        'city'      => 'Ilorin',
        'state'     => 'Kwara',
        'country'   => 'Nigeria',
        'venue'     => 'Are Osan',
        'cover'     => 'couples/ibrahim-ummi/venue-hall.jpg',
        'plan'      => 'premium',
        'template'  => 'ivory',
        'currency'  => 'NGN',
        'visibility'=> 'public',
        'buzz'      => 4890,
        'views'     => 8600,
        'design'    => [
            'custom'   => 'ivory',
            'primary'  => '#7A5C3A',
            'accent'   => '#C3A46A',
            'photo2'   => 'couples/ibrahim-ummi/memory-garden.jpg',
            'names'    => ['Ibrahim Bello', 'Ummahani Musa Tahir'],
            'hosts'    => 'Together with their families',
            'invite'   => 'request the honour of your presence at the wedding celebrations of',
            'sign'     => 'With love & gratitude',
            'palette'  => [['Ivory', '#FBF7EF'], ['Beige', '#EEE4D3'], ['Carton', '#A9845A'], ['Gold', '#C3A46A'], ['Champagne', '#E9D4A7']],
            'live'     => 'We stream the Kamu and the Wedding Dinner in Kano live, right here.',
            'chapters' => [
                ['The very beginning', 'A friend’s introduction', "A friend was certain we would suit each other. She was right. The first conversation ran late into the night, and by morning we both knew this was a road worth walking.", 'Ummi', ''],
                ['Sunset, Succoth Gardens', 'Succoth Gardens, Ilorin', "We watched the sun go down and forgot to take pictures. A storybook date, the kind you keep retelling until everybody around you is tired of hearing it.", 'Ibrahim', 'couples/ibrahim-ummi/memory-garden.jpg'],
                ['The night of closed doors', 'Charcoal, Ilorin', "Three restaurants were closed that evening. We settled for the fourth, laughing the whole way, and it turned out perfect. It usually does with him.", 'Ummi', 'couples/ibrahim-ummi/memory-cafe.jpg'],
                ['First time with chopsticks', 'Indomie Cafe', "Neither of us could hold them properly. The food was good. The memory was better. We still argue about who dropped more noodles.", 'Ibrahim', ''],
                ['Two nerds in their element', 'Muslim Tech Expo', "We went together and split up within ten minutes, then found each other at the same stand anyway. That is more or less how we do everything.", '', ''],
                ['The hall that kept our secret', 'Google DevFest', "We sat in the very hall that will host our reception, with no idea what it would one day mean to us. Now we are simply excited for the life ahead. Bismillah.", 'Ibrahim & Ummi', 'couples/ibrahim-ummi/dancefloor.jpg'],
            ],
            'timeline' => [
                'Wolimah' => [
                    ['11:00 AM', 'Wolimah Celebration', 'A joyful gathering to celebrate the union with prayers, warmth and shared blessings.'],
                    ['1:30 PM', 'Prayer Break', 'A quiet pause for prayer before the celebration continues.'],
                    ['2:00 PM', 'Food & Photos', 'Guests enjoy the meal, take pictures and share beautiful moments with the couple.'],
                    ['3:30 PM', 'Farewell & Duas', 'We close the day with heartfelt goodbyes, gratitude and prayers for the journey ahead.'],
                ],
            ],
        ],
    ],
    'programs' => [
        ['title' => 'Kamu', 'kind' => 'Islamic wedding (Nikkah)', 'starts_at' => '2026-12-18 10:00:00', 'venue' => '', 'address' => 'Kano', 'dress' => 'Modest traditional attire', 'note' => 'The solemnisation of our marriage before family, friends and Allah.'],
        ['title' => 'Wedding Dinner', 'kind' => 'Reception', 'starts_at' => '2026-12-19 19:00:00', 'venue' => '', 'address' => 'Kano', 'dress' => 'Elegant evening wear', 'note' => 'An evening of dinner, music and dancing. Bring your spraying hands.'],
        ['title' => 'Aisun', 'kind' => 'Eve of the wedding', 'starts_at' => '2026-12-24 18:00:00', 'venue' => '', 'address' => 'Ilorin', 'dress' => 'Traditional attire', 'note' => 'The night before the Wolimah, with family, friends and good food.'],
        ['title' => 'Wolimah', 'kind' => 'Wedding celebration', 'starts_at' => '2026-12-25 11:00:00', 'venue' => 'Are Osan', 'address' => 'Ilorin, Kwara State', 'dress' => 'Ivory, beige & gold', 'note' => 'A joyful gathering to celebrate the union with prayers, food and blessings.'],
        ['title' => 'Welcome Party', 'kind' => 'Welcome party', 'starts_at' => '2026-12-26 18:00:00', 'venue' => '', 'address' => 'Ilorin', 'dress' => 'Smart & festive', 'note' => 'Welcoming the bride home with music, dancing and joy.'],
    ],
    'squad' => [
        ['bride', 'Fatimah Olarewaju', 'Chief bridesmaid', '“Timah” · Pharmacist · Baking, thrift shopping and long voice notes', 'timah.bakes'],
        ['bride', 'Halimat Bello', 'Bridesmaid', '“Halz” · Product Designer · Sketching, coffee crawls and sudoku', 'halzdesigns'],
        ['bride', 'Zainab Abdulsalam', 'Bridesmaid', '“Zee” · Medical Student · Poetry, netball and photography', 'zee.writes'],
        ['bride', 'Aminat Yusuf', 'Bridesmaid', '“Ami” · Content Strategist · Travel, podcasts and interior décor', 'ami.tells'],
        ['bride', 'Khadijah Ibrahim', 'Bridesmaid', 'Accountant · Reading, calligraphy and cycling', 'khadijah.ink'],
        ['bride', 'Ruqayyah Adekunle', 'Little bride', '“Ruqy” · Dancing, drawing and spelling bees', 'ruqy.dances'],
        ['groom', 'Ibrahim Salaudeen', 'Best man', '“Ibro” · Software Engineer · Chess, football and open source', ''],
        ['groom', 'Yusuf Adeyemi', 'Groomsman', '“YK” · Civil Engineer · Gym, car talk and grilling', 'yk.builds'],
        ['groom', 'Abdulrahman Lawal', 'Groomsman', '“Rahman” · Data Analyst · Table tennis, anime and fantasy football', ''],
        ['groom', 'Musa Okonkwo', 'Groomsman', '“Mus” · Architect · Sketching, hiking and vinyl records', 'mus.draws'],
        ['groom', 'Tunde Ajala', 'Groomsman', 'Entrepreneur · Sneakers, basketball and cooking', 'tundekicks'],
        ['groom', 'Ahmad Sulaiman', 'Little groom', '“Ahmzy” · Lego, swimming and cartoons', 'ahmzy.lego'],
    ],
    'gifts' => [
        ['Our first home', 'Help us furnish our first home together.', 'fund', 1500000],
        ['Honeymoon', 'A quiet week away after the celebrations.', 'fund', 1200000],
        ['Dinner set for twelve', 'For the family dinners we plan to host.', 'item', 120000],
        ['Ivory bedding set', 'Soft sheets and pillows for our new home.', 'item', 95000],
        ['Air fryer', 'For quick suya nights in.', 'item', 85000],
        ['Prayer mats for two', 'Matching mats for our first home.', 'item', 40000],
    ],
];
