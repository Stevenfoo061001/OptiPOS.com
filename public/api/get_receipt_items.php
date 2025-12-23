<?php
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

// 1. Check orderid
if (!isset($_GET['orderid'])) {
    echo json_encode([]);
    exit;
}

$orderid = $_GET['orderid'];

// 2. Query items for this order
$sql = "
    SELECT
    s.name,
    oi.unitprice,
    oi.quantity
    FROM order_item oi
    JOIN stock s ON oi.stockid = s.stockid
    WHERE oi.orderid = :orderid
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['orderid' => $orderid]);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Return JSON
echo json_encode($items);
