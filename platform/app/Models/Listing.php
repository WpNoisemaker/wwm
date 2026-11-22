<?php
declare(strict_types=1);

/** Stays, rides, honeymoons and aso-ebi. */
final class Listing
{
    public static function bySlug(string $slug): ?array
    {
        return Db::one("SELECT * FROM listings WHERE slug = ? AND status = 'live' LIMIT 1", [$slug]);
    }

    private static function filter(array $f): array
    {
        $w = ["status = 'live'"];
        $a = [];
        if (trim((string) ($f['q'] ?? '')) !== '') {
            $w[] = "CONCAT_WS(' ', title, kind, city, about) LIKE ?";
            $a[] = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], trim((string) $f['q'])) . '%';
        }
        if (trim((string) ($f['kind'] ?? '')) !== '') { $w[] = 'kind = ?'; $a[] = trim((string) $f['kind']); }
        if (trim((string) ($f['city'] ?? '')) !== '') { $w[] = 'city = ?'; $a[] = trim((string) $f['city']); }
        return [implode(' AND ', $w), $a];
    }

    public static function search(array $f, int $limit = 18, int $offset = 0): array
    {
        [$where, $a] = self::filter($f);
        $a[] = $limit;
        $a[] = $offset;
        return Db::all("SELECT * FROM listings WHERE $where ORDER BY id DESC LIMIT ? OFFSET ?", $a);
    }

    public static function count(array $f): int
    {
        [$where, $a] = self::filter($f);
        return (int) Db::val("SELECT COUNT(*) FROM listings WHERE $where", $a, 0);
    }

    public static function kinds(): array
    {
        return Db::all("SELECT kind, COUNT(*) n FROM listings WHERE status = 'live' AND kind <> '' GROUP BY kind ORDER BY n DESC");
    }

    public static function cities(): array
    {
        return Db::all("SELECT city, COUNT(*) n FROM listings WHERE status = 'live' AND city <> '' GROUP BY city ORDER BY n DESC LIMIT 14");
    }

    /** A plain-English name for a kind, so the page never shows a database value. */
    public static function kindName(string $k): string
    {
        return ['stay' => 'Stay', 'ride' => 'Ride', 'honeymoon' => 'Honeymoon', 'service' => 'Service', 'asoebi' => 'Aso-ebi'][$k] ?? ucfirst($k);
    }
}
