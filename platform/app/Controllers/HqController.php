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
        View::render('hq/' . $tpl, $d + ['rail' => true, 'css' => ['account'], 'tab' => 'account']);
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
        ]);
    }
}
