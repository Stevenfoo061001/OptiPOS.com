<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
<script src="<?= BASE_URL ?>/assets/js/auth.js"></script>

<div class="app-layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-header">
      <h2>POS System</h2>
    </div>

    <nav class="sidebar-menu">
      <a href="<?= BASE_URL ?>/index.php?page=home">Home</a>
      <a href="<?= BASE_URL ?>/index.php?page=cashier">Cashier</a>
      <a href="<?= BASE_URL ?>/index.php?page=products">Products</a>
      <a href="<?= BASE_URL ?>/index.php?page=members">Members</a>
      <a href="<?= BASE_URL ?>/index.php?page=transactions" class="active">Transactions</a>
      <a href="<?= BASE_URL ?>/index.php?page=reports">Reports</a>
      <a href="<?= BASE_URL ?>/index.php?page=profile">Profile</a>
    </nav>

    <div class="sidebar-footer">
      <button class="logout-btn" onclick="logout()">Logout</button>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="main-content">
    <h1>Transactions</h1>

    <div class="transactions-layout">

      <!-- LEFT: TRANSACTION LIST -->
      <aside class="transaction-list">
        <h2>Transaction History</h2>
        <input
        type="text"
        id="transactionSearch"
        placeholder="Search transaction..."
        class="transaction-search"
        >

        <?php if (empty($transactions)): ?>
          <p>No transactions found</p>
        <?php else: ?>
          <?php foreach ($transactions as $index => $trx): ?>
            <div class="transaction-item <?= $index === 0 ? 'active' : '' ?>"
                 data-index="<?= $index ?>">
              <strong><?= htmlspecialchars($trx['transactionid']) ?></strong>
              <div class="trx-date">
                <?= htmlspecialchars($trx['payment_date']) ?>
                <?= htmlspecialchars($trx['payment_time']) ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </aside>

      <!-- RIGHT: RECEIPT -->
      <section class="receipt-panel">
        <h2>Receipt</h2>

        <div id="receiptItems"></div>

        <hr>

        <div class="summary-row">
          <span>Subtotal</span>
          <span id="rSubtotal">RM 0.00</span>
        </div>

        <div class="summary-row">
          <span>Tax</span>
          <span id="rTax">RM 0.00</span>
        </div>

        <div class="summary-row">
          <span>Discount</span>
          <span id="rDiscount">- RM 0.00</span>
        </div>

        <div class="summary-row total">
          <span>Total</span>
          <span id="rTotal">RM 0.00</span>
        </div>

        <p class="payment-info">
          Payment: <strong id="rPayment">-</strong>
        </p>

        <p class="member-info">
          Member: <strong id="rMember">-</strong>
        </p>


        <button class="print-btn" onclick="window.print()">Print Receipt</button>
      </section>

    </div>
  </main>

</div>

<script>
  const BASE_URL = "<?= BASE_URL ?>";
</script>
<script src="<?= BASE_URL ?>/assets/js/transactions.js"></script>
