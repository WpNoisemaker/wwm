<?php
declare(strict_types=1);

final class HomeController
{
    public function index(): void
    {
        // Until sign-in lands, the home screen shows the soonest public wedding, so the page is
        // driven entirely by imported rows rather than by anything hand-written.
        $w = Wedding::forUser(Auth::id());
        if (!$w) {
            View::render('empty', ['title' => 'Wedding Websites & More', 'tab' => 'home']);
            return;
        }
        $d    = Wedding::dash((int) $w['id']);
        $days = Wedding::daysTo($w);
        $talk = Talk::latest();

        View::render('home', [
            'title'  => Wedding::names($w) . ' — Wedding Websites & More',
            'tab'    => 'home',
            'w'      => $w,
            'd'      => $d,
            'days'   => $days,
            'talk'   => $talk,
            'tasks'  => [
                ['i' => 'rings', 't' => 'Your story',       'href' => '/account/page',   'done' => trim((string) $w['story']) !== ''],
                ['i' => 'cal',   't' => 'Programs',         'href' => '/account/page',   'v' => $d['programs'] ?: null, 'done' => $d['programs'] > 0],
                ['i' => 'user',  't' => 'The squad',        'href' => '/account/squad',  'v' => $d['squad'] ?: 'None yet'],
                ['i' => 'gift',  't' => 'Gift registry',    'href' => '/account/money',  'money' => short_money($d['raised'])],
                ['i' => 'cam',   't' => 'Guest gallery',    'href' => '/account/page',   'v' => $d['photos'] . ' photos'],
                ['i' => 'store', 't' => 'Vendors',          'href' => '/account/vendors','v' => 'Browse'],
            ],
            'feats'  => [
                ['t' => 'RSVP',     's' => 'with entry passes', 'c' => 'green',  'href' => '/account/guests'],
                ['t' => 'Registry', 's' => 'cash & gifts',      'c' => 'orange', 'href' => '/account/money'],
                ['t' => 'Spray',    's' => 'from their seat',   'c' => 'pink',   'href' => '/account/money'],
                ['t' => 'Gallery',  's' => 'photos & notes',    'c' => 'blue',   'href' => '/account/page'],
            ],
        ]);
    }
}
