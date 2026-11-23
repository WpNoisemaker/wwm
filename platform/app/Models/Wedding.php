<?php
declare(strict_types=1);

final class Wedding
{
    public static function find(int $id): ?array
    {
        return Db::one('SELECT * FROM weddings WHERE id=? LIMIT 1', [$id]);
    }

    public static function bySlug(string $slug): ?array
    {
        return Db::one('SELECT * FROM weddings WHERE slug=? LIMIT 1', [$slug]);
    }

    /** The one a signed-in person owns, or the soonest public page when nobody is signed in. */
    public static function forUser(?int $userId): ?array
    {
        if ($userId) {
            $w = Db::one('SELECT * FROM weddings WHERE owner_id=? ORDER BY id LIMIT 1', [$userId]);
            if ($w) { return $w; }
        }
        return Db::one("SELECT * FROM weddings WHERE visibility='public' ORDER BY wed_date IS NULL, wed_date LIMIT 1");
    }

    public static function names(array $w): string
    {
        $a = trim((string) $w['partner_a']);
        $b = trim((string) $w['partner_b']);
        return $a && $b ? "$a & $b" : ($a ?: ($b ?: 'Your wedding'));
    }

    /** Days until the wedding. Negative once it has passed, null when there is no date yet. */
    public static function daysTo(array $w): ?int
    {
        if (empty($w['wed_date'])) { return null; }
        $d = (new DateTimeImmutable('today'))->diff(new DateTimeImmutable((string) $w['wed_date']));
        return $d->invert ? -(int) $d->days : (int) $d->days;
    }

    /** What the big tile says: a countdown, "Today", or how long ago it was. */
    public static function countdown(array $w): array
    {
        $n = self::daysTo($w);
        if ($n === null)  { return ['—', 'No date set yet', false]; }
        if ($n === 0)     { return ['Today', 'The day is here', false]; }
        if ($n > 0)       { return [(string) $n, $n === 1 ? 'day to go' : 'days to go', true]; }
        $ago = abs($n);
        if ($ago < 31)    { return [(string) $ago, $ago === 1 ? 'day married' : 'days married', true]; }
        $m = (int) round($ago / 30.4);
        if ($m < 12)      { return [(string) $m, $m === 1 ? 'month married' : 'months married', true]; }
        $y = (int) round($ago / 365);
        return [(string) $y, $y === 1 ? 'year married' : 'years married', true];
    }

    /** Everything the home screen needs, in as few queries as it can be done. */
    public static function dash(int $id): array
    {
        $g = Db::one(
            "SELECT COUNT(*) total, SUM(coming='yes') cyes, SUM(coming='no') cno, SUM(party) heads
             FROM guests WHERE wedding_id=?",
            [$id]
        ) ?: [];
        $m = Db::one(
            "SELECT COALESCE(SUM(amount),0) raised,
                    COALESCE(SUM(CASE WHEN created_at >= NOW() - INTERVAL 7 DAY THEN amount END),0) week
             FROM payments WHERE wedding_id=? AND status IN ('paid','released')",
            [$id]
        ) ?: [];
        return [
            'guests'   => (int) ($g['total'] ?? 0),
            'coming'   => (int) ($g['cyes'] ?? 0),
            'declined' => (int) ($g['cno'] ?? 0),
            'heads'    => (int) ($g['heads'] ?? 0),
            'raised'   => (int) ($m['raised'] ?? 0),
            'week'     => (int) ($m['week'] ?? 0),
            'target'   => (int) Db::val('SELECT COALESCE(SUM(target),0) FROM gifts WHERE wedding_id=?', [$id], 0),
            'gifts'    => (int) Db::val('SELECT COUNT(*) FROM gifts WHERE wedding_id=?', [$id], 0),
            'programs' => (int) Db::val('SELECT COUNT(*) FROM programs WHERE wedding_id=?', [$id], 0),
            'squad'    => (int) Db::val('SELECT COUNT(*) FROM squad WHERE wedding_id=?', [$id], 0),
            'photos'   => (int) Db::val("SELECT COUNT(*) FROM wall WHERE wedding_id=? AND kind='photo' AND status='live'", [$id], 0),
            'notes'    => (int) Db::val("SELECT COUNT(*) FROM wall WHERE wedding_id=? AND kind='note' AND status='live'", [$id], 0),
        ];
    }

