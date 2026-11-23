<?php
declare(strict_types=1);

/**
 * Gifts and spraying by bank transfer. A guest says how much and under what name; we show the
 * couple's account number and an exact amount to send. The amount carries a few kobo nobody else
 * waiting is using (₦5,000.37), and a short code for the transfer description, so when Bachs reports
 * the transfer we know whose it is. The guest's page is waiting and shows it the moment it lands.
 */
final class Transfers
{
    public const WAIT_MINUTES = 60;

    public static function table(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        Db::run("CREATE TABLE IF NOT EXISTS transfer_intents (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT, code VARCHAR(12) NOT NULL, wedding_id INT UNSIGNED NOT NULL,
            kind ENUM('spray','gift') NOT NULL DEFAULT 'spray', gift_id INT UNSIGNED NULL, name VARCHAR(160) NOT NULL DEFAULT '',
            message VARCHAR(500) NOT NULL DEFAULT '', amount_kobo BIGINT NOT NULL, status ENUM('pending','paid','expired') NOT NULL DEFAULT 'pending',
            payment_id INT UNSIGNED NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, paid_at DATETIME NULL,
            PRIMARY KEY (id), UNIQUE KEY uq_code (code), KEY ix_wait (wedding_id, status, amount_kobo)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    /** Transfers are in naira. A page priced in another currency converts at HQ's rate. */
    public static function toNaira(int $amount, string $cur): int
    {
        return $cur === 'NGN' ? $amount : (int) round($amount * (Fx::rates()[$cur] ?? 1));
    }

    /** A guest about to transfer: the exact amount (with its own kobo) and a code. */
    public static function start(array $w, string $kind, ?int $giftId, string $name, string $message, int $naira): array
    {
        self::table();
        Db::run("UPDATE transfer_intents SET status = 'expired' WHERE status = 'pending' AND created_at < NOW() - INTERVAL ? MINUTE", [self::WAIT_MINUTES * 3]);
        $taken = array_map('intval', array_column(Db::all("SELECT amount_kobo FROM transfer_intents WHERE wedding_id = ? AND status = 'pending' AND amount_kobo DIV 100 = ?", [$w['id'], $naira]), 'amount_kobo'));
        $kobo = 0;
        for ($try = 0; $try < 200; $try++) {
            $k = $naira * 100 + mt_rand(11, 99);
            if (!in_array($k, $taken, true)) {
                $kobo = $k;
                break;
            }
        }
        if ($kobo === 0) {
            $kobo = $naira * 100 + 1;
        }
        do {
            $code = 'W' . strtoupper(substr(base_convert(bin2hex(random_bytes(4)), 16, 36), 0, 5));
        } while (Db::val('SELECT 1 FROM transfer_intents WHERE code = ?', [$code]));
        Db::insert('transfer_intents', [
            'code' => $code, 'wedding_id' => $w['id'], 'kind' => $kind, 'gift_id' => $giftId,
            'name' => mb_substr($name, 0, 160), 'message' => mb_substr($message, 0, 500), 'amount_kobo' => $kobo,
        ]);
        return Db::one('SELECT * FROM transfer_intents WHERE code = ?', [$code]);
    }

    public static function get(string $code): ?array
    {
        self::table();
        return Db::one('SELECT * FROM transfer_intents WHERE code = ?', [strtoupper($code)]);
    }

    /**
     * A transfer landed in a couple's account. Whose is it? The code in the description first, then
     * the exact amount with its kobo, then the same naira amount from someone waiting with a similar
     * name. A transfer nobody was waiting for still counts, as a cash gift from the sender.
     */
    public static function receive(array $w, int $kobo, string $sender, string $narration, string $ref): ?array
    {
        self::table();
        if ($ref !== '' && Db::val("SELECT 1 FROM payments WHERE gateway = 'bachs' AND gateway_ref = ?", [$ref])) {
            return null;   // already counted
        }
        $wait = "wedding_id = ? AND status = 'pending' AND created_at > NOW() - INTERVAL " . (self::WAIT_MINUTES * 3) . ' MINUTE';
        $intent = null;
        if (preg_match('/\bW[0-9A-Z]{5}\b/', strtoupper($narration), $m)) {
            $intent = Db::one("SELECT * FROM transfer_intents WHERE code = ? AND wedding_id = ? AND status = 'pending'", [$m[0], $w['id']]);
        }
        $intent = $intent ?: Db::one("SELECT * FROM transfer_intents WHERE $wait AND amount_kobo = ? ORDER BY id LIMIT 1", [$w['id'], $kobo]);
        if (!$intent && $sender !== '') {
            foreach (Db::all("SELECT * FROM transfer_intents WHERE $wait AND amount_kobo DIV 100 = ? ORDER BY id", [$w['id'], intdiv($kobo, 100)]) as $c) {
                similar_text(strtolower($c['name']), strtolower($sender), $pct);
                if ($pct >= 55) {
                    $intent = $c;
                    break;
                }
            }
        }
        $kind = $intent ? (string) $intent['kind'] : 'gift';
        $naira = intdiv($kobo, 100);
        $cur = (string) $w['currency'];
        $amount = $cur === 'NGN' ? $naira : (int) round($naira / (Fx::rates()[$cur] ?? 1));
        $plan = (string) $w['plan'];
        $fee = in_array($plan, ['premium', 'society'], true) ? 0 : (int) round($amount * ((float) Site::setting('fee_gift', '4')) / 100);
        $pid = (int) Db::insert('payments', [
            'ref' => 'BX-' . strtoupper(bin2hex(random_bytes(5))), 'purpose' => $kind === 'spray' ? 'spray' : 'gift',
            'wedding_id' => $w['id'], 'gift_id' => $intent['gift_id'] ?? null,
            'payer_name' => $intent ? (string) $intent['name'] : ($sender !== '' ? ucwords(strtolower($sender)) : 'A guest'),
            'message' => $intent ? (string) $intent['message'] : '', 'amount' => $amount, 'fee' => $fee, 'currency' => $cur,
            'gateway' => 'bachs', 'gateway_ref' => $ref !== '' ? $ref : 'bachs-' . bin2hex(random_bytes(6)),
            'status' => 'paid', 'paid_at' => date('Y-m-d H:i:s'),
        ]);
        if (!empty($intent['gift_id'])) {
            Db::run('UPDATE gifts SET raised = raised + ? WHERE id = ? AND wedding_id = ?', [$amount, $intent['gift_id'], $w['id']]);
        }
        if ($intent) {
            Db::update('transfer_intents', ['status' => 'paid', 'payment_id' => $pid, 'paid_at' => date('Y-m-d H:i:s')], 'id = ?', [$intent['id']]);
        }
        Buzz::add($w, $kind === 'spray' ? 'spray' : 'gift');
        return $intent;
    }
}
