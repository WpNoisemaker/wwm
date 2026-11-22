<?php
/**
 * Bring a WordPress "Wedding Websites & More" site across to the standalone platform.
 *
 * It only ever READS from WordPress. Nothing in the old database is written to, renamed or
 * dropped, so you can run this against a copy — or against the live site — without risk.
 *
 * It is idempotent: every row carries the WordPress id it came from, and re-running updates what
 * is already there rather than duplicating it. If it stops halfway, run it again.
 *
 *   php db/migrate.php --wp-host=localhost --wp-db=oldsite --wp-user=u --wp-pass=p --wp-prefix=wp_
 *   php db/migrate.php --dry-run      show what would come across, change nothing
 *   php db/migrate.php --only=users,weddings
 *
 * @package WWM
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Run this from the command line, or through your host's cron/terminal.\n");
}

$root = dirname(__DIR__);
require $root . '/app/Core/App.php';
App::boot($root);
require $root . '/app/Core/Db.php';

// ---------------------------------------------------------------- Options

$o = getopt('', [
    'wp-host::', 'wp-db::', 'wp-user::', 'wp-pass::', 'wp-port::', 'wp-prefix::',
    'only::', 'dry-run', 'help',
]);
if (isset($o['help'])) {
    exit("See the comment at the top of this file.\n");
}

$wp = [
    'host'   => $o['wp-host'] ?? 'localhost',
    'db'     => $o['wp-db'] ?? '',
    'user'   => $o['wp-user'] ?? '',
    'pass'   => $o['wp-pass'] ?? '',
    'port'   => (int) ($o['wp-port'] ?? 3306),
    'prefix' => $o['wp-prefix'] ?? 'wp_',
];
if ($wp['db'] === '') {
    exit("Which WordPress database? Pass --wp-db=name (and --wp-user, --wp-pass).\n");
}
$dry  = isset($o['dry-run']);
$only = isset($o['only']) ? array_filter(array_map('trim', explode(',', (string) $o['only']))) : [];

function want(string $step): bool
{
    global $only;
    return !$only || in_array($step, $only, true);
}

function say(string $m): void
{
    echo $m, "\n";
}

// ---------------------------------------------------------------- Connections

try {
    $src = new PDO(
        sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $wp['host'], $wp['port'], $wp['db']),
        $wp['user'],
        $wp['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (Throwable $e) {
    exit('Could not reach the WordPress database: ' . $e->getMessage() . "\n");
}
$P = $wp['prefix'];

try {
    Db::pdo();
} catch (Throwable $e) {
    exit('Could not reach the new database: ' . $e->getMessage() . "\n");
}

say($dry ? "DRY RUN — nothing will be written.\n" : "Importing from {$wp['db']} (prefix {$P}).\n");

// ---------------------------------------------------------------- Helpers

/** Does a WordPress table exist? Some sites never used a feature, so its table is simply absent. */
function has_table(PDO $src, string $t): bool
{
    static $seen = [];
    if (!isset($seen[$t])) {
        $q = $src->prepare('SHOW TABLES LIKE ?');
        $q->execute([$t]);
        $seen[$t] = (bool) $q->fetchColumn();
    }
    return $seen[$t];
}

/**
 * WordPress stores most of a wedding as one serialized PHP array in a single meta row.
 * unserialize() is given allowed_classes:false so a crafted payload can never build an object.
 */
function wp_meta(PDO $src, string $P, int $post, string $key): mixed
{
    $q = $src->prepare("SELECT meta_value FROM {$P}postmeta WHERE post_id=? AND meta_key=? LIMIT 1");
    $q->execute([$post, $key]);
    $v = $q->fetchColumn();
    if ($v === false || $v === null || $v === '') {
        return null;
    }
    // PHP serializes an array as a:5:{...} — a colon, not a semicolon. Only null is N;.
    if (is_string($v) && preg_match('/^(?:[aObsid]:|N;)/', $v)) {
        $out = @unserialize($v, ['allowed_classes' => false]);
        return $out === false ? null : $out;
    }
    return $v;
}

/**
 * Remember the mapping, so a second run updates instead of duplicating.
 *
 * On a dry run nothing is written, so the map is kept in memory instead — otherwise every step
 * after the first would find no ids and report zero, and the preview would badly understate what
 * is actually going to come across.
 */
function logged(string $kind, int $wpId, ?int $newId, string $note = ''): void
{
    global $dry, $memo;
    if ($dry) {
        $memo[$kind][$wpId] = $newId ?? -$wpId;
        return;
    }
    Db::run(
        'INSERT INTO import_log (kind, wp_id, new_id, note) VALUES (?,?,?,?)
         ON DUPLICATE KEY UPDATE new_id=VALUES(new_id), note=VALUES(note)',
        [$kind, $wpId, $newId, $note]
    );
}

function mapped(string $kind, int $wpId): ?int
{
    global $dry, $memo;
    if ($dry) {
        return $memo[$kind][$wpId] ?? null;
    }
    $v = Db::val('SELECT new_id FROM import_log WHERE kind=? AND wp_id=?', [$kind, $wpId]);
    return $v ? (int) $v : null;
}

function slugify(string $s, string $fallback = 'page'): string
{
    $s = trim(strtolower((string) preg_replace('~[^\p{L}\p{N}]++~u', '-', $s)), '-');
    return $s !== '' ? substr($s, 0, 110) : $fallback;
}

