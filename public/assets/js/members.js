document.addEventListener("DOMContentLoaded", () => {
  if (!document.getElementById("membersList")) return;

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

document.getElementById("membersList")
  .addEventListener("click", (e) => {
    const row = e.target.closest(".member-row");
    if (!row) return;

    openMemberModal("edit", {
      id: row.dataset.id.toUpperCase(),
      name: row.querySelector(".member-name").textContent,
      phone: row.querySelector(".member-phone").textContent,
      email: row.dataset.email || "",
      points: parseInt(row.dataset.points)
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

  const url =
  payload.mode === "add"
    ? "api/add_member.php"
    : "api/save_member.php";

    fetch(url, {
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
      updateMemberRow(data.member);
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
  row.dataset.email = (m.email || "").toLowerCase();
  row.dataset.points = m.points;

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
      email: m.email,
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
      row.dataset.email = (m.email || "").toLowerCase();
      row.dataset.points = m.points;

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

const saveBtn = document.querySelector(".btn-save");
const cancelBtn = document.querySelector(".btn-cancel");
const deleteBtn = document.querySelector(".btn-delete");

saveBtn && saveBtn.addEventListener("click", saveMember);
cancelBtn && cancelBtn.addEventListener("click", closeMemberModal);
deleteBtn && deleteBtn.addEventListener("click", deleteMember);

});