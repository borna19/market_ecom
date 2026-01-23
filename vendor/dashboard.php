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

// --- Data Fetching ---
// Total products
$pq = mysqli_query($conn, "SELECT COUNT(*) as total FROM products WHERE vendor_id = $vendor_id");
$total_products = ($pq) ? (mysqli_fetch_assoc($pq)['total'] ?? 0) : 0;

// Total orders
$oq = mysqli_query($conn, "
    SELECT COUNT(DISTINCT o.id) as total 
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE p.vendor_id = $vendor_id
");
$total_orders = ($oq) ? (mysqli_fetch_assoc($oq)['total'] ?? 0) : 0;

// Revenue
$rq = mysqli_query($conn, "
    SELECT SUM(oi.quantity * oi.price) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE p.vendor_id = $vendor_id
");
$revenue = ($rq) ? (mysqli_fetch_assoc($rq)['revenue'] ?? 0) : 0;

// Recent Products (Dynamic)
$recent_products_res = mysqli_query($conn, "SELECT id, name, price, stock FROM products WHERE vendor_id = $vendor_id ORDER BY id DESC LIMIT 5");

// --- Chart Demo Data ---
$sales_data = [
    ['Product', 'Sales', ['role' => 'style']],
    ['Organic Apples', 100, '#3b82f6'],
    ['Fresh Carrots', 80, '#10b981'],
    ['Farm Eggs', 120, '#f97316'],
    ['Local Honey', 50, '#f59e0b'],
    ['Whole Wheat Bread', 70, '#84cc16']
];

$category_data = [
    ['Category', 'Sales'],
    ['Fruits', 150],
    ['Vegetables', 80],
    ['Dairy', 120],
    ['Bakery', 70],
    ['Other', 50]
];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vendor Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart', 'bar']});
        google.charts.setOnLoadCallback(drawCharts);

        function drawCharts() {
            // --- Bar Chart (Product Sales) ---
            var salesData = google.visualization.arrayToDataTable(<?= json_encode($sales_data) ?>);
            var salesOptions = {
                title: 'Top Product Sales',
                chartArea: {width: '70%'},
                legend: { position: 'none' },
                hAxis: { title: 'Sales', minValue: 0 },
                vAxis: { title: 'Product' }
            };
            var salesChart = new google.visualization.BarChart(document.getElementById('product_sales_chart'));
            salesChart.draw(salesData, salesOptions);

            // --- Pie Chart (Category Sales) ---
            var categoryData = google.visualization.arrayToDataTable(<?= json_encode($category_data) ?>);
            var categoryOptions = {
                title: 'Sales by Category',
                pieHole: 0.4,
                colors: ['#3b82f6', '#10b981', '#f97316', '#f59e0b', '#84cc16']
            };
            var categoryChart = new google.visualization.PieChart(document.getElementById('category_sales_chart'));
            categoryChart.draw(categoryData, categoryOptions);
        }
    </script>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f1f5f9;
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
            grid-template-columns: repeat(auto-fit,minmax(240px,1fr));
            gap: 25px;
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

        .stat h3 { margin: 0 0 10px; color: #64748b; font-size: 16px; font-weight: 600; }
        .stat p { margin: 0; font-size: 32px; font-weight: 700; color: #1e293b; }

        .chart-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        .chart-container {
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }

        .products-table {
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }

        .products-table h3 { margin-top: 0; margin-bottom: 20px; color: #1e293b; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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

        .btn {
            background: #3b82f6;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            display: inline-block;
            transition: background 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background: #2563eb;
        }
    </style>
</head>
<body>

<div class="layout">

    <!-- Sidebar -->
    <?php include __DIR__ . '/../includes/vendor_sidebar.php'; ?>

    <!-- Main -->
    <div class="main">

        <div class="topbar">
            <h2>Welcome, <?= htmlspecialchars($_SESSION['name']) ?> 👋</h2>
            <span><?= date("l, d M Y") ?></span>
        </div>

        <!-- Stats -->
        <div class="stats">
            <div class="stat">
                <h3><i class="fa-solid fa-boxes-stacked"></i> Total Products</h3>
                <p><?= $total_products ?></p>
            </div>
            <div class="stat">
                <h3><i class="fa-solid fa-clipboard-list"></i> Total Orders</h3>
                <p><?= $total_orders ?></p>
            </div>
            <div class="stat">
                <h3><i class="fa-solid fa-indian-rupee-sign"></i> Total Revenue</h3>
                <p>₹<?= number_format($revenue, 2) ?></p>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="chart-row">
            <div class="chart-container">
                <div id="product_sales_chart"></div>
            </div>
            <div class="chart-container">
                <div id="category_sales_chart"></div>
            </div>
        </div>

        <!-- Recent Products Table -->
        <div class="products-table">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h3>Recent Products</h3>
                <a href="manage_products.php" class="btn"><i class="fa-solid fa-list-check"></i> Manage All</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($recent_products_res && mysqli_num_rows($recent_products_res) > 0): ?>
                    <?php while($p = mysqli_fetch_assoc($recent_products_res)): ?>
                        <tr>
                            <td>#<?= $p['id'] ?></td>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td>₹<?= htmlspecialchars($p['price']) ?></td>
                            <td><?= htmlspecialchars($p['stock']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align:center; padding: 30px;">No products found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

</body>
</html>
