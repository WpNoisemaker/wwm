<?php
declare(strict_types=1);

/**
 * Link previews. Every page worth sharing gets its own 1200×630 picture, so a link pasted into
 * WhatsApp, X or iMessage shows exactly what it points to: the couple and their date, the post
 * itself, the vendor, the listing, the country. Drawn once with GD, kept on disk, and drawn again
 * only when what it shows changes.
 */
final class Share
{
    public const W = 1200;
    public const H = 630;

    /** The address of a page's preview. The stamp changes with the content, so apps fetch it afresh. */
    public static function url(string $kind, string $key, string $stamp = ''): string
    {
        return App::url('og/' . $kind . '/' . rawurlencode($key) . '.jpg') . '?v=' . substr(md5($stamp), 0, 8);
    }

    /** "weddingwebsites.io": the address people can type, for the watermark. */
    public static function host(): string
    {
        $h = strtolower((string) parse_url((string) App::config('url'), PHP_URL_HOST));
        $h = (string) preg_replace('/^www\./', '', $h);
        return $h === '' || $h === 'localhost' || filter_var($h, FILTER_VALIDATE_IP) ? 'weddingwebsites.io' : $h;
    }

    public static function serve(string $kind, string $key): void
    {
        $job = self::job($kind, rawurldecode($key));
        if ($job === null || !function_exists('imagettftext')) {
            self::send(App::root() . '/public/assets/img/brand/og.jpg');
        }
        [$stamp, $draw] = $job;
        $dir  = App::root() . '/public/assets/uploads/og';
        $file = $dir . '/' . $kind . '-' . substr(md5($kind . '|' . $key . '|' . $stamp), 0, 16) . '.jpg';
        if (!is_file($file)) {
            $im = $draw();
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            if (!is_writable($dir) || !@imagejpeg($im, $file, 84)) {
                header('Content-Type: image/jpeg');
                header('Cache-Control: public, max-age=3600');
                imagejpeg($im, null, 84);
                exit;
            }
        }
        self::send($file);
    }

    private static function send(string $file): never
    {
        header('Content-Type: image/jpeg');
        header('Cache-Control: public, max-age=604800');
        header('Content-Length: ' . (string) filesize($file));
        readfile($file);
        exit;
    }

    /** What to draw for each kind of page, and a stamp of everything the picture shows. */
    private static function job(string $kind, string $key): ?array
    {
        switch ($kind) {
            case 'w':
                $w = Wedding::bySlug($key);
                if (!$w || (string) $w['visibility'] !== 'public') {
                    return null;   // a private page never shows its couple to a link preview
                }
                $d = Wedding::design($w);
                $cols = [(string) ($d['primary'] ?? '#2A3C66'), (string) ($d['accent'] ?? '#94AFD9')];
                $eyebrow = trim(Wedding::when($w) . ' · ' . Wedding::place($w), ' ·');
                $badge = Site::tier((string) $w['plan']) . ' page';
                $stamp = implode('|', [$w['cover'], Wedding::names($w), $eyebrow, $badge, implode(',', $cols)]);
                return [$stamp, static fn () => self::card((string) $w['cover'], $cols, $eyebrow, Wedding::names($w), (string) $w['hashtag'], $badge)];
            case 't':
                $t = Db::one("SELECT * FROM talks WHERE id = ? AND status = 'live'", [(int) $key]);
                if (!$t) {
                    return null;
                }
                $stamp = implode('|', [$t['body'], $t['bg'], $t['topic'], $t['pen_name']]);
                return [$stamp, static fn () => self::post($t)];
            case 'v':
                $v = Vendor::bySlug($key);
                if (!$v) {
                    return null;
                }
                $sub = trim((string) $v['category'] . ((string) $v['city'] !== '' ? ' · ' . $v['city'] : ''), ' ·');
                $badge = (int) $v['verified'] ? 'Verified' : '';
                $stamp = implode('|', [$v['logo'], $v['name'], $sub, $badge]);
                return [$stamp, static fn () => self::card((string) $v['logo'], ['#2A3C66', '#94AFD9'], $sub, (string) $v['name'], '', $badge)];
            case 'l':
                $l = Listing::bySlug($key);
                if (!$l) {
                    return null;
                }
                $price = (int) $l['on_ask'] ? 'Price on request' : cur_sign('NGN') . number_format((int) $l['price']) . ((string) $l['price_unit'] !== '' ? ' per ' . $l['price_unit'] : '');
                $by = !empty($l['seller']) ? 'by ' . $l['seller'] : (string) $l['city'];
                $badge = (int) ($l['seller_ok'] ?? 0) ? 'Verified seller' : Listing::kindName((string) $l['kind']);
                $stamp = implode('|', [$l['photos'], $l['title'], $price, $by, $badge]);
                return [$stamp, static fn () => self::card(first_photo((string) $l['photos']), ['#1A1F28', '#304476'], $price, (string) $l['title'], $by, $badge)];
            case 'b':
                $p = Db::one("SELECT * FROM posts WHERE slug = ? AND status = 'live'", [$key]);
                if (!$p) {
                    return null;
                }
                Blog::ensure();
                $label = Blog::SECTIONS[Blog::section((string) ($p['kind'] ?? 'guide'))][1];
                $by = trim((string) ($p['author'] ?? ''));
                $stamp = implode('|', [$p['cover'], $p['title'], $label, $by]);
                return [$stamp, static fn () => self::card((string) $p['cover'], ['#5C727E', '#98A9B9'], 'The blog', (string) $p['title'], $by !== '' ? 'By ' . $by : '', $label)];
            case 'p':
                $pl = Wedding::PLACES[$key] ?? null;
                if (!$pl) {
                    return null;
                }
                $top = Db::one("SELECT * FROM weddings WHERE visibility = 'public' AND country = ? AND cover <> '' ORDER BY buzz DESC LIMIT 1", [$pl[0]]);
                $n = Wedding::countSearch(['country' => $pl[0]]);
                $sub = $n . ' wedding page' . ($n === 1 ? '' : 's');
                $stamp = implode('|', [$top['cover'] ?? '', $sub]);
                return [$stamp, static fn () => self::card((string) ($top['cover'] ?? ''), ['#2A3C66', '#94AFD9'], 'African weddings in', ucfirst($pl[1]), $sub, '')];
        }
        return null;
    }

