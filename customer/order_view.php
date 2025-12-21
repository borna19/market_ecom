<?php
session_start();
include __DIR__ . '/../includes/e_db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: /market_ecom/index.php');
    exit;
}
$user_id = (int)$_SESSION['user_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch order
$os = mysqli_prepare($conn, "SELECT o.*, v.name as vendor_name FROM orders o LEFT JOIN vendors v ON o.vendor_id = v.id WHERE o.id = ? AND o.user_id = ? LIMIT 1");
mysqli_stmt_bind_param($os, 'ii', $order_id, $user_id);
mysqli_stmt_execute($os);
$ores = mysqli_stmt_get_result($os);
if (!$ores || mysqli_num_rows($ores) !== 1) {
    die('Order not found.');
}
$order = mysqli_fetch_assoc($ores);

// Get items
$it = mysqli_prepare($conn, "SELECT oi.*, p.name FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
mysqli_stmt_bind_param($it, 'i', $order_id);
mysqli_stmt_execute($it);
$items_res = mysqli_stmt_get_result($it);
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order #<?= htmlspecialchars($order['id']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-4">
    <h3>Order #<?= htmlspecialchars($order['id']) ?></h3>
    <p>Vendor: <?= htmlspecialchars($order['vendor_name'] ?? '') ?></p>
    <p>Total: ₹<?= htmlspecialchars($order['total_amount']) ?></p>
    <p>Status: <?= htmlspecialchars($order['status']) ?></p>

    <h5>Items</h5>
    <?php while($it = mysqli_fetch_assoc($items_res)): ?>
        <div class="card mb-2 p-2">
            <div class="d-flex justify-content-between">
                <div>
                    <strong><?= htmlspecialchars($it['name']) ?></strong>
                    <div>Price: ₹<?= htmlspecialchars($it['price']) ?></div>
                    <div>Qty: <?= htmlspecialchars($it['quantity']) ?></div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>

    <a href="orders.php" class="btn btn-secondary mt-3">Back to orders</a>
</div>
</body>
</html>