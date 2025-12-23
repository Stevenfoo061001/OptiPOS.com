<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../config/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$id     = $data["id"] ?? "";
$name   = trim($data["name"] ?? "");
$phone  = trim($data["phone"] ?? "");
$email  = trim($data["email"] ?? "");
$points = intval($data["points"] ?? 0);

if (!$id || !$name) {
  echo json_encode(["success" => false, "error" => "Invalid data"]);
  exit;
}

$sql = "
  UPDATE member
  SET name = :name,
      phone = :phone,
      email = :email,
      points = :points
  WHERE memberid = :id
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
  ":id"     => $id,
  ":name"   => $name,
  ":phone"  => $phone,
  ":email"  => $email,
  ":points" => $points
]);

echo json_encode([
  "success" => true,
  "member" => [
    "id"     => $id,
    "name"   => $name,
    "phone"  => $phone,
    "email"  => $email,
    "points" => $points
  ]
]);