/** Keep a slug unique without ever changing one that is already in use. */
function uniq_slug(string $table, string $slug, ?int $keepId = null): string
{
    $base = $slug;
    $n = 1;
    while (true) {
        $row = Db::one("SELECT id FROM `$table` WHERE slug=? LIMIT 1", [$slug]);
        if (!$row || ($keepId && (int) $row['id'] === $keepId)) {
            return $slug;
        }
        $slug = $base . '-' . (++$n);
    }
}

function dt(?string $v): ?string
{
    return ($v && $v !== '0000-00-00 00:00:00') ? $v : null;
}

$count = [];
$memo  = [];
/**
 * Turn whatever the old theme stored for an image into something this site can render.
 *
 * The theme kept an image in three different shapes: a numeric attachment id (an uploaded photo),
 * "photo:NAME" (one of the photographs bundled with the theme), or a plain URL. Copying that across
 * untouched is what would have left every imported cover as a broken image.
 */
function img_ref(mixed $v): string
{
    $v = trim((string) $v);
    if ($v === '' || $v === '0') {
        return '';
    }
    if (ctype_digit($v)) {
        return 'wp:' . $v;                       // resolved to a real path once the media step has run
    }
    if (str_starts_with($v, 'photo:')) {
        return 'photos/' . preg_replace('/[^A-Za-z0-9_-]/', '', substr($v, 6)) . '.jpg';   // ships in assets/img/photos
    }
    return $v;                                   // a URL, a wp: reference, or an already-final path
}

/** WordPress's own featured image for a post, as an img_ref. */
function thumb_ref(PDO $src, string $P, int $post): string
{
    return img_ref(wp_meta($src, $P, $post, '_thumbnail_id') ?? '');
}

/** A vendor's type is a taxonomy term (wd_vtype) on the old site; the new site keeps it as plain text. */
function vendor_type(PDO $src, string $P, int $post): string
{
    foreach (['term_relationships', 'term_taxonomy', 'terms'] as $t) {
        if (!has_table($src, $P . $t)) {
            return '';
        }
    }
    $q = $src->prepare(
        "SELECT t.name FROM {$P}term_relationships tr
         JOIN {$P}term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = 'wd_vtype'
         JOIN {$P}terms t ON t.term_id = tt.term_id
         WHERE tr.object_id = ? ORDER BY t.term_id LIMIT 1"
    );
    $q->execute([$post]);
    // WordPress stores term names HTML-encoded ("Photography &amp; film").
    return html_entity_decode((string) ($q->fetchColumn() ?: ''), ENT_QUOTES, 'UTF-8');
}

function tally(string $k, int $n = 1): void
{
    global $count;
    $count[$k] = ($count[$k] ?? 0) + $n;
}

// ---------------------------------------------------------------- People

if (want('users')) {
    $rows = $src->query("SELECT ID, user_login, user_email, user_pass, display_name, user_registered FROM {$P}users ORDER BY ID")->fetchAll();
    foreach ($rows as $u) {
        $wpId  = (int) $u['ID'];
        $email = trim((string) $u['user_email']);
        if ($email === '') {
            $email = 'wp' . $wpId . '@imported.local';
        }
        $q = $src->prepare("SELECT meta_key, meta_value FROM {$P}usermeta WHERE user_id=? AND meta_key IN ('wd_phone','wd_country','{$P}capabilities','first_name','last_name')");
        $q->execute([$wpId]);
        $meta = [];
        foreach ($q->fetchAll() as $m) {
            $meta[$m['meta_key']] = $m['meta_value'];
        }
        $caps = (string) ($meta[$P . 'capabilities'] ?? '');
        $role = str_contains($caps, 'administrator') ? 'owner' : 'member';

        $name = trim((string) $u['display_name']);
        if ($name === '') {
            $name = trim(($meta['first_name'] ?? '') . ' ' . ($meta['last_name'] ?? '')) ?: $u['user_login'];
        }
        $data = [
            // The WordPress hash is carried across untouched. PHP's password_verify() cannot read it,
            // so Auth checks it through LegacyHash ($P$ phpass and the $wp$ form from WordPress 6.8)
            // and replaces it with a modern hash on that person's first successful sign-in.
            'pass'       => (string) $u['user_pass'],
            'name'       => $name,
            'phone'      => (string) ($meta['wd_phone'] ?? ''),
            'country'    => (string) ($meta['wd_country'] ?? ''),
            'role'       => $role,
            'wp_id'      => $wpId,
            'created_at' => dt($u['user_registered']) ?? date('Y-m-d H:i:s'),
        ];
        if ($dry) { logged('user', $wpId, null); tally('users'); continue; }

        $have = Db::one('SELECT id FROM users WHERE wp_id=? OR email=? LIMIT 1', [$wpId, $email]);
        if ($have) {
            Db::update('users', $data, 'id=?', [(int) $have['id']]);
            $id = (int) $have['id'];
        } else {
            $id = Db::insert('users', $data + ['email' => $email]);
        }
        logged('user', $wpId, $id);
        tally('users');
    }
    say('users: ' . ($count['users'] ?? 0));
}

// ---------------------------------------------------------------- Weddings

