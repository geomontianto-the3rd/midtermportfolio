<?php
declare(strict_types=1);

const UPLOAD_MAX_BYTES = 2097152;   // 2 MB
const UPLOAD_MAX_PIXELS = 6000;     // longest side

/* ---------------- Output escaping (XSS) ---------------- */

/** Escape ANY dynamic value before it is printed in HTML. */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function str_length(string $s): int
{
    return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s);
}

function truncate(string $s, int $max): string
{
    if (str_length($s) <= $max) {
        return $s;
    }
    $cut = function_exists('mb_substr') ? mb_substr($s, 0, $max - 1, 'UTF-8') : substr($s, 0, $max - 1);
    return rtrim($cut) . '…';
}

function redirect(string $to): void
{
    header('Location: ' . $to);
    exit;
}

/** Only http:// and https:// links are allowed (blocks javascript: URLs). */
function safe_url(?string $url): string
{
    $url = trim((string) $url);
    if ($url === '' || !preg_match('~^https?://~i', $url)) {
        return '';
    }
    return filter_var($url, FILTER_VALIDATE_URL) ? $url : '';
}

/** Image paths in the database must look like uploads/xxx.jpg or assets/images/xxx.png */
function img_url(?string $path, string $prefix = ''): string
{
    $path = (string) $path;
    if ($path === '' || strpos($path, '..') !== false) {
        return '';
    }
    if (!preg_match('~^(uploads|assets/images)/[A-Za-z0-9._-]+$~', $path)) {
        return '';
    }
    return $prefix . $path;
}

function tags_list(string $tags): array
{
    $out = [];
    foreach (explode(',', $tags) as $t) {
        $t = trim($t);
        if ($t !== '') {
            $out[] = $t;
        }
    }
    return $out;
}

function hex_to_rgba(string $hex, float $alpha): string
{
    $hex = ltrim($hex, '#');
    if (!preg_match('/^[0-9a-f]{6}$/i', $hex)) {
        $hex = '17694d';
    }
    return sprintf('rgba(%d, %d, %d, %s)',
        hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)), $alpha);
}

/* ---------------- Site data ---------------- */

