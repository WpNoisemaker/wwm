<?php
declare(strict_types=1);

/**
 * Plain-text email through the host's own mail().
 *
 * Shared hosting is the target, and every cPanel host provides mail() with no setup. There is no
 * SMTP library here on purpose — nothing to install, nothing to keep patched. If deliverability
 * ever matters more than simplicity, this is the single place to swap in an SMTP client.
 */
final class Mailer
{
    public static function send(string $to, string $subject, string $body): bool
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

        $sender = '=?UTF-8?B?' . base64_encode($name) . '?= <' . $from . '>';
        $headers = [
            'From'                      => $sender,
            'Reply-To'                  => $sender,
            'MIME-Version'              => '1.0',
            'Content-Type'              => 'text/plain; charset=UTF-8',
            'Content-Transfer-Encoding' => '8bit',
        ];
        $text = str_replace("\n", "\r\n", str_replace("\r\n", "\n", $body));

        $ok = @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $text, $headers, '-f' . $from);
        if (!$ok) {
            // Logged without the recipient's address: error logs get read by people who should not
            // be handed a list of who asked to reset a password.
            error_log('[wwm] mail() refused a message with subject "' . $subject . '"');
        }
        return $ok;
    }
}
