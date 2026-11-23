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
        // Staff running the managed service open a couple's dashboard from HQ and work on their page.
        if ($id = Managed::managing()) {
            return Db::one('SELECT * FROM weddings WHERE id = ?', [$id]);
        }
        return Db::one('SELECT * FROM weddings WHERE owner_id = ? ORDER BY id LIMIT 1', [Auth::id()]);
    }

    /** The couple asks for their own account number for gifts and spraying. */
    public function bank(): void
    {
        Guard::must();
        $w = $this->need();
        $err = Bachs::assign($w);
        Guard::flash($err === '' ? 'ok' : 'bad', $err === '' ? 'Your account number is ready. Guests can now send gifts and spray by bank transfer.' : $err);
        self::go('/account/money');
    }

    /** The name on a bank account, for the couple to confirm before saving it. */
    public function bankCheck(): void
    {
        Guard::must();
        $w = $this->need();
        $code = preg_replace('/\D/', '', (string) ($_POST['bank_code'] ?? ''));
        $num = preg_replace('/\D/', '', (string) ($_POST['account_number'] ?? ''));
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['name' => strlen($num) === 10 && $code !== '' ? Bachs::resolve($code, $num) : ''], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** The couple's own bank account: every gift and spray is paid on to it the moment it lands. */
    public function bankSave(): void
    {
        Guard::must();
        $w = $this->need();
        if (Managed::managing()) {
            Guard::flash('bad', 'Only the couple can set where their money goes.');
            self::go('/account/money#settle');
        }
        $code = preg_replace('/\D/', '', (string) ($_POST['bank_code'] ?? ''));
        $num = preg_replace('/\D/', '', (string) ($_POST['account_number'] ?? ''));
        $bank = '';
        foreach (Bachs::banks() as $b) { if ($b['code'] === $code) { $bank = $b['name']; } }
        if ($bank === '' || strlen($num) !== 10) {
            Guard::flash('bad', 'Choose your bank and enter your 10-digit account number.');
            self::go('/account/money#settle');
        }
        $name = Bachs::resolve($code, $num);
        if ($name === '') {
            Guard::flash('bad', 'Your bank did not recognise that account number. Check it and try again.');
            self::go('/account/money#settle');
        }
        if ((string) $w['va_number'] === '' && ($e = Bachs::assign($w)) !== '') {
            Guard::flash('bad', $e);
            self::go('/account/money#settle');
        }
        $w = Db::one('SELECT * FROM weddings WHERE id = ?', [$w['id']]);
        $err = '';
        $dest = Bachs::destination($w, $code, $bank, $num, $name, $err);
        if ($dest === '') {
            Guard::flash('bad', 'Bachs could not save that account: ' . $err);
            self::go('/account/money#settle');
        }
        Db::update('weddings', ['payout_bank_code' => $code, 'payout_bank_name' => $bank, 'payout_account' => $num, 'payout_name' => $name, 'payout_dest' => $dest], 'id = ?', [$w['id']]);
        Settle::run();   // anything that arrived before this goes out now
        Guard::flash('ok', 'Saved. Every gift and spray now goes straight to ' . $bank . ' ••' . substr($num, -4) . ' as it arrives.');
        self::go('/account/money#settle');
    }

    /** A change request to our team (Premium and High Society): words, and up to three photos. */
    public function request(): void
    {
        Guard::must();
        $w = $this->need();
        if (!Managed::has($w) || Managed::managing()) {
            self::go('/account');
        }
        $body = trim((string) ($_POST['body'] ?? ''));
        if (mb_strlen($body) < 3) {
            Guard::flash('bad', 'Tell us what to add or change.');
            self::go('/account#managed');
        }
        $files = [];
        foreach (self::fileList('files') as $f) {
            if (count($files) < 3 && ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK && ($img = Images::store($f, 'requests/' . (int) $w['id']))) {
                $files[] = $img;
            }
        }
        Managed::add($w, (int) Auth::id(), $body, $files);
        Guard::flash('ok', 'Sent to our team. We will make the change and let you know here.');
        self::go('/account#managed');
    }

    /** Every page here needs an account and a wedding behind it. */
    private function need(): array
    {
        Auth::wall();
        Guard::boot();
        $_SESSION['acct_ctx'] = 'wedding';
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
        Guard::boot();
        $w = $this->mine();
        $shop = Auth::is('partner') || Db::val('SELECT 1 FROM vendors WHERE user_id = ?', [Auth::id()]);
        if (($_GET['as'] ?? '') === 'wedding') {
            $_SESSION['acct_ctx'] = 'wedding';
        } elseif ($shop && (!$w || ($_SESSION['acct_ctx'] ?? '') === 'shop')) {
            // A seller lands in their shop, unless they last chose their wedding page.
            self::go('/account/shop');
        }
        if (!$w) {
            // Someone who only talks to sellers lands in their messages.
            if (($_SESSION['acct_ctx'] ?? '') === 'customer' || Db::val('SELECT 1 FROM messages WHERE user_id = ? LIMIT 1', [Auth::id()])) {
                self::go('/account/messages');
            }
            self::go('/account/new');
        }
        $_SESSION['acct_ctx'] = 'wedding';
        $id = (int) $w['id'];
        $this->show('index', [
            'title' => Wedding::names($w),
            'w'     => $w,
            'requests' => Managed::has($w) ? Managed::forWedding((int) $w['id']) : [],
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
            $linkBefore = trim((string) ($d['live_url'] ?? ''));
            if (array_key_exists('live_url', $_POST) && Wedding::has($w, 'live')) {
                $live = trim((string) $_POST['live_url']);
                if ($live === '' || preg_match('~^https://(www\.)?(youtube\.com|youtu\.be)/~i', $live)) {
                    $d['live_url'] = $live;
                }
                $at = trim((string) ($_POST['live_at'] ?? ''));
                $d['live_at'] = preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $at) ? $at : '';
            }
            // Background music, Premium and up: an uploaded song, or one fetched from a link.
            if (Wedding::has($w, 'music')) {
                $err = '';
                $old = (string) ($d['music'] ?? '');
                $link = trim((string) ($_POST['music_link'] ?? ''));
                $new = '';
                if (!empty($_POST['music_off'])) {
                    Music::remove($old);
                    $d['music'] = '-';
                    $d['music_link'] = '';
                } elseif (!empty($_FILES['music_file']['name'])) {
                    $new = Music::store($_FILES['music_file'], (int) $w['id'], $err);
                    $d['music_link'] = '';
                } elseif ($link !== '' && $link !== (string) ($d['music_link'] ?? '')) {
                    $new = Music::import($link, (int) $w['id'], $err);
                    if ($new !== '') {
                        $d['music_link'] = $link;
                    }
                }
                if ($new !== '') {
                    if ($old !== $new) {
                        Music::remove($old);
                    }
                    $d['music'] = $new;
                }
                $d['music_title'] = mb_substr(trim((string) ($_POST['music_title'] ?? ($d['music_title'] ?? ''))), 0, 80);
                $vol = (int) ($_POST['music_volume'] ?? 20);
                $d['music_volume'] = isset(Music::VOLUMES[$vol]) ? $vol : 20;
                if ($err !== '') {
                    Guard::flash('bad', 'The song was not added: ' . $err);
                }
            }
            // The colour code: up to six named colours, shown under the invite.
            if (Wedding::has($w, 'colours') && isset($_POST['pal_name'])) {
                $pal = [];
                foreach ((array) $_POST['pal_name'] as $i => $name) {
                    $name = mb_substr(trim((string) $name), 0, 40);
                    $hex = self::hex((string) ($_POST['pal_hex'][$i] ?? ''));
                    if ($name !== '' && $hex !== '') {
                        $pal[] = [$name, $hex];
                    }
                }
                $d['palette'] = $pal;
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
            // A new livestream link: everyone who tapped "Remind me" hears about it now.
            if (trim((string) ($d['live_url'] ?? '')) !== '' && trim((string) ($d['live_url'] ?? '')) !== $linkBefore) {
                App::later(static function (): void { Reminder::run(200); });
            }
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

    /** Relationship statuses a squad member can show, in the words the card uses. */
    public const RELATIONSHIP = ['' => 'Not saying', 'single' => 'Single', 'taken' => 'Taken', 'engaged' => 'Engaged', 'married' => 'Married'];

    public function squad(): void
    {
        $w = $this->need();
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            $fields = [
                'side'         => in_array($_POST['side'] ?? '', ['bride', 'groom', 'both'], true) ? (string) $_POST['side'] : 'both',
                'name'         => mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 160),
                'role'         => mb_substr(trim((string) ($_POST['role'] ?? '')), 0, 120),
                'words'        => mb_substr(trim((string) ($_POST['words'] ?? '')), 0, 240),
                'work'         => mb_substr(trim((string) ($_POST['work'] ?? '')), 0, 160),
                'hobby'        => mb_substr(trim((string) ($_POST['hobby'] ?? '')), 0, 160),
                'relationship' => isset(self::RELATIONSHIP[$_POST['relationship'] ?? '']) ? (string) $_POST['relationship'] : '',
                'social'       => self::socialLink((string) ($_POST['social'] ?? '')),
            ];
            if (!empty($_FILES['photo']['name']) && ($_POST['do'] ?? '') !== 'delete') {
                $img = Images::store($_FILES['photo'], 'squad/' . (int) $w['id']);
                if ($img === null) {
                    Guard::flash('bad', 'That photo could not be used. Try a JPEG or PNG under 12 MB.');
                    self::go('/account/squad');
                }
                $fields['photo'] = $img;
            } elseif (!empty($_POST['photo_off'])) {
                $fields['photo'] = '';
            }
            $this->rowAction('squad', (int) $w['id'], $fields, 'name');
            self::go('/account/squad');
        }
        $this->show('squad', ['title' => 'The squad', 'w' => $w, 'rows' => Db::all('SELECT * FROM squad WHERE wedding_id = ? ORDER BY sort, id', [$w['id']])]);
    }

    /** One social link: a full address, or an @handle (taken as Instagram). Anything else is dropped. */
    public static function socialLink(string $v): string
    {
        $v = trim($v);
        if ($v === '') {
            return '';
        }
        if (preg_match('/^@?([A-Za-z0-9._]{1,30})$/', $v, $m) && !str_contains($v, '.com')) {
            return 'https://instagram.com/' . $m[1];
        }
        if (!preg_match('~^https?://~i', $v)) {
            $v = 'https://' . $v;
        }
        $host = (string) parse_url($v, PHP_URL_HOST);
        return filter_var($v, FILTER_VALIDATE_URL) && str_contains($host, '.') ? mb_substr($v, 0, 255) : '';
    }

    /**
     * "Our story" as diary cards the couple write themselves: a few words each, a photo if they like,
     * in the order they choose. When there are cards they take the place of the single story.
     */
    public const DIARY_MAX = 8;
    public const DIARY_WORDS = 80;

    public function story(): void
    {
        $w = $this->need();
        $d = Wedding::design($w);
        $old = is_array($d['chapters'] ?? null) ? array_values($d['chapters']) : [];
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            $cards = [];
            $cut = [];
            $files = self::fileList('photo');
            $signs = self::signs($w);
            foreach ((array) ($_POST['cards'] ?? []) as $k => $c) {
                if (!is_array($c) || count($cards) >= self::DIARY_MAX) {
                    continue;
                }
                $when  = mb_substr(trim((string) ($c['when'] ?? '')), 0, 60);
                $title = mb_substr(trim((string) ($c['title'] ?? '')), 0, 80);
                $text  = trim(preg_replace("/[ \t]+/", ' ', str_replace("\r", '', (string) ($c['text'] ?? ''))));
                if ($title === '' && $text === '') {
                    continue;   // a blank card is simply left out
                }
                $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
                if (count($words) > self::DIARY_WORDS) {
                    $text = implode(' ', array_slice($words, 0, self::DIARY_WORDS)) . '…';
                    $cut[] = $title !== '' ? $title : 'one card';
                }
                $sign = (string) ($c['sign'] ?? '');
                $sign = in_array($sign, $signs, true) ? $sign : '';
                // The photo: a new one, the one it had, or none.
                $img = (string) ($c['img'] ?? '');
                if ($img !== '' && !in_array($img, array_column($old, 4), true)) {
                    $img = '';   // only a photo this page already had can be kept
                }
                if (!empty($c['img_off'])) {
                    $img = '';
                }
                if (isset($files[$k]) && ($files[$k]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                    $new = Images::store($files[$k], 'diary/' . (int) $w['id']);
                    if ($new === null) {
                        Guard::flash('bad', 'One photo could not be used. Try a JPEG or PNG under 12 MB.');
                    } else {
                        $img = $new;
                    }
                }
                $cards[] = [$when, $title !== '' ? $title : 'Our story', $text, $sign, $img];
            }
            $d['chapters'] = $cards;
            Db::update('weddings', ['design' => json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)], 'id = ?', [$w['id']]);
            Guard::flash('ok', ($cards ? 'Saved. Your story shows as ' . count($cards) . ' diary card' . (count($cards) === 1 ? '' : 's') . '.' : 'Saved. With no cards, your page shows the story you wrote on the Page tab.')
                . ($cut ? ' Cards are ' . self::DIARY_WORDS . ' words at most, so “' . $cut[0] . '”' . (count($cut) > 1 ? ' and ' . (count($cut) - 1) . ' more were' : ' was') . ' shortened.' : ''));
            self::go('/account/story');
        }
        $this->show('story', ['title' => 'Our story', 'w' => $w, 'cards' => $old, 'signs' => self::signs($w)]);
    }

    /** Who can sign a card. */
    private static function signs(array $w): array
    {
        $a = trim((string) $w['partner_a']);
        $b = trim((string) $w['partner_b']);
        return array_values(array_filter([$a, $b, $a !== '' && $b !== '' ? $a . ' & ' . $b : '']));
    }

    /** $_FILES['name'][key] as one file array per key. */
    private static function fileList(string $name): array
    {
        $f = $_FILES[$name] ?? null;
        if (!is_array($f) || !is_array($f['name'] ?? null)) {
            return [];
        }
        $out = [];
        foreach (array_keys($f['name']) as $k) {
            $out[$k] = ['name' => $f['name'][$k], 'type' => $f['type'][$k], 'tmp_name' => $f['tmp_name'][$k], 'error' => $f['error'][$k], 'size' => $f['size'][$k]];
        }
        return $out;
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

    /** The order of the page's sections, and which ones show. Moving and hiding come with Pro. */
    public function sections(): void
    {
        $w = $this->need();
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            if (!Wedding::has($w, 'layout')) {
                Guard::flash('bad', 'Moving and hiding sections comes with Pro.');
                self::go('/account/sections');
            }
            $order = array_values(array_unique(array_filter((array) ($_POST['order'] ?? []), static fn ($k) => isset(Wedding::SECTIONS[$k]))));
            $shown = array_keys(array_filter((array) ($_POST['show'] ?? [])));
            $hidden = array_values(array_diff(array_keys(Wedding::SECTIONS), $shown));
            $d = Wedding::design($w);
            $d['sections'] = ['order' => $order, 'hidden' => $hidden];
            Db::update('weddings', ['design' => json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)], 'id = ?', [$w['id']]);
            Guard::flash('ok', 'Saved. Your page shows its sections in this order' . ($hidden ? ', with ' . count($hidden) . ' hidden.' : '.'));
            self::go('/account/sections');
        }
        $this->show('sections', ['title' => 'Sections', 'w' => $w, 'layout' => Wedding::sections($w)]);
    }

    /** "Who knows you best?": a short quiz about each partner, for guests to play on the page. */
    public function quiz(): void
    {
        $w = $this->need();
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            $d = Wedding::design($w);
            $d['quizzes'] = ['a' => Quiz::clean((array) ($_POST['a'] ?? [])), 'b' => Quiz::clean((array) ($_POST['b'] ?? []))];
            unset($d['quiz']);   // the old single quiz about the couple, if a page still had one
            Db::update('weddings', ['design' => json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)], 'id = ?', [$w['id']]);
            foreach (['a', 'b'] as $side) {
                if (!empty($_POST['restart'][$side])) {
                    Quiz::table();
                    Db::run('DELETE FROM quiz_scores WHERE wedding_id = ? AND side = ?', [$w['id'], $side]);
                }
            }
            $n = count($d['quizzes']['a']) + count($d['quizzes']['b']);
            Guard::flash('ok', $n ? 'Saved. ' . $n . ' question' . ($n === 1 ? '' : 's') . ' ready for your guests.' : 'Saved. Add a question or two and the quiz appears on your page.');
            self::go('/account/quiz');
        }
        Quiz::table();
        $this->show('quiz', [
            'title'   => 'Who knows you best?',
            'w'       => $w,
            'quizzes' => Quiz::forWedding($w),
            'players' => ['a' => Quiz::players((int) $w['id'], 'a'), 'b' => Quiz::players((int) $w['id'], 'b')],
        ]);
    }

    /**
     * A seller's shop: their business profile and their marketplace listings. Verification is
     * HQ's to give, so it is shown here and never editable.
     */
    public function shop(bool $profile = false): void
    {
        Auth::wall();
        Guard::boot();
        $_SESSION['acct_ctx'] = 'shop';
        $uid = (int) Auth::id();
        $v = Db::one('SELECT * FROM vendors WHERE user_id = ? ORDER BY verified DESC, id LIMIT 1', [$uid]);
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            $do = (string) ($_POST['do'] ?? '');
            $t = static fn (string $k, int $max = 190): string => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max);
            if ($do === 'profile') {
                $row = ['name' => $t('name', 190), 'category' => $t('category', 80), 'city' => $t('city', 120), 'state' => $t('state', 120),
                        'phone' => $t('phone', 40), 'instagram' => ltrim($t('instagram', 120), '@'), 'website' => $t('website', 255), 'about' => $t('about', 4000)];
                if ($row['name'] === '') {
                    Guard::flash('bad', 'Your business needs a name.');
                    self::go('/account/shop/profile');
                }
                if ($row['website'] !== '' && !preg_match('#^https?://#i', $row['website'])) {
                    $row['website'] = 'https://' . $row['website'];
                }
                if (!empty($_FILES['logo']['name'])) {
                    $logo = Images::store($_FILES['logo'], 'sellers/' . $uid);
                    if ($logo === null) {
                        Guard::flash('bad', 'That logo could not be used. Try a JPEG or PNG under 12 MB.');
                        self::go('/account/shop/profile');
                    }
                    $row['logo'] = $logo;
                }
                // Their three best projects: a title, a line and a photo each.
                $old = $v ? Market::projects($v) : [];
                $files = self::fileList('proj_photo');
                $projects = [];
                for ($i = 0; $i < 3; $i++) {
                    $pt = mb_substr(trim((string) ($_POST['proj_title'][$i] ?? '')), 0, 120);
                    $pa = mb_substr(trim((string) ($_POST['proj_about'][$i] ?? '')), 0, 300);
                    $pp = !empty($_POST['proj_off'][$i]) ? '' : (string) ($old[$i][2] ?? '');
                    if (isset($files[$i]) && ($files[$i]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                        $img = Images::store($files[$i], 'sellers/' . $uid);
                        if ($img !== null) {
                            $pp = $img;
                        }
                    }
                    if ($pp !== '') {
                        $projects[] = [$pt, $pa, $pp];
                    }
                }
                $row['projects'] = json_encode($projects, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                if ($v) {
                    Db::update('vendors', $row, 'id = ?', [$v['id']]);
                } else {
                    Db::insert('vendors', $row + ['user_id' => $uid, 'slug' => Listing::slugFor('vendors', $row['name'])]);
                }
                Guard::flash('ok', 'Your profile is saved.');
                self::go('/account/shop/profile');
            }
            if ($do === 'look' && $v) {
                // A Premium seller's own colours, font and logo on their invoices.
                if (!Market::premium($v)) {
                    self::go('/account/shop/profile#look');
                }
                $b = Market::look($v);
                $hex = static fn (string $k, string $d): string => preg_match('/^#[0-9a-f]{6}$/i', (string) ($_POST[$k] ?? '')) ? strtoupper((string) $_POST[$k]) : $d;
                $b = ['primary' => $hex('primary', $b['primary']), 'accent' => $hex('accent', $b['accent']),
                      'font' => isset(Market::FONTS[$_POST['font'] ?? '']) ? (string) $_POST['font'] : $b['font'], 'logo' => $b['logo']];
                if (!empty($_POST['logo_off'])) {
                    $b['logo'] = '';
                }
                if (!empty($_FILES['inv_logo']['name']) && ($img = Images::store($_FILES['inv_logo'], 'sellers/' . $uid))) {
                    $b['logo'] = $img;
                }
                Db::update('vendors', ['brand' => !empty($_POST['reset']) ? null : json_encode($b, JSON_UNESCAPED_SLASHES)], 'id = ?', [$v['id']]);
                Guard::flash('ok', !empty($_POST['reset']) ? 'Your invoices are back to our standard look.' : 'Saved. Your invoices, payment links and receipts now carry this look.');
                self::go('/account/shop/profile#look');
            }
            if ($do === 'reply' && $v) {
                $rid = (int) ($_POST['review_id'] ?? 0);
                Db::run("UPDATE reviews SET reply = ? WHERE id = ? AND vendor_id = ? AND (reply IS NULL OR reply = '')",
                        [mb_substr(trim((string) ($_POST['reply'] ?? '')), 0, 1000), $rid, $v['id']]);
                Guard::flash('ok', 'Your reply is on your profile.');
                self::go('/account/shop/profile#reviews');
            }
            $id = (int) ($_POST['id'] ?? 0);
            if ($do === 'delete' && $id) {
                Db::run('DELETE FROM listings WHERE id = ? AND partner_id = ?', [$id, $uid]);
                Guard::flash('ok', 'Listing removed.');
                self::go('/account/shop');
            }
            if ($do === 'listing') {
                $row = [
                    'title' => $t('title'), 'kind' => (string) ($_POST['kind'] ?? ''),
                    'city' => $t('city', 120), 'price' => max(0, (int) preg_replace('/\D/', '', (string) ($_POST['price'] ?? '0'))),
                    'price_unit' => $t('price_unit', 40), 'on_ask' => isset($_POST['on_ask']) ? 1 : 0, 'about' => $t('about', 6000),
                    'status' => ($_POST['status'] ?? 'live') === 'paused' ? 'paused' : 'live',
                ];
                if ($row['title'] === '') {
                    Guard::flash('bad', 'A listing needs a title.');
                    self::go('/account/shop');
                }
                $old = $id ? Db::one('SELECT * FROM listings WHERE id = ? AND partner_id = ?', [$id, $uid]) : null;
                if ($id && !$old) {
                    self::go('/account/shop');
                }
                if (!Market::isOpen($row['kind']) && !($old && (string) $old['kind'] === $row['kind'])) {
                    Guard::flash('bad', 'Pick one of the open categories. New ones are opened by our team.');
                    self::go('/account/shop');
                }
                if ($v && !Market::active($v)) {
                    Guard::flash('bad', 'Your shop is paused while we verify your business, so listings cannot change for now.');
                    self::go('/account/shop');
                }
                // Photos: the ones kept (minus any removed), the chosen cover first, then new uploads. Eight at most.
                $photos = $old ? Listing::photos($old) : [];
                $drop = array_map('strval', (array) ($_POST['drop'] ?? []));
                $photos = array_values(array_filter($photos, static fn ($ph) => !in_array($ph, $drop, true)));
                $cover = (string) ($_POST['cover'] ?? '');
                if ($cover !== '' && in_array($cover, $photos, true)) {
                    $photos = array_values(array_unique(array_merge([$cover], $photos)));
                }
                $failed = 0;
                foreach (self::fileList('photos') as $f) {
                    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE || count($photos) >= 8) {
                        continue;
                    }
                    $img = Images::store($f, 'listings/' . $uid);
                    if ($img === null) {
                        $failed++;
                    } else {
                        $photos[] = $img;
                    }
                }
                $row['photos'] = json_encode(array_slice($photos, 0, 8), JSON_UNESCAPED_SLASHES);
                if ($failed) {
                    Guard::flash('bad', $failed . ' photo' . ($failed > 1 ? 's' : '') . ' could not be used. Try JPEG or PNG under 12 MB.');
                }
                if ($old) {
                    Db::update('listings', $row, 'id = ? AND partner_id = ?', [$id, $uid]);
                } else {
                    Db::insert('listings', $row + ['partner_id' => $uid, 'slug' => Listing::slugFor('listings', $row['title']), 'photos' => $row['photos'] ?? '[]']);
                }
                if (!$failed) {
                    Guard::flash('ok', $old ? 'Listing saved.' : 'Listing added. It is live on the marketplace.');
                }
                self::go('/account/shop');
            }
            self::go('/account/shop');
        }
        View::render(!empty($profile) ? 'account/shop-profile' : 'account/shop', [
            'title' => $v ? (string) $v['name'] : 'Your shop', 'tab' => 'account', 'appTitle' => !empty($profile) ? 'Business profile' : 'Your shop',
            'back' => !empty($profile) ? App::url('/account/shop') : null,
            'v' => $v, 'rows' => Db::all('SELECT * FROM listings WHERE partner_id = ? ORDER BY id', [$uid]),
            'reviews' => $v ? Db::all("SELECT * FROM reviews WHERE vendor_id = ? AND status = 'live' ORDER BY created_at DESC LIMIT 30", [$v['id']]) : [],
        ]);
    }

    /** The seller's business profile: what couples see on their public page. */
    public function shopProfile(): void
    {
        $this->shop(true);
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
        $rows = Db::all($sql . " AND status <> 'pending' ORDER BY created_at DESC, id DESC", $arg);

        if (($_GET['csv'] ?? '') === '1') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="guests-' . $w['slug'] . '.csv"');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Phone', 'Email', 'Party', 'Coming', 'Approved', 'Pass', 'Checked in', 'Table', 'Note', 'Replied']);
            foreach ($rows as $g) {
                fputcsv($out, [$g['name'], $g['phone'], $g['email'], $g['party'], $g['coming'], $g['status'] === 'approved' ? 'yes' : 'no',
                               $g['pass_code'], $g['checked_in'], $g['table_no'], $g['note'], $g['created_at']]);
            }
            exit;
        }

        $this->show('guests', [
            'title' => 'Guests & RSVPs',
            'w'     => $w,
            'rows'  => $rows,
            'wait'  => Rsvp::pending((int) $w['id']),
            'q'     => $q,
            'sum'   => Db::one(
                "SELECT COUNT(*) total,
                        SUM(coming='yes') yes, SUM(coming='no') no, SUM(coming='maybe') maybe,
                        COALESCE(SUM(CASE WHEN coming='yes' THEN party ELSE 0 END),0) heads
                 FROM guests WHERE wedding_id = ? AND status = 'approved'",
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

    /** Approve or decline one RSVP. Approving emails the guest their access code. */
    public function guestDecide(string $id): void
    {
        Guard::must();
        $w = $this->need();
        $ok = ($_POST['do'] ?? '') === 'approve' ? Rsvp::approve($w, (int) $id) : Rsvp::decline($w, (int) $id);
        Guard::flash($ok ? 'ok' : 'bad', $ok ? (($_POST['do'] ?? '') === 'approve' ? 'Approved. We emailed them their access code.' : 'Declined. They will not get a code.') : 'That reply was already dealt with.');
        self::go('/account/guests#waiting');
    }

    public function guestApproveAll(): void
    {
        Guard::must();
        $w = $this->need();
        $n = 0;
        foreach (Rsvp::pending((int) $w['id']) as $g) {
            $n += Rsvp::approve($w, (int) $g['id']) ? 1 : 0;
        }
        Guard::flash('ok', $n === 1 ? 'Approved 1 guest and emailed their code.' : "Approved {$n} guests and emailed their codes.");
        self::go('/account/guests#waiting');
    }

    /** At the gate: type a code and see whose it is, with their face. */
    public function guestDoor(): void
    {
        Guard::must();
        $w = $this->need();
        $c = Rsvp::clean((string) ($_POST['code'] ?? ''));
        if ($c === '' || !Db::val("SELECT 1 FROM guests WHERE wedding_id = ? AND pass_code = ? AND status = 'approved'", [$w['id'], $c])) {
            Guard::flash('bad', 'No approved guest has that code. Check it and try again.');
            self::go('/account/guests#door');
        }
        self::go('/w/' . $w['slug'] . '/pass/' . $c);
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
        if (Managed::managing()) {
            Guard::flash('bad', 'Only the couple can ask for a payout.');
            self::go('/account/money');
        }
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
            $mail = strtolower(trim((string) ($_POST['email'] ?? '')));
            if ($mail !== '' && $mail !== strtolower((string) $u['email'])) {
                if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                    Guard::flash('bad', 'That email does not look right.');
                    self::go('/account/settings');
                }
                if (!Auth::attempt((string) $u['email'], (string) ($_POST['current'] ?? ''))) {
                    Guard::flash('bad', 'Type your current password to change your email.');
                    self::go('/account/settings');
                }
                if (Db::val('SELECT 1 FROM users WHERE email = ? AND id <> ?', [$mail, Auth::id()])) {
                    Guard::flash('bad', 'Another account already uses that email.');
                    self::go('/account/settings');
                }
                $set['email'] = $mail;
            }
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
