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
            'sort'  => in_array($_GET['sort'] ?? '', ['soon', 'new', 'buzz'], true) ? (string) $_GET['sort'] : 'soon',
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
            'sort'    => in_array($_GET['sort'] ?? '', ['soon', 'new', 'buzz'], true) ? (string) $_GET['sort'] : 'soon',
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
            'sponsors' => Wedding::has($w, 'quiet')   ? [] : Wedding::sponsors(2),
            'vendors'  => Wedding::vendors($w),
            'pass'     => $_SESSION['passes'][(string) $w['slug']] ?? null,
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

        $name = trim((string) ($_POST['name'] ?? ''));
        $party = max(1, min(20, (int) ($_POST['party'] ?? 1)));
        $coming = in_array($_POST['coming'] ?? '', ['yes', 'no', 'maybe'], true) ? (string) $_POST['coming'] : 'yes';
        if ($name === '') {
            Guard::flash('bad', 'Please put your name so they know who replied.');
            self::back($slug, 'rsvp');
        }

        $pass = Wedding::rank((string) $w['plan']) >= Wedding::rank('pro') && $coming !== 'no' ? strtoupper(bin2hex(random_bytes(3))) : null;
        Db::insert('guests', [
            'wedding_id' => $w['id'],
            'name'       => $name,
            'phone'      => trim((string) ($_POST['phone'] ?? '')),
            'email'      => trim((string) ($_POST['email'] ?? '')),
            'party'      => $party,
            'coming'     => $coming,
            'note'       => trim((string) ($_POST['note'] ?? '')),
            // A pass code only means something once the page can check people in.
            'pass_code'  => $pass,
        ]);
        // The guest's entry pass is shown on the page straight away, and stays on this phone.
        if ($pass !== null) {
            $_SESSION['passes'][(string) $w['slug']] = ['code' => $pass, 'name' => $name, 'party' => $party];
        }
        Buzz::add($w, 'rsvp');

        Guard::flash('ok', $coming === 'no'
            ? 'Thank you for letting them know.'
            : 'You are on the list. They will be glad you are coming.');
        self::back($slug, 'rsvp');
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

        $name = trim((string) ($_POST['name'] ?? ''));
        $body = trim((string) ($_POST['body'] ?? ''));
        // Guests on a Pro page and up can add a photo with their note.
        $image = null;
        if (Wedding::has($w, 'gallery') && !empty($_FILES['photo']['name'])) {
            $image = Images::store($_FILES['photo'], 'wall/' . (int) $w['id']);
            if ($image === null) {
                Guard::flash('bad', 'That photo could not be used. Try a JPEG or PNG under 12 MB.');
                self::back($slug, 'wall');
            }
        }
        if ($name === '' || ($body === '' && $image === null)) {
            Guard::flash('bad', 'A note needs your name and a few words.');
            self::back($slug, 'wall');
        }

        Db::insert('wall', [
            'wedding_id' => $w['id'],
            'kind'       => $image !== null ? 'photo' : 'note',
            'name'       => mb_substr($name, 0, 160),
            'body'       => mb_substr($body, 0, 1000),
            'image'      => $image ?? '',
            'status'     => 'live',
        ]);
        Buzz::add($w, $image !== null ? 'photo' : 'note');
        Guard::flash('ok', $image !== null ? 'Your photo is in their gallery.' : 'Your note is on their wall.');
        self::back($slug, $image !== null ? 'photos' : 'wall');
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
}
