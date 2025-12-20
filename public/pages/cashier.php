<style>
    .product-card { cursor: pointer; transition: transform 0.1s; }
    .product-card:active { transform: scale(0.98); }
    .cart-item { border-bottom: 1px solid #eee; padding: 8px 0; font-size: 0.9rem; }
    .category-btn { min-width: 100px; }
</style>

<div class="row g-0 h-100" style="min-height: 85vh;">
    <div class="col-md-8 p-3 bg-light d-flex flex-column">
        
        <div class="d-flex gap-2 mb-3">
            <input type="text" id="searchProduct" class="form-control" placeholder="Search product name or ID...">
            <select id="categoryFilter" class="form-select w-25">
                <option value="All">All Categories</option>
                </select>
        </div>

        <div id="productGrid" class="row g-2 overflow-auto align-content-start" style="flex:1;">
            </div>
    </div>

    <div class="col-md-4 bg-white border-start d-flex flex-column shadow-sm">
        
        <div class="p-3 bg-primary text-white">
            <div class="input-group input-group-sm">
                <input type="text" id="memberSearchInput" class="form-control" placeholder="Member Phone / ID">
                <button class="btn btn-dark" onclick="findMember()">Find</button>
            </div>
            <div id="memberInfo" class="mt-2 small" style="display:none;">
                <strong><i class="bi bi-person-check"></i> <span id="memName"></span></strong>
                <div class="d-flex justify-content-between mt-1">
                    <span>Points: <span id="memPoints">0</span></span>
                    <button class="btn btn-xs btn-outline-light py-0" onclick="redeemPointsModal()">Redeem</button>
                </div>
            </div>
        </div>

        <div class="flex-grow-1 p-3 overflow-auto" id="cartContainer">
            <div class="text-center text-muted mt-5">
                <i class="bi bi-cart3 fs-1"></i><br>Cart is empty
            </div>
        </div>

            <div class="p-3 bg-light border-top">
            <div class="d-flex justify-content-between mb-1">
                <span>Subtotal</span>
                <span id="txtSubtotal">0.00</span>
            </div>
            <div class="d-flex justify-content-between mb-1 text-danger">
                <span>Discount (Points)</span>
                <span id="txtDiscount">-0.00</span>
            </div>
            <div class="d-flex justify-content-between mb-1 text-success fw-bold">
                <span>Points to Earn</span>
                <span id="txtPointsEarned">+0</span> </div>
            <div class="d-flex justify-content-between mb-1">
                <span>Tax (6%)</span>
                <span id="txtTax">0.00</span>
            </div>
            <div class="d-flex justify-content-between fw-bold fs-4 mb-3">
                <span>Total</span>
                <span id="txtTotal">0.00</span>
            </div>

            <div class="d-grid gap-2">
                <button class="btn btn-success btn-lg" onclick="openPaymentModal()">PAY NOW</button>
            </div>
        </div>
        </div>
    </div>
</div>

<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h3 class="text-center mb-4 text-primary" id="payModalTotal">RM 0.00</h3>
                
                <div class="mb-3">
                    <label class="form-label">Amount Received</label>
                    <input type="number" id="amountReceived" class="form-control form-control-lg text-center" oninput="calcChange()">
                </div>
                
                <div class="text-center mb-3 text-muted">
                    Change: <strong id="payChange" class="text-dark">RM 0.00</strong>
                </div>

                <div class="d-grid gap-2">
                    <button class="btn btn-outline-dark" onclick="processPayment('Cash')">CASH</button>
                    <div class="row g-2">
                        <div class="col"><button class="btn btn-outline-primary w-100" onclick="processPayment('Visa')">VISA</button></div>
                        <div class="col"><button class="btn btn-outline-primary w-100" onclick="processPayment('TNG')">TNG</button></div>
                        <div class="col"><button class="btn btn-outline-primary w-100" onclick="processPayment('QR')">QR</button></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// --- STATE ---
let allProducts = [];
let cart = [];
let currentMember = null;
let redeemedPoints = 0;

// --- INITIALIZE ---
async function init() {
    // Fetch Products
    const res = await fetch('/api/products.php');
    allProducts = await res.json();
    renderProducts(allProducts);

    // Populate Category Filter
    const cats = [...new Set(allProducts.map(p => p.category || 'General'))];
    const sel = document.getElementById('categoryFilter');
    cats.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c; opt.innerText = c;
        sel.appendChild(opt);
    });
}

// --- PRODUCT LIST ---
function renderProducts(list) {
    const grid = document.getElementById('productGrid');
    grid.innerHTML = list.map(p => `
        <div class="col-6 col-md-4 col-lg-3" onclick="addToCart('${p.stockid}')">
            <div class="card h-100 border-0 shadow-sm product-card">
                <div class="card-body p-2 text-center">
                    <div class="fw-bold text-truncate">${p.name}</div>
                    <div class="text-primary mt-1">RM ${Number(p.unitprice).toFixed(2)}</div>
                    <div class="small text-muted">Stk: ${p.quantity}</div>
                </div>
            </div>
        </div>
    `).join('');
}

// Search Filter
document.getElementById('searchProduct').addEventListener('input', (e) => {
    const term = e.target.value.toLowerCase();
    const cat = document.getElementById('categoryFilter').value;
    const filtered = allProducts.filter(p => 
        (p.name.toLowerCase().includes(term) || p.stockid.toLowerCase().includes(term)) &&
        (cat === 'All' || p.category === cat)
    );
    renderProducts(filtered);
});

document.getElementById('categoryFilter').addEventListener('change', () => document.getElementById('searchProduct').dispatchEvent(new Event('input')));


