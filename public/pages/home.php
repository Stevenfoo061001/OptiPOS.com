<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-md-3">
    <div class="card p-3">
      <div class="muted small">Products</div>
      <div id="k_products" class="h4">—</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card p-3">
      <div class="muted small">Members</div>
      <div id="k_members" class="h4">—</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card p-3">
      <div class="muted small">Transactions</div>
      <div id="k_tx" class="h4">—</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card p-3">
      <div class="muted small">Low stock items</div>
      <div id="k_low" class="h4 text-danger">—</div>
    </div>
  </div>
</div>

<hr>

<p class="muted small">Quick actions</p>
<div class="d-flex gap-2">
  <a class="btn btn-primary" href="?page=cashier">Open Cashier</a>
  <a class="btn btn-outline-secondary" href="?page=products">Manage Products</a>
</div>

<script>
async function loadDashboard(){
  const [products, members, tx] = await Promise.all([
    fetch('/api/products.php').then(r=>r.json()),
    fetch('/api/members.php').then(r=>r.json()),
    fetch('/api/transactions.php').then(r=>r.json())
  ]);
  document.getElementById('k_products').innerText = products.length;
  document.getElementById('k_members').innerText = members.length;
  document.getElementById('k_tx').innerText = tx.length;
  // low stock count: stock <= reorder
  let low = 0;
  products.forEach(p=>{ if (p.reorder && (p.stock <= p.reorder)) low++; });
  document.getElementById('k_low').innerText = low;
}
loadDashboard();
</script>
