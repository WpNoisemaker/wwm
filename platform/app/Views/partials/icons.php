<?php
/**
 * An icon from the sprite in partials/sprite.php. Line icons are thin, like the chevrons and the
 * − / + in the day reference; "s-" icons are the small solid glyphs from the night reference.
 */
function icon(string $n, int $s = 0, string $cls = ''): string {
    static $line = ['arrow', 'auto', 'back', 'bag', 'bell', 'book', 'bookmark', 'calendar', 'camera', 'chart', 'chat', 'check', 'clock', 'close', 'copy', 'download', 'eye', 'filter', 'flag', 'gear', 'gift', 'globe', 'grid', 'heart', 'home', 'image', 'info', 'link', 'live', 'lock', 'mail', 'map', 'menu', 'minus', 'moon', 'more', 'music', 'next', 'out', 'pen', 'people', 'phone', 'pin', 'play', 'plus', 'qr', 'reply', 'ring', 'search', 'share', 'shield', 'sliders', 'spark', 'star', 'store', 'sun', 'ticket', 'trash', 'trophy', 'up', 'user', 'wallet'];
    static $solid = ['bag', 'book', 'bulb', 'calendar', 'camera', 'chat', 'crown', 'eye', 'gift', 'globe', 'heart', 'live', 'lock', 'music', 'pin', 'power', 'qr', 'ring', 'shield', 'spark', 'square', 'store', 'users', 'wallet', 'warning'];
    static $alias = ['rings' => 'ring', 'cash' => 'wallet', 'talk' => 'chat', 'cal' => 'calendar', 'cam' => 'camera', 'x' => 'close'];
    $isSolid = str_starts_with($n, 's-');
    $k = $isSolid ? substr($n, 2) : ($alias[$n] ?? $n);
    if ($isSolid ? !in_array($k, $solid, true) : !in_array($k, $line, true)) {
        $isSolid = false; $k = 'spark';
    }
    $size = $s > 0 ? ' style="width:' . $s . 'px;height:' . $s . 'px"' : '';
    return '<svg class="ico' . ($isSolid ? ' ico--solid' : '') . ($cls !== '' ? ' ' . $cls : '') . '"' . $size . ' aria-hidden="true" focusable="false"><use href="#' . ($isSolid ? 's-' : 'i-') . $k . '"/></svg>';
}

/**
 * An amount, with the naira drawn rather than typed. $raw gives the plain text for a screen
 * reader; the drawn sign is hidden from it so the amount is not read as "N 2.7m".
 */
function money(string $amount, string $cur = 'NGN'): string {
    $sign = ['NGN' => '<span class="ngn">N</span>', 'USD' => '$', 'GHS' => 'GH₵', 'KES' => 'KSh', 'ZAR' => 'R'];
    $sym  = ['NGN' => '₦', 'USD' => '$', 'GHS' => '₵', 'KES' => 'KSh', 'ZAR' => 'R'];
    $draw = $sign[$cur] ?? e($cur) . ' ';
    $read = $sym[$cur] ?? $cur;
    return '<span class="money"><span class="sr">' . e($read . $amount) . '</span><span aria-hidden="true">' . $draw . e($amount) . '</span></span>';
}

/** 2756500 -> "2.7m". Kept short because these sit inside small tiles. */
function short_money(int $n): string {
    if ($n >= 1000000) { return rtrim(rtrim(number_format($n / 1000000, 1), '0'), '.') . 'm'; }
    if ($n >= 1000)    { return rtrim(rtrim(number_format($n / 1000, 1), '0'), '.') . 'k'; }
    return number_format($n);
}

/** "4 hours ago". */
function ago(string $when): string {
    $s = max(0, time() - strtotime($when));
    foreach ([31536000 => 'year', 2592000 => 'month', 604800 => 'week', 86400 => 'day', 3600 => 'hour', 60 => 'minute'] as $sec => $word) {
        if ($s >= $sec) { $n = (int) floor($s / $sec); return $n . ' ' . $word . ($n > 1 ? 's' : '') . ' ago'; }
    }
    return 'just now';
}

/**
 * A stored image path rendered as an <img>, or '' when the row has none so the caller's gradient
 * stands in. Three kinds of path arrive here: an absolute URL, a WordPress upload carried over by
 * the importer (wp-content/...), and our own library under assets/img.
 */
function photo(?string $path, string $alt = '', bool $eager = false, string $sizes = ''): string
{
    $path = trim((string) $path);
    if ($path === '') {
        return '';
    }
    // An attachment WordPress no longer has. Showing nothing lets the colour fallback stand in,
    // which beats a broken-image icon on somebody's wedding page.
    if (str_starts_with($path, 'wp:')) {
        return '';
    }
    if (str_starts_with($path, 'http') || str_starts_with($path, '/')) {
        $src = $path;
    } elseif (str_starts_with($path, 'wp-content/uploads/')) {
        // The importer keeps WordPress's own layout (2025/09/file.jpg); the files live in assets/uploads.
        $src = App::asset('assets/uploads/' . substr($path, strlen('wp-content/uploads/')));
    } elseif (str_starts_with($path, 'wp-content/')) {
        $src = App::url('/' . $path);
    } else {
        $src = App::asset('assets/img/' . $path);
    }
    // Our own photos come with a 640px copy beside them, so a phone never downloads the big one.
    $set = '';
    if (!str_starts_with($path, 'http') && !str_starts_with($path, '/') && !str_starts_with($path, 'wp-content/') && str_ends_with($path, '.jpg')) {
        $small = substr($path, 0, -4) . '.640.jpg';
        if (is_file(App::root() . '/public/assets/img/' . $small)) {
            $set = ' srcset="' . e(App::url('assets/img/' . $small)) . ' 640w, ' . e(App::url('assets/img/' . $path)) . ' 1600w"'
                 . ' sizes="' . e($sizes !== '' ? $sizes : '(min-width: 960px) 30vw, 60vw') . '"';
        }
    }
    return '<img src="' . e($src) . '"' . $set . ' alt="' . e($alt) . '"'
         . ' loading="' . ($eager ? 'eager' : 'lazy') . '" decoding="async"' . ($eager ? ' fetchpriority="high"' : '') . '>';
}

/** Listings keep a JSON array of photos; the homepage only ever wants the first. */
function first_photo(?string $json): string
{
    $a = json_decode((string) $json, true);
    return is_array($a) && $a ? (string) reset($a) : '';
}
