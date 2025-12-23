<?php
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$keyword = trim($data['keyword'] ?? '');

if ($keyword === '') {
    echo json_encode([
        'success' => false,
        'error' => 'Empty keyword'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare('
    SELECT 
        memberid,
        name,
        phone,
        points
    FROM member
    WHERE memberid = :kw
       OR phone = :kw
    LIMIT 1
');

    $stmt->execute(['kw' => $keyword]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$member) {
        echo json_encode(['success' => false]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'member' => $member
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
