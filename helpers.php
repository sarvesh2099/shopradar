<?php
// ===== helpers.php =====
if (session_status() === PHP_SESSION_NONE) session_start();

function require_login() {
  if (!isset($_SESSION['merchant_id'])) {
    header('Location: shopradar-v2.php'); // your existing login page
    exit;
  }
}
function login_merchant(array $m) {
  $_SESSION['merchant_id']   = (int)$m['id'];
  $_SESSION['merchant_name'] = $m['name'];
  $_SESSION['merchant_email'] = $m['email'];
}
