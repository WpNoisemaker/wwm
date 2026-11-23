<?php
declare(strict_types=1);

/** The people who make the day. */
final class Vendor
{
    public static function bySlug(string $slug): ?array
    {
        return Db::one('SELECT * FROM vendors WHERE slug = ? LIMIT 1', [$slug]);
    }

    /** @return array{0:string,1:array} A WHERE clause and its arguments. */
    private static function filter(array $f): array
    {
        $w = ["status = 'active'"];
        $a = [];
        if (trim((string) ($f['q'] ?? '')) !== '') {
            $w[] = "CONCAT_WS(' ', name, category, city, about) LIKE ?";
            $a[] = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], trim((string) $f['q'])) . '%';
        }
        if (trim((string) ($f['category'] ?? '')) !== '') { $w[] = 'category = ?'; $a[] = trim((string) $f['category']); }
        if (trim((string) ($f['city'] ?? '')) !== '')     { $w[] = 'city = ?';     $a[] = trim((string) $f['city']); }
        return [implode(' AND ', $w), $a];
    }

    public static function search(array $f, int $limit = 18, int $offset = 0): array
    {
        [$where, $a] = self::filter($f);
        $a[] = $limit;
        $a[] = $offset;
        return Db::all("SELECT * FROM vendors WHERE $where ORDER BY verified DESC, rating DESC, reviews DESC, id LIMIT ? OFFSET ?", $a);
    }

    public static function count(array $f): int
    {
        [$where, $a] = self::filter($f);
        return (int) Db::val("SELECT COUNT(*) FROM vendors WHERE $where", $a, 0);
    }

    public static function categories(): array
    {
        return Db::all("SELECT category, COUNT(*) n FROM vendors WHERE category <> '' AND status = 'active' GROUP BY category ORDER BY n DESC, category");
    }

    public static function cities(): array
    {
        return Db::all("SELECT city, COUNT(*) n FROM vendors WHERE city <> '' AND status = 'active' GROUP BY city ORDER BY n DESC, city LIMIT 14");
    }

    /** Reviews, newest first. The reviewer's own name is on the row. */
    public static function reviews(int $id, int $n = 12): array
    {
        return Db::all(
            "SELECT * FROM reviews WHERE vendor_id = ? AND status = 'live' ORDER BY created_at DESC LIMIT ?",
            [$id, $n]
        );
    }
}
