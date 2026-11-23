<?php
declare(strict_types=1);

/**
 * The site's own words and pictures, as HQ has edited them. Every editable piece of text in the
 * templates is written t('key', 'the default'); HQ's content editor finds those calls in the
 * templates themselves, so the list of what can be edited is always complete and in page order.
 * Nothing edited means the default shows.
 */
final class Content
{
    private static ?array $all = null;

    public static function table(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        Db::run("CREATE TABLE IF NOT EXISTS site_content (
            k          VARCHAR(120) NOT NULL,
            v          MEDIUMTEXT   NOT NULL,
            updated_by INT UNSIGNED NULL,
            updated_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (k)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $done = true;
    }

    public static function get(string $key, string $default): string
    {
        if (self::$all === null) {
            try {
                self::table();
                self::$all = array_column(Db::all('SELECT k, v FROM site_content'), 'v', 'k');
            } catch (Throwable $e) {
                self::$all = [];
            }
        }
        $v = self::$all[$key] ?? '';
        return $v !== '' ? $v : $default;
    }

    public static function set(string $key, string $value): void
    {
        self::table();
        if (trim($value) === '') {
            Db::run('DELETE FROM site_content WHERE k = ?', [$key]);
        } else {
            Db::run('INSERT INTO site_content (k, v, updated_by) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v), updated_by = VALUES(updated_by)', [$key, $value, Auth::id()]);
        }
        self::$all = null;
    }

    /** Which pages HQ can edit, and the file each one's words live in. */
    public const PAGES = [
        'home'      => ['The front page', ['site/home.php']],
        'site'      => ['Across the site', ['partials/footer.php', 'layout.php']],
        'weddings'  => ['Wedding pages directory', ['wedding/index.php']],
        'rankings'  => ['Buzz Chart', ['rankings/index.php']],
        'vendors'   => ['Vendors', ['vendor/index.php']],
        'market'    => ['Marketplace', ['market/index.php']],
        'community' => ['Anonymous Community', ['community/index.php']],
        'pricing'   => ['Pricing', ['page/pricing.php']],
        'templates' => ['Designs', ['site/templates.php']],
        'about'     => ['About us', ['page/about.php']],
        'legal'     => ['Policies', ['page/terms.php', 'page/privacy.php', 'page/refunds.php', 'page/community-guidelines.php', 'page/cookies.php']],
    ];

    /**
     * Every editable field on a page, read from its templates, in the order they appear.
     *
     * @return list<array{key:string,type:string,default:string}>
     */
    public static function fields(string $page): array
    {
        $out = [];
        $seen = [];
        foreach (self::PAGES[$page][1] ?? [] as $file) {
            $src = (string) @file_get_contents(App::root() . '/app/Views/' . $file);
            preg_match_all("/\\b(t|tl|timg|tbody)\\(\\s*'([a-z0-9_.-]+)'\\s*,\\s*'((?:[^'\\\\]|\\\\.)*)'/", $src, $m, PREG_SET_ORDER);
            foreach ($m as [, $fn, $key, $def]) {
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $out[] = ['key' => $key, 'type' => $fn, 'default' => strtr($def, ["\\'" => "'", "\\\\" => "\\"])];
            }
        }
        return $out;
    }

    /** "home.heading.everything-in-one-place" -> "Heading". */
    public static function label(array $f): string
    {
        $kind = explode('.', $f['key'])[1] ?? '';
        return ['title' => 'Page title', 'heading' => 'Heading', 'subheading' => 'Small heading', 'eyebrow' => 'Label above a heading',
                'intro' => 'Introduction', 'text' => 'Paragraph', 'button' => 'Button', 'item' => 'List item', 'image' => 'Picture',
                'body' => 'Whole page', 'tile' => 'Feature', 'tileline' => 'Feature line', 'plan' => 'Plan line', 'search' => 'Search box hint'][$kind] ?? ucfirst($kind);
    }
}
