<?php
// Alias to keep links working if using messages.php
require_once __DIR__ . '/bootstrap.php';
redirect('message.php' . (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : ''));
