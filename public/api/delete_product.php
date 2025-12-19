<?php
define("BASE_PATH", dirname(__DIR__));
$file = BASE_PATH . "/data/products.json";

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['stockId'] ?? null;

$products = json_decode(file_get_contents($file), true);
$products = array_filter($products, fn($p) => $p['stockId'] !== $id);

file_put_contents($file, json_encode(array_values($products), JSON_PRETTY_PRINT));
echo json_encode(["success" => true]);
