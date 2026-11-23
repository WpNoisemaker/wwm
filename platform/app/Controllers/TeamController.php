<?php
declare(strict_types=1);

/**
 * The couple's team: inviting a planner, bouncers and vendors, and what each of them can open.
 * Planners work in the couple's dashboard (only the parts they were given), bouncers get the door
 * list, and vendors get the reviews guests left for them on that wedding page.
 */
final class TeamController
{
    private static function go(string $p): never
    {
        header('Location: ' . App::url($p));
        exit;
    }

    /** The couple's own wedding. A planner never manages the team. */
    private static function couple(): array
    {
        Auth::wall();
        Guard::boot();
        $w = Managed::managing()
            ? Db::one('SELECT * FROM weddings WHERE id = ?', [Managed::managing()])
            : Db::one('SELECT * FROM weddings WHERE owner_id = ? ORDER BY id LIMIT 1', [Auth::id()]);
        if (!$w || (Team::acting() && !Managed::managing())) {
            Guard::flash('bad', 'Only the couple can invite people to their page.');
            self::go(Team::acting() ? '/account' : '/account/new');
        }
        return $w;
    }

    /** The couple's Team tab: who is helping, with what, and the invite form. */
    public function index(): void
    {
        $w = self::couple();
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            $err = Team::invite($w, (string) ($_POST['role'] ?? ''), trim((string) ($_POST['name'] ?? '')), trim((string) ($_POST['email'] ?? '')),
                (array) ($_POST['perms'] ?? []), (int) ($_POST['vendor_id'] ?? 0) ?: null);
            Guard::flash($err === '' ? 'ok' : 'bad', $err === '' ? 'Invite sent. They will appear as active once they accept it.' : $err);
            self::go('/account/team');
        }
        View::render('account/team', [
            'title'   => 'Team', 'tab' => 'account', 'appTitle' => 'Team', 'back' => App::url('/account'),
            'w'       => $w,
            'rows'    => Team::forWedding((int) $w['id']),
            'vendors' => Db::all('SELECT v.id, v.name FROM wedding_vendors wv JOIN vendors v ON v.id = wv.vendor_id WHERE wv.wedding_id = ? ORDER BY v.name', [$w['id']]),
        ]);
    }

    /** Change a planner's access, or remove anyone. */
    public function update(string $id): void
    {
        Guard::must();
        $w = self::couple();
        $t = Db::one("SELECT * FROM wedding_team WHERE id = ? AND wedding_id = ? AND status <> 'removed'", [(int) $id, $w['id']]);
        if ($t && ($_POST['do'] ?? '') === 'remove') {
            Db::update('wedding_team', ['status' => 'removed', 'token' => null], 'id = ?', [$t['id']]);
            Guard::flash('ok', ($t['name'] ?: $t['email']) . ' no longer has access to your page.');
        } elseif ($t && $t['role'] === 'planner') {
            $perms = array_values(array_intersect(array_keys(Team::AREAS), (array) ($_POST['perms'] ?? [])));
            Db::update('wedding_team', ['perms' => implode(',', $perms)], 'id = ?', [$t['id']]);
            Guard::flash('ok', 'Saved. ' . ($t['name'] ?: $t['email']) . ' now has access to ' . (count($perms) ? strtolower(implode(', ', array_map(static fn ($p) => explode(':', Team::AREAS[$p])[0], $perms))) : 'only the overview') . '.');
        }
        self::go('/account/team');
    }

    /** The link in the invite email. Signed out: sign in or make an account first, then come back here. */
    public function join(string $token): void
    {
        Guard::boot();
        $t = Team::byToken($token);
        if (!$t) {
            Guard::flash('bad', 'This invite has already been used or was cancelled. Ask the couple to send a new one.');
            self::go(Auth::check() ? '/team' : '/');
        }
        if (!Auth::check()) {
            $known = Db::val('SELECT 1 FROM users WHERE email = ?', [$t['email']]);
            Guard::flash('ok', $known ? 'Sign in to accept the invite.' : 'Make an account with ' . $t['email'] . ' to accept the invite. It only takes a minute.');
            self::go(($known ? '/sign-in' : '/join/customer') . '?to=' . urlencode('/team/join/' . $token));
        }
        Team::accept($t, (int) Auth::id());
        $w = Db::one('SELECT * FROM weddings WHERE id = ?', [$t['wedding_id']]);
        Guard::flash('ok', 'You are now on ' . Wedding::names($w) . '’s team as their ' . strtolower(Team::ROLES[$t['role']]) . '.');
        self::go('/team');
    }

    /** Everything this person helps with. */
    public function mine(): void
    {
        Auth::wall();
        Guard::boot();
        View::render('team/index', ['title' => 'Weddings you help with', 'tab' => 'account', 'appTitle' => 'Your teams', 'back' => App::url('/account'), 'rows' => Team::forUser((int) Auth::id())]);
    }

    /** A planner opens the couple's dashboard. */
    public function open(string $id): void
    {
        Auth::wall();
        Guard::boot();
        $t = Db::one("SELECT * FROM wedding_team WHERE id = ? AND user_id = ? AND role = 'planner' AND status = 'active'", [(int) $id, Auth::id()]);
        if (!$t) {
            Guard::flash('bad', 'You no longer have access to that page.');
            self::go('/team');
        }
        $_SESSION['team_w'] = (int) $t['wedding_id'];
        self::go('/account');
    }

    public function leave(): void
    {
        Guard::boot();
        unset($_SESSION['team_w']);
        self::go('/team');
    }

    /* ------------------------------------------------------------------ the door */

    private static function doorWedding(string $slug): array
    {
        Auth::wall();
        Guard::boot();
        $w = Wedding::bySlug($slug);
        if (!$w || !Team::door((int) $w['id'])) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not here']);
            exit;
        }
        return $w;
    }

    /** The bouncer's screen: find a guest by code or name, see their face, check them in and out. */
    public function door(string $slug): void
    {
        $w = self::doorWedding($slug);
        $q = trim((string) ($_GET['q'] ?? ''));
        $arg = [$w['id']];
        $sql = "SELECT id, name, party, photo, pass_code, checked_in, checked_out, table_no FROM guests WHERE wedding_id = ? AND status = 'approved' AND coming <> 'no'";
        if ($q !== '') {
            $c = Rsvp::clean($q);
            $sql .= ' AND (pass_code = ? OR name LIKE ?)';
            $arg[] = $c;
            $arg[] = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $q) . '%';
        }
        $rows = Db::all($sql . ' ORDER BY (checked_in IS NOT NULL AND checked_out IS NULL) DESC, name LIMIT 300', $arg);
        $sum = Db::one("SELECT COUNT(*) n, SUM(checked_in IS NOT NULL AND checked_out IS NULL) inside, SUM(checked_in IS NOT NULL) arrived FROM guests WHERE wedding_id = ? AND status = 'approved' AND coming <> 'no'", [$w['id']]) ?: [];
        View::render('team/door', ['title' => 'Door · ' . Wedding::names($w), 'tab' => 'account', 'appTitle' => 'Door', 'back' => App::url('/team'), 'w' => $w, 'rows' => $rows, 'q' => $q, 'sum' => $sum]);
    }

    /** In or out, with the time. Checking out and back in again is fine: people step out. */
    public function clock(string $slug, string $id, string $dir): void
    {
        Guard::must();
        $w = self::doorWedding($slug);
        $g = Db::one("SELECT id, name FROM guests WHERE id = ? AND wedding_id = ? AND status = 'approved'", [(int) $id, $w['id']]);
        if ($g) {
            if ($dir === 'in') {
                Db::run('UPDATE guests SET checked_in = NOW(), checked_out = NULL WHERE id = ?', [$g['id']]);
            } else {
                Db::run('UPDATE guests SET checked_out = NOW() WHERE id = ? AND checked_in IS NOT NULL', [$g['id']]);
            }
            Guard::flash('ok', $g['name'] . ($dir === 'in' ? ' is checked in.' : ' is checked out.'));
        }
        $q = (string) ($_POST['q'] ?? '');
        self::go('/door/' . $w['slug'] . ($q !== '' ? '?q=' . urlencode($q) : ''));
    }

    /* ------------------------------------------------------------------ vendors */

    /** A vendor's view: the reviews guests left for them on this wedding page, with a reply box. */
    public function reviews(string $id): void
    {
        Auth::wall();
        Guard::boot();
        $t = Db::one("SELECT * FROM wedding_team WHERE id = ? AND user_id = ? AND role = 'vendor' AND status = 'active'", [(int) $id, Auth::id()]);
        if (!$t) {
            Guard::flash('bad', 'You no longer have access to those reviews.');
            self::go('/team');
        }
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            $reply = mb_substr(trim((string) ($_POST['reply'] ?? '')), 0, 1000);
            Db::run('UPDATE reviews SET reply = ? WHERE id = ? AND vendor_id = ? AND wedding_id = ?', [$reply, (int) ($_POST['review_id'] ?? 0), $t['vendor_id'], $t['wedding_id']]);
            Guard::flash('ok', $reply === '' ? 'Your reply was removed.' : 'Your reply is posted under the review.');
            self::go('/team/' . $t['id'] . '/reviews');
        }
        $w = Db::one('SELECT * FROM weddings WHERE id = ?', [$t['wedding_id']]);
        View::render('team/reviews', [
            'title' => 'Reviews from ' . Wedding::names($w) . '’s wedding', 'tab' => 'account', 'appTitle' => 'Reviews', 'back' => App::url('/team'),
            't' => $t, 'w' => $w, 'v' => Db::one('SELECT * FROM vendors WHERE id = ?', [$t['vendor_id']]),
            'rows' => Db::all("SELECT * FROM reviews WHERE vendor_id = ? AND wedding_id = ? AND status = 'live' ORDER BY created_at DESC", [$t['vendor_id'], $t['wedding_id']]),
        ]);
    }
}
