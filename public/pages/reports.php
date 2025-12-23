<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';

if (empty($_SESSION['user'])) {
    header("Location: " . BASE_URL . "/index.php?page=login");
    exit;
}

if ($_SESSION['user']['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php?page=no_permission");
    exit;
}

$startDate = $_GET['start_date'] ?? null;
$endDate   = $_GET['end_date'] ?? null;

$where = "";
$params = [];

if ($startDate && $endDate) {
    $where = "WHERE t.payment_date BETWEEN :start AND :end";
    $params = [
        'start' => $startDate,
        'end'   => $endDate
    ];
}


/* ================= SALES SUMMARY ================= */
$totalSales = 0;
$totalTax = 0;
$totalDiscount = 0;

$sqlSummary = "
    SELECT
        COUNT(*) AS total_transactions,
        SUM(o.grandtotal) AS total_sales,
        SUM(o.tax) AS total_tax,
        SUM(o.discount) AS total_discount
    FROM transactions t
    JOIN orders o ON t.orderid = o.orderid
    $where
";

$stmt = $pdo->prepare($sqlSummary);
$stmt->execute($params);
$summary = $stmt->fetch(PDO::FETCH_ASSOC);

$totalSales     = $summary['total_sales'] ?? 0;
$totalTax       = $summary['total_tax'] ?? 0;
$totalDiscount  = $summary['total_discount'] ?? 0;
$totalCount     = $summary['total_transactions'] ?? 0;


/* ================= SALES BY DATE ================= */
$sqlSalesByDate = "
    SELECT
        t.payment_date AS date,
        SUM(o.grandtotal) AS total
    FROM transactions t
    JOIN orders o ON t.orderid = o.orderid
    $where
    GROUP BY t.payment_date
    ORDER BY t.payment_date
";

$stmt = $pdo->prepare($sqlSalesByDate);
$stmt->execute($params);
$salesByDate = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);


/* ================= PAYMENT SUMMARY ================= */
$sqlPayment = "
    SELECT
        t.paymentmethod,
        SUM(o.grandtotal) AS total
    FROM transactions t
    JOIN orders o ON t.orderid = o.orderid
    $where
    GROUP BY t.paymentmethod
";

$stmt = $pdo->prepare($sqlPayment);
$stmt->execute($params);
$paymentSummary = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);



/* ================= LOW STOCK ================= */
$sqlLowStock = "
    SELECT name, quantity
    FROM stock
    WHERE quantity <= 10
    ORDER BY quantity ASC
";

$lowStock = $pdo->query($sqlLowStock)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reports</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<script>
const BASE_URL = "<?= BASE_URL ?>";
</script>
<script src="<?= BASE_URL ?>/assets/js/auth.js"></script>

<body>

<div class="app-layout">
  <?php include __DIR__ . '/sidebar.php'; ?>

  <!-- MAIN -->
  <main class="main-content">
    <h1>Reports</h1>

  <form method="get"
      action="<?= BASE_URL ?>/index.php"
      class="date-filter">

  <!-- 关键：保留 page -->
  <input type="hidden" name="page" value="reports">

  <label>
    From:
    <input type="date" name="start_date"
           value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">
  </label>

  <label>
    To:
    <input type="date" name="end_date"
           value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">
  </label>

  <button type="submit" class="chart-btn">
    Filter
  </button>
</form>



    <button class="chart-btn" onclick="toggleCharts()" id="chartToggleBtn">
      📊 View Charts
    </button>


    <!-- SUMMARY -->
    <div class="report-card">
      <h2>Sales Summary</h2>
      <div class="report-grid">
        <div>Total Sales<br><strong>RM <?= number_format($totalSales,2) ?></strong></div>
        <div>Transactions<br><strong><?= $totalCount ?></strong></div>
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
let isChartView = false;


function toggleCharts() {
  const table = document.getElementById("tableView");
  const chart = document.getElementById("chartView");
  const btn = document.getElementById("chartToggleBtn");

  if (!isChartView) {
    btn.textContent = "📋 Back to Table";
    table.style.display = "none";
    chart.style.display = "block";
    renderCharts();
    isChartView = true;
  } else {
    btn.textContent = "📊 View Charts";
    chart.style.display = "none";
    table.style.display = "block";
    isChartView = false;
  }
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

<script src="<?= BASE_URL ?>/assets/js/auth.js"></script>
</body>
</html>
