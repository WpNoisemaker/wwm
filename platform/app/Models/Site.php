<?php
declare(strict_types=1);

/**
 * Everything a visitor's homepage shows. One place, so all four looks render the same data and
 * only the dressing differs.
 */
final class Site
{
    public static function home(): array
    {
        return [
            'chart'    => self::chart(6),
            'fresh'    => self::fresh(6),
            'cities'   => self::cities(8),
            'vendors'  => self::vendors(6),
            'listings' => self::listings(6),
            'talks'    => Talk::feed('', 'top', 3),
            'countries' => Wedding::countryList(),
            'world'    => self::world(5),
            'wall'     => self::wall(7),
            'posts'    => self::posts(3),
            'stats'    => self::stats(),
        ];
    }

    /** @return array<int,array<string,mixed>> The weddings everyone is talking about. */
    public static function chart(int $n = 6): array
    {
        return Db::all(
            "SELECT w.*, (SELECT COUNT(*) FROM guests g WHERE g.wedding_id=w.id) guests
             FROM weddings w WHERE w.visibility='public'
             ORDER BY w.buzz DESC, w.views DESC LIMIT ?",
            [$n]
        );
    }

    /** @return array<int,array<string,mixed>> The most-talked-about page in each country, busiest first. */
    public static function world(int $n = 5): array
    {
        $out = [];
        foreach (Db::all("SELECT * FROM weddings WHERE visibility='public' ORDER BY buzz DESC") as $w) {
            $c = (string) $w['country'];
            if (!isset($out[$c])) { $out[$c] = $w; }
            if (count($out) >= $n) { break; }
        }
        return array_values($out);
    }

    /** @return array<int,array<string,mixed>> Coming up soonest. */
    public static function fresh(int $n = 6): array
    {
        return Db::all(
            "SELECT * FROM weddings
             WHERE visibility='public' AND wed_date IS NOT NULL AND wed_date >= CURDATE()
             ORDER BY wed_date LIMIT ?",
            [$n]
        );
    }

    /** @return array<int,array{city:string,n:int}> */
    public static function cities(int $n = 8): array
    {
        return Db::all(
            "SELECT city, COUNT(*) n FROM weddings
             WHERE visibility='public' AND city<>'' GROUP BY city ORDER BY n DESC, city LIMIT ?",
            [$n]
        );
    }

    public static function vendors(int $n = 6): array
    {
        return Db::all('SELECT * FROM vendors ORDER BY verified DESC, rating DESC, reviews DESC LIMIT ?', [$n]);
    }

    public static function listings(int $n = 6): array
    {
        return Db::all("SELECT * FROM listings WHERE status='live' ORDER BY id DESC LIMIT ?", [$n]);
    }

    public static function stats(): array
    {
        return [
            'weddings' => (int) Db::val("SELECT COUNT(*) FROM weddings WHERE visibility='public'", [], 0),
            'guests'   => (int) Db::val('SELECT COUNT(*) FROM guests', [], 0),
            'vendors'  => (int) Db::val('SELECT COUNT(*) FROM vendors', [], 0),
            'cities'   => (int) Db::val("SELECT COUNT(DISTINCT city) FROM weddings WHERE city<>''", [], 0),
            'countries' => (int) Db::val("SELECT COUNT(DISTINCT country) FROM weddings WHERE country<>'' AND visibility='public'", [], 0),
            'raised'   => (int) Db::val("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status IN ('paid','released')", [], 0),
        ];
    }

    /** The tier a page sits on, as a word a visitor understands. */
    public static function tier(string $plan): string
    {
        return ['free' => 'Free', 'pro' => 'Pro', 'premium' => 'Premium', 'society' => 'High Society'][$plan] ?? 'Free';
    }

    /** @return array<int,array<string,mixed>> The latest wedding tips and how-tos. */
    public static function posts(int $n = 3): array
    {
        return Db::all(
            "SELECT * FROM posts WHERE status='live' ORDER BY published_at DESC, id DESC LIMIT ?",
            [$n]
        );
    }

    /**
     * Love notes and photos guests have left, newest first. The wall is the clearest proof that
     * these are real pages real guests used, so the homepage shows it rather than describing it.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function wall(int $n = 8): array
    {
        return Db::all(
            "SELECT wa.*, w.slug, w.partner_a, w.partner_b
             FROM wall wa JOIN weddings w ON w.id = wa.wedding_id
             WHERE wa.status='live' AND w.visibility='public'
             ORDER BY wa.created_at DESC LIMIT ?",
            [$n]
        );
    }

    /** A saved setting, with a fallback for a fresh install that has not been configured yet. */
    public static function setting(string $key, string $fallback = ''): string
    {
        $v = Db::val('SELECT v FROM settings WHERE k = ?', [$key]);
        return $v === null || $v === '' ? $fallback : (string) $v;
    }
}
