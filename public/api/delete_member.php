<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../config/db.php";

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;

if (!$id) {
  echo json_encode(["success" => false, "error" => "Member ID missing"]);
  exit;
}

$stmt = $pdo->prepare("DELETE FROM member WHERE memberid = :id");
$stmt->execute([":id" => $id]);

echo json_encode(["success" => true]);
