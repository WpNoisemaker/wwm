<?php
declare(strict_types=1);

/**
 * Bachs (bachs.io): each couple gets their own Nigerian bank account number, through Bachs Connect
 * (one connected account per couple, with a fixed NGN virtual account on it). Guests send gifts and
 * spray by bank transfer; Bachs tells us about every transfer with a signed collection.succeeded
 * webhook, and Transfers matches it to the guest who is waiting on the page.
 *
 * Built from Bachs' API as its SDKs use it: base https://api.bachs.io (sandbox-api for sk_sandbox_
 * keys), Authorization: Bearer <secret key>, POST /v1/accounts, POST /v1/virtual-accounts with the
 * connected account in a header. Two details differ between the SDKs and are settings here: the
 * connected-account header name, and the signature header format (both formats are accepted).
 */
final class Bachs
{
    public static function key(): string
    {
        return trim(Site::setting('bachs_key', ''));
    }

    public static function on(): bool
    {
        return (bool) preg_match('/^sk_(sandbox|live)_/', self::key());
    }

    public static function base(): string
    {
        // A different address for testing (set in the database only; HQ never shows it).
        if (($o = Site::setting('bachs_base', '')) !== '') {
            return rtrim($o, '/');
        }
        return str_starts_with(self::key(), 'sk_live_') ? 'https://api.bachs.io' : 'https://sandbox-api.bachs.io';
    }

    /** @return array{0:int,1:array} HTTP status and the decoded body. */
    public static function call(string $method, string $path, ?array $body = null, string $account = ''): array
    {
        if (!self::on() || !function_exists('curl_init')) {
            return [0, ['message' => 'Bachs is not connected.']];
        }
        $h = ['Authorization: Bearer ' . self::key(), 'Accept: application/json', 'Content-Type: application/json'];
        if ($method === 'POST') {
            $h[] = 'Idempotency-Key: ' . bin2hex(random_bytes(16));
        }
        if ($account !== '') {
            $h[] = Site::setting('bachs_acct_header', 'X-Connected-Account-ID') . ': ' . $account;
        }
        $ch = curl_init(self::base() . $path);
        curl_setopt_array($ch, [CURLOPT_CUSTOMREQUEST => $method, CURLOPT_HTTPHEADER => $h, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 25,
                                CURLOPT_POSTFIELDS => $body !== null ? json_encode($body) : null]);
        $raw = (string) curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        $j = json_decode($raw, true);
        return [$code, is_array($j) ? $j : ['message' => $err !== '' ? $err : mb_substr($raw, 0, 300)]];
    }

    private static function msg(array $j): string
    {
        $m = $j['message'] ?? ($j['error']['message'] ?? ($j['error'] ?? 'Bachs did not say why.'));
        return is_string($m) ? $m : json_encode($m);
    }

    /**
     * Give a couple their account number: a connected account for them, then an NGN virtual account
     * on it. Returns '' when they have one, or what went wrong.
     */
    public static function assign(array $w): string
    {
        if ((string) ($w['va_number'] ?? '') !== '') {
            return '';
        }
        if (!self::on()) {
            return 'Bachs is not connected yet. Add the secret key in HQ → Settings.';
        }
        $acc = (string) ($w['va_account'] ?? '');
        if ($acc === '') {
            $owner = Db::one('SELECT email, name FROM users WHERE id = ?', [$w['owner_id']]);
            [$code, $j] = self::call('POST', '/v1/accounts', [
                'contact_email' => (string) ($owner['email'] ?? ''),
                'display_name'  => Wedding::names($w) . ' wedding',
                'country'       => 'NG',
                'entity_type'   => 'individual',
            ]);
            $acc = (string) ($j['id'] ?? ($j['data']['id'] ?? ''));
            if ($code >= 300 || $acc === '') {
                Db::update('weddings', ['va_status' => 'failed', 'va_error' => mb_substr(self::msg($j), 0, 250)], 'id = ?', [$w['id']]);
                return 'Bachs could not open an account: ' . self::msg($j);
            }
            Db::update('weddings', ['va_account' => $acc], 'id = ?', [$w['id']]);
        }
        [$code, $j] = self::call('POST', '/v1/virtual-accounts', ['currency' => 'NGN'], $acc);
        $va = $j['data'] ?? $j;
        if ($code >= 300 || empty($va['account_number'])) {
            Db::update('weddings', ['va_status' => 'pending', 'va_error' => mb_substr(self::msg($j), 0, 250)], 'id = ?', [$w['id']]);
            return 'Bachs has not given an account number yet: ' . self::msg($j);
        }
        Db::update('weddings', [
            'va_number' => (string) $va['account_number'], 'va_bank' => (string) ($va['bank_name'] ?? ''),
            'va_name' => (string) ($va['account_name'] ?? Wedding::names($w)), 'va_status' => 'active', 'va_error' => '',
        ], 'id = ?', [$w['id']]);
        return '';
    }

