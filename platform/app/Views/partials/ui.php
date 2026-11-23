<?php
/**
 * Small drawing helpers shared by every view, so a squircle, a card or a dot numeral is written
 * once and looks the same everywhere.
 */

/**
 * A dot numeral, drawn on the server: one row of round dots along each digit's centre line, the
 * way the reference draws its "2". The lengths were measured once so the dots land evenly.
 */
function dots(string $text, string $cls = '', bool $live = false): string
{
    static $g = [
        '0' => [50, [['M25 3A20 37 0 0 1 25 77A20 37 0 0 1 25 3', 183.102]]],
        '1' => [36, [['M9 15L22 4L22 77', 90.029]]],
        '2' => [50, [['M6 20C6 9 15 3 25 3C36 3 44 10 44 21C44 31 37 37 30 44L6 77L46 77', 166.577]]],
        '3' => [50, [['M7 12C11 6 17 3 25 3C35 3 42 9 42 19C42 30 34 37 22 37C36 37 45 45 45 56C45 69 36 77 24 77C15 77 8 73 5 66', 167.848]]],
        '4' => [50, [['M34 77L34 3L4 56L47 56', 177.902]]],
        '5' => [50, [['M42 3L11 3L8 36C13 32 19 30 25 30C37 30 45 39 45 52C45 66 36 77 24 77C15 77 8 73 5 66', 175.577]]],
        '6' => [50, [['M38 8C34 4 29 3 25 3C12 3 6 18 6 42C6 63 13 77 25 77C37 77 44 68 44 55C44 42 36 34 25 34C16 34 9 39 6 46', 194.803]]],
        '7' => [50, [['M5 3L45 3L18 77', 118.772]]],
        '8' => [50, [['M25 3A16 17 0 0 1 25 37A16 17 0 0 1 25 3', 103.689], ['M25 37A19 20 0 0 1 25 77A19 20 0 0 1 25 37', 122.56]]],
        '9' => [50, [['M44 34C41 41 34 46 25 46C14 46 6 38 6 25C6 12 14 3 25 3C37 3 44 13 44 30C44 56 37 77 23 77C17 77 13 75 10 72', 195.84]]],
        ':' => [24, [['M12 28l0 .01M12 62l0 .01', 0]]],
        '.' => [22, [['M11 76l0 .01', 0]]],
        ',' => [22, [['M11 76l0 .01', 0]]],
        ' ' => [24, []],
    ];
    $x = 0;
    $paths = '';
    foreach (preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) as $ch) {
        if (!isset($g[$ch])) {
            continue;
        }
        foreach ($g[$ch][1] as [$d, $len]) {
            $dash = '';
            if ($len > 0) {
                $n = max(1, (int) round($len / 11));
                $dash = ' stroke-dasharray="0 ' . number_format($len / $n, 3, '.', '') . '"';
            }
            $paths .= '<path transform="translate(' . $x . ' 0)" d="' . $d . '"' . $dash . ($len > 0 ? ' data-dash' : '') . '/>';
        }
        $x += $g[$ch][0] + 9;
    }
    $w = max(1, $x - 9);
    $svg = '<svg viewBox="-4 -4 ' . ($w + 8) . ' 88" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="6.2" stroke-linecap="round" stroke-linejoin="round">' . $paths . '</svg>';
    return '<span class="dotnum' . ($cls !== '' ? ' ' . e($cls) : '') . '" role="img" aria-label="' . e($text) . '" data-value="' . e($text) . '"'
         . ($live ? ' data-dots' : '') . '>' . $svg . '</span>';
}

/** A squircle holding a photo, or the design's sky with a fallback when there is no photo. */
function sq(?string $path, string $alt = '', string $cls = '', bool $eager = false, string $fallback = ''): string
{
    // Wide and tall photos fill most of the screen, so they ask for the big copy sooner.
    $big = (bool) preg_match('/\bsq--(wide|tall|round)\b|profile__pic/', $cls);
    $img = photo($path, $alt, $eager, $big ? '(min-width: 960px) 60vw, 100vw' : '');
    if ($img === '' && $fallback !== '') {
        $img = '<span class="fill-art" aria-hidden="true">' . $fallback . '</span>';
    }
    return '<span class="sq' . ($cls !== '' ? ' ' . e($cls) : '') . '">' . $img . '</span>';
}

