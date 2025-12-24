<?php
require_once __DIR__ . "/../../config/require_admin.php";
require_once __DIR__ . "/../../config/db.php";
header('Content-Type: application/json');

try {

  $data = json_decode(file_get_contents("php://input"), true);

  $mode   = $data['mode'] ?? 'add';
  $phone  = trim($data['phone'] ?? '');
  $userid = $data['userid'] ?? null;

  /* ================= PHONE UNIQUE CHECK ================= */
  if ($phone !== '') {

    if ($mode === 'edit') {
      if (empty($userid)) {
        throw new Exception('Missing user id');
      }

      $sql = "SELECT COUNT(*) FROM users WHERE phone = ? AND userid <> ?";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([$phone, $userid]);

    } else {
      $sql = "SELECT COUNT(*) FROM users WHERE phone = ?";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([$phone]);
    }

    if ($stmt->fetchColumn() > 0) {
      throw new Exception('Phone number already exists');
    }
  }

  /* ================= EDIT USER ================= */
  if ($mode === 'edit') {

    $sql = "
      UPDATE users
      SET name = ?, phone = ?, email = ?, role = ?
      WHERE userid = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      $data['name'] ?? '',
      $phone ?: null,
      $data['email'] ?? '',
      $data['role'] ?? 'cashier',
      $userid
    ]);

    echo json_encode(['success' => true]);
    exit;
  }

  /* ================= ADD USER ================= */
  if (empty($data['password'])) {
    throw new Exception('Password is required');
  }

  $userid = 'U' . str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);
  $password = $data['password'];

  $sql = "
    INSERT INTO users (userid, name, phone, email, password, role)
    VALUES (?, ?, ?, ?, ?, ?)
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    $userid,
    $data['name'] ?? '',
    $phone ?: null,
    $data['email'] ?? '',
    $password,
    $data['role'] ?? 'cashier'
  ]);

  echo json_encode(['success' => true]);

} catch (Throwable $e) {

  // 永远只回 JSON
  echo json_encode([
    'success' => false,
    'error' => $e->getMessage()
  ]);
}
