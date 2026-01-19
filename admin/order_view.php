<?php
session_start();
include __DIR__ . '/../includes/e_db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($order_id <= 0) {
    die('Invalid order id');
}

// Fetch order + user
$stmt = $conn->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email FROM orders o JOIN users u ON o.user_id=u.id WHERE o.id = ? LIMIT 1");
$stmt->bind_param('i', $order_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows !== 1) {
    die('Order not found');
}
$order = $res->fetch_assoc();

// Fetch items
$it = $conn->prepare("SELECT oi.*, p.product_name, p.id as product_id FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$it->bind_param('i', $order_id);
$it->execute();
$items = $it->get_result();

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
<?php include __DIR__ . '/panel_home.php'; ?>
<div class="content" style="margin-left:250px; padding:30px;">
    <h3>Order #<?= htmlspecialchars($order['id']) ?></h3>
    <div class="row">
        <div class="col-md-6">
            <p><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']) ?> (<?= htmlspecialchars($order['customer_email']) ?>)</p>
            <p><strong>Status:</strong> <?= htmlspecialchars(ucfirst($order['status'])) ?></p>
            <p><strong>Delivery Type:</strong> <?= htmlspecialchars($order['delivery_type']) ?></p>
            <p><strong>Total:</strong> ₹<?= number_format($order['total_amount'], 2) ?></p>
            <p><strong>Created:</strong> <?= htmlspecialchars($order['created_at']) ?></p>
        </div>
        <div class="col-md-6">
            <h5>Items</h5>
            <?php while($it = $items->fetch_assoc()): ?>
                <div class="card mb-2 p-2">
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong><?= htmlspecialchars($it['product_name'] ?? 'Product') ?></strong>
                            <div>Price: ₹<?= htmlspecialchars($it['price']) ?></div>
                            <div>Qty: <?= htmlspecialchars($it['quantity']) ?></div>
                        </div>
                        <div>
                            <!-- link to product if exists -->
                            <?php if(!empty($it['product_id'])): ?>
                                <a href="/market_ecom/admin/edit_product.php?id=<?= $it['product_id'] ?>" class="btn btn-sm btn-outline-primary">Edit Product</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <a href="manage_orders.php" class="btn btn-secondary mt-3">Back to orders</a>
</div>
</body>
</html>