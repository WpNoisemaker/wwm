<?php
declare(strict_types=1);

/**
 * Four complete home pages, each dressed to one reference screen. Same product, same content,
 * four different worlds — so the look can be chosen from the real thing rather than a mock-up.
 */
final class LooksController
{
    private const LOOKS = [
        'midnight' => ['Midnight', 'look-midnight', 'A dark control panel: deep navy, glass tiles, sliders with a grip.'],
        'sky'      => ['Sky',      'look-sky',      'Sky blue falling to cream, frosted white glass, one big figure.'],
        'paper'    => ['Paper',    'look-paper',    'A warm sheet with grain, soft cards, colour blobs, an acid pill.'],
        'data'     => ['Data',     'look-data',     'Off-white, dot figures, frosted pill rows and mesh gradient cards.'],
    ];

    public function index(): void
    {
        View::render('looks/index', ['title' => 'Four looks', 'tab' => 'home', 'looks' => self::LOOKS]);
    }

    public function show(string $look): void
    {
        if (!isset(self::LOOKS[$look])) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not found']);
            return;
        }
        [$name, $class, $desc] = self::LOOKS[$look];
        View::render('looks/' . $look, [
            'title'     => $name . ' — Wedding Websites & More',
            'tab'       => 'home',
            'bodyClass' => $class,
            'css'       => [$class],
            'looks'     => self::LOOKS,
            'current'   => $look,
        ]);
    }
}
