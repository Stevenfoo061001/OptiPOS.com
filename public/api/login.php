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

// Helper to verify password (supports hashed passwords and plain text)
function verifyPassword($plain, $stored) {
    if (!$stored) return false;
    // If stored looks like a bcrypt hash, use password_verify
    if (preg_match('/^\$2[ayb]\$[0-9]{2}\$/', $stored)) {
        return password_verify($plain, $stored);
    }
    // Fallback to plain text comparison
    return $plain === $stored;
}

// Try to find admin
$stmt = $pdo->prepare("
    SELECT adminid AS id, name, loginpassword
    FROM admin
    WHERE name = :username
    LIMIT 1
");
$stmt->execute([':username' => $username]);
$admin = $stmt->fetch();

// Try to find cashier
$stmt = $pdo->prepare("
    SELECT cashierid AS id, name, loginpassword
    FROM cashier
    WHERE name = :username
    LIMIT 1
");
$stmt->execute([':username' => $username]);
$cashier = $stmt->fetch();

// Determine which account to use (prefer admin if both exist)
$account = null;
$role = null;
if ($admin) {
    $account = $admin;
    $role = 'admin';
} elseif ($cashier) {
    $account = $cashier;
    $role = 'cashier';
}

// Validate password and set session
if ($account && verifyPassword($plainPassword, $account['loginpassword'])) {
    $_SESSION['user'] = [
        'id' => $account['id'],
        'name' => $account['name'],
        'role' => $role
    ];
    echo json_encode(['success' => true, 'user' => $_SESSION['user']]);
    exit;
}

// Authentication failed
http_response_code(401);
echo json_encode(['success' => false, 'error' => 'Invalid username or password']);
exit;
