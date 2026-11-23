<?php
declare(strict_types=1);

/** Getting in, getting out, and getting a first wedding page made. */
final class AuthController
{
    public function signIn(): void
    {
        if (Auth::check()) {
            self::go('/account');
        }
        $to = self::safeTo((string) ($_GET['to'] ?? ''));

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            $to = self::safeTo((string) ($_POST['to'] ?? ''));
            // Only FAILED attempts count, and they are counted twice: per address, and per account.
            // Counting every sign-in would lock out real people, because a mobile network can put
            // thousands of them behind one address. The per-account limit is the one that stops
            // somebody guessing at a single password from many addresses. The email is hashed so the
            // throttle table never holds a list of who is being guessed at.
            $mail  = strtolower(trim((string) ($_POST['email'] ?? '')));
            $byIp  = 'sign-in:ip:' . Guard::ip();
            $byMail = 'sign-in:acct:' . sha1($mail);
            if (Guard::blocked($byIp, 30) || Guard::blocked($byMail, 8)) {
                Guard::flash('bad', 'Too many tries. Wait a few minutes and try again.');
                self::go('/sign-in');
            }
            $u = Auth::attempt((string) ($_POST['email'] ?? ''), (string) ($_POST['pass'] ?? ''));
            if (!$u) {
                Guard::hit($byIp, 15);
                Guard::hit($byMail, 15);
                // One message for both halves, so this cannot be used to discover who has an account.
                Guard::keep(['email' => (string) ($_POST['email'] ?? '')]);
                Guard::flash('bad', 'That email and password do not match.');
                self::go('/sign-in' . ($to ? '?to=' . urlencode($to) : ''));
            }
            Guard::dropOld();
            Guard::forget($byMail);
            Auth::login($u);
            self::go($to ?: '/account');
        }

