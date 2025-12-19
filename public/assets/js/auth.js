function login() {
  const username = document.getElementById("username").value;
  const password = document.getElementById("password").value;

  fetch("api/login.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ username, password })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      window.location.href = "index.php";
    } else {
      document.getElementById("error").innerText = "Invalid login";
    }
  });
}

function logout() {
  fetch("api/logout.php")
    .then(() => {
      window.location.href = "index.php?page=login";
    });
}
