<?php
session_start();
include __DIR__ . '/../includes/e_db.php';

// Require customer
$role = strtolower($_SESSION['role'] ?? '');
if (!isset($_SESSION['user_id']) || $role !== 'customer') {
    $_SESSION['message'] = 'Please login as customer.';
    header('Location: /market_ecom/index.php');
    exit;
}
$user_id = (int)$_SESSION['user_id'];

// Fetch Orders
$sql = "SELECT id, total_amount, status, delivery_type, created_at
        FROM orders 
        WHERE user_id = $user_id
        ORDER BY id DESC";

$result = mysqli_query($conn, $sql);
$orders = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
?>

<?php include __DIR__ . '/../includes/customer_header.php'; ?>

<div class="container py-5">

    <h2 class="fw-bold text-dark mb-4"><i class="fa-solid fa-box-open me-2"></i> My Orders</h2>

    <?php if (empty($orders)): ?>
        <div class="text-center py-5 card shadow-sm border-0">
            <i class="fa-solid fa-box-open fa-4x text-muted mb-3"></i>
            <h3 class="text-muted">No orders found</h3>
            <p class="text-secondary mb-4">You haven't placed any orders yet. Start shopping!</p>
            <a href="customer_dashboard.php" class="btn btn-primary btn-lg mx-auto" style="width: fit-content;">
                <i class="fa-solid fa-store me-2"></i> Go to Shop
            </a>
        </div>
    <?php else: ?>
        <div class="card shadow-sm border-0 p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="table-light">
                            <th scope="col">Order ID</th>
                            <th scope="col">Total Amount</th>
                            <th scope="col">Status</th>
                            <th scope="col">Delivery Type</th>
                            <th scope="col">Date</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?= $order['id']; ?></td>
                                <td>₹<?= number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="badge badge-<?= strtolower($order['status']); ?>">
                                        <?= htmlspecialchars(ucfirst($order['status'])); ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($order['delivery_type']); ?></td>
                                <td><?= date("d M Y", strtotime($order['created_at'])); ?></td>
                                <td>
                                    <a href="order_view.php?id=<?= $order['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fa-solid fa-eye"></i> View Order
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
