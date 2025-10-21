<?php
// Show errors during local dev
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ---- DB settings (XAMPP defaults) ----
define('DB_HOST', 'localhost');   
define('DB_NAME', 'cdms_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Base URL for links
define('BASE_URL', '/cdms/public');

// WARNING: For simplicity, we use plaintext passwords in this demo.
// Do NOT use plaintext passwords in production systems!
// phase this out ASAP - test to ensure hashed passwords work, then remove this flag and related code.
define('USE_PLAINTEXT_PASSWORDS', true);