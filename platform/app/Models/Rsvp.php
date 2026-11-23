<?php
declare(strict_types=1);

/**
 * RSVPs the couple approves. A guest replies with their name, email and a photo of their face. The
 * reply waits until the couple approves it; then the guest gets an email with their access code, and
 * at the gate the code brings up the same face, so the person holding it can be checked.
 */
final class Rsvp
{
    /** Letters and numbers that cannot be mistaken for each other when read out at a gate. */
    private const CODE_CHARS = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public static function faceDir(): string
    {
        return App::root() . '/storage/faces';
    }

    /** A guest's face: kept outside the website and shown only through face(). */
    public static function storeFace(array $file): ?string
    {
        return Images::storeAt($file, self::faceDir());
    }

    public static function code(): string
    {
        do {
            $c = '';
            for ($i = 0; $i < 6; $i++) {
                $c .= self::CODE_CHARS[random_int(0, strlen(self::CODE_CHARS) - 1)];
            }
        } while (Db::val('SELECT 1 FROM guests WHERE pass_code = ?', [$c]));
        return $c;
    }

    /** How a code is read out: AB7-Q4K. */
    public static function pretty(string $code): string
    {
        return strlen($code) === 6 ? substr($code, 0, 3) . '-' . substr($code, 3) : $code;
    }

    /** Typed at the gate with or without the dash, in any case. */
    public static function clean(string $code): string
    {
        return strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $code));
    }

    public static function pending(int $weddingId): array
    {
        return Db::all("SELECT * FROM guests WHERE wedding_id = ? AND status = 'pending' ORDER BY created_at, id", [$weddingId]);
    }

    /** The couple says yes: the guest gets a code (on plans with entry passes) and an email. */
    public static function approve(array $w, int $guestId): bool
    {
        $g = Db::one("SELECT * FROM guests WHERE id = ? AND wedding_id = ? AND status <> 'approved'", [$guestId, $w['id']]);
        if (!$g) {
            return false;
        }
        $code = Wedding::rank((string) $w['plan']) >= Wedding::rank('pro') && $g['coming'] !== 'no' ? self::code() : null;
        Db::update('guests', ['status' => 'approved', 'pass_code' => $code, 'decided_at' => date('Y-m-d H:i:s')], 'id = ?', [$guestId]);
        $g['pass_code'] = $code;
        if (self::mail($w, $g)) {
            Db::update('guests', ['mailed_at' => date('Y-m-d H:i:s')], 'id = ?', [$guestId]);
        }
        Buzz::add($w, 'rsvp');
        return true;
    }

    public static function decline(array $w, int $guestId): bool
    {
        return Db::run("UPDATE guests SET status = 'declined', pass_code = NULL, decided_at = NOW() WHERE id = ? AND wedding_id = ?", [$guestId, $w['id']]) > 0;
    }

    /** The approval email: the code in its own block, and a button to the pass. */
    public static function mail(array $w, array $g): bool
    {
        $to = trim((string) $g['email']);
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $names  = Wedding::names($w);
        $first  = trim(explode(' ', trim((string) $g['name']))[0] ?? '');
        $party  = (int) $g['party'];
        $people = $party === 1 ? '1 person' : $party . ' people';
        $page   = App::url('/w/' . $w['slug']);
        $hello  = 'Hello' . ($first !== '' ? ' ' . $first : '') . ",\n\n";
        if (!empty($g['pass_code'])) {
            $pass = App::url('/w/' . $w['slug'] . '/pass/' . $g['pass_code']);
            $body = $hello . "{$names} have approved your RSVP for {$people}. You are on the guest list.\n\n[[wwm-code]]\n\n"
                  . "Show this code at the entrance. It is for you and your party only, so please do not share it.\n\n"
                  . "The programme, venue and dress code are on their page:\n{$pass}";
            $o = ['button' => ['Open your pass', $pass], 'code' => self::pretty((string) $g['pass_code'])];
        } else {
            $body = $hello . "{$names} have approved your RSVP for {$people}. You are on the guest list.\n\n"
                  . "The programme, venue and dress code are on their page:\n{$page}";
            $o = ['button' => ['Open their page', $page]];
        }
        $o['title'] = 'You are on the guest list';
        $o['why']   = 'You get this email because you replied to ' . $names . '’s invitation.';
        return Mailer::send($to, 'You are on the guest list for ' . $names . '’s wedding', $body, $o);
    }

    /** Who may see a guest's face: the couple, our team, or someone holding that guest's code. */
    public static function canSee(array $g, string $code = ''): bool
    {
        if ($code !== '' && !empty($g['pass_code']) && hash_equals((string) $g['pass_code'], self::clean($code))) {
            return true;
        }
        return self::isCouple((int) $g['wedding_id']) || Team::door((int) $g['wedding_id']);
    }

    /** The couple signed in (or our team, working on the page). */
    public static function isCouple(int $weddingId): bool
    {
        if (!Auth::check()) {
            return false;
        }
        if (Staff::can('weddings') || Managed::managing() === $weddingId) {
            return true;
        }
        return (bool) Db::val('SELECT 1 FROM weddings WHERE id = ? AND owner_id = ?', [$weddingId, Auth::id()]);
    }
}
