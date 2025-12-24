<?php
session_start();

if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'error' => 'Unauthorized']);
  exit;
}

if (
  !isset($_SESSION['user']['role']) ||
  $_SESSION['user']['role'] !== 'admin'
) {
  http_response_code(403);
  echo json_encode(['success' => false, 'error' => 'No permission']);
  exit;
}

/**
 * 获取当前登录用户 ID（兼容 id / userid）
 */
function current_user_id(): ?string {
  return $_SESSION['user']['userid']
      ?? $_SESSION['user']['id']
      ?? null;
}

/**
 * 禁止对自己执行敏感操作
 */
function forbid_self_action(string $targetUserId): void {
  $me = current_user_id();

  if ($me !== null && $me === $targetUserId) {
    echo json_encode([
      'success' => false,
      'error' => 'You cannot perform this action on yourself'
    ]);
    exit;
  }
}
