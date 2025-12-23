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


$stmt = $pdo->query("
    SELECT 
        stockid,
        name,
        unitprice,
        quantity,
        category
    FROM stock
    ORDER BY name
");

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Products</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
</head>
<body>

<div class="app-layout">

<?php include __DIR__ . '/sidebar.php'; ?>
  <!-- MAIN -->
  <main class="main-content">

    <!-- HEADER -->
    <div class="members-header">
      <h1>Products</h1>

      <div class="members-actions">
        <input
          type="text"
          id="productSearch"
          placeholder="Search by name or category"
        >
        <button class="add-member-btn" onclick="openProductModal('add')">
          + Add Product
        </button>
      </div>
    </div>

    <!-- PRODUCT LIST -->
    <div class="members-list">

      <?php if (empty($products)): ?>
        <div class="empty">No products found</div>
      <?php else: ?>
        <?php foreach ($products as $p): ?>
          <div
            class="product-row <?= $p['quantity'] <= 10 ? 'low-stock' : '' ?>"
            data-name="<?= strtolower($p['name']) ?>"
            data-category="<?= strtolower($p['category']) ?>"
            onclick='openProductModal("edit", <?= json_encode($p) ?>)'
            >

            <div class="product-left">
              <div class="product-id"><?= htmlspecialchars($p['stockid']) ?></div>
              <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
              <div class="product-category"><?= htmlspecialchars($p['category']) ?></div>
            </div>

            <div class="product-right">
              <div class="product-price">
                RM <?= number_format($p['unitprice'], 2) ?>
              </div>
              <div class="product-stock <?= $p['quantity'] <= 10 ? 'low-stock' : '' ?>">
                Stock: <?= intval($p['quantity']) ?>
                <?php if ($p['quantity'] <= 10): ?>
                  <span class="stock-warning">⚠ Low</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

    </div>

  </main>
</div>

<!-- PRODUCT MODAL -->
<div class="modal-overlay" id="productModal" style="display:none;">
  <div class="modal-card">

    <h2 id="modalTitle">Add Product</h2>

    <input type="hidden" id="mode">
    <input type="hidden" id="stockId">

    <div class="modal-form">
      <label>Name</label>
      <input type="text" id="productName">

      <label>Unit Price</label>
      <input type="number" id="productPrice" step="0.01">

      <label>Quantity</label>
      <input type="number" id="productQty">

      <label>Category</label>
      <input type="text" id="productCategory">
    </div>

    <div class="modal-actions">
      <button
        class="btn-delete"
        id="deleteBtn"
        style="display:none;"
        onclick="deleteProduct()"
      >
        Delete
      </button>

      <button class="btn-cancel" onclick="closeProductModal()">Cancel</button>
      <button class="btn-save" onclick="saveProduct()">Save</button>
    </div>

  </div>
</div>
<script>
  const BASE_URL = "<?= BASE_URL ?>";
</script>
<script src="<?= BASE_URL ?>/assets/js/auth.js"></script>

<script>
/* ---------- SEARCH ---------- */
document.getElementById("productSearch").addEventListener("input", function () {
  const keyword = this.value.toLowerCase();
  document.querySelectorAll(".product-row").forEach(row => {
    const match =
      row.dataset.name.includes(keyword) ||
      row.dataset.category.includes(keyword);
    row.style.display = match ? "flex" : "none";
  });
});

/* ---------- MODAL ---------- */
function openProductModal(mode, data = {}) {
  document.getElementById("productModal").style.display = "flex";
  document.getElementById("mode").value = mode;

  if (mode === "add") {
    document.getElementById("modalTitle").textContent = "Add Product";
    document.getElementById("deleteBtn").style.display = "none";

    document.getElementById("stockId").value = data.stockid;
    document.getElementById("productName").value = data.name;
    document.getElementById("productPrice").value = data.unitprice;
    document.getElementById("productQty").value = data.quantity;
    document.getElementById("productCategory").value = data.category;

  } else {
    document.getElementById("modalTitle").textContent = "Edit Product";
    document.getElementById("deleteBtn").style.display = "inline-block";

    document.getElementById("stockId").value = data.stockid;
    document.getElementById("productName").value = data.name;
    document.getElementById("productPrice").value = data.unitprice;
    document.getElementById("productQty").value = data.quantity;
    document.getElementById("productCategory").value = data.category;
  }
}

function closeProductModal() {
  document.getElementById("productModal").style.display = "none";
}

/* ---------- SAVE ---------- */
function saveProduct() {
  const mode = document.getElementById("mode").value;

  const payload = {
    stockid: document.getElementById("stockId").value,
    name: document.getElementById("productName").value,
    price: document.getElementById("productPrice").value,
    stock: document.getElementById("productQty").value,
    category: document.getElementById("productCategory").value
  };

  fetch(`${BASE_URL}/api/products.php`, {
    method: mode === "add" ? "POST" : "PUT",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload)
  })
  .then(res => res.json())
  .then(data => {
    if (data.error) {
      alert(data.error);
    } else {
      location.reload();
    }
  });
}


/* ---------- DELETE ---------- */
function deleteProduct() {
  if (!confirm("Delete this product?")) return;

  fetch("api/delete_product.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      stockId: document.getElementById("stockId").value
    })
  })
  .then(() => location.reload());
}
</script>
</body>
</html>