    /** @return array<int,array{name:string,code:string}> Nigerian banks, from Bachs, kept for a day. */
    public static function banks(): array
    {
        $cached = json_decode(Site::setting('bachs_banks', ''), true);
        if (is_array($cached) && ($cached['at'] ?? 0) > time() - 86400 && !empty($cached['banks'])) {
            return $cached['banks'];
        }
        $banks = [];
        [$code, $j] = self::call('GET', '/v1/reference/banks?country=NG');
        foreach ((array) ($j['banks'] ?? ($j['data']['banks'] ?? [])) as $b) {
            if (!empty($b['code']) && !empty($b['name'])) { $banks[] = ['name' => (string) $b['name'], 'code' => (string) $b['code']]; }
        }
        if (!$banks) {
            [$code, $j] = self::call('GET', '/v1/payouts/banks?country_code=NG');
            foreach ((array) ($j['data'] ?? []) as $b) {
                if (!empty($b['code']) && !empty($b['name'])) { $banks[] = ['name' => (string) $b['name'], 'code' => (string) $b['code']]; }
            }
        }
        if ($banks) {
            usort($banks, static fn ($a, $b) => strcasecmp($a['name'], $b['name']));
            Db::run('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)', ['bachs_banks', json_encode(['at' => time(), 'banks' => $banks])]);
        }
        return $banks;
    }

    /** The name on a bank account, or '' when the bank does not know it. */
    public static function resolve(string $bankCode, string $number): string
    {
        [$code, $j] = self::call('POST', '/v1/misc/bank-accounts/resolve', ['bank_code' => $bankCode, 'account_number' => $number]);
        if (!empty($j['resolved']) && !empty($j['account_name'])) {
            return (string) $j['account_name'];
        }
        [$code, $j] = self::call('POST', '/v1/payouts/resolve-account', ['bank_code' => $bankCode, 'account_number' => $number]);
        return (string) ($j['data']['account_name'] ?? '');
    }

    /** Save the couple's own bank account on their Bachs account, as where their money goes. Returns the destination id. */
    public static function destination(array $w, string $bankCode, string $bankName, string $number, string $name, string &$err): string
    {
        // Always on the couple's own Bachs account, never the platform's.
        if ((string) ($w['va_account'] ?? '') === '') {
            $err = 'This couple has no Bachs account yet.';
            return '';
        }
        [$code, $j] = self::call('POST', '/v1/payouts/destinations', [
            'type' => 'bank_account', 'currency' => 'NGN', 'name' => mb_substr($name, 0, 80),
            'account_number' => $number, 'account_name' => $name, 'bank_code' => $bankCode, 'bank_name' => $bankName,
        ], (string) $w['va_account']);
        $id = (string) ($j['id'] ?? ($j['data']['id'] ?? ''));
        if ($code >= 300 || $id === '') {
            $err = self::msg($j);
            return '';
        }
        return $id;
    }

    /**
     * Pay money out of the couple's Bachs account to their own bank, now. Returns the withdrawal id,
     * or '' with $err. Tries the withdrawals endpoint first, then the plain payouts one.
     */
    public static function withdraw(array $w, int $kobo, string $reference, string $email, string &$err): string
    {
        // Paid out of the couple's own Bachs account only. Without one, nothing is sent: it would
        // otherwise come out of the platform's balance.
        if ((string) ($w['va_account'] ?? '') === '' || (string) ($w['payout_dest'] ?? '') === '') {
            $err = 'There is no couple account or bank account to pay into.';
            return '';
        }
        $amount = number_format($kobo / 100, 2, '.', '');
        [$code, $j] = self::call('POST', '/v1/payouts/withdrawals', [
            'from_currency' => 'NGN', 'to_currency' => 'NGN', 'amount' => $amount, 'payment_method' => 'BANK_TRANSFER',
            'reference' => $reference, 'email' => $email, 'payout_destination_id' => (string) $w['payout_dest'],
            'description' => 'Wedding gifts: ' . Wedding::names($w), 'metadata' => ['wedding_id' => (int) $w['id']],
        ], (string) $w['va_account']);
        if ($code === 404) {
            [$code, $j] = self::call('POST', '/v1/payouts', ['destination_id' => (string) $w['payout_dest'], 'amount' => $amount, 'currency' => 'NGN', 'reference' => $reference], (string) $w['va_account']);
        }
        $id = (string) ($j['withdrawal_id'] ?? ($j['id'] ?? ($j['data']['withdrawal_id'] ?? ($j['data']['id'] ?? ''))));
        if ($code >= 300 || $id === '') {
            $err = self::msg($j);
            return '';
        }
        return $id;
    }