// --- CART LOGIC ---
function addToCart(id) {
    const p = allProducts.find(x => x.stockid === id);
    if (!p) return;

    if (p.quantity <= 0) {
        showAlert("Out of stock!", "danger");
        return;
    }

    const existing = cart.find(i => i.id === id);
    if (existing) {
        if (existing.qty < p.quantity) existing.qty++;
    } else {
        cart.push({ id: id, name: p.name, price: Number(p.unitprice), qty: 1, max: p.quantity });
    }
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cartContainer');
    
    if (cart.length === 0) {
        container.innerHTML = `<div class="text-center text-muted mt-5"><i class="bi bi-cart3 fs-1"></i><br>Cart is empty</div>`;
        updateTotals();
        return;
    }

    container.innerHTML = cart.map((item, idx) => `
        <div class="cart-item d-flex justify-content-between align-items-center">
            <div style="flex:1;">
                <div class="fw-bold">${item.name}</div>
                <div class="text-muted small">RM ${item.price.toFixed(2)}</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-light border" onclick="updateQty(${idx}, -1)">-</button>
                <span class="fw-bold" style="width:20px; text-align:center;">${item.qty}</span>
                <button class="btn btn-sm btn-light border" onclick="updateQty(${idx}, 1)">+</button>
            </div>
            <div class="text-end ms-2" style="width:60px;">
                RM ${(item.price * item.qty).toFixed(2)}
            </div>
        </div>
    `).join('');
    
    updateTotals();
}

function updateQty(idx, change) {
    const item = cart[idx];
    const newQty = item.qty + change;
    if (newQty <= 0) {
        cart.splice(idx, 1);
    } else if (newQty <= item.max) {
        item.qty = newQty;
    } else {
        showAlert("Max stock reached", "warning");
    }
    renderCart();
}

// --- MEMBER LOGIC ---
async function findMember() {
    const query = document.getElementById('memberSearchInput').value;
    if (!query) return;

    // Fetch all members (In real app, use a search API)
    const res = await fetch('/api/members.php');
    const members = await res.json();
    
    // Simple client-side find for this demo
    currentMember = members.find(m => m.phone === query || m.memberid === query);

    if (currentMember) {
        document.getElementById('memberInfo').style.display = 'block';
        document.getElementById('memName').innerText = currentMember.name;
        document.getElementById('memPoints').innerText = currentMember.points;
        redeemedPoints = 0; // Reset points on new member
        updateTotals();
    } else {
        showAlert("Member not found", "warning");
        currentMember = null;
        document.getElementById('memberInfo').style.display = 'none';
    }
}

function redeemPointsModal() {
    if (!currentMember || currentMember.points < 100) return showAlert("Need at least 100 points", "warning");
    
    const input = prompt(`Enter points to redeem (Max ${currentMember.points}). 100pts = RM1.00`);
    if (input) {
        const pts = parseInt(input);
        if (pts > currentMember.points) return showAlert("Not enough points", "danger");
        redeemedPoints = pts;
        updateTotals();
    }
}

// --- TOTALS CALCULATION ---
let finalAmount = 0;

function updateTotals() {
    const subtotal = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
    const discount = (redeemedPoints / 100); 
    const taxable = Math.max(0, subtotal - discount);
    const tax = taxable * 0.06;
    finalAmount = taxable + tax;
    
    // Update the "Points to Earn" display
    // Only calculate if a member is attached
    const pointsToEarn = currentMember ? Math.floor(finalAmount) : 0;

    document.getElementById('txtSubtotal').innerText = subtotal.toFixed(2);
    document.getElementById('txtDiscount').innerText = '-' + discount.toFixed(2);
    
    // Make sure this line exists to show the user the points they will get
    document.getElementById('txtPointsEarned').innerText = '+' + pointsToEarn; 
    
    document.getElementById('txtTax').innerText = tax.toFixed(2);
    document.getElementById('txtTotal').innerText = finalAmount.toFixed(2);
}


// --- CHECKOUT ---
function openPaymentModal() {
    if (cart.length === 0) return showAlert("Cart is empty", "danger");
    const m = new bootstrap.Modal(document.getElementById('payModal'));
    document.getElementById('payModalTotal').innerText = "RM " + finalAmount.toFixed(2);
    document.getElementById('amountReceived').value = '';
    document.getElementById('payChange').innerText = 'RM 0.00';
    m.show();
}

function calcChange() {
    const paid = parseFloat(document.getElementById('amountReceived').value) || 0;
    const change = paid - finalAmount;
    document.getElementById('payChange').innerText = change >= 0 ? "RM " + change.toFixed(2) : "Insufficient";
}

async function processPayment(method) {
    const paid = parseFloat(document.getElementById('amountReceived').value) || 0;
    
    if (method === 'Cash' && paid < finalAmount) {
        alert("Insufficient cash!");
        return;
    }

    const payload = {
        items: cart,
        member_id: currentMember ? currentMember.memberid : null,
        points_redeemed: redeemedPoints,
        payment_method: method,
        amount_paid: paid
    };

    const res = await fetch('/api/checkout.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    });
    const j = await res.json();
    
    if (j.success) {
        bootstrap.Modal.getInstance(document.getElementById('payModal')).hide();
        
        let msg = `Transaction Successful!\nReceipt: ${j.receipt}`;
        if (j.points_earned > 0) {
            msg += `\nPoints Earned: ${j.points_earned}`;
        }
        
        alert(msg); // Simple alert, or use showAlert(msg, "success")

        // Reset
        cart = [];
        currentMember = null;
        redeemedPoints = 0;
        renderCart();
        document.getElementById('memberInfo').style.display = 'none';
        document.getElementById('memberSearchInput').value = '';
        init(); 
    }
}

init();
</script>