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
        <h3>My Earnings</h3>
        <p>Overview of your earnings from sales.</p>

        <?php
        $user_id = (int) $_SESSION['user_id'];
        $vendor_id = getVendorIdForUser($conn, $user_id);
        if (!$vendor_id) {
            echo '<div class="alert alert-danger">Vendor mapping not found. Please register as a vendor or contact support.</div>';
            exit;
        }
        // This assumes you have an `orders` table with `vendor_id` and `total_amount` columns
        $stmt = mysqli_prepare($conn, "SELECT SUM(total_amount) as total_earnings FROM orders WHERE vendor_id = ? AND status IN ('completed','delivered')");
        mysqli_stmt_bind_param($stmt, 'i', $vendor_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $sum = 0;
        if ($res && $row = mysqli_fetch_assoc($res)) {
            $sum = $row['total_earnings'] ?? 0;
        }
        ?>

        <h4>Total Earnings: $<?= htmlspecialchars(number_format((float)$sum, 2)) ?></h4>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
