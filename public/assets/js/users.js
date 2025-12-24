// ===== 1. 先把所有 DOM 元素抓出来 =====
const userModal = document.getElementById('userModal');
const userModalTitle = document.getElementById('userModalTitle');
const userMode = document.getElementById('userMode');
const userId = document.getElementById('userId');
const userName = document.getElementById('userName');
const userPhone = document.getElementById('userPhone');
const userEmail = document.getElementById('userEmail');
const userRole = document.getElementById('userRole');
const deleteUserBtn = document.getElementById('deleteUserBtn');
const userPassword = document.getElementById('userPassword');
const passwordGroup = document.getElementById('passwordGroup');
const userSearchInput = document.getElementById('userSearchInput');
const roleFilter = document.getElementById('roleFilter');

// ===== 2. Add User =====
function openAddUser() {
  userModal.style.display = 'flex';
  userModalTitle.innerText = 'Add User';

  userMode.value = 'add';
  userId.value = '';
  userName.value = '';
  userPhone.value = '';
  userEmail.value = '';
  userRole.value = 'cashier';
  userPassword.value = '';

  passwordGroup.style.display = 'block';   // ✅ 显示
  deleteUserBtn.style.display = 'none';
}


// ===== 3. Edit User =====
function openEditUser(user) {
  userModal.style.display = 'flex';
  userModalTitle.innerText = 'Edit User';

  userMode.value = 'edit';
  userId.value = user.userid;
  userName.value = user.name;
  userPhone.value = user.phone ?? '';
  userEmail.value = user.email;
  userRole.value = user.role;

  passwordGroup.style.display = 'none';    // ❌ 隐藏
  deleteUserBtn.style.display = 'inline-block';
}


// ===== 4. Close Modal =====
function closeUserModal() {
  userModal.style.display = 'none';
}

// ===== 5. Save User =====
let saving = false;

function saveUser() {
  if (saving) return;
  saving = true;

  fetch(BASE_URL + '/api/save_user.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      mode: userMode.value,
      userid: userId.value,
      name: userName.value,
      phone: userPhone.value,
      email: userEmail.value,
      password: userPassword?.value,
      role: userRole.value
    })
  })
  .then(res => res.json())
  .then(data => {
    saving = false;
    if (data.success) {
      location.reload();
    } else {
      alert(data.error);
    }
  })
  .catch(() => {
    saving = false;
    alert('Network error');
  });
}



// ===== 6. Delete User =====
function confirmDeleteUser() {
  if (!confirm(`Delete user "${userName.value}"?`)) return;

  fetch(BASE_URL + '/api/delete_user.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ userid: userId.value })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      location.reload();
    } else {
      alert(data.error || 'Delete failed');
    }
  });
}

function filterUsers() {
  const keyword = userSearchInput.value.toLowerCase().trim();
  const selectedRole = roleFilter.value; // '' | admin | cashier
  const rows = document.querySelectorAll('.member-row');

  rows.forEach(row => {
    const { id, name, email, phone, role } = row.dataset;

    const matchKeyword =
      id.includes(keyword) ||
      name.includes(keyword) ||
      email.includes(keyword) ||
      phone.includes(keyword) ||
      role.includes(keyword);

    const matchRole =
      !selectedRole || role === selectedRole;

    row.style.display =
      matchKeyword && matchRole ? '' : 'none';
  });
}
userSearchInput?.addEventListener('input', filterUsers);
roleFilter?.addEventListener('change', filterUsers);

