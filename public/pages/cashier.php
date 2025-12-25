<?php
require_once __DIR__ . '/../../config/config.php';

if (empty($_SESSION['user'])) {
    header("Location: " . BASE_URL . "/index.php?page=login");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>OptiPOS - Cashier</title>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">

<style>
/* Page-specific tweaks only */
.panel {
  background: #ffffff;
  border-radius: 10px;
  padding: 16px;
  margin-bottom: 20px;
}

.cart-item {
  border-bottom: 1px solid #eee;
  padding: 8px 0;
}
</style>
</head>

<body>

<!-- ================= PAYMENT MODAL ================= -->
<div class="modal-overlay"
     id="paymentModal"
     style="display:none;"
     onclick="closePaymentModal()">

  <div class="modal-card" onclick="event.stopPropagation()">

    <h3>Payment Method</h3>

    <div class="payment-grid" id="paymentButtons">
      <button onclick="choosePayment('Visa', this)">Visa</button>
      <button onclick="choosePayment('Debit Card', this)">Debit Card</button>
      <button onclick="choosePayment('Cash', this)">Cash</button>
      <button onclick="choosePayment('TnG', this)">TnG</button>
      <button onclick="choosePayment('Alipay', this)">Alipay</button>
      <button onclick="choosePayment('Online', this)">Online</button>
    </div>

    <div class="payment-footer">
        <span id="selectedPaymentText" style="color:#666;">
        No payment selected
      </span>

      <div style="display:flex; gap:10px;">
        <button class="btn btn-secondary" onclick="closePaymentModal()">
          Cancel
        </button>
        <button class="btn btn-success" onclick="confirmPayment()">
          Confirm
        </button>
      </div>
    </div>

  </div>
</div>

<!-- ================= CASH MODAL ================= -->
<div class="modal-overlay"
     id="cashModal"
     style="display:none;"
     onclick="closeCashModal()">

  <div class="modal-card" onclick="event.stopPropagation()">

    <h3>Cash Payment</h3>

    <div style="margin-bottom:12px;">
      <label>Total Amount</label>
      <div style="font-weight:700;" id="cashTotalText">RM 0.00</div>
    </div>

    <div style="margin-bottom:12px;">
      <label>Cash Received</label>
      <input
        id="cashReceivedInput"
        type="number"
        min="0"
        step="0.01"
        class="search-input"
        placeholder="Enter cash received"
        oninput="updateCashChange()"
      >
    </div>

    <div style="margin-bottom:12px;">
      <label>Change</label>
      <div id="cashChangeText" style="font-weight:700;">RM 0.00</div>
    </div>

    <div class="payment-footer">
      <span id="cashErrorText" style="color:#dc2626;"></span>

      <div style="display:flex; gap:10px;">
        <button class="btn btn-secondary" onclick="closeCashModal()">
          Cancel
        </button>
        <button
          class="btn btn-success"
          id="confirmCashBtn"
          onclick="confirmCashPayment()"
          disabled>
          Confirm Payment
        </button>
      </div>
    </div>

  </div>
</div>


<!-- ================= LAYOUT ================= -->
<div class="app-layout">

  <!-- SIDEBAR -->
  <?php include __DIR__ . '/sidebar.php'; ?>

  <!-- MAIN -->
  <main class="main-content">

    <!-- HEADER -->
    <div class="header">
      <div>
        <h1>Cashier</h1>
        <p></p>
      </div>
      <div class="role">
        <?= htmlspecialchars($_SESSION['user']['role'] ?? 'Staff') ?>
      </div>
    </div>

    <!-- CONTENT -->
    <div class="cashier-grid">

      <!-- PRODUCTS -->
      <div class="cashier-products">
        <div class="panel">

          <div class="product-search-bar">
            <input
            id="searchInput"
            class="search-input"
            placeholder="Search product name or ID"
            >
            <select
            id="categoryFilter"
            class="search-select"
            >
              <option value="All">All Categories</option>
            </select>
          </div>

          <div id="productGrid" class="products-grid">
            <div class="text-muted text-center">Loading products…</div>
          </div>

        </div>
      </div>

      <!-- CART -->
      <div class="cashier-cart">

        <!-- MEMBER -->
        <div class="panel">
          <label class="member-label">Member</label>

          <div class="member-search-row">
            <input
            id="memberSearchInput"
            class="search-input"
            placeholder="Member Phone / ID"
            >
            <button
            class="btn btn-primary"
            type="button"
            onclick="findMember()"
            >
            Find</button>

          </div>

          <div id="memberResult" style="margin-top:8px; color:#666;">
            No member selected
          </div>
        </div>

        <!-- CART ITEMS -->
        <div class="panel" id="cartPanel">
          <div class="text-muted text-center">Cart is empty</div>
        </div>

        <!-- SUMMARY -->
        <div class="panel">

          <div style="display:flex; justify-content:space-between;">
            <span>Subtotal</span>
            <span id="subtotalValue">RM 0.00</span>
          </div>

          <div style="display:flex; justify-content:space-between; color:#dc2626;">
            <span>Discount</span>
            <span id="discountValue">-RM 0.00</span>
          </div>

          <div style="display:flex; justify-content:space-between; color:#16a34a;">
            <span>Points</span>
            <span id="pointsValue">+0</span>
          </div>

          <div style="display:flex; justify-content:space-between;">
            <span>Tax (6%)</span>
            <span id="taxValue">RM 0.00</span>
          </div>

          <hr>

          <div style="display:flex; justify-content:space-between; font-weight:700;">
            <span>Total</span>
            <span id="totalValue">RM 0.00</span>
          </div>

          <button
            class="btn btn-success"
            style="width:100%; margin-top:16px;"
            onclick="openPaymentModal()">
            PAY NOW
          </button>

        </div>

      </div>
    </div>
  </main>
</div>

<!-- ================= SCRIPTS ================= -->
<script>
const BASE_URL = "<?= BASE_URL ?>";
</script>

<script src="<?= BASE_URL ?>/assets/js/cashier.js"></script>
<script src="<?= BASE_URL ?>/assets/js/auth.js"></script>

</body>
</html>
