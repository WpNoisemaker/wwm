<?php
declare(strict_types=1);

/**
 * Messages between a customer and a seller. The customer starts the chat from a listing or the
 * seller's profile; the seller answers, and sends an invoice (with their payment link, or how to
 * pay them) or just a payment link. Each has its own page at /pay/{code}. We never hold the money.
 */
final class ChatController
{
    public function inbox(): void
    {
        Auth::wall();
        Guard::boot();
        $me = (int) Auth::id();
        View::render('chat/inbox', [
            'title'    => 'Messages',
            'tab'      => 'account',
            'appTitle' => 'Messages',
            'rows'     => Market::threads($me),
            'me'       => $me,
        ]);
    }

    public function thread(string $pid, string $uid): void
    {
        Auth::wall();
        Guard::boot();
        $pid = (int) $pid;
        $uid = (int) $uid;
        $role = Market::role($pid, $uid);
        $seller = Market::seller($pid);
        if ($role === '' || !$seller) {
            self::notFound();
        }
        $url = '/account/messages/' . $pid . '-' . $uid;

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            $body = trim((string) ($_POST['body'] ?? ''));
            if ($body === '') {
                self::go($url);
            }
            if (Guard::tooMany('chat', 30, 10)) {
                Guard::flash('bad', 'Slow down a little, then send it again.');
                self::go($url);
            }
            if ($role === 'user' && !Market::active($seller)) {
                Guard::flash('bad', 'This seller is not taking messages right now.');
                self::go($url);
            }
            Market::send($pid, $uid, $role, $body);
            if (self::wantsJson()) {
                self::json(['ok' => true, 'html' => self::bubbles(Market::thread($pid, $uid, (int) ($_POST['after'] ?? 0)), $role)]);
            }
            self::go($url . '#end');
        }

        // The page asks every few seconds for anything new.
        if (isset($_GET['after'])) {
            $rows = Market::thread($pid, $uid, (int) $_GET['after']);
            Market::markRead($pid, $uid, $role);
            self::json(['html' => self::bubbles($rows, $role), 'last' => $rows ? (int) end($rows)['id'] : (int) $_GET['after']]);
        }