if (want('weddings')) {
    $rows = $src->query("SELECT ID, post_author, post_title, post_name, post_status, post_date, post_modified FROM {$P}posts WHERE post_type='wd_wedding' AND post_status IN ('publish','draft','private','pending') ORDER BY ID")->fetchAll();
    foreach ($rows as $w) {
        $wpId = (int) $w['ID'];
        $d    = wp_meta($src, $P, $wpId, '_wd');
        $d    = is_array($d) ? $d : [];
        $plan = (string) (wp_meta($src, $P, $wpId, '_wd_plan') ?? 'free');
        $plan = in_array($plan, ['free', 'pro', 'premium', 'society'], true) ? $plan : 'free';
        $owner = mapped('user', (int) $w['post_author']);
        if (!$owner) {
            // A wedding with no importable owner would orphan; park it on the first owner account.
            $owner = (int) (Db::val("SELECT id FROM users WHERE role='owner' ORDER BY id LIMIT 1") ?: 0);
            if (!$owner) {
                say("  ! skipped wedding {$wpId}: no owner to attach it to");
                continue;
            }
        }
        $design = is_array($d['design'] ?? null) ? $d['design'] : [];
        $data = [
            'owner_id'   => $owner,
            'partner_a'  => (string) ($d['p1'] ?? ''),
            'partner_b'  => (string) ($d['p2'] ?? ''),
            'hashtag'    => (string) ($d['hashtag'] ?? ''),
            'tagline'    => (string) ($d['tagline'] ?? ''),
            'story'      => is_array($d['story'] ?? null) ? json_encode($d['story'], JSON_UNESCAPED_UNICODE) : (string) ($d['story'] ?? ''),
            'wed_date'   => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($d['date'] ?? '')) ? $d['date'] : null,
            'wed_time'   => preg_match('/^\d{2}:\d{2}/', (string) ($d['time'] ?? '')) ? substr((string) $d['time'], 0, 5) . ':00' : null,
            'city'       => (string) ($d['city'] ?? ''),
            'country'    => (string) ($d['country'] ?? 'Nigeria'),
            'venue'      => (string) ($d['venue'] ?? ''),
            'cover'      => img_ref($d['cover'] ?? ''),
            'plan'       => $plan,
            'template'   => (string) ($design['template'] ?? 'classic'),
            'design'     => $design ? json_encode($design, JSON_UNESCAPED_UNICODE) : null,
            'currency'   => substr((string) ($d['currency'] ?? 'NGN'), 0, 3) ?: 'NGN',
            'visibility' => $w['post_status'] === 'publish' ? 'public' : 'unlisted',
            'wp_id'      => $wpId,
            'created_at' => dt($w['post_date']) ?? date('Y-m-d H:i:s'),
        ];
        if ($dry) {
            logged('wedding', $wpId, null);
            tally('weddings');
            foreach (['events' => 'programs', 'party' => 'squad', 'registry' => 'gifts'] as $src_k => $as) {
                foreach ((array) ($d[$src_k] ?? []) as $row) {
                    if (is_array($row)) { tally($as); }
                }
            }
            continue;
        }

        $have = Db::one('SELECT id, slug FROM weddings WHERE wp_id=? LIMIT 1', [$wpId]);
        $slug = $have['slug'] ?? uniq_slug('weddings', slugify((string) ($w['post_name'] ?: $w['post_title']), 'wedding-' . $wpId));
        if ($have) {
            Db::update('weddings', $data, 'id=?', [(int) $have['id']]);
            $id = (int) $have['id'];
        } else {
            $id = Db::insert('weddings', $data + ['slug' => $slug]);
        }
        logged('wedding', $wpId, $id);
        tally('weddings');

        // The programs, the squad and the registry all live inside that one meta blob.
        if (!$dry) {
            Db::run('DELETE FROM programs WHERE wedding_id=?', [$id]);
            foreach ((array) ($d['events'] ?? []) as $i => $ev) {
                if (!is_array($ev)) { continue; }
                $when = trim((string) ($ev['when'] ?? ''));
                Db::insert('programs', [
                    'wedding_id' => $id,
                    'title'      => (string) ($ev['name'] ?? ($ev['title'] ?? 'Program')),
                    'kind'       => (string) ($ev['kind'] ?? 'other'),
                    'starts_at'  => preg_match('/^\d{4}-\d{2}-\d{2}/', $when) ? str_replace('T', ' ', $when) . (strlen($when) <= 10 ? ' 12:00:00' : ':00') : null,
                    'venue'      => (string) ($ev['venue'] ?? ''),
                    'address'    => (string) ($ev['address'] ?? ''),
                    'map_url'    => (string) ($ev['map'] ?? ''),
                    'dress'      => (string) ($ev['dress'] ?? ''),
                    'note'       => (string) ($ev['note'] ?? ''),
                    'is_private' => !empty($ev['private']) ? 1 : 0,
                    'sort'       => (int) $i,
                ]);
                tally('programs');
            }

            Db::run('DELETE FROM squad WHERE wedding_id=?', [$id]);
            foreach ((array) ($d['party'] ?? []) as $i => $p) {
                if (!is_array($p)) { continue; }
                Db::insert('squad', [
                    'wedding_id' => $id,
                    'side'       => in_array($p['side'] ?? '', ['bride', 'groom'], true) ? $p['side'] : 'both',
                    'name'       => (string) ($p['name'] ?? ''),
                    'role'       => (string) ($p['role'] ?? ''),
                    'photo'      => img_ref($p['photo'] ?? ''),
                    'words'      => (string) ($p['words'] ?? ''),
                    'instagram'  => (string) ($p['ig'] ?? ''),
                    'status'     => !empty($p['name']) ? 'joined' : 'invited',
                    'sort'       => (int) $i,
                ]);
                tally('squad');
            }

            Db::run('DELETE FROM gifts WHERE wedding_id=?', [$id]);
            foreach ((array) ($d['registry'] ?? []) as $i => $g) {
                if (!is_array($g)) { continue; }
                Db::insert('gifts', [
                    'wedding_id' => $id,
                    'title'      => (string) ($g['name'] ?? ($g['title'] ?? 'Gift')),
                    'note'       => (string) ($g['note'] ?? ''),
                    'image'      => img_ref($g['image'] ?? ''),
                    'target'     => (int) ($g['target'] ?? 0),
                    'raised'     => (int) ($g['raised'] ?? 0),
                    'kind'       => in_array($g['kind'] ?? '', ['cash', 'item', 'fund'], true) ? $g['kind'] : 'cash',
                    'link'       => (string) ($g['link'] ?? ''),
                    'sort'       => (int) $i,
                ]);
                tally('gifts');
            }
        }
    }
    say('weddings: ' . ($count['weddings'] ?? 0) . ', programs: ' . ($count['programs'] ?? 0) . ', squad: ' . ($count['squad'] ?? 0) . ', gifts: ' . ($count['gifts'] ?? 0));
}