/** The two initials, for when a couple has not added a photo yet. */
function initials(array $w): string
{
    return e(mb_strtoupper(mb_substr(trim((string) $w['partner_a']), 0, 1) . '&' . mb_substr(trim((string) $w['partner_b']), 0, 1)));
}

/** A wedding as a card: squircle photo, names, and one line under them. */
function wedding_card(array $w, string $sub = '', string $tag = '', string $cls = '', int $rank = 0): string
{
    $sub = $sub !== '' ? $sub : trim(Wedding::where($w) . ' · ' . Wedding::short($w), ' ·');
    return '<a class="card' . ($cls !== '' ? ' ' . e($cls) : '') . '" href="' . e(App::url('/w/' . $w['slug'])) . '">'
         . '<span class="sq">' . (photo((string) $w['cover'], Wedding::names($w)) ?: '<span class="fill-art" aria-hidden="true">' . initials($w) . '</span>')
         . ($rank > 0 ? '<span class="sq__rank" aria-label="Ranked ' . $rank . '">#' . $rank . '</span>' : '')
         . ($tag !== '' ? '<span class="sq__tag">' . e($tag) . '</span>' : '') . '</span>'
         . '<span class="card__cap"><span class="card__t">' . e(Wedding::names($w)) . '</span><span class="card__s">' . e($sub) . '</span></span></a>';
}

/** A community post's colour. Posts written before the redesign keep a sensible match. */
function talk_colour(string $bg): string
{
    static $map = ['sky' => 'c-sky', 'dusk' => 'c-dusk', 'slate' => 'c-slate', 'night' => 'c-night', 'blue' => 'c-blue', 'cream' => 'c-cream',
                   'paper' => 'c-cream', 'ink' => 'c-night', 'sun' => 'c-sky', 'citrus' => 'c-blue', 'blush' => 'c-dusk', 'noir' => 'c-night'];
    return $map[$bg] ?? 'c-dusk';
}

/** The colours a visitor can choose for a post. */
function talk_colours(): array
{
    return ['dusk' => 'Dusk', 'sky' => 'Sky', 'night' => 'Night', 'blue' => 'Blue', 'slate' => 'Slate', 'cream' => 'Cream'];
}

/** Flash messages, drawn as notes. */
function flashes(): string
{
    $out = '';
    foreach (Guard::takeFlash() as $f) {
        $bad = ($f['kind'] ?? '') === 'bad';
        $out .= '<p class="note' . ($bad ? ' note--bad' : '') . '" role="' . ($bad ? 'alert' : 'status') . '">' . icon($bad ? 'info' : 'check') . '<span>' . e($f['msg']) . '</span></p>';
    }
    return $out;
}

/** A label/value tile from the night reference. */
function kv(string $k, string $v, string $cls = '', bool $raw = false): string
{
    return '<div class="tile' . ($cls !== '' ? ' ' . e($cls) : '') . '"><span class="k">' . e($k) . '</span><span class="v">' . ($raw ? $v : e($v)) . '</span></div>';
}

/** A read-only slider pill: a percentage drawn as the lighter fill with its || grip. */
function meter(int $pct, string $label = '', string $text = '', string $cls = ''): string
{
    $pct = max(0, min(100, $pct));
    $t = $text !== '' ? $text : ($pct === 0 ? 'Be the first' : $pct . ' %');
    $cls = trim($cls . ($pct === 0 ? ' is-empty' : ''));
    return '<div class="slider' . ($cls !== '' ? ' ' . e($cls) : '') . '" role="meter" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' . $pct . '"'
         . ($label !== '' ? ' aria-label="' . e($label) . '"' : '') . '><div class="slider__fill" style="--v:' . $pct . '%">'
         . '<span class="slider__txt"><span class="slider__val">' . e($t) . '</span>' . ($label !== '' ? '<span class="slider__lab">' . e($label) . '</span>' : '') . '</span>'
         . '<i class="slider__grip"></i></div></div>';
}

