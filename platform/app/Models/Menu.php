<?php
declare(strict_types=1);

/**
 * The site's menus, which HQ can edit: the main menu (desktop header and phone menu), the extra
 * items only the phone menu shows, and the footer's groups of links. Until HQ saves a change,
 * these defaults are used.
 */
final class Menu
{
    /** Icons offered for menu items (they show on the phone menu's tiles). */
    public const ICONS = ['ring', 'chart', 'store', 'bag', 'chat', 'book', 'wallet', 'search', 'info', 'shield', 'home', 'heart',
                          'people', 'calendar', 'gift', 'star', 'globe', 'mail', 'phone', 'map', 'camera', 'music', 'live', 'ticket', 'grid', 'link'];

    private const DEFAULT = [
        'main' => [
            ['Weddings', '/weddings', 'ring'],
            ['Buzz Chart', '/rankings', 'chart'],
            ['Vendors', '/vendors', 'store'],
            ['Marketplace', '/market', 'bag'],
            ['Anonymous Community', '/community', 'chat'],
            ['Blog', '/blog', 'book'],
            ['Pricing', '/pricing', 'wallet'],
        ],
        'phone' => [
            ['Search', '/search', 'search'],
            ['Sell with us', '/sell', 'store'],
            ['About us', '/about', 'info'],
            ['Help', '/help', 'shield'],
        ],
        'footer' => [
            ['Platform', [['Wedding pages', '/weddings'], ['Buzz Chart', '/rankings'], ['Anonymous Community', '/community'], ['Blog', '/blog']]],
            ['Shop', [['Vendors', '/vendors'], ['Marketplace', '/market'], ['Sell with us', '/sell'], ['Pricing', '/pricing']]],
            ['Samples', [['Premium page', '/w/ibrahim-ummi'], ['Free page', '/w/kola-lara']]],
            ['Help', [['Help', '/help'], ['About', '/about'], ['Terms & conditions', '/terms'], ['Privacy policy', '/privacy'],
                      ['Refund policy', '/refunds'], ['Community guidelines', '/community-guidelines'], ['Cookies', '/cookies']]],
        ],
    ];

    private static ?array $all = null;

    public static function all(): array
    {
        if (self::$all === null) {
            $saved = json_decode(Site::setting('menus', ''), true);
            self::$all = is_array($saved) ? $saved + self::DEFAULT : self::DEFAULT;
        }
        return self::$all;
    }

    /** Main menu items as [icon, label, url, key]; the key is the first part of the address, for "you are here". */
    public static function main(): array
    {
        return array_map(static fn (array $i): array => [$i[2] ?? 'link', $i[0], $i[1], self::key($i[1])], self::all()['main']);
    }

    public static function phone(): array
    {
        return array_map(static fn (array $i): array => [$i[2] ?? 'link', $i[0], $i[1], self::key($i[1])], self::all()['phone']);
    }

    /** Footer groups: [heading, [[label, url], …]]. */
    public static function footer(): array
    {
        return self::all()['footer'];
    }

    /** A link as the page should use it: our own addresses go through App::url, others stay as they are. */
    public static function href(string $url): string
    {
        return preg_match('~^(https?:|mailto:|tel:|#)~i', $url) ? $url : App::url($url);
    }

    private static function key(string $url): string
    {
        return (string) explode('/', trim((string) parse_url($url, PHP_URL_PATH), '/'))[0];
    }

    /** Only our own paths, full web addresses, email, phone or an anchor. */
    public static function cleanUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '' || preg_match('~^(javascript|data|vbscript):~i', $url)) {
            return '';
        }
        if (preg_match('~^(https?://|mailto:|tel:|#)~i', $url)) {
            return mb_substr($url, 0, 300);
        }
        return '/' . ltrim(mb_substr($url, 0, 300), '/');
    }

    /** Save what HQ sent: rows with a label and a link, in the order given, without the removed ones. */
    public static function save(array $in): void
    {
        $rows = static function ($list, bool $icon): array {
            $out = [];
            foreach ((array) $list as $r) {
                $label = mb_substr(trim((string) ($r['label'] ?? '')), 0, 60);
                $url = self::cleanUrl((string) ($r['url'] ?? ''));
                if ($label === '' || $url === '' || !empty($r['off'])) {
                    continue;
                }
                $row = [$label, $url];
                if ($icon) {
                    $row[] = in_array($r['icon'] ?? '', self::ICONS, true) ? (string) $r['icon'] : 'link';
                }
                $out[] = ['sort' => (float) ($r['sort'] ?? 0), 'row' => $row];
            }
            usort($out, static fn ($a, $b) => $a['sort'] <=> $b['sort']);
            return array_column($out, 'row');
        };
        $groups = [];
        foreach ((array) ($in['footer'] ?? []) as $g) {
            $title = mb_substr(trim((string) ($g['title'] ?? '')), 0, 40);
            $links = $rows($g['links'] ?? [], false);
            if ($title === '' || !$links || !empty($g['off'])) {
                continue;
            }
            $groups[] = ['sort' => (float) ($g['sort'] ?? 0), 'g' => [$title, $links]];
        }
        usort($groups, static fn ($a, $b) => $a['sort'] <=> $b['sort']);
        $menus = ['main' => $rows($in['main'] ?? [], true), 'phone' => $rows($in['phone'] ?? [], true), 'footer' => array_column($groups, 'g')];
        Db::run('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)', ['menus', json_encode($menus, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]);
        self::$all = null;
    }

    public static function reset(): void
    {
        Db::run('DELETE FROM settings WHERE k = ?', ['menus']);
        self::$all = null;
    }
}
