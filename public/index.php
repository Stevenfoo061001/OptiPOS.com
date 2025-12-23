<?php
require_once __DIR__ . "/../config/init_db.php";
require_once __DIR__ . '/../config/config.php';
session_start();

$page = $_GET['page'] ?? 'home';

// Pages that do NOT require login
$publicPages = ['login'];

if (!isset($_SESSION['user']) && !in_array($page, $publicPages)) {
    header("Location: index.php?page=login");
    exit;
}

if (isset($_SESSION['user']) && $page === 'login') {
    header("Location: index.php?page=home");
    exit;
}

require __DIR__ . "/pages/{$page}.php";
