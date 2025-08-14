<?php
require_once 'config.php';
require_once 'helpers.php';
require_login();

header('Content-Type: application/json');
$mid = (int)$_SESSION['merchant_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

$uploadDir = __DIR__ . '/uploads/';
$publicDir = 'uploads/'; // for building URLs
if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0755, true); }

function out($arr){ echo json_encode($arr); exit; }

function handleImageUpload($field){
  global $uploadDir;
  if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;

  // Validation
  $allowed = ['image/png'=>'png','image/jpeg'=>'jpg','image/webp'=>'webp'];
  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime  = finfo_file($finfo, $_FILES[$field]['tmp_name']);
  finfo_close($finfo);
  if (!isset($allowed[$mime])) return ['error'=>'Only PNG, JPG or WebP allowed'];

  if ($_FILES[$field]['size'] > 2*1024*1024) return ['error'=>'Image too large (max 2MB)'];

  $ext = $allowed[$mime];
  $name = uniqid('prod_', true) . '.' . $ext;
  if (!move_uploaded_file($_FILES[$field]['tmp_name'], $uploadDir . $name)) {
    return ['error'=>'Failed to save image'];
  }
  return ['file'=>$name];
}

if ($action === 'list') {
  $stmt = $mysqli->prepare('SELECT id, product_name, image, category, price, quantity, in_stock FROM products WHERE merchant_id=? ORDER BY id DESC');
  $stmt->bind_param('i', $mid);
  $stmt->execute();
  $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  out($rows);
}

if ($action === 'get') {
  $id = (int)($_GET['id'] ?? 0);
  $stmt = $mysqli->prepare('SELECT id, product_name, image, category, price, quantity, in_stock FROM products WHERE id=? AND merchant_id=?');
  $stmt->bind_param('ii', $id, $mid);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  out($row ?: []);
}

if ($action === 'add') {
  $name = trim($_POST['product_name'] ?? '');
  $cat  = ($_POST['category'] === 'Other') ? trim($_POST['custom_category'] ?? '') : trim($_POST['category'] ?? '');
  $price = (float)($_POST['price'] ?? 0);
  $qty   = (int)($_POST['quantity'] ?? 0);
  $stock = (int)($_POST['in_stock'] ?? 0);

  if ($name==='' || $cat==='') out(['ok'=>false,'message'=>'Please fill all fields']);

  $img = handleImageUpload('image');
  if (is_array($img) && isset($img['error'])) out(['ok'=>false,'message'=>$img['error']]);
  $imgFile = is_array($img) ? ($img['file'] ?? null) : null;

  $stmt = $mysqli->prepare('INSERT INTO products (merchant_id, product_name, image, category, price, quantity, in_stock) VALUES (?,?,?,?,?,?,?)');
  $stmt->bind_param('isssdii', $mid, $name, $imgFile, $cat, $price, $qty, $stock);
  $ok = $stmt->execute();
  out(['ok'=>$ok,'message'=>$ok?'Product added':'Failed to add product']);
}

if ($action === 'update') {
  $id = (int)($_POST['id'] ?? 0);
  $name = trim($_POST['product_name'] ?? '');
  $cat  = ($_POST['category'] === 'Other') ? trim($_POST['custom_category'] ?? '') : trim($_POST['category'] ?? '');
  $price = (float)($_POST['price'] ?? 0);
  $qty   = (int)($_POST['quantity'] ?? 0);
  $stock = (int)($_POST['in_stock'] ?? 0);

  if ($id<=0) out(['ok'=>false,'message'=>'Invalid ID']);

  // Upload new image if any
  $img = handleImageUpload('image');
  if (is_array($img) && isset($img['error'])) out(['ok'=>false,'message'=>$img['error']]);

  if (is_array($img) && isset($img['file'])) {
    // delete old image if exists
    $old = $mysqli->prepare('SELECT image FROM products WHERE id=? AND merchant_id=?');
    $old->bind_param('ii', $id, $mid);
    $old->execute();
    $oldFile = $old->get_result()->fetch_column();
    if ($oldFile && is_file($uploadDir.$oldFile)) @unlink($uploadDir.$oldFile);

    $stmt = $mysqli->prepare('UPDATE products SET product_name=?, image=?, category=?, price=?, quantity=?, in_stock=? WHERE id=? AND merchant_id=?');
    $stmt->bind_param('sssdi iii', $name, $img['file'], $cat, $price, $qty, $stock, $id, $mid);
  } else {
    $stmt = $mysqli->prepare('UPDATE products SET product_name=?, category=?, price=?, quantity=?, in_stock=? WHERE id=? AND merchant_id=?');
    $stmt->bind_param('ssdiiii', $name, $cat, $price, $qty, $stock, $id, $mid);
  }

  $ok = $stmt->execute();
  out(['ok'=>$ok,'message'=>$ok?'Updated':'Update failed']);
}

if ($action === 'delete') {
  $id = (int)($_POST['id'] ?? 0);
  // delete image file
  $sel = $mysqli->prepare('SELECT image FROM products WHERE id=? AND merchant_id=?');
  $sel->bind_param('ii', $id, $mid);
  $sel->execute();
  $file = $sel->get_result()->fetch_column();
  if ($file) {
    $path = $uploadDir.$file;
    if (is_file($path)) @unlink($path);
  }

  $stmt = $mysqli->prepare('DELETE FROM products WHERE id=? AND merchant_id=?');
  $stmt->bind_param('ii', $id, $mid);
  $ok = $stmt->execute();
  out(['ok'=>$ok,'message'=>$ok?'Deleted':'Delete failed']);
}

out(['ok'=>false,'message'=>'Unknown action']);
