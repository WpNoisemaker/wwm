<?php
declare(strict_types=1);

/**
 * Staff and what each of them may do. The owner can do everything and is the only one who adds
 * staff or changes what they can reach. Each staff member gets exactly the areas ticked for them,
 * and the owner can change that at any time.
 */
final class Staff
{
    /** Area => [name, what it lets them do]. */
    public const PERMS = [
        'weddings' => ['Wedding pages', 'See every wedding page and give each one its Google Drive folder'],
        'people'   => ['People', 'See every account'],
        'money'    => ['Money & payouts', 'See payments and payout requests'],
        'blog'     => ['Blog', 'Write, edit, schedule and publish posts'],
        'content'  => ['Site text & images', 'Change the words and pictures across the site'],
        'market'   => ['Marketplace', 'Sellers, their listings and the verified badge'],
        'settings' => ['Settings', 'Prices, exchange rates, Google Drive and email'],
    ];

    /** What a staff member who was added before areas existed can reach: everything but settings. */
    private const LEGACY = ['weddings', 'people', 'money', 'blog', 'content', 'market'];

    public static function table(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        Db::run("CREATE TABLE IF NOT EXISTS staff_perms (
            user_id    INT UNSIGNED NOT NULL,
            perms      VARCHAR(255) NOT NULL DEFAULT '',
            updated_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id),
            CONSTRAINT staff_perms_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $done = true;
    }

    /** @return list<string> */
    public static function perms(int $userId): array
    {
        self::table();
        $row = Db::val('SELECT perms FROM staff_perms WHERE user_id = ?', [$userId]);
        if ($row === null) {
            return self::LEGACY;
        }
        return array_values(array_intersect(array_keys(self::PERMS), explode(',', (string) $row)));
    }

    public static function set(int $userId, array $perms): void
    {
        self::table();
        $perms = array_values(array_intersect(array_keys(self::PERMS), $perms));
        Db::run('INSERT INTO staff_perms (user_id, perms) VALUES (?, ?) ON DUPLICATE KEY UPDATE perms = VALUES(perms)', [$userId, implode(',', $perms)]);
    }

    /** May the signed-in person do this? The owner always may. */
    public static function can(string $perm): bool
    {
        if (Auth::is('owner')) {
            return true;
        }
        if (!Auth::is('staff')) {
            return false;
        }
        return in_array($perm, self::perms((int) Auth::id()), true);
    }

    /** @return array<int,array<string,mixed>> */
    public static function all(): array
    {
        self::table();
        return Db::all("SELECT u.id, u.name, u.email, u.created_at, u.last_seen_at FROM users u WHERE u.role = 'staff' ORDER BY u.name, u.id");
    }
}
