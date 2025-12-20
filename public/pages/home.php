<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-md-3">
    <div class="card p-3 shadow-sm border-start border-4 border-primary">
      <div class="text-muted small text-uppercase fw-bold">Products</div>
      <div class="d-flex align-items-center justify-content-between mt-2">
        <div id="k_products" class="h3 mb-0">—</div>
        <i class="bi bi-box-seam text-primary fs-4"></i>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card p-3 shadow-sm border-start border-4 border-info">
      <div class="text-muted small text-uppercase fw-bold">Members</div>
      <div class="d-flex align-items-center justify-content-between mt-2">
        <div id="k_members" class="h3 mb-0">—</div>
        <i class="bi bi-people text-info fs-4"></i>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card p-3 shadow-sm border-start border-4 border-success">
      <div class="text-muted small text-uppercase fw-bold">Transactions</div>
      <div class="d-flex align-items-center justify-content-between mt-2">
        <div id="k_tx" class="h3 mb-0">—</div>
        <i class="bi bi-receipt text-success fs-4"></i>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card p-3 shadow-sm border-start border-4 border-danger">
      <div class="text-muted small text-uppercase fw-bold">Low Stock (< 10)</div>
      <div class="d-flex align-items-center justify-content-between mt-2">
        <div id="k_low" class="h3 mb-0 text-danger">—</div>
        <i class="bi bi-exclamation-triangle text-danger fs-4"></i>
      </div>
    </div>
  </div>
</div>

<hr class="my-4">

<div class="row">
    <div class="col-md-6">
        <p class="text-muted small text-uppercase fw-bold mb-3">Quick Actions</p>
        <div class="d-flex gap-2 flex-wrap">
          <a class="btn btn-primary" href="?page=cashier">
            <i class="bi bi-cart"></i> Open Cashier
          </a>
          <a class="btn btn-outline-secondary" href="?page=products">
            <i class="bi bi-list-check"></i> Manage Products
          </a>
          <a class="btn btn-outline-secondary" href="?page=members">
            <i class="bi bi-person-plus"></i> Add Member
          </a>
        </div>
    </div>
    
    <div class="col-md-6">
        <p class="text-muted small text-uppercase fw-bold mb-3">System Status</p>
        <div class="alert alert-light border small">
            <i class="bi bi-database-check text-success me-2"></i> Connected to PostgreSQL
            <br>
            <i class="bi bi-clock text-muted me-2"></i> <?= date('Y-m-d H:i:s') ?>
        </div>
    </div>
</div>

<script>
async function loadDashboard(){
    try {
        // Fetch only the counts (lightweight), not the full data lists
        const response = await fetch('/api/dashboard.php');
        const data = await response.json();

        // Animate numbers (simple implementation)
        document.getElementById('k_products').innerText = data.products;
        document.getElementById('k_members').innerText = data.members;
        document.getElementById('k_tx').innerText = data.transactions;
        document.getElementById('k_low').innerText = data.low_stock;
        
    } catch (err) {
        console.error("Dashboard load failed", err);
    }
}

loadDashboard();
</script>