<?php
// Compatibility redirect: previous templates linked to contact.php
require_once __DIR__ . '/bootstrap.php';

$qs = $_SERVER['QUERY_STRING'] ?? '';
$target = 'contact_us.php' . ($qs !== '' ? '?' . $qs : '');
redirect($target);
