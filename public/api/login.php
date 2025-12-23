<?php
session_start();
header('Content-Type: application/json');

$pdo = new PDO(
    "pgsql:host=localhost;port=5432;dbname=postgres",
    "postgres",
    "061001",
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

$data = json_decode(file_get_contents("php://input"), true);

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if ($username === '' || $password === '') {
    echo json_encode(['success' => false, 'error' => 'Missing input']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT userid, name, password, role
    FROM users
    WHERE name = ?
");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && $password === $user['password']) {

    $_SESSION['user'] = [
        'id'   => $user['userid'],
        'name' => $user['name'],
        'role' => $user['role']
    ];

    echo json_encode([
        'success' => true,
        'role' => $user['role']
    ]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid login']);
