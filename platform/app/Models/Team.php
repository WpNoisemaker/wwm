<?php
declare(strict_types=1);

/**
 * People a couple brings in to help with their wedding page, each with only the access they need:
 *  - an event planner, with the parts of the dashboard the couple ticks;
 *  - a bouncer, who sees the guest list at the door and checks guests in and out;
 *  - a vendor, who replies to the reviews guests left for them on the wedding page.
 * The couple invites by email. The invite link signs the person in (or makes them an account) and
 * ties them to that wedding until the couple removes them.
 */
final class Team
{
    public const ROLES = [
        'planner' => 'Event planner',
        'bouncer' => 'Door (bouncer)',
        'vendor'  => 'Vendor',
    ];

    /** What a planner can be given. Bank details, payouts and the couple's own settings stay with the couple. */
    public const AREAS = [
        'page'     => 'Page, story, design and sections',
        'programs' => 'Programme',
        'squad'    => 'Squad',
        'registry' => 'Registry',
        'quiz'     => 'Quiz',
        'guests'   => 'Guests: approve RSVPs and check people in',
        'vendors'  => 'Vendors listed on the page',
        'money'    => 'Money: see gifts and sprays (no payouts)',
    ];

    /** Dashboard address → the area it belongs to. Anything not listed is for the couple only. */
    private const PATHS = [
        '/account'             => 'overview',
        '/account/page'        => 'page',
        '/account/story'       => 'page',
        '/account/sections'    => 'page',
        '/account/programs'    => 'programs',
        '/account/squad'       => 'squad',
        '/account/registry'    => 'registry',
        '/account/quiz'        => 'quiz',
        '/account/guests'      => 'guests',
        '/account/vendors'     => 'vendors',
        '/account/money'       => 'money',
    ];

