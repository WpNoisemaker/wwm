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

/** An amount with its currency sign. The ₦ comes from the Urbanist naira face in the stylesheet. */
function money(string $amount, string $cur = 'NGN'): string {
    return '<span class="money">' . e(cur_sign($cur) . $amount) . '</span>';
}

function cur_sign(string $cur): string {
    return ['NGN' => '₦', 'USD' => '$', 'GHS' => 'GH₵', 'KES' => 'KSh ', 'ZAR' => 'R', 'GBP' => '£', 'EUR' => '€',
            'CAD' => 'C$', 'AUD' => 'A$', 'NZD' => 'NZ$', 'AED' => 'AED ', 'RWF' => 'FRw '][$cur] ?? $cur . ' ';
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
    // Our own photos (the library and guest uploads) sit beside smaller and WebP copies of
    // themselves. A phone gets the 640px WebP; a big screen the 1600px one; an old browser the JPEG.
    // A photo whose file is missing is left out, so the squircle's colour stands in for it.
    $local = !str_starts_with($path, 'http') && !str_starts_with($path, '/') && !str_starts_with($path, 'wp-content/');
    $sizes = $sizes !== '' ? $sizes : '(min-width: 960px) 30vw, 60vw';
    $lazy  = ' loading="' . ($eager ? 'eager' : 'lazy') . '" decoding="async"' . ($eager ? ' fetchpriority="high"' : '');
    $fail  = ' onerror="this.onerror=null;this.style.visibility=\'hidden\'"';
    if ($local) {
        $dir  = str_starts_with($path, 'uploads/') ? 'assets/' : 'assets/img/';
        $disk = App::root() . '/public/' . $dir;
        if (!is_file($disk . $path)) {
            return '';
        }
        $stem = (string) preg_replace('/\.(jpe?g|png)$/i', '', $path);
        $url  = static fn (string $p): string => e(App::url($dir . $p));
        if ($stem !== $path && is_file($disk . $stem . '-640.jpg')) {
            $webp = is_file($disk . $stem . '.webp') && is_file($disk . $stem . '-640.webp')
                ? '<source type="image/webp" srcset="' . $url($stem . '-640.webp') . ' 640w, ' . $url($stem . '.webp') . ' 1600w" sizes="' . e($sizes) . '">'
                : '';
            return '<picture>' . $webp . '<img src="' . $url($path) . '" srcset="' . $url($stem . '-640.jpg') . ' 640w, ' . $url($path) . ' 1600w" sizes="' . e($sizes) . '" alt="' . e($alt) . '"' . $lazy . $fail . '></picture>';
        }
        return '<img src="' . $url($path) . '" alt="' . e($alt) . '"' . $lazy . $fail . '>';
    }
    return '<img src="' . e($src) . '" alt="' . e($alt) . '"' . $lazy . $fail . '>';
}

/** Listings keep a JSON array of photos; the homepage only ever wants the first. */
function first_photo(?string $json): string
{
    $a = json_decode((string) $json, true);
    return is_array($a) && $a ? (string) reset($a) : '';
}
