<?php
declare(strict_types=1);

/**
 * The couple's own pages. Every query is scoped by owner_id in the SQL itself — never filtered in
 * PHP afterwards — so one couple can never read or write another's wedding.
 */
final class AccountController
{
    /** The signed-in user's wedding, or null. */
    private function mine(): ?array
    {
        return Db::one('SELECT * FROM weddings WHERE owner_id = ? ORDER BY id LIMIT 1', [Auth::id()]);
    }

    /** Every page here needs an account and a wedding behind it. */
    private function need(): array
    {
        Auth::wall();
        $w = $this->mine();
        if (!$w) {
            Guard::flash('bad', 'Make your wedding page first.');
            header('Location: ' . App::url('/account/new'));
            exit;
        }
        return $w;
    }

    private function show(string $tpl, array $data): void
    {
        View::render('account/' . $tpl, $data + ['tab' => 'account', 'appTitle' => $tpl === 'index' ? 'Your Page' : mb_strimwidth((string) $data['title'], 0, 18, '…'), 'back' => $tpl === 'index' ? null : App::url('/account')]);
    }

    private static function go(string $p): never
    {
        header('Location: ' . App::url($p));
        exit;
    }

    /** The dashboard: what is done, what is left, and the numbers so far. */
    public function index(): void
    {
        Auth::wall();
        $w = $this->mine();
        if (!$w) {
            self::go('/account/new');
        }
        $id = (int) $w['id'];
        $this->show('index', [
            'title' => Wedding::names($w),
            'w'     => $w,
            'tally' => Wedding::tally($id),
            'dash'  => Wedding::dash($id),
            'next'  => Wedding::nextMoment($w, Wedding::programs($id)),
            'todo'  => [
                ['Your story',   '/account/page',     trim((string) $w['story']) !== ''],
                ['Programs',     '/account/programs', count(Wedding::programs($id)) > 0],
                ['The squad',    '/account/squad',    (int) Db::val('SELECT COUNT(*) FROM squad WHERE wedding_id = ?', [$id], 0) > 0],
                ['Registry',     '/account/registry', count(Wedding::gifts($id)) > 0],
                ['Make it public', '/account/page',   (string) $w['visibility'] === 'public'],
            ],
        ]);
    }

    /* ------------------------------------------------------------------------- Make one ------ */