// ---------------------------------------------------------------- Guests, the wall, money

if (want('guests') && has_table($src, $P . 'wd_rsvps')) {
    foreach ($src->query("SELECT * FROM {$P}wd_rsvps ORDER BY id")->fetchAll() as $g) {
        $wed = mapped('wedding', (int) $g['wedding_id']);
        if (!$wed) { continue; }
        if ($dry) { tally('guests'); continue; }
        $data = [
            'wedding_id' => $wed,
            'name'       => (string) $g['name'],
            'email'      => (string) $g['email'],
            'phone'      => (string) $g['phone'],
            'party'      => max(1, (int) $g['guests']),
            'coming'     => in_array($g['attending'], ['yes', 'no', 'maybe'], true) ? $g['attending'] : 'yes',
            'programs'   => $g['events'] ? json_encode(array_values(array_filter(array_map('trim', explode(',', (string) $g['events']))))) : null,
            'note'       => (string) $g['note'],
            'pass_code'  => (string) $g['pass_code'] ?: null,
            'checked_in' => dt($g['checked_in']),
            'table_no'   => (string) $g['table_no'],
            'created_at' => dt($g['created']) ?? date('Y-m-d H:i:s'),
        ];
        $have = mapped('guest', (int) $g['id']);
        if ($have) {
            Db::update('guests', $data, 'id=?', [$have]);
            $id = $have;
        } else {
            // pass_code is unique; a blank one must be NULL rather than '' or the second guest collides.
            $id = Db::insert('guests', $data);
        }
        logged('guest', (int) $g['id'], $id);
        tally('guests');
    }
    say('guests: ' . ($count['guests'] ?? 0));
}

if (want('wall')) {
    if (has_table($src, $P . 'wd_wall')) {
        foreach ($src->query("SELECT * FROM {$P}wd_wall ORDER BY id")->fetchAll() as $n) {
            $wed = mapped('wedding', (int) $n['wedding_id']);
            if (!$wed) { continue; }
            if ($dry) { tally('wall'); continue; }
            $data = [
                'wedding_id' => $wed,
                'kind'       => 'note',
                'name'       => (string) $n['name'],
                'body'       => (string) $n['message'],
                'likes'      => max(0, (int) $n['likes']),
                'status'     => $n['status'] === 'approved' ? 'live' : ($n['status'] === 'pending' ? 'held' : 'hidden'),
                'created_at' => dt($n['created']) ?? date('Y-m-d H:i:s'),
            ];
            $have = mapped('wall', (int) $n['id']);
            $id = $have ? (Db::update('wall', $data, 'id=?', [$have]) ? $have : $have) : Db::insert('wall', $data);
            logged('wall', (int) $n['id'], $id);
            tally('wall');
        }
    }
    if (has_table($src, $P . 'wd_photos')) {
        foreach ($src->query("SELECT * FROM {$P}wd_photos ORDER BY id")->fetchAll() as $p) {
            $wed = mapped('wedding', (int) $p['wedding_id']);
            if (!$wed) { continue; }
            if ($dry) { tally('photos'); continue; }
            // The file itself is a WordPress attachment; keep its id so the media step can find it.
            $data = [
                'wedding_id' => $wed,
                'kind'       => 'photo',
                'name'       => (string) $p['name'],
                'body'       => (string) $p['caption'],
                'image'      => img_ref((int) $p['attachment_id'] ?: ''),
                'likes'      => max(0, (int) $p['likes']),
                'status'     => $p['status'] === 'approved' ? 'live' : ($p['status'] === 'pending' ? 'held' : 'hidden'),
                'created_at' => dt($p['created']) ?? date('Y-m-d H:i:s'),
            ];
            $have = mapped('photo', (int) $p['id']);
            $id = $have ? ($have) : Db::insert('wall', $data);
            if ($have) { Db::update('wall', $data, 'id=?', [$have]); }
            logged('photo', (int) $p['id'], $id);
            tally('photos');
        }
    }
    say('wall notes: ' . ($count['wall'] ?? 0) . ', guest photos: ' . ($count['photos'] ?? 0));
}

