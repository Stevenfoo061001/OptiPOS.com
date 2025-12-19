<?php
$membersFile = __DIR__ . '/../data/members.json';

$members = [];
if (file_exists($membersFile)) {
  $members = json_decode(file_get_contents($membersFile), true);
  if (!is_array($members)) $members = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Members</title>
  <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>

<div class="app-layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-header">
      <h2>POS System</h2>
    </div>

    <nav class="sidebar-menu">
      <a href="index.php?page=home">Home</a>
      <a href="index.php?page=cashier">Cashier</a>
      <a href="index.php?page=products">Products</a>
      <a href="index.php?page=members" class="active">Members</a>
      <a href="index.php?page=transactions">Transactions</a>
      <a href="index.php?page=reports">Reports</a>
      <a href="index.php?page=profile">Profile</a>
    </nav>

    <div class="sidebar-footer">
      <button class="logout-btn" onclick="logout()">Logout</button>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="main-content">

    <!-- HEADER -->
    <div class="members-header">
      <h1>Members</h1>

      <div class="members-actions">
        <input
          type="text"
          id="memberSearchInput"
          placeholder="Search by ID, name or phone"
        >
        <button class="add-member-btn" type="button">+ Add Member</button>
      </div>
    </div>

    <!-- MEMBER LIST -->
    <div class="members-list" id="membersList">
      <?php if (empty($members)): ?>
        <div class="empty">No members found</div>
      <?php else: ?>
        <?php foreach ($members as $m): ?>
          <div class="member-row"
            data-id="<?= strtolower($m['id']) ?>"
            data-name="<?= strtolower($m['name']) ?>"
            data-phone="<?= strtolower($m['phone'] ?? '') ?>"
          >
            <div class="member-id"><?= htmlspecialchars($m['id']) ?></div>

            <div class="member-info">
              <div class="member-name"><?= htmlspecialchars($m['name']) ?></div>
              <div class="member-phone"><?= htmlspecialchars($m['phone'] ?? '-') ?></div>
            </div>

            <div class="member-points">
              <?= intval($m['points']) ?> pts
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </main>
</div>

<!-- MEMBER MODAL -->
<div class="modal-overlay" id="memberModal" style="display:none;">
  <div class="modal-card">
    <h2 id="modalTitle">Add Member</h2>

    <input type="hidden" id="memberMode">
    <input type="hidden" id="memberId">

    <div class="modal-form">
      <label>Name</label>
      <input type="text" id="memberName">

      <label>Phone</label>
      <input type="text" id="memberPhone">

      <label>Email</label>
      <input type="email" id="memberEmail">

      <label>Points</label>
      <input type="number" id="memberPoints" value="0">
    </div>

    <div class="modal-actions">
  <button type="button" class="btn-delete" onclick="deleteMember()">Delete</button>

  <div style="flex:1"></div>

  <button type="button" class="btn-cancel" onclick="closeMemberModal()">Cancel</button>
  <button type="button" class="btn-save" onclick="saveMember()">Save</button>
</div>

  </div>
</div>

<script src="../assets/js/auth.js"></script>

<script>
/* ---------- SEARCH ---------- */
document.getElementById("memberSearchInput")
  .addEventListener("input", function () {
    const keyword = this.value.toLowerCase();
    document.querySelectorAll(".member-row").forEach(row => {
      const match =
        row.dataset.id.includes(keyword) ||
        row.dataset.name.includes(keyword) ||
        row.dataset.phone.includes(keyword);
      row.style.display = match ? "flex" : "none";
    });
});

/* ---------- OPEN MODAL ---------- */
document.querySelector(".add-member-btn").addEventListener("click", () => {
  openMemberModal("add");
});

document.querySelectorAll(".member-row").forEach(row => {
  row.addEventListener("click", () => {
    openMemberModal("edit", {
      id: row.dataset.id.toUpperCase(),
      name: row.querySelector(".member-name").textContent,
      phone: row.querySelector(".member-phone").textContent,
      points: parseInt(row.querySelector(".member-points").textContent)
    });
  });
});

function openMemberModal(mode, data = {}) {
    const deleteBtn = document.querySelector(".btn-delete");

if (mode === "add") {
  deleteBtn.style.display = "none";
} else {
  deleteBtn.style.display = "inline-block";
}

  document.getElementById("memberModal").style.display = "flex";
  document.getElementById("memberMode").value = mode;

  document.getElementById("modalTitle").textContent =
    mode === "add" ? "Add Member" : "Edit Member";

  document.getElementById("memberId").value = data.id || "";
  document.getElementById("memberName").value = data.name || "";
  document.getElementById("memberPhone").value = data.phone || "";
  document.getElementById("memberEmail").value = data.email || "";
  document.getElementById("memberPoints").value = data.points || 0;
}

function closeMemberModal() {
  document.getElementById("memberModal").style.display = "none";
}

/* ---------- SAVE MEMBER ---------- */
function saveMember() {
  const payload = {
    mode: document.getElementById("memberMode").value,
    id: document.getElementById("memberId").value,
    name: document.getElementById("memberName").value,
    phone: document.getElementById("memberPhone").value,
    email: document.getElementById("memberEmail").value,
    points: document.getElementById("memberPoints").value
  };

  fetch("api/save_member.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload)
  })
  .then(res => res.json())
  .then(data => {
    if (!data.success) {
      alert(data.error || "Save failed");
      return;
    }

    if (payload.mode === "add") {
      appendMemberRow(data.member);
    } else {
      updateMemberRow(payload);
    }

    closeMemberModal();
  });
}

/* ---------- UI UPDATE ---------- */
function appendMemberRow(m) {
  const list = document.getElementById("membersList");

  const row = document.createElement("div");
  row.className = "member-row";
  row.dataset.id = m.id.toLowerCase();
  row.dataset.name = m.name.toLowerCase();
  row.dataset.phone = (m.phone || "").toLowerCase();

  row.innerHTML = `
    <div class="member-id">${m.id}</div>
    <div class="member-info">
      <div class="member-name">${m.name}</div>
      <div class="member-phone">${m.phone || "-"}</div>
    </div>
    <div class="member-points">${m.points} pts</div>
  `;

  row.addEventListener("click", () => {
    openMemberModal("edit", {
      id: m.id,
      name: m.name,
      phone: m.phone,
      points: m.points
    });
  });

  list.appendChild(row);
}

function updateMemberRow(m) {
  document.querySelectorAll(".member-row").forEach(row => {
    if (row.dataset.id === m.id.toLowerCase()) {
      row.querySelector(".member-name").textContent = m.name;
      row.querySelector(".member-phone").textContent = m.phone || "-";
      row.querySelector(".member-points").textContent = `${m.points} pts`;

      row.dataset.name = m.name.toLowerCase();
      row.dataset.phone = (m.phone || "").toLowerCase();
    }
  });
}

function deleteMember() {
  const id = document.getElementById("memberId").value;

  if (!id) {
    alert("Cannot delete unsaved member");
    return;
  }

  if (!confirm("Are you sure you want to delete this member?")) {
    return;
  }

  fetch("api/delete_member.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ id })
  })
    .then(res => res.json())
    .then(data => {
      if (!data.success) {
        alert(data.error || "Delete failed");
        return;
      }

      removeMemberRow(id);
      closeMemberModal();
    });
}

function removeMemberRow(id) {
  document.querySelectorAll(".member-row").forEach(row => {
    if (row.dataset.id === id.toLowerCase()) {
      row.remove();
    }
  });
}

</script>

</body>
</html>