    /* ------------------------------------------------------------------------------- drawing */

    private static function font(int $weight): string
    {
        return App::root() . '/app/Fonts/urbanist-' . ($weight >= 500 ? 500 : 300) . '.ttf';
    }

    /** A photo filling the frame, darkened towards the words; or the page's own colours if it has none. */
    private static function card(string $photo, array $cols, string $eyebrow, string $title, string $sub, string $badge): \GdImage
    {
        $im = imagecreatetruecolor(self::W, self::H);
        $src = self::load($photo);
        if ($src) {
            self::cover($im, $src);
            for ($y = 0; $y < self::H; $y++) {           // darker at the bottom, where the words are
                $a = (int) round(127 - 112 * (max(0, $y - self::H * .25) / (self::H * .75)) ** 1.25);
                imageline($im, 0, $y, self::W, $y, imagecolorallocatealpha($im, 10, 14, 22, max(0, min(127, $a))));
            }
            imagefilledrectangle($im, 0, 0, self::W, self::H, imagecolorallocatealpha($im, 10, 14, 22, 112));
        } else {
            self::gradient($im, [self::hex($cols[0], '#2A3C66'), self::hex($cols[1] ?? '', '#94AFD9')]);
            imagefilledrectangle($im, 0, 0, self::W, self::H, imagecolorallocatealpha($im, 10, 14, 22, 88));
        }
        $white = imagecolorallocate($im, 255, 255, 255);
        $soft  = imagecolorallocatealpha($im, 255, 255, 255, 30);
        $x = 72;
        $size = mb_strlen($title) > 34 ? 50 : (mb_strlen($title) > 22 ? 60 : 72);
        $lines = self::wrap($title, self::font(300), $size, self::W - 2 * $x, 2);
        $lh = (int) round($size * 1.62);
        $y = self::H - 92 - ($sub !== '' ? 46 : 0) - (count($lines) - 1) * $lh;
        if ($eyebrow !== '') {
            imagettftext($im, 21, 0, $x, $y - $lh + 8, $soft, self::font(300), self::fit($eyebrow, 21, self::W - 2 * $x));
        }
        foreach ($lines as $i => $line) {
            imagettftext($im, $size, 0, $x - 4, $y + $i * $lh, $white, self::font(300), $line);
        }
        if ($sub !== '') {
            imagettftext($im, 22, 0, $x, $y + (count($lines) - 1) * $lh + 56, $soft, self::font(300), self::fit($sub, 22, self::W - 2 * $x - 320));
        }
        if ($badge !== '') {
            self::pill($im, $x, 64, $badge, 'photo');
        }
        self::mark($im, self::W - 72, self::H - 56, 'right');
        return $im;
    }

