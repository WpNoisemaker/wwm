<?php
declare(strict_types=1);

/**
 * The wedding page designs. Every one is built from the same design system (the day glass screen
 * and the night control panel): the same Urbanist type, squircle photos, frosted card, round
 * buttons, white pill, mosaic tiles and dot numerals. What changes is the light the page is drawn
 * in, the way the top of the page is composed, and one motif made from those same parts.
 *
 * Free pages are always Classic in black & white. Pro pages are Classic in the couple's colours.
 * Premium and High Society pages pick any design below, or a custom one made for them.
 */
final class Design
{
    /**
     * key => [name, who can use it, one line, theme it is drawn in, ink, hero composition, swatch]
     * theme: 'auto' follows the visitor's day/night; 'day'/'night' are fixed by the design.
     * ink:   'light' (white type on a mid or dark sky) or 'dark' (deep type on a pale, paper sky).
     */
    public const ALL = [
        'classic'  => ['Classic',  'free',    'The reference screen itself: two squircle photos and the frosted RSVP card.', 'auto',  'light', 'trio',     ['#7B8F9B', '#C6D2CC']],
        'royal'    => ['Royal',    'premium', 'Wine and gold, formal capitals, one tall portrait in a gold ring and the date in dots.', 'night', 'light', 'portrait', ['#3A1022', '#C9A24D']],
        'pearl'    => ['Pearl',    'premium', 'Pearl white and champagne, a framed photo and a ring of pearl dots around your initials.', 'day', 'dark', 'framed', ['#F6F1EA', '#E3C9AE']],
        'garden'   => ['Garden',   'premium', 'Sage and blush, a wide photo across the top and pollen dots drifting over it.', 'day', 'dark', 'wide', ['#DCE6D6', '#E8B4A6']],
        'noir'     => ['Noir',     'premium', 'Black tie: a deep page, thin gold tick lines, a tall photo and names in the finest weight.', 'night', 'light', 'split', ['#121114', '#C8A15A']],
        'coral'    => ['Coral',    'premium', 'Terracotta and peach, a round photo with a string of coral beads drawn in dots.', 'day', 'dark', 'circle', ['#F6E2D6', '#B4432D']],
        'indigo'   => ['Indigo',   'premium', 'Adire blues at night, dotted tie-dye rings and the names over a wide photo.', 'night', 'light', 'overlay', ['#16243B', '#8DB2D8']],
        'asooke'   => ['Aso-oke',  'premium', 'Bold capitals, a woven strip of mosaic tiles and solid blocks of colour.', 'day', 'dark', 'blocks', ['#F4ECDF', '#7A2E3B']],
        'keepsake' => ['Keepsake', 'premium', 'Like an album: two prints tilted on the page, held with tape, and small hearts.', 'day', 'dark', 'album', ['#F3EDE3', '#3E5C76']],
        'savannah' => ['Savannah', 'premium', 'The day sky at golden hour: amber into terracotta, a round sun photo and an acacia on the horizon.', 'day', 'light', 'horizon', ['#E8A06A', '#F3CF96']],
        'owambe'   => ['Owambe',   'premium', 'The party: deep emerald, confetti tiles, chunky buttons and everything a little louder.', 'night', 'light', 'party', ['#0B5A47', '#F4B400']],
        'ivory'    => ['Ivory & Gold', 'custom', 'Made for Ibrahim & Ummi: ivory, beige and champagne gold, an invitation card and a diary of their story.', 'day', 'dark', 'invite', ['#FBF7EF', '#C3A46A']],
    ];

    /** Older names the importer and early demo data used. */
    private const ALIAS = ['studio' => 'pearl', 'aso' => 'asooke', 'ivory-gold' => 'ivory'];

    public static function get(string $key): array
    {
        $key = self::ALIAS[$key] ?? $key;
        $row = self::ALL[$key] ?? self::ALL['classic'];
        return ['key' => isset(self::ALL[$key]) ? $key : 'classic', 'name' => $row[0], 'plan' => $row[1], 'line' => $row[2],
                'theme' => $row[3], 'ink' => $row[4], 'hero' => $row[5], 'swatch' => $row[6]];
    }

    /** The design a page actually wears, after its plan has had its say. */
    public static function forWedding(array $w): array
    {
        $plan = (string) ($w['plan'] ?? 'free');
        $want = self::ALIAS[(string) ($w['template'] ?? '')] ?? (string) ($w['template'] ?? '');
        if (Wedding::rank($plan) < Wedding::rank('premium') || !isset(self::ALL[$want])) {
            return self::get('classic');
        }
        if (self::ALL[$want][1] === 'custom') {
            // A custom design belongs to the couple it was made for.
            $d = Wedding::design($w);
            if (($d['custom'] ?? '') !== $want) {
                return self::get('royal');
            }
        }
        return self::get($want);
    }

    /** The designs a couple on this plan can choose in their account. */
    public static function choices(array $w): array
    {
        $out = [];
        foreach (self::ALL as $k => $row) {
            if ($row[1] === 'custom') {
                if ((Wedding::design($w)['custom'] ?? '') === $k) { $out[$k] = $row[0]; }
                continue;
            }
            $out[$k] = $row[0];
        }
        return $out;
    }
}
