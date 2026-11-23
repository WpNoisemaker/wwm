<?php
declare(strict_types=1);

/**
 * Demo content: thirty wedding pages in eleven countries, one for every design and plan, their guests,
 * gifts, notes and quiz leaderboards, vendors with reviews, and the 45 community conversations, plus
 * the accounts to sign in and look around. The marketplace is not demo: it holds the real sellers.
 *
 * Every row it creates is recorded in a small table (demo_rows). That record is what lets remove()
 * delete exactly the demo rows and never touch anything a real person has made, even after the demo
 * data has been edited. Nothing is hard-coded to an id.
 *
 * The demo accounts share a published password, so they must not outlive the demo. Removing the
 * demo content removes them, and the installer and HQ both say so plainly.
 */
final class Demo
{
    /** Published on purpose: these accounts exist to be signed in to, and go when the demo does. */
    public const PASSWORD = 'DemoPass2026!';

    /** What a guest gives, in each currency, so a London registry is in pounds that make sense. */
    private const AMOUNTS = [
        'NGN' => [5000, 10000, 20000, 25000, 50000, 100000], 'GBP' => [20, 30, 50, 75, 100, 150], 'USD' => [25, 50, 75, 100, 150, 250],
        'CAD' => [25, 50, 75, 100, 150, 250], 'AUD' => [25, 50, 75, 100, 150, 250], 'NZD' => [25, 50, 75, 100, 150, 250],
        'AED' => [100, 150, 200, 300, 500, 1000], 'KES' => [1000, 2000, 3000, 5000, 10000, 20000], 'GHS' => [100, 200, 300, 500, 800, 1000],
        'RWF' => [10000, 20000, 30000, 50000, 100000, 200000], 'ZAR' => [200, 300, 500, 800, 1000, 2000],
    ];

    /**
     * Questions for the demo's "who knows them best" quizzes: the things family and friends know
     * about one person. Never how the couple met; that belongs to the couple, not the quiz.
     */
    private const QUIZ_BANK = [
        ["What is {n}'s favourite food?", ['Jollof rice', 'Pounded yam and egusi', 'Suya', 'Fried plantain', 'Pepper soup', 'Moi moi', 'Fufu and light soup', 'Waakye', 'Nyama choma']],
        ["Which drink does {n} always order?", ['Chapman', 'Malt', 'Zobo', 'Coke, no ice', 'Palm wine', 'Tea with three sugars', 'Just water', 'Fanta']],
        ["Which football club does {n} support?", ['Arsenal', 'Chelsea', 'Manchester United', 'Liverpool', 'Barcelona', 'Real Madrid', 'None, they hate football']],
        ["What is {n}'s favourite colour?", ['Burgundy', 'Emerald green', 'Royal blue', 'Gold', 'Black', 'Lilac', 'Peach', 'White']],
        ["Which month is {n}'s birthday?", ['January', 'March', 'April', 'June', 'August', 'September', 'November', 'December']],
        ["What is {n} most scared of?", ['Cockroaches', 'Flying', 'Dogs', 'Heights', 'Lizards', 'Deep water', 'Their mother']],
        ["What would {n} do on a free Saturday?", ['Sleep until noon', 'Watch football', 'Cook for everyone', 'Go to the market', 'Binge a Nollywood series', 'Go to the gym', 'Visit family']],
        ["Which artist does {n} play the most?", ['Burna Boy', 'Wizkid', 'Asake', 'Tems', 'Sauti Sol', 'Sarkodie', 'Davido', 'Simi']],
    ];

    /** Plans from which a guest pass code is issued, matching the real RSVP flow. */
    private const PASSES_FROM = ['pro', 'premium', 'society'];

    /* ------------------------------------------------------------------------- what is there */

    public static function present(): bool
    {
        if (!Db::val("SHOW TABLES LIKE 'demo_rows'")) {
            return false;
        }
        return (int) Db::val('SELECT COUNT(*) FROM demo_rows', [], 0) > 0;
    }

    /** @return array<string,int> Rows per table, for telling the admin what a button will touch. */
    public static function counts(): array
    {
        if (!Db::val("SHOW TABLES LIKE 'demo_rows'")) {
            return [];
        }
        $out = [];
        foreach (Db::all('SELECT tbl, COUNT(*) n FROM demo_rows GROUP BY tbl') as $r) {
            $out[(string) $r['tbl']] = (int) $r['n'];
        }
        return $out;
    }

