<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Transaction History</h3>
    <div>
        <button class="btn btn-sm btn-outline-secondary" onclick="loadTx()">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Trans ID</th>
                    <th>Date</th>
                    <th>Cashier</th>
                    <th>Method</th>
                    <th class="text-end">Total (RM)</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody id="txTableBody">
                <tr><td colspan="6" class="text-center p-3">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
async function loadTx() {
    try {
        const res = await fetch('/api/transactions.php');
        const data = await res.json();
        const tbody = document.getElementById('txTableBody');

        if (!data || data.length === 0) { 
            tbody.innerHTML = '<tr><td colspan="6" class="text-center p-4 text-muted">No transactions found.</td></tr>'; 
            return; 
        }

        tbody.innerHTML = data.map(t => `
            <tr>
                <td class="ps-3 text-primary small">${t.transactionid}</td>
                <td>${t.paymentdate}</td>
                <td>${t.cashier_name || 'Admin'}</td>
                <td><span class="badge bg-light text-dark border">${t.paymentmethod}</span></td>
                <td class="text-end fw-bold">${Number(t.total_order_value).toFixed(2)}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-info" onclick="viewReceipt('${t.orderid}')">
                        View Items
                    </button>
                </td>
            </tr>
        `).join('');
    } catch (err) {
        document.getElementById('txTableBody').innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading data</td></tr>';
    }
}

// Function to view specific items in an order
window.viewReceipt = async function(orderId) {
    // We can use a quick fetch to a new endpoint or just show a placeholder for now
    // Ideally, you would create api/order_details.php?id=...
    showModal('Order Details', `Loading items for Order #${orderId}...`);
    
    // FETCH ITEMS (Optional: requires a new API endpoint)
    // For now, let's just show the ID
    const body = `
        <div class="text-center my-3">
            <p>Receipt detail viewing requires a dedicated API endpoint.</p>
            <strong>Order ID: ${orderId}</strong>
        </div>
    `;
    showModal(`Receipt for Order #${orderId}`, body, '<button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>');
};

loadTx();
</script>