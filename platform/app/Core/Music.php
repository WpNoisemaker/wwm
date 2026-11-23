<?php
declare(strict_types=1);

/**
 * Background music for Premium and High Society pages. A couple uploads a song, or pastes a link
 * to one (a direct audio link, a Google Drive file or a Dropbox file). Either way the file is kept
 * on this server: it then loads fast, plays the same everywhere, and can be kept truly quiet on
 * iPhones, which ignore volume set by a web page unless the sound is our own.
 *
 * YouTube and Spotify links are refused with a reason: YouTube's rules forbid hidden players, and
 * Spotify plays only 30-second previews to anyone not signed in to it.
 */
final class Music
{
    public const MAX_BYTES = 15 * 1024 * 1024;
    public const VOLUMES = [10 => 'Very soft', 20 => 'Soft', 30 => 'A little louder'];

    /** The sample the demo pages play: written for this site, free to use. */
    public const SAMPLE = 'audio/demo-ivory-morning.mp3';

    /** What the couple chose, or null: ['path' => ..., 'title' => ..., 'volume' => 10|20|30]. */
    public static function forWedding(array $w): ?array
    {
        if (!Wedding::has($w, 'music')) {
            return null;
        }
        $d = Wedding::design($w);
        $path = trim((string) ($d['music'] ?? ''));
        if ($path === '-') {
            return null;   // the couple switched it off
        }
        if ($path === '' && self::isDemo((int) $w['id'])) {
            // Demo Premium and High Society pages play the sample, so everyone can hear the feature.
            $path = self::SAMPLE;
            $d['music_title'] = ($d['music_title'] ?? '') !== '' ? $d['music_title'] : 'Ivory Morning (sample music)';
        }
        if ($path === '' || !is_file(App::root() . '/public/assets/' . $path)) {
            return null;
        }
        $vol = (int) ($d['music_volume'] ?? 20);
        return ['path' => $path, 'url' => App::asset('assets/' . $path), 'title' => trim((string) ($d['music_title'] ?? '')),
                'volume' => isset(self::VOLUMES[$vol]) ? $vol : 20, 'link' => (string) ($d['music_link'] ?? '')];
    }

    private static function isDemo(int $weddingId): bool
    {
        try {
            return (bool) Db::val("SELECT 1 FROM demo_rows WHERE tbl = 'weddings' AND row_id = ?", [$weddingId]);
        } catch (Throwable $e) {
            return false;   // no demo on this site
        }
    }

