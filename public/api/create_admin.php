<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . "/../../config/db.php";

/* ========= 1. 只能创建一次 admin ========= */
$adminCount = (int) $pdo
    ->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")
    ->fetchColumn();

if ($adminCount > 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Admin already exists'
    ]);
    exit;
}

/* ========= 2. 读取前端数据 ========= */
$data = json_decode(file_get_contents("php://input"), true);

$name     = trim($data['name'] ?? '');
$phone    = trim($data['phone'] ?? '');
$email    = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (strlen($phone) >= 15) {
  echo json_encode([
    "success" => false,
    "error" => "Phone number must be less than 15 digits"
  ]);
  exit;
}

/* ========= 3. 必填校验（对齐 users.js） ========= */
if ($name === '' || $phone === '' || $email === '' || $password === '') {
    echo json_encode([
        'success' => false,
        'error' => 'All fields are required'
    ]);
    exit;
}

/* ========= 4. Email 格式校验 ========= */
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid email format'
    ]);
    exit;
}

/* ========= 5. Password 长度校验 ========= */
if (strlen($password) >= 6) {
    echo json_encode([
        'success' => false,
        'error' => 'Password must be at least 6 characters'
    ]);
    exit;
}

/* ========= 6. Email 唯一校验 ========= */
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
$stmt->execute([$email]);

if ((int)$stmt->fetchColumn() > 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Email already exists'
    ]);
    exit;
}

/* ========= 7. 生成 admin userid ========= */
$userid = 'A' . substr(uniqid(), -6);

/* ========= 8. 密码加密（重要） ========= */
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

/* ========= 9. 插入 admin ========= */
$stmt = $pdo->prepare("
    INSERT INTO users (userid, name, phone, email, password, role)
    VALUES (?, ?, ?, ?, ?, 'admin')
");

$stmt->execute([
    $userid,
    $name,
    $phone,
    $email,
    $hashedPassword
]);

/* ========= 10. 自动登录 ========= */
$_SESSION['user'] = [
    'userid' => $userid,
    'name'   => $name,
    'role'   => 'admin'
];

echo json_encode(['success' => true]);
