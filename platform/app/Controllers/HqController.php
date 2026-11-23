<?php
declare(strict_types=1);

/** Staff only. Everything here reads the real tables; nothing is estimated. */
final class HqController
{
    /** HQ is for staff and the owner; each area needs its own permission, which the owner gives. */
    private function gate(string $perm = ''): void
    {
        Auth::wall();
        if ((!Auth::is('staff') && !Auth::is('owner')) || ($perm !== '' && !Staff::can($perm))) {
            http_response_code(403);
            View::render('errors/403', ['title' => 'Not for you']);
            exit;
        }
    }

    private function show(string $tpl, array $d): void
    {
        View::render('hq/' . $tpl, $d + ['tab' => 'account', 'appTitle' => 'HQ', 'back' => $tpl === 'index' ? null : App::url('/hq')]);
    }

    public function index(): void
    {
        $this->gate();
        $this->show('index', [
            'title'  => 'HQ',
            'nums'   => [
                'people'   => (int) Db::val('SELECT COUNT(*) FROM users', [], 0),
                'weddings' => (int) Db::val('SELECT COUNT(*) FROM weddings', [], 0),
                'public'   => (int) Db::val("SELECT COUNT(*) FROM weddings WHERE visibility='public'", [], 0),
                'guests'   => (int) Db::val('SELECT COUNT(*) FROM guests', [], 0),
                'vendors'  => (int) Db::val('SELECT COUNT(*) FROM vendors', [], 0),
                'raised'   => (int) Db::val("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status IN ('paid','released')", [], 0),
                'owed'     => (int) Db::val("SELECT COALESCE(SUM(amount),0) FROM payouts WHERE status='requested'", [], 0),
            ],
            'plans'  => Db::all('SELECT plan, COUNT(*) n FROM weddings GROUP BY plan ORDER BY n DESC'),
            'recent' => Db::all('SELECT id, name, email, role, created_at FROM users ORDER BY id DESC LIMIT 8'),
            'asks'   => Db::all("SELECT p.*, u.name, u.email FROM payouts p JOIN users u ON u.id = p.user_id WHERE p.status='requested' ORDER BY p.created_at LIMIT 10"),
        ]);
    }

    public function weddings(): void
    {
        $this->gate('weddings');
        $this->show('weddings', [
            'title' => 'Weddings',
            'rows'  => Db::all(
                'SELECT w.*, u.email FROM weddings w LEFT JOIN users u ON u.id = w.owner_id
                 ORDER BY w.created_at DESC, w.id DESC LIMIT 100'
            ),
        ]);
    }

    public function people(): void
    {
        $this->gate('people');
        $this->show('people', [
            'title' => 'People',
            'rows'  => Db::all('SELECT * FROM users ORDER BY id DESC LIMIT 200'),
        ]);
    }

    public function money(): void
    {
        $this->gate('money');
        $this->show('money', [
            'title'    => 'Money',
            'payments' => Db::all('SELECT * FROM payments ORDER BY created_at DESC LIMIT 60'),
            'payouts'  => Db::all('SELECT p.*, u.name FROM payouts p JOIN users u ON u.id = p.user_id ORDER BY p.created_at DESC LIMIT 40'),
            'in'       => (int) Db::val("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status IN ('paid','released')", [], 0),
            'fees'     => (int) Db::val("SELECT COALESCE(SUM(fee),0) FROM payments WHERE status IN ('paid','released')", [], 0),
        ]);
    }

