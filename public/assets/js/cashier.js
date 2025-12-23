let cart = [];
let allProducts = [];
let currentTotal = 0;
let selectedPaymentMethod = null;
let currentMember = null;

/* ========== DOM READY ========== */
document.addEventListener('DOMContentLoaded', () => {
    loadProducts();

    document.getElementById('searchInput')
        ?.addEventListener('input', filterProducts);

    document.getElementById('categoryFilter')
        ?.addEventListener('change', filterProducts);
});

/* ========== LOAD PRODUCTS ========== */
async function loadProducts() {
    try {
        const res = await fetch(BASE_URL + '/api/products.php');
        const data = await res.json();

        allProducts = data;
        renderProducts(allProducts);
        populateCategories(allProducts);
    } catch (e) {
        document.getElementById('productGrid').innerHTML =
            '<div class="text-danger text-center">Failed to load products</div>';
        console.error(e);
    }
}

/* ========== RENDER PRODUCTS ========== */
function renderProducts(list) {
    const grid = document.getElementById('productGrid');

    if (!list.length) {
        grid.innerHTML = '<div class="text-muted text-center">No products</div>';
        return;
    }

    grid.innerHTML = list.map(p => `
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card product-card p-2 text-center"
                 onclick="addToCart('${p.stockid}')">
                <strong>${p.name}</strong>
                <div class="text-primary">RM ${Number(p.unitprice).toFixed(2)}</div>
                <small>Stock: ${p.quantity}</small>
            </div>
        </div>
    `).join('');
}

/* ========== CATEGORY FILTER ========== */
function populateCategories(list) {
    const sel = document.getElementById('categoryFilter');
    if (!sel) return;

    const cats = [...new Set(list.map(p => p.category || 'General'))];
    cats.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c;
        opt.textContent = c;
        sel.appendChild(opt);
    });
}

function filterProducts() {
    const term = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const cat = document.getElementById('categoryFilter')?.value || 'All';

    renderProducts(allProducts.filter(p =>
        (p.name.toLowerCase().includes(term) ||
         p.stockid.toLowerCase().includes(term)) &&
        (cat === 'All' || p.category === cat)
    ));
}

/* ========== CART ========== */
function addToCart(stockId) {
    const item = cart.find(i => i.id === stockId);
    if (item) item.qty++;
    else cart.push({ id: stockId, qty: 1 });

    renderCart();
}

function renderCart() {
    const panel = document.getElementById('cartPanel');
    if (!panel) return;

    if (!cart.length) {
        panel.innerHTML = '<div class="text-muted text-center mt-5">Cart is empty</div>';
        updateTotals();
        return;
    }

    panel.innerHTML = cart.map(item => {
        const p = allProducts.find(x => x.stockid === item.id);
        return `
            <div class="cart-item d-flex justify-content-between align-items-center">
                <div>
                    <button class="btn btn-sm btn-outline-secondary"
                        onclick="updateQty('${item.id}', -1)">−</button>
                    ${p.name} x ${item.qty}
                    <button class="btn btn-sm btn-outline-secondary"
                        onclick="updateQty('${item.id}', 1)">+</button>
                </div>
                <span>RM ${(p.unitprice * item.qty).toFixed(2)}</span>
            </div>
        `;
    }).join('');

    updateTotals();
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

/* ========== TOTALS ========== */
function updateTotals() {
    let subtotal = 0;
    let discount = 0;
    let redeemedPoints = 0;
    let earnedPoints = 0;

    cart.forEach(item => {
        const p = allProducts.find(x => x.stockid === item.id);
        if (p) subtotal += p.unitprice * item.qty;
    });

    if (currentMember && currentMember.points > 0 && subtotal > 0) {
        // 最大可抵扣 RM
        const maxDiscount = currentMember.points / 100;

        // 实际抵扣 = 不能超过 subtotal
        discount = Math.min(maxDiscount, subtotal);

        // 实际使用的 points（必须是 100 的倍数）
        redeemedPoints = Math.floor(discount) * 100;

        // 折回真正 discount
        discount = redeemedPoints / 100;
    }

    const taxableAmount = Math.max(0, subtotal - discount);
    const tax = taxableAmount * 0.06;
    currentTotal = taxableAmount + tax;

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

    const pointsEl = document.getElementById('pointsValue');
    if (pointsEl) {
        pointsEl.innerText = '+' + earnedPoints;
    }
    window.redeemedPoints = redeemedPoints;
}

/* ========== CHECKOUT ========== */
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
          points_redeemed: window.redeemedPoints || 0,
          payment_method: method,
          amount_paid: currentTotal
        })
    });

    const data = await res.json();

    if (data.success) {
        alert('Success! Receipt: ' + data.receipt);
        deductStockInUI();
        renderProducts(allProducts);
        
        cart = [];
        renderCart();
        resetMember();
        loadProducts();
        resetPaymentUI();
    } else {
        alert(data.error);
    }
}

function findMember() {
    const input = document.getElementById("memberSearchInput");
    const result = document.getElementById("memberResult");

    if (!input || !result) return;

    const keyword = input.value.trim();

    if (keyword === "") {
        result.innerHTML = "Please enter member phone or ID";
        return;
    }

    result.innerHTML = "Searching...";

    fetch(`${BASE_URL}/api/find_member.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ keyword })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            currentMember = null;
            result.innerHTML = "Member not found";
            return;
        }

        currentMember = data.member;

        result.innerHTML = `
            <strong>${currentMember.name}</strong><br>
            Phone: ${currentMember.phone}<br>
            Points: ${currentMember.points}
        `;
        updateTotals();
    })
    .catch(err => {
        console.error(err);
        result.innerHTML = "Error searching member";
    });
}

function resetMember() {
    currentMember = null;
    window.redeemedPoints = 0;

    // 清空 Member UI
    const result = document.getElementById("memberResult");
    if (result) {
        result.innerHTML = "No member selected";
    }

    const input = document.getElementById("memberSearchInput");
    if (input) {
        input.value = "";
    }

    // 重算 totals（会把 discount / points 清掉）
    updateTotals();
}

let paymentModal = null;

document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('paymentModal');
    if (modalEl) {
        paymentModal = new bootstrap.Modal(modalEl);
    }
});

function openPaymentModal() {
    if (!cart.length) {
        alert('Cart is empty');
        return;
    }
     resetPaymentUI();
    paymentModal.show();
}

function choosePayment(method,btn) {
    selectedPaymentMethod = method;

    // UI 高亮选中的按钮
    document.querySelectorAll('#paymentButtons button')
        .forEach(btn => btn.classList.remove('active'));

    btn.classList.add('active');

    const text = document.getElementById('selectedPaymentText');
    if (text) {
        text.innerText = 'Selected: ' + method;
    }
}

function confirmPayment() {
    if (!selectedPaymentMethod) {
        alert('Please select a payment method');
        return;
    }

    paymentModal.hide();
    processPayment(selectedPaymentMethod);
}

function resetPaymentUI() {
    selectedPaymentMethod = null;

    // 清除按钮高亮
    document.querySelectorAll('#paymentButtons button')
        .forEach(b => b.classList.remove('active'));

    // 重置文字
    const text = document.getElementById('selectedPaymentText');
    if (text) {
        text.innerText = 'No payment selected';
    }
}

function deductStockInUI() {
    cart.forEach(item => {
        const p = allProducts.find(x => x.stockid === item.id);
        if (p) {
            p.quantity -= item.qty;
            if (p.quantity < 0) p.quantity = 0;
        }
    });
}
