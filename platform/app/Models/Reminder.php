<?php
declare(strict_types=1);

/**
 * "Remind me" on a wedding's livestream. A guest leaves an email; we write to them twice, on
 * their own: the moment the couple adds the livestream link in their dashboard, and fifteen
 * minutes before it starts. Each email has a link to stop them. Nothing else is ever sent.
 *
 * Sending happens after the page has been delivered (so nobody waits for it), from the couple's
 * save, from wedding page visits at most every few minutes, and from cron.php if the host runs it.
 */
final class Reminder
{
    public static function table(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        Db::run("CREATE TABLE IF NOT EXISTS live_reminders (
            id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
            wedding_id INT UNSIGNED NOT NULL,
            email      VARCHAR(190) NOT NULL,
            token      CHAR(32)     NOT NULL,
            link_sent  CHAR(32)     NOT NULL DEFAULT '',
            soon_sent  CHAR(32)     NOT NULL DEFAULT '',
            created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY one_each (wedding_id, email),
            KEY by_token (token),
            CONSTRAINT live_reminders_wedding FOREIGN KEY (wedding_id) REFERENCES weddings (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $done = true;
    }

    public static function count(int $weddingId): int
    {
        self::table();
        return (int) Db::val('SELECT COUNT(*) FROM live_reminders WHERE wedding_id = ?', [$weddingId], 0);
    }

    /** Sign someone up. A link that is already on the page is not sent again; they can see it. */
    public static function add(array $w, string $email): bool
    {
        self::table();
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $link = self::link($w);
        Db::run('INSERT INTO live_reminders (wedding_id, email, token, link_sent) VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE email = email',
            [(int) $w['id'], $email, bin2hex(random_bytes(16)), $link !== '' ? md5($link) : '']);
        return true;
    }

    public static function stop(string $token): ?array
    {
        self::table();
        $r = Db::one('SELECT r.*, w.slug, w.partner_a, w.partner_b FROM live_reminders r JOIN weddings w ON w.id = r.wedding_id WHERE r.token = ?', [$token]);
        if ($r) {
            Db::run('DELETE FROM live_reminders WHERE id = ?', [$r['id']]);
        }
        return $r;
    }

    /** The livestream link the couple added, or ''. */
    public static function link(array $w): string
    {
        return trim((string) (Wedding::design($w)['live_url'] ?? ''));
    }

    /** When the stream starts: the time the couple set, or else the next event on the page. */
    public static function startsAt(array $w): ?int
    {
        $set = trim((string) (Wedding::design($w)['live_at'] ?? ''));
        if ($set !== '' && ($t = strtotime($set)) !== false) {
            return $t;
        }
        $next = Wedding::nextMoment($w, Wedding::programs((int) $w['id']));
        return $next ? (int) strtotime((string) $next['when']) : null;
    }

    /** Send whatever is due. Small batches, so a page view that triggers it never drags. */
    public static function run(int $max = 40): int
    {
        self::table();
        $sent = 0;
        $rows = Db::all("SELECT r.*, w.* , r.id rid, r.token rtoken FROM live_reminders r JOIN weddings w ON w.id = r.wedding_id
                         WHERE JSON_UNQUOTE(JSON_EXTRACT(w.design, '$.live_url')) <> '' LIMIT 400");
        foreach ($rows as $r) {
            if ($sent >= $max) {
                break;
            }
            $w = $r;
            $w['id'] = $r['wedding_id'];
            $link = self::link($w);
            if ($link === '') {
                continue;
            }
            $names = Wedding::names($w);
            $page  = App::url('/w/' . $w['slug'] . '#live');
            $stop  = App::url('/remind/stop/' . $r['rtoken']);
            $start = self::startsAt($w);
            $when  = $start ? date('l j F, g:ia', $start) : '';
            if ((string) $r['link_sent'] !== md5($link)) {
                Db::run('UPDATE live_reminders SET link_sent = ? WHERE id = ?', [md5($link), $r['rid']]);
                Mailer::send((string) $r['email'], $names . ': the livestream link is ready',
                    "The livestream for {$names} is ready" . ($when !== '' ? ". It starts on {$when}" : '') . ".\n\n"
                    . "Watch it on their page:\n{$page}\n\nWe will send one more reminder fifteen minutes before it starts.\n\n"
                    . "Stop these reminders: {$stop}\n\nWedding Websites & More",
                    ['button' => ['Watch the livestream', $page], 'unsub' => $stop, 'why' => 'You asked for a reminder about ' . $names . '’s livestream.']);
                $sent++;
                continue;
            }
            if ($start && time() >= $start - 20 * 60 && time() < $start + 60 * 60 && (string) $r['soon_sent'] !== md5((string) $start)) {
                Db::run('UPDATE live_reminders SET soon_sent = ? WHERE id = ?', [md5((string) $start), $r['rid']]);
                Mailer::send((string) $r['email'], $names . ': live in fifteen minutes',
                    "{$names} go live " . (time() >= $start ? 'now' : 'in about fifteen minutes') . ".\n\nWatch here:\n{$page}\n\n"
                    . "Stop these reminders: {$stop}\n\nWedding Websites & More",
                    ['button' => ['Watch now', $page], 'unsub' => $stop, 'why' => 'You asked for a reminder about ' . $names . '’s livestream.']);
                $sent++;
            }
        }
        return $sent;
    }

    /**
     * Called on wedding page views: sends reminders that are due and photos waiting for Google Drive,
     * at most every five minutes, after the page is out. cron.php does the same on a schedule.
     */
    public static function tick(): void
    {
        $last = (int) Site::setting('reminders_ran', '0');
        if (time() - $last < 300) {
            return;
        }
        Db::run("INSERT INTO settings (k, v) VALUES ('reminders_ran', ?) ON DUPLICATE KEY UPDATE v = VALUES(v)", [(string) time()]);
        App::later(static function (): void { self::run(); Drive::sync(10); });
    }
}
