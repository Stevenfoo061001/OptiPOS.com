<?php
require_once __DIR__ . '/../../config/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>No Permission</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
</head>
<body class="login-bg">

<div class="login-wrapper">
  <div class="login-card">

    <div class="login-right" style="width:100%; text-align:center;">
      <h2>🚫 No Permission</h2>
      <p class="subtitle">
        You do not have permission to access this page.
      </p>

      <a href="<?= BASE_URL ?>/index.php?page=home">
        <button>Back to Home</button>
      </a>
    </div>

  </div>
</div>

</body>
</html>