    /* =============================================================================== Directory
     * Everything /weddings needs. The filters are built as one WHERE so the count and the page
     * of rows can never disagree with each other.
     * ------------------------------------------------------------------------------------- */

    public const PER_PAGE = 12;

    public const SORTS = ['soon' => 'Coming soon', 'new' => 'Newest', 'buzz' => 'Most buzz'];

    /**
     * The six styles the homepage links to. There is no style column — a wedding is whatever its
     * programs say it is — so each style is a handful of words matched against what the page
     * actually holds. A style nobody is holding returns nothing, honestly.
     */
    public const STYLES = [
        'Traditional'      => ['traditional', 'engagement', 'introduction', 'igba nkwu', 'kamu', 'knocking', 'ruracio', 'lobola', 'owambe', 'asooke', 'aso-oke'],
        'White wedding'    => ['white wedding', 'church', 'reception', 'vows', 'blessing', 'first dance', 'cake'],
        'Nikkah'           => ['nikkah', 'nikah', 'walima', 'fatiha', 'sadaki'],
        'Court & registry' => ['registry', 'court', 'city hall', 'ikoyi', 'intimate lunch'],
        'Destination'      => ['destination', 'zanzibar', 'diani', 'cape town', 'marrakech', 'dubai'],
        'Diaspora'         => ['london', 'houston', 'toronto', 'dublin', 'atlanta', 'manchester', 'maryland'],
    ];

    /** Everything searchable about a wedding, programs included, as one SQL expression. */
    private static function blob(): string
    {
        return "CONCAT_WS(' ', w.partner_a, w.partner_b, w.hashtag, w.tagline, w.template, w.venue,"
             . " w.city, w.state, w.country, w.slug,"
             . " COALESCE((SELECT GROUP_CONCAT(CONCAT_WS(' ', p.title, p.kind, p.venue) SEPARATOR ' ')"
             . " FROM programs p WHERE p.wedding_id = w.id), ''))";
    }

    /** A LIKE needle with the wildcards the visitor typed treated as text. */
    private static function like(string $s): string
    {
        return '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $s) . '%';
    }

    /** The style whose name this is, matched loosely so '&' and 'and' both land. @return string */
    public static function styleKey(string $wanted): string
    {
        $want = strtolower(str_replace([' and ', '&'], [' & ', ' & '], trim($wanted)));
        $want = preg_replace('/\s+/', ' ', $want) ?? $want;
        foreach (array_keys(self::STYLES) as $name) {
            if (strtolower($name) === $want) {
                return $name;
            }
        }
        return '';
    }

    /**
     * The WHERE for a set of filters, and its arguments.
     *
     * @param array{q?:string,city?:string,style?:string} $f
     * @return array{0:string,1:array<int,mixed>}
     */
    private static function filter(array $f): array
    {
        // Private and unlisted pages are never listed. A private page is not ours to advertise.
        $where = ["w.visibility = 'public'"];
        $args  = [];

        $q = trim((string) ($f['q'] ?? ''));
        if ($q !== '') {
            $needle = self::like(ltrim($q, '#'));
            $where[] = '(' . self::blob() . " LIKE ? OR CONCAT_WS(' & ', w.partner_a, w.partner_b) LIKE ?)";
            $args[]  = $needle;
            $args[]  = $needle;
        }

        $city = trim((string) ($f['city'] ?? ''));
        if ($city !== '') {
            $where[] = 'w.city = ?';
            $args[]  = $city;
        }

        $country = trim((string) ($f['country'] ?? ''));
        if ($country !== '') {
            $where[] = 'w.country = ?';
            $args[]  = $country;
        }

        $plan = (string) ($f['plan'] ?? '');
        if (isset(self::PLANS[$plan])) {
            $where[] = 'w.plan = ?';
            $args[]  = $plan;
        }

        $style = self::styleKey((string) ($f['style'] ?? ''));
        if ($style !== '') {
            $bits = [];
            foreach (self::STYLES[$style] as $term) {
                $bits[] = self::blob() . ' LIKE ?';
                $args[] = '%' . $term . '%';
            }
            if ($style === 'Destination') {
                $bits[] = "w.country <> 'Nigeria'";
            }
            if ($style === 'Diaspora') {
                $bits[] = "w.country NOT IN ('Nigeria','Ghana','Kenya','South Africa','Tanzania','Uganda','Rwanda','Senegal','Egypt','Morocco')";
            }
            $where[] = '(' . implode(' OR ', $bits) . ')';
        }

        return [implode(' AND ', $where), $args];
    }

