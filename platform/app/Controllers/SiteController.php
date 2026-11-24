<?php
declare(strict_types=1);

/** The homepage, the design gallery, and the files that make the site an installable app. */
final class SiteController
{
    public function home(): void
    {
        $data = Site::home();
        $shown = array_map(static fn ($w) => (int) $w['id'], array_merge($data['top'], $data['trending']));
        View::render('site/home', $data + [
            'title'    => 'Wedding Websites & More: wedding websites for African couples',
            'desc'     => 'Wedding websites for African couples: invitation, RSVP, gifts and livestream on one link. Vendors, a marketplace and the Anonymous Community.',
            'tab'      => 'home',
            'appTitle' => 'Wedding Websites & More',
            'samples'  => self::samples($shown),
        ]);
    }

    /** Every design, each shown on a real page that wears it. */
    public function templates(): void
    {
        // Hidden: nothing on the site links here. It opens only from the private link HQ shares
        // with a client (/templates?k=…), or for our own team. Everyone else is told it does not exist.
        $k = (string) ($_GET['k'] ?? '');
        if (!(($k !== '' && hash_equals(Site::designsKey(), $k)) || Staff::can('content') || Staff::can('settings'))) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not here']);
            return;
        }
        header('X-Robots-Tag: noindex, nofollow');
        header('Cache-Control: private, no-store');
        View::render('site/templates', [
            'title'    => 'Wedding page designs',
            'desc'     => 'Every wedding page design: Classic for Free and Pro pages, ten Premium designs and custom designs made for one couple.',
            'tab'      => 'weddings',
            'appTitle' => 'Designs',
            'back'     => App::url('/'),
            'samples'  => self::samples(),
        ]);
    }

    /**
     * One public page per design, so a design is always shown on a real wedding. Pages already
     * shown elsewhere on the screen ($skip ids) are passed over when another page wears the design.
     */
    public static function samples(array $skip = []): array
    {
        $rows = Db::all("SELECT * FROM weddings WHERE visibility = 'public' ORDER BY buzz DESC");
        if ($skip) {
            usort($rows, static fn ($a, $b) => (int) in_array((int) $a['id'], $skip, true) <=> (int) in_array((int) $b['id'], $skip, true));
        }
        $out = [];
        foreach ($rows as $w) {
            $d = Design::forWedding($w);
            $k = $d['key'] === 'classic' ? ((string) $w['plan'] === 'free' ? 'free' : 'pro') : $d['key'];
            if (!isset($out[$k])) {
                $out[$k] = $w;
            }
        }
        return $out;
    }

    /* =============================================================== The app: PWA files */

    public function manifest(): void
    {
        $base = (string) parse_url(App::url('/'), PHP_URL_PATH);
        header('Content-Type: application/manifest+json; charset=utf-8');
        header('Cache-Control: public, max-age=3600');
        echo json_encode([
            'name'             => (string) App::config('name', 'Wedding Websites & More'),
            'short_name'       => 'Weddings',
            'description'      => 'Wedding pages for African couples: invite, RSVP, gifts, spraying and livestream in one link.',
            'id'               => $base,
            'start_url'        => $base . '?from=app',
            'scope'            => $base,
            'display'          => 'standalone',
            'display_override' => ['standalone', 'minimal-ui'],
            'orientation'      => 'portrait',
            'background_color' => '#1F2531',
            'theme_color'      => '#1E242E',
            'categories'       => ['lifestyle', 'social'],
            'icons'            => [
                ['src' => App::url('assets/img/brand/icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => App::url('assets/img/brand/icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => App::url('assets/img/brand/icon-maskable.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
            'shortcuts'        => [
                ['name' => 'Make a wedding page', 'url' => $base . 'join', 'icons' => [['src' => App::url('assets/img/brand/icon-192.png'), 'sizes' => '192x192']]],
                ['name' => 'Anonymous Community', 'url' => $base . 'community', 'icons' => [['src' => App::url('assets/img/brand/icon-192.png'), 'sizes' => '192x192']]],
                ['name' => 'Your page', 'url' => $base . 'account', 'icons' => [['src' => App::url('assets/img/brand/icon-192.png'), 'sizes' => '192x192']]],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * The service worker. Written here rather than as a static file so the list of files it keeps
     * always names the current versions: when a stylesheet changes, this file changes, and every
     * phone picks up the new site on its next visit.
     */
    public function sw(): void
    {
        $base  = (string) parse_url(App::url('/'), PHP_URL_PATH);
        $shell = [
            App::url('offline'),
            App::asset('assets/css/wwm.css'),
            App::asset('assets/js/wwm.js'),
            App::url('assets/fonts/urbanist-latin.woff2'),
            App::url('assets/fonts/urbanist-latin-ext.woff2'),
            App::url('assets/img/brand/favicon.ico'),
            App::url('assets/img/brand/icon-192.png'),
        ];
        $version = substr(sha1(implode('|', $shell) . '|' . (string) @filemtime(App::root() . '/app/Views/site/sw.js')), 0, 10);
        header('Content-Type: text/javascript; charset=utf-8');
        header('Cache-Control: no-cache');
        header('Service-Worker-Allowed: ' . $base);
        $js = file_get_contents(App::root() . '/app/Views/site/sw.js');
        echo strtr((string) $js, [
            '__VERSION__' => $version,
            '__BASE__'    => $base,
            '__SHELL__'   => json_encode($shell, JSON_UNESCAPED_SLASHES),
            '__OFFLINE__' => json_encode(App::url('offline'), JSON_UNESCAPED_SLASHES),
        ]);
    }

    public function offline(): void
    {
        View::render('site/offline', [
            'title'    => 'You are offline',
            'appTitle' => 'Offline',
            'tab'      => '',
        ]);
    }

    /** The picture a link preview shows for a page. */
    public function og(string $kind, string $key): void
    {
        Share::serve($kind, $key);
    }

    /** Every public page worth finding, for search engines. */
    public function sitemap(): void
    {
        Blog::ensure();
        header('Content-Type: application/xml; charset=utf-8');
        header('Cache-Control: public, max-age=3600');
        $x = static fn (string $s): string => htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $urls = [];
        $add = static function (string $path, ?string $mod = null, string $freq = 'weekly') use (&$urls): void {
            $urls[] = [App::url($path), $mod, $freq];
        };
        foreach (['/', '/weddings', '/rankings', '/vendors', '/market', '/sell', '/community', '/blog', '/pricing', '/about', '/help',
                  '/terms', '/privacy', '/refunds', '/community-guidelines', '/cookies'] as $pth) {
            $add($pth, null, in_array($pth, ['/', '/weddings', '/rankings', '/community', '/blog'], true) ? 'daily' : 'monthly');
        }
        foreach (array_keys(Blog::SECTIONS) as $sec) {
            $add('/blog/' . $sec, null, 'daily');
        }
        foreach (Wedding::countryList() as $c) {
            $slug = Wedding::placeSlug((string) $c['country']);
            if ($slug !== '') {
                $add('/weddings/' . $slug, null, 'daily');
            }
        }
        foreach (Db::all("SELECT slug, created_at FROM weddings WHERE visibility = 'public' ORDER BY id") as $r) {
            $add('/w/' . $r['slug'], (string) $r['created_at']);
        }
        foreach (Db::all('SELECT slug, created_at FROM vendors ORDER BY id') as $r) {
            $add('/vendors/' . $r['slug'], (string) $r['created_at'], 'monthly');
        }
        foreach (Db::all("SELECT slug, created_at FROM listings WHERE status = 'live' ORDER BY id") as $r) {
            $add('/market/' . $r['slug'], (string) $r['created_at'], 'monthly');
        }
        foreach (Db::all("SELECT slug, COALESCE(updated_at, published_at) m FROM posts WHERE status = 'live' AND (published_at IS NULL OR published_at <= NOW()) ORDER BY id") as $r) {
            $add('/blog/' . $r['slug'], $r['m'] ? (string) $r['m'] : null, 'monthly');
        }
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as [$loc, $mod, $freq]) {
            echo '<url><loc>' . $x($loc) . '</loc>' . ($mod ? '<lastmod>' . date('Y-m-d', (int) strtotime($mod)) . '</lastmod>' : '') . '<changefreq>' . $freq . '</changefreq></url>';
        }
        echo '</urlset>';
    }

    public function robots(): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        echo "User-agent: *\nDisallow: /account\nDisallow: /hq\nDisallow: /sign-in\nDisallow: /join\nDisallow: /forgot\nDisallow: /reset\nDisallow: /verify\nDisallow: /search\nDisallow: /pay\nAllow: /\n\n" . implode('', array_map(static fn (string $bot): string => "User-agent: $bot\nDisallow: /\n\n", ['GPTBot', 'ChatGPT-User', 'OAI-SearchBot', 'ClaudeBot', 'Claude-Web', 'anthropic-ai', 'CCBot', 'Google-Extended', 'PerplexityBot', 'Bytespider', 'Applebot-Extended', 'meta-externalagent', 'Diffbot', 'Omgilibot', 'cohere-ai'])) . "Sitemap: " . App::url('/sitemap.xml') . "\n";
    }
}
