<?php
// Load transactions from JSON
$transactionsFile = __DIR__ . '/../data/transactions.json';

if (!file_exists($transactionsFile)) {
    $transactions = [];
} else {
    $transactions = json_decode(file_get_contents($transactionsFile), true);
    if (!is_array($transactions)) {
        $transactions = [];
    }
}
?>

<link rel="stylesheet" href="../assets/css/app.css">
<script src="../assets/js/auth.js"></script>

<div class="app-layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-header">
      <h2>POS System</h2>
    </div>

    <nav class="sidebar-menu">
      <a href="index.php?page=home">Home</a>
      <a href="index.php?page=cashier">Cashier</a>
      <a href="index.php?page=products">Products</a>
      <a href="index.php?page=members">Members</a>
      <a href="index.php?page=transactions" class="active">Transactions</a>
      <a href="index.php?page=reports">Reports</a>
      <a href="index.php?page=profile">Profile</a>
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
              <strong><?= htmlspecialchars($trx['id']) ?></strong>
              <div class="trx-date">
                <?= htmlspecialchars($trx['date']) ?>
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

        <button class="print-btn" onclick="window.print()">Print Receipt</button>
      </section>

    </div>
  </main>

</div>

<!-- ================= JS (T2) ================= -->
<script>
const transactions = <?= json_encode($transactions) ?>;

const itemsBox   = document.getElementById("receiptItems");
const subtotalEl = document.getElementById("rSubtotal");
const taxEl      = document.getElementById("rTax");
const discountEl = document.getElementById("rDiscount");
const totalEl    = document.getElementById("rTotal");
const paymentEl  = document.getElementById("rPayment");

function renderReceipt(index) {
  const trx = transactions[index];
  if (!trx) return;

  itemsBox.innerHTML = "";

  trx.items.forEach(item => {
    const row = document.createElement("div");
    row.className = "receipt-item";
    row.innerHTML = `
      <span>${item.name} x ${item.qty}</span>
      <span>RM ${(item.price * item.qty).toFixed(2)}</span>
    `;
    itemsBox.appendChild(row);
  });

  subtotalEl.textContent = `RM ${trx.subtotal.toFixed(2)}`;
  taxEl.textContent      = `RM ${trx.tax.toFixed(2)}`;
  discountEl.textContent = `- RM ${trx.discount.toFixed(2)}`;
  totalEl.textContent    = `RM ${trx.total.toFixed(2)}`;
  paymentEl.textContent  = trx.payment;
}

// Load first transaction by default
if (transactions.length > 0) {
  renderReceipt(0);
}

// Click to change receipt
document.querySelectorAll(".transaction-item").forEach(item => {
  item.addEventListener("click", () => {
    document.querySelectorAll(".transaction-item")
      .forEach(i => i.classList.remove("active"));

    item.classList.add("active");
    renderReceipt(item.dataset.index);
  });
});
</script>

<script>
const searchInput = document.getElementById("transactionSearch");

searchInput.addEventListener("input", () => {
  const keyword = searchInput.value.toLowerCase();

  document.querySelectorAll(".transaction-item").forEach(item => {
    const index = item.dataset.index;
    const trx = transactions[index];

    const match =
      trx.id.toLowerCase().includes(keyword) ||
      trx.payment.toLowerCase().includes(keyword) ||
      (trx.memberId && trx.memberId.toLowerCase().includes(keyword));

    item.style.display = match ? "block" : "none";
  });
});
</script>
