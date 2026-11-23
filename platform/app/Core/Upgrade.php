<?php
declare(strict_types=1);

/**
 * Changes to content that a site already installed should also get, applied once each, the first
 * time any page loads after an update. Each step only touches what it names and checks before it
 * writes, so running one twice, or on a site without that content, changes nothing.
 */
final class Upgrade
{
    private const STEPS = ['couple-photos-1', 'ibrahim-ummi-photos-1'];

    public static function run(): void
    {
        $done = array_filter(explode(',', Site::setting('upgrades_done', '')));
        $todo = array_diff(self::STEPS, $done);
        if (!$todo) {
            return;
        }
        foreach ($todo as $step) {
            try {
                match ($step) {
                    'couple-photos-1'      => self::couplePhotos(),
                    'ibrahim-ummi-photos-1' => self::ibrahimUmmi(),
                };
            } catch (Throwable $e) {
                error_log('[wwm] upgrade ' . $step . ': ' . $e->getMessage());
                continue;   // tried again on the next page load
            }
            $done[] = $step;
            Db::run('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)', ['upgrades_done', implode(',', array_unique($done))]);
        }
    }

    /** Every demo wedding shows a couple: the covers from the current demo content. */
    private static function couplePhotos(): void
    {
        if (!Db::val("SHOW TABLES LIKE 'demo_rows'")) {
            return;
        }
        $content = require App::root() . '/db/demo/content.php';
        foreach ($content['weddings'] as $w) {
            $row = Db::one("SELECT w.id, w.design FROM weddings w JOIN demo_rows d ON d.tbl = 'weddings' AND d.row_id = w.id WHERE w.slug = ?", [$w['slug']]);
            if (!$row) {
                continue;   // only the demo's own pages
            }
            $d = json_decode((string) $row['design'], true) ?: [];
            $d['photo2'] = $w['photo2'] ?? '';
            Db::update('weddings', ['cover' => $w['cover'], 'design' => json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)], 'id = ?', [$row['id']]);
        }
    }

    /** Ibrahim & Ummi: their own photos, and the sample music, unless they have changed them. */
    private static function ibrahimUmmi(): void
    {
        $w = Db::one("SELECT id, cover, design FROM weddings WHERE slug = 'ibrahim-ummi'");
        if (!$w) {
            return;
        }
        $d = json_decode((string) $w['design'], true) ?: [];
        $set = [];
        if ((string) $w['cover'] === 'couples/ibrahim-ummi/venue-hall.jpg') {
            $set['cover'] = 'couples/ibrahim-ummi/couple-terrace.jpg';
            $d['photo2'] = 'couples/ibrahim-ummi/couple-together.jpg';
            $d['labels'] = ['Ibrahim & Ummi', 'Bismillah'];
        }
        if (trim((string) ($d['music'] ?? '')) === '') {
            $d += ['music_volume' => 20];
            $d['music'] = 'audio/demo-ivory-morning.mp3';
            $d['music_title'] = 'Ivory Morning (sample music)';
        }
        $set['design'] = json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        Db::update('weddings', $set, 'id = ?', [$w['id']]);
    }
}