    /** An uploaded song. Returns its path, or '' with $err saying why not. */
    public static function store(array $file, int $weddingId, string &$err): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file((string) $file['tmp_name'])) {
            $err = ($file['error'] ?? 0) === UPLOAD_ERR_INI_SIZE ? 'That song is bigger than this server accepts. Try one under 15 MB.' : 'The song did not upload. Try again.';
            return '';
        }
        if ((int) $file['size'] > self::MAX_BYTES) {
            $err = 'That song is over 15 MB. Try an MP3 of it.';
            return '';
        }
        $ext = self::kind((string) file_get_contents((string) $file['tmp_name'], false, null, 0, 64));
        if ($ext === '') {
            $err = 'That is not a song we can play. Use an MP3, M4A, AAC, OGG or WAV file.';
            return '';
        }
        $path = self::place($weddingId, $ext);
        if (!@move_uploaded_file((string) $file['tmp_name'], App::root() . '/public/assets/' . $path)) {
            $err = 'The song could not be saved on the server.';
            return '';
        }
        return $path;
    }

    /** A link to a song, fetched once and kept here. Returns its path, or '' with $err saying why not. */
    public static function import(string $link, int $weddingId, string &$err): string
    {
        $link = trim($link);
        if (preg_match('~(youtube\\.com|youtu\\.be)~i', $link)) {
            $err = 'YouTube does not allow its videos to play hidden in the background. Upload the song, or link to the audio file.';
            return '';
        }
        if (preg_match('~(spotify\\.com|music\\.apple\\.com|audiomack\\.com|boomplay\\.com)~i', $link)) {
            $err = 'Streaming apps only play their songs inside their own app, so they cannot play here. Upload the song, or link to the audio file.';
            return '';
        }
        $url = self::direct($link);
        if (!preg_match('~^https?://~i', $url)) {
            $err = 'Paste a full link, starting with https://';
            return '';
        }
        if (!function_exists('curl_init')) {
            $err = 'This server cannot fetch links. Upload the song instead.';
            return '';
        }
        $tmp = tempnam(sys_get_temp_dir(), 'wwm-song');
        for ($hop = 0; $hop < 6; $hop++) {
            if (!self::safe($url)) {
                $err = 'That link cannot be used.';
                @unlink($tmp);
                return '';
            }
            $fh = fopen($tmp, 'wb');
            $got = 0;
            $c = curl_init($url);
            curl_setopt_array($c, [
                CURLOPT_FILE => $fh, CURLOPT_FOLLOWLOCATION => false, CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_TIMEOUT => 90,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS, CURLOPT_USERAGENT => 'WeddingWebsitesAndMore/1.0',
                CURLOPT_NOPROGRESS => false,
                CURLOPT_PROGRESSFUNCTION => static function ($ch, $dlTotal, $dl) use (&$got): int { $got = (int) $dl; return $dl > self::MAX_BYTES ? 1 : 0; },
            ]);
            curl_exec($c);
            $st = (int) curl_getinfo($c, CURLINFO_HTTP_CODE);
            $next = (string) curl_getinfo($c, CURLINFO_REDIRECT_URL);
            $failed = curl_errno($c) !== 0;
            curl_close($c);
            fclose($fh);
            if ($st >= 300 && $st < 400 && $next !== '') {
                $url = $next;   // every hop is checked again before it is followed
                continue;
            }
            if ($got > self::MAX_BYTES) {
                $err = 'That song is over 15 MB. Try an MP3 of it.';
            } elseif ($failed || $st !== 200) {
                $err = 'That link did not give us the song (' . ($st ?: 'no answer') . '). Check it is shared with anyone who has the link, or upload the song.';
            }
            break;
        }
        $ext = $err === '' ? self::kind((string) file_get_contents($tmp, false, null, 0, 64)) : '';
        if ($err === '' && $ext === '') {
            $err = 'That link is not a song we can play. It may be a web page; link to the audio file itself, or upload the song.';
        }
        if ($err !== '') {
            @unlink($tmp);
            return '';
        }
        $path = self::place($weddingId, $ext);
        if (!@rename($tmp, App::root() . '/public/assets/' . $path) && !@copy($tmp, App::root() . '/public/assets/' . $path)) {
            $err = 'The song could not be saved on the server.';
            @unlink($tmp);
            return '';
        }
        @unlink($tmp);
        @chmod(App::root() . '/public/assets/' . $path, 0644);
        return $path;
    }

    /** Take a song off the server, if it is one of ours (never the shared sample). */
    public static function remove(string $path): void
    {
        if (preg_match('~^uploads/music/\\d+/[a-f0-9]{16}\\.(mp3|m4a|aac|ogg|wav)$~', $path)) {
            @unlink(App::root() . '/public/assets/' . $path);
        }
    }

    /** Share links to the file itself: Google Drive and Dropbox hand over the song from these. */
    public static function direct(string $url): string
    {
        if (preg_match('~drive\\.google\\.com/(?:file/d/|open\\?id=|uc\\?(?:[^ ]*&)?id=)([A-Za-z0-9_-]{10,})~', $url, $m)) {
            return 'https://drive.google.com/uc?export=download&id=' . $m[1];
        }
        if (preg_match('~^https?://(www\\.)?dropbox\\.com/~i', $url)) {
            $url = (string) preg_replace('~([?&])dl=0~', '$1raw=1', $url);
            return str_contains($url, 'raw=1') ? $url : $url . (str_contains($url, '?') ? '&' : '?') . 'raw=1';
        }
        return $url;
    }

    /** Only the open internet: never this server, its network or anything private. */
    private static function safe(string $url): bool
    {
        $p = parse_url($url);
        if (!$p || !in_array(strtolower((string) ($p['scheme'] ?? '')), ['http', 'https'], true) || empty($p['host'])) {
            return false;
        }
        $host = (string) $p['host'];
        $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : (gethostbynamel($host) ?: []);
        if (!$ips) {
            return false;
        }
        foreach ($ips as $ip) {
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return false;
            }
        }
        return true;
    }

    /** What kind of audio a file is, from its first bytes, or '' if it is not audio we play. */
    private static function kind(string $head): string
    {
        return match (true) {
            str_starts_with($head, 'ID3'), (strlen($head) > 1 && ord($head[0]) === 0xFF && (ord($head[1]) & 0xE6) === 0xE2) => 'mp3',
            strlen($head) > 1 && ord($head[0]) === 0xFF && (ord($head[1]) & 0xF6) === 0xF0 => 'aac',
            substr($head, 4, 4) === 'ftyp' => 'm4a',
            str_starts_with($head, 'OggS') => 'ogg',
            str_starts_with($head, 'RIFF') && substr($head, 8, 4) === 'WAVE' => 'wav',
            default => '',
        };
    }

    private static function place(int $weddingId, string $ext): string
    {
        $dir = App::root() . '/public/assets/uploads/music/' . $weddingId;
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return 'uploads/music/' . $weddingId . '/' . bin2hex(random_bytes(8)) . '.' . $ext;
    }
}
