<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../config/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$name = trim($data["name"] ?? "");
$phone = trim($data["phone"] ?? "");
$email = trim($data["email"] ?? "");
$points = intval($data["points"] ?? 0);

/* ===== VALIDATION (ADDED ONLY) ===== */

// required fields
if (!$name || !$phone) {
  echo json_encode(["success" => false, "error" => "Missing required fields"]);
  exit;
}

// name: alphabetic only
if (!preg_match("/^[a-zA-Z ]+$/", $name)) {
  echo json_encode(["success" => false, "error" => "Name must be alphabetic only"]);
  exit;
}

// phone: numeric only
if (!ctype_digit($phone)) {
  echo json_encode(["success" => false, "error" => "Phone number must be numeric only"]);
  exit;
}

// email: gmail or yahoo only (if provided)
if ($email && !preg_match("/^[a-zA-Z0-9._%+-]+@(gmail|yahoo)\.com$/", $email)) {
  echo json_encode(["success" => false, "error" => "Email must be @gmail.com or @yahoo.com"]);
  exit;
}

/* ===== ORIGINAL CODE CONTINUES ===== */

$stmt = $pdo->query("SELECT COUNT(*) FROM member");
$count = $stmt->fetchColumn();
$memberId = "M" . str_pad($count + 1, 3, "0", STR_PAD_LEFT);

$sql = "
  INSERT INTO member (memberid, name, phone, email, points)
  VALUES (:id, :name, :phone, :email, :points)
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
  ":id"    => $memberId,
  ":name"  => $name,
  ":phone" => $phone,
  ":email" => $email,
  ":points"=> $points
]);

echo json_encode([
  "success" => true,
  "member" => [
    "id"     => $memberId,
    "name"   => $name,
    "phone"  => $phone,
    "email"  => $email,
    "points" => $points
  ]
]);
