<?php
// Common header include: central place for global <link> / meta tags.
// Assumes bootstrap.php has already defined BASE_URL.
if (!defined('BASE_URL')) {
    // fallback: try to compute a sane BASE_URL
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir    = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/cdms/public/index.php'), '/');
    define('BASE_URL', $scheme . '://' . $host . $dir);
}
?>
<link rel="stylesheet" href="<?php echo BASE_URL ?>/assets/style.css">
