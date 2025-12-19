<?php
header("Content-Type: application/json");

define("BASE_PATH", dirname(__DIR__)); // /public
$membersFile = BASE_PATH . "/data/members.json";

$data = json_decode(file_get_contents("php://input"), true);

$name = trim($data["name"] ?? "");
$phone = trim($data["phone"] ?? "");
$email = trim($data["email"] ?? "");

if (!$name || !$phone) {
  echo json_encode(["success" => false, "error" => "Missing required fields"]);
  exit;
}

$members = file_exists($membersFile)
  ? json_decode(file_get_contents($membersFile), true)
  : [];

if (!is_array($members)) $members = [];

/* Generate Member ID */
$nextNumber = count($members) + 1;
$memberId = "M" . str_pad($nextNumber, 3, "0", STR_PAD_LEFT);

$today = date("Y-m-d");
$expire = date("Y-m-d", strtotime("+1 year"));

$newMember = [
  "id" => $memberId,
  "name" => $name,
  "phone" => $phone,
  "email" => $email,
  "dateIssued" => $today,
  "dateExpired" => $expire,
  "points" => 0
];

$members[] = $newMember;
file_put_contents($membersFile, json_encode($members, JSON_PRETTY_PRINT));

echo json_encode(["success" => true]);
