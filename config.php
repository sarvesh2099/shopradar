<?php
// ===== config.php =====
$DB_HOST = 'localhost';
$DB_USER = 'root';      // change on your server
$DB_PASS = '';          // change on your server
$DB_NAME = 'shopradar'; // must match SQL above

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
  http_response_code(500);
  die('DB connection failed: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

if (session_status() === PHP_SESSION_NONE) { session_start(); }
