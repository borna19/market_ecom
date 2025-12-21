<?php
session_start();
include __DIR__ . '/../includes/e_db.php';

// 🔐 Vendor login check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header("Location: ../login.php");
    exit;
}

$vendor_id = $_SESSION['user_id'];

// ================= ORDERS LIST =================
$order_stmt = $conn->prepare("
    SELECT 
        o.id,
        o.user_id,
        o.total_amount,
        o.status,
        o.created_at,
        o.delivery_type,
        c.full_name AS customer_name
    FROM orders o
    JOIN customer c ON o.user_id = c.id
    WHERE o.vendor_id = ?
    ORDER BY o.created_at DESC
");

if (!$order_stmt) {
    die("Prepare failed: " . $conn->error);
}

$order_stmt->bind_param("i", $vendor_id);
$order_stmt->execute();
$orders = $order_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ================= SUMMARY =================
$summary_stmt = $conn->prepare("
    SELECT 
        COUNT(*) AS total_orders,
        SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) AS pending_orders,
        SUM(CASE WHEN status='confirmed' THEN 1 ELSE 0 END) AS confirmed_orders,
        SUM(CASE WHEN status='delivered' THEN 1 ELSE 0 END) AS delivered_orders,
        SUM(total_amount) AS total_revenue
    FROM orders
    WHERE vendor_id = ?
");

$summary_stmt->bind_param("i", $vendor_id);
$summary_stmt->execute();
$summary = $summary_stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Orders | Vendor Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* ===== Sidebar overlap fix ===== */
.content-area {
    margin-left: 250px; /* sidebar width */
    padding: 20px;
}

@media (max-width: 768px) {
    .content-area {
        margin-left: 0;
    }
}

.table th, .table td {
    vertical-align: middle;
}
</style>
</head>

<body>

<!-- ✅ SIDEBAR -->
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<!-- ✅ MAIN CONTENT -->
<div class="content-area">

    <h2 class="mb-4">My Orders</h2>

    <!-- ===== SUMMARY CARDS ===== -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h6>Total Orders</h6>
                    <h3><?= $summary['total_orders'] ?? 0 ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h6>Pending</h6>
                    <h3><?= $summary['pending_orders'] ?? 0 ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h6>Delivered</h6>
                    <h3><?= $summary['delivered_orders'] ?? 0 ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h6>Total Revenue</h6>
                    <h3>₹<?= $summary['total_revenue'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== ORDERS TABLE ===== -->
    <div class="card shadow-sm">
        <div class="card-header fw-bold">Orders List</div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#Order ID</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Delivery</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td>₹<?= $order['total_amount'] ?></td>
                            <td>
                                <span class="badge bg-info text-dark">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </td>
                            <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                            <td><?= ucfirst($order['delivery_type']) ?></td>
                            <td>
                                <a href="view_order.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-primary">
                                    View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">No orders found</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