    /** A community post, the way it looks on the site: its colour, its words, and our address under them. */
    private static function post(array $t): \GdImage
    {
        $stops = [
            'sky' => ['#6EAAE6', '#CAD3C9'], 'dusk' => ['#2A3C66', '#94AFD9'], 'slate' => ['#5C727E', '#98A9B9'],
            'night' => ['#1A1F28', '#304476'], 'blue' => ['#2F6FF0', '#A6C6F2'], 'cream' => ['#E8DFC6', '#B9C9D6'],
        ];
        $c = substr(talk_colour((string) $t['bg']), 2);
        [$a, $b] = $stops[$c] ?? $stops['dusk'];
        $im = imagecreatetruecolor(self::W, self::H);
        self::gradient($im, [self::hex($a, '#2A3C66'), self::hex($b, '#94AFD9')]);
        $dark = $c === 'cream';
        $ink  = $dark ? imagecolorallocate($im, 46, 58, 70) : imagecolorallocate($im, 255, 255, 255);
        $soft = $dark ? imagecolorallocatealpha($im, 46, 58, 70, 40) : imagecolorallocatealpha($im, 255, 255, 255, 34);
        $body = trim((string) preg_replace('/\s+/', ' ', (string) $t['body']));
        $len = mb_strlen($body);
        $size = $len > 200 ? 30 : ($len > 120 ? 36 : ($len > 60 ? 44 : 52));
        $lines = self::wrap($body, self::font(300), $size, 960, $size >= 44 ? 4 : 6);
        $lh = (int) round($size * 1.58);
        $block = count($lines) * $lh;
        $y = (int) round((self::H - $block) / 2 + $size * 1.2) - 6;
        foreach ($lines as $i => $line) {
            $w = self::width($line, $size, self::font(300));
            imagettftext($im, $size, 0, (int) round((self::W - $w) / 2), $y + $i * $lh, $ink, self::font(300), $line);
        }
        // Our address, right under the words.
        self::mark($im, (int) (self::W / 2), $y + (count($lines) - 1) * $lh + 58, 'center', $dark);
        // The topic at the top, the pen name at the bottom, as on the card.
        $topic = Talk::topic((string) $t['topic']);
        self::pill($im, (int) round((self::W - self::width($topic, 17, self::font(500)) - 48) / 2), 46, $topic, $dark ? 'cream' : 'colour');
        $name = ($t['pen_name'] ?: 'Someone') . ' · Community';
        imagettftext($im, 18, 0, (int) round((self::W - self::width($name, 18, self::font(300))) / 2), self::H - 48, $soft, self::font(300), $name);
        return $im;
    }

    /** The watermark: the "&" mark in a ring, then the address. */
    private static function mark(\GdImage $im, int $x, int $y, string $align, bool $dark = false): void
    {
        $host = self::host();
        [$r, $g, $b] = $dark ? [46, 58, 70] : [255, 255, 255];
        $ink = imagecolorallocatealpha($im, $r, $g, $b, $dark ? 24 : 20);
        $size = 17;
        $w = self::width($host, $size, self::font(500)) + 40;
        $left = $align === 'right' ? $x - $w : ($align === 'center' ? (int) round($x - $w / 2) : $x);
        self::shape($im, $left, $y - 22, 28, 28, 14, [$r, $g, $b], $dark ? 24 : 20, 2);
        $amp = self::width('&', 13, self::font(500));
        imagettftext($im, 13, 0, (int) round($left + 14 - $amp / 2) - 1, $y - 3, $ink, self::font(500), '&');
        imagettftext($im, $size, 0, $left + 40, $y, $ink, self::font(500), $host);
    }

    /** A label in a capsule: dark glass on a photo, light glass on a colour, as on the site. */
    private static function pill(\GdImage $im, int $x, int $y, string $text, string $on = 'photo'): void
    {
        $size = 17;
        $h = 46;
        $w = self::width($text, $size, self::font(500)) + 48;
        [$bg, $alpha, $ink] = match ($on) {
            'cream'  => [[46, 58, 70], 108, [46, 58, 70]],
            'colour' => [[255, 255, 255], 100, [255, 255, 255]],
            default  => [[10, 14, 22], 56, [255, 255, 255]],
        };
        self::shape($im, $x, $y, $w, $h, (int) ($h / 2), $bg, $alpha);
        imagettftext($im, $size, 0, $x + 24, $y + 30, imagecolorallocate($im, ...$ink), self::font(500), $text);
    }

