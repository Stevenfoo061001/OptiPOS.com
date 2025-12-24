function login() {
  const username = document.getElementById("username").value;
  const password = document.getElementById("password").value;
  const errorEl = document.getElementById("error");

  errorEl.textContent = "";

  fetch(`${BASE_URL}/api/login.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ username, password })
  })
  .then(res => res.json())
  .then(data => {
  if (data.success) {
      window.location.replace(
    BASE_URL + "/index.php?page=home"
    );
  } else {
    errorEl.textContent = data.error || "Login failed";
  }
});

}

function logout() {
  fetch(`${BASE_URL}/api/logout.php`)
    .then(res => res.json())
    .then(data => {
      if (data.success) {
      window.location.href = BASE_URL + "/index.php?page=login";
      }
    });
}

document.addEventListener('DOMContentLoaded', () => {
  if (typeof NEED_ADMIN_SETUP !== 'undefined' && NEED_ADMIN_SETUP) {
    document.getElementById('adminSetupOverlay').style.display = 'flex';
  }
});

function createAdmin() {
  const name = document.getElementById('admin_name').value.trim();
  const phone = document.getElementById('admin_phone').value.trim();
  const email = document.getElementById('admin_email').value.trim();
  const password = document.getElementById('admin_password').value;
  const errorEl = document.getElementById('adminSetupError');

  errorEl.textContent = '';

  if (!name || !phone || !email || !password) {
    errorEl.textContent = 'Please fill in all fields';
    return;
  }

  fetch(`${BASE_URL}/api/create_admin.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ name, phone, email, password })
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        window.location.href = BASE_URL + '/index.php?page=home';
      } else {
        errorEl.textContent = data.error || 'Failed to create admin';
      }
    })
    .catch(() => {
      errorEl.textContent = 'Network error';
    });
}
