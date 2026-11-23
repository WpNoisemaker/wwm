<?php
declare(strict_types=1);

/**
 * The blog: guides written for search, news from the platform, and features and interviews.
 *
 * HQ writes posts in a plain format (a blank line between paragraphs, "## " for a heading, "> " for
 * a quote, "- " for a list, "Q:" and "A:" for an interview) and the page gets clean HTML. Posts
 * that arrived as HTML from the old site are kept as HTML.
 */
final class Blog
{
    /** Address => [kind, name, what it is]. */
    public const SECTIONS = [
        'guides'   => ['guide',   'Guides',                'Planning advice and how-tos for African weddings, wherever you are.'],
        'news'     => ['news',    'News',                  'What is new on Wedding Websites & More, and in the wedding world.'],
        'features' => ['feature', 'Features & interviews', 'Couples, vendors and the people who make the day, in their own words.'],
    ];
    public const KIND = ['guide' => 'Guide', 'news' => 'News', 'feature' => 'Feature'];

    /** Sites installed before the blog had sections get the new columns the first time they are needed. */
    public static function ensure(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        if (!Db::val("SHOW COLUMNS FROM posts LIKE 'kind'")) {
            self::addColumns();
        }
        if (Site::setting('blog_starter_2', '') === '') {
            self::starter();
        }
    }

