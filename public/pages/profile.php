<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>POS System - Employee Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#e5e5e5; font-family: Arial, sans-serif; }

    /* Sidebar */
    .sidebar {
      width:220px;
      height:100vh;
      background:#fff;
      position:fixed;
      left:0; top:0;
      border-right:1px solid #ddd;
      padding:20px;
    }

    .sidebar h5 {
      font-weight:600;
      margin-bottom:30px;
    }

    .sidebar a {
      display:block;
      padding:10px 0;
      color:#666;
      text-decoration:none;
      font-size:14px;
    }

    .sidebar a.active {
      color:#000;
      font-weight:600;
    }

    /* Main */
    .main {
      margin-left:220px;
      padding:25px;
    }

    .top-bar {
      display:flex;
      justify-content:space-between;
      align-items:center;
      margin-bottom:20px;
    }

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

<!-- Main -->
<div class="main">

  <div class="top-bar">
    <div></div>
    <div class="d-flex align-items-center gap-2">
      <div class="avatar" style="width:35px;height:35px;font-size:16px;">👤</div>
      <a href="#" class="text-decoration-none">Logout</a>
    </div>
  </div>

  <div class="row">
    <!-- Left Profile -->
    <div class="col-md-5">
      <div class="profile-section">
        <div class="avatar">👤</div>
        <div class="name-role">
          <h4>Name</h4>
          <small>Store Manager</small>
        </div>
      </div>
    </div>

    <!-- Right Info -->
    <div class="col-md-7">
      <div class="card-box mb-3">
        <h6>Employee Details</h6>
        <div class="info-grid mt-3">
          <div>
            <div class="label">Employee ID</div>
            XXXX-XX-XXX
          </div>
          <div>
            <div class="label">Employment Type</div>
            Full / Part Time
          </div>
          <div>
            <div class="label">Join Date</div>
            DD / MM / YYYY
          </div>
        </div>
      </div>

      <div class="card-box">
        <h6>Contact</h6>
        <div class="info-grid mt-3">
          <div>
            <div class="label">Email</div>
            xxx@gmail.com
          </div>
          <div>
            <div class="label">Emergency Contact</div>
            Mom/Dad 012-345 6789
          </div>
          <div>
            <div class="label">Contact Number</div>
            012-345 6789
          </div>
          <div>
            <div class="label">Location</div>
            XXX
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

</body>
</html>