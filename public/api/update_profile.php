<?php
require_once __DIR__ . '/../../config/db.php';

session_start();
header('Content-Type: application/json');

if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');

if ($email === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Email is required']);
    exit;
}

$userId = $_SESSION['user']['id'];

$sql = "
    UPDATE users
    SET email = :email,
        phone = :phone
    WHERE userid = :id
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    'email' => $email,
    'phone' => $phone,
    'id'    => $userId
]);

echo json_encode(['success' => true]);
