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
            'styles'  => array_keys(Wedding::STYLES),
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

        if (!$mine) {
            Db::run('UPDATE weddings SET views = views + 1 WHERE id = ?', [$w['id']]);
        }

        $id       = (int) $w['id'];
        $programs = Wedding::programs($id);
        $next     = Wedding::nextMoment($w, $programs);

        View::render('wedding/show', [
            'title'    => Wedding::names($w) . ' — ' . Wedding::when($w),
            'desc'     => trim((string) $w['tagline']) !== ''
                ? (string) $w['tagline']
                : Wedding::names($w) . ' are getting married. RSVP, send a gift and leave them a note.',
            'tab'      => 'weddings',
            'css'      => ['wedding'],
            'bare'     => false,
            'w'        => $w,
            'mine'     => $mine,
            'pal'      => Wedding::palette($w),
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

        Db::insert('guests', [
            'wedding_id' => $w['id'],
            'name'       => $name,
            'phone'      => trim((string) ($_POST['phone'] ?? '')),
            'email'      => trim((string) ($_POST['email'] ?? '')),
            'party'      => $party,
            'coming'     => $coming,
            'note'       => trim((string) ($_POST['note'] ?? '')),
            // A pass code only means something once the page can check people in.
            'pass_code'  => Wedding::rank((string) $w['plan']) >= Wedding::rank('pro') ? strtoupper(bin2hex(random_bytes(3))) : null,
        ]);
        Db::run('UPDATE weddings SET buzz = buzz + 12 WHERE id = ?', [$w['id']]);

        Guard::flash('ok', $coming === 'no'
            ? 'Thank you for letting them know.'
            : 'You are on the list. They will be glad you are coming.');
        self::back($slug, 'rsvp');
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
        if ($name === '' || $body === '') {
            Guard::flash('bad', 'A note needs your name and a few words.');
            self::back($slug, 'wall');
        }

        Db::insert('wall', [
            'wedding_id' => $w['id'],
            'kind'       => 'note',
            'name'       => mb_substr($name, 0, 160),
            'body'       => mb_substr($body, 0, 1000),
            'status'     => 'live',
        ]);
        Db::run('UPDATE weddings SET buzz = buzz + 6 WHERE id = ?', [$w['id']]);
        Guard::flash('ok', 'Your note is on their wall.');
        self::back($slug, 'wall');
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
            'css'   => ['wedding'],
            'bare'  => true,
            'w'     => $w,
        ]);
    }

    private static function back(string $slug, string $anchor): never
    {
        header('Location: ' . App::url('/w/' . $slug) . '#' . $anchor);
        exit;
    }

    private static function notFound(): never
    {
        http_response_code(404);
        View::render('errors/404', ['title' => 'Not found']);
        exit;
    }
}
