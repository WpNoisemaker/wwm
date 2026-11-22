<?php
/** One inline sprite. No icon font, no extra request. */
function icon(string $n, int $s = 22): string {
    $p = [
        'home'    => '<path d="M4 11 12 4l8 7v8a1 1 0 0 1-1 1h-4v-6H9v6H5a1 1 0 0 1-1-1z"/>',
        'search'  => '<circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/>',
        'talk'    => '<path d="M4 5.5h16v12H8l-4 3z"/>',
        'heart'   => '<path d="M12 20s-7.5-4.6-9.3-9.2C1.5 7.6 3.6 4.5 6.9 4.5c2 0 3.4 1.1 5.1 3 1.7-1.9 3.1-3 5.1-3 3.3 0 5.4 3.1 4.2 6.3C19.5 15.4 12 20 12 20z"/>',
        'user'    => '<circle cx="12" cy="8" r="3.6"/><path d="M4.5 20a7.5 7.5 0 0 1 15 0"/>',
        'plus'    => '<path d="M12 5v14M5 12h14"/>',
        'arrow'   => '<path d="M5 12h13M13 6l6 6-6 6"/>',
        'up'      => '<path d="M7 17 17 7M9 7h8v8"/>',
        'rings'   => '<circle cx="9" cy="14" r="5.5"/><circle cx="15" cy="14" r="5.5"/><path d="M12 4.5 14 7h-4z"/>',
        'cash'    => '<rect x="3" y="6" width="18" height="12" rx="3"/><circle cx="12" cy="12" r="2.6"/>',
        'cal'     => '<rect x="3.5" y="5" width="17" height="15.5" rx="4"/><path d="M3.5 10h17M8 3v4M16 3v4"/>',
        'gift'    => '<rect x="3.5" y="8.5" width="17" height="11.5" rx="2.5"/><path d="M3.5 13h17M12 8.5V20M12 8.5S10 3.5 7.5 4.8 9.5 8.5 12 8.5zM12 8.5s2-5 4.5-3.7S14.5 8.5 12 8.5z"/>',
        'cam'     => '<rect x="3" y="7" width="18" height="13" rx="4"/><circle cx="12" cy="13.5" r="3.6"/><path d="M8.5 7 10 4.5h4L15.5 7"/>',
        'store'   => '<path d="M4.5 11v9h15v-9"/><path d="M3 9.5 4.8 4h14.4L21 9.5a2.6 2.6 0 0 1-4.5 1.8 2.6 2.6 0 0 1-4.5 0 2.6 2.6 0 0 1-4.5 0A2.6 2.6 0 0 1 3 9.5z"/>',
        'chart'   => '<path d="M4 20V9M10 20V4M16 20v-7M22 20H2"/>',
        'spark'   => '<path d="m12 3 1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9z"/>',
        'lock'    => '<rect x="5" y="10.5" width="14" height="10" rx="3"/><path d="M8.5 10.5v-3a3.5 3.5 0 0 1 7 0v3"/>',
        'back'    => '<path d="M19 12H6M11 6l-6 6 6 6"/>',
        'bell'    => '<path d="M6 9a6 6 0 0 1 12 0c0 5 2 6 2 6H4s2-1 2-6z"/><path d="M10 19a2 2 0 0 0 4 0"/>',
        'check'   => '<path d="m5 12.5 4.5 4.5L19 7"/>',
    ];
    $d = $p[$n] ?? $p['spark'];
    return '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $d . '</svg>';
}
