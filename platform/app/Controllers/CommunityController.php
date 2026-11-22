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
    private const BGS = ['paper', 'ink', 'sun', 'citrus', 'blush', 'dusk', 'noir'];

    public function index(): void
    {
        $topic = isset($_GET['topic']) && isset(Talk::TOPICS[$_GET['topic']]) ? (string) $_GET['topic'] : '';
        $sort  = ($_GET['sort'] ?? '') === 'new' ? 'new' : 'top';

        View::render('community/index', [
            'title'  => 'The anonymous community',
            'desc'   => 'Love, family, money, faith, in-laws and starting over. No account, no real names.',
            'tab'    => 'community',
            'css'    => ['community'],
            'js'     => ['deck'],
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
            'title'   => Talk::topic((string) $t['topic']),
            'desc'    => mb_strimwidth((string) $t['body'], 0, 150, '…'),
            'tab'     => 'community',
            'css'     => ['community'],
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
            'bg'         => self::BGS[array_rand(self::BGS)],
            'status'     => 'live',
            'writer_key' => self::key(),
        ]);
        Guard::flash('ok', 'Posted. Nobody can tell it was you.');
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