    public function create(): void
    {
        Auth::wall();
        if ($this->mine()) {
            self::go('/account');
        }
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            $a = trim((string) ($_POST['a'] ?? ''));
            $b = trim((string) ($_POST['b'] ?? ''));
            if ($a === '' || $b === '') {
                Guard::flash('bad', 'Both first names, please.');
                self::go('/account/new');
            }
            $base = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($a . '-' . $b)) ?? '', '-') ?: 'wedding';
            $slug = $base;
            for ($i = 2; Db::val('SELECT id FROM weddings WHERE slug = ?', [$slug]); $i++) {
                $slug = $base . '-' . $i;
            }
            Db::insert('weddings', [
                'owner_id' => Auth::id(), 'slug' => $slug, 'partner_a' => $a, 'partner_b' => $b,
                'wed_date' => ($_POST['date'] ?? '') !== '' ? (string) $_POST['date'] : null,
                'city' => trim((string) ($_POST['city'] ?? '')), 'country' => 'Nigeria',
                'plan' => 'free', 'visibility' => 'private', 'currency' => 'NGN', 'template' => 'classic',
            ]);
            Guard::flash('ok', 'Made. Now fill it in.');
            self::go('/account/page');
        }
        $this->show('new', ['title' => 'Make your wedding page']);
    }

    /* ------------------------------------------------------------------------- The page ------ */

    public function page(): void
    {
        $w = $this->need();
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            // Only the fields on this form change. Everything else the design holds (a custom design,
            // the story diary, the quiz, the colours of the day) is kept as it was.
            $d = Wedding::design($w);
            foreach (['primary', 'accent'] as $k) {
                $hex = self::hex((string) ($_POST[$k] ?? ''));
                if ($hex !== '') { $d[$k] = $hex; }
            }
            if (array_key_exists('live_url', $_POST)) {
                $live = trim((string) $_POST['live_url']);
                if ($live === '' || preg_match('~^https://(www\.)?(youtube\.com|youtu\.be)/~i', $live)) {
                    $d['live_url'] = $live;
                }
            }
            $design = json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            // Going public from private is the one change email confirmation gates. A page that is
            // already public is left alone when it is edited — pulling it down would punish somebody
            // for a check they were never asked to pass.
            $visibility = ($_POST['visibility'] ?? '') === 'public' ? 'public' : 'private';
            $held = false;
            if ($visibility === 'public' && (string) $w['visibility'] !== 'public' && Verify::needed(Auth::user())) {
                $visibility = 'private';
                $held = true;
            }
            Db::update('weddings', [
                'partner_a'  => trim((string) ($_POST['a'] ?? '')),
                'partner_b'  => trim((string) ($_POST['b'] ?? '')),
                'hashtag'    => trim((string) ($_POST['hashtag'] ?? '')),
                'tagline'    => trim((string) ($_POST['tagline'] ?? '')),
                'story'      => trim((string) ($_POST['story'] ?? '')),
                'wed_date'   => ($_POST['date'] ?? '') !== '' ? (string) $_POST['date'] : null,
                'wed_time'   => ($_POST['time'] ?? '') !== '' ? (string) $_POST['time'] : null,
                'city'       => trim((string) ($_POST['city'] ?? '')),
                'state'      => trim((string) ($_POST['state'] ?? '')),
                'country'    => in_array($_POST['country'] ?? '', Wedding::COUNTRIES, true) ? (string) $_POST['country'] : (string) $w['country'],
                'currency'   => Wedding::CURRENCY[(string) ($_POST['country'] ?? $w['country'])] ?? (string) $w['currency'],
                'venue'      => trim((string) ($_POST['venue'] ?? '')),
                'template'   => array_key_exists((string) ($_POST['template'] ?? ''), Design::choices($w)) ? (string) $_POST['template'] : (string) $w['template'],
                'design'     => $design,
                'visibility' => $visibility,
            ], 'id = ? AND owner_id = ?', [$w['id'], Auth::id()]);
            if ($held) {
                Guard::flash('bad', 'Saved — but your page is still private. Confirm your email first, then make it public. We sent you a link; use "Send it again" on this page if it did not arrive.');
            } else {
                Guard::flash('ok', 'Saved.');
            }
            self::go('/account/page');
        }
        $this->show('page', ['title' => 'Your page', 'w' => $w, 'd' => Wedding::design($w)]);
    }

    /* ------------------------------------------------------------------- Lists on the page --- */

    public function programs(): void
    {
        $w = $this->need();
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            $this->rowAction('programs', (int) $w['id'], [
                'title'      => trim((string) ($_POST['title'] ?? '')),
                'kind'       => trim((string) ($_POST['kind'] ?? '')),
                'starts_at'  => ($_POST['starts_at'] ?? '') !== '' ? str_replace('T', ' ', (string) $_POST['starts_at']) . ':00' : null,
                'venue'      => trim((string) ($_POST['venue'] ?? '')),
                'address'    => trim((string) ($_POST['address'] ?? '')),
                'dress'      => trim((string) ($_POST['dress'] ?? '')),
                'note'       => trim((string) ($_POST['note'] ?? '')),
                'is_private' => isset($_POST['is_private']) ? 1 : 0,
            ], 'title');
            self::go('/account/programs');
        }
        $this->show('programs', ['title' => 'Programs', 'w' => $w, 'rows' => Wedding::programs((int) $w['id'])]);
    }

    public function squad(): void
    {
        $w = $this->need();
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            $this->rowAction('squad', (int) $w['id'], [
                'side'  => in_array($_POST['side'] ?? '', ['bride', 'groom', 'both'], true) ? (string) $_POST['side'] : 'both',
                'name'  => trim((string) ($_POST['name'] ?? '')),
                'role'  => trim((string) ($_POST['role'] ?? '')),
                'words' => trim((string) ($_POST['words'] ?? '')),
            ], 'name');
            self::go('/account/squad');
        }
        $this->show('squad', ['title' => 'The squad', 'w' => $w, 'rows' => Db::all('SELECT * FROM squad WHERE wedding_id = ? ORDER BY sort, id', [$w['id']])]);
    }

    public function registry(): void
    {
        $w = $this->need();
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            $this->rowAction('gifts', (int) $w['id'], [
                'title'  => trim((string) ($_POST['title'] ?? '')),
                'note'   => trim((string) ($_POST['note'] ?? '')),
                'kind'   => in_array($_POST['kind'] ?? '', ['cash', 'item', 'fund'], true) ? (string) $_POST['kind'] : 'cash',
                'target' => max(0, (int) ($_POST['target'] ?? 0)),
                'link'   => trim((string) ($_POST['link'] ?? '')),
            ], 'title');
            self::go('/account/registry');
        }
        $this->show('registry', ['title' => 'Registry', 'w' => $w, 'rows' => Wedding::gifts((int) $w['id'])]);
    }

    /**
     * Add, edit or delete one row of a list that belongs to this wedding. The wedding_id is always
     * in the WHERE clause, so a guessed id from another couple's page matches nothing.
     */
    private function rowAction(string $table, int $weddingId, array $fields, string $required): void
    {
        $id  = (int) ($_POST['id'] ?? 0);
        $del = ($_POST['do'] ?? '') === 'delete';
        if ($del && $id) {
            Db::run("DELETE FROM $table WHERE id = ? AND wedding_id = ?", [$id, $weddingId]);
            Guard::flash('ok', 'Removed.');
            return;
        }
        if (trim((string) ($fields[$required] ?? '')) === '') {
            Guard::flash('bad', 'It needs a name at least.');
            return;
        }
        if ($id) {
            Db::update($table, $fields, 'id = ? AND wedding_id = ?', [$id, $weddingId]);
            Guard::flash('ok', 'Updated.');
            return;
        }
        Db::insert($table, $fields + ['wedding_id' => $weddingId]);
        Guard::flash('ok', 'Added.');
    }

    /* ------------------------------------------------------------------------- Guests -------- */

    public function guests(): void
    {
        $w   = $this->need();
        $q   = trim((string) ($_GET['q'] ?? ''));
        $arg = [$w['id']];
        $sql = 'SELECT * FROM guests WHERE wedding_id = ?';
        if ($q !== '') {
            $sql .= " AND CONCAT_WS(' ', name, phone, email) LIKE ?";
            $arg[] = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $q) . '%';
        }
        $rows = Db::all($sql . ' ORDER BY created_at DESC, id DESC', $arg);

        if (($_GET['csv'] ?? '') === '1') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="guests-' . $w['slug'] . '.csv"');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Phone', 'Email', 'Party', 'Coming', 'Pass', 'Table', 'Note', 'Replied']);
            foreach ($rows as $g) {
                fputcsv($out, [$g['name'], $g['phone'], $g['email'], $g['party'], $g['coming'],
                               $g['pass_code'], $g['table_no'], $g['note'], $g['created_at']]);
            }
            exit;
        }

        $this->show('guests', [
            'title' => 'Guests & RSVPs',
            'w'     => $w,
            'rows'  => $rows,
            'q'     => $q,
            'sum'   => Db::one(
                "SELECT COUNT(*) total,
                        SUM(coming='yes') yes, SUM(coming='no') no, SUM(coming='maybe') maybe,
                        COALESCE(SUM(CASE WHEN coming='yes' THEN party ELSE 0 END),0) heads
                 FROM guests WHERE wedding_id = ?",
                [$w['id']]
            ) ?? [],
        ]);
    }

    public function guestUpdate(string $id): void
    {
        Guard::must();
        $w = $this->need();
        $coming = in_array($_POST['coming'] ?? '', ['yes', 'no', 'maybe'], true) ? (string) $_POST['coming'] : 'maybe';
        Db::update('guests', ['coming' => $coming, 'table_no' => trim((string) ($_POST['table_no'] ?? ''))],
                   'id = ? AND wedding_id = ?', [(int) $id, $w['id']]);
        Guard::flash('ok', 'Updated.');
        self::go('/account/guests');
    }

    /* -------------------------------------------------------------------------- Money -------- */

    public function money(): void
    {
        $w   = $this->need();
        $fee = (float) Site::setting('fee_gift', '4');
        $this->show('money', [
            'title'    => 'Money',
            'w'        => $w,
            'fee'      => Wedding::has($w, 'spray') ? 0.0 : $fee,
            'payments' => Db::all("SELECT * FROM payments WHERE wedding_id = ? ORDER BY created_at DESC LIMIT 50", [$w['id']]),
            'payouts'  => Db::all('SELECT * FROM payouts WHERE user_id = ? ORDER BY created_at DESC LIMIT 20', [Auth::id()]),
            'in'       => (int) Db::val("SELECT COALESCE(SUM(amount),0) FROM payments WHERE wedding_id = ? AND status IN ('paid','released')", [$w['id']], 0),
            'out'      => (int) Db::val("SELECT COALESCE(SUM(amount),0) FROM payouts WHERE user_id = ? AND status <> 'failed'", [Auth::id()], 0),
        ]);
    }

    public function payout(): void
    {
        Guard::must();
        $w   = $this->need();
        if (Verify::needed(Auth::user())) {
            Guard::flash('bad', 'Confirm your email before you ask for a payout. Money goes to a bank account, so we need to know the address on this account is really yours.');
            self::go('/account/money');
        }
        $amt = max(0, (int) ($_POST['amount'] ?? 0));
        $in  = (int) Db::val("SELECT COALESCE(SUM(amount),0) FROM payments WHERE wedding_id = ? AND status IN ('paid','released')", [$w['id']], 0);
        $out = (int) Db::val("SELECT COALESCE(SUM(amount),0) FROM payouts WHERE user_id = ? AND status <> 'failed'", [Auth::id()], 0);
        if ($amt <= 0 || $amt > $in - $out) {
            Guard::flash('bad', 'That is more than the balance available.');
            self::go('/account/money');
        }
        Db::insert('payouts', [
            'user_id'    => Auth::id(),
            'amount'     => $amt,
            'currency'   => (string) $w['currency'],
            'status'     => 'requested',
            'bank'       => trim((string) ($_POST['bank'] ?? '')),
            'account'    => trim((string) ($_POST['acct'] ?? '')),
            'account_nm' => trim((string) ($_POST['acct_name'] ?? '')),
        ]);
        Guard::flash('ok', 'Payout requested. It is reviewed before it goes out.');
        self::go('/account/money');
    }

    /** Send the confirmation link again. Throttled, because it sends an email each time. */
    public function resendVerify(): void
    {
        Guard::must();
        Auth::wall();
        $u = Auth::user();
        if (!Verify::needed($u)) {
            Guard::flash('ok', 'Nothing to confirm.');
        } elseif (Guard::tooMany('verify', 3, 30)) {
            Guard::flash('bad', 'We have sent a few already. Check your spam folder, or wait a little before asking again.');
        } else {
            Verify::send($u);
            Guard::flash('ok', 'Sent again. Check your inbox, and your spam folder.');
        }
        self::go('/account');
    }

    /* ------------------------------------------------------------------------ Settings ------- */

    public function settings(): void
    {
        Auth::wall();
        $u = Auth::user();
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            $set = ['name' => trim((string) ($_POST['name'] ?? '')), 'phone' => trim((string) ($_POST['phone'] ?? ''))];
            $new = (string) ($_POST['pass'] ?? '');
            if ($new !== '') {
                if (strlen($new) < 8) {
                    Guard::flash('bad', 'A new password needs at least 8 characters.');
                    self::go('/account/settings');
                }
                if (!Auth::attempt((string) $u['email'], (string) ($_POST['current'] ?? ''))) {
                    Guard::flash('bad', 'Your current password did not match.');
                    self::go('/account/settings');
                }
                $set['pass'] = password_hash($new, PASSWORD_DEFAULT);
            }
            Db::update('users', $set, 'id = ?', [Auth::id()]);
            if (isset($set['pass'])) {
                Db::run('DELETE FROM sessions WHERE user_id = ? AND id <> ?', [Auth::id(), $_COOKIE['wwm'] ?? '']);
            }
            Guard::flash('ok', 'Saved.');
            self::go('/account/settings');
        }
        $this->show('settings', ['title' => 'Settings', 'u' => $u, 'w' => $this->mine()]);
    }

    private static function hex(string $v): string
    {
        return preg_match('/^#(?:[0-9a-f]{3}|[0-9a-f]{6})$/i', trim($v)) ? trim($v) : '';
    }
}
