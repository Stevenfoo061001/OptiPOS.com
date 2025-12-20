<?php
// public/api/login.php
header('Content-Type: application/json');
session_start();

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['username']) || !isset($input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing credentials']);
    exit;
}

// Database config (same as index.php)
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
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$username = trim($input['username']);
$plainPassword = $input['password'];

// Query admin table
$stmt = $pdo->prepare("
    SELECT adminid, name, loginpassword
    FROM admin
    WHERE name = :username
    LIMIT 1
");
$stmt->execute([':username' => $username]);
$userRow = $stmt->fetch();

if (!$userRow) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid username or password']);
    exit;
}

/*
|--------------------------------------------------------------------------
| PASSWORD CHECK
|--------------------------------------------------------------------------
*/

// ✅ Plain text version (simple)
if ($plainPassword !== $userRow['loginpassword']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid username or password']);
    exit;
}

// Set session
$_SESSION['user'] = [
    'id'   => $userRow['adminid'],
    'username' => $userRow['name'],
    'name' => $userRow['name'],
    'role' => 'admin'
];

// Success response
echo json_encode([
    'success' => true,
    'user' => $_SESSION['user']
]);
