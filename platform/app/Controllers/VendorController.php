<?php
declare(strict_types=1);

final class VendorController
{
    public function index(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $f = [
            'q'        => trim((string) ($_GET['q'] ?? '')),
            'category' => trim((string) ($_GET['category'] ?? '')),
            'city'     => trim((string) ($_GET['city'] ?? '')),
        ];
        $per   = 18;
        $total = Vendor::count($f);

        View::render('vendor/index', [
            'title'  => 'Wedding vendors',
            'desc'   => 'Photographers, caterers, bands, gele artists and planners, reviewed by couples who actually booked them.',
            'tab'    => 'vendors',
            'css'    => ['market'],
            'rows'   => Vendor::search($f, $per, ($page - 1) * $per),
            'total'  => $total,
            'pages'  => (int) ceil($total / $per),
            'page'   => $page,
            'f'      => $f,
            'cats'   => Vendor::categories(),
            'cities' => Vendor::cities(),
        ]);
    }

    public function show(string $slug): void
    {
        $v = Vendor::bySlug($slug);
        if (!$v || (string) ($v['status'] ?? 'active') === 'removed') {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not found']);
            return;
        }
        $me = (int) Auth::id();
        View::render('vendor/show', [
            'title'   => $v['name'] . ' — ' . ($v['category'] ?: 'Vendor') . ((string) $v['city'] !== '' ? ', ' . $v['city'] : ''),
            'desc'    => mb_substr(trim((string) $v['about']) ?: ($v['name'] . ' on Wedding Websites & More.'), 0, 180),
            'tab'     => 'vendors',
            'css'     => ['market'],
            'v'       => $v,
            'ogImage' => Share::url('v', (string) $v['slug'], implode('|', [$v['logo'], $v['name'], $v['category'], $v['city'], $v['verified']])),
            'reviews' => Vendor::reviews((int) $v['id']),
            'sells'   => $v['user_id'] ? Listing::bySeller((int) $v['user_id']) : [],
            'projects' => Market::projects($v),
            'me'      => $me,
            'canReview' => Market::canReview($v, $me),
            'mine'    => $me && $me === (int) $v['user_id'],
            'myReview' => $me ? Db::one('SELECT * FROM reviews WHERE vendor_id = ? AND user_id = ?', [$v['id'], $me]) : null,
        ]);
    }

    private static function live(string $slug): array
    {
        $v = Vendor::bySlug($slug);
        if (!$v || !$v['user_id'] || (string) ($v['status'] ?? 'active') === 'removed') {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not found']);
            exit;
        }
        return $v;
    }

    /** "Message" from the seller's profile. */
    public function message(string $slug): void
    {
        $v = self::live($slug);
        ChatController::start((int) $v['user_id'], null, '/vendors/' . $v['slug'] . '#chat');
    }

    /** A review, from a customer who has talked to the seller here. One each; writing again replaces it. */
    public function review(string $slug): void
    {
        Auth::wall();
        Guard::must();
        $v = self::live($slug);
        $me = (int) Auth::id();
        $back = 'Location: ' . App::url('/vendors/' . $v['slug'] . '#reviews');
        if (!Market::canReview($v, $me)) {
            Guard::flash('bad', 'Reviews come from couples who booked ' . $v['name'] . ' and from guests at their weddings. If you booked them, add them to your wedding page under Vendors first.');
            header($back);
            exit;
        }
        $body = trim((string) ($_POST['body'] ?? ''));
        if (mb_strlen($body) < 10) {
            Guard::flash('bad', 'Please write a sentence or two about how it went.');
            header($back);
            exit;
        }
        Market::review($v, $me, (int) ($_POST['stars'] ?? 5), $body);
        Guard::flash('ok', 'Thank you. Your review is now on their profile.');
        header($back);
        exit;
    }

    /** "Report this seller". Signed in, once per seller. What happens next is HQ's business. */
    public function report(string $slug): void
    {
        Auth::wall();
        Guard::must();
        $v = self::live($slug);
        $me = (int) Auth::id();
        $back = (string) ($_POST['back'] ?? '');
        $back = $back !== '' && $back[0] === '/' && !str_starts_with($back, '//') ? $back : '/vendors/' . $v['slug'];
        if ($me === (int) $v['user_id']) {
            header('Location: ' . App::url($back));
            exit;
        }
        $listing = (int) ($_POST['listing_id'] ?? 0);
        $ok = Market::report($v, $me, (string) ($_POST['reason'] ?? 'other'), trim((string) ($_POST['body'] ?? '')), $listing ?: null);
        Guard::flash('ok', $ok ? 'Thank you. We look at every report.' : 'You have already reported this seller. Thank you; we are looking at it.');
        header('Location: ' . App::url($back));
        exit;
    }
}
