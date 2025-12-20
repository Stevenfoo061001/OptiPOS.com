<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card shadow-sm mt-4">
      <div class="card-body text-center">

        <h4 id="pf_name" class="mb-1">Loading...</h4>
        <div class="badge bg-primary mb-3" id="pf_role">...</div>

        <hr>

        <div class="text-start px-3">
            <div class="mb-3">
                <label class="small text-muted fw-bold">USER ID</label>
                <div id="pf_id" class="fs-5">—</div>
            </div>
            
            <div class="mb-3">
                <label class="small text-muted fw-bold">EMAIL ADDRESS</label>
                <div id="pf_email" class="fs-5">—</div>
            </div>

            <div class="mb-3">
                <label class="small text-muted fw-bold">PHONE NUMBER</label>
                <div id="pf_phone" class="fs-5">—</div>
            </div>
        </div>

        <hr>

        <button id="logoutBtnProfile" class="btn btn-outline-danger w-100">
            <i class="bi bi-box-arrow-right"></i> Sign Out
        </button>

      </div>
    </div>
  </div>
</div>

<script>
async function loadProfile() {
    try {
        const res = await fetch('/api/profile.php');
        
        if (!res.ok) {
            if (res.status === 401) window.location.href = '?page=login';
            return;
        }

        const data = await res.json();
        
        // Populate fields
        document.getElementById('pf_name').innerText = data.name;
        document.getElementById('pf_role').innerText = data.role_display;
        document.getElementById('pf_id').innerText = data.id;
        document.getElementById('pf_email').innerText = data.email;
        document.getElementById('pf_phone').innerText = data.phone;

        // Color badge based on role
        if (data.role_display === 'Admin') {
            document.getElementById('pf_role').className = 'badge bg-danger mb-3';
        } else {
            document.getElementById('pf_role').className = 'badge bg-success mb-3';
        }

    } catch (error) {
        console.error("Error loading profile:", error);
    }
}

// Logout logic duplicated here for the button inside the card
document.getElementById('logoutBtnProfile').addEventListener('click', async () => {
    const r = await fetch('/api/logout.php');
    const j = await r.json();
    if (j.success) location.href = '?page=home';
});

loadProfile();
</script>