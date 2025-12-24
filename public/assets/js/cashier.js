/* ================= GLOBAL STATE ================= */
let cart = [];
let allProducts = [];
let currentTotal = 0;
let selectedPaymentMethod = null;
let currentMember = null;
let redeemedPoints = 0;
let cashReceivedValue = 0;
let changeValue = 0;
let useMemberPoints = false;


/* ================= DOM READY ================= */
document.addEventListener('DOMContentLoaded', () => {
  loadProducts();

  document.getElementById('searchInput')
    ?.addEventListener('input', filterProducts);

  document.getElementById('categoryFilter')
    ?.addEventListener('change', filterProducts);
});

/* ================= LOAD PRODUCTS ================= */
async function loadProducts() {
  try {
    const res = await fetch(BASE_URL + '/api/products.php');
    const data = await res.json();

    allProducts = data;
    renderProducts(allProducts);
    populateCategories(allProducts);
  } catch (e) {
    const grid = document.getElementById('productGrid');
    if (grid) {
      grid.innerHTML =
        '<div class="text-muted text-center">Failed to load products</div>';
    }
    console.error(e);
  }
}

/* ================= RENDER PRODUCTS ================= */
function renderProducts(list) {
  const grid = document.getElementById('productGrid');
  if (!grid) return;

  if (!list.length) {
    grid.innerHTML =
      '<div class="text-muted text-center">No products</div>';
    return;
  }

  grid.innerHTML = list.map(p => `
  <div class="product-card" onclick="addToCart('${p.stockid}')">
    <div class="product-content">
      <div class="product-top">
        <span class="product-name">${p.name}</span>
        <span class="product-price">RM ${Number(p.unitprice).toFixed(2)}</span>
      </div>
      <div class="product-stock">Stock: ${p.quantity}</div>
    </div>
  </div>
`).join('');


}

/* ================= CATEGORY FILTER ================= */
function populateCategories(list) {
  const sel = document.getElementById('categoryFilter');
  if (!sel) return;

  sel.innerHTML = '<option value="All">All Categories</option>';

  const cats = [...new Set(list.map(p => p.category || 'General'))];
  cats.forEach(c => {
    const opt = document.createElement('option');
    opt.value = c;
    opt.textContent = c;
    sel.appendChild(opt);
  });
}

function filterProducts() {
  const term =
    document.getElementById('searchInput')?.value.toLowerCase() || '';
  const cat =
    document.getElementById('categoryFilter')?.value || 'All';

  renderProducts(
    allProducts.filter(p =>
      (p.name.toLowerCase().includes(term) ||
       p.stockid.toLowerCase().includes(term)) &&
      (cat === 'All' || p.category === cat)
    )
  );
}

/* ================= CART ================= */
function addToCart(stockId) {
  const item = cart.find(i => i.id === stockId);
  if (item) {
    item.qty++;
  } else {
    cart.push({ id: stockId, qty: 1 });
  }
  renderCart();
}

function updateQty(id, change) {
  const item = cart.find(i => i.id === id);
  if (!item) return;

  item.qty += change;
  if (item.qty <= 0) {
    cart = cart.filter(i => i.id !== id);
  }
  renderCart();
}

function renderCart() {
  const panel = document.getElementById('cartPanel');
  if (!panel) return;

  if (!cart.length) {
    panel.innerHTML =
      '<div class="text-muted text-center mt-5">Cart is empty</div>';
    updateTotals();
    return;
  }

  panel.innerHTML = cart.map(item => {
    const p = allProducts.find(x => x.stockid === item.id);
    if (!p) return '';

    return `
      <div class="cart-item"
           style="display:flex;justify-content:space-between;align-items:center;">
        <div class="cart-left">
  <button class="qty-btn" onclick="updateQty('${item.id}', -1)">−</button>
  <span>${p.name} x ${item.qty}</span>
  <button class="qty-btn" onclick="updateQty('${item.id}', 1)">+</button>
</div>

        <span>RM ${(p.unitprice * item.qty).toFixed(2)}</span>
      </div>
    `;
  }).join('');

  updateTotals();
}

/* ================= TOTALS ================= */
function updateTotals() {
  let subtotal = 0;
  let discount = 0;
  let earnedPoints = 0;
  redeemedPoints = 0;

  cart.forEach(item => {
    const p = allProducts.find(x => x.stockid === item.id);
    if (p) subtotal += p.unitprice * item.qty;
  });

  if (
  currentMember &&
  useMemberPoints &&      // ⭐ 只有开关 ON
  currentMember.points > 0 &&
  subtotal > 0
) {
  const maxDiscount = currentMember.points / 100;
  discount = Math.min(maxDiscount, subtotal);
  redeemedPoints = Math.floor(discount) * 100;
  discount = redeemedPoints / 100;
} else {
  redeemedPoints = 0;     // ⭐ 关掉就不扣
}


  const taxable = Math.max(0, subtotal - discount);
  const tax = taxable * 0.06;
  currentTotal = taxable + tax;

  document.getElementById('subtotalValue').innerText =
    'RM ' + subtotal.toFixed(2);
  document.getElementById('discountValue').innerText =
    '-RM ' + discount.toFixed(2);
  document.getElementById('taxValue').innerText =
    'RM ' + tax.toFixed(2);
  document.getElementById('totalValue').innerText =
    'RM ' + currentTotal.toFixed(2);

  if (currentMember) {
    earnedPoints = Math.floor(currentTotal);
  }

  document.getElementById('pointsValue').innerText =
    '+' + earnedPoints;
}

