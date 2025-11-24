<?php
// config.php - Configuration for Paynow and Database

// Set timezone
date_default_timezone_set('Africa/Harare');

// Database configuration from environment variables
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'dssm_payments');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Paynow configuration from environment variables
define('PAYNOW_ID', getenv('PAYNOW_ID') ?: 'your_paynow_id');
define('PAYNOW_KEY', getenv('PAYNOW_KEY') ?: 'your_paynow_key');

// URLs
define('RETURN_URL', getenv('RETURN_URL') ?: 'https://yourapp.com/success');
define('RESULT_URL', getenv('RESULT_URL') ?: 'https://yourapp.com/paynow/callback.php');
?>