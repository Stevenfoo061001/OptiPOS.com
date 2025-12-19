<?php
session_start();

$page = $_GET['page'] ?? 'home';

// Pages that do NOT require login
$publicPages = ['login'];

if (!isset($_SESSION['user']) && !in_array($page, $publicPages)) {
    header("Location: index.php?page=login");
    exit;
}

require __DIR__ . "/pages/{$page}.php";
