<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../config/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$name  = trim($data["name"] ?? "");
$phone = trim($data["phone"] ?? "");
$email = trim($data["email"] ?? "");

/* ---------- BASIC VALIDATION ---------- */
if ($name === "" || $phone === "") {
  echo json_encode([
    "success" => false,
    "error" => "Name and phone are required"
  ]);
  exit;
}

/* ---------- PHONE VALIDATION ---------- */
if (!preg_match('/^\d+$/', $phone)) {
  echo json_encode([
    "success" => false,
    "error" => "Phone number must contain digits only"
  ]);
  exit;
}

if (strlen($phone) >= 15) {
  echo json_encode([
    "success" => false,
    "error" => "Phone number must be less than 15 digits"
  ]);
  exit;
}

/* ---------- DEFAULT POINTS ---------- */
$points = 200;

try {
  /* ---------- TRANSACTION ---------- */
  $pdo->beginTransaction();

  /* ---------- DUPLICATE PHONE (DB-SAFE) ---------- */
  $stmt = $pdo->prepare(
    "SELECT 1 FROM member WHERE phone = :phone FOR UPDATE"
  );
  $stmt->execute([":phone" => $phone]);

  if ($stmt->fetch()) {
    $pdo->rollBack();
    echo json_encode([
      "success" => false,
      "error" => "This phone number already exists"
    ]);
    exit;
  }

  /* ---------- SAFE MEMBER ID (LOCKED) ---------- */
  $stmt = $pdo->query("
    SELECT COALESCE(
      MAX(
        CAST(
          NULLIF(
            REGEXP_REPLACE(memberid, '[^0-9]', '', 'g'),
            ''
          ) AS INT
        )
      ), 0
    ) + 1
    FROM member
  ");

  $nextId = $stmt->fetchColumn();
  $memberId = 'M' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

  /* ---------- INSERT ---------- */
  $stmt = $pdo->prepare("
    INSERT INTO member (memberid, name, phone, email, points)
    VALUES (:id, :name, :phone, :email, :points)
  ");

  $stmt->execute([
    ":id"     => $memberId,
    ":name"   => $name,
    ":phone"  => $phone,
    ":email"  => $email,
    ":points" => $points
  ]);

  $pdo->commit();

} catch (PDOException $e) {
  if ($pdo->inTransaction()) {
    $pdo->rollBack();
  }

  http_response_code(500);

  // ⚠️ 调试阶段先看清楚错误
  echo json_encode([
    "success" => false,
    "error" => $e->getMessage()
  ]);
  exit;
}

/* ---------- RESPONSE ---------- */
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
