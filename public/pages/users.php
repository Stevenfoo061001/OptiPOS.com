<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . "/../../config/db.php";

if (empty($_SESSION['user'])) {
    header("Location: " . BASE_URL . "/index.php?page=login");
    exit;
}

if ($_SESSION['user']['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php?page=no_permission");
    exit;
}

$sql = "
  SELECT userid, name, phone, email, role
  FROM users
  ORDER BY userid
";
$stmt = $pdo->query($sql);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Users</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
</head>
<body>

<div class="app-layout">

  <!-- SIDEBAR -->
  <?php include __DIR__ . '/sidebar.php'; ?>

  <!-- MAIN CONTENT -->
  <main class="main-content">

    <!-- HEADER -->
    <div class="members-header">
      <h1>Users</h1>

      <div class="members-actions">
  <input
    type="text"
    id="userSearchInput"
    placeholder="Search by ID, name or email"
  >

  <select id="roleFilter" class="pretty-select">
    <option value="">All Roles</option>
    <option value="admin">Admin</option>
    <option value="cashier">Cashier</option>
  </select>

  <button class="add-member-btn" type="button" onclick="openAddUser()">
    + Add User
  </button>
</div>

    </div>

    <!-- USER LIST -->
    <div class="members-list" id="usersList">
      <?php if (empty($users)): ?>
        <div class="empty">No users found</div>
      <?php else: ?>
        <?php foreach ($users as $u): ?>
          <div class="member-row"
  onclick='openEditUser(<?= json_encode($u) ?>)'
  data-id="<?= strtolower($u['userid']) ?>"
  data-name="<?= strtolower($u['name']) ?>"
  data-email="<?= strtolower($u['email']) ?>"
  data-phone="<?= strtolower($u['phone'] ?? '') ?>"
  data-role="<?= strtolower($u['role']) ?>"
>

            <div class="member-id"><?= htmlspecialchars($u['userid']) ?></div>

            <div class="member-info">
              <div class="member-name"><?= htmlspecialchars($u['name']) ?></div>
              <div class="member-phone"><?= htmlspecialchars($u['email']) ?></div>
            </div>

            <div class="member-points">
              <?= htmlspecialchars($u['role']) ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </main>
</div>

<!-- USER MODAL -->
<div class="modal-overlay" id="userModal" style="display:none;">
  <div class="modal-card">
    <h2 id="userModalTitle">Add User</h2>

    <input type="hidden" id="userMode">
    <input type="hidden" id="userId">

    <div class="modal-form">
      <label>Name</label>
      <input type="text" id="userName">

      <label>Phone</label>
      <input type="text" id="userPhone">

      <label>Email</label>
      <input type="email" id="userEmail">

      <div id="passwordGroup">
        <label>Password</label>
        <input type="password" id="userPassword" placeholder="Set initial password">
      </div>

      <label>Role</label>
      <select id="userRole" class="pretty-select">
        <option value="admin">Admin</option>
        <option value="cashier">Cashier</option>
      </select>
    </div>

    <div class="modal-actions">
  <button
    type="button"
    class="btn-delete"
    id="deleteUserBtn"
    onclick="confirmDeleteUser()"
    style="display:none"
  >
    Delete
  </button>

  <div style="flex:1"></div>

  <button type="button" class="btn-cancel" onclick="closeUserModal()">Cancel</button>
  <button type="button" class="btn-save" onclick="saveUser()">Save</button>
</div>
  </div>
</div>
<script>
  const BASE_URL = "<?= BASE_URL ?>";
</script>

<script src="<?= BASE_URL ?>/assets/js/auth.js"></script>
<script src="<?= BASE_URL ?>/assets/js/users.js"></script>

</body>
</html>

