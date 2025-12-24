<?php 
require_once __DIR__ . '/../../config/config.php'; 
require_once __DIR__ . '/../../config/db.php'; 

if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

/* ================= 判断是否需要初始化 Admin ================= */
$needSetupAdmin = false;

/* PostgreSQL only */
$tableExists = $pdo->query("
    SELECT to_regclass('public.users') IS NOT NULL
")->fetchColumn();

if (!$tableExists) {
    $needSetupAdmin = true;
} else {
    $adminCount = (int) $pdo
        ->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")
        ->fetchColumn();

    if ($adminCount === 0) {
        $needSetupAdmin = true;
    }
}

/* 已登录直接进 Home */
if (!empty($_SESSION['user'])) {
    header("Location: " . BASE_URL . "/index.php?page=home");
    exit;
}
?> 

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>OptiPOS Login</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
</head>

<body class="login-bg">

<div class="login-wrapper">

  <!-- ================= LOGIN CARD ================= -->
  <div class="login-card" <?= $needSetupAdmin ? 'style="visibility:hidden"' : '' ?>>

    <!-- LEFT BRAND PANEL -->
    <div class="login-left">
      <div class="brand">
        <h1>OptiPOS</h1>
        <p>Retail Management System</p>
      </div>
    </div>

    <!-- RIGHT LOGIN PANEL -->
    <div class="login-right">
      <h2>Welcome Back</h2>
      <p class="subtitle">Please login to continue</p>

      <input id="username" type="text" placeholder="Username">
      <input id="password" type="password" placeholder="Password">

      <button onclick="login()">Login</button>

      <p id="error" class="error-text"></p>
    </div>

  </div>

</div>

<script>
  const BASE_URL = "<?= BASE_URL ?>";
  const NEED_ADMIN_SETUP = <?= $needSetupAdmin ? 'true' : 'false' ?>;
</script>
<script src="<?= BASE_URL ?>/assets/js/auth.js"></script>

<!-- ================= ADMIN SETUP MODAL (唯一一份) ================= -->
<div id="adminSetupOverlay" class="modal-overlay" style="display:none;">
  <div class="admin-setup-card">

    <h3>Initial Admin Setup</h3>
    <p class="admin-subtitle">
      This system has no administrator yet.<br>
      Please create the first admin account.
    </p>

    <div class="admin-form">
      <input id="admin_name" type="text" placeholder="Admin Name">
      <input id="admin_phone" type="text" placeholder="Phone">
      <input id="admin_email" type="email" placeholder="Email">
      <input id="admin_password" type="password" placeholder="Password">
    </div>

    <button class="admin-primary-btn" onclick="createAdmin()">
      Create Admin
    </button>

    <p id="adminSetupError" class="admin-error-text"></p>

  </div>
</div>

<!-- ================= ADMIN SETUP LOGIC ================= -->
<script>
  if (NEED_ADMIN_SETUP) {
    const overlay = document.getElementById('adminSetupOverlay');
    overlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }
</script>

</body>
</html>
