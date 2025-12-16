<h3>Cashier</h3>

<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>POS Cashier</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#f4f4f4; }
    .sidebar {
      width:200px; background:#fff; height:100vh; border-right:1px solid #ddd;
      padding:20px; position:fixed;
    }
    .sidebar h4 { margin-bottom:20px; }
    .sidebar a {
      display:block; color:#666; margin:10px 0; text-decoration:none;
    }
    .sidebar a.active { color:#2ecc71; font-weight:600; }

    .main { margin-left:200px; padding:20px; }

    .panel { background:#e0e0e0; padding:20px; border-radius:4px; }
    .pay-btn {
      background:#fff; border:0; border-radius:6px;
      padding:20px; width:100%; box-shadow:0 2px 4px rgba(0,0,0,.15);
    }
    .order-box { background:#fff; padding:15px; height:100%; }

    .modal-backdrop { background:rgba(0,0,0,.3); }
  </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h4>POS System</h4>
  <a href="#">Home</a>
  <a href="#">Member</a>
  <a href="#" class="active">Cashier</a>
  <a href="#">Report</a>
  <a href="#">Stocks</a>
  <a href="#">Transaction</a>
  <a href="#">Add</a>
</div>

<!-- Main -->
<div class="main">
  <div class="d-flex justify-content-end mb-2">👤 Logout</div>

  <div class="row g-3">
    <!-- Left -->
    <div class="col-md-8">
      <div class="panel">
        <h5>Member Points</h5>
        <button class="pay-btn mb-3" data-bs-toggle="modal" data-bs-target="#pointsModal">Redeem Point</button>

        <h5>Payment Method</h5>
        <div class="row g-3">
          <div class="col-4"><button class="pay-btn">Visa</button></div>
          <div class="col-4"><button class="pay-btn">Credit Card</button></div>
          <div class="col-4"><button class="pay-btn">Debit Card</button></div>
          <div class="col-4"><button class="pay-btn">TnG</button></div>
          <div class="col-4"><button class="pay-btn">Alipay</button></div>
          <div class="col-4"><button class="pay-btn">Online Payment</button></div>
          <div class="col-4"><button class="pay-btn" data-bs-toggle="modal" data-bs-target="#cashModal">Cash</button></div>
        </div>
      </div>
    </div>

    <!-- Right Orders -->
    <div class="col-md-4">
      <div class="order-box">
        <h5>Orders</h5>
        <div id="cartList"></div>
        <hr>
        <div><small>Sub Total</small></div>
        <div><small>Rounding</small></div>
        <div><small>Tax</small></div>
        <strong id="totalText">Total</strong>
      </div>
    </div>
  </div>
</div>

<!-- Cash Modal -->
<div class="modal fade" id="cashModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-3">
      <p>Total:</p>
      <p>Amount:</p>
      <p>Change: Amount - Total</p>
      <div class="text-end">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary">Done</button>
      </div>
    </div>
  </div>
</div>

<!-- Points Modal -->
<div class="modal fade" id="pointsModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-3 text-center">
      <div class="mb-2">
        <button class="btn btn-light rounded-pill">All Points</button>
        <button class="btn btn-light rounded-pill">1000</button>
        <button class="btn btn-light rounded-pill">500</button>
      </div>
      <div>
        <button class="btn btn-light rounded-pill">300</button>
        <button class="btn btn-light rounded-pill">100</button>
      </div>
    </div>
  </div>
</div>

<script>
// Load demo products from API endpoint
async function loadProducts(){
  const res = await fetch('api/products.php');
  const products = await res.json();
  const grid = document.getElementById('productGrid');
  grid.innerHTML = '';
  products.forEach(p=>{
    const col = document.createElement('div');
    col.className='col-6 col-lg-3';
    col.innerHTML = `
      <div class="card p-2 h-100">
        <div><strong>${p.name}</strong></div>
        <div>Price: RM ${p.price}</div>
        <div class="small text-muted">Stock: ${p.stock}</div>
        <button class="btn btn-sm btn-primary mt-2">Add</button>
      </div>`;
    col.querySelector('button').onclick = ()=> addToCart(p);
    grid.appendChild(col);
  });
}

let cart = [];
function addToCart(p){
  const it = cart.find(i=>i.product_id===p.id);
  if(it) it.qty++;
  else cart.push({product_id:p.id, name:p.name, price:p.price, qty:1});
  renderCart();
}

function renderCart(){
  const el = document.getElementById('cartList'); el.innerHTML='';
  let total = 0;
  cart.forEach((c,idx)=>{
    total += c.qty * c.price;
    el.innerHTML += `<div class="d-flex justify-content-between"><div>${c.name} <small>x${c.qty}</small></div><div>RM ${(c.qty*c.price).toFixed(2)}</div></div>`;
  });
  el.innerHTML += `<hr><div><strong>Total: RM ${total.toFixed(2)}</strong></div>`;
}

document.getElementById('checkoutBtn').addEventListener('click', async ()=>{
  if(cart.length===0) return alert('Cart empty');
  const payload = { items: cart.map(c=>({product_id:c.product_id, price:c.price, qty:c.qty})), discount:parseFloat(document.getElementById('discount').value||0) };
  // call demo checkout
  const res = await fetch('api/checkout.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
  const j = await res.json();
  if(j.success){
    cart = []; renderCart();
    document.getElementById('checkoutMsg').innerHTML = `<div class="alert alert-success">Sale done. Receipt: ${j.receipt}</div>`;
    loadProducts(); // refresh demo stocks
  } else {
    document.getElementById('checkoutMsg').innerHTML = `<div class="alert alert-danger">${j.error}</div>`;
  }
});

loadProducts();
</script>
