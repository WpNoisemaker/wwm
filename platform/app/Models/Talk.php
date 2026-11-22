<?php
declare(strict_types=1);

final class Talk
{
    public const TOPICS = [
        'love' => 'Love & marriage', 'family' => 'Family & in-laws', 'money' => 'Money & pressure',
        'faith' => 'Faith & culture', 'mind' => 'Mind & body', 'leaving' => 'Leaving & starting over',
        'work' => 'Work & ambition', 'wedding' => 'The wedding itself', 'confess' => 'Confessions',
    ];

    /** @return array<int,array<string,mixed>> */
    public static function feed(string $topic = '', string $sort = 'new', int $limit = 20, int $offset = 0): array
    {
        $where = "status='live'";
        $args  = [];
        if ($topic !== '' && isset(self::TOPICS[$topic])) {
            $where .= ' AND topic=?';
            $args[] = $topic;
        }
        $order = $sort === 'top' ? 'likes DESC, replies DESC, id DESC' : 'created_at DESC, id DESC';
        $args[] = $limit;
        $args[] = $offset;
        return Db::all("SELECT * FROM talks WHERE $where ORDER BY $order LIMIT ? OFFSET ?", $args);
    }

    public static function latest(): ?array
    {
        return Db::one("SELECT * FROM talks WHERE status='live' ORDER BY likes DESC LIMIT 1");
    }

    public static function topic(string $k): string
    {
        return self::TOPICS[$k] ?? 'Confessions';
    }
}
