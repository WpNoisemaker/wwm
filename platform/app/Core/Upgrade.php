<?php
declare(strict_types=1);

/**
 * Changes to content that a site already installed should also get, applied once each, the first
 * time any page loads after an update. Each step only touches what it names and checks before it
 * writes, so running one twice, or on a site without that content, changes nothing.
 */
final class Upgrade
{
    private const STEPS = ['couple-photos-1', 'ibrahim-ummi-photos-1', 'wall-voice-squad-1', 'squad-profiles-1'];

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
                    'wall-voice-squad-1'   => self::wallVoiceSquad(),
                    'squad-profiles-1'     => self::squadProfiles(),
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

    /**
     * Voice notes on the wall (and no photos there: photos have their own place), and the squad's
     * profiles: what they do, a hobby, their relationship status and one social link.
     */
    private static function wallVoiceSquad(): void
    {
        if (!Db::val("SHOW COLUMNS FROM wall LIKE 'audio'")) {
            Db::run("ALTER TABLE wall MODIFY kind ENUM('note','photo','voice') NOT NULL DEFAULT 'note',
                     ADD COLUMN audio VARCHAR(255) NOT NULL DEFAULT '' AFTER image");
        }
        if (!Db::val("SHOW COLUMNS FROM squad LIKE 'social'")) {
            Db::run("ALTER TABLE squad
                     ADD COLUMN work VARCHAR(160) NOT NULL DEFAULT '' AFTER words,
                     ADD COLUMN hobby VARCHAR(160) NOT NULL DEFAULT '' AFTER work,
                     ADD COLUMN relationship VARCHAR(40) NOT NULL DEFAULT '' AFTER hobby,
                     ADD COLUMN social VARCHAR(255) NOT NULL DEFAULT '' AFTER relationship");
            // Their old Instagram handle becomes the one social link.
            Db::run("UPDATE squad SET social = CONCAT('https://instagram.com/', TRIM(LEADING '@' FROM instagram)) WHERE instagram <> '' AND social = ''");
        }
    }

    /** Squad lines written as “Nick” · Work · Hobbies become the card's own fields, where nothing is filled in yet. */
    private static function squadProfiles(): void
    {
        foreach (Db::all("SELECT id, words FROM squad WHERE work = '' AND hobby = '' AND words LIKE '% · %'") as $r) {
            Db::update('squad', Pack::squadFacts((string) $r['words']), 'id = ?', [$r['id']]);
        }
        if (Db::val("SHOW TABLES LIKE 'demo_rows'")) {
            foreach (Db::all("SELECT s.id FROM squad s JOIN demo_rows d ON d.tbl = 'squad' AND d.row_id = s.id WHERE s.work = '' ORDER BY s.id") as $i => $r) {
                Db::update('squad', Demo::squadProfile($i), 'id = ?', [$r['id']]);
            }
        }
    }
}
