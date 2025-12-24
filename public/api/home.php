<?php
// public/api/dashboard.php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . "/../../config/db.php";

$dbStatus = 'connected';

try {
    // 1. Total Products
    $productCount = $pdo->query("SELECT COUNT(*) FROM stock")->fetchColumn();

    // 2. Total Members
    $memberCount = $pdo->query("SELECT COUNT(*) FROM member")->fetchColumn();

    // 3. Total Transactions
    $txCount = $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();

    // 4. Low Stock (< 10)
    $lowStockCount = $pdo
        ->query("SELECT COUNT(*) FROM stock WHERE quantity < 10")
        ->fetchColumn();

    echo json_encode([
        'products'     => (int)$productCount,
        'members'      => (int)$memberCount,
        'transactions' => (int)$txCount,
        'low_stock'    => (int)$lowStockCount,
        'db_status'    => $dbStatus,
        'checked_at'   => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    echo json_encode([
        'products'     => 0,
        'members'      => 0,
        'transactions' => 0,
        'low_stock'    => 0,
        'db_status'    => 'disconnected',
        'checked_at'   => date('Y-m-d H:i:s')
    ]);
}
