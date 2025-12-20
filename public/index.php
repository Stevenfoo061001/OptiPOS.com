<?php
session_start();

// ----------------------------
// DATABASE CONNECTION (PDO)
// ----------------------------
$host = "localhost";
$port = "5432";
$dbname = "postgres"; 
$user = "postgres";         
$password = "skittle3699";   // put your own password here

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    // echo "Connected successfully"; // optional
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// ----------------------------
// PAGE ROUTING
// ----------------------------
$page = $_GET['page'] ?? 'home';

// list of allowed pages
$allowed = [
    'home',
    'cashier',
    'products',
    'members',
    'transactions',
    'reports',
    'profile',
    'login'
];

if (!in_array($page, $allowed)) {
    $page = 'home';
}

$user = $_SESSION['user'] ?? null;

// ----------------------------
// LOGIN PROTECTION
// ----------------------------
if (!$user && $page !== 'login') {
    header("Location: ?page=login");
    exit;
}

if ($user && $page === 'login') {
    header("Location: ?page=home");
    exit;
}

// Optional admin-only pages
$adminOnlyPages = ['products', 'members', 'reports'];
if ($user && ($user['role'] ?? '') !== 'admin' && in_array($page, $adminOnlyPages)) {
    $page = 'home';
    $error = "You do not have permission to access that page.";
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>OptiPOS</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root{--sidebar-width:220px;}
    body{background:#f7fafc;font-family:system-ui,Segoe UI,Roboto,Arial;}
    .app-shell{display:flex;min-height:100vh;}
    .sidebar{width:var(--sidebar-width);padding:18px;border-right:1px solid rgba(0,0,0,0.06);}
    .main{flex:1;padding:20px;}
    .content{max-width:1200px;margin:0 auto;background:#fff;padding:22px;border-radius:10px;box-shadow:0 6px 18px rgba(25,45,60,0.06);}
    .user-area{display:flex;gap:8px;align-items:center;}
    .muted { color:#6c757d; }
    @media (max-width:920px){ .sidebar{display:none;} }
  </style>
</head>
<body>
  <div class="app-shell">
    <aside class="sidebar">
      <div class="fw-bold mb-3">OptiPOS</div>
      <nav class="nav flex-column mb-3">
        <a class="nav-link <?= $page=='home'?'fw-bold':'' ?>" href="?page=home">Home</a>
        <a class="nav-link <?= $page=='cashier'?'fw-bold':'' ?>" href="?page=cashier">Cashier</a>
        <a class="nav-link <?= $page=='products'?'fw-bold':'' ?>" href="?page=products">Products</a>
        <a class="nav-link <?= $page=='members'?'fw-bold':'' ?>" href="?page=members">Members</a>
        <a class="nav-link <?= $page=='transactions'?'fw-bold':'' ?>" href="?page=transactions">Transactions</a>
        <a class="nav-link <?= $page=='reports'?'fw-bold':'' ?>" href="?page=reports">Reports</a>
        <a class="nav-link <?= $page=='profile'?'fw-bold':'' ?>" href="?page=profile">Profile</a>
      </nav>

      <?php if ($user): ?>
        <div class="muted">Signed in as</div>
        <div class="fw-semibold"><?= htmlspecialchars($user['name']) ?></div>
        <div class="text-muted small"><?= htmlspecialchars($user['role']) ?></div>
      <?php else: ?>
        <div class="mt-3">
          <a class="btn btn-sm btn-outline-primary" href="?page=login">Sign in</a>
        </div>
      <?php endif; ?>
    </aside>

    <main class="main">
      <div class="content">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <?php
              $titles = [
                'home'=>'Welcome to OptiPOS (Starter)',
                'cashier'=>'Cashier',
                'products'=>'Products',
                'members'=>'Members',
                'transactions'=>'Transactions',
                'reports'=>'Reports',
                'profile'=>'Profile',
                'login'=>'Sign in'
              ];
              $title = $titles[$page] ?? 'OptiPOS';
            ?>
            <h1 class="h4 mb-0"><?= htmlspecialchars($title) ?></h1>
            <div class="muted small mt-1">
              <?php if ($page === 'home'): ?>This is the beginner-friendly demo.<?php else: ?>Manage <?= htmlspecialchars($title) ?>.<?php endif; ?>
            </div>
          </div>

          <div class="user-area">
            <?php if ($user): ?>
              <div class="text-end me-2">
                <div class="fw-semibold"><?= htmlspecialchars($user['name']) ?></div>
                <div class="muted small"><?= htmlspecialchars($user['role']) ?></div>
              </div>
              <button id="logoutBtn" class="btn btn-outline-secondary btn-sm">Logout</button>
            <?php else: ?>
              <a href="?page=login" class="btn btn-primary btn-sm">Login</a>
            <?php endif; ?>
          </div>
        </div>

        <section id="pageArea">
          <?php include __DIR__ . "/pages/{$page}.php"; ?>
        </section>
      </div>
    </main>
  </div>

  <div class="modal fade" id="genericModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header"><h5 id="genericModalTitle" class="modal-title">Title</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body" id="genericModalBody"></div>
        <div class="modal-footer" id="genericModalFooter"></div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // logout handler
    document.getElementById('logoutBtn')?.addEventListener('click', async ()=>{
      const r = await fetch('/api/logout.php');
      const j = await r.json();
      if (j.success) location.href='?page=home';
    });

    function showModal(title, bodyHtml, footerHtml = '') {
      document.getElementById('genericModalTitle').innerText = title;
      document.getElementById('genericModalBody').innerHTML = bodyHtml;
      document.getElementById('genericModalFooter').innerHTML = footerHtml;
      const mEl = document.getElementById('genericModal');
      const modal = new bootstrap.Modal(mEl);
      modal.show();
    }

    function showAlert(message, type='info', timeout=2500) {
      const el = document.createElement('div');
      el.className = `toast align-items-center text-bg-${type} border-0 show`;
      el.style.position = 'fixed'; el.style.right = '16px'; el.style.bottom = '16px'; el.style.zIndex = 9999;
      el.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div><button class="btn-close btn-close-white ms-2 me-1"></button></div>`;
      document.body.appendChild(el);
      el.querySelector('.btn-close')?.addEventListener('click', ()=>el.remove());
      setTimeout(()=>el.remove(), timeout);
    }
  </script>
</body>
</html>
