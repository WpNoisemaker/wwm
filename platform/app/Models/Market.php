<?php
declare(strict_types=1);

/**
 * The marketplace's working parts, as the original site had them: categories HQ opens, one chat
 * per customer and seller, invoices and payment links the seller sends in the chat, reviews from
 * customers who talked to the seller, three best projects on the seller's profile, and reports.
 *
 * We never hold money. A payment link is the seller's own; the customer pays the seller directly.
 */
final class Market
{
    /** Five reports or more: the seller is paused and asked to verify. Fifteen or more: removed. */
    public const SUSPEND_AT = 5;
    public const REMOVE_AT = 15;

    public const REASONS = [
        'scam'     => 'I think it is a scam',
        'paid'     => 'I paid and got nothing',
        'photos'   => 'The photos are fake or stolen',
        'abuse'    => 'They were rude or abusive',
        'other'    => 'Something else',
    ];

    /** The categories the site starts with. HQ adds more and opens or closes them. */
    private const START = [
        ['stay', 'Stays', 'Apartments, villas and family houses near the venue'],
        ['ride', 'Rides', 'Bridal cars, convoys, coaches and airport pick-ups'],
        ['honeymoon', 'Honeymoons', 'Flights, hotels and honeymoon packages'],
        ['asoebi', 'Aso-ebi', 'Lace, aso-oke, ankara and gele for the couple and guests'],
        ['service', 'Services', 'Help for the wedding and the weeks around it'],
        ['home', 'Home organising', 'Packing, moving and setting up the new home'],
        ['rental', 'Rentals', 'Chairs, canopies, décor and equipment to hire'],
    ];

