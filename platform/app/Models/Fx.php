<?php
declare(strict_types=1);

/**
 * Prices in the visitor's own money. Plans are priced in naira; every other currency is shown as a
 * guide at the rate HQ sets, so a couple in London sees pounds and one in Nairobi sees shillings.
 */
final class Fx
{
    /** Naira for one unit of each currency. HQ can change any of them in Settings. */
    public const DEFAULT = [
        'NGN' => 1, 'GBP' => 1950, 'USD' => 1450, 'EUR' => 1690, 'CAD' => 1040, 'AUD' => 950,
        'NZD' => 840, 'AED' => 395, 'GHS' => 130, 'KES' => 11.2, 'ZAR' => 83, 'RWF' => 1.0,
    ];

    public const NAMES = [
        'NGN' => 'Nigerian naira', 'GBP' => 'British pound', 'USD' => 'US dollar', 'EUR' => 'Euro',
        'CAD' => 'Canadian dollar', 'AUD' => 'Australian dollar', 'NZD' => 'New Zealand dollar',
        'AED' => 'UAE dirham', 'GHS' => 'Ghanaian cedi', 'KES' => 'Kenyan shilling',
        'ZAR' => 'South African rand', 'RWF' => 'Rwandan franc',
    ];

    /** "British pounds", "Nigerian naira": the name in a sentence. */
    public static function many(string $cur): string
    {
        $n = self::NAMES[$cur] ?? $cur;
        return $cur === 'NGN' || $cur === 'EUR' ? $n : $n . 's';
    }

    /** Which currency a visitor's region most likely spends. */
    private const REGION = [
        'NG' => 'NGN', 'GB' => 'GBP', 'UK' => 'GBP', 'US' => 'USD', 'CA' => 'CAD', 'AU' => 'AUD', 'NZ' => 'NZD',
        'AE' => 'AED', 'GH' => 'GHS', 'KE' => 'KES', 'ZA' => 'ZAR', 'RW' => 'RWF',
        'IE' => 'EUR', 'DE' => 'EUR', 'FR' => 'EUR', 'NL' => 'EUR', 'IT' => 'EUR', 'ES' => 'EUR', 'BE' => 'EUR', 'PT' => 'EUR',
    ];

    /** @return array<string,float> */
    public static function rates(): array
    {
        static $r = null;
        if ($r !== null) {
            return $r;
        }
        $r = self::DEFAULT;
        $set = json_decode(Site::setting('fx_rates', '{}'), true);
        foreach (is_array($set) ? $set : [] as $k => $v) {
            if (isset($r[$k]) && $k !== 'NGN' && is_numeric($v) && (float) $v > 0) {
                $r[$k] = (float) $v;
            }
        }
        return $r;
    }

    /** The visitor's choice: ?cur= first, then what they picked before, then a guess from their region. */
    public static function pick(): string
    {
        foreach ([$_GET['cur'] ?? '', $_COOKIE['wwm-cur'] ?? ''] as $c) {
            $c = strtoupper((string) $c);
            if (isset(self::DEFAULT[$c])) {
                return $c;
            }
        }
        $cc = strtoupper((string) ($_SERVER['HTTP_CF_IPCOUNTRY'] ?? ''));
        if (isset(self::REGION[$cc])) {
            return self::REGION[$cc];
        }
        if (preg_match_all('/[a-z]{2,3}-([A-Za-z]{2})\b/', (string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''), $m)) {
            foreach ($m[1] as $cc) {
                if (isset(self::REGION[strtoupper($cc)])) {
                    return self::REGION[strtoupper($cc)];
                }
            }
        }
        return 'NGN';
    }

    /** A converted price kept to three figures, the way a person would quote it: £102, KSh 17,700. */
    public static function amount(int $ngn, string $cur): int
    {
        $v = $ngn / (self::rates()[$cur] ?? 1);
        if ($v <= 0) {
            return 0;
        }
        $step = $v < 1000 ? 1 : 10 ** ((int) floor(log10($v)) - 2);
        return (int) (round($v / $step) * $step);
    }

    /** The price as HTML, marked so the picker can redraw it in another currency. */
    public static function show(int $ngn, string $cur): string
    {
        $n = self::amount($ngn, $cur);
        $guide = $cur !== 'NGN' && $ngn > 0 ? '≈ ' : '';
        return '<span data-ngn="' . $ngn . '">' . ($guide !== '' ? '<span class="fx-guide">' . $guide . '</span>' : '')
            . money(number_format($n), $cur) . '</span>';
    }

    /** What the picker needs in the browser: each currency's rate and sign. */
    public static function forScript(): string
    {
        $out = [];
        foreach (self::rates() as $k => $v) {
            $out[$k] = [$v, cur_sign($k)];
        }
        return (string) json_encode($out, JSON_UNESCAPED_UNICODE);
    }
}
