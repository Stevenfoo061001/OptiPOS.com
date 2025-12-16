<?php
// public/api/products.php
header('Content-Type: application/json');
session_start();

// path to data file
$dataFile = __DIR__ . '/../../data/products.json';
if (!file_exists($dataFile)) file_put_contents($dataFile, json_encode([]));

// load products
$raw = file_get_contents($dataFile);
$products = json_decode($raw, true) ?: [];

$method = $_SERVER['REQUEST_METHOD'];

// helper: save products
function save_products($file, $arr) {
    file_put_contents($file, json_encode(array_values($arr), JSON_PRETTY_PRINT));
}

// GET -> list products
if ($method === 'GET') {
    echo json_encode($products);
    exit;
}

// All write operations require admin
$user = $_SESSION['user'] ?? null;
if (!$user || ($user['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden: admin only']);
    exit;
}

// POST -> add new product
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['name'])) {
        http_response_code(400);
        echo json_encode(['error'=>'Invalid payload']);
        exit;
    }
    $ids = array_column($products, 'id');
    $nid = $ids ? max($ids)+1 : 1;
    $new = [
      'id' => $nid,
      'sku' => $input['sku'] ?? '',
      'name' => $input['name'],
      'price' => (float)($input['price'] ?? 0),
      'stock' => (int)($input['stock'] ?? 0),
      'reorder' => (int)($input['reorder'] ?? 0)
    ];
    $products[] = $new;
    save_products($dataFile, $products);
    echo json_encode($new);
    exit;
}

// PUT -> edit existing product (expects JSON body with id)
if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['error'=>'Missing id']);
        exit;
    }
    $id = (int)$input['id'];
    $found = false;
    foreach ($products as &$p) {
        if ((int)$p['id'] === $id) {
            // update fields if provided
            if (isset($input['sku'])) $p['sku'] = $input['sku'];
            if (isset($input['name'])) $p['name'] = $input['name'];
            if (isset($input['price'])) $p['price'] = (float)$input['price'];
            if (isset($input['stock'])) $p['stock'] = (int)$input['stock'];
            if (isset($input['reorder'])) $p['reorder'] = (int)$input['reorder'];
            $found = true;
            $updated = $p;
            break;
        }
    }
    if (!$found) {
        http_response_code(404);
        echo json_encode(['error'=>'Product not found']);
        exit;
    }
    save_products($dataFile, $products);
    echo json_encode($updated);
    exit;
}

// DELETE -> remove product
if ($method === 'DELETE') {
    // accept id via query param ?id= or JSON body {id:...}
    $id = null;
    if (isset($_GET['id'])) $id = (int)$_GET['id'];
    else {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input && isset($input['id'])) $id = (int)$input['id'];
    }
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error'=>'Missing id']);
        exit;
    }
    $found = false;
    foreach ($products as $k => $p) {
        if ((int)$p['id'] === $id) {
            $found = true;
            unset($products[$k]);
            break;
        }
    }
    if (!$found) {
        http_response_code(404);
        echo json_encode(['error'=>'Product not found']);
        exit;
    }
    save_products($dataFile, $products);
    echo json_encode(['success'=>true]);
    exit;
}

http_response_code(405);
echo json_encode(['error'=>'Method not allowed']);
