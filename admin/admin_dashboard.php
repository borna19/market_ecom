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

// Stats
$total_users     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users"))['c'] ?? 0;
$total_customers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='customer'"))['c'] ?? 0;
$total_vendors   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='vendor'"))['c'] ?? 0;
$total_products  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM products"))['c'] ?? 0;
$total_orders    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders"))['c'] ?? 0;

// Revenue
$rq = mysqli_query($conn, "SELECT SUM(quantity * price) as total FROM order_items");
$revenue = ($rq) ? (mysqli_fetch_assoc($rq)['total'] ?? 0) : 0;

// Latest users
$recent_users = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC LIMIT 5");

// Chart Data (Demo)
$user_growth_data = [
    ['Month', 'Users'],
    ['Jan', 50],
    ['Feb', 80],
    ['Mar', 120],
    ['Apr', 150],
    ['May', 200]
];

$role_distribution_data = [
    ['Role', 'Count'],
    ['Customers', (int)$total_customers],
    ['Vendors', (int)$total_vendors],
    ['Admins', (int)($total_users - $total_customers - $total_vendors)]
];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart', 'bar']});
        google.charts.setOnLoadCallback(drawCharts);

        function drawCharts() {
            // User Growth Chart
            var growthData = google.visualization.arrayToDataTable(<?= json_encode($user_growth_data) ?>);
            var growthOptions = {
                title: 'User Growth (Monthly)',
                legend: { position: 'bottom' },
                colors: ['#3b82f6']
            };
            var growthChart = new google.visualization.LineChart(document.getElementById('user_growth_chart'));
            growthChart.draw(growthData, growthOptions);

            // Role Distribution Chart
            var roleData = google.visualization.arrayToDataTable(<?= json_encode($role_distribution_data) ?>);
            var roleOptions = {
                title: 'User Distribution by Role',
                pieHole: 0.4,
                colors: ['#10b981', '#f59e0b', '#6366f1']
            };
            var roleChart = new google.visualization.PieChart(document.getElementById('role_chart'));
            roleChart.draw(roleData, roleOptions);
        }
    </script>
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

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit,minmax(200px,1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat {
            background: #fff;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            transition: transform 0.2s;
        }
        .stat:hover { transform: translateY(-5px); }

        .stat h3 { margin: 0 0 10px; color: #64748b; font-size: 14px; font-weight: 600; text-transform: uppercase; }
        .stat p { margin: 0; font-size: 28px; font-weight: 700; color: #1e293b; }

        .chart-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        .chart-container {
            background: #fff;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            height: 350px;
        }

        .table-card {
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }

        .table-card h3 { margin-top: 0; margin-bottom: 20px; color: #1e293b; }

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
        }
        .badge-admin { background: #e0e7ff; color: #4338ca; }
        .badge-vendor { background: #fef3c7; color: #b45309; }
        .badge-customer { background: #dcfce7; color: #15803d; }

    </style>
</head>
<body>

<div class="layout">

    <!-- Sidebar -->
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <!-- Main -->
    <div class="main">

        <div class="topbar">
            <h2>Welcome Admin, <?= htmlspecialchars($_SESSION['name']) ?> 👋</h2>
            <span><?= date("l, d M Y") ?></span>
        </div>

        <!-- Stats -->
        <div class="stats">
            <div class="stat">
                <h3><i class="fa-solid fa-users text-primary"></i> Total Users</h3>
                <p><?= $total_users ?></p>
            </div>
            <div class="stat">
                <h3><i class="fa-solid fa-user text-success"></i> Customers</h3>
                <p><?= $total_customers ?></p>
            </div>
            <div class="stat">
                <h3><i class="fa-solid fa-store text-warning"></i> Vendors</h3>
                <p><?= $total_vendors ?></p>
            </div>
            <div class="stat">
                <h3><i class="fa-solid fa-box text-info"></i> Products</h3>
                <p><?= $total_products ?></p>
            </div>
            <div class="stat">
                <h3><i class="fa-solid fa-receipt text-secondary"></i> Orders</h3>
                <p><?= $total_orders ?></p>
            </div>
            <div class="stat">
                <h3><i class="fa-solid fa-wallet text-danger"></i> Revenue</h3>
                <p>₹<?= number_format($revenue, 2) ?></p>
            </div>
        </div>

        <!-- Charts -->
        <div class="chart-row">
            <div class="chart-container">
                <div id="user_growth_chart" style="height: 100%;"></div>
            </div>
            <div class="chart-container">
                <div id="role_chart" style="height: 100%;"></div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="table-card">
            <h3>Recent Users</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($u = mysqli_fetch_assoc($recent_users)): ?>
                    <tr>
                        <td>#<?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <span class="badge badge-<?= strtolower($u['role']) ?>">
                                <?= ucfirst($u['role']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

</body>
</html>
