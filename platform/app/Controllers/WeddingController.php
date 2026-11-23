<?php
declare(strict_types=1);

/** The wedding directory, and the page a couple actually shares. */
final class WeddingController
{
    public function index(): void
    {
        $page   = max(1, (int) ($_GET['page'] ?? 1));
        $filter = [
            'q'     => trim((string) ($_GET['q'] ?? '')),
            'city'  => trim((string) ($_GET['city'] ?? '')),
            'style' => Wedding::styleKey((string) ($_GET['style'] ?? '')),
            'plan'  => isset(Wedding::PLANS[$_GET['plan'] ?? '']) ? (string) $_GET['plan'] : '',
            'country' => trim((string) ($_GET['country'] ?? '')),
            'sort'  => self::sort(),
        ];
        $per   = 12;
        $total = Wedding::countSearch($filter);

        View::render('wedding/index', [
            'title'   => $filter['q'] !== '' ? 'Weddings matching “' . $filter['q'] . '”' : 'Wedding pages',
            'desc'    => 'Browse real Nigerian and African wedding pages — owambe, traditional, nikkah, white wedding and more.',
            'tab'     => 'weddings',
            'css'     => ['wedding'],
            'rows'    => Wedding::search($filter, $per, ($page - 1) * $per),
            'total'   => $total,
            'pages'   => (int) ceil($total / $per),
            'page'    => $page,
            'f'       => $filter,
            'cities'  => Wedding::cityList(14),
            'countries' => Wedding::countryList(),
            'plans'   => Wedding::planCounts(),
            'styles'  => array_keys(Wedding::STYLES),
        ]);
    }

    /** Trending unless the visitor picked another; the old "buzz" links land on Top ranked. */
    private static function sort(): string
    {
        Wedding::readyToSort();
        $s = (string) ($_GET['sort'] ?? '');
        $s = $s === 'buzz' ? 'top' : $s;
        return isset(Wedding::SORTS[$s]) ? $s : 'trending';
    }

    /** Weddings near the visitor, from the location their browser shared. A small piece of HTML. */
    public function near(): void
    {
        $lat = (float) ($_GET['lat'] ?? 0);
        $lng = (float) ($_GET['lng'] ?? 0);
        header('Cache-Control: private, max-age=300');
        if (abs($lat) > 90 || abs($lng) > 180 || ($lat == 0.0 && $lng == 0.0)) {
            http_response_code(400);
            exit;
        }
        [[$city, $country, $dist], $rows] = Wedding::near($lat, $lng, 8);
        $ranks = Wedding::ranks();
        $where = $dist < 120 ? $city : 'your area';
        echo '<p class="near__where">' . icon('pin') . '<span>Near <b>' . e($where) . '</b>' . ($dist < 120 && $country !== '' ? ', ' . e($country) : '') . '</span></p>';
        if (!$rows) {
            echo '<p class="sub center">No public wedding pages near you yet.</p>';
            exit;
        }
        echo '<div class="rail">';
        foreach ($rows as $w) {
            $km = (float) $w['km'];
            $away = $km < 15 ? 'In ' . (string) $w['city'] : number_format($km < 100 ? round($km) : round($km, -1)) . ' km · ' . (string) $w['city'];
            echo wedding_card($w, $away . ' · ' . Wedding::short($w), Site::tier((string) $w['plan']), '', $ranks[(int) $w['id']] ?? 0);
        }
        echo '</div>';
        exit;
    }

