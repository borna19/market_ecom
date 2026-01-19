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

// Total products
$pq = mysqli_query($conn, "SELECT COUNT(*) as total FROM products WHERE vendor_id = $vendor_id");
$total_products = mysqli_fetch_assoc($pq)['total'] ?? 0;

// Total orders
$oq = mysqli_query($conn, "
    SELECT COUNT(DISTINCT o.id) as total 
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE p.vendor_id = $vendor_id
");
$total_orders = mysqli_fetch_assoc($oq)['total'] ?? 0;

// Revenue
$rq = mysqli_query($conn, "
    SELECT SUM(oi.quantity * oi.price) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE p.vendor_id = $vendor_id
");
// $revenue = mysqli_fetch_assoc($rq)['revenue'] ?? 0;

// Vendor products
$products = mysqli_query($conn, "SELECT * FROM products WHERE vendor_id = $vendor_id ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vendor Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f8fafc;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 240px;
            background: #0f172a;
            color: #fff;
            padding: 20px;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            color: #cbd5e1;
            text-decoration: none;
            padding: 10px;
            margin-bottom: 8px;
            border-radius: 8px;
        }

        .sidebar a:hover, .sidebar a.active {
            background: #3b82f6;
            color: #fff;
        }

        /* Main */
        .main {
            flex: 1;
            padding: 25px;
        }

        .topbar {
            background: #fff;
            padding: 15px 20px;
            border-radius: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit,minmax(220px,1fr));
            gap: 15px;
            margin: 25px 0;
        }

        .stat {
            background: #fff;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .products {
            background: #fff;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }

        th {
            background: #f1f5f9;
        }

        .btn {
            background: #2563eb;
            color: #fff;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
        }

        .btn:hover {
            background: #1d4ed8;
        }

        .btn-danger {
            background: #ef4444;
        }

        .btn-danger:hover {
            background: #dc2626;
        }
    </style>
</head>
<body>

<div class="layout">

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Vendor Panel</h2>
        <a class="active" href="dashboard.php">📊 Dashboard</a>
        <a href="add_product.php">➕ Add Product</a>
        <a href="orders.php">📦 Orders</a>
        <a href="../logout.php">🚪 Logout</a>
    </div>

    <!-- Main -->
    <div class="main">

        <div class="topbar">
            <h2>Welcome, <?= htmlspecialchars($_SESSION['name']) ?> 👨‍🌾</h2>
            <span><?= date("d M Y") ?></span>
        </div>

        <!-- Stats -->
        <div class="stats">
            <div class="stat">
                <h3>🛒 Products</h3>
                <p style="font-size:28px"><?= $total_products ?></p>
            </div>
            <div class="stat">
                <h3>📦 Orders</h3>
                <p style="font-size:28px"><?= $total_orders ?></p>
            </div>
            <div class="stat">
                <h3>💰 Revenue</h3>
                <p style="font-size:28px">₹<?= number_format($revenue, 2) ?></p>
            </div>
        </div>

        <!-- Products -->
        <div class="products">
            <h3>Your Products</h3>
            <a href="add_product.php" class="btn">+ Add New Product</a>

            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Action</th>
                </tr>

                <?php if ($products && mysqli_num_rows($products) > 0): ?>
                    <?php while($p = mysqli_fetch_assoc($products)): ?>
                        <tr>
                            <td><?= $p['id'] ?></td>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td>₹<?= $p['price'] ?></td>
                            <td><?= $p['stock'] ?></td>
                            <td>
                                <a class="btn" href="edit_product.php?id=<?= $p['id'] ?>">Edit</a>
                                <a class="btn btn-danger" href="delete_product.php?id=<?= $p['id'] ?>" onclick="return confirm('Delete product?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">No products yet.</td></tr>
                <?php endif; ?>
            </table>
        </div>

    </div>
</div>

</body>
</html>