        View::render('auth/sign-in', [
            'title'    => 'Sign in',
            'appTitle' => 'Sign in',
            'tab'      => 'account',
            'to'       => $to,
        ]);
    }

    public function join(): void
    {
        if (Auth::check()) {
            self::go('/account');
        }

        if ((($_SERVER['REQUEST_METHOD'] ?? '') === 'POST')) {
            Guard::must();
            if (Guard::tooMany('join', 6, 30)) {
                Guard::flash('bad', 'Too many sign-ups from here just now. Try again shortly.');
                self::go('/join');
            }
            $in = [
                'name'  => trim((string) ($_POST['name'] ?? '')),
                'email' => strtolower(trim((string) ($_POST['email'] ?? ''))),
                'phone' => trim((string) ($_POST['phone'] ?? '')),
                'a'     => trim((string) ($_POST['a'] ?? '')),
                'b'     => trim((string) ($_POST['b'] ?? '')),
                'date'  => trim((string) ($_POST['date'] ?? '')),
                'city'  => trim((string) ($_POST['city'] ?? '')),
            ];
            $pass = (string) ($_POST['pass'] ?? '');

            $bad = [];
            if ($in['name'] === '')                                   { $bad[] = 'your name'; }
            if (!filter_var($in['email'], FILTER_VALIDATE_EMAIL))     { $bad[] = 'a real email address'; }
            if (strlen($pass) < 8)                                    { $bad[] = 'a password of at least 8 characters'; }
            if ($in['a'] === '' || $in['b'] === '')                   { $bad[] = 'both of your first names'; }
            if ($bad) {
                Guard::keep($in);
                Guard::flash('bad', 'We still need ' . self::list($bad) . '.');
                self::go('/join');
            }

            $u = Auth::register($in['email'], $pass, $in['name'], $in['phone']);
            if (!$u) {
                Guard::keep($in);
                Guard::flash('bad', 'There is already an account on that email. Sign in instead.');
                self::go('/sign-in');
            }

            Db::insert('weddings', [
                'owner_id'   => $u['id'],
                'slug'       => self::freeSlug($in['a'] . '-' . $in['b']),
                'partner_a'  => $in['a'],
                'partner_b'  => $in['b'],
                'wed_date'   => $in['date'] !== '' ? $in['date'] : null,
                'city'       => $in['city'],
                'country'    => 'Nigeria',
                'plan'       => 'free',
                // It starts private, so nobody lands on a half-made page.
                'visibility' => 'private',
                'currency'   => 'NGN',
                'template'   => 'classic',
            ]);

            Guard::dropOld();
            Auth::login($u);
            if (Verify::enabled()) {
                // The admin has switched confirmation on. They can still build the page; the link is
                // what lets it go public.
                Verify::send($u);
                Guard::flash('ok', 'Welcome. Your page is made. We have emailed you a link to confirm your address — you will need it before the page can go public.');
            } else {
                Guard::flash('ok', 'Welcome. Your page is made — fill it in, then make it public when you are ready.');
            }
            self::go('/account');
        }

        View::render('auth/join', ['title' => 'Create your free wedding page', 'appTitle' => 'Make your page', 'back' => App::url('/'), 'tab' => 'account']);
    }

    public function signOut(): void
    {
        Guard::must();
        Auth::logout();
        Guard::flash('ok', 'Signed out.');
        self::go('/');
    }

    public function forgot(): void
    {
        $link = '';
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            $u = Db::one('SELECT id FROM users WHERE email = ?', [$email]);
            if ($u && !Guard::tooMany('forgot', 5, 30)) {
                $tok = bin2hex(random_bytes(32));
                Db::run(
                    "INSERT INTO tokens (user_id, kind, token, expires_at) VALUES (?, 'reset', ?, DATE_ADD(NOW(), INTERVAL 2 HOUR))",
                    [$u['id'], $tok]
                );
                $url = App::url('/reset/' . $tok);
                Mailer::send(
                    $email,
                    'Reset your password',
                    "Hello,\n\nSomebody asked to reset the password on your Wedding Websites & More account.\n"
                    . "To choose a new one, open this link:\n\n" . $url . "\n\n"
                    . "It works once and expires in 2 hours. If this was not you, ignore this email "
                    . "and nothing will change.\n\n— Wedding Websites & More\n"
                );
                // In debug the link is also shown on screen, so the flow can be tested on a machine
                // that cannot send mail. A live site never shows it.
                if (App::config('debug')) {
                    $link = $url;
                }
            }
            // The same answer whether or not the address exists.
            Guard::flash('ok', 'If that email has an account, a reset link is on its way.');
            if ($link === '') {
                self::go('/forgot');
            }
        }
        View::render('auth/forgot', ['title' => 'Forgot password', 'appTitle' => 'Password', 'back' => App::url('/sign-in'), 'link' => $link]);
    }

    /** The link in the confirmation email. Works signed in or out, and only once. */
    public function verify(string $token): void
    {
        if (Verify::confirm($token)) {
            Guard::flash('ok', 'Thank you — your email is confirmed.');
        } else {
            Guard::flash('bad', 'That link has expired or has already been used. Sign in and ask for a new one.');
        }
        self::go(Auth::check() ? '/account' : '/sign-in');
    }

    public function reset(string $token): void
    {
        $row = Db::one(
            "SELECT * FROM tokens WHERE token = ? AND kind = 'reset' AND used_at IS NULL AND expires_at > NOW()",
            [$token]
        );
        if (!$row) {
            Guard::flash('bad', 'That link has expired or has already been used. Ask for another.');
            self::go('/forgot');
        }

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            $pass = (string) ($_POST['pass'] ?? '');
            if (strlen($pass) < 8) {
                Guard::flash('bad', 'Pick a password of at least 8 characters.');
                self::go('/reset/' . $token);
            }
            Db::update('users', ['pass' => password_hash($pass, PASSWORD_DEFAULT)], 'id = ?', [$row['user_id']]);
            Db::run('UPDATE tokens SET used_at = NOW() WHERE token = ?', [$token]);
            // A password change ends every other session, which is the point of keeping them in a table.
            Db::run('DELETE FROM sessions WHERE user_id = ?', [$row['user_id']]);
            Guard::flash('ok', 'Password changed. Sign in with it.');
            self::go('/sign-in');
        }

        View::render('auth/reset', ['title' => 'Choose a new password', 'appTitle' => 'Password', 'token' => $token]);
    }

    /* ---------------------------------------------------------------------------------------- */

    /** Only ever redirect inside this site. "//evil.com" is a path to a browser, so it is refused. */
    /**
     * A customer account: a name, an email and a password, nothing about a wedding. It is what lets
     * someone message sellers on the marketplace. They go straight back to where they were.
     */
    public function customer(): void
    {
        $to = self::safeTo((string) ($_GET['to'] ?? $_POST['to'] ?? '')) ?: '/market';
        if (Auth::check()) {
            self::go($to);
        }
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            if (Guard::tooMany('join', 6, 30)) {
                Guard::flash('bad', 'Too many sign-ups from here just now. Try again shortly.');
                self::go('/join/customer?to=' . urlencode($to));
            }
            $in = ['name' => trim((string) ($_POST['name'] ?? '')), 'email' => strtolower(trim((string) ($_POST['email'] ?? ''))), 'phone' => trim((string) ($_POST['phone'] ?? ''))];
            $pass = (string) ($_POST['pass'] ?? '');
            $bad = [];
            if ($in['name'] === '')                               { $bad[] = 'your name'; }
            if (!filter_var($in['email'], FILTER_VALIDATE_EMAIL)) { $bad[] = 'a real email address'; }
            if (strlen($pass) < 8)                                { $bad[] = 'a password of at least 8 characters'; }
            if ($bad) {
                Guard::keep($in);
                Guard::flash('bad', 'We still need ' . self::list($bad) . '.');
                self::go('/join/customer?to=' . urlencode($to));
            }
            $u = Auth::register($in['email'], $pass, $in['name'], $in['phone']);
            if (!$u) {
                Guard::keep($in);
                Guard::flash('bad', 'There is already an account on that email. Sign in instead.');
                self::go('/sign-in?to=' . urlencode($to));
            }
            Guard::dropOld();
            Auth::login($u);
            $_SESSION['acct_ctx'] = 'customer';
            Guard::flash('ok', 'Your account is ready. Write to the seller below.');
            self::go($to);
        }
        View::render('auth/customer', ['title' => 'Create a customer account', 'tab' => 'account', 'appTitle' => 'Customer account', 'to' => $to, 'back' => App::url($to)]);
    }

    private static function safeTo(string $to): string
    {
        return ($to !== '' && $to[0] === '/' && !str_starts_with($to, '//')) ? $to : '';
    }

    private static function freeSlug(string $raw): string
    {
        $base = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($raw)) ?? '', '-') ?: 'wedding';
        $slug = $base;
        for ($i = 2; Db::val('SELECT id FROM weddings WHERE slug = ?', [$slug]); $i++) {
            $slug = $base . '-' . $i;
        }
        return $slug;
    }

    private static function list(array $bits): string
    {
        if (count($bits) === 1) {
            return $bits[0];
        }
        $last = array_pop($bits);
        return implode(', ', $bits) . ' and ' . $last;
    }

    private static function go(string $path): never
    {
        header('Location: ' . App::url($path));
        exit;
    }
}
