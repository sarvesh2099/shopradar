<?php
session_start();
unset($_SESSION['merchant_id'], $_SESSION['merchant_name'], $_SESSION['merchant_email']);
session_destroy();
header('Location: shopradar-v2.php');
exit;