if (want('payments') && has_table($src, $P . 'wd_tx')) {
    foreach ($src->query("SELECT * FROM {$P}wd_tx ORDER BY id")->fetchAll() as $t) {
        if ($dry) { tally('payments'); continue; }
        $wed = mapped('wedding', (int) $t['wedding_id']);
        $map = ['gift' => 'gift', 'spray' => 'spray', 'plan' => 'plan', 'cards' => 'cards', 'ad' => 'ad'];
        $data = [
            'purpose'     => $map[$t['kind']] ?? 'gift',
            'wedding_id'  => $wed,
            'payer_name'  => (string) $t['name'],
            'payer_email' => (string) $t['email'],
            'message'     => (string) $t['message'],
            'amount'      => (int) $t['amount'],
            'fee'         => (int) $t['fee'],
            'currency'    => substr((string) $t['currency'], 0, 3) ?: 'NGN',
            'gateway_ref' => (string) $t['reference'],
            'status'      => in_array($t['status'], ['paid', 'failed', 'refunded', 'released'], true) ? $t['status'] : 'pending',
            'paid_at'     => dt($t['paid_at']),
            'created_at'  => dt($t['created']) ?? date('Y-m-d H:i:s'),
        ];
        $have = mapped('tx', (int) $t['id']);
        if ($have) {
            Db::update('payments', $data, 'id=?', [$have]);
            $id = $have;
        } else {
            // ref is unique in the new schema; the old reference may be blank or repeated.
            $ref = (string) $t['reference'];
            $data['ref'] = $ref !== '' ? 'wp-' . $ref : 'wp-tx-' . (int) $t['id'];
            $id = Db::insert('payments', $data);
        }
        logged('tx', (int) $t['id'], $id);
        tally('payments');
    }
    say('payments: ' . ($count['payments'] ?? 0));
}

if (want('payouts') && has_table($src, $P . 'wd_payouts')) {
    foreach ($src->query("SELECT * FROM {$P}wd_payouts ORDER BY id")->fetchAll() as $p) {
        $user = mapped('user', (int) $p['user_id']);
        if (!$user || $dry) { if ($dry) { tally('payouts'); } continue; }
        $data = [
            'user_id'    => $user,
            'amount'     => (int) $p['amount'],
            'currency'   => substr((string) $p['currency'], 0, 3) ?: 'NGN',
            'bank'       => (string) $p['bank'],
            'account'    => (string) $p['account_no'],
            'account_nm' => (string) $p['account_name'],
            'status'     => $p['status'] === 'paid' ? 'sent' : ($p['status'] === 'failed' ? 'failed' : 'requested'),
            'note'       => (string) $p['note'],
            'created_at' => dt($p['created']) ?? date('Y-m-d H:i:s'),
            'sent_at'    => dt($p['paid_at']),
        ];
        $have = mapped('payout', (int) $p['id']);
        if ($have) { Db::update('payouts', $data, 'id=?', [$have]); $id = $have; }
        else { $id = Db::insert('payouts', $data); }
        logged('payout', (int) $p['id'], $id);
        tally('payouts');
    }
    say('payouts: ' . ($count['payouts'] ?? 0));
}

// ---------------------------------------------------------------- Vendors, the market, the blog

if (want('vendors')) {
    foreach ($src->query("SELECT ID, post_author, post_title, post_name, post_content, post_excerpt, post_date, post_status FROM {$P}posts WHERE post_type='wd_vendor' AND post_status IN ('publish','draft','pending') ORDER BY ID")->fetchAll() as $v) {
        $wpId = (int) $v['ID'];
        if ($dry) { logged('vendor', $wpId, null); tally('vendors'); continue; }
        $data = [
            'user_id'  => mapped('user', (int) $v['post_author']),
            'name'     => (string) $v['post_title'],
            'about'    => (string) $v['post_content'],
            'city'     => (string) (wp_meta($src, $P, $wpId, '_wd_city') ?? ''),
            'rating'   => (float) (wp_meta($src, $P, $wpId, '_wd_rating') ?? 0),
            'reviews'  => (int) (wp_meta($src, $P, $wpId, '_wd_reviews') ?? 0),
            'category' => vendor_type($src, $P, $wpId),
            'logo'     => thumb_ref($src, $P, $wpId),
            'wp_id'    => $wpId,
            'created_at' => dt($v['post_date']) ?? date('Y-m-d H:i:s'),
        ];
        $have = Db::one('SELECT id, slug FROM vendors WHERE wp_id=? LIMIT 1', [$wpId]);
        if ($have) { Db::update('vendors', $data, 'id=?', [(int) $have['id']]); $id = (int) $have['id']; }
        else { $id = Db::insert('vendors', $data + ['slug' => uniq_slug('vendors', slugify((string) ($v['post_name'] ?: $v['post_title']), 'vendor-' . $wpId))]); }
        logged('vendor', $wpId, $id);
        tally('vendors');
    }
    if (has_table($src, $P . 'wd_reviews')) {
        foreach ($src->query("SELECT * FROM {$P}wd_reviews ORDER BY id")->fetchAll() as $r) {
            $ven = mapped('vendor', (int) $r['vendor_id']);
            if (!$ven || $dry) { if ($dry) { tally('reviews'); } continue; }
            $data = [
                'vendor_id' => $ven,
                'name'      => (string) $r['name'],
                'stars'     => max(1, min(5, (int) $r['rating'])),
                'body'      => (string) $r['text'],
                'status'    => $r['status'] === 'approved' ? 'live' : 'held',
                'created_at' => dt($r['created']) ?? date('Y-m-d H:i:s'),
            ];
            $have = mapped('review', (int) $r['id']);
            if ($have) { Db::update('reviews', $data, 'id=?', [$have]); $id = $have; }
            else { $id = Db::insert('reviews', $data); }
            logged('review', (int) $r['id'], $id);
            tally('reviews');
        }
    }
    say('vendors: ' . ($count['vendors'] ?? 0) . ', reviews: ' . ($count['reviews'] ?? 0));
}

