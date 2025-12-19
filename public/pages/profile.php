<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>POS System - Employee Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body { background:#e5e5e5; font-family: Arial, sans-serif; }

    .sidebar {
  width:220px;
  background:#fff;
  height:100vh;
  position:fixed;
  left:0;
  top:0;
  border-right:1px solid #ddd;
  padding:20px;
}

.sidebar h5 {
  font-weight:600;
  margin-bottom:25px;
}

.sidebar a {
  display:block;
  padding:10px 0;
  color:#666;
  text-decoration:none;
  font-size:14px;
}

.sidebar a.active {
  color:#2563eb;
  font-weight:600;
}


    /* Main */
    .main {
      margin-left:220px;
      padding:25px;
    }

    .profile-section {
      display:flex;
      gap:30px;
      align-items:center;
    }

    .avatar {
      width:150px;
      height:150px;
      border-radius:50%;
      background:#cfd8e3;
      display:flex;
      align-items:center;
      justify-content:center;
      font-size:48px;
      color:#fff;
    }

    .name-role h4 { margin:10px 0 0; }
    .name-role small { color:#555; }

    .card-box {
      background:#fff;
      border-radius:10px;
      padding:20px;
      box-shadow:0 2px 6px rgba(0,0,0,0.1);
    }

    .info-grid {
      display:grid;
      grid-template-columns:1fr 1fr;
      gap:15px;
      font-size:14px;
    }

    .label { color:#777; font-size:12px; }
  </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <h5>POS System</h5>

  <a href="index.php?page=home"
     class="<?= ($_GET['page'] ?? '') === 'home' ? 'active' : '' ?>">
     Home
  </a>

  <a href="index.php?page=cashier"
     class="<?= ($_GET['page'] ?? '') === 'cashier' ? 'active' : '' ?>">
     Cashier
  </a>

  <a href="index.php?page=products"
     class="<?= ($_GET['page'] ?? '') === 'products' ? 'active' : '' ?>">
     Products
  </a>

  <a href="index.php?page=members"
     class="<?= ($_GET['page'] ?? '') === 'members' ? 'active' : '' ?>">
     Members
  </a>

  <a href="index.php?page=transactions"
     class="<?= ($_GET['page'] ?? '') === 'transactions' ? 'active' : '' ?>">
     Transactions
  </a>

  <a href="index.php?page=reports"
     class="<?= ($_GET['page'] ?? '') === 'reports' ? 'active' : '' ?>">
     Reports
  </a>

  <a href="index.php?page=profile"
     class="<?= ($_GET['page'] ?? '') === 'profile' ? 'active' : '' ?>">
     Profile
  </a>

  <hr>

  <a href="logout.php" style="color:#e11d48; font-weight:600;">
    Logout
  </a>
</aside>


<!-- MAIN -->
<div class="main">

  <div class="row">
    <!-- Left Profile -->
    <div class="col-md-5">
      <div class="profile-section">
        <div class="avatar">👤</div>
        <div class="name-role">
          <h4>Name</h4>
          <small>Store Manager</small>
        </div>
      </div>
    </div>

    <!-- Right Info -->
    <div class="col-md-7">
      <div class="card-box mb-3">
        <h6>Employee Details</h6>
        <div class="info-grid mt-3">
          <div>
            <div class="label">Employee ID</div>
            XXXX-XX-XXX
          </div>
          <div>
            <div class="label">Employment Type</div>
            Full / Part Time
          </div>
          <div>
            <div class="label">Join Date</div>
            DD / MM / YYYY
          </div>
        </div>
      </div>

      <div class="card-box">
        <h6>Contact</h6>
        <div class="info-grid mt-3">
          <div>
            <div class="label">Email</div>
            xxx@gmail.com
          </div>
          <div>
            <div class="label">Emergency Contact</div>
            Mom/Dad 012-345 6789
          </div>
          <div>
            <div class="label">Contact Number</div>
            012-345 6789
          </div>
          <div>
            <div class="label">Location</div>
            XXX
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

</body>
</html>
