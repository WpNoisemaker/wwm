<?php
declare(strict_types=1);

/** The homepage, the design gallery, and the files that make the site an installable app. */
final class SiteController
{
    public function home(): void
    {
        $data = Site::home();
        View::render('site/home', $data + [
            'title'    => 'Wedding Websites & More: wedding pages for African couples',
            'desc'     => 'One beautiful page for your whole wedding: invite, RSVP, gifts, spraying and livestream in one link. See the Buzz Chart, book vendors, make yours free.',
            'tab'      => 'home',
            'appTitle' => 'Wedding Pages',
            'samples'  => self::samples(),
        ]);
    }

    /** Every design, each shown on a real page that wears it. */
    public function templates(): void
    {
        View::render('site/templates', [
            'title'    => 'Wedding page designs',
            'desc'     => 'Every wedding page design: Classic for Free and Pro pages, ten Premium designs and custom designs made for one couple.',
            'tab'      => 'weddings',
            'appTitle' => 'Designs',
            'back'     => App::url('/'),
            'samples'  => self::samples(),
        ]);
    }

    /** One public page per design, so a design is always shown on a real wedding. */
    public static function samples(): array
    {
        $rows = Db::all("SELECT * FROM weddings WHERE visibility = 'public' ORDER BY buzz DESC");
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
            'background_color' => '#7B8F9B',
            'theme_color'      => '#6F8590',
            'categories'       => ['lifestyle', 'social'],
            'icons'            => [
                ['src' => App::url('assets/img/brand/icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => App::url('assets/img/brand/icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => App::url('assets/img/brand/icon-maskable.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
            'shortcuts'        => [
                ['name' => 'Make a wedding page', 'url' => $base . 'join', 'icons' => [['src' => App::url('assets/img/brand/icon-192.png'), 'sizes' => '192x192']]],
                ['name' => 'Community', 'url' => $base . 'community', 'icons' => [['src' => App::url('assets/img/brand/icon-192.png'), 'sizes' => '192x192']]],
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
        $version = substr(sha1(implode('|', $shell) . '|3'), 0, 10);
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
}