/** Previous / next for a paged list. */
function pager(int $page, int $pages, array $query = []): string
{
    if ($pages <= 1) {
        return '';
    }
    $url = static function (int $p) use ($query): string {
        $q = http_build_query(array_filter($query + ['page' => $p], static fn ($v) => $v !== '' && $v !== null));
        return '?' . $q;
    };
    $prev = $page > 1 ? ' href="' . e($url($page - 1)) . '"' : ' aria-disabled="true"';
    $next = $page < $pages ? ' href="' . e($url($page + 1)) . '"' : ' aria-disabled="true"';
    return '<nav class="pager" aria-label="Pages"><a class="cbtn"' . $prev . ' aria-label="Previous page">' . icon('back') . '</a>'
         . '<span>Page ' . $page . ' of ' . $pages . '</span><a class="cbtn"' . $next . ' aria-label="Next page">' . icon('next') . '</a></nav>';
}

/**
 * A Pro or Premium tag on something a page's plan does not include. It opens the upgrade sheet,
 * which says what the feature is and links to the plans.
 */
function up_tag(string $plan, string $feature, string $cls = ''): string
{
    $name = $plan === 'premium' ? 'Premium' : 'Pro';
    return '<button type="button" class="up-tag up-tag--' . e($plan) . ($cls !== '' ? ' ' . e($cls) : '') . '" data-upgrade="' . e($plan) . '" data-feature="' . e($feature) . '"'
        . ' aria-label="' . e($feature . ' comes with ' . $name . '. See how to get it') . '">' . icon('s-spark') . '<span>' . $name . '</span></button>';
}

/** An editable piece of the site's own text (see Content), escaped for the page. */
function t(string $key, string $default): string
{
    $v = e(Content::get($key, $default));
    // While an admin edits the live page, the words carry invisible marks naming their key.
    return LiveEdit::editing() ? LiveEdit::markKey($key, $v) : $v;
}

/** Editable text that may run to several lines. */
function tl(string $key, string $default): string
{
    $v = nl2br(e(Content::get($key, $default)));
    if (LiveEdit::editing()) {
        LiveEdit::$keys[] = $key;
        return '<span data-cms-k="' . (count(LiveEdit::$keys) - 1) . '" data-cms-ml="1">' . $v . '</span>';
    }
    return $v;
}

/** An editable picture: the path HQ uploaded, or the default. */
function timg(string $key, string $default): string
{
    $v = Content::get($key, $default);
    if (LiveEdit::editing()) {
        LiveEdit::$imgKeys[$key] = $v;
    }
    return $v;
}

/** A picture path as a full address, for link previews and backgrounds. */
function img_url(string $path): string
{
    $path = $path !== '' ? LiveEdit::img($path) : $path;
    if ($path === '' || str_starts_with($path, 'http')) {
        return $path;
    }
    return App::url((str_starts_with($path, 'uploads/') ? 'assets/' : 'assets/img/') . ltrim($path, '/'));
}

/** A whole page HQ may have rewritten, in the blog's writing format; '' keeps the standard text. */
function tbody(string $key, string $default): string
{
    return Content::get($key, $default);
}

/**
 * A social profile link as [network, @handle], for the squad cards. Unknown sites show their own
 * name; anything that is not a web address shows nothing.
 *
 * @return array{0:string,1:string}
 */
function social_label(string $url): array
{
    $host = strtolower((string) parse_url($url, PHP_URL_HOST));
    if ($host === '' || !preg_match('~^https?://~i', $url)) {
        return ['', ''];
    }
    $host = preg_replace('/^(www|m|mobile)\./', '', $host);
    $nets = ['instagram.com' => 'Instagram', 'tiktok.com' => 'TikTok', 'x.com' => 'X', 'twitter.com' => 'X', 'facebook.com' => 'Facebook',
             'linkedin.com' => 'LinkedIn', 'snapchat.com' => 'Snapchat', 'youtube.com' => 'YouTube', 'threads.net' => 'Threads', 'pinterest.com' => 'Pinterest'];
    $net = $nets[$host] ?? $host;
    $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
    $first = explode('/', $path)[0] ?? '';
    $handle = '';
    if (isset($nets[$host]) && $host !== 'linkedin.com' && $host !== 'facebook.com' && preg_match('/^@?([A-Za-z0-9._]{1,30})$/', $first, $m)) {
        $handle = '@' . $m[1];
    }
    return [$net, $handle];
}
