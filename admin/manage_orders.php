<?php
session_start();
include __DIR__ . '/../includes/e_db.php';

// Only admin allowed
$role = strtolower($_SESSION['role'] ?? '');
if (!isset($_SESSION['user_id']) || $role !== 'admin') {
    $_SESSION['message'] = "Admin access only.";
    header("Location: /market_ecom/index.php");
    exit;
}

// Update Order Status
if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status   = $_POST['status'];
    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
    $_SESSION['success'] = "Order status updated successfully.";
    header("Location: manage_orders.php");
    exit;
}

// Fetch All Orders
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
<html>
<head>
    <title>Manage Orders - Admin</title>
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

        .content-card {
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }

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
            vertical-align: middle;
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
            text-transform: capitalize;
        }
        .badge-pending { background: #fffbeb; color: #b45309; }
        .badge-processing { background: #eff6ff; color: #2563eb; }
        .badge-shipped { background: #f0f9ff; color: #0284c7; }
        .badge-delivered { background: #f0fdf4; color: #16a34a; }
        .badge-cancelled { background: #fef2f2; color: #dc2626; }

        .form-select-sm {
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
        }

        .btn-success {
            background: #10b981;
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn-success:hover { background: #059669; }

    </style>
</head>
<body>

<div class="layout">

    <!-- Sidebar -->
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <!-- Main -->
    <div class="main">

        <div class="topbar">
            <h2><i class="fa-solid fa-receipt"></i> Manage Orders</h2>
            <span><?= date("l, d M Y") ?></span>
        </div>

        <div class="content-card">

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Delivery</th>
                        <th>Date</th>
                        <th width="250">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= $order['id']; ?></td>
                            <td><?= htmlspecialchars($order['customer_name']); ?></td>
                            <td>₹<?= number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span class="badge badge-<?= strtolower($order['status']); ?>">
                                    <?= htmlspecialchars($order['status']); ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($order['delivery_type']); ?></td>
                            <td><?= date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td>
                                <form method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                                    <select name="status" class="form-select-sm">
                                        <option value="pending" <?= $order['status']=='pending'?'selected':''; ?>>Pending</option>
                                        <option value="processing" <?= $order['status']=='processing'?'selected':''; ?>>Processing</option>
                                        <option value="shipped" <?= $order['status']=='shipped'?'selected':''; ?>>Shipped</option>
                                        <option value="delivered" <?= $order['status']=='delivered'?'selected':''; ?>>Delivered</option>
                                        <option value="cancelled" <?= $order['status']=='cancelled'?'selected':''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn-success">
                                        <i class="fa-solid fa-save"></i> Update
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding: 40px;">
                            <i class="fa-solid fa-inbox fa-3x" style="margin-bottom:15px; color:#cbd5e1;"></i>
                            <p style="color:#64748b;">No orders found.</p>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

</body>
</html>
