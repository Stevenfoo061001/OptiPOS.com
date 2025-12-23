<?php
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$sql = "
    SELECT
        t.transactionid,
        t.orderid,
        t.userid,
        t.paymentmethod,
        t.amountpaid,
        t.payment_date,
        t.payment_time,
        t.status,

        o.subtotal,
        o.tax,
        o.discount,
        o.grandtotal,
        o.memberid,

        m.name AS member_name,
        m.points AS member_points
    FROM transactions t
    JOIN orders o ON t.orderid = o.orderid
    LEFT JOIN member m ON o.memberid = m.memberid
    ORDER BY t.payment_date DESC, t.payment_time DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