        Market::markRead($pid, $uid, $role);
        $customer = Db::one('SELECT id, name, email FROM users WHERE id = ?', [$uid]);
        View::render('chat/thread', [
            'title'    => $role === 'user' ? (string) $seller['name'] : (string) ($customer['name'] ?? 'Customer'),
            'tab'      => 'account',
            'appTitle' => $role === 'user' ? (string) $seller['name'] : (string) ($customer['name'] ?? 'Customer'),
            'back'     => App::url('/account/messages'),
            'rows'     => Market::thread($pid, $uid),
            'role'     => $role,
            'seller'   => $seller,
            'customer' => $customer,
            'pid'      => $pid,
            'uid'      => $uid,
            'listings' => Listing::bySeller($pid, 30),
        ]);
    }

    /** The seller sends an invoice or a payment link into the chat. */
    public function invoice(string $pid, string $uid): void
    {
        Auth::wall();
        Guard::must();
        $pid = (int) $pid;
        $uid = (int) $uid;
        $role = Market::role($pid, $uid);
        $url = '/account/messages/' . $pid . '-' . $uid;
        if (!in_array($role, ['partner', 'staff'], true)) {
            self::notFound();
        }
        if (!Db::val("SELECT 1 FROM messages WHERE partner_id = ? AND user_id = ? AND sender = 'user' LIMIT 1", [$pid, $uid])) {
            Guard::flash('bad', 'You can send an invoice once the customer has written to you.');
            self::go($url);
        }
        $kind = ($_POST['kind'] ?? '') === 'link' ? 'link' : 'invoice';
        $cur = isset(Fx::DEFAULT[strtoupper((string) ($_POST['currency'] ?? ''))]) ? strtoupper((string) $_POST['currency']) : 'NGN';
        $num = static fn ($v): int => max(0, (int) preg_replace('/[^\d]/', '', (string) $v));
        $lines = [];
        foreach ((array) ($_POST['what'] ?? []) as $i => $what) {
            $what = mb_substr(trim((string) $what), 0, 160);
            $amt = $num($_POST['amt'][$i] ?? 0);
            if ($what !== '' || $amt > 0) {
                $lines[] = [$what !== '' ? $what : 'Item', $amt];
            }
        }
        $amount = $kind === 'link' ? $num($_POST['amount'] ?? 0) : array_sum(array_column($lines, 1));
        $link = Market::payLink((string) ($_POST['pay_link'] ?? ''));
        $info = mb_substr(trim((string) ($_POST['pay_info'] ?? '')), 0, 1000);
        $title = mb_substr(trim((string) ($_POST['title'] ?? '')), 0, 190);
        $bad = [];
        if ($amount <= 0) { $bad[] = 'an amount'; }
        if ($kind === 'link' && $link === '') { $bad[] = 'your payment link (it starts with https://)'; }
        if ($kind === 'invoice' && $link === '' && $info === '') { $bad[] = 'a way to pay you (a payment link or your bank details)'; }
        if ($kind === 'invoice' && !$lines) { $bad[] = 'at least one line item'; }
        if ($bad) {
            Guard::flash('bad', 'The ' . ($kind === 'link' ? 'payment link' : 'invoice') . ' needs ' . implode(' and ', $bad) . '.');
            self::go($url);
        }
        $listing = (int) ($_POST['listing_id'] ?? 0);
        $listing = $listing && Db::val('SELECT 1 FROM listings WHERE id = ? AND partner_id = ?', [$listing, $pid]) ? $listing : null;
        $due = (string) ($_POST['due_on'] ?? '');
        $code = Market::newCode();
        $id = (int) Db::insert('invoices', [
            'code' => $code, 'kind' => $kind, 'partner_id' => $pid, 'user_id' => $uid, 'listing_id' => $listing,
            'title' => $title !== '' ? $title : ($kind === 'link' ? 'Payment' : 'Invoice'), 'lines_json' => json_encode($lines, JSON_UNESCAPED_UNICODE),
            'amount' => $amount, 'currency' => $cur, 'pay_link' => $link, 'pay_info' => $info,
            'due_on' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $due) ? $due : null, 'note' => mb_substr(trim((string) ($_POST['note'] ?? '')), 0, 1000),
        ]);
        $what = ($kind === 'link' ? 'Payment link' : 'Invoice') . ' #' . $code . ': ' . ($title !== '' ? $title . ', ' : '') . trim(cur_sign($cur)) . number_format($amount);
        Market::send($pid, $uid, $role, $what, $listing, $id);
        Guard::flash('ok', ($kind === 'link' ? 'Payment link' : 'Invoice') . ' sent.');
        self::go($url . '#end');
    }

    /** An invoice or payment link's own page; once paid, the receipt. */
    public function pay(string $code): void
    {
        Auth::wall();
        Guard::boot();
        $inv = Market::invoice($code);
        if (!$inv) {
            self::notFound();
        }
        $role = Market::role((int) $inv['partner_id'], (int) $inv['user_id']);
        if ($role === '') {
            self::notFound();
        }
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            Guard::must();
            $do = (string) ($_POST['do'] ?? '');
            if (in_array($role, ['partner', 'staff'], true) && (string) $inv['status'] === 'open' && in_array($do, ['paid', 'cancel'], true)) {
                Db::update('invoices', ['status' => $do === 'paid' ? 'paid' : 'cancelled', 'paid_at' => $do === 'paid' ? date('Y-m-d H:i:s') : null], 'id = ?', [$inv['id']]);
                Market::send((int) $inv['partner_id'], (int) $inv['user_id'], $role, ($do === 'paid' ? 'Marked as paid: ' : 'Cancelled: ') . ($inv['kind'] === 'link' ? 'payment link' : 'invoice') . ' #' . $inv['code'] . '. ' . ($do === 'paid' ? 'Thank you!' : ''), null, (int) $inv['id']);
                Guard::flash('ok', $do === 'paid' ? 'Marked as paid. It is now a receipt.' : 'Cancelled.');
            }
            self::go('/pay/' . $inv['code']);
        }
        $seller = Market::seller((int) $inv['partner_id']);
        View::render('chat/pay', [
            'title'    => ((string) $inv['status'] === 'paid' ? 'Receipt' : ($inv['kind'] === 'link' ? 'Payment link' : 'Invoice')) . ' #' . $inv['code'],
            'tab'      => 'account',
            'appTitle' => (string) $inv['status'] === 'paid' ? 'Receipt' : 'Invoice',
            'back'     => App::url('/account/messages/' . $inv['partner_id'] . '-' . $inv['user_id']),
            'inv'      => $inv,
            'role'     => $role,
            'seller'   => $seller,
            'customer' => Db::one('SELECT name, email FROM users WHERE id = ?', [$inv['user_id']]),
            'listing'  => $inv['listing_id'] ? Db::one('SELECT title, slug FROM listings WHERE id = ?', [$inv['listing_id']]) : null,
        ]);
    }

    /**
     * A customer writes to a seller, from a listing or the seller's profile. Only signed-in customers
     * can: the account is how a seller knows who they are talking to, and how a report counts.
     */
    public static function start(int $partnerId, ?int $listingId, string $back): never
    {
        Auth::wall();
        Guard::must();
        $me = (int) Auth::id();
        $seller = Market::seller($partnerId);
        if (!Market::active($seller) || $me === $partnerId) {
            self::go($back);
        }
        $body = trim((string) ($_POST['body'] ?? ''));
        if ($body === '') {
            Guard::flash('bad', 'Write your message first.');
            self::go($back);
        }
        if (Guard::tooMany('chat', 30, 10)) {
            Guard::flash('bad', 'Slow down a little, then send it again.');
            self::go($back);
        }
        Market::send($partnerId, $me, 'user', $body, $listingId);
        Guard::flash('ok', 'Sent. ' . $seller['name'] . ' will answer here, and we will email you when they do.');
        self::go('/account/messages/' . $partnerId . '-' . $me . '#end');
    }

    /** Messages as chat bubbles, for the page and for the bits the page fetches. */
    public static function bubbles(array $rows, string $role): string
    {
        $out = '';
        foreach ($rows as $m) {
            $mine = (string) $m['sender'] === $role || ($role === 'staff' && (string) $m['sender'] === 'staff');
            $out .= View::partial('chat/bubble', ['m' => $m, 'mine' => $mine]);
        }
        return $out;
    }

    private static function wantsJson(): bool
    {
        return str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');
    }

    private static function json(array $data): never
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private static function go(string $p): never
    {
        header('Location: ' . App::url($p));
        exit;
    }

    private static function notFound(): never
    {
        http_response_code(404);
        View::render('errors/404', ['title' => 'Not found']);
        exit;
    }
}