if (want('listings')) {
    foreach ($src->query("SELECT ID, post_author, post_title, post_name, post_content, post_date, post_status FROM {$P}posts WHERE post_type='wd_listing' ORDER BY ID")->fetchAll() as $l) {
        $wpId = (int) $l['ID'];
        $partner = mapped('user', (int) $l['post_author']);
        if (!$partner || $dry) { if ($dry) { logged('listing', $wpId, null); tally('listings'); } continue; }
        // The old theme keeps a listing as ONE array under _wl (sector, city, price, unit, about,
        // photos, price_mode). The _wl_city and _wl_price keys are only search copies of two of its
        // fields; reading the _wd_* keys instead — which is what this used to do — found nothing.
        $wl   = wp_meta($src, $P, $wpId, '_wl');
        $wl   = is_array($wl) ? $wl : [];
        $ask  = (($wl['price_mode'] ?? 'fixed') === 'ask');
        $refs = [];
        foreach ((array) ($wl['photos'] ?? []) as $ph) {
            $r = img_ref($ph);
            if ($r !== '') { $refs[] = $r; }
        }
        if (!$refs && ($t = thumb_ref($src, $P, $wpId)) !== '') { $refs[] = $t; }
        $kinds = ['stays' => 'stay', 'rides' => 'ride', 'travel' => 'honeymoon', 'asoebi' => 'asoebi'];
        $data = [
            'partner_id' => $partner,
            'title'      => (string) $l['post_title'],
            'kind'       => $kinds[(string) ($wl['sector'] ?? wp_meta($src, $P, $wpId, '_wl_sector') ?? '')] ?? 'other',
            'about'      => trim((string) ($wl['about'] ?? '')) !== '' ? (string) $wl['about'] : (string) $l['post_content'],
            'city'       => (string) ($wl['city'] ?? wp_meta($src, $P, $wpId, '_wl_city') ?? ''),
            'price'      => $ask ? 0 : (int) ($wl['price'] ?? wp_meta($src, $P, $wpId, '_wl_price') ?? 0),
            'price_unit' => (string) ($wl['unit'] ?? ''),
            'on_ask'     => $ask ? 1 : 0,
            'photos'     => json_encode(array_values($refs)),
            'status'     => $l['post_status'] === 'publish' ? 'live' : 'draft',
            'wp_id'      => $wpId,
            'created_at' => dt($l['post_date']) ?? date('Y-m-d H:i:s'),
        ];
        $have = Db::one('SELECT id, slug FROM listings WHERE wp_id=? LIMIT 1', [$wpId]);
        if ($have) { Db::update('listings', $data, 'id=?', [(int) $have['id']]); $id = (int) $have['id']; }
        else { $id = Db::insert('listings', $data + ['slug' => uniq_slug('listings', slugify((string) ($l['post_name'] ?: $l['post_title']), 'listing-' . $wpId))]); }
        logged('listing', $wpId, $id);
        tally('listings');
    }
    if (has_table($src, $P . 'wd_bookings')) {
        foreach ($src->query("SELECT * FROM {$P}wd_bookings ORDER BY id")->fetchAll() as $b) {
            $partner = mapped('user', (int) $b['partner_id']);
            if (!$partner || $dry) { if ($dry) { tally('orders'); } continue; }
            $st = ['pending' => 'awaiting', 'paid' => 'paid', 'released' => 'released', 'refunded' => 'refunded', 'cancelled' => 'cancelled', 'disputed' => 'disputed'];
            $data = [
                'listing_id' => mapped('listing', (int) $b['listing_id']),
                'partner_id' => $partner,
                'user_id'    => mapped('user', (int) $b['user_id']),
                'name'       => (string) $b['name'],
                'email'      => (string) $b['email'],
                'phone'      => (string) $b['phone'],
                'detail'     => (string) $b['note'],
                'amount'     => (int) $b['amount'],
                'currency'   => substr((string) $b['currency'], 0, 3) ?: 'NGN',
                'status'     => $st[$b['status']] ?? 'quote',
                'starts_on'  => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $b['start_date']) ? $b['start_date'] : null,
                'ends_on'    => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $b['end_date']) ? $b['end_date'] : null,
                'created_at' => dt($b['created']) ?? date('Y-m-d H:i:s'),
                'released_at' => dt($b['released_at']),
            ];
            $have = mapped('booking', (int) $b['id']);
            if ($have) { Db::update('orders', $data, 'id=?', [$have]); $id = $have; }
            else { $id = Db::insert('orders', $data + ['code' => strtoupper((string) $b['reference']) ?: 'WP' . (int) $b['id']]); }
            logged('booking', (int) $b['id'], $id);
            tally('orders');
        }
    }
    if (has_table($src, $P . 'wd_chats')) {
        foreach ($src->query("SELECT * FROM {$P}wd_chats ORDER BY id")->fetchAll() as $c) {
            $partner = mapped('user', (int) $c['partner_id']);
            $user    = mapped('user', (int) $c['user_id']);
            if (!$partner || !$user || $dry) { if ($dry) { tally('messages'); } continue; }
            if (trim((string) $c['body']) === '') { continue; }
            $data = [
                'partner_id' => $partner,
                'user_id'    => $user,
                'listing_id' => mapped('listing', (int) $c['listing_id']),
                'sender'     => in_array($c['sender'], ['user', 'partner', 'staff'], true) ? $c['sender'] : 'user',
                'body'       => (string) $c['body'],
                'read_at'    => !empty($c['seen']) ? (dt($c['created']) ?? null) : null,
                'created_at' => dt($c['created']) ?? date('Y-m-d H:i:s'),
            ];
            $have = mapped('chat', (int) $c['id']);
            if ($have) { Db::update('messages', $data, 'id=?', [$have]); $id = $have; }
            else { $id = Db::insert('messages', $data); }
            logged('chat', (int) $c['id'], $id);
            tally('messages');
        }
    }
    say('listings: ' . ($count['listings'] ?? 0) . ', orders: ' . ($count['orders'] ?? 0) . ', messages: ' . ($count['messages'] ?? 0));
}

