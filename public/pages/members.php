<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Member (Manage)</h3>
    <button class="btn btn-primary" id="btnAddMember">
        <i class="bi bi-person-plus"></i> Add New Member
    </button>
</div>

<div id="memberFormCard" class="card shadow-sm mb-4" style="display:none; border-left: 4px solid #0d6efd;">
    <div class="card-body">
        <h5 class="card-title mb-3">New Member Details</h5>
        <div class="row g-2">
            <div class="col-md-4">
                <label class="form-label small text-muted">Full Name</label>
                <input id="m_name" class="form-control" placeholder="e.g. John Doe">
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">Phone Number</label>
                <input id="m_phone" class="form-control" placeholder="e.g. 012-3456789">
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">Email Address</label>
                <input id="m_email" class="form-control" placeholder="e.g. john@example.com">
            </div>
        </div>
        <div class="mt-3 text-end">
            <button id="cancelMember" class="btn btn-light btn-sm me-2">Cancel</button>
            <button id="saveMember" class="btn btn-success btn-sm">Save Member</button>
        </div>
        <div id="memberMsg" class="mt-2"></div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="memberTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Points</th>
                        <th>Expiry</th>
                    </tr>
                </thead>
                <tbody id="memberTableBody">
                    <tr><td colspan="6" class="text-center p-3">Loading members...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// --- UI Toggles ---
const formCard = document.getElementById('memberFormCard');
document.getElementById('btnAddMember').addEventListener('click', () => {
    formCard.style.display = 'block';
    document.getElementById('m_name').focus();
});
document.getElementById('cancelMember').addEventListener('click', () => {
    formCard.style.display = 'none';
    document.getElementById('memberMsg').innerHTML = '';
});

// --- Load Members ---
async function loadMembers() {
    try {
        const res = await fetch('/api/members.php');
        const data = await res.json();
        
        const tbody = document.getElementById('memberTableBody');
        
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center p-4 text-muted">No members found.</td></tr>';
            return;
        }

        tbody.innerHTML = data.map(m => `
            <tr>
                <td class="ps-3 fw-bold text-primary">${m.memberid}</td>
                <td>${m.name}</td>
                <td>${m.phone || '-'}</td>
                <td>${m.email || '-'}</td>
                <td><span class="badge bg-info text-dark">${m.points} pts</span></td>
                <td class="small">${m.dateexpired}</td>
            </tr>
        `).join('');
    } catch (err) {
        console.error(err);
        document.getElementById('memberTableBody').innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading data.</td></tr>';
    }
}

// --- Save Member ---
document.getElementById('saveMember').addEventListener('click', async () => {
    const msgDiv = document.getElementById('memberMsg');
    const payload = { 
        name: document.getElementById('m_name').value.trim(), 
        phone: document.getElementById('m_phone').value.trim(), 
        email: document.getElementById('m_email').value.trim() 
    };

    if (!payload.name) {
        msgDiv.innerHTML = '<div class="text-danger small"><i class="bi bi-exclamation-circle"></i> Name is required</div>';
        return;
    }
    
    // Disable button to prevent double-click
    const btn = document.getElementById('saveMember');
    btn.disabled = true;
    btn.innerText = 'Saving...';

    try {
        const res = await fetch('/api/members.php', { 
            method:'POST', 
            headers:{'Content-Type':'application/json'}, 
            body: JSON.stringify(payload)
        });
        const j = await res.json();

        if (j.success) {
            showAlert('Member added successfully!', 'success'); // Uses the global showAlert from index.php
            formCard.style.display = 'none';
            // Clear inputs
            document.getElementById('m_name').value = '';
            document.getElementById('m_phone').value = '';
            document.getElementById('m_email').value = '';
            msgDiv.innerHTML = '';
            loadMembers();
        } else {
            msgDiv.innerHTML = `<div class="text-danger small">${j.error || 'Failed to save'}</div>`;
        }
    } catch (e) {
        msgDiv.innerHTML = '<div class="text-danger small">Network error</div>';
    } finally {
        btn.disabled = false;
        btn.innerText = 'Save Member';
    }
});

// Initial Load
loadMembers();
</script>