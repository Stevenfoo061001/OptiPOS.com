<?php
// public/api/checkout.php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . "/../../config/db.php";
date_default_timezone_set('Asia/Kuala_Lumpur');

// 1. Auth Check
if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$currentUser = $_SESSION['user']; 
$cashierId = $currentUser['id']; 

// Safety: Ensure Cashier ID fits in DB (max 7 chars)
if (strlen($cashierId) > 7) $cashierId = substr($cashierId, 0, 7);

// 3. Process Input
$input = json_decode(file_get_contents('php://input'), true);
$memberId = $input['member_id'] ?? null;

if (empty($input['items'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Cart is empty']);
    exit;
}

$paymentMethod = $input['payment_method'] ?? 'Cash';
$memberId = $input['member_id'] ?? null;
$pointsRedeemed = (int)($input['points_redeemed'] ?? 0);
$amountPaid = (float)($input['amount_paid'] ?? 0);

// Start Transaction
$paymentDate = date('Y-m-d');
$paymentTime = date('H:i:s');

$pdo->beginTransaction();

try {
    // ---------------------------------------------------------
    // ID GENERATION (Strictly 7 Chars)
    // ---------------------------------------------------------
    
    // 1. Order ID (Format: O000001)
    $stmt = $pdo->query("SELECT orderid FROM orders ORDER BY orderid DESC LIMIT 1");
    $lastOid = $stmt->fetchColumn();
    if ($lastOid) {
        $num = (int)substr($lastOid, 1);
        $orderId = 'O' . str_pad($num + 1, 6, '0', STR_PAD_LEFT);
    } else {
        $orderId = 'O000001';
    }

    // 2. Transaction ID (Format: T000001)
    $stmt = $pdo->query("SELECT transactionid FROM transactions ORDER BY transactionid DESC LIMIT 1");
    $lastTid = $stmt->fetchColumn();
    if ($lastTid) {
        $num = (int)substr($lastTid, 1);
        $transId = 'T' . str_pad($num + 1, 6, '0', STR_PAD_LEFT);
    } else {
        $transId = 'T000001';
    }

    // ---------------------------------------------------------

    // A. Calculate Totals & Deduct Stock
    $totalPrice = 0;
    $orderItems = [];

    foreach ($input['items'] as $item) {
        $stockId = $item['id'];
        $qty = (int)$item['qty'];

        // Lock row
        $stmt = $pdo->prepare("SELECT unitprice, quantity FROM stock WHERE stockid = ? FOR UPDATE");
        $stmt->execute([$stockId]);
        $stock = $stmt->fetch();

        if (!$stock) throw new Exception("Product $stockId not found.");
        if ($stock['quantity'] < $qty) throw new Exception("Not enough stock for $stockId.");

        $lineTotal = $stock['unitprice'] * $qty;
        $totalPrice += $lineTotal;

        // Deduct Stock
        $pdo->prepare("UPDATE stock SET quantity = quantity - ? WHERE stockid = ?")
            ->execute([$qty, $stockId]);

        $orderItems[] = [
            'id' => $stockId,
            'price' => $stock['unitprice'],
            'qty' => $qty
        ];
    }

    // B. Handle Member Point Redemption (Discount)
    $discount = 0;
    if ($memberId && $pointsRedeemed > 0) {
        $stmt = $pdo->prepare("SELECT points FROM member WHERE memberid = ? FOR UPDATE");
        $stmt->execute([$memberId]);
        $member = $stmt->fetch();

        if (!$member || $member['points'] < $pointsRedeemed) {
            throw new Exception("Insufficient member points.");
        }

        // Logic: 100 Points = RM 1.00 Discount
        $discount = $pointsRedeemed / 100; 

        // Deduct points from member
        $pdo->prepare("UPDATE member SET points = points - ? WHERE memberid = ?")
            ->execute([$pointsRedeemed, $memberId]);
    }

    // Calculate Final Amounts
    // $grandTotal = Amount BEFORE Tax (Taxable Amount)
    $grandTotal = max(0, $totalPrice - $discount);
    $tax = $grandTotal * 0.06; 
    $finalTotal = $grandTotal + $tax; // This is what the customer pays

    // C. Insert Order
    $stmt = $pdo->prepare("
    INSERT INTO orders (
        orderid,
        subtotal,
        tax,
        discount,
        grandtotal,
        memberid
    ) VALUES (?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $orderId,
    $totalPrice,   // subtotal
    $tax,
    $discount,
    $finalTotal,   // grandtotal
    $memberId
]);



    // D. Insert Order Items
    $stmt = $pdo->prepare("INSERT INTO order_item (orderid, stockid, unitprice, quantity) VALUES (?, ?, ?, ?)");
    foreach ($orderItems as $item) {
        $stmt->execute([$orderId, $item['id'], $item['price'], $item['qty']]);
    }

    // E. Handle Point Earning (RM 1 = 1 Point)
    $pointsEarned = 0;
    $newPointsBalance = 0;
    if ($memberId) {
        // Earn points based on Final Total (rounded down)
        $pointsEarned = floor($finalTotal); 
        $pdo->prepare("UPDATE member SET points = points + ? WHERE memberid = ?")
            ->execute([$pointsEarned, $memberId]);
            
        // Get new balance for receipt
        $stmt = $pdo->prepare("SELECT points FROM member WHERE memberid = ?");
        $stmt->execute([$memberId]);
        $newPointsBalance = $stmt->fetchColumn();
    }

    // F. Insert Transaction (Once only!)
    $stmt = $pdo->prepare("
    INSERT INTO transactions (
        transactionid,
        orderid,
        userid,
        paymentmethod,
        amountpaid,
        payment_date,
        payment_time,
        status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, 'PAID')
");

$stmt->execute([
    $transId,
    $orderId,
    $cashierId,   // cashier 也是 users 表里的 userid
    $paymentMethod,
    $amountPaid ?: $finalTotal,
    $paymentDate,
    $paymentTime
]);


    $pdo->commit();
    
    // Return Success
    echo json_encode([
        'success' => true, 
        'receipt' => $orderId, 
        'total' => $finalTotal, 
        'change' => ($amountPaid - $finalTotal),
        'points_earned' => $pointsEarned,
        'points_balance' => $newPointsBalance
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>