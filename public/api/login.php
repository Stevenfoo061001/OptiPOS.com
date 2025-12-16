<?php
// public/api/login.php
header('Content-Type: application/json');
session_start();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['username']) || !isset($input['password'])) {
  http_response_code(400);
  echo json_encode(['success'=>false,'error'=>'Missing credentials']);
  exit;
}

$usersFile = __DIR__ . '/../../data/users.json';
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

$username = trim($input['username']);
$password = $input['password'];

foreach ($users as $u) {
  if ($u['username'] === $username && $u['password'] === $password) {
    // set session
    $_SESSION['user'] = [
      'id' => $u['id'],
      'username' => $u['username'],
      'role' => $u['role'],
      'name' => $u['name'] ?? $u['username']
    ];
    echo json_encode(['success'=>true,'user'=>$_SESSION['user']]);
    exit;
  }
}

http_response_code(401);
echo json_encode(['success'=>false,'error'=>'Invalid username or password']);
