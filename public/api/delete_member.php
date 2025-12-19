<?php
header("Content-Type: application/json");

$membersFile = __DIR__ . "/../data/members.json";

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;

if (!$id) {
  echo json_encode(["success" => false, "error" => "Member ID missing"]);
  exit;
}

if (!file_exists($membersFile)) {
  echo json_encode(["success" => false, "error" => "Members file not found"]);
  exit;
}

$members = json_decode(file_get_contents($membersFile), true);
if (!is_array($members)) $members = [];

$members = array_values(array_filter($members, function ($m) use ($id) {
  return $m['id'] !== $id;
}));

file_put_contents($membersFile, json_encode($members, JSON_PRETTY_PRINT));

echo json_encode(["success" => true]);
