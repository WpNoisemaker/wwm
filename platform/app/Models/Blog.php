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
        if (Site::setting('blog_news_1', '') === '') {
            self::firstNews();
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
                $src = str_starts_with($m[2], 'uploads/') ? App::url('assets/' . $m[2]) : $m[2];
                $out[] = '<figure><img src="' . e($src) . '" alt="' . e($m[1]) . '" loading="lazy">' . ($m[1] !== '' ? '<figcaption>' . e($m[1]) . '</figcaption>' : '') . '</figure>';
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
     * The first two news posts, about what the platform actually does now. Added once to every site,
     * old or new; HQ can edit or delete them like any other post.
     */
    private static function firstNews(): void
    {
        Db::run("INSERT INTO settings (k, v) VALUES ('blog_news_1', '1') ON DUPLICATE KEY UPDATE v = '1'");
        $posts = [
            ['african-weddings-in-eleven-countries', 'African weddings in eleven countries, each with its own page',
             'Nigeria, Ghana, Kenya, Rwanda and South Africa, and the families in the UK, the US, Canada, Australia, New Zealand and Dubai: every country now has its own listing.',
             "Our weddings travel. The aso-ebi leaves Lagos for London, the uncles fly from Accra to Houston, and the livestream reaches Auckland at four in the morning. Wedding Websites & More now follows them.\n\n## A page for every country\n\nEvery country with a public wedding page now has its own listing: [the UK](/weddings/uk), [the US](/weddings/usa), [Canada](/weddings/canada), [Australia](/weddings/australia), [New Zealand](/weddings/new-zealand), [Dubai and the UAE](/weddings/dubai), [Ghana](/weddings/ghana), [Kenya](/weddings/kenya), [Rwanda](/weddings/rwanda), [South Africa](/weddings/south-africa) and [Nigeria](/weddings/nigeria). Each one shows the weddings coming up there, the cities, the plans and the vendors couples booked locally.\n\n## Gifts in your own currency\n\nA couple in London receives gifts in pounds, a couple in Nairobi in shillings, a couple in Toronto in Canadian dollars. Guests see the right sign and sensible amounts.\n\n## Prices in yours\n\nOn [pricing](/pricing), pick your currency and every plan price redraws. Plans are priced in naira; other currencies are a guide at today's rate.",
             'photos/listing-dubai.jpg', date('Y-m-d H:i:s', time() - 3 * 86400)],
            ['who-knows-them-best-quiz', 'Who knows them best? A quiz about each of you, with a leaderboard',
             'Guests now pick the partner they know and answer that person\'s quiz. The best friend, the cousin and the colleague finally find out who really knows them.',
             "Every family has someone who claims to know the bride best. Now there is a way to settle it.\n\n## How it works\n\nOn Pro, Premium and High Society pages, the couple writes a short quiz about each of them: the nickname at home, the food they always order, where they grew up, the football club, the thing they are secretly scared of. Not how they met; this is about who knows each of them.\n\nGuests open the page, tap the partner they know, answer, and see their score straight away. Every score goes on that partner's leaderboard, so the whole wedding can see who knows them best.\n\n## Writing yours\n\nIn your dashboard, open **Quiz**. Write up to eight questions for each of you, tick the right answer, and save. It appears on your page at once.\n\n## It counts towards the Buzz Chart\n\nEvery quiz played earns the couple buzz coins, more on higher plans. See [how each plan earns buzz](/rankings#coins).",
             'photos/couple-dance.jpg', date('Y-m-d H:i:s', time() - 600)],
        ];
        foreach ($posts as [$slug, $title, $excerpt, $source, $cover, $when]) {
            if (Db::val('SELECT 1 FROM posts WHERE slug = ?', [$slug])) {
                continue;
            }
            Db::insert('posts', [
                'slug' => $slug, 'title' => $title, 'excerpt' => $excerpt, 'source' => $source, 'body' => self::toHtml($source),
                'cover' => $cover, 'category' => 'News', 'kind' => 'news', 'author' => 'Wedding Websites & More',
                'status' => 'live', 'published_at' => $when, 'updated_at' => $when,
            ]);
        }
    }
}