    private static function addColumns(): void
    {
        Db::run("ALTER TABLE posts
            ADD COLUMN kind ENUM('guide','news','feature') NOT NULL DEFAULT 'guide' AFTER category,
            ADD COLUMN author VARCHAR(120) NOT NULL DEFAULT '' AFTER kind,
            ADD COLUMN source MEDIUMTEXT NULL AFTER body,
            ADD COLUMN meta_title VARCHAR(190) NOT NULL DEFAULT '',
            ADD COLUMN meta_desc VARCHAR(300) NOT NULL DEFAULT '',
            ADD COLUMN featured TINYINT(1) NOT NULL DEFAULT 0,
            ADD COLUMN updated_at DATETIME NULL");
    }

    /** @return array<int,array<string,mixed>> */
    public static function live(string $kind = '', int $limit = 12, int $offset = 0): array
    {
        self::ensure();
        $in = $kind !== '' ? ' AND kind = ?' : '';
        $args = $kind !== '' ? [$kind, $limit, $offset] : [$limit, $offset];
        return Db::all("SELECT * FROM posts WHERE status = 'live' AND (published_at IS NULL OR published_at <= NOW())$in
                        ORDER BY published_at DESC, id DESC LIMIT ? OFFSET ?", $args);
    }

    public static function count(string $kind = ''): int
    {
        self::ensure();
        $in = $kind !== '' ? ' AND kind = ?' : '';
        return (int) Db::val("SELECT COUNT(*) FROM posts WHERE status = 'live' AND (published_at IS NULL OR published_at <= NOW())$in", $kind !== '' ? [$kind] : [], 0);
    }

    /** The lead story: the newest post marked featured, or simply the newest. */
    public static function lead(): ?array
    {
        self::ensure();
        return Db::one("SELECT * FROM posts WHERE status = 'live' AND (published_at IS NULL OR published_at <= NOW())
                        ORDER BY featured DESC, published_at DESC, id DESC LIMIT 1");
    }

    public static function bySlug(string $slug, bool $drafts = false): ?array
    {
        self::ensure();
        return Db::one('SELECT * FROM posts WHERE slug = ?' . ($drafts ? '' : " AND status = 'live' AND (published_at IS NULL OR published_at <= NOW())"), [$slug]);
    }

    public static function minutes(array $p): int
    {
        return max(1, (int) round(str_word_count(strip_tags((string) $p['body'])) / 220));
    }

    public static function section(string $kind): string
    {
        foreach (self::SECTIONS as $slug => $s) {
            if ($s[0] === $kind) {
                return $slug;
            }
        }
        return 'guides';
    }

    /** True when the text is already HTML (old posts), so it is kept as it is. */
    public static function isHtml(string $text): bool
    {
        return (bool) preg_match('#<(p|h2|h3|ul|ol|blockquote|figure|div)\b#i', $text);
    }

    /** The plain writing format to HTML. Everything typed is escaped first, so only these shapes come out. */
    public static function toHtml(string $text): string
    {
        $text = str_replace("\r\n", "\n", trim($text));
        if ($text === '') {
            return '';
        }
        $inline = static function (string $s): string {
            $s = e($s);
            $s = (string) preg_replace('/\*\*(.+?)\*\*/u', '<b>$1</b>', $s);
            $s = (string) preg_replace('/(?<![*\w])\*(?!\s)(.+?)(?<!\s)\*(?![*\w])/u', '<i>$1</i>', $s);
            return (string) preg_replace_callback('/\[([^\]]+)\]\(((?:https?:\/\/|\/)[^\s)]+)\)/u', static function (array $m): string {
                $external = str_starts_with($m[2], 'http') && !str_contains($m[2], Share::host());
                return '<a href="' . $m[2] . '"' . ($external ? ' rel="noopener" target="_blank"' : '') . '>' . $m[1] . '</a>';
            }, $s);
        };
        $out = [];
        foreach (preg_split('/\n{2,}/', $text) ?: [] as $block) {
            $lines = array_values(array_filter(array_map('rtrim', explode("\n", trim($block))), static fn ($l) => $l !== ''));
            if (!$lines) {
                continue;
            }
            $first = $lines[0];
            if (preg_match('/^!\[([^\]]*)\]\(((?:https?:\/\/|\/|uploads\/)[^\s)]+)\)$/u', $first, $m)) {
                $img = str_starts_with($m[2], 'uploads/') ? photo($m[2], $m[1], false, '(min-width: 960px) 680px, 100vw')
                    : '<img src="' . e($m[2]) . '" alt="' . e($m[1]) . '" loading="lazy" decoding="async" onerror="this.onerror=null;this.style.visibility=\'hidden\'">';
                $out[] = '<figure>' . $img . ($m[1] !== '' ? '<figcaption>' . e($m[1]) . '</figcaption>' : '') . '</figure>';
            } elseif (str_starts_with($first, '### ')) {
                $out[] = '<h3>' . $inline(substr($first, 4)) . '</h3>';
            } elseif (str_starts_with($first, '## ')) {
                $out[] = '<h2>' . $inline(substr($first, 3)) . '</h2>';
            } elseif (str_starts_with($first, '> ')) {
                $q = array_map(static fn ($l) => ltrim(preg_replace('/^>\s?/', '', $l) ?? ''), $lines);
                $cite = '';
                if (count($q) > 1 && preg_match('/^(—|--|-)\s*(.+)$/u', end($q), $c)) {
                    array_pop($q);
                    $cite = '<cite>' . $inline($c[2]) . '</cite>';
                }
                $out[] = '<blockquote><p>' . implode('<br>', array_map($inline, $q)) . '</p>' . $cite . '</blockquote>';
            } elseif (preg_match('/^[-*] /', $first)) {
                $out[] = '<ul>' . implode('', array_map(static fn ($l) => '<li>' . $inline(preg_replace('/^[-*]\s+/', '', $l) ?? '') . '</li>', $lines)) . '</ul>';
            } elseif (preg_match('/^\d+[.)] /', $first)) {
                $out[] = '<ol>' . implode('', array_map(static fn ($l) => '<li>' . $inline(preg_replace('/^\d+[.)]\s+/', '', $l) ?? '') . '</li>', $lines)) . '</ol>';
            } elseif (preg_match('/^Q:\s*/', $first)) {
                $out[] = '<p class="qa qa--q"><b>' . $inline(preg_replace('/^Q:\s*/', '', implode(' ', $lines)) ?? '') . '</b></p>';
            } elseif (preg_match('/^A:\s*/', $first)) {
                $out[] = '<p class="qa qa--a">' . $inline(preg_replace('/^A:\s*/', '', implode(' ', $lines)) ?? '') . '</p>';
            } else {
                $out[] = '<p>' . implode('<br>', array_map($inline, $lines)) . '</p>';
            }
        }
        return implode("\n", $out);
    }

    /**
     * The starter posts (db/blog/starter.php), written for search. Added to a site that does not
     * have them, and brought up to date on one that does, but only where HQ has not edited the post.
     */
    private static function starter(): void
    {
        Db::run("INSERT INTO settings (k, v) VALUES ('blog_starter_2', '1') ON DUPLICATE KEY UPDATE v = '1'");
        $posts = require App::root() . '/db/blog/starter.php';
        $now = date('Y-m-d H:i:s');
        foreach ($posts as $slug => [$kind, $title, $metaTitle, $metaDesc, $excerpt, $cover, $daysAgo, $source]) {
            $row = [
                'title' => $title, 'meta_title' => $metaTitle, 'meta_desc' => $metaDesc, 'excerpt' => $excerpt,
                'source' => $source, 'body' => self::toHtml($source), 'kind' => $kind, 'category' => self::KIND[$kind],
                'author' => 'Wedding Websites & More', 'updated_at' => $now,
            ];
            $have = Db::one('SELECT id, source, published_at, updated_at, cover FROM posts WHERE slug = ?', [$slug]);
            if (!$have) {
                Db::insert('posts', $row + ['slug' => $slug, 'cover' => $cover, 'status' => 'live',
                    'published_at' => date('Y-m-d H:i:s', time() - $daysAgo * 86400 - 600)]);
                continue;
            }
            $untouched = trim((string) $have['source']) === '' || $have['updated_at'] === null
                || strtotime((string) $have['updated_at']) <= strtotime((string) ($have['published_at'] ?? $now));
            if ($untouched) {
                Db::update('posts', $row + ((string) $have['cover'] === '' ? ['cover' => $cover] : []), 'id = ?', [$have['id']]);
            }
        }
    }
}
