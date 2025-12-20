<?php
// public/api/dashboard.php
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
        [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// 1. Total Products
$stmt = $pdo->query("SELECT COUNT(*) FROM stock");
$productCount = $stmt->fetchColumn();

// 2. Total Members
$stmt = $pdo->query("SELECT COUNT(*) FROM member");
$memberCount = $stmt->fetchColumn();

// 3. Total Transactions
$stmt = $pdo->query("SELECT COUNT(*) FROM transactions");
$txCount = $stmt->fetchColumn();

// 4. Low Stock Items (Assumed < 10 since no 'reorder' column exists in schema)
$stmt = $pdo->query("SELECT COUNT(*) FROM stock WHERE quantity < 10");
$lowStockCount = $stmt->fetchColumn();

echo json_encode([
    'products' => $productCount,
    'members' => $memberCount,
    'transactions' => $txCount,
    'low_stock' => $lowStockCount
]);
?>