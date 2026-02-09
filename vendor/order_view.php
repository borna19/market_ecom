<?php
session_start();
include __DIR__ . '/../includes/e_db.php';

// Only vendor allowed
$role = strtolower($_SESSION['role'] ?? '');
if (!isset($_SESSION['user_id']) || $role !== 'vendor') {
    $_SESSION['message'] = "Please login as vendor.";
    header("Location: /market_ecom/index.php");
    exit;
}

$vendor_id = (int)$_SESSION['user_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch order details ensuring it contains at least one product from this vendor
// We need to join order_items and products to verify ownership
$query = "
    SELECT o.*, u.name as customer_name, u.email as customer_email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
    AND EXISTS (
        SELECT 1 FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = o.id AND p.vendor_id = ?
    )
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $query);
if ($stmt === false) {
    die("Database Error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, 'ii', $order_id, $vendor_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if (!$res || mysqli_num_rows($res) !== 1) {
    die('Order not found or you do not have permission to view this order.');
}
$order = mysqli_fetch_assoc($res);

// Fetch items for this order that belong to this vendor
$items_query = "
    SELECT oi.*, p.name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ? AND p.vendor_id = ?
";
$it_stmt = mysqli_prepare($conn, $items_query);
if ($it_stmt === false) {
    die("Database Error (Items): " . mysqli_error($conn));
}
mysqli_stmt_bind_param($it_stmt, 'ii', $order_id, $vendor_id);
mysqli_stmt_execute($it_stmt);
$items_res = mysqli_stmt_get_result($it_stmt);

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    // Update order status
    // Note: In a real multi-vendor system, status might be per-item or require all vendors to ship.
    // For simplicity here, we allow any vendor involved to update the main order status.
    $update_stmt = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($update_stmt, 'si', $new_status, $order_id);
    if (mysqli_stmt_execute($update_stmt)) {
        $order['status'] = $new_status; // Update local variable for display
        $success_msg = "Order status updated successfully.";
    } else {
        $error_msg = "Failed to update status.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order #<?= htmlspecialchars($order['id']) ?> - Vendor</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f8fafc;
        }

        .layout {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: #1e293b;
            color: #fff;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            height: 100%;
        }

        .sidebar-header {
            padding: 25px 20px;
            background: #0f172a;
            text-align: center;
            border-bottom: 1px solid #334155;
            flex-shrink: 0;
        }

        .sidebar-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .sidebar-menu li {
            border-bottom: 1px solid #334155;
            flex-shrink: 0;
        }

        .sidebar-menu li:last-child {
            border-bottom: none;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: #cbd5e1;
            text-decoration: none;
            font-size: 15px;
            transition: all 0.3s;
            gap: 12px;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: #3b82f6;
            color: #fff;
            padding-left: 30px;
        }

        .sidebar-menu a i {
            width: 20px;
            text-align: center;
        }

        .logout-link {
            background: #ef4444;
            color: white !important;
            justify-content: center;
        }
        .logout-link:hover {
            background: #dc2626 !important;
            padding-left: 25px !important;
        }

        /* Main */
        .main {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            height: 100%;
        }

        .topbar {
            background: #fff;
            padding: 20px 30px;
            border-radius: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            margin-bottom: 30px;
            flex-shrink: 0;
        }

        .topbar h2 { margin: 0; font-size: 22px; color: #1e293b; }
        .topbar span { color: #64748b; font-weight: 500; }

        .card {
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            margin-bottom: 30px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 15px;
        }

        .card-header h4 { margin: 0; color: #1e293b; }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .info-box h6 {
            color: #64748b;
            margin-bottom: 10px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
        }

        .info-box p {
            margin: 0 0 5px;
            color: #334155;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            color: #334155;
        }

        th {
            background: #f8fafc;
            font-weight: 600;
            color: #475569;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        .badge-pending { background: #fffbeb; color: #b45309; }
        .badge-completed { background: #f0fdf4; color: #16a34a; }
        .badge-cancelled { background: #fef2f2; color: #dc2626; }
        .badge-processing { background: #eff6ff; color: #2563eb; }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 20px;
        }
        .btn-back:hover { color: #334155; }

        .status-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .status-select {
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background: white;
        }
        .btn-update {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn-update:hover { background: #2563eb; }
    </style>
</head>
<body>

<div class="layout">

    <!-- Sidebar -->
    <?php include __DIR__ . '/../includes/vendor_sidebar.php'; ?>

    <!-- Main -->
    <div class="main">

        <div class="topbar">
            <h2><i class="fa-solid fa-box-open"></i> Order Details</h2>
            <span><?= date("l, d M Y") ?></span>
        </div>

        <a href="orders.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Back to Orders</a>

        <?php if (isset($success_msg)): ?>
            <div style="background-color: #f0fdf4; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <?= htmlspecialchars($success_msg) ?>
            </div>
        <?php endif; ?>
        <?php if (isset($error_msg)): ?>
            <div style="background-color: #fef2f2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h4>Order #<?= htmlspecialchars($order['id']) ?></h4>
                <span class="badge badge-<?= strtolower($order['status']) ?>"><?= ucfirst($order['status']) ?></span>
            </div>

            <div class="info-grid">
                <div class="info-box">
                    <h6>Customer Details</h6>
                    <p><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($order['customer_email']) ?></p>
                    <p><strong>Date:</strong> <?= date("d M Y, h:i A", strtotime($order['created_at'])) ?></p>
                </div>
                <div class="info-box">
                    <h6>Shipping Information</h6>
                    <p><strong>Type:</strong> <?= ucfirst($order['delivery_type']) ?></p>
                    <p><strong>Address:</strong><br>
                        <?php if($order['delivery_type'] == 'pickup'): ?>
                            <span class="badge badge-processing">Store Pickup</span>
                        <?php else: ?>
                            <?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <div class="info-box" style="margin-bottom: 30px;">
                <h6>Update Status</h6>
                <form method="POST" class="status-form">
                    <select name="status" class="status-select">
                        <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                        <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                        <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                    <button type="submit" name="update_status" class="btn-update">Update Status</button>
                </form>
            </div>

            <h5 style="margin-bottom: 15px; color: #1e293b;">Items Ordered (Your Products)</h5>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_vendor_amount = 0;
                        while($item = mysqli_fetch_assoc($items_res)):
                            $line_total = ($item['unit_price'] ?? 0) * $item['quantity'];
                            $total_vendor_amount += $line_total;
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td>₹<?= number_format($item['unit_price'] ?? 0, 2) ?></td>
                                <td><?= htmlspecialchars($item['quantity']) ?></td>
                                <td>₹<?= number_format($line_total, 2) ?></td>
                            </tr>
                        <?php endwhile; ?>
                        <tr style="background-color: #f8fafc; font-weight: bold;">
                            <td colspan="3" style="text-align: right;">Total (Your Items):</td>
                            <td>₹<?= number_format($total_vendor_amount, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

</body>
</html>
