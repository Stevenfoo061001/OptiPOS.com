<?php
// public/pages/login.php
// if user already logged in, show message

if (!empty($_SESSION['user'])) {
  echo '<div class="alert alert-success">You are already signed in as ' . htmlspecialchars($_SESSION['user']['name']) . '.</div>';
  echo '<p><a href="?page=home" class="btn btn-sm btn-primary">Go to Home</a></p>';
  return;
}
?>
<div class="row">
  <div class="col-md-6">
    <div class="card p-3">
      <h5>Sign in</h5>
      <div class="mb-2"><input id="login_username" class="form-control" placeholder="Username"></div>
      <div class="mb-2"><input id="login_password" type="password" class="form-control" placeholder="Password"></div>
      <div class="d-flex gap-2">
        <button id="loginSubmit" class="btn btn-primary">Sign in</button>
        <button id="loginDemo" class="btn btn-outline-secondary">Demo Admin</button>
      </div>
      <div id="loginMsg" class="mt-2"></div>
    </div>
  </div>
</div>

<script>
document.getElementById('loginSubmit').addEventListener('click', async ()=>{
  const username = document.getElementById('login_username').value.trim();
  const password = document.getElementById('login_password').value;
  if (!username || !password) { document.getElementById('loginMsg').innerHTML = '<div class="alert alert-warning">Enter username & password</div>'; return; }
  const res = await fetch('/api/login.php', {
    method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({username,password})
  });
  const j = await res.json();
  if (j.success) {
    // redirect to home and show toast
    location.href='?page=home';
  } else {
    document.getElementById('loginMsg').innerHTML = '<div class="alert alert-danger">' + (j.error || 'Login failed') + '</div>';
  }
});

document.getElementById('loginDemo').addEventListener('click', ()=>{
  document.getElementById('login_username').value = 'admin';
  document.getElementById('login_password').value = 'secret123';
});
</script>
