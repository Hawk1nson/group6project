<?php
// Redirect to dashboard if already logged in, else to login page

require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/helpers.php';

if (auth_check()) redirect('/../public/dashboard.php');
redirect('login.php');

?>

<?php require_once __DIR__ . '/bootstrap.php'; ?>