    /** Soonest first is the default: a directory is for finding a day you can still turn up to. */
    private static function order(string $sort): string
    {
        return match ($sort) {
            'new'  => 'w.created_at DESC, w.id DESC',
            'buzz' => 'w.buzz DESC, w.views DESC, w.id DESC',
            default => 'CASE WHEN w.wed_date >= CURDATE() THEN 0 WHEN w.wed_date IS NULL THEN 2 ELSE 1 END,'
                     . ' CASE WHEN w.wed_date >= CURDATE() THEN w.wed_date END ASC, w.wed_date DESC, w.id DESC',
        };
    }

    /**
     * One page of the directory.
     *
     * @param array{q?:string,city?:string,style?:string,sort?:string} $f
     * @return array<int,array<string,mixed>>
     */
    public static function search(array $f, int $limit = self::PER_PAGE, int $offset = 0): array
    {
        [$where, $args] = self::filter($f);
        $order  = self::order((string) ($f['sort'] ?? 'soon'));
        $args[] = max(1, $limit);
        $args[] = max(0, $offset);
        return Db::all(
            "SELECT w.*,
                    (SELECT COUNT(*) FROM guests g WHERE g.wedding_id = w.id AND g.coming = 'yes') guests,
                    (SELECT COUNT(*) FROM wall x WHERE x.wedding_id = w.id AND x.status = 'live') notes
             FROM weddings w WHERE $where ORDER BY $order LIMIT ? OFFSET ?",
            $args
        );
    }

    /** How many weddings match, so the page can say so and work out its last page. */
    public static function countSearch(array $f): int
    {
        [$where, $args] = self::filter($f);
        return (int) Db::val("SELECT COUNT(*) FROM weddings w WHERE $where", $args, 0);
    }

    /** @return array<int,array{country:string,n:int}> Countries with at least one public page. */
    public static function countryList(): array
    {
        return Db::all(
            "SELECT country, COUNT(*) n FROM weddings
             WHERE visibility = 'public' AND country <> '' GROUP BY country ORDER BY n DESC, country"
        );
    }

    /** @return array<string,int> How many public pages each plan has, in plan order. */
    public static function planCounts(string $country = ''): array
    {
        $out = array_fill_keys(array_keys(self::PLANS), 0);
        $in  = $country !== '' ? ' AND country = ?' : '';
        foreach (Db::all("SELECT plan, COUNT(*) n FROM weddings WHERE visibility = 'public'$in GROUP BY plan", $country !== '' ? [$country] : []) as $r) {
            $out[(string) $r['plan']] = (int) $r['n'];
        }
        return $out;
    }

    /** @return array<int,array{city:string,n:int}> Every city with a public page, busiest first. */
    public static function cityList(int $n = 24, string $country = ''): array
    {
        $in = $country !== '' ? ' AND country = ?' : '';
        return Db::all(
            "SELECT city, COUNT(*) n FROM weddings
             WHERE visibility = 'public' AND city <> ''$in GROUP BY city ORDER BY n DESC, city LIMIT ?",
            $country !== '' ? [$country, $n] : [$n]
        );
    }

