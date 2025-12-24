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
    SELECT stockid, name, unitprice, quantity, category, image
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

<style>
/* ===== IMAGE ===== */
.product-left {
  display: flex;
  align-items: center;
  gap: 12px;
}
.product-thumb {
  width: 48px;
  height: 48px;
  object-fit: cover;
  border-radius: 6px;
  background: #f3f4f6;
}

/* ===== GRID VIEW ===== */
.members-list.grid-view {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 16px;
}

.members-list.grid-view .product-row {
  flex-direction: column;
  align-items: flex-start;
  padding: 14px;
}

.members-list.grid-view .product-thumb {
  width: 100%;
  height: 140px;
  margin-bottom: 10px;
}

.members-list.grid-view .product-right {
  width: 100%;
  display: flex;
  justify-content: space-between;
  margin-top: 8px;
}
</style>
</head>

<body>
<div class="app-layout">
<?php include __DIR__ . '/sidebar.php'; ?>

<main class="main-content">

<!-- HEADER -->
<div class="members-header">
  <h1>Products</h1>

  <div class="members-actions">
    <input type="text" id="productSearch" placeholder="Search by name or category">

    <select id="categoryFilter" class="pretty-select">
      <option value="">All Categories</option>
      <?php
        $categories = array_unique(array_column($products, 'category'));
        foreach ($categories as $c):
      ?>
        <option value="<?= strtolower($c) ?>"><?= htmlspecialchars($c) ?></option>
      <?php endforeach; ?>
    </select>

    <!-- ✅ VIEW TOGGLE BUTTON -->
    <button type="button" class="pretty-btn" id="viewToggleBtn" onclick="toggleView()">
      🔳 Grid View
    </button>

    <button class="add-member-btn" onclick="openProductModal('add')">
      + Add Product
    </button>
  </div>
</div>

<!-- PRODUCT LIST -->
<div class="members-list" id="productList">
<?php foreach ($products as $p): ?>
  <div
    class="product-row <?= $p['quantity'] <= 10 ? 'low-stock' : '' ?>"
    data-name="<?= strtolower($p['name']) ?>"
    data-category="<?= strtolower($p['category'] ?? '') ?>"
    onclick='openProductModal("edit", <?= json_encode($p) ?>)'
  >

    <div class="product-left">
      <img
        class="product-thumb"
        src="<?= $p['image']
              ? BASE_URL . $p['image']
              : BASE_URL . '/assets/img/no-image.png' ?>"
      >
      <div>
        <div class="product-id"><?= htmlspecialchars($p['stockid']) ?></div>
        <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
        <div class="product-category">
          <?= htmlspecialchars($p['category'] ?? 'Uncategorized') ?>
        </div>
      </div>
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
      <input type="text" id="productName"
      placeholder="Name of Product">

      <label>Unit Price</label>
      <input type="number" id="productPrice" step="0.01">

      <label>Quantity</label>
      <input type="number" id="productQty">

      <label>Category</label>
      <input type="text" id="productCategory">

      <label>Product Image</label>
      <input type="file" id="productImage" accept="image/*">
    </div>

    <div class="modal-actions">
      <button type="button" class="btn-delete" id="deleteBtn"
        style="display:none;" onclick="deleteProduct()">Delete</button>

      <button type="button" class="btn-cancel"
        onclick="closeProductModal()">Cancel</button>

      <button type="button" class="btn-save"
        onclick="saveProduct()">Save</button>
    </div>
  </div>
</div>

<script>
const BASE_URL = "<?= BASE_URL ?>";

/* ===== SEARCH & FILTER ===== */
function filterProducts() {
  const keyword = document.getElementById('productSearch').value.toLowerCase();
  const cat = document.getElementById('categoryFilter').value;

  document.querySelectorAll('.product-row').forEach(row => {
    const match =
      (row.dataset.name.includes(keyword) ||
       row.dataset.category.includes(keyword)) &&
      (!cat || row.dataset.category === cat);
    row.style.display = match ? '' : 'none';
  });
}
productSearch.oninput = filterProducts;
categoryFilter.onchange = filterProducts;

/* ===== GRID / LIST TOGGLE ===== */
let isGridView = false;

function toggleView() {
  const list = document.getElementById('productList');
  const btn = document.getElementById('viewToggleBtn');

  const isGrid = list.classList.toggle('grid-view');

  // ✅ save preference
  localStorage.setItem('productViewMode', isGrid ? 'grid' : 'list');

  // update button text
  btn.textContent = isGrid ? '📋 List View' : '🔳 Grid View';
}


/* ===== MODAL ===== */
function openProductModal(mode, data = {}) {
  productModal.style.display = "flex";
  document.getElementById("mode").value = mode;
  productImage.value = "";

  if (mode === "add") {
    modalTitle.textContent = "Add Product";
    deleteBtn.style.display = "none";
    stockId.value = productName.value = productPrice.value =
    productQty.value = productCategory.value = "";
  } else {
    modalTitle.textContent = "Edit Product";
    deleteBtn.style.display = "inline-block";
    stockId.value = data.stockid;
    productName.value = data.name;
    productPrice.value = data.unitprice;
    productQty.value = data.quantity;
    productCategory.value = data.category ?? '';
  }
}

function closeProductModal() {
  productModal.style.display = "none";
}

/* ===== SAVE ===== */
function saveProduct() {
  const fd = new FormData();
  fd.append("mode", mode.value);
  fd.append("stockid", stockId.value);
  fd.append("name", productName.value);
  fd.append("price", productPrice.value);
  fd.append("stock", productQty.value);
  fd.append("category", productCategory.value);

  if (productImage.files.length) {
    fd.append("image", productImage.files[0]);
  }

  fetch(`${BASE_URL}/api/products.php`, {
    method: "POST",
    body: fd
  })
  .then(r => r.json())
  .then(d => d.error ? alert(d.error) : location.reload());
}

/* ===== DELETE ===== */
function deleteProduct() {
  if (!confirm("Delete this product?")) return;
  fetch(`${BASE_URL}/api/products.php?id=${stockId.value}`, { method: "DELETE" })
    .then(r => r.json())
    .then(d => d.error ? alert(d.error) : location.reload());
}

document.addEventListener('DOMContentLoaded', () => {
  const savedView = localStorage.getItem('productViewMode');
  const list = document.getElementById('productList');
  const btn = document.getElementById('viewToggleBtn');

  if (savedView === 'grid') {
    list.classList.add('grid-view');
    btn.textContent = '📋 List View';
  } else {
    list.classList.remove('grid-view');
    btn.textContent = '🔳 Grid View';
  }
});

</script>

</body>
</html>
