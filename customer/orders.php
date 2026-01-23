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

<!DOCTYPE html>
<html>
<head>
    <title>My Orders - Customer</title>
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

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn-primary { background: #3b82f6; color: #fff; }
        .btn-primary:hover { background: #2563eb; }
        .btn-success { background: #10b981; color: #fff; }
        .btn-success:hover { background: #059669; }
        .btn-secondary { background: #64748b; color: #fff; }
        .btn-secondary:hover { background: #475569; }

    </style>
</head>
<body>

<div class="layout">

    <!-- Sidebar -->
    <?php include __DIR__ . '/../includes/customer_sidebar.php'; ?>

    <!-- Main -->
    <div class="main">

        <div class="topbar">
            <h2><i class="fa-solid fa-box-open"></i> My Orders</h2>
            <span><?= date("l, d M Y") ?></span>
        </div>

        <div class="content-card">
            <?php if (!empty($orders)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Delivery Type</th>
                            <th>Date</th>
                            <th>Action</th>
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
                                        <i class="fa-solid fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="text-align:center; padding: 50px;">
                    <i class="fa-solid fa-box-open fa-4x" style="color:#cbd5e1; margin-bottom: 20px;"></i>
                    <h3 style="color:#64748b;">No orders found</h3>
                    <p style="color:#94a3b8; margin-bottom: 20px;">You haven't placed any orders yet. Start shopping!</p>
                    <a href="customer_dashboard.php" class="btn btn-primary">Go to Shop</a>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

</body>
</html>