// ---------------------------------------------------------------- The community

if (want('talks')) {
    foreach ($src->query("SELECT ID, post_content, post_date, post_status, comment_count FROM {$P}posts WHERE post_type='wd_talk' ORDER BY ID")->fetchAll() as $t) {
        $wpId = (int) $t['ID'];
        if ($dry) { logged('talk', $wpId, null); tally('talks'); }
        // Old topic keys were reorganised when the community widened past weddings.
        $topic = (string) (wp_meta($src, $P, $wpId, '_wd_topic') ?? 'confess');
        $topic = ['planning' => 'wedding', 'guests' => 'wedding', 'sellers' => 'money'][$topic] ?? $topic;
        $bg    = (string) (wp_meta($src, $P, $wpId, '_wd_bg') ?? 'paper');
        $bg    = ['grad' => 'sun', 'aubergine' => 'noir', 'purple' => 'dusk', 'lilac' => 'dusk', 'marigold' => 'sun'][$bg] ?? $bg;
        $data = [
            'topic'    => $topic,
            'pen_name' => (string) (wp_meta($src, $P, $wpId, '_wd_name') ?? ''),
            'body'     => (string) $t['post_content'],
            'bg'       => $bg,
            'likes'    => max(0, (int) (wp_meta($src, $P, $wpId, '_wd_likes') ?? 0)),
            'replies'  => max(0, (int) $t['comment_count']),
            'status'   => $t['post_status'] === 'publish' ? 'live' : 'held',
            'is_seed'  => wp_meta($src, $P, $wpId, '_wd_seed') ? 1 : 0,
            'wp_id'    => $wpId,
            'created_at' => dt($t['post_date']) ?? date('Y-m-d H:i:s'),
        ];
        if ($dry) {
            $id = -$wpId;
        } else {
            $have = Db::one('SELECT id FROM talks WHERE wp_id=? LIMIT 1', [$wpId]);
            if ($have) { Db::update('talks', $data, 'id=?', [(int) $have['id']]); $id = (int) $have['id']; }
            else { $id = Db::insert('talks', $data); }
            logged('talk', $wpId, $id);
            tally('talks');
        }

        foreach ($src->query("SELECT comment_ID, comment_content, comment_date, comment_approved FROM {$P}comments WHERE comment_post_ID={$wpId} AND comment_type='wd_reply' ORDER BY comment_ID")->fetchAll() as $c) {
            $cid = (int) $c['comment_ID'];
            $q = $src->prepare("SELECT meta_value FROM {$P}commentmeta WHERE comment_id=? AND meta_key='_wd_name' LIMIT 1");
            $q->execute([$cid]);
            $rdata = [
                'talk_id'  => $id,
                'pen_name' => (string) ($q->fetchColumn() ?: ''),
                'body'     => (string) $c['comment_content'],
                'status'   => $c['comment_approved'] === '1' ? 'live' : 'held',
                'created_at' => dt($c['comment_date']) ?? date('Y-m-d H:i:s'),
            ];
            if ($dry) { tally('talk_replies'); continue; }
            $rhave = mapped('talk_reply', $cid);
            if ($rhave) { Db::update('talk_replies', $rdata, 'id=?', [$rhave]); $rid = $rhave; }
            else { $rid = Db::insert('talk_replies', $rdata); }
            logged('talk_reply', $cid, $rid);
            tally('talk_replies');
        }
    }
    say('community posts: ' . ($count['talks'] ?? 0) . ', replies: ' . ($count['talk_replies'] ?? 0));
}