    /** The schema this needs, on a site installed before it existed, and the starting categories. */
    public static function install(): void
    {
        Db::run("CREATE TABLE IF NOT EXISTS categories (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT, slug VARCHAR(40) NOT NULL, name VARCHAR(80) NOT NULL,
            about VARCHAR(255) NOT NULL DEFAULT '', open TINYINT(1) NOT NULL DEFAULT 1, sort SMALLINT NOT NULL DEFAULT 0,
            PRIMARY KEY (id), UNIQUE KEY uq_slug (slug)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        Db::run("CREATE TABLE IF NOT EXISTS invoices (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT, code VARCHAR(16) NOT NULL, kind ENUM('invoice','link') NOT NULL DEFAULT 'invoice',
            partner_id INT UNSIGNED NOT NULL, user_id INT UNSIGNED NOT NULL, listing_id INT UNSIGNED NULL,
            title VARCHAR(190) NOT NULL DEFAULT '', lines_json TEXT NULL, amount BIGINT NOT NULL DEFAULT 0, currency CHAR(3) NOT NULL DEFAULT 'NGN',
            pay_link VARCHAR(500) NOT NULL DEFAULT '', pay_info TEXT NULL, due_on DATE NULL, note TEXT NULL,
            status ENUM('open','paid','cancelled') NOT NULL DEFAULT 'open', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, paid_at DATETIME NULL,
            PRIMARY KEY (id), UNIQUE KEY uq_code (code), KEY ix_thread (partner_id, user_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        Db::run("CREATE TABLE IF NOT EXISTS seller_reports (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT, vendor_id INT UNSIGNED NOT NULL, user_id INT UNSIGNED NOT NULL, listing_id INT UNSIGNED NULL,
            reason VARCHAR(40) NOT NULL DEFAULT '', body TEXT NULL, status ENUM('open','cleared') NOT NULL DEFAULT 'open',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id), UNIQUE KEY uq_one (vendor_id, user_id), KEY ix_vendor (vendor_id, status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        if (stripos((string) (Db::one("SHOW COLUMNS FROM listings LIKE 'kind'")['Type'] ?? ''), 'enum') === 0) {
            Db::run("ALTER TABLE listings MODIFY kind VARCHAR(40) NOT NULL DEFAULT 'service'");
        }
        if (!Db::val("SHOW COLUMNS FROM vendors LIKE 'projects'")) {
            Db::run("ALTER TABLE vendors ADD COLUMN projects JSON NULL AFTER reviews,
                     ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active' AFTER projects, ADD COLUMN status_at DATETIME NULL AFTER status");
        }
        if (!Db::val("SHOW COLUMNS FROM messages LIKE 'invoice_id'")) {
            Db::run('ALTER TABLE messages ADD COLUMN invoice_id INT UNSIGNED NULL AFTER body');
        }
        foreach (self::START as $i => [$slug, $name, $about]) {
            Db::run('INSERT IGNORE INTO categories (slug, name, about, open, sort) VALUES (?, ?, ?, 1, ?)', [$slug, $name, $about, $i]);
        }
        // Organized by Sheedah organises homes; that has its own category now.
        $sheedah = Db::val("SELECT user_id FROM vendors WHERE slug = 'organized-by-sheedah'");
        if ($sheedah) {
            Db::run("UPDATE listings SET kind = 'home' WHERE partner_id = ? AND kind = 'service'", [$sheedah]);
            Db::run("UPDATE vendors SET category = 'Home organising' WHERE user_id = ?", [$sheedah]);
        }
        // Ẹbí Lace & Gele's best work, from their portfolio on the original site.
        $ebi = Db::one("SELECT id, projects FROM vendors WHERE slug = 'ebi-lace-gele'");
        if ($ebi && empty(json_decode((string) $ebi['projects'], true))) {
            Db::update('vendors', ['projects' => json_encode(Pack::EBI_PROJECTS, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)], 'id = ?', [$ebi['id']]);
        }
    }

    /* ------------------------------------------------------------------ categories */

    /** @return array<int,array{slug:string,name:string,about:string,open:int}> */
    public static function categories(bool $openOnly = false): array
    {
        static $all = null;
        if ($all === null) {
            try {
                $all = Db::all('SELECT c.*, (SELECT COUNT(*) FROM listings l WHERE l.kind = c.slug) listings FROM categories c ORDER BY c.sort, c.name');
            } catch (Throwable $e) {
                $all = [];
            }
        }
        return $openOnly ? array_values(array_filter($all, static fn ($c) => (int) $c['open'] === 1)) : $all;
    }

    public static function catName(string $slug): string
    {
        foreach (self::categories() as $c) {
            if ($c['slug'] === $slug) {
                return (string) $c['name'];
            }
        }
        return Listing::kindName($slug);
    }

    public static function isOpen(string $slug): bool
    {
        foreach (self::categories(true) as $c) {
            if ($c['slug'] === $slug) {
                return true;
            }
        }
        return false;
    }

    /* ------------------------------------------------------------------ sellers */

    /** The seller's business, by their user id. */
    public static function seller(int $userId): ?array
    {
        return Db::one('SELECT * FROM vendors WHERE user_id = ? ORDER BY verified DESC, id LIMIT 1', [$userId]);
    }

    public static function active(?array $v): bool
    {
        return $v !== null && (string) ($v['status'] ?? 'active') === 'active';
    }

    /** @return array<int,array{0:string,1:string,2:string}> Up to three [title, about, photo]. */
    public static function projects(array $v): array
    {
        $p = json_decode((string) ($v['projects'] ?? ''), true);
        $out = [];
        foreach (is_array($p) ? $p : [] as $x) {
            if (is_array($x) && trim((string) ($x[2] ?? '')) !== '') {
                $out[] = [(string) ($x[0] ?? ''), (string) ($x[1] ?? ''), (string) $x[2]];
            }
        }
        return array_slice($out, 0, 3);
    }

    /* ------------------------------------------------------------------ chats */

    /** Who the signed-in person is in a chat: the customer (user), the seller (partner), HQ (staff) or nobody. */
    public static function role(int $partnerId, int $userId): string
    {
        $me = (int) Auth::id();
        if ($me === 0) {
            return '';
        }
        if ($me === $userId) {
            return 'user';
        }
        if ($me === $partnerId) {
            return 'partner';
        }
        return Staff::can('market') ? 'staff' : '';
    }

    /** @return array<int,array<string,mixed>> A chat, oldest first, with each message's invoice. */
    public static function thread(int $partnerId, int $userId, int $after = 0): array
    {
        return Db::all(
            'SELECT m.*, i.code inv_code, i.kind inv_kind, i.title inv_title, i.amount inv_amount, i.currency inv_cur, i.status inv_status, i.pay_link inv_link,
                    l.title listing_title, l.slug listing_slug
             FROM messages m LEFT JOIN invoices i ON i.id = m.invoice_id LEFT JOIN listings l ON l.id = m.listing_id
             WHERE m.partner_id = ? AND m.user_id = ? AND m.id > ? ORDER BY m.id',
            [$partnerId, $userId, $after]
        );
    }

    /** @return array<int,array<string,mixed>> Every chat the person is in, newest first. */
    public static function threads(int $me): array
    {
        return Db::all(
            "SELECT t.partner_id, t.user_id, t.last_id, m.body, m.sender, m.created_at, m.invoice_id,
                    (SELECT COUNT(*) FROM messages x WHERE x.partner_id = t.partner_id AND x.user_id = t.user_id AND x.read_at IS NULL
                        AND ((t.user_id = ? AND x.sender <> 'user') OR (t.partner_id = ? AND x.sender = 'user'))) unread,
                    u.name customer, v.name seller, v.logo, v.slug seller_slug
             FROM (SELECT partner_id, user_id, MAX(id) last_id FROM messages WHERE partner_id = ? OR user_id = ? GROUP BY partner_id, user_id) t
             JOIN messages m ON m.id = t.last_id
             LEFT JOIN users u ON u.id = t.user_id
             LEFT JOIN vendors v ON v.id = (SELECT id FROM vendors WHERE user_id = t.partner_id ORDER BY verified DESC, id LIMIT 1)
             ORDER BY t.last_id DESC",
            [$me, $me, $me, $me]
        );
    }

    public static function unread(int $me): int
    {
        if ($me === 0) {
            return 0;
        }
        return (int) Db::val(
            "SELECT COUNT(*) FROM messages WHERE read_at IS NULL AND ((user_id = ? AND sender <> 'user') OR (partner_id = ? AND sender = 'user'))",
            [$me, $me], 0
        );
    }

    public static function markRead(int $partnerId, int $userId, string $role): void
    {
        if ($role === 'user') {
            Db::run("UPDATE messages SET read_at = NOW() WHERE partner_id = ? AND user_id = ? AND sender <> 'user' AND read_at IS NULL", [$partnerId, $userId]);
        } elseif ($role === 'partner') {
            Db::run("UPDATE messages SET read_at = NOW() WHERE partner_id = ? AND user_id = ? AND sender = 'user' AND read_at IS NULL", [$partnerId, $userId]);
        }
    }

    /** A message into a chat, and an email to the other side (one per chat every 20 minutes). */
    public static function send(int $partnerId, int $userId, string $sender, string $body, ?int $listingId = null, ?int $invoiceId = null): int
    {
        $last = Db::one('SELECT sender, created_at FROM messages WHERE partner_id = ? AND user_id = ? ORDER BY id DESC LIMIT 1', [$partnerId, $userId]);
        $id = (int) Db::insert('messages', [
            'partner_id' => $partnerId, 'user_id' => $userId, 'listing_id' => $listingId, 'sender' => $sender,
            'body' => mb_substr($body, 0, 4000), 'invoice_id' => $invoiceId,
        ]);
        $quiet = $last && (string) $last['sender'] === $sender && strtotime((string) $last['created_at']) > time() - 1200;
        if (!$quiet || $invoiceId) {
            $to = $sender === 'user' ? $partnerId : $userId;
            $who = $sender === 'user' ? (string) Db::val('SELECT name FROM users WHERE id = ?', [$userId], 'A customer')
                : ($sender === 'staff' ? 'Our team' : (string) (self::seller($partnerId)['name'] ?? 'The seller'));
            $url = App::url('/account/messages/' . $partnerId . '-' . $userId);
            App::later(static function () use ($to, $who, $body, $url, $invoiceId): void {
                $u = Db::one('SELECT email, name FROM users WHERE id = ?', [$to]);
                if (!$u || str_ends_with((string) $u['email'], '@example.com')) {
                    return;
                }
                Mailer::send((string) $u['email'], ($invoiceId ? $who . ' sent you an invoice' : 'New message from ' . $who),
                    "Hello " . ($u['name'] ?: 'there') . ",\n\n" . $who . ($invoiceId ? " sent you an invoice:\n\n" : " wrote:\n\n") . mb_strimwidth($body, 0, 600, '…')
                    . "\n\nReply here: " . $url . "\n\nWe never hold money for sellers. Pay only through the seller's own invoice or link, and be sure who you are paying.",
                    ['button' => [$invoiceId ? 'See it in the chat' : 'Read and reply', $url], 'quote' => mb_strimwidth($body, 0, 600, '…'), 'why' => 'You get this email because of a chat on the Wedding Websites & More marketplace.']);
            });
        }
        return $id;
    }

    /* ------------------------------------------------------------------ invoices */

    public static function newCode(): string
    {
        do {
            $c = strtoupper(substr(str_replace(['0', 'O', '1', 'I', 'L'], '', base_convert(bin2hex(random_bytes(6)), 16, 36)), 0, 8));
        } while (strlen($c) < 6 || Db::val('SELECT 1 FROM invoices WHERE code = ?', [$c]));
        return $c;
    }

    public static function invoice(string $code): ?array
    {
        return Db::one('SELECT * FROM invoices WHERE code = ?', [strtoupper($code)]);
    }

    /** @return array<int,array{0:string,1:int}> [what, amount] */
    public static function lines(array $inv): array
    {
        $l = json_decode((string) ($inv['lines_json'] ?? ''), true);
        return is_array($l) ? array_values(array_filter(array_map(static fn ($x) => is_array($x) ? [(string) ($x[0] ?? ''), (int) ($x[1] ?? 0)] : null, $l))) : [];
    }

    /** A payment link that is safe to send someone to: https only. */
    public static function payLink(string $v): string
    {
        $v = trim($v);
        if ($v !== '' && !preg_match('~^https?://~i', $v)) {
            $v = 'https://' . $v;
        }
        return filter_var($v, FILTER_VALIDATE_URL) && preg_match('~^https://~i', $v) && str_contains((string) parse_url($v, PHP_URL_HOST), '.') ? mb_substr($v, 0, 500) : '';
    }

    /* ------------------------------------------------------------------ reviews */

    /** Only customers who have written to the seller here can review them. */
    public static function canReview(array $v, int $me): bool
    {
        return $me > 0 && (int) $v['user_id'] !== $me
            && (bool) Db::val("SELECT 1 FROM messages WHERE partner_id = ? AND user_id = ? AND sender = 'user' LIMIT 1", [(int) $v['user_id'], $me]);
    }

    public static function review(array $v, int $me, int $stars, string $body): void
    {
        $name = (string) Db::val('SELECT name FROM users WHERE id = ?', [$me], '');
        $old = Db::val('SELECT id FROM reviews WHERE vendor_id = ? AND user_id = ?', [$v['id'], $me]);
        $row = ['stars' => max(1, min(5, $stars)), 'body' => mb_substr($body, 0, 2000), 'name' => mb_substr($name, 0, 160), 'status' => 'live'];
        if ($old) {
            Db::update('reviews', $row, 'id = ?', [$old]);
        } else {
            Db::insert('reviews', $row + ['vendor_id' => $v['id'], 'user_id' => $me]);
        }
        self::rate((int) $v['id']);
    }

    public static function rate(int $vendorId): void
    {
        $r = Db::one("SELECT COUNT(*) n, COALESCE(AVG(stars), 0) a FROM reviews WHERE vendor_id = ? AND status = 'live'", [$vendorId]);
        Db::update('vendors', ['rating' => round((float) $r['a'], 2), 'reviews' => (int) $r['n']], 'id = ?', [$vendorId]);
    }

    /* ------------------------------------------------------------------ reports */

    /** A report. Returns false if this person had already reported this seller. */
    public static function report(array $v, int $me, string $reason, string $body, ?int $listingId): bool
    {
        if (Db::val('SELECT 1 FROM seller_reports WHERE vendor_id = ? AND user_id = ?', [$v['id'], $me])) {
            return false;
        }
        Db::insert('seller_reports', [
            'vendor_id' => $v['id'], 'user_id' => $me, 'listing_id' => $listingId,
            'reason' => isset(self::REASONS[$reason]) ? $reason : 'other', 'body' => mb_substr($body, 0, 2000),
        ]);
        self::weigh((int) $v['id']);
        return true;
    }

    public static function reports(int $vendorId): int
    {
        return (int) Db::val("SELECT COUNT(*) FROM seller_reports WHERE vendor_id = ? AND status = 'open'", [$vendorId], 0);
    }

    /**
     * What the reports add up to. From five the seller is paused and asked to verify their business
     * by hand; from fifteen they are removed from the site, for HQ to delete. HQ can reverse either.
     */
    public static function weigh(int $vendorId): void
    {
        $v = Db::one('SELECT * FROM vendors WHERE id = ?', [$vendorId]);
        if (!$v) {
            return;
        }
        $n = self::reports($vendorId);
        $now = (string) ($v['status'] ?? 'active');
        $to = $n >= self::REMOVE_AT ? 'removed' : ($n >= self::SUSPEND_AT ? 'suspended' : $now);
        if ($to === $now || ($now === 'removed' && $to !== 'removed')) {
            return;
        }
        self::setStatus($v, $to);
    }

    public static function setStatus(array $v, string $to): void
    {
        Db::update('vendors', ['status' => $to, 'status_at' => date('Y-m-d H:i:s'), 'verified' => $to === 'active' ? (int) $v['verified'] : 0], 'id = ?', [$v['id']]);
        $u = $v['user_id'] ? Db::one('SELECT email, name FROM users WHERE id = ?', [$v['user_id']]) : null;
        if (!$u || str_ends_with((string) $u['email'], '@example.com')) {
            return;
        }
        $name = (string) $v['name'];
        $mail = match ($to) {
            'suspended' => ['Your shop is paused while we verify ' . $name,
                "Hello,\n\nWe have paused " . $name . " on Wedding Websites & More while we verify the business by hand. Your listings are hidden until then.\n\nTo verify, reply to this email with: your full name, a phone number we can call, your business address, and a photo of a government ID or your CAC certificate. We will look at it and let you know.\n\nThe Wedding Websites & More team"],
            'removed' => ['Your shop has been removed', "Hello,\n\n" . $name . " has been removed from Wedding Websites & More. If you believe this is a mistake, reply to this email.\n\nThe Wedding Websites & More team"],
            'active' => ['Your shop is live again', "Hello,\n\n" . $name . " is live again on Wedding Websites & More. Thank you for your patience.\n\nThe Wedding Websites & More team"],
            default => null,
        };
        if ($mail) {
            $btn = $to === 'active' ? ['button' => ['Open your shop', App::url('/account/shop')]] : [];
            App::later(static fn () => Mailer::send((string) $u['email'], $mail[0], $mail[1], $btn));
        }
    }

    /* ------------------------------------------------------------------ Premium sellers */

    /** Fonts a Premium seller can choose for their invoices: key => [name, CSS family, Google Fonts family or '']. */
    public const FONTS = [
        'urbanist'   => ['Urbanist (our font)', '"Urbanist", system-ui, sans-serif', ''],
        'jakarta'    => ['Plus Jakarta Sans', '"Plus Jakarta Sans", system-ui, sans-serif', 'Plus+Jakarta+Sans:wght@400;600'],
        'jost'       => ['Jost', '"Jost", system-ui, sans-serif', 'Jost:wght@300;400;500;600'],
        'playfair'   => ['Playfair Display', '"Playfair Display", Georgia, serif', 'Playfair+Display:wght@500;700'],
        'cormorant'  => ['Cormorant Garamond', '"Cormorant Garamond", Georgia, serif', 'Cormorant+Garamond:wght@500;700'],
        'cinzel'     => ['Cinzel', '"Cinzel", Georgia, serif', 'Cinzel:wght@500;700'],
        'dmserif'    => ['DM Serif Display', '"DM Serif Display", Georgia, serif', 'DM+Serif+Display'],
        'greatvibes' => ['Great Vibes (script)', '"Great Vibes", cursive', 'Great+Vibes'],
        'marcellus'  => ['Marcellus', '"Marcellus", Georgia, serif', 'Marcellus'],
        'montserrat' => ['Montserrat', '"Montserrat", system-ui, sans-serif', 'Montserrat:wght@400;600;700'],
        'lora'       => ['Lora', '"Lora", Georgia, serif', 'Lora:wght@400;600'],
    ];

    public static function premium(?array $v): bool
    {
        return $v !== null && (int) ($v['premium'] ?? 0) === 1;
    }

    /** A seller's saved invoice look (whether or not it is switched on). */
    public static function look(array $v): array
    {
        $b = json_decode((string) ($v['brand'] ?? ''), true) ?: [];
        $hex = static fn ($x, string $d): string => is_string($x) && preg_match('/^#[0-9a-f]{6}$/i', $x) ? strtoupper($x) : $d;
        $font = isset(self::FONTS[$b['font'] ?? '']) ? (string) $b['font'] : 'urbanist';
        $primary = $hex($b['primary'] ?? null, '#1E242E');
        return ['primary' => $primary, 'accent' => $hex($b['accent'] ?? null, '#E9B949'), 'font' => $font,
                'logo' => (string) ($b['logo'] ?? ''), 'on' => self::textOn($primary)];
    }

    /** The look to dress a seller's invoices in: theirs if they are Premium, otherwise none. */
    public static function brand(?array $v): ?array
    {
        if (!self::premium($v)) {
            return null;
        }
        $b = self::look($v);
        $b['logo'] = $b['logo'] !== '' ? $b['logo'] : (string) $v['logo'];
        $b['css'] = self::FONTS[$b['font']][1];
        $b['gf'] = self::FONTS[$b['font']][2];
        return $b;
    }

    /** White on dark colours, ink on light ones. */
    public static function textOn(string $hex): string
    {
        $h = ltrim($hex, '#');
        [$r, $g, $bb] = [hexdec(substr($h, 0, 2)), hexdec(substr($h, 2, 2)), hexdec(substr($h, 4, 2))];
        return (0.299 * $r + 0.587 * $g + 0.114 * $bb) > 160 ? '#1A2030' : '#FFFFFF';
    }
}
