<?php
// public/api/products.php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . "/../../config/db.php";

$method = $_SERVER['REQUEST_METHOD'];

/* =========================
   GET – List products
========================= */
if ($method === 'GET') {
    $stmt = $pdo->query("
        SELECT
            stockid,
            name,
            unitprice,
            quantity,
            category,
            image
        FROM stock
        ORDER BY stockid ASC
    ");
    echo json_encode($stmt->fetchAll());
    exit;
}

/* =========================
   Admin check
========================= */
$currentUser = $_SESSION['user'] ?? null;

if (!$currentUser || ($currentUser['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Admin only']);
    exit;
}

/* =========================
   POST – Add OR Edit product
   (used for image upload)
========================= */
if ($method === 'POST') {

    $mode = $_POST['mode'] ?? 'add';

    /* ---------- IMAGE UPLOAD ---------- */
    $imagePath = null;

    if (!empty($_FILES['image']['name'])) {

        $allowed = ['image/jpeg','image/png','image/webp'];
        if (!in_array($_FILES['image']['type'], $allowed)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid image type']);
            exit;
        }

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('product_') . '.' . $ext;

        $uploadDir = __DIR__ . '/../uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            $uploadDir . $filename
        );

        $imagePath = "/uploads/products/" . $filename;
    }

    /* ---------- EDIT PRODUCT ---------- */
    if ($mode === 'edit') {

        if (empty($_POST['stockid'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Product ID is missing']);
            exit;
        }

        // Update product (with or without image)
        if ($imagePath) {
            $stmt = $pdo->prepare("
                UPDATE stock
                SET name = :name,
                    unitprice = :price,
                    quantity = :qty,
                    category = :cat,
                    image = :image
                WHERE stockid = :id
            ");
        } else {
            $stmt = $pdo->prepare("
                UPDATE stock
                SET name = :name,
                    unitprice = :price,
                    quantity = :qty,
                    category = :cat
                WHERE stockid = :id
            ");
        }

        $params = [
            ':id'    => $_POST['stockid'],
            ':name'  => $_POST['name'],
            ':price' => $_POST['price'],
            ':qty'   => $_POST['stock'],
            ':cat'   => $_POST['category'] ?? 'General'
        ];

        if ($imagePath) {
            $params[':image'] = $imagePath;
        }

        $stmt->execute($params);

        echo json_encode(['success' => true]);
        exit;
    }

    /* ---------- ADD PRODUCT ---------- */
    if (empty($_POST['name']) || !isset($_POST['price'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Name and Price are required']);
        exit;
    }

    // Generate Stock ID
    $stmt = $pdo->query("SELECT stockid FROM stock ORDER BY stockid DESC LIMIT 1");
    $lastId = $stmt->fetchColumn();
    $newId = $lastId
        ? 'S' . str_pad((int)substr($lastId, 1) + 1, 6, '0', STR_PAD_LEFT)
        : 'S000001';

    $stmt = $pdo->prepare("
        INSERT INTO stock
        (stockid, name, unitprice, quantity, category, adminid, image)
        VALUES
        (:id, :name, :price, :qty, :cat, :admin, :image)
    ");

    $stmt->execute([
        ':id'    => $newId,
        ':name'  => $_POST['name'],
        ':price' => $_POST['price'],
        ':qty'   => $_POST['stock'] ?? 0,
        ':cat'   => $_POST['category'] ?? 'General',
        ':admin' => $currentUser['id'],
        ':image' => $imagePath
    ]);

    echo json_encode(['success' => true, 'stockid' => $newId]);
    exit;
}

/* =========================
   DELETE – Remove product
========================= */
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Product ID is missing']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM stock WHERE stockid = :id");
        $stmt->execute([':id' => $id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Cannot delete product (likely in use).']);
    }
    exit;
}
