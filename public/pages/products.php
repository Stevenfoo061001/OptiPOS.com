<?php
// public/pages/products.php
// products management UI (Add / Edit / Delete) - client side interacts with /api/products.php
?>
<div class="d-flex justify-content-between align-items-center mb-2">
  <h3 class="mb-0">Products (Manage)</h3>
  <div>
    <button class="btn btn-sm btn-primary" id="btnShowAdd" style="display:none">Add Product</button>
  </div>
</div>

<div id="addForm" class="card p-3 mb-3" style="display:none">
  <h6>Add Product</h6>
  <div class="mb-1"><input id="p_name" class="form-control" placeholder="Name"></div>
  <div class="mb-1"><input id="p_sku" class="form-control" placeholder="SKU"></div>
  <div class="mb-1"><input id="p_price" type="number" class="form-control" placeholder="Price"></div>
  <div class="mb-1"><input id="p_stock" type="number" class="form-control" placeholder="Stock"></div>
  <div class="mb-1"><input id="p_reorder" type="number" class="form-control" placeholder="Reorder level"></div>
  <div><button id="saveProduct" class="btn btn-success btn-sm">Save</button> <button id="cancelAdd" class="btn btn-sm btn-secondary">Cancel</button></div>
  <div id="addMsg" class="mt-2"></div>
</div>

<div id="productTable"></div>

<script>
let currentUser = null;
async function loadUser() {
  const r = await fetch('/api/usersession.php');
  currentUser = await r.json();
  if (currentUser && currentUser.role === 'admin') {
    document.getElementById('btnShowAdd').style.display = 'inline-block';
  }
}

// show add form
document.getElementById('btnShowAdd').addEventListener('click', ()=>{ document.getElementById('addForm').style.display='block'; });
document.getElementById('cancelAdd').addEventListener('click', ()=>{ document.getElementById('addForm').style.display='none'; });

async function loadProducts(){
  const res = await fetch('/api/products.php');
  const data = await res.json();
  // table with actions
  const head = `<table class="table"><thead><tr><th>Name</th><th>SKU</th><th class="text-end">Price</th><th class="text-end">Stock</th><th class="text-end">Reorder</th><th>Actions</th></tr></thead><tbody>`;
  const rows = data.map(p => {
    return `<tr data-id="${p.id}">
      <td>${escapeHtml(p.name)}</td>
      <td>${escapeHtml(p.sku)}</td>
      <td class="text-end">${Number(p.price).toFixed(2)}</td>
      <td class="text-end">${p.stock}</td>
      <td class="text-end">${p.reorder ?? ''}</td>
      <td>
        ${currentUser && currentUser.role === 'admin' ? `<button class="btn btn-sm btn-outline-primary me-1" onclick="openEdit(${p.id})">Edit</button><button class="btn btn-sm btn-outline-danger" onclick="openDelete(${p.id})">Delete</button>` : ''}
      </td>
    </tr>`;
  }).join('');
  document.getElementById('productTable').innerHTML = head + rows + '</tbody></table>';
}

// save new product (POST)
document.getElementById('saveProduct').addEventListener('click', async ()=>{
  const payload = {
    name: document.getElementById('p_name').value.trim(),
    sku: document.getElementById('p_sku').value.trim(),
    price: parseFloat(document.getElementById('p_price').value || 0),
    stock: parseInt(document.getElementById('p_stock').value || 0),
    reorder: parseInt(document.getElementById('p_reorder').value || 0)
  };
  if (!payload.name) { showAlert('Name required','warning'); return; }
  const res = await fetch('/api/products.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
  const j = await res.json();
  if (j.id) {
    showAlert('Product added','success');
    document.getElementById('addForm').style.display='none';
    // clear fields
    document.getElementById('p_name').value=''; document.getElementById('p_sku').value=''; document.getElementById('p_price').value=''; document.getElementById('p_stock').value=''; document.getElementById('p_reorder').value='';
    loadProducts();
  } else {
    showAlert(j.error || 'Error adding product','danger');
  }
});

// open edit modal using genericModal
window.openEdit = async function(id) {
  // fetch product list and find product
  const res = await fetch('/api/products.php'); const list = await res.json();
  const p = list.find(x=>x.id===id);
  if (!p) return showAlert('Product not found','danger');
  const body = `
    <div class="mb-2"><label class="form-label">Name</label><input id="edit_name" class="form-control" value="${escapeHtmlAttr(p.name)}"></div>
    <div class="mb-2"><label class="form-label">SKU</label><input id="edit_sku" class="form-control" value="${escapeHtmlAttr(p.sku)}"></div>
    <div class="mb-2"><label class="form-label">Price</label><input id="edit_price" type="number" class="form-control" value="${Number(p.price)}"></div>
    <div class="mb-2"><label class="form-label">Stock</label><input id="edit_stock" type="number" class="form-control" value="${p.stock}"></div>
    <div class="mb-2"><label class="form-label">Reorder</label><input id="edit_reorder" type="number" class="form-control" value="${p.reorder ?? 0}"></div>
  `;
  const footer = `<button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button class="btn btn-primary" id="saveEditBtn">Save</button>`;
  showModal('Edit Product', body, footer);

  // wait for modal to render and wire save click
  setTimeout(()=>{
    document.getElementById('saveEditBtn').addEventListener('click', async ()=>{
      const payload = {
        id: id,
        name: document.getElementById('edit_name').value.trim(),
        sku: document.getElementById('edit_sku').value.trim(),
        price: parseFloat(document.getElementById('edit_price').value||0),
        stock: parseInt(document.getElementById('edit_stock').value||0),
        reorder: parseInt(document.getElementById('edit_reorder').value||0)
      };
      if (!payload.name) { showAlert('Name required','warning'); return; }
      const r = await fetch('/api/products.php', { method:'PUT', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
      const j = await r.json();
      if (j.id) {
        showAlert('Product updated','success');
        // hide modal
        const m = bootstrap.Modal.getInstance(document.getElementById('genericModal'));
        m.hide();
        loadProducts();
      } else {
        showAlert(j.error || 'Update failed','danger');
      }
    });
  }, 100);
};

// open delete confirm modal
window.openDelete = function(id) {
  const body = `<p>Are you sure you want to delete this product (id: ${id})?</p>`;
  const footer = `<button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button class="btn btn-danger" id="confirmDeleteBtn">Delete</button>`;
  showModal('Confirm Delete', body, footer);
  setTimeout(()=>{
    document.getElementById('confirmDeleteBtn').addEventListener('click', async ()=>{
      const r = await fetch('/api/products.php?id=' + encodeURIComponent(id), { method:'DELETE' });
      const j = await r.json();
      if (j.success) {
        showAlert('Product deleted','success');
        const m = bootstrap.Modal.getInstance(document.getElementById('genericModal'));
        m.hide();
        loadProducts();
      } else {
        showAlert(j.error || 'Delete failed','danger');
      }
    });
  }, 100);
};

// helpers
function escapeHtml(s){ if (s===null||s===undefined) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function escapeHtmlAttr(s){ return escapeHtml(s).replace(/"/g, '&quot;'); }

// initial
(async ()=>{ await loadUser(); await loadProducts(); })();
</script>
