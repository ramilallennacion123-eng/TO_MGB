<?php
// Local Network Configuration

// Base URL - Change this to your IP when accessing from network
define('BASE_URL', '/TO_MGB');

// Database Settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'to_inventory');
define('DB_USER', 'root'); 
define('DB_PASS', '');

//time(10 hours)
define('SESSION_LIFETIME', 36000);

// Error Logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Application Settings
define('APP_NAME', 'TO_MGB - Travel Order Management');
define('UPLOAD_MAX_SIZE', 5242880); // 5MB
?>