<?php
/* ================= LOAD DATA ================= */
$transactionsFile = __DIR__ . '/../data/transactions.json';
$productsFile     = __DIR__ . '/../data/products.json';

$transactions = file_exists($transactionsFile)
  ? json_decode(file_get_contents($transactionsFile), true)
  : [];

$products = file_exists($productsFile)
  ? json_decode(file_get_contents($productsFile), true)
  : [];

if (!is_array($transactions)) $transactions = [];
if (!is_array($products)) $products = [];

/* ================= SALES SUMMARY ================= */
$totalSales = 0;
$totalTax = 0;
$totalDiscount = 0;

foreach ($transactions as $t) {
  $totalSales += $t['total'];
  $totalTax += $t['tax'];
  $totalDiscount += $t['discount'];
}

/* ================= SALES BY DATE ================= */
$salesByDate = [];
foreach ($transactions as $t) {
  $date = substr($t['date'], 0, 10);
  if (!isset($salesByDate[$date])) {
    $salesByDate[$date] = 0;
  }
  $salesByDate[$date] += $t['total'];
}

/* ================= PAYMENT SUMMARY ================= */
$paymentSummary = [];
foreach ($transactions as $t) {
  $paymentSummary[$t['payment']] =
    ($paymentSummary[$t['payment']] ?? 0) + $t['total'];
}

/* ================= LOW STOCK ================= */
$lowStock = array_filter($products, fn($p) => $p['quantity'] <= 10);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reports</title>
  <link rel="stylesheet" href="../assets/css/app.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="app-layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-header"><h2>POS System</h2></div>
    <nav class="sidebar-menu">
      <a href="index.php?page=home">Home</a>
      <a href="index.php?page=cashier">Cashier</a>
      <a href="index.php?page=products">Products</a>
      <a href="index.php?page=members">Members</a>
      <a href="index.php?page=transactions">Transactions</a>
      <a href="index.php?page=reports" class="active">Reports</a>
      <a href="index.php?page=profile">Profile</a>
    </nav>
    <div class="sidebar-footer">
      <button class="logout-btn" onclick="logout()">Logout</button>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="main-content">
    <h1>Reports</h1>

    <button class="chart-btn" onclick="toggleCharts()">
      📊 View Charts
    </button>

    <!-- SUMMARY -->
    <div class="report-card">
      <h2>Sales Summary</h2>
      <div class="report-grid">
        <div>Total Sales<br><strong>RM <?= number_format($totalSales,2) ?></strong></div>
        <div>Transactions<br><strong><?= count($transactions) ?></strong></div>
        <div>Tax Collected<br><strong>RM <?= number_format($totalTax,2) ?></strong></div>
        <div>Discount Given<br><strong>RM <?= number_format($totalDiscount,2) ?></strong></div>
      </div>
    </div>

    <!-- TABLE VIEW -->
    <div id="tableView">

      <div class="report-card">
        <h2>Sales by Date</h2>
        <table class="report-table">
          <tr><th>Date</th><th>Total</th></tr>
          <?php foreach ($salesByDate as $d => $v): ?>
            <tr>
              <td><?= $d ?></td>
              <td>RM <?= number_format($v,2) ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>

      <div class="report-card">
        <h2>Payment Methods</h2>
        <table class="report-table">
          <tr><th>Method</th><th>Total</th></tr>
          <?php foreach ($paymentSummary as $m => $v): ?>
            <tr>
              <td><?= $m ?></td>
              <td>RM <?= number_format($v,2) ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>

      <div class="report-card">
        <h2>Low Stock Items</h2>
        <?php if (empty($lowStock)): ?>
          <p>No low stock items 🎉</p>
        <?php else: ?>
          <table class="report-table">
            <tr><th>Product</th><th>Stock</th></tr>
            <?php foreach ($lowStock as $p): ?>
              <tr class="low-stock">
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td><?= $p['quantity'] ?> ⚠</td>
              </tr>
            <?php endforeach; ?>
          </table>
        <?php endif; ?>
      </div>

    </div>

    <!-- CHART VIEW -->
    <div id="chartView" style="display:none;">
      <div class="chart-tabs">
        <button class="chart-tab active" onclick="showChart('sales')">Sales</button>
        <button class="chart-tab" onclick="showChart('payment')">Payments</button>
      </div>

      <div class="chart-box" id="salesChartBox">
        <canvas id="salesChart"></canvas>
      </div>

      <div class="chart-box" id="paymentChartBox" style="display:none;">
        <canvas id="paymentChart"></canvas>
      </div>
    </div>

  </main>
</div>

<script>
const salesData = <?= json_encode($salesByDate) ?>;
const paymentData = <?= json_encode($paymentSummary) ?>;

let salesChart, paymentChart;

function toggleCharts() {
  document.getElementById("tableView").style.display = "none";
  document.getElementById("chartView").style.display = "block";
  renderCharts();
}

function showChart(type) {
  document.getElementById("salesChartBox").style.display =
    type === "sales" ? "block" : "none";
  document.getElementById("paymentChartBox").style.display =
    type === "payment" ? "block" : "none";

  document.querySelectorAll(".chart-tab").forEach(b => b.classList.remove("active"));
  event.target.classList.add("active");
}

function renderCharts() {
  if (!salesChart) {
    salesChart = new Chart(document.getElementById("salesChart"), {
      type: "bar",
      data: {
        labels: Object.keys(salesData),
        datasets: [{
          label: "Sales (RM)",
          data: Object.values(salesData),
          backgroundColor: "#2563eb"
        }]
      },
      options: { responsive:true, maintainAspectRatio:false }
    });
  }

  if (!paymentChart) {
    paymentChart = new Chart(document.getElementById("paymentChart"), {
      type: "pie",
      data: {
        labels: Object.keys(paymentData),
        datasets: [{
          data: Object.values(paymentData),
          backgroundColor: [
            "#16a34a","#2563eb","#f59e0b","#ef4444","#9333ea"
          ]
        }]
      },
      options: { responsive:true, maintainAspectRatio:false }
    });
  }
}
</script>

<script src="../assets/js/auth.js"></script>
</body>
</html>
