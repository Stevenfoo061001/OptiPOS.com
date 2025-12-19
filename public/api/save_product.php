<?php
session_start();
header("Content-Type: application/json");

define("BASE_PATH", dirname(__DIR__)); // /public

$data = json_decode(file_get_contents("php://input"), true);

$mode = $data['mode'] ?? null;
$stockId = $data['stockId'] ?? null;
$name = trim($data['name'] ?? '');
$unitPrice = floatval($data['unitPrice'] ?? 0);
$quantity = intval($data['quantity'] ?? 0);
$category = trim($data['category'] ?? '');

if (!$mode || !$name) {
  echo json_encode(["success" => false, "error" => "Invalid data"]);
  exit;
}

$productsFile = BASE_PATH . "/data/products.json";
$products = file_exists($productsFile)
  ? json_decode(file_get_contents($productsFile), true)
  : [];

if (!is_array($products)) {
  $products = [];
}

/* ================= ADD PRODUCT ================= */
if ($mode === "add") {

  // Generate new Stock ID
  $newId = "S" . str_pad(count($products) + 1, 6, "0", STR_PAD_LEFT);

  $products[] = [
    "stockId"   => $newId,
    "name"      => $name,
    "unitPrice" => $unitPrice,
    "quantity"  => $quantity,
    "category"  => $category
  ];
}

/* ================= EDIT PRODUCT ================= */
if ($mode === "edit") {

  foreach ($products as &$p) {
    if ($p['stockId'] === $stockId) {
      $p['name'] = $name;
      $p['unitPrice'] = $unitPrice;
      $p['quantity'] = $quantity;
      $p['category'] = $category;
      break;
    }
  }
  unset($p);
}

file_put_contents($productsFile, json_encode($products, JSON_PRETTY_PRINT));

echo json_encode(["success" => true]);
