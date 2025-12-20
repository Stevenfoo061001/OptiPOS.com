<?php
// public/api/transactions.php
header('Content-Type: application/json');
session_start();

// Check if user is logged in
if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

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

if ($method === 'GET') {
    // We join transactions with orders to get the total price and tax info
    // We also join cashier to see who handled it
    $sql = "
        SELECT 
            t.transactionid,
            t.paymentdate,
            t.paymentmethod,
            t.amountpaid,
            t.status,
            o.orderid,
            o.grandprice as total_order_value,
            c.name as cashier_name
        FROM transactions t
        JOIN orders o ON t.orderid = o.orderid
        LEFT JOIN cashier c ON t.cashierid = c.cashierid
        ORDER BY t.paymentdate DESC, t.transactionid DESC
        LIMIT 50
    ";

    try {
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll();
        echo json_encode($data);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?>