    public function settings(): void
    {
        $this->gate('settings');
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            foreach (['pro_price', 'premium_price', 'fee_gift', 'email', 'whatsapp'] as $k) {
                if (array_key_exists($k, $_POST)) {
                    Db::run('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)',
                            [$k, trim((string) $_POST[$k])]);
                }
            }
            // Exchange rates: naira for one unit of each currency, for prices shown abroad.
            if (isset($_POST['fx']) && is_array($_POST['fx'])) {
                $fx = [];
                foreach (Fx::DEFAULT as $k => $def) {
                    $v = str_replace(',', '', trim((string) ($_POST['fx'][$k] ?? '')));
                    if ($k !== 'NGN' && is_numeric($v) && (float) $v > 0) {
                        $fx[$k] = round((float) $v, 4);
                    }
                }
                Db::run('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)', ['fx_rates', json_encode($fx)]);
            }
            // A ticked box is present in the POST, an unticked one is simply absent.
            Verify::set(isset($_POST['email_verify']));
            Guard::flash('ok', 'Saved. The pricing page now shows these.');
            header('Location: ' . App::url('/hq/settings'));
            exit;
        }
        $this->show('settings', [
            'title' => 'HQ settings',
            'v'     => [
                'pro_price'     => Site::setting('pro_price', '198000'),
                'premium_price' => Site::setting('premium_price', '448000'),
                'fee_gift'      => Site::setting('fee_gift', '4'),
                'email'         => Site::setting('email', ''),
                'whatsapp'      => Site::setting('whatsapp', ''),
            ],
            'fx'     => Fx::rates(),
            'verify' => ['on' => Verify::enabled(), 'since' => Site::setting('email_verify_since', '')],
            'demo'   => ['present' => Demo::present(), 'counts' => Demo::counts(), 'logins' => Demo::logins(), 'password' => Demo::PASSWORD],
        ]);
    }

    /** Adding or removing the demo is the owner's call alone. A staff account can look, not change. */
    private function ownerOnly(): void
    {
        $this->gate();
        if (!Auth::is('owner')) {
            Guard::flash('bad', 'Only the owner can add or remove the demo content.');
            header('Location: ' . App::url('/hq/settings'));
            exit;
        }
    }

    public function demoAdd(): void
    {
        $this->ownerOnly();
        Guard::must();
        try {
            Demo::install();
            Guard::flash('ok', 'Demo content added. The demo logins are listed below.');
        } catch (Throwable $e) {
            Guard::flash('bad', 'The demo content was not added: ' . $e->getMessage());
        }
        header('Location: ' . App::url('/hq/settings'));
        exit;
    }

    /** Put a real couple's page from the old site back, if it is not here already. */
    public function packAdd(string $key): void
    {
        $this->ownerOnly();
        Guard::must();
        try {
            $r = Pack::install($key);
            if ($key === 'market') {
                Guard::flash('ok', $r === 'added'
                    ? 'The marketplace sellers are back, verified, with their listings. Listings that were only demo content are gone.'
                    : 'Every seller is already on this site, so nothing was changed.');
            } else {
                Guard::flash('ok', $r === 'added'
                    ? (Pack::ALL[$key] ?? 'The') . "'s page is back, with their account. Their old password still works."
                    : (Pack::ALL[$key] ?? 'That') . "'s page is already on this site, so nothing was changed.");
            }
        } catch (Throwable $e) {
            Guard::flash('bad', 'The page was not added: ' . $e->getMessage());
        }
        header('Location: ' . App::url('/hq/settings'));
        exit;
    }

    public function demoRemove(): void
    {
        $this->ownerOnly();
        Guard::must();
        $gone = Demo::remove();
        Guard::flash('ok', $gone
            ? sprintf('Demo content removed: %d wedding pages, %d accounts, %d vendors, %d listings and %d community posts. Nothing else was touched.',
                $gone['weddings'] ?? 0, $gone['users'] ?? 0, $gone['vendors'] ?? 0, $gone['listings'] ?? 0, $gone['talks'] ?? 0)
            : 'There was no demo content to remove.');
        header('Location: ' . App::url('/hq/settings'));
        exit;
    }

    /**
     * Sends the signed-in admin a test message. Switching email confirmation on when the host cannot
     * actually deliver mail would leave new sign-ups waiting for a link that never comes, so this is
     * how an admin proves it works first.
     */
    public function testMail(): void
    {
        $this->gate('settings');
        Guard::must();
        $me = Auth::user();
        $ok = Mailer::send(
            (string) $me['email'],
            'A test email from your site',
            "If you can read this, your site can send email.\n\nIt is safe to switch on email confirmation.\n",
            ['button' => ['Open HQ', App::url('/hq/settings')]]
        );
        Guard::flash(
            $ok ? 'ok' : 'bad',
            $ok
                ? 'Handed to the mail server for ' . $me['email'] . '. Check the inbox and the spam folder — if it arrives, sending works.'
                : 'The server refused to send. Leave email confirmation off until that is sorted; ask your host about PHP mail().'
        );
        header('Location: ' . App::url('/hq/settings'));
        exit;
    }

    /* ---------------------------------------------------------------------------- the blog */

    public function blog(): void
    {
        $this->gate('blog');
        Blog::ensure();
        $this->show('blog', [
            'title' => 'Blog',
            'rows'  => Db::all('SELECT id, slug, title, kind, author, status, featured, published_at FROM posts ORDER BY COALESCE(published_at, NOW()) DESC, id DESC'),
        ]);
    }

    /** Write, edit, schedule, publish or delete a post. */
    public function blogEdit(string $id = ''): void
    {
        $this->gate('blog');
        Blog::ensure();
        $p = $id !== '' ? Db::one('SELECT * FROM posts WHERE id = ?', [(int) $id]) : null;
        if ($id !== '' && !$p) {
            header('Location: ' . App::url('/hq/blog'));
            exit;
        }
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            if ($p && ($_POST['do'] ?? '') === 'delete') {
                Db::run('DELETE FROM posts WHERE id = ?', [$p['id']]);
                Guard::flash('ok', 'Post deleted.');
                header('Location: ' . App::url('/hq/blog'));
                exit;
            }
            $t = static fn (string $k, int $max): string => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max);
            $title = $t('title', 255);
            if ($title === '') {
                Guard::flash('bad', 'A post needs a title.');
                header('Location: ' . App::url($p ? '/hq/blog/' . $p['id'] : '/hq/blog/new'));
                exit;
            }
            $source = str_replace("\r\n", "\n", (string) ($_POST['body'] ?? ''));
            if (!empty($_FILES['inline']['name'])) {
                $pic = Images::store($_FILES['inline'], 'blog');
                if ($pic !== null) {
                    $source = rtrim($source) . "\n\n![" . $t('inline_alt', 160) . '](' . $pic . ')';
                }
            }
            $kind = in_array($_POST['kind'] ?? '', ['guide', 'news', 'feature'], true) ? (string) $_POST['kind'] : 'guide';
            $live = ($_POST['status'] ?? '') === 'live';
            $date = trim((string) ($_POST['date'] ?? ''));
            $when = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date . ' ' . (preg_match('/^\d{2}:\d{2}$/', (string) ($_POST['time'] ?? '')) ? $_POST['time'] : '09:00') . ':00' : null;
            $row = [
                'title' => $title, 'kind' => $kind, 'category' => Blog::KIND[$kind], 'author' => $t('author', 120),
                'excerpt' => $t('excerpt', 500), 'source' => $source,
                'body' => Blog::isHtml($source) ? $source : Blog::toHtml($source),
                'meta_title' => $t('meta_title', 190), 'meta_desc' => $t('meta_desc', 300),
                'featured' => isset($_POST['featured']) ? 1 : 0,
                'status' => $live ? 'live' : 'draft',
                'published_at' => $when ?? ($live ? ($p['published_at'] ?? date('Y-m-d H:i:s')) : ($p['published_at'] ?? null)),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            $slug = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', $t('slug', 160)), '-'));
            if ($slug === '' || in_array($slug, ['guides', 'news', 'features', 'feed-xml'], true)) {
                $slug = $p && $t('slug', 160) === '' ? (string) $p['slug'] : $title;
            }
            if (!$p || $slug !== (string) $p['slug']) {
                $base = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', (string) (iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug) ?: $slug)), '-')) ?: 'post';
                if (in_array($base, ['guides', 'news', 'features'], true)) {
                    $base .= '-post';
                }
                $row['slug'] = $base;
                for ($n = 2; Db::val('SELECT 1 FROM posts WHERE slug = ? AND id <> ?', [$row['slug'], (int) ($p['id'] ?? 0)]); $n++) {
                    $row['slug'] = $base . '-' . $n;
                }
            }
            if (!empty($_FILES['cover']['name'])) {
                $cover = Images::store($_FILES['cover'], 'blog');
                if ($cover === null) {
                    Guard::flash('bad', 'That cover photo could not be used. Try a JPEG or PNG under 12 MB.');
                } else {
                    $row['cover'] = $cover;
                }
            } elseif (isset($_POST['no_cover'])) {
                $row['cover'] = '';
            }
            if ($p) {
                Db::update('posts', $row, 'id = ?', [$p['id']]);
                $pid = (int) $p['id'];
            } else {
                $pid = (int) Db::insert('posts', $row);
            }
            $saved = Db::one('SELECT slug, status, published_at FROM posts WHERE id = ?', [$pid]);
            $future = $saved && $saved['published_at'] && strtotime((string) $saved['published_at']) > time();
            Guard::flash('ok', !$live ? 'Saved as a draft.' : ($future ? 'Scheduled. It goes live on ' . date('j M, g:ia', (int) strtotime((string) $saved['published_at'])) . '.' : 'Published.'));
            header('Location: ' . App::url('/hq/blog/' . $pid));
            exit;
        }
        $this->show('blog-edit', ['title' => $p ? 'Edit post' : 'New post', 'p' => $p]);
    }

    /* --------------------------------------------------------------------------- staff */

    /** The owner adds staff, picks what each may do, changes it, or takes someone off staff. */
    public function staff(): void
    {
        $this->gate();
        if (!Auth::is('owner')) {
            http_response_code(403);
            View::render('errors/403', ['title' => 'Not for you']);
            exit;
        }
        Staff::table();
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            $do = (string) ($_POST['do'] ?? '');
            $perms = array_keys(array_filter((array) ($_POST['perm'] ?? [])));
            if ($do === 'add') {
                $email = strtolower(trim((string) ($_POST['email'] ?? '')));
                $name = mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 120);
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    Guard::flash('bad', 'That email does not look right.');
                    header('Location: ' . App::url('/hq/staff'));
                    exit;
                }
                $u = Db::one('SELECT * FROM users WHERE email = ?', [$email]);
                if ($u && (string) $u['role'] === 'owner') {
                    Guard::flash('bad', 'That is the owner account, which can already do everything.');
                    header('Location: ' . App::url('/hq/staff'));
                    exit;
                }
                $new = !$u;
                $uid = $u ? (int) $u['id'] : (int) Db::insert('users', ['email' => $email, 'name' => $name !== '' ? $name : $email, 'role' => 'staff', 'pass' => '']);
                if ($u) {
                    Db::update('users', ['role' => 'staff'], 'id = ?', [$uid]);
                }
                Staff::set($uid, $perms);
                // A new person sets their own password from a link; nobody else ever knows it.
                $tok = bin2hex(random_bytes(32));
                Db::run("INSERT INTO tokens (user_id, kind, token, expires_at) VALUES (?, 'reset', ?, DATE_ADD(NOW(), INTERVAL 7 DAY))", [$uid, $tok]);
                $link = App::url('/reset/' . $tok);
                $sent = Mailer::send($email, 'You have been added to the Wedding Websites & More team',
                    "Hello" . ($name !== '' ? ' ' . $name : '') . ",\n\nYou now have staff access to Wedding Websites & More.\n"
                    . ($new ? "Choose your password here (the link works once, for 7 days):\n\n" : "Sign in as usual, or set a new password here (the link works once, for 7 days):\n\n")
                    . $link . "\n\nThen open HQ: " . App::url('/hq') . "\n\n— Wedding Websites & More\n",
                    ['button' => [$new ? 'Choose your password' : 'Set a new password', $link], 'title' => 'Welcome to the team']);
                Guard::flash('ok', ($new ? 'Added. ' : 'Moved onto staff. ') . ($sent ? 'We emailed them a link to set their password.' : 'Email could not be sent from this server, so send them this link yourself: ' . $link));
            } elseif ($do === 'save') {
                $uid = (int) ($_POST['id'] ?? 0);
                if (Db::val("SELECT 1 FROM users WHERE id = ? AND role = 'staff'", [$uid])) {
                    Staff::set($uid, $perms);
                    Guard::flash('ok', 'Access updated.');
                }
            } elseif ($do === 'remove') {
                $uid = (int) ($_POST['id'] ?? 0);
                if (Db::val("SELECT 1 FROM users WHERE id = ? AND role = 'staff'", [$uid])) {
                    Db::update('users', ['role' => 'member'], 'id = ?', [$uid]);
                    Db::run('DELETE FROM staff_perms WHERE user_id = ?', [$uid]);
                    Guard::flash('ok', 'Taken off staff. Their account stays, without HQ.');
                }
            }
            header('Location: ' . App::url('/hq/staff'));
            exit;
        }
        $rows = Staff::all();
        foreach ($rows as &$r) {
            $r['perms'] = Staff::perms((int) $r['id']);
        }
        unset($r);
        $this->show('staff', ['title' => 'Staff', 'rows' => $rows]);
    }

    /* ------------------------------------------------------------------- the site's words */

    /** Edit the words and pictures on any page of the site. Empty means the standard text. */
    public function content(): void
    {
        $this->gate('content');
        Content::table();
        $page = isset(Content::PAGES[$_GET['page'] ?? '']) ? (string) $_GET['page'] : 'home';
        $fields = Content::fields($page);
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            $changed = 0;
            foreach ($fields as $f) {
                $id = str_replace('.', '__', $f['key']);
                if ($f['type'] === 'timg') {
                    if (!empty($_POST['reset'][$id])) {
                        Content::set($f['key'], '');
                        $changed++;
                    } elseif (!empty($_FILES['img']['name'][$id])) {
                        $file = ['name' => $_FILES['img']['name'][$id], 'type' => $_FILES['img']['type'][$id], 'tmp_name' => $_FILES['img']['tmp_name'][$id],
                                 'error' => $_FILES['img']['error'][$id], 'size' => $_FILES['img']['size'][$id]];
                        $path = Images::store($file, 'site');
                        if ($path === null) {
                            Guard::flash('bad', 'One of the pictures could not be used. Try a JPEG or PNG under 12 MB.');
                        } else {
                            Content::set($f['key'], $path);
                            $changed++;
                        }
                    }
                    continue;
                }
                if (!array_key_exists($id, (array) ($_POST['v'] ?? []))) {
                    continue;
                }
                $val = str_replace("\r\n", "\n", trim((string) $_POST['v'][$id]));
                $now = Content::get($f['key'], $f['default']);
                if ($val === $now) {
                    continue;
                }
                // Typing the standard text back, or clearing the box, returns to the standard text.
                Content::set($f['key'], $val === $f['default'] ? '' : mb_substr($val, 0, 60000));
                $changed++;
            }
            Guard::flash('ok', $changed ? 'Saved. ' . $changed . ' change' . ($changed === 1 ? '' : 's') . ' are live.' : 'Nothing changed.');
            header('Location: ' . App::url('/hq/content?page=' . $page));
            exit;
        }
        $this->show('content', ['title' => 'Site content', 'page' => $page, 'fields' => $fields]);
    }

    /* ----------------------------------------------------------------------- Google Drive */

    /** Settings for Google Drive: the app's keys, full or app-made folders only, the parent folder. */
    public function drive(): void
    {
        $this->gate('settings');
        Guard::must();
        $do = (string) ($_POST['do'] ?? '');
        $put = static fn (string $k, string $v) => Db::run('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)', [$k, $v]);
        if ($do === 'keys') {
            $put('drive_client_id', trim((string) ($_POST['client_id'] ?? '')));
            if (trim((string) ($_POST['client_secret'] ?? '')) !== '') {
                $put('drive_client_secret', trim((string) $_POST['client_secret']));
            }
            $scope = isset($_POST['full']) ? 'full' : 'file';
            if ($scope !== Site::setting('drive_scope', 'file')) {
                $put('drive_scope', $scope);
                Drive::disconnect();   // the access Google gave was for the other choice
            }
            $parent = trim((string) ($_POST['parent'] ?? ''));
            $put('drive_parent', $parent === '' ? '' : Drive::idFrom($parent));
            Guard::flash('ok', Drive::connected() ? 'Saved.' : 'Saved. Now connect your Google account.');
        } elseif ($do === 'disconnect') {
            Drive::disconnect();
            Guard::flash('ok', 'Google Drive disconnected. Photos wait on the server until it is connected again.');
        } elseif ($do === 'sync') {
            Drive::retry();
            $n = Drive::sync(60);
            Guard::flash('ok', $n . ' photo' . ($n === 1 ? '' : 's') . ' sent to Drive. Any still waiting belong to weddings without a folder yet.');
        }
        header('Location: ' . App::url('/hq/settings#drive'));
        exit;
    }

    public function driveConnect(): void
    {
        $this->gate('settings');
        if (!Drive::configured()) {
            Guard::flash('bad', 'Add the Google client ID and secret first.');
            header('Location: ' . App::url('/hq/settings#drive'));
            exit;
        }
        header('Location: ' . Drive::authUrl());
        exit;
    }

    public function driveCallback(): void
    {
        $this->gate('settings');
        $err = isset($_GET['error']) ? 'Google said: ' . (string) $_GET['error'] : Drive::finish((string) ($_GET['code'] ?? ''), (string) ($_GET['state'] ?? ''));
        Guard::flash($err === '' ? 'ok' : 'bad', $err === '' ? 'Google Drive is connected. Give each wedding its folder in Weddings.' : $err);
        header('Location: ' . App::url('/hq/settings#drive'));
        exit;
    }

    /** Give a wedding its folder: make one, or use one that already exists. */
    public function driveFolder(string $id): void
    {
        $this->gate('weddings');
        Guard::must();
        $w = Db::one('SELECT * FROM weddings WHERE id = ?', [(int) $id]);
        if (!$w) {
            header('Location: ' . App::url('/hq/weddings'));
            exit;
        }
        if (!Drive::connected()) {
            Guard::flash('bad', 'Connect Google Drive in Settings first.');
        } else {
            $err = ($_POST['do'] ?? '') === 'create' ? Drive::create($w) : Drive::attach($w, (string) ($_POST['link'] ?? ''));
            Guard::flash($err === '' ? 'ok' : 'bad', $err === '' ? Wedding::names($w) . ' now save their guests\' photos to Drive.' : $err);
        }
        header('Location: ' . App::url('/hq/weddings#w' . (int) $w['id']));
        exit;
    }

    /* ------------------------------------------------------------------ marketplace */

    /**
     * Sellers and the reports against them. From five open reports a seller is paused and asked to
     * verify by hand; from fifteen they are removed. Here HQ clears reports, verifies, pauses,
     * reinstates, removes or deletes for good.
     */
    public function market(): void
    {
        $this->gate('market');
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            $v = Db::one('SELECT * FROM vendors WHERE id = ?', [(int) ($_POST['id'] ?? 0)]);
            $do = (string) ($_POST['do'] ?? '');
            if ($v) {
                $msg = '';
                switch ($do) {
                    case 'verify':
                        Db::update('vendors', ['verified' => 1], 'id = ?', [$v['id']]);
                        $msg = $v['name'] . ' is verified.';
                        break;
                    case 'unverify':
                        Db::update('vendors', ['verified' => 0], 'id = ?', [$v['id']]);
                        $msg = $v['name'] . ' is no longer verified.';
                        break;
                    case 'suspend':
                        Market::setStatus($v, 'suspended');
                        $msg = $v['name'] . ' is paused, and we emailed them to ask for verification.';
                        break;
                    case 'reinstate':
                        Market::setStatus($v, 'active');
                        $msg = $v['name'] . ' is live again.';
                        break;
                    case 'remove':
                        Market::setStatus($v, 'removed');
                        $msg = $v['name'] . ' is removed from the site.';
                        break;
                    case 'clear':
                        Db::run("UPDATE seller_reports SET status = 'cleared' WHERE vendor_id = ?", [$v['id']]);
                        $msg = 'Reports on ' . $v['name'] . ' are cleared.';
                        break;
                    case 'delete':
                        $msg = self::deleteSeller($v);
                        break;
                }
                if ($msg !== '') {
                    Guard::flash('ok', $msg);
                }
            }
            header('Location: ' . App::url('/hq/market' . ($do === 'delete' ? '' : '#s' . (int) ($v['id'] ?? 0))));
            exit;
        }
        $sellers = Db::all("SELECT v.*, u.email,
                (SELECT COUNT(*) FROM listings l WHERE l.partner_id = v.user_id) listings,
                (SELECT COUNT(*) FROM seller_reports r WHERE r.vendor_id = v.id AND r.status = 'open') reports
            FROM vendors v LEFT JOIN users u ON u.id = v.user_id
            ORDER BY reports DESC, FIELD(v.status, 'suspended', 'removed', 'active'), v.name");
        $reports = [];
        foreach (Db::all("SELECT r.*, u.name, u.email, l.title FROM seller_reports r LEFT JOIN users u ON u.id = r.user_id LEFT JOIN listings l ON l.id = r.listing_id
                          WHERE r.status = 'open' ORDER BY r.created_at DESC") as $r) {
            $reports[(int) $r['vendor_id']][] = $r;
        }
        $this->show('market', ['title' => 'Marketplace', 'sellers' => $sellers, 'reports' => $reports]);
    }

    /** A seller gone for good: their shop, listings and reports. Their account stays, as an ordinary one. */
    private static function deleteSeller(array $v): string
    {
        if ($v['user_id']) {
            Db::run('DELETE FROM listings WHERE partner_id = ?', [$v['user_id']]);
            Db::run("UPDATE users SET role = 'member' WHERE id = ? AND role = 'partner'", [$v['user_id']]);
        }
        Db::run('DELETE FROM seller_reports WHERE vendor_id = ?', [$v['id']]);
        Db::run('DELETE FROM vendors WHERE id = ?', [$v['id']]);
        return $v['name'] . ' is deleted, with their listings.';
    }

    /** What can be sold: HQ adds categories and opens or closes them. Sellers only list in open ones. */
    public function categories(): void
    {
        $this->gate('market');
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            $do = (string) ($_POST['do'] ?? '');
            $name = mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 80);
            $about = mb_substr(trim((string) ($_POST['about'] ?? '')), 0, 255);
            if ($do === 'add' && $name !== '') {
                $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower((string) (iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) ?: $name))), '-') ?: 'category';
                $slug = substr($slug, 0, 36);
                for ($n = 2, $base = $slug; Db::val('SELECT 1 FROM categories WHERE slug = ?', [$slug]); $n++) {
                    $slug = $base . '-' . $n;
                }
                Db::insert('categories', ['slug' => $slug, 'name' => $name, 'about' => $about, 'open' => 1, 'sort' => (int) Db::val('SELECT COALESCE(MAX(sort), 0) + 1 FROM categories', [], 0)]);
                Guard::flash('ok', $name . ' is open. Sellers can list in it now.');
            } elseif ($do === 'save') {
                $id = (int) ($_POST['id'] ?? 0);
                if ($name !== '') {
                    Db::update('categories', ['name' => $name, 'about' => $about, 'open' => isset($_POST['open']) ? 1 : 0], 'id = ?', [$id]);
                    Guard::flash('ok', 'Saved.');
                }
            }
            header('Location: ' . App::url('/hq/categories'));
            exit;
        }
        $this->show('categories', ['title' => 'Categories', 'rows' => Market::categories()]);
    }

    /** What the site's emails look like, with sample words. ?k= shows one on its own, as sent. */
    public function emails(): void
    {
        $this->gate('settings');
        $u = App::url('/');
        $samples = [
            'chat'   => ['New message from AbujaCarhire', "Hello Ada,\n\nAbujaCarhire wrote:\n\nYes, the Range Rover is free on 14 December. Full day with a driver, ribbons and flowers included. I will send you an invoice with my payment link.\n\nReply here: {$u}account/messages\n\nWe never hold money for sellers. Pay only through the seller's own invoice or link, and be sure who you are paying.",
                         ['button' => ['Read and reply', $u . 'account/messages'], 'quote' => 'Yes, the Range Rover is free on 14 December. Full day with a driver, ribbons and flowers included. I will send you an invoice with my payment link.', 'why' => 'You get this email because of a chat on the Wedding Websites & More marketplace.']],
            'live'   => ['Chidi & Amaka: the livestream link is ready', "The livestream for Chidi & Amaka is ready, starting Saturday 19 December, 11:00am.\n\nWatch it on their page:\n{$u}w/chidi-amaka#live\n\nWe will send one more reminder fifteen minutes before it starts.\n\nStop these reminders: {$u}remind/stop/sample",
                         ['button' => ['Watch the livestream', $u . 'w/chidi-amaka#live'], 'unsub' => $u . 'remind/stop/sample', 'why' => 'You asked for a reminder about Chidi & Amaka’s livestream.']],
            'reset'  => ['Reset your password', "Hello,\n\nSomebody asked to reset the password on your Wedding Websites & More account.\nTo choose a new one, open this link:\n\n{$u}reset/sample\n\nIt works once and expires in 2 hours. If this was not you, ignore this email and nothing will change.",
                         ['button' => ['Choose a new password', $u . 'reset/sample'], 'why' => 'You get this email because someone asked to reset the password on this account.']],
            'verify' => ['Confirm your email', "Hello Ada,\n\nPlease confirm this is your email address by opening this link:\n\n{$u}verify/sample\n\nIt works once and expires in 3 days. If you did not make an account, ignore this email.",
                         ['button' => ['Confirm my email', $u . 'verify/sample'], 'title' => 'Confirm your email, Ada']],
            'paused' => ['Your shop is paused while we verify Your Business', "Hello,\n\nWe have paused Your Business on Wedding Websites & More while we verify the business by hand. Your listings are hidden until then.\n\nTo verify, reply to this email with: your full name, a phone number we can call, your business address, and a photo of a government ID or your CAC certificate. We will look at it and let you know.", []],
        ];
        $k = (string) ($_GET['k'] ?? '');
        if (isset($samples[$k])) {
            [$subj, $body, $o] = $samples[$k];
            header('Content-Type: text/html; charset=utf-8');
            echo Mailer::html($subj, $body, $o);
            return;
        }
        $this->show('emails', ['title' => 'Emails', 'samples' => $samples]);
    }

    /* ------------------------------------------------------------------ editing the live site */

    /** Edit mode on or off, then back to the page. */
    public function edit(): void
    {
        $this->gate('content');
        Guard::boot();
        $_SESSION['cms_edit'] = empty($_GET['off']);
        $back = (string) ($_GET['back'] ?? '/');
        $back = $back !== '' && $back[0] === '/' && !str_starts_with($back, '//') ? $back : '/';
        header('Location: ' . App::url($back));
        exit;
    }

    /** Words changed on a page: a t() key, or that exact text everywhere or on one page. */
    public function editSave(): void
    {
        $this->gate('content');
        Guard::must();
        $kind = (string) ($_POST['kind'] ?? '');
        $value = str_replace("\r", '', (string) ($_POST['value'] ?? ''));
        if ($kind === 'key') {
            $key = (string) ($_POST['key'] ?? '');
            if (!preg_match('/^[a-z0-9_.-]{1,120}$/', $key)) {
                self::json(['ok' => false, 'msg' => 'That cannot be saved.'], 400);
            }
            Content::set($key, trim($value));
        } elseif ($kind === 'text') {
            $scope = (string) ($_POST['scope'] ?? '*');
            $scope = $scope === '*' ? '*' : '/' . ltrim(mb_substr($scope, 0, 180), '/');
            LiveEdit::saveText((string) ($_POST['original'] ?? ''), $value, $scope);
        } else {
            self::json(['ok' => false, 'msg' => 'Nothing to save.'], 400);
        }
        self::json(['ok' => true]);
    }

    /** A picture replaced on a page: the new one is stored in every size and used everywhere. */
    public function editImage(): void
    {
        $this->gate('content');
        Guard::must();
        $orig = trim((string) ($_POST['original'] ?? ''));
        $key = (string) ($_POST['key'] ?? '');
        if (($orig === '' && $key === '') || empty($_FILES['file']['name'])) {
            self::json(['ok' => false, 'msg' => 'Choose a picture.'], 400);
        }
        $img = Images::store($_FILES['file'], 'site');
        if ($img === null) {
            self::json(['ok' => false, 'msg' => 'That picture could not be used. Try a JPEG or PNG under 12 MB.'], 400);
        }
        if ($key !== '' && preg_match('/^[a-z0-9_.-]{1,120}$/', $key)) {
            Content::set($key, $img);
        } else {
            LiveEdit::saveImg($orig, $img);
        }
        self::json(['ok' => true, 'path' => $img]);
    }

    public function editUndo(): void
    {
        $this->gate('content');
        Guard::must();
        LiveEdit::remove((int) ($_POST['id'] ?? 0));
        Guard::flash('ok', 'Undone. The page shows what it showed before.');
        header('Location: ' . App::url('/hq/content#live-edits'));
        exit;
    }

    /** The site's own ads: which slots show them, and every ad's words, picture and link. */
    public function ads(): void
    {
        $this->gate('content');
        Promo::table();
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            $do = (string) ($_POST['do'] ?? '');
            $set = static fn (string $k, string $v) => Db::run('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)', [$k, $v]);
            if ($do === 'slots') {
                $set('ads_premium', isset($_POST['on']) ? '1' : '0');
                $slots = [];
                foreach (array_keys(Promo::SLOTS) as $k) {
                    $slots[$k] = isset($_POST['slot'][$k]) ? 1 : 0;
                }
                $set('ad_slots', (string) json_encode($slots));
                Guard::flash('ok', 'Saved. Slots that are off show nothing, and the page closes up around them.');
            } elseif ($do === 'delete') {
                Db::run('DELETE FROM ads WHERE id = ?', [(int) ($_POST['id'] ?? 0)]);
                Guard::flash('ok', 'Ad deleted.');
            } elseif ($do === 'save') {
                $id = (int) ($_POST['id'] ?? 0);
                $t = static fn (string $k, int $n) => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $n);
                $link = $t('link', 500);
                if ($link !== '' && $link[0] !== '/' && !preg_match('~^https://~i', $link)) {
                    $link = 'https://' . ltrim(preg_replace('~^http://~i', '', $link), '/');
                }
                $row = [
                    'title' => $t('title', 160), 'line' => $t('line', 300), 'button' => $t('button', 60), 'link' => $link,
                    'show_price' => isset($_POST['show_price']) ? 1 : 0, 'active' => isset($_POST['active']) ? 1 : 0,
                    'slots' => implode(',', array_values(array_intersect(array_keys(Promo::SLOTS), array_keys((array) ($_POST['slots'] ?? []))))),
                    'sort' => (int) ($_POST['sort'] ?? 0),
                ];
                if (!empty($_FILES['image']['name'])) {
                    $img = Images::store($_FILES['image'], 'ads');
                    if ($img === null) {
                        Guard::flash('bad', 'That picture could not be used. Try a JPEG or PNG under 12 MB.');
                        header('Location: ' . App::url('/hq/ads'));
                        exit;
                    }
                    $row['image'] = $img;
                } elseif (!empty($_POST['image_off'])) {
                    $row['image'] = '';
                }
                if ($row['title'] === '') {
                    Guard::flash('bad', 'An ad needs a headline.');
                } elseif ($id) {
                    Db::update('ads', $row, 'id = ?', [$id]);
                    Guard::flash('ok', 'Ad saved.');
                } else {
                    $id = (int) Db::insert('ads', $row);
                    Guard::flash('ok', 'Ad added.');
                }
            }
            header('Location: ' . App::url('/hq/ads' . (!empty($id) ? '#ad' . $id : '')));
            exit;
        }
        $this->show('ads', ['title' => 'Ads', 'ads' => Db::all('SELECT * FROM ads ORDER BY sort, id'), 'slots' => Promo::slots(), 'on' => Site::setting('ads_premium', '1') !== '0']);
    }

    private static function json(array $d, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($d, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
