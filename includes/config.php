<?php
// includes/config.php
// Database and base URL configuration
error_reporting(E_ALL);
ini_set('display_errors', 1);

// IMPORTANT: adjust BASE_URL to match your folder name exactly
define('BASE_URL', 'http://localhost:8080/FYP_3DVR_House/');

// DB credentials for local XAMPP (change if you set a password)
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';            // default XAMPP root has empty password
$DB_NAME = 'fyp_3dvr_house';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create mysqli connection
$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

// Check connection
if ($mysqli->connect_errno) {
    // In development show error (production: log and show generic message)
    error_log("MySQL connection error ({$mysqli->connect_errno}): {$mysqli->connect_error}");
    // Optionally set $mysqli to null so pages can handle it gracefully
    $mysqli = null;
} else {
    // Make sure connection uses utf8mb4
    $mysqli->set_charset('utf8mb4');
}