    /** A country's own listing: /weddings/uk, /weddings/ghana and the rest. */
    public function place(string $place): void
    {
        $p = Wedding::PLACES[$place] ?? null;
        if (!$p) {
            self::notFound();
        }
        [$country, $in, $line] = $p;
        $page   = max(1, (int) ($_GET['page'] ?? 1));
        $filter = [
            'q'       => trim((string) ($_GET['q'] ?? '')),
            'city'    => trim((string) ($_GET['city'] ?? '')),
            'style'   => '',
            'plan'    => isset(Wedding::PLANS[$_GET['plan'] ?? '']) ? (string) $_GET['plan'] : '',
            'country' => $country,
            'sort'    => self::sort(),
        ];
        $per    = 12;
        $total  = Wedding::countSearch($filter);
        $all    = Wedding::countSearch(['country' => $country]);
        $others = [];
        foreach (Wedding::countryList() as $c) {
            $slug = Wedding::placeSlug((string) $c['country']);
            if ($slug !== '' && $slug !== $place) {
                $others[$slug] = ['name' => Wedding::PLACES[$slug][1], 'n' => (int) $c['n']];
            }
        }
        $cur    = Wedding::CURRENCY[$country] ?? 'NGN';
        $cities = Wedding::cityList(24, $country);
        $names  = array_map(static fn ($c) => (string) $c['city'], $cities);
        $vendors = $names ? Db::all(
            'SELECT * FROM vendors WHERE city IN (' . implode(',', array_fill(0, count($names), '?')) . ')
             ORDER BY verified DESC, rating DESC, reviews DESC LIMIT 4',
            $names
        ) : [];

        View::render('wedding/place', [
            'title'   => 'African weddings in ' . $in,
            'desc'    => $line,
            'ogImage' => Share::url('p', $place, (string) $all),
            'tab'     => 'weddings',
            'css'     => ['wedding'],
            'appTitle'=> ucfirst(preg_replace('/^the /', '', explode(' and ', $in)[0])),
            'back'    => App::url('/weddings'),
            'place'   => $place,
            'country' => $country,
            'in'      => $in,
            'line'    => $line,
            'rows'    => Wedding::search($filter, $per, ($page - 1) * $per),
            'total'   => $total,
            'all'     => $all,
            'pages'   => (int) ceil($total / $per),
            'page'    => $page,
            'f'       => $filter,
            'cities'  => $cities,
            'vendors' => $vendors,
            'plans'   => Wedding::planCounts($country),
            'soon'    => Wedding::soonCount($country),
            'cur'     => $cur,
            'others'  => $others,
        ]);
    }

    public function show(string $slug): void
    {
        $w = Wedding::bySlug($slug);
        if (!$w) {
            self::notFound();
        }
        if (Wedding::has($w, 'live')) {
            Reminder::tick();
        }

        // A private page is not browsable; one with a pass code can be opened by typing it.
        $private = (string) $w['visibility'] !== 'public';
        $mine    = Auth::check() && (int) $w['owner_id'] === Auth::id();
        if ($private && !$mine) {
            if ((string) $w['pass_code'] === '') {
                self::notFound();
            }
            if (!Wedding::unlocked($w)) {
                $this->askPass($w);
                return;
            }
        }

        // A page fetched ahead of a tap (Chrome's prefetch, or our service worker) is not a visit.
        $ahead = str_contains((string) ($_SERVER['HTTP_SEC_PURPOSE'] ?? $_SERVER['HTTP_PURPOSE'] ?? ''), 'prefetch')
              || ($_SERVER['HTTP_X_PURPOSE'] ?? '') === 'prefetch';
        if (!$mine && !$ahead) {
            Db::run('UPDATE weddings SET views = views + 1 WHERE id = ?', [$w['id']]);
            // A visit earns buzz once per visitor per page, so refreshing cannot pump the chart.
            if (empty($_SESSION['seen'][(int) $w['id']])) {
                $_SESSION['seen'][(int) $w['id']] = 1;
                Buzz::add($w, 'visit');
            }
        }

        $id       = (int) $w['id'];
        $programs = Wedding::programs($id);
        $next     = Wedding::nextMoment($w, $programs);

        $design = Design::forWedding($w);
        $dz     = Wedding::design($w);
        $pal    = Wedding::palette($w);
        View::render('wedding/show', [
            'wed'      => true,
            'wtpl'     => $design['key'],
            'fixTheme' => $design['theme'] === 'auto' ? null : $design['theme'],
            'tone'     => (string) $w['plan'] === 'free' ? 'mono' : '',
            'ink'      => $design['ink'] === 'dark' ? 'dark' : '',
            'themeColor'      => $design['theme'] === 'auto' ? ((string) $w['plan'] === 'free' ? '#6E6E6E' : '#6F8590') : $design['swatch'][0],
            'themeColorNight' => $design['theme'] === 'auto' ? ((string) $w['plan'] === 'free' ? '#141414' : '#1E242E') : $design['swatch'][0],
            // The preview is the couple: their photo, names, date and city. A private page keeps the brand's.
            'ogImage'  => (string) $w['visibility'] === 'public' ? Share::url('w', (string) $w['slug'], implode('|', [$w['cover'], Wedding::names($w), $w['wed_date'], $w['city'], $w['country'], $w['plan'], $w['design']])) : null,
            'ogTitle'  => Wedding::names($w) . ' are getting married',
            'ogDesc'   => trim(Wedding::when($w) . ' · ' . Wedding::place($w), ' ·') . '. ' . (trim((string) $w['tagline']) !== '' ? trim((string) $w['tagline']) . ' ' : '') . 'RSVP, send a gift and leave them a note.',
            'ogAlt'    => Wedding::names($w) . ', ' . Wedding::when($w),
            'design'   => $design,
            'dz'       => $dz,
            'title'    => Wedding::names($w) . ' — ' . Wedding::when($w),
            'desc'     => trim((string) $w['tagline']) !== ''
                ? (string) $w['tagline']
                : Wedding::names($w) . ' are getting married. RSVP, send a gift and leave them a note.',
            'tab'      => 'weddings',
            'w'        => $w,
            'mine'     => $mine,
            'pal'      => $pal,
            'programs' => $programs,
            'squad'    => Wedding::has($w, 'squad')   ? Wedding::squad($id)  : [],
            'gifts'    => Wedding::gifts($id),
            'notes'    => Wedding::notes($id),
            'photos'   => Wedding::has($w, 'gallery') ? Wedding::photos($id) : [],
            'tally'    => Wedding::tally($id),
            'next'     => $next,
            'left'     => $next ? Wedding::until($next['when']) : null,
            'locked'   => $mine ? Wedding::locked($w) : [],
            'sponsors' => Wedding::has($w, 'quiet') || !Promo::slotOn('sponsors') ? [] : Wedding::sponsors(2),
            'vendors'  => Wedding::vendors($w),
            'pass'     => self::myReply($w),
        ]);
    }

