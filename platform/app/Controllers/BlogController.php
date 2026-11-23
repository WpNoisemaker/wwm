<?php
declare(strict_types=1);

/** The blog: every post, each section, one post, and the feed. */
final class BlogController
{
    public function index(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $per  = 12;
        $lead = $page === 1 ? Blog::lead() : null;
        $rows = Blog::live('', $per + 1, ($page - 1) * $per);
        if ($lead) {
            $rows = array_values(array_filter($rows, static fn ($r) => (int) $r['id'] !== (int) $lead['id']));
        }
        $total = Blog::count();
        View::render('blog/index', [
            'title'   => 'The Wedding Websites & More blog: guides, news and interviews',
            'desc'    => 'Planning guides for African weddings at home and abroad, platform news, and features and interviews with couples and the people behind the day.',
            'appTitle'=> 'Blog',
            'tab'     => 'blog',
            'css'     => ['pages'],
            'lead'    => $lead,
            'rows'    => array_slice($rows, 0, $per),
            'section' => '',
            'page'    => $page,
            'pages'   => (int) ceil($total / $per),
            'counts'  => array_map(static fn ($s) => Blog::count($s[0]), Blog::SECTIONS),
        ]);
    }

    public function section(string $section): void
    {
        $s = Blog::SECTIONS[$section] ?? null;
        if (!$s) {
            self::notFound();
        }
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $per  = 12;
        $total = Blog::count($s[0]);
        View::render('blog/index', [
            'title'   => $s[1] . ' · Wedding Websites & More',
            'desc'    => $s[2],
            'appTitle'=> $s[1],
            'back'    => App::url('/blog'),
            'tab'     => 'blog',
            'css'     => ['pages'],
            'lead'    => null,
            'rows'    => Blog::live($s[0], $per, ($page - 1) * $per),
            'section' => $section,
            'page'    => $page,
            'pages'   => (int) ceil($total / $per),
            'counts'  => array_map(static fn ($x) => Blog::count($x[0]), Blog::SECTIONS),
        ]);
    }

    public function show(string $slug): void
    {
        $staff = Auth::check() && (Auth::is('staff') || Auth::is('owner'));
        $p = Blog::bySlug($slug, $staff);   // HQ can read a draft before it goes out
        if (!$p) {
            self::notFound();
        }
        $kind = (string) ($p['kind'] ?? 'guide');
        $sec  = Blog::section($kind);
        $title = trim((string) ($p['meta_title'] ?? '')) !== '' ? (string) $p['meta_title'] : (string) $p['title'];
        $desc  = trim((string) ($p['meta_desc'] ?? '')) !== '' ? (string) $p['meta_desc'] : mb_strimwidth(trim((string) $p['excerpt']) ?: strip_tags((string) $p['body']), 0, 180, '…');
        View::render('blog/show', [
            'title'   => $title . ' · Wedding Websites & More',
            'desc'    => $desc,
            'ogTitle' => $title,
            'ogType'  => 'article',
            'ogImage' => Share::url('b', (string) $p['slug'], implode('|', [$p['cover'], $p['title'], $kind])),
            'appTitle'=> Blog::SECTIONS[$sec][1],
            'back'    => App::url('/blog/' . $sec),
            'tab'     => 'blog',
            'css'     => ['pages'],
            'p'       => $p,
            'sec'     => $sec,
            'draft'   => (string) $p['status'] !== 'live',
            'more'    => Db::all("SELECT * FROM posts WHERE status = 'live' AND id <> ? AND (published_at IS NULL OR published_at <= NOW())
                                  ORDER BY (kind = ?) DESC, published_at DESC LIMIT 3", [$p['id'], $kind]),
        ]);
    }

    /** RSS, for readers and for search engines that like a feed. */
    public function feed(): void
    {
        header('Content-Type: application/rss+xml; charset=utf-8');
        header('Cache-Control: public, max-age=1800');
        $x = static fn (string $s): string => htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"><channel>';
        echo '<title>Wedding Websites &amp; More</title><link>' . $x(App::url('/blog')) . '</link>';
        echo '<atom:link href="' . $x(App::url('/blog/feed.xml')) . '" rel="self" type="application/rss+xml"/>';
        echo '<description>Guides, news and interviews for African weddings.</description><language>en</language>';
        foreach (Blog::live('', 30) as $p) {
            $url = App::url('/blog/' . $p['slug']);
            echo '<item><title>' . $x((string) $p['title']) . '</title><link>' . $x($url) . '</link><guid isPermaLink="true">' . $x($url) . '</guid>';
            echo '<category>' . $x(Blog::KIND[(string) ($p['kind'] ?? 'guide')] ?? 'Guide') . '</category>';
            if ($p['published_at']) {
                echo '<pubDate>' . date(DATE_RSS, (int) strtotime((string) $p['published_at'])) . '</pubDate>';
            }
            echo '<description>' . $x((string) ($p['excerpt'] ?: mb_strimwidth(strip_tags((string) $p['body']), 0, 300, '…'))) . '</description></item>';
        }
        echo '</channel></rss>';
    }

    private static function notFound(): never
    {
        http_response_code(404);
        View::render('errors/404', ['title' => 'Not found']);
        exit;
    }
}
