<?php
// public/api/products.php
header('Content-Type: application/json');
session_start();

// Database Connection
$host = "localhost";
$port = "5432";
$dbname = "postgres";
$user = "postgres";
$password = "skittle3699"; 

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// GET -> List products
if ($method === 'GET') {
    $stmt = $pdo->query("SELECT stockid, name, unitprice, quantity, category FROM stock ORDER BY stockid ASC");
    echo json_encode($stmt->fetchAll());
    exit;
}

// ADMIN CHECK
$currentUser = $_SESSION['user'] ?? null;
if (!$currentUser || ($currentUser['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden: Admin access only']);
    exit;
}

// POST -> Add new product
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['name']) || !isset($input['price'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Name and Price are required']);
        exit;
    }

    // Generate Stock ID
    $stmt = $pdo->query("SELECT stockid FROM stock ORDER BY stockid DESC LIMIT 1");
    $lastId = $stmt->fetchColumn();
    $newId = $lastId ? 'S' . str_pad((int)substr($lastId, 1) + 1, 6, '0', STR_PAD_LEFT) : 'S000001';

    try {
        $stmt = $pdo->prepare("
            INSERT INTO stock (stockid, name, unitprice, quantity, category, adminid)
            VALUES (:id, :name, :price, :qty, :cat, :admin)
        ");
        
        $stmt->execute([
            ':id'    => $newId,
            ':name'  => $input['name'],
            ':price' => $input['price'],
            ':qty'   => $input['stock'] ?? 0,
            ':cat'   => $input['category'] ?? 'General', // Now uses input or defaults to General
            ':admin' => $currentUser['id']
        ]);
        
        echo json_encode(['success' => true, 'stockid' => $newId]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// PUT -> Edit product
if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['stockid'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Product ID is missing']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE stock 
            SET name = :name, unitprice = :price, quantity = :qty, category = :cat
            WHERE stockid = :id
        ");
        
        $stmt->execute([
            ':id'    => $input['stockid'],
            ':name'  => $input['name'],
            ':price' => $input['price'],
            ':qty'   => $input['stock'],
            ':cat'   => $input['category'] ?? 'General' // Update category
        ]);
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// DELETE -> Remove product
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
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
?>