    /** A guest says they are coming. No account, ever — guests never sign in. */
    public function rsvp(string $slug): void
    {
        Guard::must();
        $w = Wedding::bySlug($slug);
        if (!$w) {
            self::notFound();
        }
        if (Guard::tooMany('rsvp', 12, 30)) {
            Guard::flash('bad', 'That is a lot of replies from one place. Try again in a few minutes.');
            self::back($slug, 'rsvp');
        }

        $name   = trim((string) ($_POST['name'] ?? ''));
        $email  = trim((string) ($_POST['email'] ?? ''));
        $party  = max(1, min(20, (int) ($_POST['party'] ?? 1)));
        $coming = in_array($_POST['coming'] ?? '', ['yes', 'no', 'maybe'], true) ? (string) $_POST['coming'] : 'yes';
        $going  = $coming !== 'no';
        if ($name === '') {
            Guard::flash('bad', 'Please put your name so they know who replied.');
            self::back($slug, 'rsvp');
        }
        if ($going && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Guard::flash('bad', 'Please put your email. Your access code is sent there when they approve your RSVP.');
            self::back($slug, 'rsvp');
        }
        if ($email !== '' && Db::val("SELECT 1 FROM guests WHERE wedding_id = ? AND email = ? AND status <> 'declined'", [$w['id'], $email])) {
            Guard::flash('bad', 'Someone already replied with this email. If you need to change your reply, contact ' . Wedding::names($w) . '.');
            self::back($slug, 'rsvp');
        }
        // A photo of the guest's face, so whoever is at the gate can see the code belongs to them.
        $face = '';
        if ($going) {
            $face = isset($_FILES['face']) ? (string) Rsvp::storeFace($_FILES['face']) : '';
            if ($face === '') {
                Guard::flash('bad', 'Please add a photo of your face, with the camera or from your phone. It is checked against your code at the entrance.');
                self::back($slug, 'rsvp');
            }
        }

        $id = (int) Db::insert('guests', [
            'wedding_id' => $w['id'],
            'name'       => mb_substr($name, 0, 160),
            'phone'      => mb_substr(trim((string) ($_POST['phone'] ?? '')), 0, 40),
            'email'      => mb_substr($email, 0, 190),
            'party'      => $party,
            'coming'     => $coming,
            // The couple approves everyone who is coming. "Can't make it" needs nothing from them.
            'status'     => $going ? 'pending' : 'approved',
            'note'       => mb_substr(trim((string) ($_POST['note'] ?? '')), 0, 1000),
            'photo'      => $face,
        ]);
        // This phone remembers the reply, so the page can show "waiting" and later the pass itself.
        $_SESSION['rsvp'][(string) $w['slug']] = $id;
        unset($_SESSION['passes'][(string) $w['slug']]);

        Guard::flash('ok', $going
            ? 'Thank you. ' . Wedding::names($w) . ' will check your RSVP. When they approve it, your access code is emailed to ' . $email . '.'
            : 'Thank you for letting them know.');
        self::back($slug, 'rsvp');
    }

