<?php
// Redirect to dashboard if already logged in, else to login page

require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/helpers.php';

if (auth_check()) redirect('/../cdms/public/dealership/dashboard.php');
redirect('login.php');

?>