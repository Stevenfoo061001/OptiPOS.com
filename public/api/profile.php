<?php
// public/api/profile.php
header('Content-Type: application/json');
session_start();

// Check if logged in
if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
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
        [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$currentUser = $_SESSION['user'];
$role = $currentUser['role']; // 'admin' or 'cashier'
$id = $currentUser['id'];     // e.g., 'A001' or 'C001'

// Query the correct table based on role
if ($role === 'admin') {
    $stmt = $pdo->prepare("SELECT adminid as id, name, phone, email, 'Admin' as role_display FROM admin WHERE adminid = :id");
} else {
    $stmt = $pdo->prepare("SELECT cashierid as id, name, phone, email, 'Cashier' as role_display FROM cashier WHERE cashierid = :id");
}

$stmt->execute([':id' => $id]);
$data = $stmt->fetch();

if ($data) {
    echo json_encode($data);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'User profile not found']);
}
?>