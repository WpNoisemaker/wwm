<?php
declare(strict_types=1);

final class MarketController
{
    public function index(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $f = [
            'q'    => trim((string) ($_GET['q'] ?? '')),
            'kind' => trim((string) ($_GET['kind'] ?? '')),
            'city' => trim((string) ($_GET['city'] ?? '')),
        ];
        $per   = 18;
        $total = Listing::count($f);

        View::render('market/index', [
            'title'  => 'Shortlets, vacation rentals, rides, honeymoons & aso-ebi',
            'desc'   => 'Shortlets and vacation rentals, rides, honeymoons, aso-ebi and services for your wedding, from sellers you can message, review and report.',
            'tab'    => 'market',
            'css'    => ['market'],
            'rows'   => Listing::search($f, $per, ($page - 1) * $per),
            'total'  => $total,
            'pages'  => (int) ceil($total / $per),
            'page'   => $page,
            'f'      => $f,
            'kinds'  => Listing::kinds(),
            'cities' => Listing::cities(),
        ]);
    }

    public function show(string $slug): void
    {
        $l = Listing::bySlug($slug);
        if (!$l) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not found']);
            return;
        }
        View::render('market/show', [
            'title' => $l['title'] . ((string) $l['city'] !== '' ? ' — ' . $l['city'] : '') . (!empty($l['seller']) ? ' · ' . $l['seller'] : ''),
            'desc'  => mb_substr(trim((string) $l['about']) ?: (string) $l['title'], 0, 180),
            'tab'   => 'market',
            'css'   => ['market'],
            'l'     => $l,
            'ogImage' => Share::url('l', (string) $l['slug'], implode('|', [$l['photos'], $l['title'], $l['price'], $l['on_ask'], $l['seller'] ?? '', $l['seller_ok'] ?? ''])),
            'more'  => Listing::search(['kind' => (string) $l['kind']], 4),
        ]);
    }

    /** "Message the seller" from a listing. */
    public function message(string $slug): void
    {
        $l = Listing::bySlug($slug);
        if (!$l) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not found']);
            return;
        }
        ChatController::start((int) $l['partner_id'], (int) $l['id'], '/market/' . $l['slug'] . '#chat');
    }

    /**
     * Sell with us: what selling here is like, and a seller account in one form. Someone already
     * signed in just adds a shop to their account.
     */
    public function sell(): void
    {
        Guard::boot();
        $me = (int) Auth::id();
        if ($me && Market::seller($me)) {
            header('Location: ' . App::url('/account/shop'));
            exit;
        }
        $cats = Market::categories(true);
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            if (Guard::tooMany('join', 6, 30)) {
                Guard::flash('bad', 'There have been too many sign-ups from here just now. Try again shortly.');
                self::back();
            }
            $in = [
                'business' => mb_substr(trim((string) ($_POST['business'] ?? '')), 0, 190),
                'cat'      => (string) ($_POST['cat'] ?? ''),
                'city'     => mb_substr(trim((string) ($_POST['city'] ?? '')), 0, 120),
                'name'     => trim((string) ($_POST['name'] ?? '')),
                'email'    => strtolower(trim((string) ($_POST['email'] ?? ''))),
                'phone'    => trim((string) ($_POST['phone'] ?? '')),
            ];
            $pass = (string) ($_POST['pass'] ?? '');
            $bad = [];
            if ($in['business'] === '') { $bad[] = 'your business name'; }
            if (!Market::isOpen($in['cat'])) { $bad[] = 'what you sell'; }
            if (!$me) {
                if ($in['name'] === '') { $bad[] = 'your name'; }
                if (!filter_var($in['email'], FILTER_VALIDATE_EMAIL)) { $bad[] = 'a valid email address'; }
                if (strlen($pass) < 8) { $bad[] = 'a password of at least 8 characters'; }
            }
            if ($bad) {
                Guard::keep($in);
                Guard::flash('bad', 'We still need ' . preg_replace('/, ([^,]*)$/', ' and $1', implode(', ', $bad)) . '.');
                self::back();
            }
            if (!$me) {
                $u = Auth::register($in['email'], $pass, $in['name'], $in['phone']);
                if (!$u) {
                    Guard::keep($in);
                    Guard::flash('bad', 'There is already an account with that email. Sign in, then come back to Sell with us.');
                    header('Location: ' . App::url('/sign-in?to=' . urlencode('/sell')));
                    exit;
                }
                Guard::dropOld();
                Auth::login($u);
                $me = (int) $u['id'];
            }
            Db::run("UPDATE users SET role = 'partner' WHERE id = ? AND role = 'member'", [$me]);
            Db::insert('vendors', [
                'user_id' => $me, 'slug' => Listing::slugFor('vendors', $in['business']), 'name' => $in['business'],
                'category' => Market::catName($in['cat']), 'city' => $in['city'], 'phone' => $in['phone'],
            ]);
            $_SESSION['acct_ctx'] = 'shop';
            Guard::flash('ok', 'Welcome to the marketplace. Add a logo and a few words about your business, then add your first listing.');
            header('Location: ' . App::url('/account/shop/profile'));
            exit;
        }
        View::render('market/sell', [
            'title' => 'Sell to couples on Wedding Websites & More',
            'desc'  => 'List your shortlets, vacation rentals, rides, honeymoons, aso-ebi and wedding services where couples plan their day. Customers message you, and you send your own invoice or payment link.',
            'tab'   => 'market',
            'appTitle' => 'Sell with us',
            'cats'  => $cats,
            'me'    => $me,
        ]);
    }

    private static function back(): never
    {
        header('Location: ' . App::url('/sell#join'));
        exit;
    }
}
