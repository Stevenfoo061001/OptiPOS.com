<?php
// public/api/dashboard.php
header('Content-Type: application/json');
session_start();

// Database Connection
$host = "localhost";
$port = "5432";
$dbname = "postgres";
$user = "postgres";
$password = "061001"; 

$dbStatus = 'disconnected';


try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    $dbStatus = 'connected';
} catch (PDOException $e) {
    echo json_encode([
        'products'     => 0,
        'members'      => 0,
        'transactions' => 0,
        'low_stock'    => 0,
        'db_status'    => 'disconnected',
        'checked_at'   => date('Y-m-d H:i:s')
    ]);
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
    'products'     => (int)$productCount,
    'members'      => (int)$memberCount,
    'transactions' => (int)$txCount,
    'low_stock'    => (int)$lowStockCount,
    'db_status'    => $dbStatus,
    'checked_at'   => date('Y-m-d H:i:s')
]);
?>