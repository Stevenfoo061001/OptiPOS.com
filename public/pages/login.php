<?php
// Start session
if (session_status() == PHP_SESSION_NONE) session_start();

// If user already logged in
if (!empty($_SESSION['user'])) {
    echo '<div class="alert alert-success">You are already signed in as ' . htmlspecialchars($_SESSION['user']['name']) . '.</div>';
    echo '<p><a href="?page=home" class="btn btn-sm btn-primary">Go to Home</a></p>';
    return;
}

// Handle POST login
$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {

        // -----------------------
        // Check ADMIN table
        // -----------------------
        $stmt = $pdo->prepare("
            SELECT adminid AS id, name, loginpassword
            FROM admin
            WHERE name = :username
            LIMIT 1
        ");
        $stmt->execute([':username' => $username]);
        $admin = $stmt->fetch();

        if ($admin && $password === $admin['loginpassword']) {
            $_SESSION['user'] = [
                'id' => $admin['id'],
                'name' => $admin['name'],
                'role' => 'admin'
            ];
            header("Location: ?page=home");
            exit;
        }

        // -----------------------
        // Check CASHIER table
        // -----------------------
        $stmt = $pdo->prepare("
            SELECT cashierid AS id, name, loginpassword
            FROM cashier
            WHERE name = :username
            LIMIT 1
        ");
        $stmt->execute([':username' => $username]);
        $cashier = $stmt->fetch();

        if ($cashier && $password === $cashier['loginpassword']) {
            $_SESSION['user'] = [
                'id' => $cashier['id'],
                'name' => $cashier['name'],
                'role' => 'cashier'
            ];
            header("Location: ?page=cashier");
            exit;
        }

        $loginError = "Invalid username or password.";

    } else {
        $loginError = "Please enter username and password.";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card p-3">
            <h5>Sign in</h5>
            <?php if ($loginError): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($loginError) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-2">
                    <input name="username" class="form-control" placeholder="Username" required>
                </div>
                <div class="mb-2">
                    <input name="password" type="password" class="form-control" placeholder="Password" required>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Sign in</button>
                </div>
            </form>
        </div>
    </div>
</div>