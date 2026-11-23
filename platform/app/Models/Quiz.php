<?php
declare(strict_types=1);

/**
 * "Who knows them best?"
 *
 * Each partner has their own quiz, written by the couple: the things family and friends are meant
 * to know about that person (their nickname at home, the food they always order, where they grew
 * up). A guest picks the partner they know, answers, and lands on that partner's leaderboard.
 * Answers are marked on the server, so the right answers never reach the page.
 */
final class Quiz
{
    public const MAX_QUESTIONS = 8;

    /** Question prompts offered to a couple writing a quiz. {name} becomes the partner's name. */
    public const PROMPTS = [
        "What is {name}'s nickname at home?",
        "What food would {name} order every single time?",
        "Where did {name} grow up?",
        "Which month is {name}'s birthday?",
        "What does {name} do for work?",
        "What is {name} most scared of?",
        "Which football club does {name} support?",
        "What is {name}'s favourite colour?",
    ];

    /** @return array{a:array,b:array} Each partner's questions: [question, [options], index of the right one]. */
    public static function forWedding(array $w): array
    {
        $raw = Wedding::design($w)['quizzes'] ?? [];
        $out = ['a' => [], 'b' => []];
        foreach (['a', 'b'] as $side) {
            foreach ((array) ($raw[$side] ?? []) as $q) {
                if (!is_array($q) || count($q) < 3 || !is_array($q[1])) {
                    continue;
                }
                $opts = array_values(array_filter(array_map(static fn ($o) => trim((string) $o), $q[1]), static fn ($o) => $o !== ''));
                $right = (int) $q[2];
                if (trim((string) $q[0]) !== '' && count($opts) >= 2 && isset($opts[$right])) {
                    $out[$side][] = [trim((string) $q[0]), $opts, $right];
                }
            }
            $out[$side] = array_slice($out[$side], 0, self::MAX_QUESTIONS);
        }
        return $out;
    }

    /** The scores live in their own small table, made the first time it is needed. */
    public static function table(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        Db::run("CREATE TABLE IF NOT EXISTS quiz_scores (
            id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
            wedding_id INT UNSIGNED NOT NULL,
            side       CHAR(1)      NOT NULL,
            name       VARCHAR(80)  NOT NULL DEFAULT '',
            score      TINYINT UNSIGNED NOT NULL DEFAULT 0,
            total      TINYINT UNSIGNED NOT NULL DEFAULT 0,
            created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY board (wedding_id, side, score),
            CONSTRAINT quiz_scores_wedding FOREIGN KEY (wedding_id) REFERENCES weddings (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $done = true;
    }

    /** Best first; on a tie, whoever got there first. @return array<int,array<string,mixed>> */
    public static function board(int $weddingId, string $side, int $n = 10): array
    {
        self::table();
        return Db::all(
            'SELECT id, name, score, total, created_at FROM quiz_scores WHERE wedding_id = ? AND side = ?
             ORDER BY score DESC, created_at ASC, id ASC LIMIT ?',
            [$weddingId, $side, $n]
        );
    }

    public static function players(int $weddingId, string $side): int
    {
        self::table();
        return (int) Db::val('SELECT COUNT(*) FROM quiz_scores WHERE wedding_id = ? AND side = ?', [$weddingId, $side], 0);
    }

    /** Record a score and return the place it earned on that partner's board. */
    public static function save(int $weddingId, string $side, string $name, int $score, int $total, ?string $at = null): int
    {
        self::table();
        $row = ['wedding_id' => $weddingId, 'side' => $side, 'name' => mb_substr($name, 0, 80), 'score' => $score, 'total' => $total];
        if ($at !== null) {
            $row['created_at'] = $at;
        }
        $id = Db::insert('quiz_scores', $row);
        return 1 + (int) Db::val(
            'SELECT COUNT(*) FROM quiz_scores WHERE wedding_id = ? AND side = ? AND (score > ? OR (score = ? AND id < ?))',
            [$weddingId, $side, $score, $score, $id],
            0
        );
    }

    /**
     * Cleans what the couple typed in the quiz editor: up to eight questions per partner, each with
     * two to four answers and one of them marked right. Blank rows are dropped.
     *
     * @return array<int,array{0:string,1:array<int,string>,2:int}>
     */
    public static function clean(array $rows): array
    {
        $out = [];
        foreach ($rows as $r) {
            if (!is_array($r)) {
                continue;
            }
            $q = mb_substr(trim((string) ($r['q'] ?? '')), 0, 160);
            $raw = array_map(static fn ($o) => mb_substr(trim((string) $o), 0, 80), array_slice((array) ($r['o'] ?? []), 0, 4));
            $right = (int) ($r['r'] ?? 0);
            $opts = [];
            $newRight = -1;
            foreach ($raw as $i => $o) {
                if ($o === '') {
                    continue;
                }
                if ($i === $right) {
                    $newRight = count($opts);
                }
                $opts[] = $o;
            }
            if ($q === '' || count($opts) < 2) {
                continue;
            }
            $out[] = [$q, $opts, max(0, $newRight)];
            if (count($out) >= self::MAX_QUESTIONS) {
                break;
            }
        }
        return $out;
    }
}
