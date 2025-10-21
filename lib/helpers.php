<?php
// HTML-escape (now null-safe)
function e($s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
// Generate image src URL from DB value (now null-safe)
function img_src_from_db(?string $val): string
{
    $val = trim((string)$val);
    if ($val === '') return '';

    // If it's protocol-relative or absolute URL, return as-is (but encode spaces)
    if (preg_match('~^(https?:)?//~i', $val)) {
        return preg_replace('/\s+/', '%20', $val);
    }

    // If it starts with a slash, treat as root-relative
    if (str_starts_with($val, '/')) {
        return preg_replace('/\s+/', '%20', $val);
    }

    // Otherwise, treat as a filename under your local images folder:
    // Adjust the base path if your images live elsewhere.
    $base = '/cdms/images/vehicles/';
    return $base . rawurlencode($val);
}

function redirect(string $rel): void
{
    header('Location: ' . $rel);
    exit;
}
