<?php
require_once __DIR__ . "/../../config/require_admin.php";
require_once __DIR__ . "/../../config/db.php";

$data = json_decode(file_get_contents("php://input"), true);
$userid = $data['userid'] ?? '';

if (!$userid) {
  echo json_encode(['success' => false, 'error' => 'Missing user id']);
  exit;
}

// ❌ 不能删自己
forbid_self_action($userid);

$stmt = $pdo->prepare("DELETE FROM users WHERE userid = ?");
$stmt->execute([$userid]);

echo json_encode(['success' => true]);
