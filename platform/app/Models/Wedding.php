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
}