    /**
     * Each country has its own listing page, /weddings/uk and so on: [country, "in ..." phrase, line].
     * Kept in the order the countries are shown.
     */
    public const PLACES = [
        'nigeria'      => ['Nigeria', 'Nigeria', 'Owambe, igba nkwu, nikkah and white weddings in Lagos, Abuja, Port Harcourt and every hometown in between.'],
        'uk'           => ['United Kingdom', 'the UK', 'Nigerian, Ghanaian and Kenyan couples marrying in London, Manchester, Birmingham and beyond, with half the family flying in.'],
        'usa'          => ['United States', 'the US', 'Houston, Atlanta, Maryland and New York: owambe, knocking and ruracio, American-sized.'],
        'canada'       => ['Canada', 'Canada', 'Toronto, Calgary and Montreal, with every aunty in a puffer over her george.'],
        'australia'    => ['Australia', 'Australia', 'Melbourne, Sydney and Perth: kente on the beach and jollof at the reception.'],
        'new-zealand'  => ['New Zealand', 'New Zealand', 'Auckland and Wellington, with the family at home watching the livestream at dawn.'],
        'dubai'        => ['United Arab Emirates', 'Dubai and the UAE', 'Nikkahs, walimahs and white weddings in Dubai and Abu Dhabi, where both families can fly in.'],
        'kenya'        => ['Kenya', 'Kenya', 'Ruracio, church and garden receptions in Nairobi, Mombasa and beyond.'],
        'ghana'        => ['Ghana', 'Ghana', 'Knockings, engagements and white weddings in Accra, Kumasi and Cape Coast.'],
        'rwanda'       => ['Rwanda', 'Rwanda', 'Gusaba, gutwikurura and weddings in Kigali and the thousand hills.'],
        'south-africa' => ['South Africa', 'South Africa', 'Lobola, white weddings and owambe in Johannesburg, Pretoria and Cape Town.'],
    ];

    /** The listing page for a country, or '' when it has none. */
    public static function placeSlug(string $country): string
    {
        foreach (self::PLACES as $slug => $p) {
            if ($p[0] === $country) {
                return $slug;
            }
        }
        return '';
    }

    /** How many public pages are coming up in the next $days days, for a country or everywhere. */
    public static function soonCount(string $country = '', int $days = 90): int
    {
        $in = $country !== '' ? ' AND country = ?' : '';
        $args = $country !== '' ? [$country, $days] : [$days];
        return (int) Db::val(
            "SELECT COUNT(*) FROM weddings WHERE visibility = 'public'$in AND wed_date >= CURDATE() AND wed_date <= CURDATE() + INTERVAL ? DAY",
            $args,
            0
        );
    }

    /* ================================================================================== Plans
     * What a visitor can see depends on what the couple is paying for. One ladder, read in one
     * place, so the page and the upsell can never drift apart.
     * ------------------------------------------------------------------------------------- */

    public const PLANS = ['free' => 0, 'pro' => 1, 'premium' => 2, 'society' => 3];

    /** Where African couples are getting married, and the money their guests give in. */
    public const COUNTRIES = ['Nigeria', 'Ghana', 'Kenya', 'Rwanda', 'South Africa', 'United Kingdom', 'United States', 'Canada',
                              'Australia', 'New Zealand', 'United Arab Emirates'];
    public const CURRENCY = ['Nigeria' => 'NGN', 'Ghana' => 'GHS', 'Kenya' => 'KES', 'Rwanda' => 'RWF', 'South Africa' => 'ZAR',
                             'United Kingdom' => 'GBP', 'United States' => 'USD', 'Canada' => 'CAD', 'Australia' => 'AUD',
                             'New Zealand' => 'NZD', 'United Arab Emirates' => 'AED'];

    /** The lowest plan that unlocks each part of the page. */
    private const NEEDS = [
        'squad'    => 'pro',
        'gallery'  => 'pro',
        'colours'  => 'pro',
        'badge'    => 'pro',
        'quiet'    => 'pro',      // no sponsored slots
        'cover'    => 'premium',  // the full-bleed mesh treatment
        'spray'    => 'premium',
        'live'     => 'premium',
        'grand'    => 'premium',  // the larger type scale and wider layout
        'monogram' => 'society',
        'gold'     => 'society',
        'layout'   => 'pro',      // moving and hiding the sections of the page
    ];

    public static function rank(string $plan): int
    {
        return self::PLANS[$plan] ?? 0;
    }

    /** True when this wedding's plan reaches the part of the page named. */
    public static function has(array $w, string $feature): bool
    {
        $needs = self::NEEDS[$feature] ?? 'free';
        return self::rank((string) ($w['plan'] ?? 'free')) >= self::rank($needs);
    }

