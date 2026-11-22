<?php
declare(strict_types=1);

/** Staff only. Everything here reads the real tables; nothing is estimated. */
final class HqController
{
    private function gate(): void
    {
        Auth::wall();
        if (!Auth::is('staff') && !Auth::is('owner')) {
            http_response_code(403);
            View::render('errors/403', ['title' => 'Not for you']);
            exit;
        }
    }

    private function show(string $tpl, array $d): void
    {
        View::render('hq/' . $tpl, $d + ['tab' => 'account', 'appTitle' => 'HQ', 'back' => $tpl === 'index' ? null : App::url('/hq')]);
    }

    public function index(): void
    {
        $this->gate();
        $this->show('index', [
            'title'  => 'HQ',
            'nums'   => [
                'people'   => (int) Db::val('SELECT COUNT(*) FROM users', [], 0),
                'weddings' => (int) Db::val('SELECT COUNT(*) FROM weddings', [], 0),
                'public'   => (int) Db::val("SELECT COUNT(*) FROM weddings WHERE visibility='public'", [], 0),
                'guests'   => (int) Db::val('SELECT COUNT(*) FROM guests', [], 0),
                'vendors'  => (int) Db::val('SELECT COUNT(*) FROM vendors', [], 0),
                'raised'   => (int) Db::val("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status IN ('paid','released')", [], 0),
                'owed'     => (int) Db::val("SELECT COALESCE(SUM(amount),0) FROM payouts WHERE status='requested'", [], 0),
            ],
            'plans'  => Db::all('SELECT plan, COUNT(*) n FROM weddings GROUP BY plan ORDER BY n DESC'),
            'recent' => Db::all('SELECT id, name, email, role, created_at FROM users ORDER BY id DESC LIMIT 8'),
            'asks'   => Db::all("SELECT p.*, u.name, u.email FROM payouts p JOIN users u ON u.id = p.user_id WHERE p.status='requested' ORDER BY p.created_at LIMIT 10"),
        ]);
    }

    public function weddings(): void
    {
        $this->gate();
        $this->show('weddings', [
            'title' => 'Weddings',
            'rows'  => Db::all(
                'SELECT w.*, u.email FROM weddings w LEFT JOIN users u ON u.id = w.owner_id
                 ORDER BY w.created_at DESC, w.id DESC LIMIT 100'
            ),
        ]);
    }

    public function people(): void
    {
        $this->gate();
        $this->show('people', [
            'title' => 'People',
            'rows'  => Db::all('SELECT * FROM users ORDER BY id DESC LIMIT 200'),
        ]);
    }

    public function money(): void
    {
        $this->gate();
        $this->show('money', [
            'title'    => 'Money',
            'payments' => Db::all('SELECT * FROM payments ORDER BY created_at DESC LIMIT 60'),
            'payouts'  => Db::all('SELECT p.*, u.name FROM payouts p JOIN users u ON u.id = p.user_id ORDER BY p.created_at DESC LIMIT 40'),
            'in'       => (int) Db::val("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status IN ('paid','released')", [], 0),
            'fees'     => (int) Db::val("SELECT COALESCE(SUM(fee),0) FROM payments WHERE status IN ('paid','released')", [], 0),
        ]);
    }

    public function settings(): void
    {
        $this->gate();
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            foreach (['pro_price', 'premium_price', 'fee_gift', 'email', 'whatsapp'] as $k) {
                if (array_key_exists($k, $_POST)) {
                    Db::run('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)',
                            [$k, trim((string) $_POST[$k])]);
                }
            }
            // A ticked box is present in the POST, an unticked one is simply absent.
            Verify::set(isset($_POST['email_verify']));
            Guard::flash('ok', 'Saved. The pricing page now shows these.');
            header('Location: ' . App::url('/hq/settings'));
            exit;
        }
        $this->show('settings', [
            'title' => 'HQ settings',
            'v'     => [
                'pro_price'     => Site::setting('pro_price', '198000'),
                'premium_price' => Site::setting('premium_price', '448000'),
                'fee_gift'      => Site::setting('fee_gift', '4'),
                'email'         => Site::setting('email', ''),
                'whatsapp'      => Site::setting('whatsapp', ''),
            ],
            'verify' => ['on' => Verify::enabled(), 'since' => Site::setting('email_verify_since', '')],
            'demo'   => ['present' => Demo::present(), 'counts' => Demo::counts(), 'logins' => Demo::logins(), 'password' => Demo::PASSWORD],
        ]);
    }

    /** Adding or removing the demo is the owner's call alone. A staff account can look, not change. */
    private function ownerOnly(): void
    {
        $this->gate();
        if (!Auth::is('owner')) {
            Guard::flash('bad', 'Only the owner can add or remove the demo content.');
            header('Location: ' . App::url('/hq/settings'));
            exit;
        }
    }

    public function demoAdd(): void
    {
        $this->ownerOnly();
        Guard::must();
        try {
            Demo::install();
            Guard::flash('ok', 'Demo content added. The demo logins are listed below.');
        } catch (Throwable $e) {
            Guard::flash('bad', 'The demo content was not added: ' . $e->getMessage());
        }
        header('Location: ' . App::url('/hq/settings'));
        exit;
    }

    /** Put a real couple's page from the old site back, if it is not here already. */
    public function packAdd(string $key): void
    {
        $this->ownerOnly();
        Guard::must();
        try {
            $r = Pack::install($key);
            Guard::flash('ok', $r === 'added'
                ? (Pack::ALL[$key] ?? 'The') . "'s page is back, with their account. Their old password still works."
                : (Pack::ALL[$key] ?? 'That') . "'s page is already on this site, so nothing was changed.");
        } catch (Throwable $e) {
            Guard::flash('bad', 'The page was not added: ' . $e->getMessage());
        }
        header('Location: ' . App::url('/hq/settings'));
        exit;
    }

    public function demoRemove(): void
    {
        $this->ownerOnly();
        Guard::must();
        $gone = Demo::remove();
        Guard::flash('ok', $gone
            ? sprintf('Demo content removed: %d wedding pages, %d accounts, %d vendors, %d listings and %d community posts. Nothing else was touched.',
                $gone['weddings'] ?? 0, $gone['users'] ?? 0, $gone['vendors'] ?? 0, $gone['listings'] ?? 0, $gone['talks'] ?? 0)
            : 'There was no demo content to remove.');
        header('Location: ' . App::url('/hq/settings'));
        exit;
    }

    /**
     * Sends the signed-in admin a test message. Switching email confirmation on when the host cannot
     * actually deliver mail would leave new sign-ups waiting for a link that never comes, so this is
     * how an admin proves it works first.
     */
    public function testMail(): void
    {
        $this->gate();
        Guard::must();
        $me = Auth::user();
        $ok = Mailer::send(
            (string) $me['email'],
            'A test email from your site',
            "If you can read this, your site can send email.\n\nIt is safe to switch on email confirmation.\n"
        );
        Guard::flash(
            $ok ? 'ok' : 'bad',
            $ok
                ? 'Handed to the mail server for ' . $me['email'] . '. Check the inbox and the spam folder — if it arrives, sending works.'
                : 'The server refused to send. Leave email confirmation off until that is sorted; ask your host about PHP mail().'
        );
        header('Location: ' . App::url('/hq/settings'));
        exit;
    }
}
