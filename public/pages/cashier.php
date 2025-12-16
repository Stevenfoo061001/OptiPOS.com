<h3>Cashier</h3>

<div class="row">
  <div class="col-md-8">
    <div id="productGrid" class="row g-2"></div>
  </div>

  <div class="col-md-4">
    <div class="card p-2">
      <h5>Cart</h5>
      <div id="cartList"></div>

      <div class="mt-2">
        <label>Discount</label>
        <input id="discount" class="form-control" value="0" type="number">
      </div>

      <button id="checkoutBtn" class="btn btn-success w-100 mt-2">Checkout</button>
      <div id="checkoutMsg" class="mt-2"></div>
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
