<?php
declare(strict_types=1);

final class BlogController
{
    public function index(): void
    {
        $cat  = trim((string) ($_GET['category'] ?? ''));
        $args = [];
        $where = "status = 'live'";
        if ($cat !== '') {
            $where .= ' AND category = ?';
            $args[] = $cat;
        }
        View::render('blog/index', [
            'title' => 'Wedding tips & how-tos',
            'desc'  => 'Written for Nigerian and African weddings — programs, registries, RSVPs, guests and money.',
            'tab'   => 'blog',
            'css'   => ['pages'],
            'rows'  => Db::all("SELECT * FROM posts WHERE $where ORDER BY published_at DESC, id DESC LIMIT 30", $args),
            'cats'  => Db::all("SELECT category, COUNT(*) n FROM posts WHERE status='live' AND category<>'' GROUP BY category ORDER BY n DESC"),
            'cat'   => $cat,
        ]);
    }

    public function show(string $slug): void
    {
        $p = Db::one("SELECT * FROM posts WHERE slug = ? AND status = 'live'", [$slug]);
        if (!$p) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not found']);
            return;
        }
        View::render('blog/show', [
            'title' => (string) $p['title'],
            'desc'  => mb_substr((string) $p['excerpt'], 0, 180),
            'tab'   => 'blog',
            'css'   => ['pages'],
            'p'     => $p,
            'more'  => Db::all("SELECT * FROM posts WHERE status='live' AND id <> ? ORDER BY published_at DESC LIMIT 3", [$p['id']]),
        ]);
    }
}