    /** What is still locked, in the couple's own words, for the honest note at the foot. */
    public static function locked(array $w): array
    {
        $rank = self::rank((string) ($w['plan'] ?? 'free'));
        $all  = [
            'pro'      => ['The squad', 'Guest photo gallery', 'Their own colours', 'Verified badge, no ads'],
            'premium'  => ['A full-bleed cover', 'Spraying from the seat', 'Livestream on the page'],
            'society'  => ['A monogram made for them', 'Gold throughout'],
        ];
        $out = [];
        foreach ($all as $plan => $items) {
            if (self::rank($plan) > $rank) {
                $out[$plan] = $items;
            }
        }
        return $out;
    }

    /** A colour the couple saved, or '' when it is missing or not a colour at all. */
    private static function hex(mixed $v): string
    {
        $v = trim((string) $v);
        return preg_match('/^#(?:[0-9a-f]{3}|[0-9a-f]{6})$/i', $v) ? $v : '';
    }

    /**
     * The two colours a page is drawn in. Yellow is the house, and it leads every page; a Pro
     * page and up may bring the couple's own pair in as the accent beside it.
     *
     * @return array{p:string,a:string,own:bool}
     */
    public static function palette(array $w): array
    {
        $house = ['p' => '#F5B800', 'a' => '#FFD426', 'own' => false];
        if (!self::has($w, 'colours')) {
            return $house;
        }
        $d = json_decode((string) ($w['design'] ?? ''), true);
        $d = is_array($d) ? $d : [];
        $p = self::hex($d['primary'] ?? '');
        $a = self::hex($d['accent'] ?? '');
        if ($p === '' && $a === '') {
            return $house;
        }
        return ['p' => $p ?: $house['p'], 'a' => $a ?: $house['a'], 'own' => true];
    }

    /** The design JSON, always an array. */
    public static function design(array $w): array
    {
        $d = json_decode((string) ($w['design'] ?? ''), true);
        return is_array($d) ? $d : [];
    }

    /** "T&B" — the two initials, for the High Society monogram. */
    public static function monogram(array $w): string
    {
        $a = mb_substr(trim((string) $w['partner_a']), 0, 1);
        $b = mb_substr(trim((string) $w['partner_b']), 0, 1);
        return mb_strtoupper($a . $b);
    }

    /* ============================================================================ Page content */

    /** @return array<int,array<string,mixed>> The programs a guest is allowed to see. */
    public static function programs(int $id): array
    {
        return Db::all(
            'SELECT * FROM programs WHERE wedding_id = ? AND is_private = 0
             ORDER BY starts_at IS NULL, starts_at, sort, id',
            [$id]
        );
    }

    /** @return array<int,array<string,mixed>> */
    public static function squad(int $id): array
    {
        return Db::all(
            "SELECT * FROM squad WHERE wedding_id = ? AND status <> 'hidden' ORDER BY sort, id",
            [$id]
        );
    }

    /** @return array<int,array<string,mixed>> */
    public static function gifts(int $id): array
    {
        return Db::all('SELECT * FROM gifts WHERE wedding_id = ? ORDER BY sort, id', [$id]);
    }

    /** @return array<int,array<string,mixed>> Love notes, newest first. */
    public static function notes(int $id, int $n = 30): array
    {
        return Db::all(
            "SELECT * FROM wall WHERE wedding_id = ? AND kind = 'note' AND status = 'live'
             ORDER BY created_at DESC, id DESC LIMIT ?",
            [$id, $n]
        );
    }

    /** @return array<int,array<string,mixed>> Guest photos, newest first. */
    public static function photos(int $id, int $n = 18): array
    {
        return Db::all(
            "SELECT * FROM wall WHERE wedding_id = ? AND kind = 'photo' AND status = 'live'
             AND image <> '' ORDER BY created_at DESC, id DESC LIMIT ?",
            [$id, $n]
        );
    }

