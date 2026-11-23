<?php
declare(strict_types=1);

/**
 * Guests' photos, kept in Google Drive. HQ connects the platform's Google account once, then gives
 * each wedding page its own folder (one tap creates it, or paste a link to one). Every photo a
 * guest adds goes to that folder at full size; the wedding page keeps small copies so it stays
 * fast. A photo waits on the server until its folder exists, is sent after the page has gone out,
 * is tried again if Google is unreachable, and is removed from the server once it is safely in Drive.
 *
 * Plain HTTPS calls to Google's own endpoints: no library to install on shared hosting.
 */
final class Drive
{
    private const AUTH  = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN = 'https://oauth2.googleapis.com/token';
    private const API   = 'https://www.googleapis.com/drive/v3';
    private const UP    = 'https://www.googleapis.com/upload/drive/v3/files';

    public static function tables(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        Db::run("CREATE TABLE IF NOT EXISTS drive_folders (
            wedding_id INT UNSIGNED NOT NULL,
            folder_id  VARCHAR(128) NOT NULL,
            name       VARCHAR(255) NOT NULL DEFAULT '',
            link       VARCHAR(255) NOT NULL DEFAULT '',
            created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (wedding_id),
            CONSTRAINT drive_folders_wedding FOREIGN KEY (wedding_id) REFERENCES weddings (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        Db::run("CREATE TABLE IF NOT EXISTS drive_queue (
            id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
            wedding_id INT UNSIGNED NOT NULL,
            wall_id    INT UNSIGNED NULL,
            file       VARCHAR(255) NOT NULL,
            name       VARCHAR(255) NOT NULL DEFAULT '',
            mime       VARCHAR(60)  NOT NULL DEFAULT 'image/jpeg',
            state      ENUM('waiting','sent','failed') NOT NULL DEFAULT 'waiting',
            tries      TINYINT UNSIGNED NOT NULL DEFAULT 0,
            drive_id   VARCHAR(128) NOT NULL DEFAULT '',
            error      VARCHAR(255) NOT NULL DEFAULT '',
            created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            sent_at    DATETIME     NULL,
            PRIMARY KEY (id),
            KEY waiting (state, wedding_id),
            CONSTRAINT drive_queue_wedding FOREIGN KEY (wedding_id) REFERENCES weddings (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $done = true;
    }

    /* -------------------------------------------------------------------- connecting Google */

    public static function configured(): bool
    {
        return Site::setting('drive_client_id') !== '' && Site::setting('drive_client_secret') !== '';
    }

    public static function connected(): bool
    {
        return self::configured() && Site::setting('drive_refresh') !== '';
    }

    /** Full access lets HQ use folders made by hand in Drive; otherwise the platform makes them. */
    public static function full(): bool
    {
        return Site::setting('drive_scope') === 'full';
    }

    public static function redirect(): string
    {
        return App::url('/hq/drive/callback');
    }

    public static function authUrl(): string
    {
        Guard::boot();
        $_SESSION['drive_state'] = bin2hex(random_bytes(16));
        return self::AUTH . '?' . http_build_query([
            'client_id' => Site::setting('drive_client_id'), 'redirect_uri' => self::redirect(), 'response_type' => 'code',
            'scope' => 'openid email ' . (self::full() ? 'https://www.googleapis.com/auth/drive' : 'https://www.googleapis.com/auth/drive.file'),
            'access_type' => 'offline', 'prompt' => 'consent', 'include_granted_scopes' => 'true', 'state' => $_SESSION['drive_state'],
        ]);
    }

    /** Google sends HQ back here with a one-time code; swap it for a token that lasts. */
    public static function finish(string $code, string $state): string
    {
        Guard::boot();
        if ($state === '' || !hash_equals((string) ($_SESSION['drive_state'] ?? ''), $state)) {
            return 'That sign-in did not come from this site. Try connecting again.';
        }
        unset($_SESSION['drive_state']);
        [$st, $body] = self::http('POST', self::TOKEN, ['Content-Type: application/x-www-form-urlencoded'], http_build_query([
            'code' => $code, 'client_id' => Site::setting('drive_client_id'), 'client_secret' => Site::setting('drive_client_secret'),
            'redirect_uri' => self::redirect(), 'grant_type' => 'authorization_code',
        ]));
        $j = json_decode($body, true) ?: [];
        if ($st !== 200 || empty($j['refresh_token'])) {
            return 'Google did not hand over access (' . ($j['error_description'] ?? $j['error'] ?? 'HTTP ' . $st) . ').';
        }
        self::put('drive_refresh', (string) $j['refresh_token']);
        self::put('drive_access', (string) ($j['access_token'] ?? ''));
        self::put('drive_access_exp', (string) (time() + (int) ($j['expires_in'] ?? 3000) - 120));
        [, $me] = self::api('GET', self::API . '/about?fields=user(emailAddress)');
        self::put('drive_email', (string) (json_decode($me, true)['user']['emailAddress'] ?? ''));
        return '';
    }

    public static function disconnect(): void
    {
        foreach (['drive_refresh', 'drive_access', 'drive_access_exp', 'drive_email'] as $k) {
            self::put($k, '');
        }
    }

    /* ------------------------------------------------------------------ folders per wedding */

    public static function folder(int $weddingId): ?array
    {
        self::tables();
        return Db::one('SELECT * FROM drive_folders WHERE wedding_id = ?', [$weddingId]);
    }

    /** Make a folder for a wedding, inside the parent folder HQ chose (if any). */
    public static function create(array $w): string
    {
        $name = Wedding::names($w) . ' · ' . ((string) $w['wed_date'] !== '' ? date('j M Y', (int) strtotime((string) $w['wed_date'])) : 'Wedding') . ' · guest photos';
        $meta = ['name' => $name, 'mimeType' => 'application/vnd.google-apps.folder'];
        $parent = Site::setting('drive_parent');
        if ($parent !== '') {
            $meta['parents'] = [$parent];
        }
        [$st, $body] = self::api('POST', self::API . '/files?supportsAllDrives=true&fields=id,name,webViewLink', ['Content-Type: application/json'], (string) json_encode($meta));
        $j = json_decode($body, true) ?: [];
        if ($st !== 200 || empty($j['id'])) {
            return 'Google would not make the folder (' . ($j['error']['message'] ?? 'HTTP ' . $st) . ').';
        }
        self::assign((int) $w['id'], (string) $j['id'], (string) $j['name'], (string) ($j['webViewLink'] ?? ''));
        return '';
    }

    /** Give a wedding a folder that already exists, from its link or its id. */
    public static function attach(array $w, string $linkOrId): string
    {
        $id = self::idFrom($linkOrId);
        if ($id === '') {
            return 'Paste the folder\'s link from Google Drive.';
        }
        [$st, $body] = self::api('GET', self::API . '/files/' . rawurlencode($id) . '?supportsAllDrives=true&fields=id,name,mimeType,webViewLink');
        $j = json_decode($body, true) ?: [];
        if ($st !== 200 || ($j['mimeType'] ?? '') !== 'application/vnd.google-apps.folder') {
            return $st === 404
                ? 'The platform cannot see that folder.' . (self::full() ? ' Check that it is in the connected Google account.' : ' Use "Make a folder", or turn on full Drive access in Settings to use folders you made yourself.')
                : 'That link is not a Drive folder.';
        }
        self::assign((int) $w['id'], (string) $j['id'], (string) $j['name'], (string) ($j['webViewLink'] ?? ''));
        return '';
    }

    private static function assign(int $weddingId, string $id, string $name, string $link): void
    {
        self::tables();
        Db::run('INSERT INTO drive_folders (wedding_id, folder_id, name, link) VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE folder_id = VALUES(folder_id), name = VALUES(name), link = VALUES(link)',
            [$weddingId, $id, $name, $link !== '' ? $link : 'https://drive.google.com/drive/folders/' . $id]);
        App::later(static function () use ($weddingId): void { self::sync(50, $weddingId); });
    }

    public static function idFrom(string $s): string
    {
        $s = trim($s);
        if (preg_match('~/folders/([A-Za-z0-9_-]{10,})~', $s, $m) || preg_match('~[?&]id=([A-Za-z0-9_-]{10,})~', $s, $m)) {
            return $m[1];
        }
        return preg_match('~^[A-Za-z0-9_-]{10,}$~', $s) ? $s : '';
    }

    /* ----------------------------------------------------------------------- guest photos */

    /** Keep the original a guest sent, to go to Drive. Called before the upload is processed. */
    public static function hold(array $file, int $weddingId): ?array
    {
        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return null;
        }
        $info = @getimagesize($tmp);
        $type = [IMAGETYPE_JPEG => ['jpg', 'image/jpeg'], IMAGETYPE_PNG => ['png', 'image/png'], IMAGETYPE_WEBP => ['webp', 'image/webp']][$info[2] ?? 0] ?? null;
        if (!$type) {
            return null;
        }
        $dir = App::root() . '/storage/drive/' . $weddingId;
        if (!is_dir($dir) && !@mkdir($dir, 0750, true)) {
            return null;
        }
        $path = $dir . '/' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $type[0];
        return @copy($tmp, $path) ? ['path' => $path, 'mime' => $type[1]] : null;
    }

    public static function queue(array $w, ?int $wallId, array $held, string $guest): void
    {
        self::tables();
        $name = preg_replace('/[^\\p{L}\\p{N} ._-]+/u', '', $guest) ?: 'Guest';
        Db::insert('drive_queue', [
            'wedding_id' => (int) $w['id'], 'wall_id' => $wallId, 'file' => $held['path'], 'mime' => $held['mime'],
            'name' => mb_substr($name, 0, 60) . ' · ' . date('j M Y, H.i') . '.' . pathinfo($held['path'], PATHINFO_EXTENSION),
        ]);
        $id = (int) $w['id'];
        App::later(static function () use ($id): void { self::sync(5, $id); });
    }

    /** Send waiting photos to their wedding's folder. Returns how many went. */
    public static function sync(int $max = 20, int $weddingId = 0): int
    {
        if (!self::connected()) {
            return 0;
        }
        self::tables();
        $rows = Db::all("SELECT q.*, f.folder_id FROM drive_queue q JOIN drive_folders f ON f.wedding_id = q.wedding_id
                         WHERE q.state = 'waiting'" . ($weddingId ? ' AND q.wedding_id = ' . (int) $weddingId : '') . ' ORDER BY q.id LIMIT ' . max(1, $max));
        $sent = 0;
        foreach ($rows as $r) {
            if (!is_file((string) $r['file'])) {
                Db::update('drive_queue', ['state' => 'failed', 'error' => 'The file was no longer on the server.'], 'id = ?', [$r['id']]);
                continue;
            }
            $err = '';
            $id = self::upload((string) $r['file'], (string) $r['name'], (string) $r['mime'], (string) $r['folder_id'], $err);
            if ($id !== '') {
                Db::update('drive_queue', ['state' => 'sent', 'drive_id' => $id, 'sent_at' => date('Y-m-d H:i:s'), 'error' => ''], 'id = ?', [$r['id']]);
                @unlink((string) $r['file']);
                $sent++;
            } else {
                $tries = (int) $r['tries'] + 1;
                Db::update('drive_queue', ['tries' => $tries, 'error' => mb_substr($err, 0, 250), 'state' => $tries >= 6 ? 'failed' : 'waiting'], 'id = ?', [$r['id']]);
            }
        }
        return $sent;
    }

    /** @return array{waiting:int,sent:int,failed:int} */
    public static function counts(int $weddingId = 0): array
    {
        self::tables();
        $out = ['waiting' => 0, 'sent' => 0, 'failed' => 0];
        foreach (Db::all('SELECT state, COUNT(*) n FROM drive_queue' . ($weddingId ? ' WHERE wedding_id = ' . (int) $weddingId : '') . ' GROUP BY state') as $r) {
            $out[(string) $r['state']] = (int) $r['n'];
        }
        return $out;
    }

    /** Try failed photos again (HQ's button), for example after reconnecting. */
    public static function retry(): void
    {
        self::tables();
        Db::run("UPDATE drive_queue SET state = 'waiting', tries = 0 WHERE state = 'failed'");
    }

    private static function upload(string $file, string $name, string $mime, string $folder, string &$err): string
    {
        $b = 'wwm' . bin2hex(random_bytes(8));
        $meta = (string) json_encode(['name' => $name, 'parents' => [$folder]]);
        $body = "--$b\r\nContent-Type: application/json; charset=UTF-8\r\n\r\n$meta\r\n--$b\r\nContent-Type: $mime\r\n\r\n" . (string) file_get_contents($file) . "\r\n--$b--";
        [$st, $res] = self::api('POST', self::UP . '?uploadType=multipart&supportsAllDrives=true&fields=id', ['Content-Type: multipart/related; boundary=' . $b], $body);
        $j = json_decode($res, true) ?: [];
        if ($st === 200 && !empty($j['id'])) {
            return (string) $j['id'];
        }
        $err = (string) ($j['error']['message'] ?? 'HTTP ' . $st);
        return '';
    }

    /* ------------------------------------------------------------------------------ HTTP */

    private static function token(): string
    {
        if ((int) Site::setting('drive_access_exp', '0') > time() && Site::setting('drive_access') !== '') {
            return Site::setting('drive_access');
        }
        [$st, $body] = self::http('POST', self::TOKEN, ['Content-Type: application/x-www-form-urlencoded'], http_build_query([
            'client_id' => Site::setting('drive_client_id'), 'client_secret' => Site::setting('drive_client_secret'),
            'refresh_token' => Site::setting('drive_refresh'), 'grant_type' => 'refresh_token',
        ]));
        $j = json_decode($body, true) ?: [];
        if ($st !== 200 || empty($j['access_token'])) {
            return '';
        }
        self::put('drive_access', (string) $j['access_token']);
        self::put('drive_access_exp', (string) (time() + (int) ($j['expires_in'] ?? 3000) - 120));
        return (string) $j['access_token'];
    }

    /** @return array{0:int,1:string} */
    private static function api(string $method, string $url, array $headers = [], ?string $body = null): array
    {
        $t = self::token();
        if ($t === '') {
            return [401, '{"error":{"message":"Google Drive is not connected, or its access was withdrawn. Reconnect it in HQ Settings."}}'];
        }
        return self::http($method, $url, array_merge($headers, ['Authorization: Bearer ' . $t]), $body);
    }

    /** @return array{0:int,1:string} */
    private static function http(string $method, string $url, array $headers = [], ?string $body = null): array
    {
        if (!function_exists('curl_init')) {
            return [0, '{"error":{"message":"This server cannot make web requests (PHP curl is missing)."}}'];
        }
        $c = curl_init($url);
        curl_setopt_array($c, [CURLOPT_CUSTOMREQUEST => $method, CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => $headers,
                               CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_TIMEOUT => 120]);
        if ($body !== null) {
            curl_setopt($c, CURLOPT_POSTFIELDS, $body);
        }
        $res = curl_exec($c);
        $st = (int) curl_getinfo($c, CURLINFO_HTTP_CODE);
        curl_close($c);
        return [$st, is_string($res) ? $res : ''];
    }

    private static function put(string $k, string $v): void
    {
        Db::run('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)', [$k, $v]);
    }
}
