<?php
declare(strict_types=1);

final class HomeController
{
    public function index(): void
    {
        View::render('home', [
            'title'  => 'Wedding Websites & More',
            'tab'    => 'home',
            'hello'  => 'Tolu & Ayo',
            'sub'    => 'Your page is live. 418 guests have replied, and 106 are still to.',
            'days'   => '84',
            'raised' => '2.7m', 'raisedCur' => 'NGN',
            'raisedSub' => '+₦44,357 this week',
            'stats'  => [
                ['n' => '418', 'l' => 'RSVPs', 'pill' => 'Total'],
                ['n' => '312', 'l' => 'Coming'],
                ['n' => '106', 'l' => 'To reply'],
                ['n' => '12',  'l' => 'Vendors'],
            ],
            'tasks'  => [
                ['i' => 'rings', 't' => 'Your story',      'href' => '/account/page',  'done' => true],
                ['i' => 'cal',   't' => 'Programs',        'href' => '/account/page',  'done' => true],
                ['i' => 'user',  't' => 'Invite the squad','href' => '/account/squad', 'v' => '3 of 8'],
                ['i' => 'gift',  't' => 'Gift registry',   'href' => '/account/money', 'v' => '₦1.4m'],
                ['i' => 'cam',   't' => 'Guest gallery',   'href' => '/account/page',  'v' => '214 photos'],
            ],
            'feats'  => [
                ['t' => 'RSVP',     's' => 'with entry passes', 'c' => 'green',  'href' => '/account/guests'],
                ['t' => 'Registry', 's' => 'cash & gifts',      'c' => 'orange', 'href' => '/account/money'],
                ['t' => 'Spray',    's' => 'from their seat',   'c' => 'pink',   'href' => '/account/money'],
                ['t' => 'Gallery',  's' => 'photos & notes',    'c' => 'blue',   'href' => '/account/page'],
            ],
            'talk'   => 'My mother-in-law calls me by my husband\'s first wife\'s name. On purpose.',
        ]);
    }
}
