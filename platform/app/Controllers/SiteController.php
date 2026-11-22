<?php
declare(strict_types=1);

final class SiteController
{
    private const LOOKS = [
        'midnight' => ['Midnight', 'look-midnight'],
        'sky'      => ['Sky',      'look-sky'],
        'paper'    => ['Paper',    'look-paper'],
        'data'     => ['Data',     'look-data'],
    ];

    /** The homepage. ?look=paper previews it in another dressing while the choice is open. */
    public function home(): void
    {
        $look  = isset($_GET['look']) && isset(self::LOOKS[$_GET['look']]) ? (string) $_GET['look'] : 'data';
        [$name, $class] = self::LOOKS[$look];

        View::render('site/home', Site::home() + [
            'title'     => 'Wedding Websites & More — Nigerian & African weddings, everywhere',
            'desc'      => 'Browse real wedding pages, see the Buzz Chart, book verified vendors, and make your own wedding page free.',
            'tab'       => 'home',
            'bodyClass' => $class,
            'css'       => ['home', $class],
            'look'      => $look,
            'lookName'  => $name,
            'looks'     => self::LOOKS,
        ]);
    }
}
