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
        if (!$v) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not found']);
            return;
        }
        View::render('vendor/show', [
            'title'   => $v['name'] . ' — ' . ($v['category'] ?: 'Vendor') . ((string) $v['city'] !== '' ? ', ' . $v['city'] : ''),
            'desc'    => mb_substr(trim((string) $v['about']) ?: ($v['name'] . ' on Wedding Websites & More.'), 0, 180),
            'tab'     => 'vendors',
            'css'     => ['market'],
            'v'       => $v,
            'ogImage' => Share::url('v', (string) $v['slug'], implode('|', [$v['logo'], $v['name'], $v['category'], $v['city'], $v['verified']])),
            'reviews' => Vendor::reviews((int) $v['id']),
            'sells'   => $v['user_id'] ? Listing::bySeller((int) $v['user_id']) : [],
        ]);
    }
}
