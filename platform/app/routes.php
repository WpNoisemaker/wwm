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
$r->get('/weddings/near',       [WeddingController::class, 'near']);    // weddings near the visitor
$r->get('/weddings/{place:[a-z-]+}', [WeddingController::class, 'place']);   // each country's own listing
$r->any('/w/{slug}',            [WeddingController::class, 'show']);   // GET shows it; POST is the pass-code form on a private page
$r->post('/w/{slug}/rsvp',      [WeddingController::class, 'rsvp']);
$r->get('/w/{slug}/pass/{code:[A-Za-z0-9-]+}',       [WeddingController::class, 'pass']);
$r->post('/w/{slug}/pass/{code:[A-Za-z0-9-]+}/in',   [WeddingController::class, 'checkIn']);
$r->post('/w/{slug}/review',    [WeddingController::class, 'review']);
$r->get('/face/{id:\d+}',      [WeddingController::class, 'face']);
$r->post('/w/{slug}/note',      [WeddingController::class, 'note']);
$r->post('/w/{slug}/photo',     [WeddingController::class, 'photo']);
$r->get('/w/{slug}/sprays',     [WeddingController::class, 'sprays']);
$r->post('/w/{slug}/give',      [WeddingController::class, 'give']);     // a gift or spray by bank transfer: the details to send to
$r->get('/w/{slug}/give/{code}', [WeddingController::class, 'given']);  // has that transfer arrived?
$r->post('/webhooks/bachs',     static fn () => Bachs::webhook());        // Bachs: a transfer landed   // live spray total and top sprayers
$r->post('/w/{slug}/share',     [WeddingController::class, 'share']);
$r->post('/w/{slug}/quiz',      [WeddingController::class, 'quiz']);
$r->post('/w/{slug}/remind',    [WeddingController::class, 'remind']);   // email me about the livestream
$r->get('/w/{slug}/live.ics',   [WeddingController::class, 'ics']);
$r->get('/remind/stop/{token:[a-f0-9]{32}}', [WeddingController::class, 'remindStop']);
$r->get('/rankings',            [RankingsController::class, 'index']);
$r->get('/vendors',             [VendorController::class, 'index']);
$r->get('/vendors/{slug}',      [VendorController::class, 'show']);
$r->get('/market',              [MarketController::class, 'index']);
$r->get('/market/{slug}',       [MarketController::class, 'show']);
$r->post('/market/{slug}/message', [MarketController::class, 'message']);
$r->any('/sell',                [MarketController::class, 'sell']);
$r->post('/vendors/{slug}/message', [VendorController::class, 'message']);
$r->post('/vendors/{slug}/review',  [VendorController::class, 'review']);
$r->post('/vendors/{slug}/report',  [VendorController::class, 'report']);
$r->get('/account/messages',    [ChatController::class, 'inbox']);
$r->any('/account/messages/{pid:\d+}-{uid:\d+}', [ChatController::class, 'thread']);
$r->post('/account/messages/{pid:\d+}-{uid:\d+}/invoice', [ChatController::class, 'invoice']);
$r->any('/pay/{code:[A-Za-z0-9]+}', [ChatController::class, 'pay']);
$r->get('/blog',                [BlogController::class, 'index']);
$r->get('/blog/feed.xml',       [BlogController::class, 'feed']);
$r->get('/blog/{section:guides|news|features}', [BlogController::class, 'section']);
$r->get('/blog/{slug}',         [BlogController::class, 'show']);
$r->get('/sitemap.xml',         [SiteController::class, 'sitemap']);
$r->get('/robots.txt',          [SiteController::class, 'robots']);
$r->get('/search',              [SearchController::class, 'index']);
$r->get('/pricing',             [PageController::class, 'pricing']);
$r->get('/about',               [PageController::class, 'about']);
$r->get('/help',                [PageController::class, 'help']);
$r->get('/terms',               [PageController::class, 'terms']);
$r->get('/privacy',             [PageController::class, 'privacy']);
$r->get('/refunds',             [PageController::class, 'refunds']);
$r->get('/community-guidelines', [PageController::class, 'guidelines']);
$r->get('/cookies',             [PageController::class, 'cookies']);

// --- The anonymous community ------------------------------------------------------------------
$r->get('/community',           [CommunityController::class, 'index']);
$r->get('/community/{id:\d+}',  [CommunityController::class, 'show']);
$r->post('/community/post',     [CommunityController::class, 'store']);
$r->post('/community/{id:\d+}/reply', [CommunityController::class, 'reply']);
$r->post('/community/{id:\d+}/like',  [CommunityController::class, 'like']);

