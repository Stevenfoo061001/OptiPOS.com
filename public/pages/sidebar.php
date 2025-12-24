<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['user'] ?? null;
$role = $user['role'] ?? null;
?>

<aside class="sidebar">

  <!-- HEADER -->
  <div class="sidebar-header">
    <h2>POS System</h2>
  </div>

  <!-- MENU -->
  <nav class="sidebar-menu">

    <a href="<?= BASE_URL ?>/index.php?page=home"
       class="<?= ($_GET['page'] ?? '') === 'home' ? 'active' : '' ?>">
      Home
    </a>

    <a href="<?= BASE_URL ?>/index.php?page=cashier"
       class="<?= ($_GET['page'] ?? '') === 'cashier' ? 'active' : '' ?>">
      Cashier
    </a>

    <a href="<?= BASE_URL ?>/index.php?page=products"
       class="<?= ($_GET['page'] ?? '') === 'products' ? 'active' : '' ?>">
      Products
    </a>

    <a href="<?= BASE_URL ?>/index.php?page=members"
       class="<?= ($_GET['page'] ?? '') === 'members' ? 'active' : '' ?>">
      Members
    </a>

    <a href="<?= BASE_URL ?>/index.php?page=transactions"
       class="<?= ($_GET['page'] ?? '') === 'transactions' ? 'active' : '' ?>">
      Transactions
    </a>

    <a href="<?= BASE_URL ?>/index.php?page=reports"
       class="<?= ($_GET['page'] ?? '') === 'reports' ? 'active' : '' ?>">
      Reports
    </a>

    <a href="<?= BASE_URL ?>/index.php?page=users"
       class="<?= ($_GET['page'] ?? '') === 'users' ? 'active' : '' ?>">
      Users
    </a>


    <a href="<?= BASE_URL ?>/index.php?page=profile"
       class="<?= ($_GET['page'] ?? '') === 'profile' ? 'active' : '' ?>">
      Profile
    </a>

  </nav>

  <!-- FOOTER -->
  <div class="sidebar-footer">
    <button class="logout-btn" onclick="logout()">Logout</button>
  </div>

</aside>
