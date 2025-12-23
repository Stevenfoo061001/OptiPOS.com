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

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">

<style>
body { background:#f5f6f8; }
.panel {
    background:#fff;
    border-radius:10px;
    padding:16px;
    margin-bottom:20px;
}
.wrapper {
    display: flex;
    min-height: 100vh;
}

.main {
    flex: 1;
    padding: 30px;
}

.product-card { cursor:pointer; }
.cart-item { border-bottom:1px solid #eee; padding:8px 0; }
</style>
</head>
<body>


<!-- PAYMENT METHOD MODAL -->
<div class="modal fade" id="paymentModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content p-3">

      <h5 class="mb-3">Payment Method</h5>

      <div class="row g-3" id="paymentButtons">
        <div class="col-md-4">
          <button class="btn btn-outline-secondary w-100 py-3"
                  onclick="choosePayment('Visa', this)">Visa</button>
        </div>
        <div class="col-md-4">
          <button class="btn btn-outline-secondary w-100 py-3"
                  onclick="choosePayment('Debit Card', this)">Debit Card</button>
        </div>
        <div class="col-md-4">
          <button class="btn btn-outline-secondary w-100 py-3"
                  onclick="choosePayment('Cash', this)">Cash</button>
        </div>

        <div class="col-md-4">
          <button class="btn btn-outline-secondary w-100 py-3"
                  onclick="choosePayment('TnG', this)">TnG</button>
        </div>
        <div class="col-md-4">
          <button class="btn btn-outline-secondary w-100 py-3"
                  onclick="choosePayment('Alipay', this)">Alipay</button>
        </div>
        <div class="col-md-4">
          <button class="btn btn-outline-secondary w-100 py-3"
                  onclick="choosePayment('Online', this)">Online</button>
        </div>
      </div>

      <div class="mt-4 d-flex justify-content-end gap-2">
        <span class="me-auto text-muted" id="selectedPaymentText">
            No payment selected
        </span>
        <button class="btn btn-secondary" data-bs-dismiss="modal">
            Cancel
        </button>
        <button class="btn btn-success"
                onclick="confirmPayment()">
            Confirm Payment
        </button>
      </div>

    </div>
  </div>
</div>

<div class="wrapper">

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-header"><h2>POS System</h2></div>
    <nav class="sidebar-menu">
        <a href="<?= BASE_URL ?>/index.php?page=home">Home</a>
        <a href="<?= BASE_URL ?>/index.php?page=cashier" class="active">Cashier</a>
        <a href="<?= BASE_URL ?>/index.php?page=products">Products</a>
        <a href="<?= BASE_URL ?>/index.php?page=members">Members</a>
        <a href="<?= BASE_URL ?>/index.php?page=transactions">Transactions</a>
        <a href="<?= BASE_URL ?>/index.php?page=reports">Reports</a>
        <a href="<?= BASE_URL ?>/index.php?page=profile">Profile</a>
    </nav>
    <div class="sidebar-footer">
        <button class="logout-btn" onclick="logout()">Logout</button>
    </div>
</aside>

<!-- MAIN -->
<div class="main">

<div class="header d-flex justify-content-between mb-3">
    <div>
        <h3>Cashier</h3>
        <small class="text-muted">Manage sales & checkout</small>
    </div>
    <strong><?= htmlspecialchars($_SESSION['user']['role'] ?? 'Staff') ?></strong>
</div>

<div class="row g-3">

<!-- PRODUCTS -->
<div class="col-md-8">
    <div class="panel">
        <div class="d-flex gap-2 mb-3">
            <input id="searchInput" class="form-control" placeholder="Search product name or ID">
            <select id="categoryFilter" class="form-select w-25">
                <option value="All">All Categories</option>
            </select>
        </div>

        <div id="productGrid" class="row g-2">
            <div class="text-muted text-center">Loading products…</div>
        </div>
    </div>
</div>

<!-- CART -->
<div class="col-md-4">
  <!-- MEMBER SEARCH -->
<div class="panel mb-3">
    <label class="form-label fw-semibold">Member</label>

    <div class="input-group">
        <input
            type="text"
            id="memberSearchInput"
            class="form-control"
            placeholder="Member Phone / ID"
        >
        <button class="btn btn-primary" type="button" onclick="findMember()">
          Find
        </button>

    </div>

    <div id="memberResult" class="mt-2 text-muted">
        No member selected
    </div>
</div>

    <div class="panel mb-3" id="cartPanel">
        <div class="text-muted text-center mt-5">Cart is empty</div>
    </div>

    <div class="panel">
        <div class="d-flex justify-content-between">
            <span>Subtotal</span>
            <span id="subtotalValue">RM 0.00</span>
        </div>
        <div class="d-flex justify-content-between mb-1 text-danger">
                        <span>Discount</span>
                       <span id="discountValue">-0.00</span>
        </div>
        <div class="d-flex justify-content-between mb-1 text-success">
                        <span>Points</span>
                        <span id="pointsValue">+0</span>
        </div>
        <div class="d-flex justify-content-between">
            <span>Tax (6%)</span>
            <span id="taxValue">RM 0.00</span>
        </div>
        <hr>
        <div class="d-flex justify-content-between fw-bold fs-5">
            <span>Total</span>
            <span id="totalValue">RM 0.00</span>
        </div>

        <button class="btn btn-success w-100 mt-3"
                onclick="openPaymentModal()">
            PAY NOW
        </button>
    </div>
</div>

</div>
</div>
</div>

<!-- ================= JS (原 cashier.js，内嵌版) ================= -->
<script>
const BASE_URL = "<?= BASE_URL ?>";
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/cashier.js"></script>
<script src="<?= BASE_URL ?>/assets/js/auth.js"></script>
</body>
</html>