if (want('posts')) {
    foreach ($src->query("SELECT ID, post_title, post_name, post_content, post_excerpt, post_date, post_status FROM {$P}posts WHERE post_type='post' AND post_status IN ('publish','draft') ORDER BY ID")->fetchAll() as $p) {
        $wpId = (int) $p['ID'];
        if ($dry) { tally('posts'); continue; }
        $data = [
            'title'   => (string) $p['post_title'],
            'excerpt' => mb_substr(trim(strip_tags((string) ($p['post_excerpt'] ?: $p['post_content']))), 0, 480),
            'body'    => (string) $p['post_content'],
            'cover'   => thumb_ref($src, $P, $wpId),
            'status'  => $p['post_status'] === 'publish' ? 'live' : 'draft',
            'wp_id'   => $wpId,
            'published_at' => dt($p['post_date']),
        ];
        $have = Db::one('SELECT id, slug FROM posts WHERE wp_id=? LIMIT 1', [$wpId]);
        if ($have) { Db::update('posts', $data, 'id=?', [(int) $have['id']]); $id = (int) $have['id']; }
        else { $id = Db::insert('posts', $data + ['slug' => uniq_slug('posts', slugify((string) ($p['post_name'] ?: $p['post_title']), 'post-' . $wpId))]); }
        logged('post', $wpId, $id);
        tally('posts');
    }
    say('blog posts: ' . ($count['posts'] ?? 0));
}

// ---------------------------------------------------------------- Settings & media paths

if (want('settings')) {
    $q = $src->prepare("SELECT option_value FROM {$P}options WHERE option_name='wd_settings' LIMIT 1");
    $q->execute();
    $raw = $q->fetchColumn();
    if ($raw) {
        $s = @unserialize((string) $raw, ['allowed_classes' => false]);
        if (is_array($s)) {
            foreach ($s as $k => $v) {
                if ($dry) { tally('settings'); continue; }
                Db::run(
                    'INSERT INTO settings (k, v) VALUES (?,?) ON DUPLICATE KEY UPDATE v=VALUES(v)',
                    [substr((string) $k, 0, 80), is_scalar($v) ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE)]
                );
                tally('settings');
            }
        }
    }
    say('settings: ' . ($count['settings'] ?? 0));
}

if (want('media')) {
    foreach ($src->query("SELECT ID, post_title, post_date, guid FROM {$P}posts WHERE post_type='attachment' ORDER BY ID")->fetchAll() as $m) {
        $wpId = (int) $m['ID'];
        if ($dry) { tally('media'); continue; }
        $file = wp_meta($src, $P, $wpId, '_wp_attached_file');
        if (!is_string($file) || $file === '') { continue; }
        $data = ['path' => 'wp-content/uploads/' . $file, 'kind' => 'image', 'wp_id' => $wpId, 'created_at' => dt($m['post_date']) ?? date('Y-m-d H:i:s')];
        $have = Db::one('SELECT id FROM media WHERE wp_id=? LIMIT 1', [$wpId]);
        if ($have) { Db::update('media', $data, 'id=?', [(int) $have['id']]); }
        else { Db::insert('media', $data); }
        tally('media');
    }
    say('media rows: ' . ($count['media'] ?? 0) . '  (files themselves are copied separately — see below)');

    // Every attachment id has a path now, so swap the "wp:123" placeholders written above for it.
    if (!$dry) {
        $fixed = 0;
        foreach ([['weddings', 'cover'], ['squad', 'photo'], ['gifts', 'image'], ['wall', 'image'], ['vendors', 'logo'], ['posts', 'cover']] as [$t, $c]) {
            $fixed += Db::run("UPDATE `$t` x JOIN media m ON x.`$c` = CONCAT('wp:', m.wp_id) SET x.`$c` = m.path");
        }
        foreach (Db::all("SELECT id, photos FROM listings WHERE photos LIKE '%wp:%'") as $l) {
            $list = json_decode((string) $l['photos'], true) ?: [];
            foreach ($list as $i => $ref) {
                if (is_string($ref) && preg_match('/^wp:(\d+)$/', $ref, $mm)) {
                    $path = Db::val('SELECT path FROM media WHERE wp_id = ?', [(int) $mm[1]]);
                    if ($path) { $list[$i] = $path; $fixed++; }
                }
            }
            Db::update('listings', ['photos' => json_encode(array_values($list))], 'id=?', [(int) $l['id']]);
        }
        say("image references resolved: $fixed");
        $left = (int) Db::val("SELECT (SELECT COUNT(*) FROM weddings WHERE cover LIKE 'wp:%') + (SELECT COUNT(*) FROM wall WHERE image LIKE 'wp:%')", [], 0);
        if ($left) {
            say("  $left image(s) point at an attachment WordPress no longer has. They will show the colour fallback.");
        }
    }
}

say("\nDone." . ($dry ? ' Nothing was written.' : ''));
if (!$dry && ($count['media'] ?? 0)) {
    say("Copy the old uploads folder across so the images resolve. Over SSH:");
    say("  rsync -a /path/to/old/wp-content/uploads/  public/assets/uploads/");
    say("Or in cPanel File Manager: upload the contents of wp-content/uploads into public/assets/uploads/,");
    say("keeping the year/month folders exactly as they were.");
}
