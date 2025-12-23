<?php
session_start();
header("Content-Type: application/json");

define("BASE_PATH", dirname(__DIR__)); // /public

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
  echo json_encode(["success" => false, "error" => "Cart empty"]);
  exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$paymentMethod = $data["payment"] ?? null;
$memberId = $data["memberId"] ?? null;
$discount = floatval($data["discount"] ?? 0);
$cashReceived = floatval($data["cashReceived"] ?? 0);
$change = floatval($data["change"] ?? 0);
$LOW_STOCK_LIMIT = 10;


if (!$paymentMethod) {
  echo json_encode(["success" => false, "error" => "Payment method missing"]);
  exit;
}

/* ---------- STOCK VALIDATION ---------- */
$productsFile = BASE_PATH . "/data/products.json";

if (!file_exists($productsFile)) {
  echo json_encode(["success" => false, "error" => "Products data missing"]);
  exit;
}

$products = json_decode(file_get_contents($productsFile), true);

foreach ($cart as $cartItem) {
  $found = false;

  foreach ($products as $product) {
    if (strtolower($product['name']) === strtolower($cartItem['name'])) {
      $found = true;

      if (intval($product['quantity']) < intval($cartItem['qty'])) {
        echo json_encode([
          "success" => false,
          "error" => "Insufficient stock for {$product['name']}"
        ]);
        exit;
      }
    }
  }

  if (!$found) {
    echo json_encode([
      "success" => false,
      "error" => "Product not found: {$cartItem['name']}"
    ]);
    exit;
  }
}


/* ---------- CALCULATE TOTAL ---------- */
$subtotal = 0;
foreach ($cart as $item) {
  $subtotal += $item['price'] * $item['qty'];
}

$tax = $subtotal * 0.06;
$total = max(0, $subtotal + $tax - $discount);

/* ---------- SAVE TRANSACTION ---------- */
$transactionFile = BASE_PATH . "/data/transactions.json";
$transactions = file_exists($transactionFile)
  ? json_decode(file_get_contents($transactionFile), true)
  : [];

$transaction = [
  "id" => "TRX" . time(),
  "date" => date("Y-m-d H:i:s"),
  "items" => array_values($cart),
  "subtotal" => $subtotal,
  "tax" => $tax,
  "discount" => $discount,
  "total" => $total,
  "payment" => $paymentMethod,
  "memberId" => $memberId,
  "cashReceived" => $paymentMethod === "Cash" ? $cashReceived : null,
  "change" => $paymentMethod === "Cash" ? $change : null
];


$transactions[] = $transaction;
file_put_contents($transactionFile, json_encode($transactions, JSON_PRETTY_PRINT));

/* ---------- DEDUCT MEMBER POINTS ---------- */
/* ---------- DEDUCT MEMBER POINTS (MAX 1000) ---------- */
if ($memberId && $discount > 0) {

  $membersFile = BASE_PATH . "/data/members.json";

  if (file_exists($membersFile)) {
    $members = json_decode(file_get_contents($membersFile), true);
    if (!is_array($members)) $members = [];

    foreach ($members as &$m) {
      if ($m['id'] === $memberId) {

        // points requested by discount
        $requestedPoints = intval($discount / 0.01);

        // enforce max usage and available balance
        $pointsUsed = min(
          1000,              // max points per transaction
          $requestedPoints,  // requested
          $m['points']       // member balance
        );

        // deduct points
        $m['points'] -= $pointsUsed;

        break;
      }
    }

    

    file_put_contents(
      $membersFile,
      json_encode($members, JSON_PRETTY_PRINT)
    );
  }
}

/* ---------- EARN MEMBER POINTS ---------- */
if ($memberId) {

  $membersFile = BASE_PATH . "/data/members.json";

  if (file_exists($membersFile)) {
    $members = json_decode(file_get_contents($membersFile), true);
    if (!is_array($members)) $members = [];

    foreach ($members as &$m) {
      if ($m['id'] === $memberId) {

        // earn 1 point per RM 1 spent (after discount)
        $pointsEarned = floor($total);

        $m['points'] += $pointsEarned;

        break;
      }
    }

    file_put_contents(
      $membersFile,
      json_encode($members, JSON_PRETTY_PRINT)
    );
  }
}

/* ---------- AUTO DEDUCT PRODUCT STOCK ---------- */
$productsFile = BASE_PATH . "/data/products.json";

if (file_exists($productsFile)) {
  $products = json_decode(file_get_contents($productsFile), true);

  if (is_array($products)) {

    foreach ($cart as $cartItem) {
      foreach ($products as &$product) {

        // Match by product name OR stockId if you add it later
        if (
          strtolower($product['name']) === strtolower($cartItem['name'])
        ) {
          $product['quantity'] = max(
            0,
            intval($product['quantity']) - intval($cartItem['qty'])
          );
          break;
        }
      }
      unset($product);
    }

    file_put_contents(
      $productsFile,
      json_encode($products, JSON_PRETTY_PRINT)
    );
  }
}


/* ---------- CLEAR CART ---------- */
$_SESSION['cart'] = [];

echo json_encode(["success" => true]);
