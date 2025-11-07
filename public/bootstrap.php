<?php
// cdms/public/bootstrap.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Absolute paths
define('APP_ROOT', dirname(__DIR__));   // /.../CDMS
define('PUB_ROOT', __DIR__);            // /.../CDMS/public

// Load config and libs from your structure
$cfg = APP_ROOT . '/config/config.php';
$db  = APP_ROOT . '/lib/db.php';
$auth = APP_ROOT . '/lib/auth.php';
$helpers = APP_ROOT . '/lib/helpers.php';

foreach ([$cfg, $db, $auth, $helpers] as $f) {
    if (is_file($f)) require_once $f;
}

// BASE_URL (only if your config didn’t set it)
if (!defined('BASE_URL')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir    = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/cdms/public/index.php'), '/');
    define('BASE_URL', $scheme . '://' . $host . $dir);
}
