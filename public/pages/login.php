<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>OptiPOS Login</title>
  <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="login-bg">

<div class="login-wrapper">

  <div class="login-card">

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

<script src="assets/js/auth.js"></script>
</body>
</html>
