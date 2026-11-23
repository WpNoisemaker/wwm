<?php
declare(strict_types=1);

/**
 * The managed service on Premium and High Society: our team sets up the couple's page and keeps it
 * updated. The couple sends change requests from their dashboard; staff see them in HQ → Requests,
 * open the couple's dashboard to make the change ("manage this page"), and mark the request done.
 */
final class Managed
{
    public static function table(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        Db::run("CREATE TABLE IF NOT EXISTS page_requests (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT, wedding_id INT UNSIGNED NOT NULL, user_id INT UNSIGNED NULL,
            body TEXT NOT NULL, files TEXT NULL, status ENUM('open','done') NOT NULL DEFAULT 'open', reply TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, done_at DATETIME NULL,
            PRIMARY KEY (id), KEY ix_wed (wedding_id, status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    /** Premium and High Society pages have the service. */
    public static function has(array $w): bool
    {
        return in_array((string) $w['plan'], ['premium', 'society'], true);
    }

    /** The wedding a staff member is managing right now, if any. */
    public static function managing(): int
    {
        return !empty($_SESSION['manage']) && Staff::can('weddings') ? (int) $_SESSION['manage'] : 0;
    }

    public static function forWedding(int $wid, int $n = 10): array
    {
        self::table();
        return Db::all('SELECT * FROM page_requests WHERE wedding_id = ? ORDER BY id DESC LIMIT ?', [$wid, $n]);
    }

    public static function openCount(): int
    {
        try {
            self::table();
            return (int) Db::val("SELECT COUNT(*) FROM page_requests WHERE status = 'open'", [], 0);
        } catch (Throwable $e) {
            return 0;
        }
    }

    public static function add(array $w, int $uid, string $body, array $files): void
    {
        self::table();
        Db::insert('page_requests', ['wedding_id' => $w['id'], 'user_id' => $uid, 'body' => mb_substr($body, 0, 4000), 'files' => json_encode($files, JSON_UNESCAPED_SLASHES)]);
        $to = Site::setting('email', '');
        if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $names = Wedding::names($w);
            App::later(static fn () => Mailer::send($to, 'Change request from ' . $names,
                $names . ' (' . Site::tier((string) $w['plan']) . ") asked for a change to their page:\n\n" . $body . "\n\nOpen it in HQ: " . App::url('/hq/requests'),
                ['button' => ['Open the request', App::url('/hq/requests')], 'quote' => $body]));
        }
    }
}
