<?php
declare(strict_types=1);

/**
 * Email through the host's own mail(), every message branded.
 *
 * Shared hosting is the target, and every cPanel host provides mail() with no setup. There is no
 * SMTP library here on purpose — nothing to install, nothing to keep patched. If deliverability
 * ever matters more than simplicity, this is the single place to swap in an SMTP client.
 *
 * Each message goes out twice in one: the plain words as written, for any program that wants text,
 * and the same words in the branded design (app/Views/emails/branded.php). The main link becomes a
 * button: pass it as $o['button'] = [label, url], or a link alone on its own line is used.
 */
final class Mailer
{
    /**
     * @param array{button?:array{0:string,1:string},pre?:string,unsub?:string,why?:string,title?:string,quote?:string} $o
     */
    public static function send(string $to, string $subject, string $body, array $o = []): bool
    {
        $to = trim($to);
        // A newline in an address or a subject is how mail header injection works. Refuse outright.
        if (!filter_var($to, FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $subject)) {
            return false;
        }

        $cfg  = (array) App::config('mail', []);
        $name = trim((string) ($cfg['name'] ?? 'Wedding Websites & More'));
        $from = trim((string) ($cfg['from'] ?? ''));
        if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
            // No usable sender configured: fall back to noreply@ this site's own domain, which is
            // the address a receiving server is most likely to accept for it.
            $host = (string) (parse_url(App::url('/'), PHP_URL_HOST) ?: 'localhost');
            $from = 'noreply@' . preg_replace('/^www\./', '', $host);
        }

        $html = self::html($subject, $body, $o);
        // The plain-text copy has no code block: the code goes in as a line of its own.
        $plain = str_replace('[[wwm-code]]', !empty($o['code']) ? ($o['code_label'] ?? 'Your access code') . ': ' . $o['code'] : '', $body);
        $text = str_replace("\n", "\r\n", str_replace("\r\n", "\n", $plain));
        $b = 'wwm-' . bin2hex(random_bytes(12));
        $sender = '=?UTF-8?B?' . base64_encode($name) . '?= <' . $from . '>';
        $headers = [
            'From'         => $sender,
            'Reply-To'     => $sender,
            'MIME-Version' => '1.0',
            'Content-Type' => 'multipart/alternative; boundary="' . $b . '"',
        ];
        if (!empty($o['unsub'])) {
            $headers['List-Unsubscribe'] = '<' . $o['unsub'] . '>';
        }
        $msg = "--$b\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" . chunk_split(base64_encode($text))
             . "--$b\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" . chunk_split(base64_encode($html))
             . "--$b--\r\n";

        $ok = @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $msg, $headers, '-f' . $from);
        if (!$ok) {
            // Logged without the recipient's address: error logs get read by people who should not
            // be handed a list of who asked to reset a password.
            error_log('[wwm] mail() refused a message with subject "' . $subject . '"');
        }
        return $ok;
    }

    /** The branded HTML for a message: what goes out, and what HQ previews. */
    public static function html(string $subject, string $body, array $o = []): string
    {
        $button = $o['button'] ?? null;
        $unsub = (string) ($o['unsub'] ?? '');
        $lines = preg_split('/\R/', trim(str_replace("\r\n", "\n", $body)));
        // Without a button given, a link alone on its own line becomes the button.
        if (!$button) {
            foreach ($lines as $ln) {
                if (preg_match('~^https?://\S+$~', trim($ln))) {
                    $button = ['Open it', trim($ln)];
                    break;
                }
            }
        }
        $keep = [];
        $placed = false;
        foreach ($lines as $ln) {
            $t = trim($ln);
            // The button goes where its link was written; the "stop these emails" link goes in the foot.
            foreach (array_filter([$button[1] ?? '', $unsub]) as $u) {
                if ($t !== '' && str_contains($t, $u)) {
                    $t = trim(str_replace($u, '', $t));
                    $isBtn = $u === ($button[1] ?? null);
                    if ($t === '' || (mb_strlen($t) < 40 && str_ends_with($t, ':'))) {
                        if ($isBtn && !$placed) {
                            if ($t !== '') {
                                $keep[] = $t;
                            }
                            $keep[] = "[[wwm-button]]";
                            $placed = true;
                        }
                        continue 2;
                    }
                }
            }
            // The sign-off is in the design too.
            if (preg_match('/^(—\s*)?(the\s+)?wedding websites & more(\s+team)?\.?$/i', $t)) {
                continue;
            }
            $keep[] = $t;
        }
        $html = '';
        $p = static fn (string $s): string => '<p style="margin:0 0 16px;">' . $s . '</p>';
        foreach (preg_split('/\n\s*\n/', trim(implode("\n", $keep))) as $para) {
            $para = trim($para);
            if ($para === '') {
                continue;
            }
            $rows = explode("\n", $para);
            $at = array_search("[[wwm-button]]", $rows, true);
            if ($at !== false) {
                $before = array_slice($rows, 0, (int) $at);
                $after = array_slice($rows, (int) $at + 1);
                if ($before) {
                    $html .= $p(implode('<br>', array_map([self::class, 'inline'], $before)));
                }
                $html .= View::partial('emails/button', ['button' => $button]);
                if ($after) {
                    $html .= $p(implode('<br>', array_map([self::class, 'inline'], $after)));
                }
                continue;
            }
            // Someone's own words (a chat message) sit in a quote card.
            if (isset($o['quote']) && trim($para) === trim((string) $o['quote'])) {
                $html .= '<div style="margin:0 0 18px;padding:16px 18px;border-radius:18px;background:#2A3242;border-left:3px solid #E9B949;color:#EEF1F6;">' . implode('<br>', array_map([self::class, 'inline'], $rows)) . '</div>';
                continue;
            }
            if (count(array_filter($rows, static fn ($r) => str_starts_with(trim($r), '• '))) === count($rows)) {
                $html .= '<ul style="margin:0 0 16px;padding-left:20px;">' . implode('', array_map(static fn ($r) => '<li style="margin:0 0 6px;">' . self::inline(mb_substr(trim($r), 2)) . '</li>', $rows)) . '</ul>';
            } else {
                $html .= $p(implode('<br>', array_map([self::class, 'inline'], $rows)));
            }
        }
        if (!empty($o['code'])) {
            $code = View::partial('emails/code', ['code' => (string) $o['code'], 'label' => (string) ($o['code_label'] ?? 'Your access code')]);
            $html = str_contains($html, '[[wwm-code]]') ? str_replace('<p style="margin:0 0 16px;">[[wwm-code]]</p>', $code, $html) : $code . $html;
        }
        if ($button && !$placed) {
            $html .= View::partial('emails/button', ['button' => $button]);
        }
        return View::partial('emails/branded', [
            'title'  => (string) ($o['title'] ?? $subject),
            'body'   => $html,
            'pre'    => (string) ($o['pre'] ?? mb_strimwidth(trim(preg_replace('/\s+/', ' ', strip_tags(str_replace('<br>', ' ', (string) preg_replace('~<table.*?</table>\s*<p.*?</p>~s', ' ', $html))))), 0, 110, '…')),
            'unsub'  => $unsub,
            'why'    => (string) ($o['why'] ?? 'You get this email because of your account on Wedding Websites & More.'),
        ]);
    }

    /** One line of text, made safe, with its links made clickable. */
    private static function inline(string $s): string
    {
        $s = e($s);
        return (string) preg_replace('~https?://[^\s<]+~', '<a href="$0" style="color:#A9C2FF;">$0</a>', $s);
    }
}
