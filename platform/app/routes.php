<?php
/**
 * Every address on the site. Kept in one file so a page is never reachable by accident and the
 * controllers have a single contract to meet.
 *
 * @var Router $r
 */

// --- The public site --------------------------------------------------------------------------
$r->get('/',                    [SiteController::class, 'home']);
$r->get('/weddings',            [WeddingController::class, 'index']);
$r->any('/w/{slug}',            [WeddingController::class, 'show']);   // GET shows it; POST is the pass-code form on a private page
$r->post('/w/{slug}/rsvp',      [WeddingController::class, 'rsvp']);
$r->post('/w/{slug}/note',      [WeddingController::class, 'note']);
$r->get('/rankings',            [RankingsController::class, 'index']);
$r->get('/vendors',             [VendorController::class, 'index']);
$r->get('/vendors/{slug}',      [VendorController::class, 'show']);
$r->get('/market',              [MarketController::class, 'index']);
$r->get('/market/{slug}',       [MarketController::class, 'show']);
$r->get('/blog',                [BlogController::class, 'index']);
$r->get('/blog/{slug}',         [BlogController::class, 'show']);
$r->get('/search',              [SearchController::class, 'index']);
$r->get('/pricing',             [PageController::class, 'pricing']);
$r->get('/about',               [PageController::class, 'about']);
$r->get('/help',                [PageController::class, 'help']);
$r->get('/terms',               [PageController::class, 'terms']);
$r->get('/privacy',             [PageController::class, 'privacy']);

// --- The anonymous community ------------------------------------------------------------------
$r->get('/community',           [CommunityController::class, 'index']);
$r->get('/community/{id:\d+}',  [CommunityController::class, 'show']);
$r->post('/community/post',     [CommunityController::class, 'store']);
$r->post('/community/{id:\d+}/reply', [CommunityController::class, 'reply']);
$r->post('/community/{id:\d+}/like',  [CommunityController::class, 'like']);

// --- Getting in and out -----------------------------------------------------------------------
$r->any('/sign-in',             [AuthController::class, 'signIn']);
$r->any('/join',                [AuthController::class, 'join']);
$r->post('/sign-out',           [AuthController::class, 'signOut']);
$r->any('/forgot',              [AuthController::class, 'forgot']);
$r->get('/verify/{token}',       [AuthController::class, 'verify']);
$r->any('/reset/{token}',       [AuthController::class, 'reset']);

// --- The couple's own pages -------------------------------------------------------------------
$r->get('/account',             [AccountController::class, 'index']);
$r->any('/account/new',         [AccountController::class, 'create']);
$r->any('/account/page',        [AccountController::class, 'page']);
$r->any('/account/programs',    [AccountController::class, 'programs']);
$r->any('/account/squad',       [AccountController::class, 'squad']);
$r->any('/account/registry',    [AccountController::class, 'registry']);
$r->get('/account/guests',      [AccountController::class, 'guests']);
$r->post('/account/guests/{id:\d+}', [AccountController::class, 'guestUpdate']);
$r->get('/account/money',       [AccountController::class, 'money']);
$r->post('/account/payout',     [AccountController::class, 'payout']);
$r->post('/account/verify/resend', [AccountController::class, 'resendVerify']);
$r->any('/account/settings',    [AccountController::class, 'settings']);

// --- HQ, for staff ----------------------------------------------------------------------------
$r->get('/hq',                  [HqController::class, 'index']);
$r->get('/hq/weddings',         [HqController::class, 'weddings']);
$r->get('/hq/people',           [HqController::class, 'people']);
$r->get('/hq/money',            [HqController::class, 'money']);
$r->any('/hq/settings',         [HqController::class, 'settings']);
$r->post('/hq/test-mail',       [HqController::class, 'testMail']);
$r->post('/hq/demo/add',        [HqController::class, 'demoAdd']);
$r->post('/hq/demo/remove',     [HqController::class, 'demoRemove']);

// --- Kept from the build, handy for comparing the four dressings ------------------------------
$r->get('/looks',               [LooksController::class, 'index']);
$r->get('/looks/{look}',        [LooksController::class, 'show']);
