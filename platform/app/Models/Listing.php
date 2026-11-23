<?php
declare(strict_types=1);

/** Stays, rides, honeymoons and aso-ebi. */
final class Listing
{
    /** Who sells it: their business name, profile address and whether HQ has verified them. */
    private const SELLER = "(SELECT v.name FROM vendors v WHERE v.user_id = l.partner_id ORDER BY v.verified DESC, v.id LIMIT 1) seller,
        (SELECT v.slug FROM vendors v WHERE v.user_id = l.partner_id ORDER BY v.verified DESC, v.id LIMIT 1) seller_slug,
        (SELECT MAX(v.verified) FROM vendors v WHERE v.user_id = l.partner_id) seller_ok,
        (SELECT v.logo FROM vendors v WHERE v.user_id = l.partner_id ORDER BY v.verified DESC, v.id LIMIT 1) seller_logo,
        (SELECT v.city FROM vendors v WHERE v.user_id = l.partner_id ORDER BY v.verified DESC, v.id LIMIT 1) seller_city";

    /** A listing shows only while its seller is in good standing (not paused or removed after reports). */
    private const SELLER_OK = "NOT EXISTS (SELECT 1 FROM vendors sv WHERE sv.user_id = l.partner_id AND sv.status <> 'active')";

    public static function bySlug(string $slug): ?array
    {
        return Db::one("SELECT l.*, " . self::SELLER . " FROM listings l WHERE l.slug = ? AND l.status = 'live' AND " . self::SELLER_OK . " LIMIT 1", [$slug]);
    }

    /** Everything one seller has live, for their profile. */
    public static function bySeller(int $userId, int $n = 12): array
    {
        return Db::all("SELECT l.*, " . self::SELLER . " FROM listings l WHERE l.partner_id = ? AND l.status = 'live' AND " . self::SELLER_OK . " ORDER BY l.id LIMIT ?", [$userId, $n]);
    }

    private static function filter(array $f): array
    {
        $w = ["l.status = 'live'", self::SELLER_OK];
        $a = [];
        if (trim((string) ($f['q'] ?? '')) !== '') {
            $w[] = "CONCAT_WS(' ', l.title, l.kind, l.city, l.about, (SELECT GROUP_CONCAT(v.name) FROM vendors v WHERE v.user_id = l.partner_id)) LIKE ?";
            $a[] = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], trim((string) $f['q'])) . '%';
        }
        if (trim((string) ($f['kind'] ?? '')) !== '') { $w[] = 'l.kind = ?'; $a[] = trim((string) $f['kind']); }
        if (trim((string) ($f['city'] ?? '')) !== '') { $w[] = 'l.city = ?'; $a[] = trim((string) $f['city']); }
        return [implode(' AND ', $w), $a];
    }

    public static function search(array $f, int $limit = 18, int $offset = 0): array
    {
        [$where, $a] = self::filter($f);
        $a[] = $limit;
        $a[] = $offset;
        return Db::all("SELECT l.*, " . self::SELLER . " FROM listings l WHERE $where ORDER BY l.id LIMIT ? OFFSET ?", $a);
    }

    public static function count(array $f): int
    {
        [$where, $a] = self::filter($f);
        return (int) Db::val("SELECT COUNT(*) FROM listings l WHERE $where", $a, 0);
    }

    public static function kinds(): array
    {
        return Db::all("SELECT l.kind, COUNT(*) n FROM listings l WHERE l.status = 'live' AND l.kind <> '' AND " . self::SELLER_OK . " GROUP BY l.kind ORDER BY n DESC");
    }

    public static function cities(): array
    {
        return Db::all("SELECT l.city, COUNT(*) n FROM listings l WHERE l.status = 'live' AND l.city <> '' AND " . self::SELLER_OK . " GROUP BY l.city ORDER BY n DESC LIMIT 14");
    }

    /** A plain-English name for a kind, so the page never shows a database value. */
    public static function kindName(string $k): string
    {
        return ['stay' => 'Shortlet', 'ride' => 'Ride', 'honeymoon' => 'Honeymoon', 'service' => 'Service', 'asoebi' => 'Aso-ebi', 'home' => 'Home organising', 'rental' => 'Rental'][$k] ?? ucfirst(str_replace('-', ' ', $k));
    }

    /** All of a listing's photos, in order. */
    public static function photos(array $l): array
    {
        $p = json_decode((string) ($l['photos'] ?? ''), true);
        return is_array($p) ? array_values(array_filter($p, static fn ($x) => is_string($x) && $x !== '')) : [];
    }

    /** A free address for a new row: "black-range-rover", then "black-range-rover-2" and so on. */
    public static function slugFor(string $table, string $text): string
    {
        $base = strtolower(trim((string) preg_replace('/[^A-Za-z0-9]+/', '-', (string) (iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text)), '-'));
        $base = $base !== '' ? substr($base, 0, 120) : 'listing';
        $slug = $base;
        for ($n = 2; Db::val("SELECT 1 FROM $table WHERE slug = ?", [$slug]); $n++) {
            $slug = $base . '-' . $n;
        }
        return $slug;
    }

    public const KINDS = ['stay' => 'Stay', 'ride' => 'Ride', 'honeymoon' => 'Honeymoon', 'service' => 'Service', 'asoebi' => 'Aso-ebi', 'rental' => 'Rental', 'other' => 'Other'];
}