/* ================= MEMBER ================= */
function findMember() {
  const input = document.getElementById('memberSearchInput');
  const result = document.getElementById('memberResult');
  if (!input || !result) return;

  const keyword = input.value.trim();
  if (!keyword) {
    result.innerText = 'Please enter member phone or ID';
    return;
  }

  result.innerText = 'Searching…';

  fetch(BASE_URL + '/api/find_member.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ keyword })
  })
    .then(res => res.json())
    .then(data => {
      if (!data.success) {
        currentMember = null;
        result.innerText = 'Member not found';
        updateTotals();
        return;
      }

      currentMember = data.member;
      useMemberPoints = false; // ⭐ 默认不要用积分

result.innerHTML = `
  <div class="member-card">
    <div class="member-name">${currentMember.name}</div>

    <div class="member-meta">
      <span>Phone: ${currentMember.phone}</span>
      <span>Points: ${currentMember.points}</span>
    </div>

    <div style="margin-top:8px;">
      <label style="display:flex;align-items:center;gap:8px;">
        <input type="checkbox"
               onchange="toggleUsePoints(this)">
        Use Member Points
      </label>
    </div>
  </div>
`;


      updateTotals();
    })
    .catch(err => {
      console.error(err);
      result.innerText = 'Error searching member';
    });
}

function resetMember() {
  currentMember = null;
  redeemedPoints = 0;
  useMemberPoints = false;

  const result = document.getElementById('memberResult');
  if (result) result.innerText = 'No member selected';

  const input = document.getElementById('memberSearchInput');
  if (input) input.value = '';

  updateTotals();
}

/* ================= PAYMENT MODAL ================= */
function openPaymentModal() {
  if (!cart.length) {
    alert('Cart is empty');
    return;
  }
  resetPaymentUI();
  document.getElementById('paymentModal').style.display = 'flex';
}

function closePaymentModal() {
  document.getElementById('paymentModal').style.display = 'none';
}

function choosePayment(method, btn) {
  selectedPaymentMethod = method;

  document.querySelectorAll('#paymentButtons button')
    .forEach(b => b.classList.remove('active'));

  btn.classList.add('active');

  const text = document.getElementById('selectedPaymentText');
  if (text) text.innerText = 'Selected: ' + method;
}

function resetPaymentUI() {
  selectedPaymentMethod = null;

  document.querySelectorAll('#paymentButtons button')
    .forEach(b => b.classList.remove('active'));

  const text = document.getElementById('selectedPaymentText');
  if (text) text.innerText = 'No payment selected';
}

function confirmPayment() {
  if (!selectedPaymentMethod) {
    alert('Please select a payment method');
    return;
  }

  if (selectedPaymentMethod === 'Cash') {
    openCashModal();   // 👈 以后扩展
    return;
  }


  closePaymentModal();
  processPayment(selectedPaymentMethod);
}

/* ================= CHECKOUT ================= */
async function processPayment(method) {
  if (!cart.length) {
  alert('Cart is empty');
  return;
}

  const res = await fetch(BASE_URL + '/api/checkout.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
  items: cart,
  member_id: currentMember ? currentMember.memberid : null,
  points_redeemed: redeemedPoints,
  payment_method: method,
  amount_paid: method === 'Cash' ? cashReceivedValue : currentTotal,
  change: method === 'Cash' ? changeValue : 0
})

  });

  const data = await res.json();

  if (data.success) {
    alert('Success! Receipt: ' + data.receipt);

    cart.forEach(item => {
      const p = allProducts.find(x => x.stockid === item.id);
      if (p) p.quantity = Math.max(0, p.quantity - item.qty);
    });

    cart = [];
    renderCart();
    resetMember();
    loadProducts();
  } else {
    alert(data.error || 'Checkout failed');
  }
}

function openCashModal() {
  closePaymentModal();

  cashReceivedValue = 0;
  changeValue = 0;

  document.getElementById('cashTotalText').innerText =
    'RM ' + currentTotal.toFixed(2);

  document.getElementById('cashReceivedInput').value = '';
  document.getElementById('cashChangeText').innerText = 'RM 0.00';
  document.getElementById('cashErrorText').innerText = '';
  document.getElementById('confirmCashBtn').disabled = true;

  document.getElementById('cashModal').style.display = 'flex';
}

function closeCashModal() {
  document.getElementById('cashModal').style.display = 'none';
}

function updateCashChange() {
  const input = document.getElementById('cashReceivedInput');
  const error = document.getElementById('cashErrorText');
  const btn = document.getElementById('confirmCashBtn');

  cashReceivedValue = parseFloat(input.value) || 0;
  changeValue = cashReceivedValue - currentTotal;

  document.getElementById('cashChangeText').innerText =
    'RM ' + Math.max(0, changeValue).toFixed(2);

  if (cashReceivedValue < currentTotal) {
    error.innerText = 'Cash received is not enough';
    btn.disabled = true;
  } else {
    error.innerText = '';
    btn.disabled = false;
  }
}

function confirmCashPayment() {
  closeCashModal();
  processPayment('Cash');
}

function toggleUsePoints(checkbox) {
  useMemberPoints = checkbox.checked;
  updateTotals();
}