    /**
     * A guest rates one of the vendors listed on this wedding page. No account: a name, stars and a
     * few words. Only vendors the couple listed, a few reviews per visitor, and one per vendor.
     */
    public function review(string $slug): void
    {
        Guard::must();
        $w = Wedding::bySlug($slug);
        if (!$w) {
            self::notFound();
        }
        $vid = (int) ($_POST['vendor_id'] ?? 0);
        $v = null;
        foreach (Wedding::vendors($w) as $x) {
            if ((int) $x['id'] === $vid) {
                $v = $x;
            }
        }
        $name = trim((string) ($_POST['name'] ?? ''));
        $body = trim((string) ($_POST['body'] ?? ''));
        if (!$v) {
            Guard::flash('bad', 'That vendor is not listed on this page.');
        } elseif (!empty($_SESSION['vrev'][$vid]) || Guard::tooMany('vreview', 6, 60)) {
            Guard::flash('bad', 'You already reviewed ' . $v['name'] . ' from this phone.');
        } elseif ($name === '' || mb_strlen($body) < 10) {
            Guard::flash('bad', 'Put your name and say a little about how they did: a sentence or two.');
        } else {
            Db::insert('reviews', [
                'vendor_id' => $vid, 'user_id' => Auth::id() ?: null, 'wedding_id' => $w['id'], 'guest' => 1,
                'name' => mb_substr($name, 0, 160), 'stars' => max(1, min(5, (int) ($_POST['stars'] ?? 5))),
                'body' => mb_substr($body, 0, 2000), 'status' => 'live',
            ]);
            Market::rate($vid);
            $_SESSION['vrev'][$vid] = 1;
            Guard::flash('ok', 'Thank you. Your review is on ' . $v['name'] . '’s profile.');
        }
        self::back($slug, 'vendors');
    }

    /** The reply this phone sent: still waiting, approved (with its pass), or nothing. */
    private static function myReply(array $w): ?array
    {
        $id = (int) ($_SESSION['rsvp'][(string) $w['slug']] ?? 0);
        if ($id) {
            $g = Db::one('SELECT id, name, party, coming, status, pass_code FROM guests WHERE id = ? AND wedding_id = ?', [$id, $w['id']]);
            if ($g && $g['coming'] !== 'no' && $g['status'] !== 'declined') {
                return $g['status'] === 'approved'
                    ? ($g['pass_code'] ? ['code' => $g['pass_code'], 'name' => $g['name'], 'party' => $g['party']] : null)
                    : ['waiting' => true, 'name' => $g['name']];
            }
            return null;
        }
        return $_SESSION['passes'][(string) $w['slug']] ?? null;
    }

    /**
     * A guest's pass, from the link in their approval email: the code, their party and the face they
     * sent. The couple (or our team) opening it at the gate also gets a button to check them in.
     */
    public function pass(string $slug, string $code): void
    {
        $w = Wedding::bySlug($slug);
        if (!$w) {
            self::notFound();
        }
        $g = Db::one("SELECT * FROM guests WHERE wedding_id = ? AND pass_code = ? AND status = 'approved'", [$w['id'], Rsvp::clean($code)]);
        if (!$g) {
            http_response_code(404);
        }
        header('X-Robots-Tag: noindex');
        header('Cache-Control: private, no-store');
        View::render('wedding/pass', [
            'title'  => $g ? 'Entry pass · ' . Wedding::names($w) : 'Pass not found',
            'w'      => $w,
            'g'      => $g,
            'code'   => Rsvp::clean($code),
            'door'   => Team::door((int) $w['id']),
        ]);
    }

