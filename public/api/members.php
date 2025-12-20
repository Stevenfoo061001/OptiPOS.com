<?php
// public/api/members.php
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
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// GET: Fetch all members
if ($method === 'GET') {
    // FIXED: Table name is 'member', not 'members'
    $stmt = $pdo->query("
        SELECT memberid, name, phone, email,
               points, dateissued, dateexpired
        FROM member 
        ORDER BY memberid ASC
    ");
    echo json_encode($stmt->fetchAll());
    exit;
}

// POST: Add new member
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || empty($input['name'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Name is required']);
        exit;
    }

    // FIXED: ID Generation Logic (Max ID + 1 is safer than Count)
    $stmt = $pdo->query("SELECT memberid FROM member ORDER BY memberid DESC LIMIT 1");
    $lastId = $stmt->fetchColumn();

    if ($lastId) {
        // Extract number (remove 'M'), add 1, pad with zeros
        $num = (int)substr($lastId, 1); 
        $newId = 'M' . str_pad($num + 1, 6, '0', STR_PAD_LEFT);
    } else {
        $newId = 'M000001';
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO member
            (memberid, name, phone, email, dateissued, dateexpired, points)
            VALUES
            (:id, :name, :phone, :email, :issued, :expired, 0)
        ");

        $stmt->execute([
            ':id' => $newId,
            ':name' => $input['name'],
            ':phone' => $input['phone'] ?? null,
            ':email' => $input['email'] ?? null,
            ':issued' => date('Y-m-d'), // Default to today
            ':expired' => date('Y-m-d', strtotime('+1 year')) // Default to 1 year validity
        ]);

        echo json_encode(['success' => true, 'memberid' => $newId]);
    } catch (PDOException $e) {
        http_response_code(500);
        // Check for duplicate email error (SQL State 23505)
        if ($e->getCode() == '23505') {
            echo json_encode(['error' => 'Email already exists']);
        } else {
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?>