    /**
     * Is this webhook really from Bachs? HMAC-SHA256 of "{timestamp}.{raw body}" with the signing
     * secret, sent either as X-Bachs-Signature-V2: t=…,v1=… or as X-Bachs-Signature with
     * X-Bachs-Timestamp. Five minutes either way.
     */
    public static function verify(string $raw): bool
    {
        $secret = trim(Site::setting('bachs_whsec', ''));
        if ($secret === '') {
            return false;
        }
        $sigs = [];
        $ts = 0;
        $v2 = (string) ($_SERVER['HTTP_X_BACHS_SIGNATURE_V2'] ?? '');
        if ($v2 !== '') {
            foreach (explode(',', $v2) as $part) {
                [$k, $v] = array_pad(explode('=', trim($part), 2), 2, '');
                if ($k === 't') { $ts = (int) $v; }
                if ($k === 'v1') { $sigs[] = strtolower($v); }
            }
        } else {
            $ts = (int) ($_SERVER['HTTP_X_BACHS_TIMESTAMP'] ?? 0);
            $sigs[] = strtolower((string) ($_SERVER['HTTP_X_BACHS_SIGNATURE'] ?? ''));
        }
        if ($ts === 0 || abs(time() - $ts) > 300) {
            return false;
        }
        $want = hash_hmac('sha256', $ts . '.' . $raw, $secret);
        foreach ($sigs as $s) {
            if ($s !== '' && hash_equals($want, $s)) {
                return true;
            }
        }
        return false;
    }

    /** POST /webhooks/bachs */
    public static function webhook(): void
    {
        $raw = (string) file_get_contents('php://input');
        if (!self::verify($raw)) {
            http_response_code(401);
            exit('bad signature');
        }
        $ev = json_decode($raw, true);
        $id = (string) ($ev['id'] ?? '');
        Db::run("CREATE TABLE IF NOT EXISTS bachs_events (id VARCHAR(80) NOT NULL, type VARCHAR(60) NOT NULL DEFAULT '', received_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        // Bachs may send an event more than once; each is handled once.
        if ($id === '' || Db::val('SELECT 1 FROM bachs_events WHERE id = ?', [$id])) {
            http_response_code(200);
            exit('ok');
        }
        Db::run('INSERT INTO bachs_events (id, type) VALUES (?, ?)', [$id, (string) ($ev['type'] ?? '')]);
        if (($ev['type'] ?? '') === 'collection.succeeded') {
            $d = (array) ($ev['data'] ?? []);
            $bt = (array) ($d['payment_method_details']['bank_transfer'] ?? []);
            $number = (string) ($bt['virtual_account']['account_number'] ?? '');
            $acc = (string) ($ev['account'] ?? '');
            $w = $number !== '' ? Db::one('SELECT * FROM weddings WHERE va_number = ?', [$number]) : null;
            if (!$w && $acc !== '') {
                $w = Db::one('SELECT * FROM weddings WHERE va_account = ?', [$acc]);
            }
            $amount = (string) ($d['received_amount'] ?? ($d['amount'] ?? '0'));
            if ($w) {
                Transfers::receive($w, (int) round((float) $amount * 100), (string) ($bt['sender_name'] ?? ''), (string) ($bt['narration'] ?? ''),
                    (string) ($bt['session_id'] ?? ($d['reference'] ?? $id)));
                // Straight on to the couple's own bank account.
                Settle::run(20);
            } else {
                error_log('[wwm] bachs: transfer to an account we do not know: ' . $number);
            }
        }
        if (in_array($ev['type'] ?? '', ['payout.paid', 'payout.failed'], true)) {
            $d = (array) ($ev['data'] ?? []);
            Settle::update((string) ($d['withdrawal_id'] ?? ($d['id'] ?? '')), ($ev['type'] ?? '') === 'payout.paid', (string) ($d['rejection_reason'] ?? ($d['reason'] ?? '')));
        }
        http_response_code(200);
        echo 'ok';
    }
}
