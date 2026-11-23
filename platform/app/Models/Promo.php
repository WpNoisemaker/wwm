<?php
declare(strict_types=1);

/**
 * The site's own ads: Premium, shown in the places a paying advertiser would sit. Free wedding
 * pages, between the cards of the community deck, among the wedding pages and in the blog. Pro and
 * Premium pages carry none, and someone whose own page is already Premium is not shown them.
 * HQ can switch them off.
 */
final class Promo
{
    /** Headline and line, varied from slot to slot so the same words never sit twice on a screen. */
    private const LINES = [
        ['Planning your own wedding?', 'Make it Premium: ten designs, a song playing softly, a livestream and spraying from the seat.'],
        ['The page your guests talk about', 'Premium pages wear their own design, play your song and let guests leave voice notes.'],
        ['Your day, live, for everyone', 'Premium streams the ceremony to family abroad and lets them spray you from wherever they are.'],
        ['Love a page like this?', 'Yours can do more. Premium adds ten designs, background music, livestream and no ads.'],
    ];

    public static function on(): bool
    {
        static $on = null;
        if ($on !== null) {
            return $on;
        }
        if (Site::setting('ads_premium', '1') === '0') {
            return $on = false;
        }
        if (Auth::check()) {
            // Someone already on Premium or High Society has nothing to gain from these.
            $top = Db::val("SELECT 1 FROM weddings WHERE owner_id = ? AND plan IN ('premium','society') LIMIT 1", [Auth::id()]);
            if ($top) {
                return $on = false;
            }
        }
        return $on = true;
    }

    /** The ad for a slot: 'post' (a card in the community deck), 'card' (among wedding cards), 'story' (among blog posts) or 'band' (on a wedding page). */
    public static function html(string $as, int $n = 0): string
    {
        if (!self::on()) {
            return '';
        }
        [$title, $line] = self::LINES[$n % count(self::LINES)];
        return View::partial('partials/promo', [
            'as'    => $as,
            'title' => $title,
            'line'  => $line,
            'price' => Fx::show((int) Site::setting('premium_price', '448000'), Fx::pick()),
            'img'   => self::picture($n),
            'href'  => App::url('/pricing#premium'),
        ]);
    }

    /** A real Premium page's photo, so the ad shows what Premium looks like. */
    private static function picture(int $n): string
    {
        static $pics = null;
        if ($pics === null) {
            $pics = array_column(Db::all("SELECT cover FROM weddings WHERE plan IN ('premium','society') AND visibility = 'public' AND cover <> '' ORDER BY buzz DESC LIMIT 6"), 'cover');
        }
        return $pics ? (string) $pics[$n % count($pics)] : '';
    }
}
