<?php
header("Content-Type: application/json");

define("BASE_PATH", dirname(__DIR__)); // /public

$data = json_decode(file_get_contents("php://input"), true);
$query = strtoupper(trim($data["query"] ?? ""));

if ($query === "") {
    echo json_encode(["success" => false, "error" => "Empty query"]);
    exit;
}

$membersFile = BASE_PATH . "/data/members.json";

if (!file_exists($membersFile)) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "members.json not found",
        "path" => $membersFile
    ]);
    exit;
}

$members = json_decode(file_get_contents($membersFile), true);

foreach ($members as $member) {
    if (
        strtoupper($member["id"]) === $query ||
        $member["phone"] === $query
    ) {
        echo json_encode([
            "success" => true,
            "member" => $member
        ]);
        exit;
    }
}

echo json_encode(["success" => false]);