    /** @return array<int,array{email:string,who:string,shows:string}> The accounts, for the installer and the guide. */
    public static function logins(): array
    {
        return [
            ['email' => 'society@demo.test', 'who' => 'Couple — High Society', 'shows' => 'Yusuf & Halima: the Savannah design'],
            ['email' => 'premium@demo.test', 'who' => 'Couple — Premium',       'shows' => 'Chidi & Amaka: the Royal design, spraying, livestream'],
            ['email' => 'emeka@demo.test',   'who' => 'Couple — Pro',           'shows' => 'Emeka & Ngozi: Classic in their own colours, squad, gallery'],
            ['email' => 'free@demo.test',    'who' => 'Couple — Free',          'shows' => 'Kola & Lara: the free page, black & white'],
            ['email' => 'vendor@demo.test',  'who' => 'Vendor',                 'shows' => 'Owns the Golden Lens profile'],
            ['email' => 'staff@demo.test',   'who' => 'Staff',                  'shows' => 'Can open HQ (but not remove the demo content)'],
        ];
    }

    /* ----------------------------------------------------------------------------- adding it */

    /**
     * Add the demo content. Refuses if it is already there or if a real page already uses one of its
     * names, and if anything goes wrong part-way it removes what it had added, so it never leaves a
     * half-made demo behind.
     *
     * @return array{logins:array,counts:array<string,int>}
     */
    public static function install(): array
    {
        if (self::present()) {
            throw new RuntimeException('Demo content is already installed.');
        }
        $root    = App::root();
        $content = require $root . '/db/demo/content.php';
        $talks   = require $root . '/db/demo/talks.php';
        self::preflight($content);

        Db::run('CREATE TABLE IF NOT EXISTS demo_rows (
                    tbl VARCHAR(40) NOT NULL, row_id INT UNSIGNED NOT NULL, PRIMARY KEY (tbl, row_id)
                 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

        try {
            self::seed($content, $talks);
        } catch (Throwable $e) {
            self::remove();
            throw $e;
        }
        return ['logins' => self::logins(), 'counts' => self::counts()];
    }

    /** Refuse before writing anything if a real row would collide with a demo one. */
    private static function preflight(array $c): void
    {
        $emails = array_merge(array_column($c['weddings'], 'email'), array_column($c['people'], 'email'));
        foreach ($emails as $e) {
            if (Db::val('SELECT id FROM users WHERE email = ?', [$e])) {
                throw new RuntimeException("An account already uses {$e}, so the demo content was not added.");
            }
        }
        foreach ([['weddings', $c['weddings']], ['vendors', $c['vendors']], ['listings', $c['listings']]] as [$table, $rows]) {
            foreach ($rows as $r) {
                if (Db::val("SELECT id FROM {$table} WHERE slug = ?", [$r['slug']])) {
                    throw new RuntimeException("A page called \"{$r['slug']}\" already exists, so the demo content was not added.");
                }
            }
        }
    }

    private static function put(string $table, array $row): int
    {
        $id = Db::insert($table, $row);
        Db::run('INSERT IGNORE INTO demo_rows (tbl, row_id) VALUES (?, ?)', [$table, $id]);
        return $id;
    }

    private static function seed(array $c, array $talks): void
    {
        mt_srand(20260101);   // the same demo every time, so a screenshot is always of the same site
        // The database's own clock, so every stamp here agrees with the ones MySQL writes itself.
        $now = (int) strtotime((string) Db::val('SELECT NOW()'));
        $ago = static fn(int $secs): string => date('Y-m-d H:i:s', $now - max(0, $secs));
        $pick = static fn(array $a): mixed => $a[mt_rand(0, count($a) - 1)];
        $person = static fn(): string => $pick($c['first']) . ' ' . $pick($c['last']);

        // ---- people -------------------------------------------------------------------------
        $hash = password_hash(self::PASSWORD, PASSWORD_DEFAULT);   // one hash, shared: bcrypt per user is slow
        $uid  = [];
        foreach ($c['weddings'] as $w) {
            $uid[$w['email']] = self::put('users', [
                'email' => $w['email'], 'pass' => $hash, 'name' => $w['a'] . ' ' . $w['surname'],
                'country' => 'Nigeria', 'role' => 'member', 'created_at' => $ago(86400 * 60),
            ]);
        }
        foreach ($c['people'] as $p) {
            $uid[$p['email']] = self::put('users', [
                'email' => $p['email'], 'pass' => $hash, 'name' => $p['name'],
                'country' => 'Nigeria', 'role' => $p['role'], 'created_at' => $ago(86400 * 60),
            ]);
        }

        // ---- the couples --------------------------------------------------------------------
        $giftPool = [
            ['Our honeymoon',            'Zanzibar, if you can help us get there.',      'fund'],
            ['Our first home',           'Every naira goes into the deposit.',            'cash'],
            ['Help us furnish',          'A sofa that fits two people and three in-laws.', 'fund'],
            ['A gift from the registry', 'A blender, a pressure cooker and good wishes.',  'item'],
        ];
        $squadRoles = [['bride', 'Chief bridesmaid'], ['groom', 'Best man'], ['bride', 'Bridesmaid'], ['groom', 'Groomsman']];
        $squadWords = [
            'Has known them since primary school and still has the receipts.',
            'Will give a speech nobody asked for and everybody will love.',
            'Responsible for the group chat, the schedule and the snacks.',
            'Here for the jollof, and also for them.',
        ];
        $n = 0;
        foreach ($c['weddings'] as $i => $w) {
            $when = $now + $w['days'] * 86400;
            // Pro pages and up get a quiz about each partner, five questions each.
            $quizzes = null;
            if ($w['plan'] !== 'free') {
                $quizzes = [];
                foreach (['a' => $w['a'], 'b' => $w['b']] as $side => $name) {
                    $keys = array_keys(self::QUIZ_BANK);
                    usort($keys, static fn () => mt_rand(-1, 1));
                    $quizzes[$side] = [];
                    foreach (array_slice($keys, 0, 5) as $k) {
                        [$q, $pool] = self::QUIZ_BANK[$k];
                        usort($pool, static fn () => mt_rand(-1, 1));
                        $opts = array_slice($pool, 0, mt_rand(3, 4));
                        $quizzes[$side][] = [str_replace('{n}', $name, $q), $opts, mt_rand(0, count($opts) - 1)];
                    }
                }
            }
            $design = ['primary' => $w['colours'][0], 'accent' => $w['colours'][1], 'photo2' => $w['photo2'] ?? ''];
            if ($quizzes) {
                $design['quizzes'] = $quizzes;
            }
            $wid = self::put('weddings', [
                'owner_id' => $uid[$w['email']], 'slug' => $w['slug'], 'partner_a' => $w['a'], 'partner_b' => $w['b'],
                'hashtag' => $w['hashtag'], 'tagline' => $w['tagline'], 'story' => $w['story'],
                'wed_date' => date('Y-m-d', $when), 'wed_time' => $w['time'],
                'city' => $w['city'], 'state' => $w['state'], 'country' => $w['country'] ?? 'Nigeria', 'venue' => $w['venue'],
                'cover' => $w['cover'], 'plan' => $w['plan'], 'template' => $w['template'],
                'design' => json_encode($design, JSON_UNESCAPED_UNICODE),
                'currency' => Wedding::CURRENCY[$w['country'] ?? 'Nigeria'] ?? 'NGN', 'visibility' => $w['visibility'] ?? 'public', 'pass_code' => $w['pass_code'] ?? '',
                'buzz' => $w['buzz'], 'views' => $w['views'], 'created_at' => $ago(86400 * mt_rand(20, 90)),
            ]);

            foreach (self::programs($w['culture'], $when, $w['venue'], $w['city']) as $k => $p) {
                self::put('programs', $p + ['wedding_id' => $wid, 'sort' => $k]);
            }

            foreach ($squadRoles as $k => [$side, $role]) {
                self::put('squad', [
                    'wedding_id' => $wid, 'side' => $side, 'name' => $person(), 'role' => $role,
                    'words' => $squadWords[$k], 'status' => 'joined', 'sort' => $k,
                ] + self::squadProfile($i * 4 + $k));
            }

            // Gifts, each backed by real payment rows so "raised" is the sum of something, not a number.
            $fee = in_array($w['plan'], ['premium', 'society'], true) ? 0.0 : 0.04;
            foreach (array_slice($giftPool, $i % 2, $w['plan'] === 'free' ? 2 : 3) as $g => [$title, $note, $kind]) {
                $gid = self::put('gifts', [
                    'wedding_id' => $wid, 'title' => $title, 'note' => $note, 'kind' => $kind,
                    'target' => 0, 'raised' => 0, 'sort' => $g,
                ]);
                $sum = 0;
                for ($p = mt_rand(4, 8); $p > 0; $p--) {
                    $amount = $pick(self::AMOUNTS[Wedding::CURRENCY[$w['country'] ?? 'Nigeria'] ?? 'NGN'] ?? self::AMOUNTS['NGN']);
                    $sum += $amount;
                    $age = mt_rand(86400, 20 * 86400);
                    self::put('payments', [
                        'ref' => 'DEMO-' . strtoupper(substr($w['key'], 0, 3)) . '-' . (++$n), 'purpose' => 'gift',
                        'wedding_id' => $wid, 'gift_id' => $gid, 'payer_name' => $person(),
                        'message' => $pick($c['gift_messages']), 'amount' => $amount, 'fee' => (int) round($amount * $fee),
                        'currency' => Wedding::CURRENCY[$w['country'] ?? 'Nigeria'] ?? 'NGN', 'gateway' => 'demo', 'gateway_ref' => 'demo-' . $n,
                        'status' => $age > 10 * 86400 ? 'released' : 'paid',
                        'paid_at' => $ago($age), 'created_at' => $ago($age),
                    ]);
                }
                $round = max(10, (int) (max(self::AMOUNTS[Wedding::CURRENCY[$w['country'] ?? 'Nigeria'] ?? 'NGN']) / 2));
                Db::update('gifts', ['raised' => $sum, 'target' => (int) (ceil($sum * (1.6 + mt_rand(0, 12) / 10) / $round) * $round)], 'id = ?', [$gid]);
            }

            if (in_array($w['plan'], ['premium', 'society'], true)) {
                self::sprays($wid, Wedding::CURRENCY[$w['country'] ?? 'Nigeria'] ?? 'NGN', (string) $w['key']);
            }

            // Guests: a believable spread of yes, maybe and no, with pass codes where the plan issues them.
            $passes = in_array($w['plan'], self::PASSES_FROM, true);
            for ($g = 0; $g < $w['guests']; $g++) {
                $roll = mt_rand(1, 100);
                $coming = $roll <= 72 ? 'yes' : ($roll <= 86 ? 'maybe' : 'no');
                self::put('guests', [
                    'wedding_id' => $wid, 'name' => $person(), 'phone' => '0803 555 ' . sprintf('%04d', mt_rand(1, 9999)),
                    'party' => $coming === 'no' ? 1 : mt_rand(1, 4), 'coming' => $coming,
                    'note' => mt_rand(1, 10) === 1 ? $pick(['Can I bring my mother?', 'Vegetarian, please.', 'Flying in from London.', 'We will be late, traffic.']) : null,
                    'pass_code' => $passes && $coming === 'yes' ? strtoupper(bin2hex(random_bytes(3))) : null,
                    'table_no' => $coming === 'yes' && mt_rand(1, 10) <= 4 ? (string) mt_rand(1, 14) : '',
                    // A wedding that has already happened has people who were checked in at the gate.
                    'checked_in' => $w['days'] < 0 && $coming === 'yes' && mt_rand(1, 10) <= 8 ? date('Y-m-d H:i:s', $when + mt_rand(0, 7200)) : null,
                    'created_at' => $ago(mt_rand(3600, 24 * 86400)),
                ]);
            }

            // Their leaderboards: friends and family who have played each partner's quiz.
            if ($quizzes) {
                Quiz::table();
                foreach ($quizzes as $side => $qs) {
                    for ($k = mt_rand(4, 9); $k > 0; $k--) {
                        $total = count($qs);
                        $score = max(1, min($total, $total - mt_rand(0, 3) + (mt_rand(0, 3) === 0 ? 1 : 0)));
                        self::put('quiz_scores', [
                            'wedding_id' => $wid, 'side' => $side, 'name' => $pick($c['first']) . ' ' . substr($pick($c['last']), 0, 1) . '.',
                            'score' => $score, 'total' => $total, 'created_at' => $ago(mt_rand(3600, 20 * 86400)),
                        ]);
                    }
                }
            }

            // The wall of love: a few notes, and a photograph or two.
            for ($k = mt_rand(3, 5); $k > 0; $k--) {
                self::put('wall', [
                    'wedding_id' => $wid, 'kind' => 'note', 'name' => $pick($c['first']), 'body' => $pick($c['notes']),
                    'likes' => mt_rand(2, 60), 'status' => 'live', 'created_at' => $ago(mt_rand(3600, 18 * 86400)),
                ]);
            }
            for ($k = mt_rand(1, 2); $k > 0; $k--) {
                self::put('wall', [
                    'wedding_id' => $wid, 'kind' => 'photo', 'name' => $pick($c['first']), 'body' => $pick($c['captions']),
                    'image' => $pick($c['wall_photos']), 'likes' => mt_rand(5, 120), 'status' => 'live',
                    'created_at' => $ago(mt_rand(3600, 18 * 86400)),
                ]);
            }
        }

        // A couple of payout requests, so HQ has something waiting and something done.
        self::put('payouts', ['user_id' => $uid['premium@demo.test'], 'amount' => 150000, 'currency' => 'NGN', 'bank' => 'GTBank',
                              'account' => '0123456789', 'account_nm' => 'Chidi Okafor', 'status' => 'requested', 'created_at' => $ago(2 * 86400)]);
        self::put('payouts', ['user_id' => $uid['society@demo.test'], 'amount' => 300000, 'currency' => 'NGN', 'bank' => 'Access Bank',
                              'account' => '0987654321', 'account_nm' => 'Yusuf Abubakar', 'status' => 'sent', 'sent_at' => $ago(5 * 86400), 'created_at' => $ago(9 * 86400)]);

        // ---- vendors, and what couples said about them ---------------------------------------
        foreach ($c['vendors'] as $v) {
            $vid = self::put('vendors', [
                'user_id' => $v['email'] ? ($uid[$v['email']] ?? null) : null, 'slug' => $v['slug'], 'name' => $v['name'],
                'category' => $v['category'], 'city' => $v['city'], 'state' => $v['state'], 'about' => $v['about'],
                'phone' => $v['phone'], 'instagram' => $v['instagram'], 'website' => $v['website'], 'logo' => $v['logo'],
                'verified' => $v['verified'], 'verified_until' => $v['verified'] ? date('Y-m-d', $now + 365 * 86400) : null,
                'created_at' => $ago(86400 * mt_rand(30, 100)),
            ]);
            $stars = 0;
            foreach ($v['reviews'] as [$name, $s, $body, $reply]) {
                $stars += $s;
                self::put('reviews', [
                    'vendor_id' => $vid, 'name' => $name, 'stars' => $s, 'body' => $body,
                    'reply' => $reply !== '' ? $reply : null, 'status' => 'live', 'created_at' => $ago(mt_rand(86400, 40 * 86400)),
                ]);
            }
            // The rating and the count are worked out from the reviews above, never invented.
            Db::update('vendors', ['rating' => round($stars / count($v['reviews']), 2), 'reviews' => count($v['reviews'])], 'id = ?', [$vid]);
        }

        // ---- the marketplace ------------------------------------------------------------------
        foreach ($c['listings'] as $l) {
            self::put('listings', [
                'partner_id' => $uid['seller@demo.test'], 'slug' => $l['slug'], 'title' => $l['title'], 'kind' => $l['kind'],
                'about' => $l['about'], 'city' => $l['city'], 'state' => $l['state'], 'price' => $l['price'], 'price_unit' => $l['unit'],
                'on_ask' => $l['on_ask'] ?? 0, 'photos' => json_encode($l['photos']), 'status' => 'live',
                'created_at' => $ago(86400 * mt_rand(5, 60)),
            ]);
        }

        // ---- the community: 45 conversations and their replies --------------------------------
        foreach ($talks as $t) {
            $age = max(600, (int) $t['hours'] * 3600 + mt_rand(0, 1800));
            $tid = self::put('talks', [
                'topic' => $t['topic'], 'pen_name' => $t['name'], 'body' => $t['body'], 'bg' => $t['bg'],
                'likes' => $t['likes'], 'replies' => count($t['replies']), 'status' => 'live', 'is_seed' => 1,
                'created_at' => $ago($age),
            ]);
            foreach ($t['replies'] as $k => [$name, $body, $likes]) {
                // Each reply lands after the post and before now, never in the future.
                $replyAge = max(120, $age - ($k + 1) * mt_rand(600, 3000));
                self::put('talk_replies', [
                    'talk_id' => $tid, 'pen_name' => $name, 'body' => $body, 'likes' => $likes, 'status' => 'live',
                    'created_at' => $ago($replyAge),
                ]);
            }
        }
    }

    /** The ceremonies for a kind of wedding, as offsets from the main day. */
    private static function programs(string $culture, int $when, string $venue, string $city): array
    {
        $at = static fn(int $days, string $hm) => date('Y-m-d', $when + $days * 86400) . ' ' . $hm . ':00';
        $row = static fn(string $title, string $kind, string $starts, string $where, string $dress, bool $private = false, string $note = '') => [
            'title' => $title, 'kind' => $kind, 'starts_at' => $starts, 'venue' => $where, 'dress' => $dress,
            'note' => $note !== '' ? $note : null, 'is_private' => $private ? 1 : 0,
        ];
        return match ($culture) {
            'hausa' => [
                $row('Family dinner', 'dinner', $at(-2, '19:00'), 'By invitation only', 'Smart', true, 'Close family only. The couple will share the venue directly.'),
                $row('Kamu', 'kamu', $at(-1, '16:00'), "The bride's family house, {$city}", 'Lace and gold'),
                $row('Nikkah', 'nikkah', $at(0, '10:00'), "Central Mosque, {$city}", 'Modest, cream and gold'),
                $row('Walimah', 'walima', $at(0, '16:00'), $venue, 'Cream and gold'),
            ],
            'igbo' => [
                $row('Igba Nkwu (traditional wedding)', 'traditional', $at(0, '10:00'), "The family compound, {$city}", 'Isiagu and george wrappers, coral beads'),
                $row('White wedding', 'church', $at(1, '11:00'), "St. Peter's Cathedral, {$city}", 'Suits and long dresses'),
                $row('Reception', 'reception', $at(1, '15:00'), $venue, 'Black tie, burgundy accents'),
            ],
            'yoruba' => [
                $row('Introduction', 'introduction', $at(-1, '11:00'), "The bride's family house, {$city}", 'Aso-oke, in green and gold'),
                $row('Traditional wedding', 'traditional', $at(0, '11:00'), $venue, 'Aso-oke, gele and agbada'),
                $row('Church service', 'church', $at(1, '10:00'), "Cathedral Church, {$city}", 'Suits and long dresses'),
                $row('Reception', 'reception', $at(1, '14:00'), $venue, 'Black tie'),
            ],
            'ghana' => [
                $row('Knocking ceremony', 'traditional', $at(-1, '10:00'), "The bride's family house, {$city}", 'Kente, family colours'),
                $row('Engagement', 'traditional', $at(0, '09:00'), "The bride's family house, {$city}", 'Kente and kaba'),
                $row('Church wedding', 'church', $at(0, '14:00'), "Holy Spirit Cathedral, {$city}", 'Suits and long dresses'),
                $row('Reception', 'reception', $at(0, '18:00'), $venue, 'Kente accents'),
            ],
            'kenya' => [
                $row('Ruracio (dowry)', 'traditional', $at(-14, '10:00'), "The bride's family home, {$city}", 'Kitenge and shukas'),
                $row('Church wedding', 'church', $at(0, '10:00'), "All Saints Cathedral, {$city}", 'Suits and long dresses'),
                $row('Reception', 'reception', $at(0, '14:00'), $venue, 'Garden formal'),
            ],
            'rwanda' => [
                $row('Gusaba (traditional)', 'traditional', $at(-1, '11:00'), "The bride's family home, {$city}", 'Imishanana and umukenyero'),
                $row('Church wedding', 'church', $at(0, '10:00'), "Sainte-Famille Church, {$city}", 'Suits and long dresses'),
                $row('Reception', 'reception', $at(0, '15:00'), $venue, 'Smart, gold accents'),
            ],
            'diaspora' => [
                $row('Traditional wedding', 'traditional', $at(-1, '13:00'), $venue, 'Aso-oke, gele and agbada'),
                $row('White wedding', 'church', $at(0, '11:00'), "St. Mary's Church, {$city}", 'Suits and long dresses'),
                $row('Reception', 'reception', $at(0, '17:00'), $venue, 'Black tie'),
            ],
            default => [
                $row('White wedding', 'church', $at(0, '11:00'), "Chapel, {$city}", 'Suits and long dresses'),
                $row('Reception', 'reception', $at(0, '15:00'), $venue, 'Smart'),
                $row('After-party', 'afterparty', $at(0, '20:00'), $venue, 'Come as you are'),
            ],
        };
    }

    /* --------------------------------------------------------------------------- removing it */

    /**
     * Delete exactly the rows this class created and nothing else.
     *
     * @return array<string,int> What was removed, per table.
     */
    public static function remove(): array
    {
        if (!Db::val("SHOW TABLES LIKE 'demo_rows'")) {
            return [];
        }
        $counts = self::counts();
        $ids = static fn(string $t): string => implode(',', array_map('intval', array_column(Db::all('SELECT row_id FROM demo_rows WHERE tbl = ?', [$t]), 'row_id'))) ?: '0';

        // Tables with no foreign key to the thing that owns them go first, by their recorded ids.
        Db::run('DELETE FROM payments WHERE id IN (' . $ids('payments') . ')');
        Db::run('DELETE FROM listings WHERE id IN (' . $ids('listings') . ')');
        Db::run('DELETE FROM vendors  WHERE id IN (' . $ids('vendors') . ')');    // reviews follow, by cascade
        Db::run('DELETE FROM talks    WHERE id IN (' . $ids('talks') . ')');      // and so do replies
        $users = $ids('users');
        Db::run('DELETE FROM tokens WHERE user_id IN (' . $users . ')');
        Db::run('DELETE FROM users  WHERE id IN (' . $users . ')');               // weddings, guests, wall, sessions, payouts
        Db::run('DROP TABLE demo_rows');

        return $counts;
    }

    /** What a demo squad member does, a hobby and a relationship status, varied from one to the next. */
    public static function squadProfile(int $i): array
    {
        $work = ['Pharmacist', 'Software engineer', 'Architect', 'Banker', 'Doctor', 'Fashion designer', 'Lawyer', 'Teacher', 'Photographer',
                 'Product designer', 'Nurse', 'Chef', 'Civil engineer', 'Content creator', 'Accountant', 'Event planner', 'Data analyst'];
        $hobby = ['Afrobeats and long drives', 'Football on Saturdays', 'Baking and thrift shopping', 'Owambe dance floors', 'Reading and podcasts',
                  'Travel and street food', 'Gym and grilling', 'Photography', 'Board games and karaoke', 'Netball and poetry', 'Cooking jollof for everyone',
                  'Chess and sneakers', 'Gele tying, unofficially', 'Swimming and spa days'];
        $rel = ['single', 'taken', 'married', 'engaged', 'single', '', 'married', 'taken'];
        return ['work' => $work[($i * 7) % count($work)], 'hobby' => $hobby[($i * 5) % count($hobby)], 'relationship' => $rel[$i % count($rel)]];
    }

    /** Demo spraying for a Premium page: a handful of guests, some spraying more than once. */
    public static function sprays(int $wid, string $cur, string $key): void
    {
        $c = require App::root() . '/db/demo/content.php';
        $amounts = self::AMOUNTS[$cur] ?? self::AMOUNTS['NGN'];
        $people = [];
        for ($i = 0; $i < 6; $i++) {
            $people[] = $c['first'][mt_rand(0, count($c['first']) - 1)] . ' ' . $c['last'][mt_rand(0, count($c['last']) - 1)];
        }
        for ($k = mt_rand(8, 12); $k > 0; $k--) {
            $amount = $amounts[mt_rand(0, count($amounts) - 1)];
            $age = mt_rand(3600, 12 * 86400);
            $at = date('Y-m-d H:i:s', time() - $age);
            self::put('payments', [
                'ref' => 'DEMO-SPR-' . strtoupper(substr($key, 0, 3)) . '-' . bin2hex(random_bytes(3)), 'purpose' => 'spray',
                'wedding_id' => $wid, 'payer_name' => $people[mt_rand(0, count($people) - 1)], 'amount' => $amount,
                'currency' => $cur, 'gateway' => 'demo', 'gateway_ref' => 'demo-spray', 'status' => 'paid', 'paid_at' => $at, 'created_at' => $at,
            ]);
        }
    }
}
