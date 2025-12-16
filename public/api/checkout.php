<?php
// public/api/checkout.php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input) || empty($input['items'])) {
  http_response_code(400);
  echo json_encode(['success'=>false,'error'=>'Invalid payload']);
  exit;
}

$total = 0;
foreach ($input['items'] as $it) {
  $total += ($it['price'] ?? 0) * ($it['qty'] ?? 0);
}
$discount = (float)($input['discount'] ?? 0);
$total_after = max(0, $total - $discount);
$receipt = 'R' . time() . rand(100,999);

$txFile = __DIR__ . '/../../data/transactions.json';
$tx = json_decode(file_get_contents($txFile), true) ?: [];
$ids = array_column($tx,'id'); $nid = $ids ? max($ids)+1 : 1;
$record = [
  'id' => $nid,
  'receipt' => $receipt,
  'items' => $input['items'],
  'discount' => $discount,
  'total' => $total_after,
  'created_at' => date('c')
];
$tx[] = $record;
file_put_contents($txFile, json_encode($tx, JSON_PRETTY_PRINT));

echo json_encode(['success'=>true,'receipt'=>$receipt,'transaction'=>$record]);
