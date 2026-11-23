<?php
declare(strict_types=1);

/**
 * Buzz coins: how a wedding page climbs the Buzz Chart.
 *
 * Every coin comes from something a real guest did on the page. The same action is worth more on
 * a higher plan, and some actions only exist on higher plans (a Free page has no gallery, so it
 * cannot earn from guest photos). This table is the only place the numbers live: the Buzz Chart
 * page prints it, and every action that earns coins reads it.
 */
final class Buzz
{
    /** Plan order for the columns: Free, Pro, Premium, High Society. */
    public const PLANS = ['free', 'pro', 'premium', 'society'];

    /** action => [what the guest did, [Free, Pro, Premium, High Society]]. 0 = the plan does not have it. */
    public const ACTS = [
        'visit'   => ['Opens the page',                 [1, 2, 3, 5]],
        'rsvp'    => ['Sends an RSVP',                  [5, 10, 15, 20]],
        'note'    => ['Leaves a wish on the wall',      [2, 5, 8, 12]],
        'share'   => ['Shares the page',                [1, 3, 5, 8]],
        'gift'    => ['Gives a gift',                   [5, 10, 15, 20]],
        'photo'   => ['Adds a guest photo',             [0, 8, 12, 16]],
        'quiz'    => ['Plays the quiz',                 [0, 4, 6, 8]],
        'checkin' => ['Is checked in at the gate',      [0, 6, 10, 14]],
        'spray'   => ['Sprays the couple',              [0, 0, 15, 25]],
        'live'    => ['Watches the livestream',         [0, 0, 5, 8]],
    ];

    public static function coins(string $act, string $plan): int
    {
        $i = array_search($plan, self::PLANS, true);
        return $i === false || !isset(self::ACTS[$act]) ? 0 : (int) self::ACTS[$act][1][$i];
    }

    /** Give a page the coins an action earns on its plan. */
    public static function add(array $w, string $act): void
    {
        $n = self::coins($act, (string) ($w['plan'] ?? 'free'));
        if ($n > 0) {
            Db::run('UPDATE weddings SET buzz = buzz + ? WHERE id = ?', [$n, (int) $w['id']]);
        }
    }
}
