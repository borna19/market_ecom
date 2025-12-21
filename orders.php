<?php
session_start();
include __DIR__ . '/../includes/e_db.php';
include __DIR__ . '/../includes/vendor_helpers.php';

// Vendor access check
$role = strtolower($_SESSION['role'] ?? '');
if (!isset($_SESSION['user_id']) || ($role !== 'vendor' && $role !== 'farmer')) {
    $_SESSION['message'] = 'Access denied: vendors only.';
    header('Location: /market_ecom/');
    exit;
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<style>
.content-area { margin-left: 260px; padding: 30px; }
.card-box { background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,0.08);} 
</style>

<div class="content-area">
    <div class="card-box">
        <h3>Vendor Orders</h3>
        <p>Orders placed for your products.</p>

        <?php
        $vendor_id = (int) ($_SESSION['vendor_id'] ?? 0);
        if (!$vendor_id) {
            echo '<div class="alert alert-danger">Vendor mapping not found. Please register as a vendor or contact support.</div>';
            exit;
        }

        // ------------------ FETCH ORDERS ------------------ //
        $sql = "SELECT o.id, o.user_id, o.total_amount, o.status, o.delivery_type, o.shipping_address, o.created_at,
                       u.name AS customer_name
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.vendor_id = ?
                ORDER BY o.created_at DESC";

        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            die("SQL Prepare Failed: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, 'i', $vendor_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        ?>

        <?php if ($res && mysqli_num_rows($res) > 0): ?>
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Delivery Type</th>
                        <th>Shipping Address</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($o = mysqli_fetch_assoc($res)): ?>
                    <tr>
                        <td>#<?= htmlspecialchars($o['id']) ?></td>
                        <td><?= htmlspecialchars($o['customer_name'] ?? $o['user_id']) ?></td>
                        <td>$<?= htmlspecialchars($o['total_amount']) ?></td>
                        <td><?= htmlspecialchars($o['status']) ?></td>
                        <td><?= htmlspecialchars($o['delivery_type']) ?></td>
                        <td><?= htmlspecialchars($o['shipping_address']) ?></td>
                        <td><?= htmlspecialchars($o['created_at']) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No orders found yet.</p>
        <?php endif; ?>

    </div>
</div>

<?php
include __DIR__ . '/../includes/footer.php';
?>
