<?php
// ============================
// START SESSION
// ============================
session_start();

// ============================
// DATABASE CONNECTION
// ============================
include __DIR__ . '/../includes/e_db.php';

// ============================
// ADMIN ACCESS CHECK
// ============================
// If user is not logged in OR not admin, redirect
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// ============================
// UPDATE ORDER STATUS
// ============================
if (isset($_POST['update_status'])) {

    // Get order ID and new status from form
    $order_id = (int)$_POST['order_id'];
    $status   = $_POST['status']; // lowercase value

    // Update query
    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();

    // Redirect after update
    header("Location: manage_orders.php?success=1");
    exit;
}

// ============================
// FETCH ALL ORDERS
// ============================
$sql = "
    SELECT o.*, u.name AS customer_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
";

$result = $conn->query($sql);
$orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- SIDEBAR -->
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<!-- MAIN CONTENT -->
<div class="content" style="margin-left:250px; padding:30px;">

    <h2 class="mb-4">Manage Orders</h2>

    <!-- SUCCESS MESSAGE -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            Order status updated successfully!
        </div>
    <?php endif; ?>

    <!-- ORDERS TABLE -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Delivery Type</th>
                <th>Created At</th>
                <th width="230">Action</th>
            </tr>
        </thead>
        <tbody>

        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
                <tr>

                    <td><?= $order['id']; ?></td>

                    <td><?= htmlspecialchars($order['customer_name']); ?></td>

                    <td>₹<?= number_format($order['total_amount'], 2); ?></td>

                    <td class="text-capitalize"><?= $order['status']; ?></td>

                    <td><?= $order['delivery_type']; ?></td>

                    <td><?= $order['created_at']; ?></td>

                    <td>
                        <!-- STATUS UPDATE FORM -->
                        <form method="POST" class="d-flex gap-1 mb-1">

                            <input type="hidden" name="order_id" value="<?= $order['id']; ?>">

                            <select name="status" class="form-select form-select-sm">
                                <option value="pending" <?= $order['status']=='pending'?'selected':''; ?>>Pending</option>
                                <option value="processing" <?= $order['status']=='processing'?'selected':''; ?>>Processing</option>
                                <option value="shipped" <?= $order['status']=='shipped'?'selected':''; ?>>Shipped</option>
                                <option value="delivered" <?= $order['status']=='delivered'?'selected':''; ?>>Delivered</option>
                                <option value="cancelled" <?= $order['status']=='cancelled'?'selected':''; ?>>Cancelled</option>
                            </select>

                            <button type="submit" name="update_status" class="btn btn-sm btn-success">
                                Update
                            </button>
                        </form>

                        <!-- DELETE BUTTON -->
                        <a href="delete_order.php?id=<?= $order['id']; ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Are you sure you want to delete this order?');">
                           Delete
                        </a>
                    </td>

                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="text-center">No orders found</td>
            </tr>
        <?php endif; ?>

        </tbody>
    </table>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