function settings(): array
{
    static $s = null;
    if ($s === null) {
        $s = [
            'site_title' => 'Portfolio', 'meta_description' => '', 'logo_text' => 'Portfolio',
            'logo_image' => '', 'footer_text' => '', 'accent_light' => '#17694d',
            'accent_dark' => '#7fdcb7', 'default_theme' => 'system',
        ];
        foreach (db()->query('SELECT setting_key, setting_value FROM settings') as $row) {
            $s[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $s;
}

function profile(): array
{
    static $p = null;
    if ($p === null) {
        $row = db()->query('SELECT * FROM profile WHERE id = 1')->fetch();
        $p = $row ?: [];
    }
    return $p;
}

/* ---------------- Icons and placeholder thumbnails ---------------- */

function icon_choices(): array
{
    return ['cap' => 'Graduation cap', 'code' => 'Code', 'award' => 'Award', 'briefcase' => 'Briefcase'];
}

function icon_svg(string $name, int $size = 56): string
{
    $paths = [
        'cap'       => '<path d="M2 9l10-5 10 5-10 5-10-5z"/><path d="M6 11.5V16c0 1.5 3 3 6 3s6-1.5 6-3v-4.5"/><path d="M22 9v6"/>',
        'code'      => '<path d="M8 8l-4 4 4 4"/><path d="M16 8l4 4-4 4"/><path d="M13.5 5l-3 14"/>',
        'award'     => '<circle cx="12" cy="9" r="6"/><path d="M8.5 14L7 22l5-3 5 3-1.5-8"/>',
        'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2M3 13h18"/>',
    ];
    $inner = $paths[$name] ?? $paths['cap'];
    return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
         . 'stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $inner . '</svg>';
}

function placeholder_choices(): array
{
    return ['dashboard' => 'Dashboard', 'ballot' => 'Ballot / voting', 'portfolio' => 'Portfolio page', 'grid' => 'Item grid'];
}

/** Drawn thumbnails used when a project has no uploaded screenshot. */
function placeholder_svg(string $style, string $label): string
{
    $shapes = [
        'dashboard' =>
            '<rect class="s-w" x="28" y="24" width="264" height="132" rx="14"/>'
          . '<rect class="s-a" x="44" y="40" width="72" height="8" rx="4"/>'
          . '<rect class="s-m" x="44" y="66" width="140" height="8" rx="4"/><rect class="s-m" x="206" y="66" width="70" height="8" rx="4"/><rect class="s-a" x="206" y="66" width="52" height="8" rx="4"/>'
          . '<rect class="s-m" x="44" y="88" width="110" height="8" rx="4"/><rect class="s-m" x="206" y="88" width="70" height="8" rx="4"/><rect class="s-a" x="206" y="88" width="34" height="8" rx="4"/>'
          . '<rect class="s-m" x="44" y="110" width="150" height="8" rx="4"/><rect class="s-m" x="206" y="110" width="70" height="8" rx="4"/><rect class="s-a" x="206" y="110" width="64" height="8" rx="4"/>'
          . '<rect class="s-m" x="44" y="132" width="96" height="8" rx="4"/><rect class="s-m" x="206" y="132" width="70" height="8" rx="4"/><rect class="s-a" x="206" y="132" width="24" height="8" rx="4"/>',
        'ballot' =>
            '<rect class="s-w" x="28" y="24" width="264" height="132" rx="14"/>'
          . '<circle class="s-a" cx="56" cy="58" r="9"/><rect class="s-a" x="80" y="54" width="190" height="8" rx="4"/>'
          . '<circle class="s-m" cx="56" cy="92" r="9"/><rect class="s-m" x="80" y="88" width="130" height="8" rx="4"/>'
          . '<circle class="s-m" cx="56" cy="126" r="9"/><rect class="s-m" x="80" y="122" width="80" height="8" rx="4"/>',
        'portfolio' =>
            '<rect class="s-w" x="28" y="24" width="264" height="132" rx="14"/>'
          . '<circle class="s-a" cx="70" cy="70" r="22"/>'
          . '<rect class="s-a" x="108" y="56" width="120" height="9" rx="4.5"/>'
          . '<rect class="s-m" x="108" y="74" width="84" height="8" rx="4"/>'
          . '<rect class="s-m" x="44" y="108" width="72" height="34" rx="9"/>'
          . '<rect class="s-m" x="124" y="108" width="72" height="34" rx="9"/>'
          . '<rect class="s-m" x="204" y="108" width="72" height="34" rx="9"/>',
        'grid' =>
            '<rect class="s-w" x="28" y="24" width="264" height="132" rx="14"/>'
          . '<rect class="s-m" x="44" y="40" width="72" height="46" rx="9"/><circle class="s-a" cx="58" cy="54" r="5"/>'
          . '<rect class="s-m" x="124" y="40" width="72" height="46" rx="9"/><circle class="s-a" cx="138" cy="54" r="5"/>'
          . '<rect class="s-m" x="204" y="40" width="72" height="46" rx="9"/><circle class="s-m" cx="218" cy="54" r="5"/>'
          . '<rect class="s-m" x="44" y="96" width="72" height="46" rx="9"/><circle class="s-m" cx="58" cy="110" r="5"/>'
          . '<rect class="s-m" x="124" y="96" width="72" height="46" rx="9"/><circle class="s-a" cx="138" cy="110" r="5"/>'
          . '<rect class="s-m" x="204" y="96" width="72" height="46" rx="9"/><circle class="s-a" cx="218" cy="110" r="5"/>',
    ];
    $inner = $shapes[$style] ?? $shapes['dashboard'];
    return '<svg viewBox="0 0 320 180" role="img" aria-label="' . e($label) . '">' . $inner . '</svg>';
}

/** One card used by the Education, Experience and Achievements groups. */
function render_info_card(array $r, int $i): string
{
    $img = img_url($r['image'] ?? null);
    $kicker = trim((string) ($r['kicker'] ?? ''));
    $period = trim((string) ($r['period'] ?? ''));
    $top = $kicker . ($kicker !== '' && $period !== '' ? ' · ' : '') . $period;

    $h = '<article class="card glass">';
    if ($img !== '') {
        $h .= '<div class="media media-photo"><img src="' . e($img) . '" alt="' . e($r['title'] ?? '') . '" loading="lazy"></div>';
    } else {
        $h .= '<div class="media tone-' . (($i % 4) + 1) . '">' . icon_svg((string) ($r['icon'] ?? 'cap')) . '</div>';
    }
    $h .= '<div class="card-body">';
    if ($top !== '') {
        $h .= '<p class="kicker">' . e($top) . '</p>';
    }
    $h .= '<h3>' . e($r['title'] ?? '') . '</h3>';
    if (trim((string) ($r['organization'] ?? '')) !== '') {
        $h .= '<p class="org">' . e($r['organization']) . '</p>';
    }
    if (trim((string) ($r['description'] ?? '')) !== '') {
        $h .= '<p>' . e($r['description']) . '</p>';
    }
    return $h . '</div></article>';
}

/* ---------------- Secure image upload ---------------- */

/**
 * Validates and stores one uploaded image.
 * Returns [relative path or null, error message or null].
 * [null, null] means "no file was chosen".
 */
function upload_image(array $file): array
{
    $err = $file['error'] ?? UPLOAD_ERR_NO_FILE;
    if (is_array($err)) {
        return [null, 'Invalid upload.'];
    }
    if ($err === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
        return [null, 'The image is too large (maximum 2 MB).'];
    }
    if ($err !== UPLOAD_ERR_OK) {
        return [null, 'The upload failed. Please try again.'];
    }

    $tmp = (string) ($file['tmp_name'] ?? '');
    if (!is_uploaded_file($tmp)) {
        return [null, 'Invalid upload.'];
    }
    if ((int) ($file['size'] ?? 0) > UPLOAD_MAX_BYTES || filesize($tmp) > UPLOAD_MAX_BYTES) {
        return [null, 'The image is too large (maximum 2 MB).'];
    }

    // 1) Real MIME type from the file CONTENT (not the name, not the browser's claim)
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string) $finfo->file($tmp);
    if (!isset($allowed[$mime])) {
        return [null, 'Only JPG, PNG, WEBP or GIF images are allowed.'];
    }

    // 2) It must also parse as a real image
    $info = @getimagesize($tmp);
    $okTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF];
    if ($info === false || !in_array($info[2], $okTypes, true)) {
        return [null, 'That file is not a valid image.'];
    }
    if ($info[0] > UPLOAD_MAX_PIXELS || $info[1] > UPLOAD_MAX_PIXELS) {
        return [null, 'The image dimensions are too large (maximum 6000 px).'];
    }

    // 3) Random server-chosen name; the extension comes from the detected type
    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $dir = dirname(__DIR__) . '/uploads';
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        return [null, 'The uploads folder is not available.'];
    }
    if (!move_uploaded_file($tmp, $dir . '/' . $name)) {
        return [null, 'The image could not be saved.'];
    }
    @chmod($dir . '/' . $name, 0644);   // readable, never executable
    return ['uploads/' . $name, null];
}

/** Deletes a file we created. Seeded files in assets/ are never touched. */
function delete_upload(?string $path): void
{
    if (!$path || !preg_match('~^uploads/[a-f0-9]{32}\.(jpg|png|webp|gif)$~', $path)) {
        return;
    }
    $full = dirname(__DIR__) . '/' . $path;
    if (is_file($full)) {
        @unlink($full);
    }
}