// --- Getting in and out -----------------------------------------------------------------------
$r->any('/sign-in',             [AuthController::class, 'signIn']);
$r->any('/join',                [AuthController::class, 'join']);
$r->any('/join/customer',       [AuthController::class, 'customer']);
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
$r->post('/account/squad/{id:\d+}/decide', [AccountController::class, 'squadDecide']);
$r->post('/account/squad/link', [AccountController::class, 'squadLink']);
$r->any('/w/{slug}/squad/join/{key:[a-f0-9]+}', [WeddingController::class, 'squadJoin']);
$r->any('/account/story',       [AccountController::class, 'story']);
$r->post('/account/request',    [AccountController::class, 'request']);
$r->post('/account/bank',       [AccountController::class, 'bank']);
$r->post('/account/bank/check', [AccountController::class, 'bankCheck']);
$r->post('/account/bank/save',  [AccountController::class, 'bankSave']);
$r->any('/account/registry',    [AccountController::class, 'registry']);
$r->any('/account/vendors',     [AccountController::class, 'vendors']);
$r->any('/account/team',        [TeamController::class, 'index']);
$r->post('/account/team/{id:\d+}', [TeamController::class, 'update']);
$r->get('/team',                [TeamController::class, 'mine']);
$r->get('/team/join/{token:[a-f0-9]+}', [TeamController::class, 'join']);
$r->get('/team/open/{id:\d+}',  [TeamController::class, 'open']);
$r->get('/team/leave',          [TeamController::class, 'leave']);
$r->any('/team/{id:\d+}/reviews', [TeamController::class, 'reviews']);
$r->get('/door/{slug}',         [TeamController::class, 'door']);
$r->post('/door/{slug}/{id:\d+}/{dir:in|out}', [TeamController::class, 'clock']);
$r->any('/account/quiz',        [AccountController::class, 'quiz']);
$r->any('/account/sections',    [AccountController::class, 'sections']);
$r->any('/account/shop',        [AccountController::class, 'shop']);   // a marketplace seller's listings
$r->get('/account/shop/profile', [AccountController::class, 'shopProfile']);
$r->get('/account/guests',      [AccountController::class, 'guests']);
$r->post('/account/guests/{id:\d+}', [AccountController::class, 'guestUpdate']);
$r->post('/account/guests/{id:\d+}/decide', [AccountController::class, 'guestDecide']);
$r->post('/account/guests/approve-all', [AccountController::class, 'guestApproveAll']);
$r->post('/account/guests/door', [AccountController::class, 'guestDoor']);
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
$r->post('/hq/packs/{key}',     [HqController::class, 'packAdd']);
$r->any('/hq/staff',            [HqController::class, 'staff']);
$r->any('/hq/content',          [HqController::class, 'content']);
$r->post('/hq/drive',           [HqController::class, 'drive']);
$r->get('/hq/drive/connect',    [HqController::class, 'driveConnect']);
$r->get('/hq/drive/callback',   [HqController::class, 'driveCallback']);
$r->post('/hq/drive/folder/{id:\d+}', [HqController::class, 'driveFolder']);
$r->any('/hq/market',           [HqController::class, 'market']);
$r->get('/hq/emails',           [HqController::class, 'emails']);
$r->any('/hq/ads',              [HqController::class, 'ads']);
$r->any('/hq/requests',         [HqController::class, 'requests']);
$r->post('/hq/bank/{id:\d+}',   [HqController::class, 'bank']);
$r->post('/hq/settle/{id:\d+}', [HqController::class, 'settle']);
$r->get('/hq/manage/{id:\d+|stop}', [HqController::class, 'manage']);
$r->get('/hq/edit',             [HqController::class, 'edit']);         // edit mode on or off
$r->post('/hq/edit/save',       [HqController::class, 'editSave']);     // words changed on the page
$r->post('/hq/edit/image',      [HqController::class, 'editImage']);    // a picture replaced on the page
$r->post('/hq/edit/undo',       [HqController::class, 'editUndo']);     // HQ undoes a change
$r->any('/hq/categories',       [HqController::class, 'categories']);
$r->get('/hq/blog',             [HqController::class, 'blog']);
$r->any('/hq/blog/new',         [HqController::class, 'blogEdit']);
$r->any('/hq/blog/{id:\d+}',    [HqController::class, 'blogEdit']);

// --- The app: install, offline, and the design gallery -------------------------------------
$r->get('/templates',           [SiteController::class, 'templates']);
$r->get('/manifest.webmanifest', [SiteController::class, 'manifest']);
$r->get('/sw.js',               [SiteController::class, 'sw']);
$r->get('/offline',             [SiteController::class, 'offline']);
$r->get('/og/{kind:[a-z]}/{key:[A-Za-z0-9_%-]+}.jpg', [SiteController::class, 'og']);   // link-preview pictures