    /** The couple (or our team) at the gate: this pass is used now. */
    public function checkIn(string $slug, string $code): void
    {
        Guard::must();
        $w = Wedding::bySlug($slug);
        if (!$w || !Team::door((int) $w['id'])) {
            self::notFound();
        }
        $c = Rsvp::clean($code);
        $n = Db::run("UPDATE guests SET checked_in = NOW() WHERE wedding_id = ? AND pass_code = ? AND status = 'approved' AND checked_in IS NULL", [$w['id'], $c]);
        Guard::flash($n ? 'ok' : 'bad', $n ? 'Checked in.' : 'This pass was already used, or it is not on the list.');
        header('Location: ' . App::url('/w/' . $slug . '/pass/' . $c));
        exit;
    }

    /** A guest's face, for the couple, our team or whoever holds that guest's code. Never cached publicly. */
    public function face(string $id): void
    {
        $g = Db::one('SELECT id, wedding_id, pass_code, photo FROM guests WHERE id = ?', [(int) $id]);
        $file = $g && $g['photo'] !== '' ? Rsvp::faceDir() . '/' . basename((string) $g['photo']) : '';
        if (!$g || !is_file($file) || !Rsvp::canSee($g, (string) ($_GET['c'] ?? ''))) {
            http_response_code(404);
            exit;
        }
        header('Content-Type: image/jpeg');
        header('Content-Length: ' . filesize($file));
        header('Cache-Control: private, max-age=600');
        header('X-Robots-Tag: noindex');
        readfile($file);
        exit;
    }

    /** The share button was used. Counted once per visitor per page; answers with no page. */
    public function share(string $slug): void
    {
        $w = Wedding::bySlug($slug);
        if ($w && Guard::ok() && empty($_SESSION['shared'][(int) $w['id']])) {
            $_SESSION['shared'][(int) $w['id']] = 1;
            Buzz::add($w, 'share');
        }
        http_response_code(204);
        exit;
    }

    /** A love note on the wall. */
    /**
     * The wall of love: words, never pictures. On Premium and High Society pages a guest can leave a
     * voice note instead of (or as well as) writing.
     */
    public function note(string $slug): void
    {
        Guard::must();
        $w = Wedding::bySlug($slug);
        if (!$w) {
            self::notFound();
        }
        if (Guard::tooMany('note', 10, 20)) {
            Guard::flash('bad', 'Give it a minute before leaving another note.');
            self::back($slug, 'wall');
        }

        $name  = trim((string) ($_POST['name'] ?? ''));
        $body  = trim((string) ($_POST['body'] ?? ''));
        $audio = '';
        if (Wedding::has($w, 'voice') && !empty($_FILES['voice']['name']) && $name !== '') {
            $err = '';
            $audio = Voice::store($_FILES['voice'], (int) $w['id'], $err);
            if ($audio === '') {
                Guard::flash('bad', $err);
                self::back($slug, 'wall');
            }
        }
        if ($name === '' || ($body === '' && $audio === '')) {
            Guard::flash('bad', Wedding::has($w, 'voice') ? 'A note needs your name, and a few words or a voice note.' : 'A note needs your name and a few words.');
            self::back($slug, 'wall');
        }

        Db::insert('wall', [
            'wedding_id' => $w['id'],
            'kind'       => $audio !== '' ? 'voice' : 'note',
            'name'       => mb_substr($name, 0, 160),
            'body'       => mb_substr($body, 0, 1000),
            'audio'      => $audio,
            'status'     => 'live',
        ]);
        Buzz::add($w, 'note');
        Guard::flash('ok', $audio !== '' ? 'Your voice note is on their wall.' : 'Your note is on their wall.');
        self::back($slug, 'wall');
    }

