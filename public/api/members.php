<?php
// public/api/members.php
header('Content-Type: application/json');
$dataFile = __DIR__ . '/../../data/members.json';
if (!file_exists($dataFile)) { file_put_contents($dataFile, json_encode([])); }
$method = $_SERVER['REQUEST_METHOD'];
$members = json_decode(file_get_contents($dataFile), true) ?: [];

if ($method === 'GET') {
  echo json_encode($members);
  exit;
}
if ($method === 'POST') {
  $input = json_decode(file_get_contents('php://input'), true);
  if (!$input || empty($input['name'])) { http_response_code(400); echo json_encode(['error'=>'Invalid']); exit; }
  $ids = array_column($members,'id'); $nid = $ids?max($ids)+1:1;
  $new = ['id'=>$nid,'name'=>$input['name'],'phone'=>$input['phone'] ?? '','email'=>$input['email'] ?? '','points'=>0];
  $members[] = $new;
  file_put_contents($dataFile, json_encode($members, JSON_PRETTY_PRINT));
  echo json_encode($new);
  exit;
}
http_response_code(405); echo json_encode(['error'=>'Method not allowed']);
