<?php
require_once __DIR__ . '/bootstrap.php';
if (!auth_check()) { http_response_code(403); exit('Forbidden'); }
$u = auth_user();
if (!isset($u['role']) || $u['role'] !== 'admin') { http_response_code(403); exit('Forbidden'); }

$baseDir = realpath(APP_ROOT . '/storage/reports');
if (!$baseDir) { http_response_code(404); exit('Not found'); }

$fname = $_GET['file'] ?? '';
$fname = basename($fname);
$path = realpath($baseDir . '/' . $fname);
if (!$path || strpos($path, $baseDir) !== 0 || !is_file($path)) {
    http_response_code(404);
    exit('Not found');
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $fname . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
