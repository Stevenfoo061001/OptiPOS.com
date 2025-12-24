<?php
require_once __DIR__ . '/../../config/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
    <title>POS System - Employee Profile</title>
    <script>
    const BASE_URL = "<?= BASE_URL ?>";
    </script>
    <script src="<?= BASE_URL ?>/assets/js/auth.js"></script>

    <style>
    body { background:#e5e5e5; font-family: Arial, sans-serif; }


    .profile-section {
      display:flex;
      gap:30px;
      align-items:center;
    }

    .avatar {
      width:150px;
      height:150px;
      border-radius:50%;
      background:#cfd8e3;
      display:flex;
      align-items:center;
      justify-content:center;
      font-size:48px;
      color:#fff;
    }

    .name-role h4 { margin:10px 0 0; }
    .name-role small { color:#555; }

    .card-box {
      background:#fff;
      border-radius:10px;
      padding:20px;
      box-shadow:0 2px 6px rgba(0,0,0,0.1);
    }

    .info-grid {
      display:grid;
      grid-template-columns:1fr 1fr;
      gap:15px;
      font-size:14px;
    }

    .label { color:#777; font-size:12px; }
  </style>
</head>
<body>
    <div class="app-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

<!-- MAIN -->
<main class="main-content">
  <div class="profile-layout">
    <!-- Left Profile -->
    <div class="profile-left">
      <div class="profile-section">
        <div class="avatar">👤</div>
        <div class="name-role">
            <h4 id="profileName">-</h4>
            <small id="profileRole">-</small>
        </div>
      </div>
    </div>

    <!-- Right Info -->
    <div class="profile-right">
      <div class="card-box mb-3">
        <h6>Employee Details</h6>
        <div class="info-grid mt-3">
          <div>
            <div class="label">Employee ID</div>
            <span id="profileId">-</span>
          </div>
      </div>
    </div>

      <div class="card-box">
  <h6>Contact</h6>

  <div class="info-grid mt-3">

    <div>
      <div class="label">Email</div>

      <span id="profileEmailText">-</span>
      <input type="email"
             id="profileEmailInput"
             class="profile-input"
             style="display:none;">
    </div>

    <div>
      <div class="label">Contact Number</div>

      <span id="profilePhoneText">-</span>
      <input type="text"
             id="profilePhoneInput"
             class="profile-input"
             style="display:none;">
    </div>

  </div>

  <!-- Buttons -->
  <!-- <div style="margin-top:20px;">
    <button id="editBtn" class="chart-btn" onclick="enterEdit()">Edit Profile</button>
    <button id="saveBtn" class="chart-btn" style="display:none;" onclick="saveProfile()">Save</button>
    <button id="cancelBtn" class="chart-btn" style="display:none;" onclick="cancelEdit()">Cancel</button>
  </div>
</div>

    </div>
  </div>
</main>
</div> -->

<script>
let originalProfile = {};

fetch("<?= BASE_URL ?>/api/profile.php")
  .then(res => {
    if (!res.ok) throw new Error("Unauthorized");
    return res.json();
  })
  .then(user => {
  originalProfile = user;

  document.getElementById("profileName").textContent  = user.name;
  document.getElementById("profileRole").textContent  = user.role_display;
  document.getElementById("profileId").textContent    = user.id;

  document.getElementById("profileEmailText").textContent = user.email;
  document.getElementById("profilePhoneText").textContent = user.phone ?? "-";

  document.getElementById("profileEmailInput").value = user.email;
  document.getElementById("profilePhoneInput").value = user.phone ?? "";

  document.querySelector(".avatar").textContent =
    user.name.charAt(0).toUpperCase();
})

  .catch(err => {
    console.error(err);
    alert("Please login again");
    window.location.href = "index.php?page=login";
  });

function enterEdit() {
  toggleEdit(true);
}

function cancelEdit() {
  document.getElementById("profileEmailInput").value = originalProfile.email;
  document.getElementById("profilePhoneInput").value = originalProfile.phone ?? "";
  toggleEdit(false);
}

function toggleEdit(editing) {
  document.getElementById("profileEmailText").style.display = editing ? "none" : "inline";
  document.getElementById("profilePhoneText").style.display = editing ? "none" : "inline";

  document.getElementById("profileEmailInput").style.display = editing ? "block" : "none";
  document.getElementById("profilePhoneInput").style.display = editing ? "block" : "none";

  document.getElementById("editBtn").style.display = editing ? "none" : "inline-block";
  document.getElementById("saveBtn").style.display = editing ? "inline-block" : "none";
  document.getElementById("cancelBtn").style.display = editing ? "inline-block" : "none";
}

function saveProfile() {
  const email = document.getElementById("profileEmailInput").value;
  const phone = document.getElementById("profilePhoneInput").value;

  fetch("<?= BASE_URL ?>/api/update_profile.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ email, phone })
  })
  .then(res => res.json())
  .then(data => {
    if (!data.success) throw new Error(data.error || "Update failed");

    // 更新画面
    document.getElementById("profileEmailText").textContent = email;
    document.getElementById("profilePhoneText").textContent = phone || "-";

    originalProfile.email = email;
    originalProfile.phone = phone;

    toggleEdit(false);
    alert("Profile updated successfully");
  })
  .catch(err => {
    alert(err.message);
  });
}

</script>


</body>
</html>
