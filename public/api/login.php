<?php
session_start();
$data = json_decode(file_get_contents("php://input"), true);

if ($data['username'] === 'admin' && $data['password'] === '1234') {
    $_SESSION['user'] = [
        'id' => 'C0001',
        'name' => 'Store Manager'
    ];
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
