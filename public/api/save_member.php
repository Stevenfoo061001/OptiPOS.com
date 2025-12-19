<?php
header("Content-Type: application/json");

$membersFile = __DIR__ . "/../data/members.json";

$data = json_decode(file_get_contents("php://input"), true);

$mode = $data['mode'] ?? null;
$id = $data['id'] ?? null;
$name = trim($data['name'] ?? '');
$phone = trim($data['phone'] ?? '');
$email = trim($data['email'] ?? '');
$points = intval($data['points'] ?? 0);

if (!$mode || !$name) {
  echo json_encode(["success" => false, "error" => "Invalid data"]);
  exit;
}

$members = file_exists($membersFile)
  ? json_decode(file_get_contents($membersFile), true)
  : [];

if (!is_array($members)) $members = [];

/* ---------- ADD MEMBER ---------- */
if ($mode === "add") {
  $nextId = "M" . str_pad(count($members) + 1, 3, "0", STR_PAD_LEFT);

  $members[] = [
    "id" => $nextId,
    "name" => $name,
    "phone" => $phone,
    "email" => $email,
    "points" => $points,
    "dateIssued" => date("Y-m-d"),
    "dateExpired" => date("Y-m-d", strtotime("+1 year"))
  ];

  file_put_contents($membersFile, json_encode($members, JSON_PRETTY_PRINT));

  echo json_encode(["success" => true, "member" => end($members)]);
  exit;
}

/* ---------- EDIT MEMBER ---------- */
if ($mode === "edit" && $id) {
  foreach ($members as &$m) {
    if ($m['id'] === $id) {
      $m['name'] = $name;
      $m['phone'] = $phone;
      $m['email'] = $email;
      $m['points'] = $points;
      break;
    }
  }

  file_put_contents($membersFile, json_encode($members, JSON_PRETTY_PRINT));
  echo json_encode(["success" => true]);
  exit;
}

echo json_encode(["success" => false, "error" => "Action failed"]);