    /** @return array{guests:int,heads:int,gifts:int,raised:int,target:int,notes:int,photos:int} */
    public static function tally(int $id): array
    {
        $g = Db::one(
            "SELECT COUNT(*) n, COALESCE(SUM(party), 0) heads FROM guests
             WHERE wedding_id = ? AND coming = 'yes'",
            [$id]
        ) ?: [];
        $m = Db::one('SELECT COUNT(*) n, COALESCE(SUM(raised),0) raised, COALESCE(SUM(target),0) target
                      FROM gifts WHERE wedding_id = ?', [$id]) ?: [];
        return [
            'guests' => (int) ($g['n'] ?? 0),
            'heads'  => (int) ($g['heads'] ?? 0),
            'gifts'  => (int) ($m['n'] ?? 0),
            'raised' => (int) ($m['raised'] ?? 0),
            'target' => (int) ($m['target'] ?? 0),
            'notes'  => (int) Db::val("SELECT COUNT(*) FROM wall WHERE wedding_id=? AND kind='note' AND status='live'", [$id], 0),
            'photos' => (int) Db::val("SELECT COUNT(*) FROM wall WHERE wedding_id=? AND kind='photo' AND status='live'", [$id], 0),
        ];
    }

    /**
     * The next thing that happens: the soonest program still ahead, or the wedding day itself.
     * Null once everything has been and gone.
     *
     * @param array<int,array<string,mixed>> $programs
     * @return array{when:string,label:string,venue:string}|null
     */
    public static function nextMoment(array $w, array $programs): ?array
    {
        $now  = time();
        $best = null;
        foreach ($programs as $p) {
            if (empty($p['starts_at'])) {
                continue;
            }
            $t = strtotime((string) $p['starts_at']);
            if ($t !== false && $t >= $now && ($best === null || $t < $best[0])) {
                $best = [$t, ['when' => (string) $p['starts_at'], 'label' => (string) $p['title'], 'venue' => (string) $p['venue']]];
            }
        }
        if ($best !== null) {
            return $best[1];
        }
        if (!empty($w['wed_date'])) {
            $when = (string) $w['wed_date'] . ' ' . (!empty($w['wed_time']) ? (string) $w['wed_time'] : '10:00:00');
            $t    = strtotime($when);
            if ($t !== false && $t >= $now) {
                return ['when' => $when, 'label' => 'The wedding', 'venue' => (string) $w['venue']];
            }
        }
        return null;
    }

    /**
     * Days, hours and minutes between now and a moment, so the countdown is right before a line
     * of JavaScript runs — and still right for anyone who has it switched off.
     *
     * @return array{d:int,h:int,m:int,s:int}
     */
    public static function until(string $when): array
    {
        $left = max(0, (int) strtotime($when) - time());
        return [
            'd' => intdiv($left, 86400),
            'h' => intdiv($left % 86400, 3600),
            'm' => intdiv($left % 3600, 60),
            's' => $left % 60,
        ];
    }

    /** A date a guest can read, with the time only when the couple has set one. */
    public static function when(array $w): string
    {
        if (empty($w['wed_date'])) {
            return 'Date to be announced';
        }
        $out = date('l, j F Y', (int) strtotime((string) $w['wed_date']));
        if (!empty($w['wed_time'])) {
            $out .= ' · ' . date('g:ia', (int) strtotime((string) $w['wed_date'] . ' ' . (string) $w['wed_time']));
        }
        return $out;
    }

    /** "12 Dec" or "12 Dec 2027" when it is not this year: short enough for a card. */
    public static function short(array $w): string
    {
        if (empty($w['wed_date'])) {
            return 'Date to come';
        }
        $t = (int) strtotime((string) $w['wed_date']);
        return date((date('Y', $t) === date('Y') ? 'j M' : 'j M Y'), $t);
    }

    /**
     * The parts of a wedding page below the invite, in their default order: the colour code sits
     * right under the invite, then the countdown, the story and the rest. key => [name, what it
     * is, the plan that shows it on the page].
     */
    public const SECTIONS = [
        'palette'   => ['Colours of the day', 'The colours guests should wear', 'pro'],
        'countdown' => ['Countdown', 'Days, hours and minutes to the next event', 'free'],
        'story'     => ['Your story', 'How it started, or your diary', 'free'],
        'when'      => ['Programmes', 'Every event, its time, place and dress code', 'free'],
        'squad'     => ['The squad', 'Bridesmaids, groomsmen and the people holding it together', 'pro'],
        'gifts'     => ['Gifts & spraying', 'Your registry, cash gifts and spraying', 'free'],
        'live'      => ['Livestream', 'Watch live, for family who cannot travel', 'premium'],
        'photos'    => ['Guest photos', 'Every photo guests add', 'pro'],
        'quiz'      => ['Who knows them best?', 'A quiz about each of you, with leaderboards', 'pro'],
        'vendors'   => ['Our vendors', 'The vendors you booked', 'free'],
        'wall'      => ['Wall of love', 'Wishes and notes from guests', 'free'],
    ];

    /**
     * The order the couple chose, and what they hid. Free pages always use the default order with
     * nothing hidden: moving and hiding sections comes with Pro.
     *
     * @return array{order: list<string>, hidden: list<string>}
     */
    public static function sections(array $w): array
    {
        $all = array_keys(self::SECTIONS);
        if (!self::has($w, 'layout')) {
            return ['order' => $all, 'hidden' => []];
        }
        $saved = self::design($w)['sections'] ?? [];
        $order = array_values(array_unique(array_filter((array) ($saved['order'] ?? []), static fn ($k) => isset(self::SECTIONS[$k]))));
        foreach ($all as $i => $k) {            // a section added later slots in at its default place
            if (!in_array($k, $order, true)) {
                array_splice($order, min($i, count($order)), 0, [$k]);
            }
        }
        $hidden = array_values(array_intersect((array) ($saved['hidden'] ?? []), $all));
        return ['order' => $order, 'hidden' => $hidden];
    }

    /** The vendors the couple booked: the ones this couple has reviewed. */
    public static function vendors(array $w): array
    {
        return Db::all(
            "SELECT v.*, MAX(r.stars) stars, MAX(r.body) said FROM vendors v JOIN reviews r ON r.vendor_id = v.id
             WHERE r.status = 'live' AND r.name = ? GROUP BY v.id ORDER BY v.verified DESC, v.name",
            [self::names($w)]
        );
    }

    /** "Lagos" at home, "London, United Kingdom" abroad: the city, and the country when it is not Nigeria. */
    public static function place(array $w): string
    {
        $city = trim((string) ($w['city'] ?? ''));
        $country = trim((string) ($w['country'] ?? ''));
        if ($country === '' || $country === 'Nigeria') {
            return $city;
        }
        return $city !== '' ? $city . ', ' . $country : $country;
    }

    /** The date as dot numerals: "24.12.26". */
    public static function dotDate(array $w): string
    {
        return empty($w['wed_date']) ? '' : date('d.m.y', (int) strtotime((string) $w['wed_date']));
    }

    /** Where it is happening, as one line, skipping the parts that are blank. */
    public static function where(array $w): string
    {
        $bits = array_filter([trim((string) $w['venue']), trim((string) $w['city']), trim((string) $w['state'])]);
        $bits = array_values(array_unique($bits));
        return $bits ? implode(' · ', $bits) : '';
    }

    /* ============================================================================ Private pages */

    /** The cookie a visitor carries once they have typed a page's pass code correctly. */
    public static function passCookie(array $w): string
    {
        return 'wpass' . (int) $w['id'];
    }

    /** A one-way stamp of the pass code, so the cookie never carries the code itself. */
    public static function passStamp(array $w): string
    {
        return hash_hmac('sha256', (string) $w['pass_code'], (string) App::config('key', 'wwm'));
    }

    /** True when this visitor may see a private page: they have typed the code before. */
    public static function unlocked(array $w): bool
    {
        if ((string) $w['pass_code'] === '') {
            return false;
        }
        $got = (string) ($_COOKIE[self::passCookie($w)] ?? '');
        return $got !== '' && hash_equals(self::passStamp($w), $got);
    }

    /** The sponsored slots a free page carries. Real rows, never invented. */
    public static function sponsors(int $n = 2): array
    {
        return Db::all(
            "SELECT name, category, city, logo, slug, 'vendor' AS kind FROM vendors
             WHERE verified = 1 ORDER BY rating DESC, reviews DESC LIMIT ?",
            [$n]
        );
    }
}