    /**
     * A rounded shape, drawn four times larger on its own layer and shrunk onto the picture, so its
     * edges are smooth and its overlapping parts never double up. $ring > 0 draws only the outline.
     */
    private static function shape(\GdImage $im, int $x, int $y, int $w, int $h, int $r, array $rgb, int $alpha, int $ring = 0): void
    {
        $k = 4;
        $t = imagecreatetruecolor($w * $k, $h * $k);
        imagealphablending($t, false);
        imagesavealpha($t, true);
        $clear = imagecolorallocatealpha($t, $rgb[0], $rgb[1], $rgb[2], 127);
        imagefilledrectangle($t, 0, 0, $w * $k, $h * $k, $clear);
        $c = imagecolorallocatealpha($t, $rgb[0], $rgb[1], $rgb[2], $alpha);
        $fill = static function (int $inset, int $col) use ($t, $w, $h, $r, $k): void {
            $i = $inset * $k;
            $rr = max(0, $r * $k - $i);
            imagefilledrectangle($t, $r * $k, $i, $w * $k - $r * $k - 1, $h * $k - $i - 1, $col);
            imagefilledrectangle($t, $i, $r * $k, $w * $k - $i - 1, $h * $k - $r * $k - 1, $col);
            foreach ([[$r * $k, $r * $k], [$w * $k - $r * $k - 1, $r * $k], [$r * $k, $h * $k - $r * $k - 1], [$w * $k - $r * $k - 1, $h * $k - $r * $k - 1]] as [$cx, $cy]) {
                imagefilledellipse($t, $cx, $cy, 2 * $rr, 2 * $rr, $col);
            }
        };
        $fill(0, $c);
        if ($ring > 0) {
            $fill($ring, $clear);
        }
        imagealphablending($im, true);
        imagecopyresampled($im, $t, $x, $y, 0, 0, $w, $h, $w * $k, $h * $k);
    }

    private static function gradient(\GdImage $im, array $stops): void
    {
        [$a, $b] = $stops;
        for ($y = 0; $y < self::H; $y++) {
            $t = $y / (self::H - 1);
            imageline($im, 0, $y, self::W, $y, imagecolorallocate($im,
                (int) round($a[0] + ($b[0] - $a[0]) * $t), (int) round($a[1] + ($b[1] - $a[1]) * $t), (int) round($a[2] + ($b[2] - $a[2]) * $t)));
        }
    }

    /** Fill the frame with the photo, cropped from the middle, a little above centre for faces. */
    private static function cover(\GdImage $im, \GdImage $src): void
    {
        $sw = imagesx($src);
        $sh = imagesy($src);
        $scale = max(self::W / $sw, self::H / $sh);
        $cw = (int) round(self::W / $scale);
        $ch = (int) round(self::H / $scale);
        $cx = (int) round(($sw - $cw) / 2);
        $cy = (int) round(($sh - $ch) * .35);
        imagecopyresampled($im, $src, 0, 0, $cx, $cy, self::W, self::H, $cw, $ch);
    }

    private static function load(string $path): ?\GdImage
    {
        $path = trim($path);
        if ($path === '' || str_starts_with($path, 'http') || str_starts_with($path, 'wp:')) {
            return null;
        }
        $file = match (true) {
            str_starts_with($path, 'uploads/')            => App::root() . '/public/assets/' . $path,
            str_starts_with($path, 'wp-content/uploads/') => App::root() . '/public/assets/uploads/' . substr($path, 19),
            default                                       => App::root() . '/public/assets/img/' . $path,
        };
        if (!is_file($file)) {
            return null;
        }
        $type = @getimagesize($file)[2] ?? 0;
        $im = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($file),
            IMAGETYPE_PNG  => @imagecreatefrompng($file),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file) : false,
            default        => false,
        };
        return $im ?: null;
    }

    private static function width(string $text, int $size, string $font): int
    {
        $b = imagettfbbox($size, 0, $font, $text);
        return (int) abs($b[2] - $b[0]);
    }

    /** Break words into lines that fit, ending the last with "…" when the words run out of room. */
    private static function wrap(string $text, string $font, int $size, int $max, int $most): array
    {
        $lines = [];
        $line = '';
        $words = preg_split('/\s+/u', trim($text)) ?: [];
        foreach ($words as $i => $word) {
            $try = $line === '' ? $word : $line . ' ' . $word;
            if (self::width($try, $size, $font) <= $max || $line === '') {
                $line = $try;
                continue;
            }
            $lines[] = $line;
            $line = $word;
            if (count($lines) === $most) {
                $line = '';
                $lines[$most - 1] = self::fit($lines[$most - 1] . ' ' . implode(' ', array_slice($words, $i)), $size, $max, $font);
                break;
            }
        }
        if ($line !== '') {
            $lines[] = self::fit($line, $size, $max, $font);
        }
        return $lines;
    }

    private static function fit(string $text, int $size, int $max, ?string $font = null): string
    {
        $font ??= self::font(300);
        if (self::width($text, $size, $font) <= $max) {
            return $text;
        }
        while ($text !== '' && self::width($text . '…', $size, $font) > $max) {
            $text = mb_substr($text, 0, -1);
        }
        return rtrim($text, " ,.;:-") . '…';
    }

    /** @return array{0:int,1:int,2:int} */
    private static function hex(string $hex, string $fallback): array
    {
        $h = ltrim(preg_match('/^#?[0-9a-f]{6}$/i', $hex) ? $hex : $fallback, '#');
        return [hexdec(substr($h, 0, 2)), hexdec(substr($h, 2, 2)), hexdec(substr($h, 4, 2))];
    }
}
