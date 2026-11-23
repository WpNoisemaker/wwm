<?php
declare(strict_types=1);

/**
 * Editing the live site. Anyone who can change site content can switch on edit mode, then click any
 * words on any page to change them, or any picture to replace it.
 *
 * - Words written as t('key', ...) in a template save to that key (the same field HQ → Site content
 *   shows).
 * - Any other words save as a change of that exact text: everywhere it appears, or on one page only.
 * - A picture saves as a replacement for that picture, in every size, everywhere it is used.
 *
 * Changes are applied to the page as it is sent, so templates never need to change for an edit.
 */
final class LiveEdit
{
    /** Invisible marks put around editable words while editing, so the browser knows what each is. */
    public const S = "\u{2063}";   // start
    public const K = "\u{2064}";   // after a t() key number
    public const O = "\u{2061}";   // after an override number
    public const E = "\u{2062}";   // end

    private static ?array $rows = null;
    /** @var list<string> keys of t() words on this page, in the order printed */
    public static array $keys = [];
    /** @var array<string,string> timg() key => picture path on this page */
    public static array $imgKeys = [];

    public static function table(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        Db::run("CREATE TABLE IF NOT EXISTS site_overrides (
            id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
            kind       ENUM('text','img') NOT NULL DEFAULT 'text',
            scope      VARCHAR(190) NOT NULL DEFAULT '*',
            original   TEXT NOT NULL,
            ohash      CHAR(40) NOT NULL,
            value      TEXT NOT NULL,
            updated_by INT UNSIGNED NULL,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id), UNIQUE KEY uq_one (kind, scope, ohash)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $done = true;
    }

    /** Whether this visitor is editing the page right now. */
    public static function editing(): bool
    {
        static $on = null;
        if ($on === null) {
            $on = !empty($_SESSION['cms_edit']) && self::allowed();
        }
        return $on;
    }

    public static function allowed(): bool
    {
        return Auth::check() && Staff::can('content');
    }

    /** All changes, loaded once per page. */
    private static function rows(): array
    {
        if (self::$rows === null) {
            try {
                self::table();
                self::$rows = Db::all('SELECT id, kind, scope, original, value FROM site_overrides');
            } catch (Throwable $e) {
                self::$rows = [];
            }
        }
        return self::$rows;
    }

    public static function norm(string $s): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(str_replace([self::S, self::K, self::O, self::E], '', $s), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    /** A picture's replacement, if HQ has put one in. */
    public static function img(string $path): string
    {
        foreach (self::rows() as $r) {
            if ($r['kind'] === 'img' && $r['original'] === $path) {
                return (string) $r['value'];
            }
        }
        return $path;
    }

    /**
     * Put the changed words into a finished page. Only whole text between tags, and a few attributes
     * people read (button hints, picture descriptions, form hints), are matched; scripts and styles
     * are left alone.
     */
    public static function apply(string $html, string $path): string
    {
        $map = [];
        foreach (self::rows() as $r) {
            if ($r['kind'] === 'text' && ($r['scope'] === '*' || $r['scope'] === $path)) {
                // A change made for this page wins over one made for everywhere.
                if (!isset($map[$r['original']]) || $r['scope'] !== '*') {
                    $map[$r['original']] = [(string) $r['value'], (int) $r['id']];
                }
            }
        }
        $edit = self::editing();
        if (!$map && !$edit) {
            return $html;
        }
        $parts = preg_split('~(<script\b.*?</script>|<style\b.*?</style>|<textarea\b.*?</textarea>)~is', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($parts as $i => $part) {
            if ($i % 2 === 1) {
                continue;   // script, style, textarea: untouched
            }
            if ($map) {
                $part = (string) preg_replace_callback('/>([^<>]+)</u', static function (array $m) use ($map, $edit): string {
                    $n = self::norm($m[1]);
                    if ($n === '' || !isset($map[$n])) {
                        return $m[0];
                    }
                    preg_match('/^\s*/u', $m[1], $a);
                    preg_match('/\s*$/u', $m[1], $b);
                    $v = e($map[$n][0]);
                    return '>' . $a[0] . ($edit ? self::S . self::bits($map[$n][1]) . self::O . $v . self::E : $v) . $b[0] . '<';
                }, $part);
                $part = (string) preg_replace_callback('/\b(placeholder|alt|title|aria-label)="([^"]*)"/u', static function (array $m) use ($map): string {
                    $n = self::norm($m[2]);
                    return isset($map[$n]) ? $m[1] . '="' . e($map[$n][0]) . '"' : $m[0];
                }, $part);
            }
            $parts[$i] = $part;
        }
        return implode('', $parts);
    }

    /** A number written in invisible characters (zero-width space = 0, zero-width non-joiner = 1). */
    public static function bits(int $n): string
    {
        return strtr(decbin($n), ['0' => "\u{200B}", '1' => "\u{200C}"]);
    }

    /** t() words while editing: marked with the key's number on this page. */
    public static function markKey(string $key, string $html): string
    {
        self::$keys[] = $key;
        return self::S . self::bits(count(self::$keys) - 1) . self::K . $html . self::E;
    }

    /** What the editor in the browser needs: the keys and pictures on this page. */
    public static function script(string $path): string
    {
        return '<script type="application/json" id="cms-data">' . json_encode([
            'keys'  => self::$keys,
            'imgs'  => self::$imgKeys,
            'path'  => $path,
            'save'  => App::url('/hq/edit/save'),
            'image' => App::url('/hq/edit/image'),
            'off'   => App::url('/hq/edit?off=1&back=' . rawurlencode($path)),
            'ids'   => array_column(array_filter(self::rows(), static fn ($r) => $r['kind'] === 'text'), 'original', 'id'),
            'token' => Guard::token(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) . '</script>';
    }

    public static function saveText(string $original, string $value, string $scope): void
    {
        self::table();
        $original = self::norm($original);
        if ($original === '') {
            return;
        }
        $value = trim($value);
        if ($value === '' || $value === $original) {
            Db::run("DELETE FROM site_overrides WHERE kind = 'text' AND scope = ? AND ohash = ?", [$scope, sha1($original)]);
        } else {
            Db::run("INSERT INTO site_overrides (kind, scope, original, ohash, value, updated_by) VALUES ('text', ?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE value = VALUES(value), updated_by = VALUES(updated_by)", [$scope, $original, sha1($original), mb_substr($value, 0, 5000), Auth::id()]);
        }
        self::$rows = null;
    }

    public static function saveImg(string $original, string $value): void
    {
        self::table();
        Db::run("INSERT INTO site_overrides (kind, scope, original, ohash, value, updated_by) VALUES ('img', '*', ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE value = VALUES(value), updated_by = VALUES(updated_by)", [$original, sha1($original), $value, Auth::id()]);
        self::$rows = null;
    }

    /** Every change made on the live site, newest first, for HQ to review or undo. */
    public static function all(): array
    {
        self::table();
        return Db::all('SELECT o.*, u.name FROM site_overrides o LEFT JOIN users u ON u.id = o.updated_by ORDER BY o.updated_at DESC');
    }

    public static function remove(int $id): void
    {
        self::table();
        Db::run('DELETE FROM site_overrides WHERE id = ?', [$id]);
        self::$rows = null;
    }
}