    /** A guest's photo for the gallery (Pro and up). The full-size original goes to the wedding's Drive folder. */
    public function photo(string $slug): void
    {
        Guard::must();
        $w = Wedding::bySlug($slug);
        if (!$w || !Wedding::has($w, 'gallery')) {
            self::notFound();
        }
        if (Guard::tooMany('photo', 10, 20)) {
            Guard::flash('bad', 'Give it a minute before adding another photo.');
            self::back($slug, 'photos');
        }
        $name = trim((string) ($_POST['name'] ?? ''));
        $body = trim((string) ($_POST['body'] ?? ''));
        if ($name === '' || empty($_FILES['photo']['name'])) {
            Guard::flash('bad', 'Pick a photo and add your name.');
            self::back($slug, 'photos');
        }
        // The page keeps small copies; the full-size original is held for Drive.
        $held  = Drive::hold($_FILES['photo'], (int) $w['id']);
        $image = Images::store($_FILES['photo'], 'wall/' . (int) $w['id']);
        if ($image === null) {
            if ($held !== null) {
                @unlink($held['path']);
            }
            Guard::flash('bad', 'That photo could not be used. Try a JPEG or PNG under 12 MB.');
            self::back($slug, 'photos');
        }
        $wallId = (int) Db::insert('wall', [
            'wedding_id' => $w['id'],
            'kind'       => 'photo',
            'name'       => mb_substr($name, 0, 160),
            'body'       => mb_substr($body, 0, 300),
            'image'      => $image,
            'status'     => 'live',
        ]);
        if ($held !== null) {
            Drive::queue($w, $wallId, $held, $name);
        }
        Buzz::add($w, 'photo');
        Guard::flash('ok', 'Your photo is in their gallery.');
        self::back($slug, 'photos');
    }

    /**
     * "Who knows them best?" A guest picks the partner they know and answers that partner's quiz.
     * It is marked here, never in the browser, then the score goes on that partner's leaderboard.
     */
    public function quiz(string $slug): void
    {
        Guard::must();
        $w = Wedding::bySlug($slug);
        if (!$w || !Wedding::has($w, 'squad')) {
            self::notFound();
        }
        $mine = Auth::check() && (int) $w['owner_id'] === Auth::id();
        if ((string) $w['visibility'] !== 'public' && !$mine && !Wedding::unlocked($w)) {
            self::notFound();
        }
        $side = ($_POST['side'] ?? '') === 'b' ? 'b' : 'a';
        $who  = (string) ($side === 'a' ? $w['partner_a'] : $w['partner_b']);
        $qs   = Quiz::forWedding($w)[$side];
        if (!$qs) {
            self::back($slug, 'quiz');
        }
        if (!empty($_SESSION['quiz'][(int) $w['id']][$side])) {
            Guard::flash('bad', 'You have already played ' . $who . '’s quiz. Your score is on the leaderboard.');
            self::back($slug, 'quiz');
        }
        if (Guard::tooMany('quiz', 12, 20)) {
            Guard::flash('bad', 'Give it a minute before playing again.');
            self::back($slug, 'quiz');
        }
        $name = trim(preg_replace('/\s+/', ' ', (string) ($_POST['name'] ?? '')));
        $picked = (array) ($_POST['q'] ?? []);
        if ($name === '' || count(array_filter(array_keys($qs), static fn ($i) => isset($picked[$i]))) < count($qs)) {
            Guard::flash('bad', 'Answer every question and add your name for the leaderboard.');
            self::back($slug, 'quiz');
        }
        $score = 0;
        foreach ($qs as $i => [, , $right]) {
            if ((string) $picked[$i] === (string) $right) {
                $score++;
            }
        }
        $total = count($qs);
        $rank  = Quiz::save((int) $w['id'], $side, mb_substr($name, 0, 60), $score, $total);
        $_SESSION['quiz'][(int) $w['id']][$side] = ['score' => $score, 'total' => $total, 'rank' => $rank, 'name' => $name];
        Buzz::add($w, 'quiz');
        $line = $score === $total ? 'Full marks. ' : ($score >= $total - 1 ? 'So close. ' : '');
        Guard::flash('ok', $line . $score . ' of ' . $total . ' on ' . $who . '’s quiz. You are number ' . $rank . ' on the leaderboard.');
        self::back($slug, 'quiz');
    }

