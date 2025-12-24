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
  SELECT
    memberid,
    name,
    phone,
    email,
    points
  FROM member
  ORDER BY dateissued DESC
";

$stmt = $pdo->query($sql);
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Members</title>
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
      <h1>Members</h1>

      <div class="members-actions">
        <input
          type="text"
          id="memberSearchInput"
          placeholder="Search by ID, name or phone"
        >
        <button class="add-member-btn" type="button">+ Add Member</button>
      </div>
    </div>

    <!-- MEMBER LIST -->
    <div class="members-list" id="membersList">
      <?php if (empty($members)): ?>
        <div class="empty">No members found</div>
      <?php else: ?>
        <?php foreach ($members as $m): ?>
          <div class="member-row"
            data-id="<?= strtolower($m['memberid']) ?>"
            data-name="<?= strtolower($m['name']) ?>"
            data-phone="<?= strtolower($m['phone'] ?? '') ?>"
            data-email="<?= strtolower($m['email'] ?? '') ?>"
            data-points="<?= intval($m['points']) ?>"
          >
            <div class="member-id"><?= htmlspecialchars($m['memberid']) ?></div>

            <div class="member-info">
              <div class="member-name"><?= htmlspecialchars($m['name']) ?></div>
              <div class="member-phone"><?= htmlspecialchars($m['phone'] ?? '-') ?></div>
            </div>

            <div class="member-points">
              <?= intval($m['points']) ?> pts
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </main>
</div>

<!-- MEMBER MODAL -->
<div class="modal-overlay" id="memberModal" style="display:none;">
  <div class="modal-card">
    <h2 id="modalTitle">Add Member</h2>

    <input type="hidden" id="memberMode">
    <input type="hidden" id="memberId">

    <div class="modal-form">
      <label>Name</label>
      <input type="text" id="memberName"
      placeholder="Full Name">

      <label>Phone</label>
      <input type="text" 
      id="memberPhone"
      maxlength="13"
      minlength="3"
      placeholder="Phone Number without -">

      <label>Email</label>
      <input type="email" id="memberEmail">

      <label>Points</label>
      <input type="number" id="memberPoints" value="0">
    </div>

    <div class="modal-actions">
  <button type="button" class="btn-delete">Delete</button>

  <div style="flex:1"></div>

  <button type="button" class="btn-cancel">Cancel</button>
  <button type="button" class="btn-save">Save</button>
</div>

  </div>
</div>

<script>
  const BASE_URL = "<?= BASE_URL ?>";
</script>
<script src="<?= BASE_URL ?>/assets/js/auth.js"></script>
<script src="<?= BASE_URL ?>/assets/js/members.js"></script>

</body>
</html>
