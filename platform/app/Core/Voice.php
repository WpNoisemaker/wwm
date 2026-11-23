<?php
declare(strict_types=1);

/**
 * Voice notes on the wall of love, for Premium and High Society pages. A guest records a short
 * message in the browser (or picks a recording from their phone); it is kept here and plays on the
 * wall like a note. Only real audio is kept: the first bytes have to say so, whatever the name says.
 */
final class Voice
{
    public const MAX_BYTES = 4 * 1024 * 1024;   // about two minutes, whichever way a phone records
    public const MAX_SECONDS = 90;

    /** Returns the saved path, or '' with $err saying why not. */
    public static function store(array $file, int $weddingId, string &$err): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file((string) $file['tmp_name'])) {
            $err = ($file['error'] ?? 0) === UPLOAD_ERR_INI_SIZE ? 'That recording is too long. Keep it under a minute and a half.' : 'The voice note did not upload. Try again.';
            return '';
        }
        if ((int) $file['size'] <= 0 || (int) $file['size'] > self::MAX_BYTES) {
            $err = 'That recording is too long. Keep it under a minute and a half.';
            return '';
        }
        $ext = self::kind((string) file_get_contents((string) $file['tmp_name'], false, null, 0, 64));
        if ($ext === '') {
            $err = 'That is not a recording we can play.';
            return '';
        }
        $dir = App::root() . '/public/assets/uploads/voice/' . $weddingId;
        if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
            $err = 'The voice note could not be saved on the server.';
            return '';
        }
        $path = 'uploads/voice/' . $weddingId . '/' . bin2hex(random_bytes(8)) . '.' . $ext;
        if (!@move_uploaded_file((string) $file['tmp_name'], App::root() . '/public/assets/' . $path)) {
            $err = 'The voice note could not be saved on the server.';
            return '';
        }
        return $path;
    }

    /** The kind of audio, from its first bytes: what browsers record (WebM, MP4, Ogg) and what phones save. */
    public static function kind(string $head): string
    {
        return match (true) {
            str_starts_with($head, "\x1A\x45\xDF\xA3") => 'webm',
            substr($head, 4, 4) === 'ftyp' => 'm4a',
            str_starts_with($head, 'OggS') => 'ogg',
            str_starts_with($head, 'RIFF') && substr($head, 8, 4) === 'WAVE' => 'wav',
            str_starts_with($head, 'ID3'), (strlen($head) > 1 && ord($head[0]) === 0xFF && (ord($head[1]) & 0xE6) === 0xE2) => 'mp3',
            strlen($head) > 1 && ord($head[0]) === 0xFF && (ord($head[1]) & 0xF6) === 0xF0 => 'aac',
            default => '',
        };
    }

    /** The type to tell a browser, from the saved file's ending. */
    public static function mime(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'webm' => 'audio/webm',
            'm4a'  => 'audio/mp4',
            'ogg'  => 'audio/ogg',
            'wav'  => 'audio/wav',
            'aac'  => 'audio/aac',
            default => 'audio/mpeg',
        };
    }
}
