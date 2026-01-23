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

// Fetch orders for this vendor's products
$query = "
    SELECT o.id as order_id, o.created_at, u.name as customer_name,
           p.name as product_name, oi.quantity, oi.price, (oi.quantity * oi.price) as total_price,
           o.status
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN products p ON oi.product_id = p.id
    JOIN users u ON o.user_id = u.id
    WHERE p.vendor_id = $vendor_id
    ORDER BY o.created_at DESC
";
$orders = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Orders - Vendor</title>
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

        .table-card {
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
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

        tr:hover {
            background: #f8fafc;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-pending { background: #fffbeb; color: #b45309; }
        .badge-completed { background: #f0fdf4; color: #16a34a; }
        .badge-cancelled { background: #fef2f2; color: #dc2626; }
        .badge-processing { background: #eff6ff; color: #2563eb; }
    </style>
</head>
<body>

<div class="layout">

    <!-- Sidebar -->
    <?php include __DIR__ . '/../includes/vendor_sidebar.php'; ?>

    <!-- Main -->
    <div class="main">

        <div class="topbar">
            <h2><i class="fa-solid fa-box"></i> Customer Orders</h2>
            <span><?= date("l, d M Y") ?></span>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders && mysqli_num_rows($orders) > 0): ?>
                        <?php while($o = mysqli_fetch_assoc($orders)): ?>
                            <tr>
                                <td>#<?= $o['order_id'] ?></td>
                                <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                                <td><?= htmlspecialchars($o['customer_name']) ?></td>
                                <td><?= htmlspecialchars($o['product_name']) ?></td>
                                <td><?= $o['quantity'] ?></td>
                                <td>₹<?= number_format($o['total_price'], 2) ?></td>
                                <td>
                                    <span class="badge badge-<?= strtolower($o['status'] ?? 'pending') ?>">
                                        <?= ucfirst($o['status'] ?? 'Pending') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding: 40px;">
                                <i class="fa-solid fa-inbox fa-3x" style="margin-bottom:15px; color:#cbd5e1;"></i>
                                <p style="color:#64748b;">No orders found yet.</p>
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
