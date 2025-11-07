<?php
// =====================================================
// Configuration File
// =====================================================

// error reporting - !!! remove for production !!!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- URL and Path Config ---
define('BASE_URL', '/cdms/public'); 

// --- Database Configuration ---
//  CHANGE THESE SETTINGS IF PUSHED TO SERVER
define('DB_HOST', 'localhost');
define('DB_NAME', 'cdms_db');
define('DB_USER', 'root');
define('DB_PASS', ''); 

// --- Misc ---
define('APP_NAME', 'CDMS');