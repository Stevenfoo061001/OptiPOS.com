<?php
// session already started in index.php
$cart = $_SESSION['cart'] ?? [];

$subtotal = 0;
foreach ($cart as $item) {
  $subtotal += $item['price'] * $item['qty'];
}

$tax = $subtotal * 0.06;
$total = $subtotal + $tax;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cashier</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- App CSS -->
  <link rel="stylesheet" href="../assets/css/app.css">

  <style>
    body { background:#f4f4f4; }

    .panel {
      background:#e0e0e0;
      padding:20px;
      border-radius:10px;
    }

    .pay-btn {
      background:#fff;
      border:0;
      border-radius:10px;
      padding:20px;
      width:100%;
      box-shadow:0 2px 4px rgba(0,0,0,.15);
      font-weight:600;
    }

    .pay-btn.active {
      outline:3px solid #2563eb;
    }

    .order-box {
      background:#fff;
      padding:20px;
      border-radius:10px;
      height:100%;
    }
  </style>
</head>
<body>

<div class="app-layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-header">
      <h2>POS System</h2>
    </div>

    <nav class="sidebar-menu">
      <a href="index.php?page=home">Home</a>
      <a href="index.php?page=cashier" class="active">Cashier</a>
      <a href="index.php?page=products">Products</a>
      <a href="index.php?page=members">Members</a>
      <a href="index.php?page=transactions">Transactions</a>
      <a href="index.php?page=reports">Reports</a>
      <a href="index.php?page=profile">Profile</a>
    </nav>

    <div class="sidebar-footer">
      <button class="logout-btn" onclick="logout()">Logout</button>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="main-content">

    <h1>Cashier</h1>

    <div class="row g-3">

      <!-- LEFT -->
      <div class="col-md-8">
        <div class="panel">

          <!-- MEMBER -->
          <h5>Member</h5>
          <div class="mb-3 d-flex gap-2">
            <input
              type="text"
              id="memberInput"
              class="form-control"
              placeholder="Member ID / Phone"
            >
            <button class="btn btn-primary" onclick="searchMember()">
              Search
            </button>
          </div>

          <div id="memberResult" class="mb-2" style="display:none;">
            <strong id="memberName"></strong><br>
            Points: <span id="memberPoints"></span>
          </div>

          <p id="memberError" class="text-danger" style="display:none;">
            Member not found
          </p>

          <!-- PAYMENT -->
          <h5 class="mt-4">Payment Method</h5>
          <div class="row g-3">
            <div class="col-4"><button class="pay-btn payment-btn" data-method="Visa">Visa</button></div>
            <div class="col-4"><button class="pay-btn payment-btn" data-method="Debit Card">Debit Card</button></div>
            <div class="col-4"><button class="pay-btn payment-btn" data-method="Cash">Cash</button></div>
            <div class="col-4"><button class="pay-btn payment-btn" data-method="TnG">TnG</button></div>
            <div class="col-4"><button class="pay-btn payment-btn" data-method="Alipay">Alipay</button></div>
            <div class="col-4"><button class="pay-btn payment-btn" data-method="Online">Online</button></div>
          </div>

        </div>
      </div>

      <!-- RIGHT -->
      <div class="col-md-4">
        <div class="order-box">

          <h5>Orders</h5>

          <?php if (empty($cart)): ?>
            <p class="text-muted">Cart is empty</p>
          <?php else: ?>
            <?php foreach ($cart as $item): ?>
              <div class="d-flex justify-content-between">
                <div><?= htmlspecialchars($item['name']) ?> x<?= $item['qty'] ?></div>
                <div>RM <?= number_format($item['price'] * $item['qty'], 2) ?></div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

          <hr>

          <div class="d-flex justify-content-between">
            <small>Subtotal</small>
            <small id="subtotalValue" data-value="<?= $subtotal ?>">
              RM <?= number_format($subtotal, 2) ?>
            </small>
          </div>

          <div class="d-flex justify-content-between">
            <small>Tax (6%)</small>
            <small id="taxValue">
              RM <?= number_format($tax, 2) ?>
            </small>
          </div>

          <div
            class="d-flex justify-content-between text-danger"
            id="memberDiscountRow"
            style="display:none;"
          >
            <small>Member Discount</small>
            <small id="memberDiscount">- RM 0.00</small>
          </div>

          <hr>

          <h5 class="text-end" id="totalValue">
            RM <?= number_format($total, 2) ?>
          </h5>

          <button
            class="btn btn-primary w-100 mt-3"
            id="confirmPaymentBtn"
            disabled
          >
            Checkout
          </button>

        </div>
      </div>

    </div>
  </main>
</div>

<!-- CASH MODAL -->
<div class="modal fade" id="cashModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content p-3">

      <h5>Cash Payment</h5>

      <div class="mb-2">
        <label>Total</label>
        <input id="cashTotal" class="form-control" readonly>
      </div>

      <div class="mb-2">
        <label>Cash Received</label>
        <input id="cashReceived" class="form-control" type="number" min="0" step="0.01">
      </div>

      <div class="mb-2">
        <label>Change</label>
        <input id="changeAmount" class="form-control" readonly>
      </div>

      <div class="text-end">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
          Cancel
        </button>
        <button class="btn btn-primary btn-sm" id="confirmCashBtn" disabled>
          Confirm
        </button>
      </div>

    </div>
  </div>
</div>

<!-- SUCCESS MODAL -->
<div class="modal-overlay" id="paymentModal" style="display:none;">
  <div class="modal-card">
    <div class="modal-icon">✅</div>
    <h2>Payment Successful</h2>

    <p><strong>Method:</strong> <span id="modalPaymentMethod"></span></p>
    <p><strong>Total Paid:</strong> <span id="modalTotalPaid"></span></p>

    <button class="modal-btn" onclick="finishPayment()">Back to Home</button>
  </div>
</div>

<!-- SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/auth.js"></script>
<script src="../assets/js/cashier.js"></script>

<script>
  const CART_HAS_ITEMS = <?= empty($cart) ? 'false' : 'true' ?>;
</script>

</body>
</html>