    public static function table(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        Db::run("CREATE TABLE IF NOT EXISTS wedding_team (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT, wedding_id INT UNSIGNED NOT NULL,
            role ENUM('planner','bouncer','vendor') NOT NULL, name VARCHAR(160) NOT NULL DEFAULT '', email VARCHAR(190) NOT NULL,
            perms VARCHAR(255) NOT NULL DEFAULT '', vendor_id INT UNSIGNED NULL, user_id INT UNSIGNED NULL,
            token CHAR(64) NULL, status ENUM('invited','active','removed') NOT NULL DEFAULT 'invited',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, accepted_at DATETIME NULL,
            PRIMARY KEY (id), UNIQUE KEY uq_token (token), KEY ix_wed (wedding_id, status), KEY ix_user (user_id, status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    public static function forWedding(int $wid): array
    {
        self::table();
        return Db::all("SELECT t.*, v.name vendor_name FROM wedding_team t LEFT JOIN vendors v ON v.id = t.vendor_id
                        WHERE t.wedding_id = ? AND t.status <> 'removed' ORDER BY t.role, t.id", [$wid]);
    }

    /** The weddings this person helps with, newest first. */
    public static function forUser(int $uid): array
    {
        self::table();
        if ($uid <= 0) {
            return [];
        }
        return Db::all("SELECT t.*, w.slug, w.partner_a, w.partner_b, w.wed_date, v.name vendor_name FROM wedding_team t
                        JOIN weddings w ON w.id = t.wedding_id LEFT JOIN vendors v ON v.id = t.vendor_id
                        WHERE t.user_id = ? AND t.status = 'active' ORDER BY t.id DESC", [$uid]);
    }

    public static function member(int $wid, int $uid, string $role = ''): ?array
    {
        self::table();
        if ($uid <= 0) {
            return null;
        }
        return Db::one("SELECT * FROM wedding_team WHERE wedding_id = ? AND user_id = ? AND status = 'active'" . ($role !== '' ? ' AND role = ?' : '') . ' ORDER BY id LIMIT 1',
            $role !== '' ? [$wid, $uid, $role] : [$wid, $uid]);
    }

    /** A new invite: the email goes out with a link only this person has. Returns an error, or ''. */
    public static function invite(array $w, string $role, string $name, string $email, array $perms, ?int $vendorId): string
    {
        self::table();
        if (!isset(self::ROLES[$role])) {
            return 'Choose what they will do.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Put their email address. The invite goes there.';
        }
        if ($role === 'vendor' && (!$vendorId || !Db::val('SELECT 1 FROM wedding_vendors WHERE wedding_id = ? AND vendor_id = ?', [$w['id'], $vendorId]))) {
            return 'Pick the vendor from the ones on your page. Add them under Vendors first.';
        }
        if (Db::val("SELECT 1 FROM wedding_team WHERE wedding_id = ? AND email = ? AND role = ? AND status <> 'removed'", [$w['id'], $email, $role])) {
            return 'You already invited ' . $email . ' for this.';
        }
        $perms = $role === 'planner' ? array_values(array_intersect(array_keys(self::AREAS), $perms)) : [];
        $token = bin2hex(random_bytes(24));
        Db::insert('wedding_team', [
            'wedding_id' => $w['id'], 'role' => $role, 'name' => mb_substr($name, 0, 160), 'email' => mb_substr($email, 0, 190),
            'perms' => implode(',', $perms), 'vendor_id' => $role === 'vendor' ? $vendorId : null, 'token' => hash('sha256', $token),
        ]);
        $names = Wedding::names($w);
        $link  = App::url('/team/join/' . $token);
        $what  = match ($role) {
            'planner' => "help run their wedding page as their event planner",
            'bouncer' => "check guests in at the door. You will see the guest list and each guest's photo and code",
            default   => "reply to the reviews their guests leave for you on their wedding page",
        };
        Mailer::send($email, $names . ' invited you to their wedding page', "Hello" . ($name !== '' ? ' ' . trim(explode(' ', $name)[0]) : '') . ",\n\n{$names} invited you to {$what}.\n\nOpen the invite to accept. You sign in, or make a free account with this email, and you are in:\n{$link}\n\nIf you were not expecting this, ignore this email.",
            ['button' => ['Accept the invite', $link], 'title' => 'You are invited', 'why' => 'You get this email because ' . $names . ' invited this address to their wedding page.']);
        return '';
    }

    public static function byToken(string $token): ?array
    {
        self::table();
        return $token === '' ? null : Db::one("SELECT * FROM wedding_team WHERE token = ? AND status = 'invited'", [hash('sha256', $token)]);
    }

    public static function accept(array $t, int $uid): void
    {
        Db::update('wedding_team', ['user_id' => $uid, 'status' => 'active', 'token' => null, 'accepted_at' => date('Y-m-d H:i:s')], 'id = ?', [$t['id']]);
    }

    /* ------------------------------------------------------------ a planner working on the page */

    /** The wedding a planner opened from their Team page, while they still have access to it. */
    public static function acting(): int
    {
        $wid = (int) ($_SESSION['team_w'] ?? 0);
        if ($wid && Auth::check() && self::member($wid, (int) Auth::id(), 'planner')) {
            return $wid;
        }
        unset($_SESSION['team_w']);
        return 0;
    }

    public static function perms(int $wid): array
    {
        $m = self::member($wid, (int) Auth::id(), 'planner');
        return $m ? array_filter(explode(',', (string) $m['perms'])) : [];
    }

    /** May the planner acting now open this dashboard address? */
    public static function allows(string $path): bool
    {
        $wid = self::acting();
        if (!$wid) {
            return true;
        }
        $path = '/' . trim((string) preg_replace('~/\d+(/.*)?$~', '', $path), '/');
        $area = self::PATHS[$path] ?? (str_starts_with($path, '/account/guests') ? 'guests' : null);
        return $area === 'overview' || ($area !== null && in_array($area, self::perms($wid), true));
    }

    /** At the door: the couple, our team, a bouncer, or a planner trusted with guests. */
    public static function door(int $wid): bool
    {
        if (!Auth::check()) {
            return false;
        }
        if (Staff::can('weddings') || Managed::managing() === $wid || Db::val('SELECT 1 FROM weddings WHERE id = ? AND owner_id = ?', [$wid, Auth::id()])) {
            return true;
        }
        $m = self::member($wid, (int) Auth::id());
        return $m && ($m['role'] === 'bouncer' || ($m['role'] === 'planner' && in_array('guests', explode(',', (string) $m['perms']), true)));
    }
}
