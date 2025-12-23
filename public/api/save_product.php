<?php
// public/api/save_product.php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../config/db.php';

// ---- 权限检查（只允许 admin）----
$user = $_SESSION['user'] ?? null;
if (!$user || ($user['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Admin only']);
    exit;
}

// ---- 读取输入 ----
$data = json_decode(file_get_contents('php://input'), true);

$mode     = $data['mode'] ?? '';
$stockId  = trim($data['stockId'] ?? '');
$name     = trim($data['name'] ?? '');
$price    = (float)($data['unitPrice'] ?? 0);
$qty      = (int)($data['quantity'] ?? 0);
$category = trim($data['category'] ?? 'General');

// ---- 基本验证 ----
if ($name === '' || $price <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid product name or price']);
    exit;
}

try {

    // =========================
    // ADD PRODUCT
    // =========================
    if ($mode === 'add') {

        // 生成 stockid（S000001）
        $stmt = $pdo->query("
            SELECT stockid 
            FROM stock 
            ORDER BY stockid DESC 
            LIMIT 1
        ");
        $lastId = $stmt->fetchColumn();

        if ($lastId) {
            $num = (int)substr($lastId, 1);
            $newId = 'S' . str_pad($num + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newId = 'S000001';
        }

        $stmt = $pdo->prepare("
            INSERT INTO stock (stockid, name, unitprice, quantity, category)
            VALUES (:id, :name, :price, :qty, :cat)
        ");
        $stmt->execute([
            ':id'    => $newId,
            ':name'  => $name,
            ':price' => $price,
            ':qty'   => $qty,
            ':cat'   => $category
        ]);

        echo json_encode([
            'success' => true,
            'stockid' => $newId
        ]);
        exit;
    }

    // =========================
    // EDIT PRODUCT
    // =========================
    if ($mode === 'edit') {

        if ($stockId === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Missing stock ID']);
            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE stock
            SET 
                name = :name,
                unitprice = :price,
                quantity = :qty,
                category = :cat
            WHERE stockid = :id
        ");
        $stmt->execute([
            ':id'    => $stockId,
            ':name'  => $name,
            ':price' => $price,
            ':qty'   => $qty,
            ':cat'   => $category
        ]);

        echo json_encode(['success' => true]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Invalid mode']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
