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
            'title'  => 'Stays, rides, honeymoons & aso-ebi',
            'desc'   => 'Book the apartment, the convoy, the honeymoon and the aso-ebi in one place. Your money is held until you confirm.',
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
            'title' => $l['title'] . ' — ' . $l['city'],
            'desc'  => mb_substr(trim((string) $l['about']) ?: (string) $l['title'], 0, 180),
            'tab'   => 'market',
            'css'   => ['market'],
            'l'     => $l,
            'more'  => Listing::search(['kind' => (string) $l['kind']], 4),
        ]);
    }
}
