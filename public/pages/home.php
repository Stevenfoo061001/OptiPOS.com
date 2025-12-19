<?php
// IMPORTANT: session_start() MUST already be in index.php
// Do NOT put session_start() here

/* ---------- CART INITIALISATION ---------- */
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/* ---------- LOAD PRODUCTS FROM JSON ---------- */
$productsFile = __DIR__ . '/../data/products.json';

if (!file_exists($productsFile)) {
    die("products.json NOT FOUND");
}

$products = json_decode(file_get_contents($productsFile), true);

if (!$products) {
    die("products.json EMPTY or INVALID");
}

/* ---------- ADD TO CART (POST) ---------- */
if (isset($_POST['add'])) {
    $productId = $_POST['add'];

    foreach ($products as $product) {
        if ($product['stockId'] === $productId) {

            if (!isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId] = [
                    'id'    => $product['stockId'],
                    'name'  => $product['name'],
                    'price' => $product['unitPrice'],
                    'qty'   => 1
                ];
            } else {
                $_SESSION['cart'][$productId]['qty']++;
            }

            break;
        }
    }
}

/* ---------- UPDATE CART QTY ---------- */
if (isset($_POST['increase'])) {
    $id = $_POST['increase'];
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['qty']++;
    }
}

if (isset($_POST['decrease'])) {
    $id = $_POST['decrease'];
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['qty']--;

        if ($_SESSION['cart'][$id]['qty'] <= 0) {
            unset($_SESSION['cart'][$id]);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Home</title>
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
      <a href="index.php?page=home" class="active">Home</a>
      <a href="index.php?page=cashier">Cashier</a>
      <a href="index.php?page=products">Products</a>
      <a href="index.php?page=members">Members</a>
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

    <h1>Home</h1>

    <div class="home-layout">

      <!-- PRODUCT GRID -->
      <section class="products-grid">
        <?php foreach ($products as $product): ?>
          <form method="post" class="product-card-form">
            <input type="hidden" name="add" value="<?= $product['stockId'] ?>">
            <button type="submit" class="product-card-btn">
              <h3><?= htmlspecialchars($product['name']) ?></h3>
              <p>RM <?= number_format($product['unitPrice'], 2) ?></p>
            </button>
          </form>
        <?php endforeach; ?>
      </section>

      <!-- ORDER PANEL -->
      <aside class="order-panel">
        <h2>Orders</h2>

        <?php $subtotal = 0; ?>

       <?php foreach ($_SESSION['cart'] as $item): ?>
  <div class="order-item">
    <div>
      <strong><?= htmlspecialchars($item['name']) ?></strong>

      <div class="qty-controls">
        <form method="post">
          <button type="submit" name="decrease" value="<?= $item['id'] ?>">−</button>
        </form>

        <span class="qty-value"><?= $item['qty'] ?></span>


        <form method="post">
          <button type="submit" name="increase" value="<?= $item['id'] ?>">+</button>
        </form>
      </div>
    </div>

    <div class="price">
      RM <?= number_format($item['price'] * $item['qty'], 2) ?>
    </div>
  </div>

  <?php $subtotal += $item['price'] * $item['qty']; ?>
<?php endforeach; ?>


        <hr>

        <?php
        $tax   = $subtotal * 0.06;
        $total = $subtotal + $tax;
        ?>

        <div class="summary-row">
          <span>Subtotal</span>
          <span>RM <?= number_format($subtotal, 2) ?></span>
        </div>

        <div class="summary-row">
          <span>Tax (6%)</span>
          <span>RM <?= number_format($tax, 2) ?></span>
        </div>

        <div class="summary-row total">
          <span>Total</span>
          <span>RM <?= number_format($total, 2) ?></span>
        </div>

       <form action="index.php?page=cashier" method="post">
            <button class="checkout-btn" <?= empty($_SESSION['cart']) ? 'disabled' : '' ?>>
            Checkout
            </button>
        </form>

      </aside>

    </div>
  </main>

</div>

<script src="../assets/js/auth.js"></script>
</body>
</html>
