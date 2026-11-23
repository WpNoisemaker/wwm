<?php
declare(strict_types=1);

/**
 * The anonymous community. No account, no real names, no images — a post is words on a colour.
 *
 * Nothing here records who wrote what beyond a rotating per-browser key used only to stop one
 * person liking the same post twice. We never claim, in copy or in code, that a post can be traced
 * back to a person.
 */
final class CommunityController
{
    private const BGS = ['dusk', 'sky', 'night', 'blue', 'slate', 'cream'];

    public function index(): void
    {
        $topic = isset($_GET['topic']) && isset(Talk::TOPICS[$_GET['topic']]) ? (string) $_GET['topic'] : '';
        $sort  = ($_GET['sort'] ?? '') === 'new' ? 'new' : 'top';

        $liked = [];
        foreach (Db::all("SELECT subject_id FROM likes WHERE subject = 'talk' AND voter = ?", [self::key()]) as $l) {
            $liked[(int) $l['subject_id']] = true;
        }
        View::render('community/index', [
            'title'  => 'The community: say it here instead',
            'desc'   => 'Love, family, money, faith, in-laws and starting over. No account, any pen name.',
            'tab'    => 'community',
            'action' => '<button class="cbtn" type="button" data-open="compose" aria-label="Write a post">' . icon('pen') . '</button>',
            'liked'  => $liked,
            'rows'   => Talk::feed($topic, $sort, 40),
            'topic'  => $topic,
            'sort'   => $sort,
            'topics' => Talk::TOPICS,
        ]);
    }

    public function show(string $id): void
    {
        $t = Db::one("SELECT * FROM talks WHERE id = ? AND status = 'live'", [(int) $id]);
        if (!$t) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not found']);
            return;
        }
        View::render('community/show', [
            'title'   => '“' . mb_strimwidth(trim((string) preg_replace('/\s+/', ' ', (string) $t['body'])), 0, 64, '…') . '” · Community',
            'desc'    => mb_strimwidth(trim((string) preg_replace('/\s+/', ' ', (string) $t['body'])), 0, 180, '…'),
            // The preview is the post itself, on its own colour, with our address under it.
            'ogImage' => Share::url('t', (string) $t['id'], implode('|', [$t['body'], $t['bg'], $t['topic'], $t['pen_name']])),
            'ogTitle' => ($t['pen_name'] ?: 'Someone') . ' asked the community · ' . Talk::topic((string) $t['topic']),
            'ogDesc'  => mb_strimwidth(trim((string) preg_replace('/\s+/', ' ', (string) $t['body'])), 0, 200, '…') . ' Reply under any pen name.',
            'ogType'  => 'article',
            'tab'     => 'community',
            'appTitle'=> 'Community',
            'back'    => App::url('/community'),
            'liked'   => (bool) Db::val("SELECT 1 FROM likes WHERE subject = 'talk' AND subject_id = ? AND voter = ?", [(int) $t['id'], self::key()]),
            't'       => $t,
            'replies' => Db::all("SELECT * FROM talk_replies WHERE talk_id = ? AND status = 'live' ORDER BY created_at", [$t['id']]),
        ]);
    }

    public function store(): void
    {
        Guard::must();
        if (Guard::tooMany('talk', 6, 30)) {
            Guard::flash('bad', 'Give it a few minutes before posting again.');
            self::go('/community');
        }
        $body = trim((string) ($_POST['body'] ?? ''));
        $pen  = trim((string) ($_POST['pen_name'] ?? ''));
        $topic = isset(Talk::TOPICS[$_POST['topic'] ?? '']) ? (string) $_POST['topic'] : 'confess';
        if (mb_strlen($body) < 12) {
            Guard::flash('bad', 'Say a little more — at least a sentence.');
            self::go('/community');
        }
        Db::insert('talks', [
            'topic'      => $topic,
            // Any name they like. It is a label on the thread, not an identity.
            'pen_name'   => mb_substr($pen !== '' ? $pen : 'Someone', 0, 60),
            'body'       => mb_substr($body, 0, 2000),
            'bg'         => in_array($_POST['bg'] ?? '', self::BGS, true) ? (string) $_POST['bg'] : self::BGS[array_rand(self::BGS)],
            'status'     => 'live',
            'writer_key' => self::key(),
        ]);
        Guard::flash('ok', 'Posted under your pen name.');
        self::go('/community?sort=new');
    }

    public function reply(string $id): void
    {
        Guard::must();
        $t = Db::one("SELECT id FROM talks WHERE id = ? AND status='live'", [(int) $id]);
        if (!$t || Guard::tooMany('reply', 12, 20)) {
            self::go('/community/' . (int) $id);
        }
        $body = trim((string) ($_POST['body'] ?? ''));
        if (mb_strlen($body) < 2) {
            self::go('/community/' . (int) $id);
        }
        Db::insert('talk_replies', [
            'talk_id'    => (int) $id,
            'pen_name'   => mb_substr(trim((string) ($_POST['pen_name'] ?? '')) ?: 'Someone', 0, 60),
            'body'       => mb_substr($body, 0, 1500),
            'status'     => 'live',
            'writer_key' => self::key(),
        ]);
        Db::run('UPDATE talks SET replies = replies + 1 WHERE id = ?', [(int) $id]);
        Guard::flash('ok', 'Replied under your pen name.');
        self::go('/community/' . (int) $id);
    }

    /** One like per browser per post. The key identifies a browser, never a person. */
    public function like(string $id): void
    {
        Guard::must();
        $key = self::key();
        $had = Db::val("SELECT 1 FROM likes WHERE subject = 'talk' AND subject_id = ? AND voter = ?", [(int) $id, $key]);
        if ($had) {
            Db::run("DELETE FROM likes WHERE subject='talk' AND subject_id = ? AND voter = ?", [(int) $id, $key]);
            Db::run('UPDATE talks SET likes = GREATEST(0, likes - 1) WHERE id = ?', [(int) $id]);
        } else {
            Db::run("INSERT IGNORE INTO likes (subject, subject_id, voter) VALUES ('talk', ?, ?)", [(int) $id, $key]);
            Db::run('UPDATE talks SET likes = likes + 1 WHERE id = ?', [(int) $id]);
        }
        // The deck likes without leaving the page: it asks for JSON and gets the new count back.
        if (str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json')) {
            header('Content-Type: application/json');
            echo json_encode(['likes' => (int) Db::val('SELECT likes FROM talks WHERE id = ?', [(int) $id], 0), 'liked' => !$had]);
            exit;
        }
        self::go('/community/' . (int) $id);
    }

    /**
     * A per-browser key. It is a random value kept in the visitor's own session — it is not
     * derived from an IP, a name or anything else that could point back at a person.
     */
    private static function key(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['_wk'])) {
            $_SESSION['_wk'] = bin2hex(random_bytes(16));
        }
        return (string) $_SESSION['_wk'];
    }

    private static function go(string $p): never
    {
        header('Location: ' . App::url($p));
        exit;
    }
}
