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
