<?php
require_once __DIR__ . '/../../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>OptiPOS - Home</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
<style>
* {
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif;
}
/* ===== Header ===== */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.header h1 {
    margin: 0;
}

.header .role {
    font-weight: bold;
}

/* ===== Stat Cards ===== */
.stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    border-left: 6px solid;
}

.card.blue { border-color: #2563eb; }
.card.cyan { border-color: #06b6d4; }
.card.green { border-color: #16a34a; }
.card.red { border-color: #dc2626; }

.card h3 {
    margin: 0 0 10px;
}

.card .value {
    font-size: 26px;
    font-weight: bold;
}

/* ===== Sections ===== */
.section {
    background: white;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}

.section h3 {
    margin-top: 0;
}

/* ===== Buttons ===== */
.btn {
    padding: 10px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin-right: 10px;
}

.btn.primary { background: #2563eb; color: white; }
.btn.gray { background: #e5e7eb; }
</style>
</head>

<body>

<div class="app-layout">

  <?php include __DIR__ . '/sidebar.php'; ?>

  <main class="main-content">

        <!-- Header -->
        <div class="header">
            <div>
                <h1>Welcome to OptiPOS</h1>
                <p></p>
            </div>
            <div class="role">
               <?= htmlspecialchars($_SESSION['user']['role'] ?? 'Staff') ?>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats">
            <div class="card blue">
                <h3>PRODUCTS</h3>
                <div class="value" id="productsCount">—</div>
            </div>

            <div class="card cyan">
                <h3>MEMBERS</h3>
                <div class="value" id="membersCount">—</div>
            </div>

            <div class="card green">
                <h3>TRANSACTIONS</h3>
                <div class="value" id="transactionsCount">—</div>
            </div>

            <div class="card red">
                <h3>LOW STOCK (&lt; 10)</h3>
                <div class="value" id="lowStockCount">—</div>
            </div>
        </div>

        <!-- Quick Actions -->
       <div class="section">
            <h3>Quick Actions</h3>

            <button class="btn primary"
                    onclick="goTo('cashier')">
                Open Cashier
            </button>

            <button class="btn gray"
                    onclick="goTo('products')">
                Manage Products
            </button>

            <button class="btn gray"
                    onclick="goTo('members')">
                Add Member
            </button>
        </div>


        <!-- System Status -->
        <div class="section">
            <h3>System Status</h3>
            <p id="dbStatusText">Checking database...</p>
            <small id="dbStatusTime">—</small>
        </div>
</main>

    </div>
</body>

<script>
fetch("<?= BASE_URL ?>/api/home.php")
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            console.error(data.error);
            return;
        }

        document.getElementById("productsCount").textContent = data.products;
        document.getElementById("membersCount").textContent = data.members;
        document.getElementById("transactionsCount").textContent = data.transactions;
        document.getElementById("lowStockCount").textContent = data.low_stock;

        const statusText = document.getElementById("dbStatusText");
        const statusTime = document.getElementById("dbStatusTime");

        if (data.db_status === "connected") {
            statusText.textContent = "🟢 Connected to PostgreSQL";
            statusText.style.color = "#16a34a";
        } else {
            statusText.textContent = "🔴 Database connection failed";
            statusText.style.color = "#dc2626";
        }

        statusTime.textContent = data.checked_at;
    })
    .catch(err => {
        document.getElementById("dbStatusText").textContent =
            "🔴 Unable to reach server";
        document.getElementById("dbStatusText").style.color = "#dc2626";
        console.error("Dashboard fetch failed", err);
    });
</script>
<script>
  const BASE_URL = "<?= BASE_URL ?>";
</script>
<script src="<?= BASE_URL ?>/assets/js/auth.js"></script>
<script>
function goTo(page) {
    window.location.href = "<?= BASE_URL ?>/index.php?page=" + page;
}
</script>

</body>
</html>
