<?php
declare(strict_types=1);

/**
 * The site's own ads. HQ → Ads writes each ad (headline, text, button, link, picture) and picks the
 * slots it shows in; each slot can be switched off. A slot that is off, or has no ad, prints
 * nothing at all, so the page closes up around it. Pro and up never carry ads, and someone whose
 * own page is already Premium is not shown them.
 */
final class Promo
{
    /** Where ads can show: key => [name for HQ, shape]. */
    public const SLOTS = [
        'wedding'  => ['Free wedding pages: a card after the third section', 'band'],
        'sponsors' => ['Free wedding pages: sponsored vendors at the bottom', 'rows'],
        'deck'     => ['Anonymous Community: a card between posts', 'post'],
        'home'     => ['Front page: a card in the Anonymous Community cards', 'post'],
        'cards'    => ['Wedding pages list and country pages: a card among the weddings', 'card'],
        'blog'     => ['Blog: a card among the posts and under each post', 'story'],
    ];

    private const START = [
        ['Planning your own wedding?', 'Premium gives you ten designs, background music, a livestream and spraying from the seat.'],
        ['Make your page Premium', 'Choose from ten designs, set your own colours, play your song and collect voice notes from guests. Our team can set it all up for you.'],
        ['Stream your wedding live', 'Premium streams the ceremony to family abroad and lets them spray you from wherever they are.'],
        ['Like this page?', 'Yours can do more. Premium adds ten designs, background music and a livestream, and takes away the ads.'],
    ];

    public static function table(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        Db::run("CREATE TABLE IF NOT EXISTS ads (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT, title VARCHAR(160) NOT NULL DEFAULT '', line VARCHAR(300) NOT NULL DEFAULT '',
            button VARCHAR(60) NOT NULL DEFAULT 'See Premium', link VARCHAR(500) NOT NULL DEFAULT '', image VARCHAR(255) NOT NULL DEFAULT '',
            show_price TINYINT(1) NOT NULL DEFAULT 1, slots VARCHAR(160) NOT NULL DEFAULT '', active TINYINT(1) NOT NULL DEFAULT 1,
            sort SMALLINT NOT NULL DEFAULT 0, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        if (Site::setting('ads_seeded', '') === '') {
            foreach (self::START as $i => [$t, $l]) {
                Db::insert('ads', ['title' => $t, 'line' => $l, 'button' => 'See Premium', 'link' => '/pricing#premium', 'slots' => implode(',', array_keys(self::SLOTS)), 'sort' => $i]);
            }
            Db::run("INSERT INTO settings (k, v) VALUES ('ads_seeded', '1') ON DUPLICATE KEY UPDATE v = '1'");
        }
    }

    /** Ads are on at all (HQ's master switch), and this visitor should see them. */
    public static function on(): bool
    {
        static $on = null;
        if ($on !== null) {
            return $on;
        }
        if (Site::setting('ads_premium', '1') === '0') {
            return $on = false;
        }
        if (Auth::check() && Db::val("SELECT 1 FROM weddings WHERE owner_id = ? AND plan IN ('premium','society') LIMIT 1", [Auth::id()])) {
            return $on = false;
        }
        return $on = true;
    }

    /** @return array<string,bool> which slots are switched on */
    public static function slots(): array
    {
        $set = json_decode(Site::setting('ad_slots', ''), true);
        $out = [];
        foreach (array_keys(self::SLOTS) as $k) {
            $out[$k] = !is_array($set) || !array_key_exists($k, $set) || (bool) $set[$k];
        }
        return $out;
    }

    public static function slotOn(string $slot): bool
    {
        return self::on() && (self::slots()[$slot] ?? false);
    }

    /** @return array<int,array<string,mixed>> live ads for a slot */
    public static function ads(string $slot): array
    {
        static $all = null;
        if ($all === null) {
            try {
                self::table();
                $all = Db::all('SELECT * FROM ads WHERE active = 1 ORDER BY sort, id');
            } catch (Throwable $e) {
                $all = [];
            }
        }
        return array_values(array_filter($all, static fn ($a) => in_array($slot, explode(',', (string) $a['slots']), true)));
    }

    /** The ad for a slot, the nth one there (they take turns), or '' when there is nothing to show. */
    public static function html(string $slot, int $n = 0): string
    {
        if (!isset(self::SLOTS[$slot]) || !self::slotOn($slot)) {
            return '';
        }
        $ads = self::ads($slot);
        if (!$ads) {
            return '';
        }
        $a = $ads[$n % count($ads)];
        $link = trim((string) $a['link']) !== '' ? (string) $a['link'] : '/pricing#premium';
        return View::partial('partials/promo', [
            'as'     => self::SLOTS[$slot][1],
            'id'     => (int) $a['id'],
            'title'  => (string) $a['title'],
            'line'   => (string) $a['line'],
            'button' => trim((string) $a['button']) !== '' ? (string) $a['button'] : 'See Premium',
            'price'  => (int) $a['show_price'] ? Fx::show((int) Site::setting('premium_price', '448000'), Fx::pick()) : '',
            'img'    => (string) $a['image'] !== '' ? (string) $a['image'] : self::picture($n),
            'href'   => str_starts_with($link, '/') ? App::url($link) : $link,
        ]);
    }

    /** A real Premium page's photo, for an ad with no picture of its own. */
    private static function picture(int $n): string
    {
        static $pics = null;
        if ($pics === null) {
            $pics = array_column(Db::all("SELECT cover FROM weddings WHERE plan IN ('premium','society') AND visibility = 'public' AND cover <> '' ORDER BY buzz DESC LIMIT 6"), 'cover');
        }
        return $pics ? (string) $pics[$n % count($pics)] : '';
    }
}
