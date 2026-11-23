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
            } else {
                error_log('[wwm] bachs: transfer to an account we do not know: ' . $number);
            }
        }
        http_response_code(200);
        echo 'ok';
    }
}
