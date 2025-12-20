<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Product Inventory</h3>
    <button class="btn btn-primary" id="btnShowAdd" style="display:none">
        <i class="bi bi-box-seam"></i> Add Product
    </button>
</div>

<datalist id="catList">
  <option value="Food">
  <option value="Beverage">
  <option value="Snack">
  <option value="Dessert">
  <option value="Stationery">
  <option value="Electronics">
</datalist>

<div id="addFormCard" class="card shadow-sm mb-4" style="display:none; border-left: 4px solid #198754;">
    <div class="card-body">
        <h5 class="card-title mb-3">New Product</h5>
        <div class="row g-2">
            <div class="col-md-4">
                <label class="form-label small text-muted">Product Name</label>
                <input id="p_name" class="form-control" placeholder="e.g. Green Tea Latte">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Category</label>
                <input id="p_cat" class="form-control" list="catList" placeholder="Select or type..." maxlength="20">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Price (RM)</label>
                <input id="p_price" type="number" step="0.01" class="form-control" placeholder="0.00">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Quantity</label>
                <input id="p_qty" type="number" class="form-control" placeholder="0">
            </div>
        </div>
        <div class="mt-3 text-end">
            <button id="cancelAdd" class="btn btn-light btn-sm me-2">Cancel</button>
            <button id="saveProduct" class="btn btn-success btn-sm">Save Product</button>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">ID</th>
                    <th>Name</th>
                    <th>Category</th> <th class="text-end">Price</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody id="productTableBody"></tbody>
        </table>
    </div>
</div>

<script>
let currentUserRole = '<?php echo $_SESSION['user']['role'] ?? 'cashier'; ?>';

if (currentUserRole === 'admin') {
    document.getElementById('btnShowAdd').style.display = 'block';
}

// UI Toggles
document.getElementById('btnShowAdd').addEventListener('click', () => document.getElementById('addFormCard').style.display = 'block');
document.getElementById('cancelAdd').addEventListener('click', () => document.getElementById('addFormCard').style.display = 'none');

// Load Products
async function loadProducts() {
    const res = await fetch('/api/products.php');
    const data = await res.json();
    const tbody = document.getElementById('productTableBody');

    tbody.innerHTML = data.map(p => `
        <tr>
            <td class="ps-3 fw-bold text-primary">${p.stockid}</td>
            <td class="fw-medium">${escapeHtml(p.name)}</td>
            <td><span class="badge bg-light text-dark border">${escapeHtml(p.category || 'General')}</span></td>
            <td class="text-end text-success fw-bold">${Number(p.unitprice).toFixed(2)}</td>
            <td class="text-end">${p.quantity}</td>
            <td class="text-end">
                ${currentUserRole === 'admin' ? `
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="openEdit('${p.stockid}')">Edit</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="openDelete('${p.stockid}')">Delete</button>
                ` : '<span class="text-muted small">View Only</span>'}
            </td>
        </tr>
    `).join('');
}

// Save New Product
document.getElementById('saveProduct').addEventListener('click', async () => {
    const payload = {
        name: document.getElementById('p_name').value.trim(),
        category: document.getElementById('p_cat').value.trim() || 'General',
        price: parseFloat(document.getElementById('p_price').value),
        stock: parseInt(document.getElementById('p_qty').value) || 0
    };

    if (!payload.name || isNaN(payload.price)) {
        showAlert('Name and Price are required', 'warning');
        return;
    }

    const res = await fetch('/api/products.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    });
    
    if (res.ok) {
        showAlert('Product added successfully', 'success');
        document.getElementById('addFormCard').style.display = 'none';
        document.getElementById('p_name').value = '';
        document.getElementById('p_cat').value = '';
        document.getElementById('p_price').value = '';
        document.getElementById('p_qty').value = '';
        loadProducts();
    } else {
        showAlert('Failed to add product', 'danger');
    }
});

// Edit Product
window.openEdit = async function(id) {
    const res = await fetch('/api/products.php');
    const list = await res.json();
    const p = list.find(x => x.stockid === id);

    const body = `
        <div class="mb-2"><label>Name</label><input id="e_name" class="form-control" value="${escapeHtml(p.name)}"></div>
        <div class="mb-2"><label>Category</label><input id="e_cat" class="form-control" list="catList" value="${escapeHtml(p.category || 'General')}" maxlength="20"></div>
        <div class="row">
            <div class="col"><label>Price</label><input id="e_price" type="number" class="form-control" value="${p.unitprice}"></div>
            <div class="col"><label>Quantity</label><input id="e_qty" type="number" class="form-control" value="${p.quantity}"></div>
        </div>
    `;
    
    const footer = `<button class="btn btn-primary" onclick="saveEdit('${id}')">Update</button>`;
    showModal(`Edit ${p.stockid}`, body, footer);
};

window.saveEdit = async function(id) {
    const payload = {
        stockid: id,
        name: document.getElementById('e_name').value,
        category: document.getElementById('e_cat').value,
        price: document.getElementById('e_price').value,
        stock: document.getElementById('e_qty').value
    };
    
    const res = await fetch('/api/products.php', { method: 'PUT', body: JSON.stringify(payload) });
    if (res.ok) {
        showAlert('Updated!', 'success');
        bootstrap.Modal.getInstance(document.getElementById('genericModal')).hide();
        loadProducts();
    }
};

window.openDelete = function(id) {
    const footer = `<button class="btn btn-danger" onclick="confirmDelete('${id}')">Yes, Delete</button>`;
    showModal('Confirm Delete', 'Are you sure?', footer);
};

window.confirmDelete = async function(id) {
    const res = await fetch(`/api/products.php?id=${id}`, { method: 'DELETE' });
    if (res.ok) {
        showAlert('Deleted', 'success');
        bootstrap.Modal.getInstance(document.getElementById('genericModal')).hide();
        loadProducts();
    } else {
        showAlert('Failed to delete. Item may be in sales records.', 'danger');
    }
};

function escapeHtml(text) {
  if (!text) return text;
  return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

loadProducts();
</script>