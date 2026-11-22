<?php
declare(strict_types=1);

/**
 * Couple packs: a real couple's page, carried over from the old site and added in one step.
 * A pack never overwrites anything: if the page already exists it is left alone, and if the
 * couple's email already has an account the page is given to that account.
 */
final class Pack
{
    public const ALL = ['ibrahim-ummi' => 'Ibrahim & Ummi'];

    public static function present(string $key): bool
    {
        return (bool) Db::val('SELECT 1 FROM weddings WHERE slug = ?', [$key]);
    }

    /**
     * Earlier demo content shipped a made-up page at this couple's address. If the page there is a
     * demo row (and only then) it is taken out, with everything hanging off it, so the real page
     * can go in. A real page at the address is never touched.
     */
    private static function clearDemoCopy(string $slug): bool
    {
        if (!Db::val("SHOW TABLES LIKE 'demo_rows'")) {
            return false;
        }
        $id = (int) Db::val('SELECT id FROM weddings WHERE slug = ?', [$slug], 0);
        if ($id === 0 || !Db::val("SELECT 1 FROM demo_rows WHERE tbl = 'weddings' AND row_id = ?", [$id])) {
            return false;
        }
        foreach (['programs', 'guests', 'squad', 'gifts', 'wall', 'payments'] as $t) {
            $ids = array_map(static fn ($r) => (int) $r['id'], Db::all("SELECT id FROM $t WHERE wedding_id = ?", [$id]));
            if ($ids) {
                $in = implode(',', $ids);
                Db::run("DELETE FROM $t WHERE id IN ($in)");
                Db::run("DELETE FROM demo_rows WHERE tbl = ? AND row_id IN ($in)", [$t]);
            }
        }
        Db::run('DELETE FROM weddings WHERE id = ?', [$id]);
        Db::run("DELETE FROM demo_rows WHERE tbl = 'weddings' AND row_id = ?", [$id]);
        return true;
    }

    /** @return string 'added' or 'exists' */
    public static function install(string $key): string
    {
        if (!isset(self::ALL[$key])) {
            throw new InvalidArgumentException('Unknown pack');
        }
        if (self::present($key) && !self::clearDemoCopy($key)) {
            return 'exists';
        }
        $p = require App::root() . '/db/packs/' . $key . '.php';
        $pdo = Db::pdo();
        $pdo->beginTransaction();
        try {
            $u = $p['user'];
            $uid = (int) Db::val('SELECT id FROM users WHERE email = ?', [$u['email']], 0);
            if ($uid === 0) {
                $uid = (int) Db::insert('users', ['email' => $u['email'], 'pass' => $u['hash'], 'name' => $u['name'], 'country' => 'Nigeria', 'role' => 'member']);
            }
            $w = $p['wedding'];
            $w['owner_id'] = $uid;
            $w['design'] = json_encode($w['design'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $wid = (int) Db::insert('weddings', $w);
            foreach ($p['programs'] as $k => $row) {
                Db::insert('programs', $row + ['wedding_id' => $wid, 'sort' => $k]);
            }
            foreach ($p['squad'] as $k => [$side, $name, $role, $words, $ig]) {
                Db::insert('squad', ['wedding_id' => $wid, 'side' => $side, 'name' => $name, 'role' => $role, 'words' => $words, 'instagram' => $ig, 'status' => 'joined', 'sort' => $k]);
            }
            foreach ($p['gifts'] as $k => [$title, $note, $kind, $target]) {
                Db::insert('gifts', ['wedding_id' => $wid, 'title' => $title, 'note' => $note, 'kind' => $kind, 'target' => $target, 'raised' => 0, 'sort' => $k]);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        return 'added';
    }
}
