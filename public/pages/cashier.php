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

<!-- Main -->
<div class="main">
  <div class="d-flex justify-content-end mb-2">
  <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
</div>


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
        <div id="usedPointsRow" class="text-danger d-none">
          Used Points: <span id="usedPointsText"></span>
        </div>
        <strong id="totalText">Total</strong>
      </div>
    </div>
  </div>
</div>

<!-- Cash Modal -->
<div class="modal fade" id="cashModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content p-3">
      
      <div class="mb-2">
        <label>Total</label>
        <input id="cashTotal" class="form-control" readonly>
      </div>

      <div class="mb-2">
        <label>Amount</label>
        <input id="cashAmount" class="form-control" type="number">
      </div>

      <div class="mb-2">
        <label>Change</label>
        <input id="cashChange" class="form-control" readonly>
      </div>

      <div class="text-end">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary btn-sm" onclick="completeCash()">Done</button>
      </div>

    </div>
  </div>
</div>


<!-- Points Modal -->
<div class="modal fade" id="pointsModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content p-3 text-center">

      <h6 class="mb-2">Redeem Points</h6>

      <div class="mb-2">
        <button class="btn btn-light rounded-pill m-1" onclick="selectPoints('all')">All Points</button>
        <button class="btn btn-light rounded-pill m-1" onclick="selectPoints(1000)">1000</button>
        <button class="btn btn-light rounded-pill m-1" onclick="selectPoints(500)">500</button>
      </div>

      <div class="mb-2">
        <button class="btn btn-light rounded-pill m-1" onclick="selectPoints(300)">300</button>
        <button class="btn btn-light rounded-pill m-1" onclick="selectPoints(100)">100</button>
      </div>

      <div class="mt-2">
        <small id="selectedPointsText">Selected: 0 points</small>
      </div>

      <div class="text-end mt-3">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary btn-sm" onclick="applyPoints()">Done</button>
      </div>

    </div>
  </div>
</div>


<script>
// Load demo products from API endpoint

// ================= PRODUCTS =================
async function loadProducts(){
  const res = await fetch('api/products.php');
  const products = await res.json();
  const grid = document.getElementById('productGrid');
  if(!grid) return;

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

// ================= CART =================
let cart = [];
let cashTotal = 0;
let usedPoints = 0;
let selectedPoints = 0;

function addToCart(p){
  const it = cart.find(i=>i.product_id===p.id);
  if(it) it.qty++;
  else cart.push({product_id:p.id, name:p.name, price:p.price, qty:1});
  renderCart();
}

function renderCart(){
  const el = document.getElementById('cartList');
  el.innerHTML='';
  let total = 0;

  cart.forEach(c=>{
    total += c.qty * c.price;
    el.innerHTML += `
      <div class="d-flex justify-content-between">
        <div>${c.name} <small>x${c.qty}</small></div>
        <div>RM ${(c.qty*c.price).toFixed(2)}</div>
      </div>`;
  });

  const pointsDiscount = usedPoints * 0.01;
  const finalTotal = Math.max(0, total - pointsDiscount);

  el.innerHTML += `<hr><div><strong>Total: RM ${finalTotal.toFixed(2)}</strong></div>`;

  document.getElementById('totalText').innerText =
    'Total: RM ' + finalTotal.toFixed(2);

  // update cash modal total
  cashTotal = finalTotal;
  const cashInput = document.getElementById('cashTotal');
  if(cashInput){
    cashInput.value = 'RM ' + finalTotal.toFixed(2);
  }
}

// ================= CASH =================
const cashAmountInput = document.getElementById('cashAmount');
const cashChangeInput = document.getElementById('cashChange');

if(cashAmountInput){
  cashAmountInput.addEventListener('input', ()=>{
    const amount = parseFloat(cashAmountInput.value) || 0;
    const change = amount - cashTotal;
    cashChangeInput.value =
      change >= 0 ? 'RM ' + change.toFixed(2) : 'RM 0.00';
  });
}

function completeCash(){
  const amount = parseFloat(cashAmountInput.value) || 0;
  if(amount < cashTotal){
    alert('Amount not enough');
    return;
  }

  alert('Cash payment completed');
  bootstrap.Modal.getInstance(document.getElementById('cashModal')).hide();
}

// ================= POINTS =================
function selectPoints(points){
  selectedPoints = points === 'all' ? 1000 : points;
  document.getElementById('selectedPointsText').innerText =
    `Selected: ${selectedPoints} points`;
}

function applyPoints(){
  usedPoints = selectedPoints;
  selectedPoints = 0;

  document.getElementById('usedPointsRow').classList.remove('d-none');
  document.getElementById('usedPointsText').innerText =
    `${usedPoints} (-RM ${(usedPoints * 0.01).toFixed(2)})`;

  bootstrap.Modal.getInstance(document.getElementById('pointsModal')).hide();
  renderCart();
}

loadProducts();
</script>