    /** "Remind me": an email when the livestream link is added, and again just before it starts. */
    public function remind(string $slug): void
    {
        Guard::must();
        $w = Wedding::bySlug($slug);
        if (!$w || !Wedding::has($w, 'live')) {
            self::notFound();
        }
        if (Guard::tooMany('remind', 8, 30)) {
            Guard::flash('bad', 'Give it a minute and try again.');
            self::back($slug, 'live');
        }
        if (!Reminder::add($w, (string) ($_POST['email'] ?? ''))) {
            Guard::flash('bad', 'That email does not look right. Try again?');
            self::back($slug, 'live');
        }
        Guard::flash('ok', Reminder::link($w) !== ''
            ? 'Done. We will email you fifteen minutes before it starts.'
            : 'Done. We will email you the moment the livestream link is added, and again just before it starts.');
        self::back($slug, 'live');
    }

    public function remindStop(string $token): void
    {
        $r = Reminder::stop($token);
        Guard::flash('ok', $r ? 'You will not get any more livestream reminders.' : 'Those reminders were already stopped.');
        header('Location: ' . App::url($r ? '/w/' . $r['slug'] : '/'));
        exit;
    }

    /** The livestream as a calendar event, with an alert fifteen minutes before. */
    public function ics(string $slug): void
    {
        $w = Wedding::bySlug($slug);
        if (!$w || (string) $w['visibility'] !== 'public' || !Wedding::has($w, 'live')) {
            self::notFound();
        }
        $start = Reminder::startsAt($w) ?? time() + 86400;
        $page = App::url('/w/' . $w['slug'] . '#live');
        $esc = static fn (string $t): string => str_replace(["\\", ';', ',', "\n"], ['\\\\', '\\;', '\\,', '\\n'], $t);
        $lines = [
            'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Wedding Websites & More//Livestream//EN', 'CALSCALE:GREGORIAN', 'METHOD:PUBLISH',
            'BEGIN:VEVENT', 'UID:live-' . (int) $w['id'] . '@' . Share::host(), 'DTSTAMP:' . gmdate('Ymd\THis\Z'),
            'DTSTART:' . gmdate('Ymd\THis\Z', $start), 'DTEND:' . gmdate('Ymd\THis\Z', $start + 3 * 3600),
            'SUMMARY:' . $esc(Wedding::names($w) . ': livestream'), 'DESCRIPTION:' . $esc('Watch live on their page: ' . $page), 'URL:' . $page,
            'BEGIN:VALARM', 'ACTION:DISPLAY', 'DESCRIPTION:' . $esc(Wedding::names($w) . ' go live in 15 minutes'), 'TRIGGER:-PT15M', 'END:VALARM',
            'END:VEVENT', 'END:VCALENDAR',
        ];
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . preg_replace('/[^a-z0-9-]/', '', (string) $w['slug']) . '-livestream.ics"');
        echo implode("\r\n", $lines) . "\r\n";
        exit;
    }

    /* ---------------------------------------------------------------------------------------- */

    private function askPass(array $w): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && Guard::ok()) {
            $try = trim((string) ($_POST['code'] ?? ''));
            if ($try !== '' && hash_equals((string) $w['pass_code'], $try)) {
                setcookie(Wedding::passCookie($w), Wedding::passStamp($w), [
                    'expires' => time() + 86400 * 30, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax',
                ]);
                header('Location: ' . App::url('/w/' . $w['slug']));
                exit;
            }
            Guard::flash('bad', 'That code does not match.');
        }
        http_response_code(401);
        View::render('wedding/locked', [
            'title' => 'This page is private',
            'appTitle' => 'Private page',
            'w'     => $w,
        ]);
    }

    private static function back(string $slug, string $anchor): never
    {
        // The RSVP lives in a sheet that opens from #rsvp, so after sending one the guest lands at the
        // top of the page, where the confirmation is, instead of back inside the sheet.
        header('Location: ' . App::url('/w/' . $slug) . ($anchor !== '' && $anchor !== 'rsvp' ? '#' . $anchor : ''));
        exit;
    }

    private static function notFound(): never
    {
        http_response_code(404);
        View::render('errors/404', ['title' => 'Not found']);
        exit;
    }

    /** The spray total and the top sprayers, for the page to refresh as people spray. */
    public function sprays(string $slug): void
    {
        $w = Wedding::bySlug($slug);
        $mine = $w && Auth::check() && (int) $w['owner_id'] === Auth::id();
        if (!$w || !Wedding::has($w, 'spray') || ((string) $w['visibility'] !== 'public' && !$mine && !Wedding::unlocked($w))) {
            http_response_code(404);
            exit;
        }
        $sp = Wedding::sprayers($w);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode([
            'sig'   => $sp['sig'],
            'total' => money(number_format($sp['total']), (string) $w['currency']),
            'list'  => View::partial('wedding/sprayers', ['rows' => $sp['rows'], 'cur' => (string) $w['currency']]),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * A guest is about to spray or give by bank transfer: the couple's account number and the exact
     * amount to send. The page then waits for the transfer (given()).
     */
    public function give(string $slug): void
    {
        Guard::must();
        $w = Wedding::bySlug($slug);
        $json = static function (array $d, int $c = 200): never {
            http_response_code($c);
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store');
            echo json_encode($d, JSON_UNESCAPED_UNICODE);
            exit;
        };
        if (!$w || (string) $w['va_number'] === '' || !Bachs::on()) {
            $json(['ok' => false, 'msg' => 'Transfers are not switched on for this page yet.'], 400);
        }
        if (Guard::tooMany('give', 20, 10)) {
            $json(['ok' => false, 'msg' => 'Give it a minute, then try again.'], 429);
        }
        $kind = ($_POST['kind'] ?? '') === 'spray' && Wedding::has($w, 'spray') ? 'spray' : 'gift';
        $gift = (int) ($_POST['gift_id'] ?? 0);
        $gift = $gift && Db::val('SELECT 1 FROM gifts WHERE id = ? AND wedding_id = ?', [$gift, $w['id']]) ? $gift : null;
        $name = trim((string) ($_POST['name'] ?? ''));
        $amount = max(0, (int) preg_replace('/\D/', '', (string) ($_POST['amount'] ?? '0')));
        if ($name === '') {
            $json(['ok' => false, 'msg' => $kind === 'spray' ? 'Add your name, so the couple sees who sprayed.' : 'Add your name, so the couple knows who it is from.'], 400);
        }
        $naira = Transfers::toNaira($amount, (string) $w['currency']);
        if ($naira < 100) {
            $json(['ok' => false, 'msg' => 'Choose an amount first.'], 400);
        }
        $t = Transfers::start($w, $kind, $gift, $name, trim((string) ($_POST['message'] ?? '')), $naira);
        $json([
            'ok' => true, 'code' => $t['code'],
            'amount' => '₦' . number_format(intdiv((int) $t['amount_kobo'], 100)) . '.' . str_pad((string) ((int) $t['amount_kobo'] % 100), 2, '0', STR_PAD_LEFT),
            'amount_plain' => number_format((int) $t['amount_kobo'] / 100, 2, '.', ''),
            'number' => (string) $w['va_number'], 'bank' => (string) $w['va_bank'], 'name' => (string) $w['va_name'],
            'poll' => App::url('/w/' . $w['slug'] . '/give/' . $t['code']),
        ]);
    }

    /** Has the guest's transfer arrived? The page asks every few seconds. */
    public function given(string $slug, string $code): void
    {
        $t = Transfers::get($code);
        $w = $t ? Db::one('SELECT slug, currency FROM weddings WHERE id = ?', [$t['wedding_id']]) : null;
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        if (!$t || !$w || $w['slug'] !== $slug) {
            http_response_code(404);
            echo '{"status":"unknown"}';
            return;
        }
        $paid = $t['status'] === 'paid' ? Db::one('SELECT amount, currency FROM payments WHERE id = ?', [$t['payment_id']]) : null;
        echo json_encode(['status' => $t['status'], 'kind' => $t['kind'], 'name' => $t['name'],
            'amount' => $paid ? strip_tags(money(number_format((int) $paid['amount']), (string) $paid['currency'])) : ''], JSON_UNESCAPED_UNICODE);
    }
}
