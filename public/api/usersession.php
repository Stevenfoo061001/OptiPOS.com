<?php
// public/api/usersession.php
header('Content-Type: application/json');
session_start();
$user = $_SESSION['user'] ?? null;
if ($user) echo json_encode($user);
else echo json_encode(null);
