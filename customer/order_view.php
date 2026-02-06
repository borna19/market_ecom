<?php
session_start();
include __DIR__ . '/../includes/e_db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: /market_ecom/index.php');
    exit;
}
$user_id = (int)$_SESSION['user_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch order - REMOVED JOIN WITH VENDORS to fix error
$query = "SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1";
$os = mysqli_prepare($conn, $query);

if ($os === false) {
    die("Database Error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($os, 'ii', $order_id, $user_id);
mysqli_stmt_execute($os);
$ores = mysqli_stmt_get_result($os);

if (!$ores || mysqli_num_rows($ores) !== 1) {
    die('Order not found.');
}
$order = mysqli_fetch_assoc($ores);

// Get items
$it = mysqli_prepare($conn, "SELECT oi.*, p.name FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
if ($it === false) {
    die("Database Error (Items): " . mysqli_error($conn));
}
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include __DIR__ . '/../includes/customer_header.php'; ?>

<div class="container py-5">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold text-primary">Order #<?= htmlspecialchars($order['id']) ?></h4>
            <span class="badge bg-secondary"><?= htmlspecialchars($order['status']) ?></span>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="text-muted">Order Details</h6>
                    <p class="mb-1"><strong>Date:</strong> <?= date("d M Y, h:i A", strtotime($order['created_at'])) ?></p>
                    <p class="mb-1"><strong>Total Amount:</strong> <span class="text-success fw-bold">₹<?= htmlspecialchars($order['total_amount']) ?></span></p>
                    <p class="mb-1"><strong>Payment Method:</strong> <?= htmlspecialchars($order['delivery_type']) ?></p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted">Shipping Address</h6>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
                </div>
            </div>

            <h5 class="fw-bold mb-3">Items Ordered</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($item = mysqli_fetch_assoc($items_res)): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td>₹<?= htmlspecialchars($item['unit_price'] ?? 0) ?></td>
                                <td><?= htmlspecialchars($item['quantity']) ?></td>
                                <td>₹<?= number_format(($item['unit_price'] ?? 0) * $item['quantity'], 2) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3">
            <a href="orders.php" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-2"></i> Back to Orders
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
