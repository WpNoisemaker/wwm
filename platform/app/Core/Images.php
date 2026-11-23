<?php
declare(strict_types=1);

/**
 * Photos people upload. Every one is opened, turned upright, shrunk and saved again by the server,
 * so what lands on disk is always a plain picture: never the file the visitor sent, never anything
 * that could run. Each photo is kept in four copies: 1600px and 640px, each as WebP and JPEG.
 */
final class Images
{
    private const MAX_BYTES = 12 * 1024 * 1024;

    /**
     * Save an uploaded photo under public/assets/uploads/$dir. Returns the path photo() takes
     * ("uploads/wall/ab12cd.jpg"), or null when there is no usable photo.
     */
    public static function store(array $file, string $dir): ?string
    {
        $src = self::open($file);
        if (!$src) {
            return null;
        }
        $base = App::root() . '/public/assets/uploads/' . trim($dir, '/');
        if (!is_dir($base) && !@mkdir($base, 0755, true)) {
            imagedestroy($src);
            return null;
        }
        $stem = bin2hex(random_bytes(8));
        self::write($src, $base . '/' . $stem, 1600);
        self::write($src, $base . '/' . $stem . '-640', 640);
        imagedestroy($src);
        return 'uploads/' . trim($dir, '/') . '/' . $stem . '.jpg';
    }

    /**
     * A private photo (a guest's face for their entry pass): one 640px JPEG in a folder outside the
     * website, so it is only ever shown through a page that checks who is asking. Returns the file name.
     */
    public static function storeAt(array $file, string $absDir): ?string
    {
        $src = self::open($file);
        if (!$src) {
            return null;
        }
        if (!is_dir($absDir) && !@mkdir($absDir, 0750, true)) {
            imagedestroy($src);
            return null;
        }
        $stem = bin2hex(random_bytes(12));
        $w = imagesx($src);
        $h = imagesy($src);
        $k = min(1, 640 / max($w, $h));
        $dst = imagecreatetruecolor(max(1, (int) round($w * $k)), max(1, (int) round($h * $k)));
        imagecopyresampled($dst, $src, 0, 0, 0, 0, imagesx($dst), imagesy($dst), $w, $h);
        imagejpeg($dst, $absDir . '/' . $stem . '.jpg', 82);
        imagedestroy($dst);
        imagedestroy($src);
        return $stem . '.jpg';
    }

    /** Open an uploaded JPEG, PNG or WebP as a picture, the right way up. Anything else: null. */
    private static function open(array $file): ?GdImage
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file((string) $file['tmp_name'])) {
            return null;
        }
        if ((int) $file['size'] <= 0 || (int) $file['size'] > self::MAX_BYTES || !function_exists('imagecreatetruecolor')) {
            return null;
        }
        $info = @getimagesize((string) $file['tmp_name']);
        if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            return null;
        }
        $src = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg((string) $file['tmp_name']),
            IMAGETYPE_PNG  => @imagecreatefrompng((string) $file['tmp_name']),
            default        => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp((string) $file['tmp_name']) : false,
        };
        if (!$src) {
            return null;
        }
        // Phones store photos sideways and say which way up they go; follow that.
        if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $o = (int) (@exif_read_data((string) $file['tmp_name'])['Orientation'] ?? 1);
            $turn = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0;
            if ($turn !== 0) {
                $r = imagerotate($src, $turn, 0);
                if ($r) { imagedestroy($src); $src = $r; }
            }
        }
        return $src;
    }

    /** One size, as JPEG and (where the server can) WebP. */
    private static function write(GdImage $src, string $path, int $max): void
    {
        $w = imagesx($src);
        $h = imagesy($src);
        $k = min(1, $max / max($w, $h));
        $nw = max(1, (int) round($w * $k));
        $nh = max(1, (int) round($h * $k));
        $dst = imagecreatetruecolor($nw, $nh);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));   // transparent PNGs land on white
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imageinterlace($dst, true);
        imagejpeg($dst, $path . '.jpg', 80);
        if (function_exists('imagewebp')) {
            imagewebp($dst, $path . '.webp', 78);
        }
        imagedestroy($dst);
    }

    /**
     * Make the smaller and WebP copies for a picture that arrived without them (the old site's
     * uploads, or a file put on the server by hand). Runs after the page has gone out, once per
     * picture: the next visitor gets the fast copies.
     */
    public static function variants(string $file): void
    {
        $stem = (string) preg_replace('/\.(jpe?g|png|webp)$/i', '', $file);
        if ($stem === $file || is_file($stem . '-640.jpg') || !is_file($file) || !function_exists('imagecreatetruecolor')) {
            return;
        }
        if ((int) @filesize($file) > 40 * 1024 * 1024) {
            return;
        }
        $info = @getimagesize($file);
        if (!$info) {
            return;
        }
        $src = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($file),
            IMAGETYPE_PNG  => @imagecreatefrompng($file),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file) : false,
            default        => false,
        };
        if (!$src) {
            return;
        }
        self::write($src, $stem . '-640', 640);
        if (function_exists('imagewebp') && !is_file($stem . '.webp')) {
            $w = imagesx($src);
            $h = imagesy($src);
            $k = min(1, 1600 / max($w, $h));
            $dst = imagecreatetruecolor(max(1, (int) round($w * $k)), max(1, (int) round($h * $k)));
            imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
            imagecopyresampled($dst, $src, 0, 0, 0, 0, imagesx($dst), imagesy($dst), $w, $h);
            imagewebp($dst, $stem . '.webp', 78);
            imagedestroy($dst);
        }
        imagedestroy($src);
    }
}
