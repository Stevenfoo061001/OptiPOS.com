<?php
require_once __DIR__ . '/../../config/db.php';

session_start();
header('Content-Type: application/json');

if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user']['id'];

$sql = "
    SELECT
        userid,
        name,
        email,
        phone,
        role
    FROM users
    WHERE userid = :id
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit;
}

$roleDisplay = $user['role'] === 'admin' ? 'Admin' : 'Cashier';

echo json_encode([
    'id'           => $user['userid'],
    'name'         => $user['name'],
    'email'        => $user['email'],
    'phone'        => $user['phone'],
    'role'         => $user['role'],
    'role_display' => $roleDisplay
]);
