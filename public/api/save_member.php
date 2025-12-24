<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../config/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$id     = trim($data["id"] ?? "");
$name   = trim($data["name"] ?? "");
$phone  = trim($data["phone"] ?? "");
$email  = trim($data["email"] ?? "");
$points = intval($data["points"] ?? 0);

if (!$id || !$name || $phone ==="") {
  echo json_encode(["success" => false, "error" => "Invalid data"]);
  exit;
}

if (!preg_match('/^\d+$/', $phone)) {
  echo json_encode([
    "success" => false,
    "error" => "Phone number must contain digits only"
  ]);
  exit;
}

// less than 15 digits
if (strlen($phone) >= 15) {
  echo json_encode([
    "success" => false,
    "error" => "Phone number must be less than 15 digits"
  ]);
  exit;
}

/* ---------- DUPLICATE PHONE CHECK (exclude self) ---------- */
$stmt = $pdo->prepare("
  SELECT 1
  FROM member
  WHERE phone = :phone
    AND memberid != :id
  LIMIT 1
");
$stmt->execute([
  ":phone" => $phone,
  ":id"    => $id
]);

if ($stmt->fetch()) {
  echo json_encode([
    "success" => false,
    "error" => "This phone number is already used by another member"
  ]);
  exit;
}

try{
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
}catch (PDOException $e) {
  http_response_code(500);
  echo json_encode([
    "success" => false,
    "error" => "Database error"
  ]);
  exit;
}

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
