<?php
session_start();
include __DIR__ . '/../includes/e_db.php'; // Database connection

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Update order status
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();

    header("Location: manage_orders.php?success=1");
    exit;
}

// Fetch all orders with customer name
$orders = $conn->query("
    SELECT o.*, u.name AS customer_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<div class="content" style="margin-left:250px; padding:30px;">
    <h1 class="mb-4">Manage Orders</h1>

    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success">Order status updated successfully!</div>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Delivery Type</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if(!empty($orders)): ?>
            <?php foreach($orders as $order): ?>
            <tr>
                <td><?= $order['id'] ?></td>
                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                <td>$<?= number_format($order['total_amount'],2) ?></td>
                <td><?= $order['status'] ?></td>
                <td><?= $order['delivery_type'] ?></td>
                <td><?= $order['created_at'] ?></td>
                <td>
                    <!-- Simple edit form for status -->
                    <form method="POST" style="display:inline-block;">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <select name="status" class="form-select form-select-sm d-inline-block" style="width:auto;">
                            <option value="Pending" <?= $order['status']=='Pending'?'selected':'' ?>>Pending</option>
                            <option value="Processing" <?= $order['status']=='Processing'?'selected':'' ?>>Processing</option>
                            <option value="Shipped" <?= $order['status']=='Shipped'?'selected':'' ?>>Shipped</option>
                            <option value="Delivered" <?= $order['status']=='Delivered'?'selected':'' ?>>Delivered</option>
                            <option value="Cancelled" <?= $order['status']=='Cancelled'?'selected':'' ?>>Cancelled</option>
                        </select>
                        <button type="submit" name="update_status" class="btn btn-sm btn-success">Update</button>
                    </form>
                    <a href="delete_order.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="7" class="text-center">No orders found</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
