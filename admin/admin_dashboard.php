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

// // Revenue
// $revenue = mysqli_fetch_assoc(mysqli_query($conn, "
//     SELECT SUM(oi.quantity * oi.price) as total
//     FROM order_items oi
// "))['total'] ?? 0;

// Latest users
$recent_users = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC LIMIT 5");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body { margin:0; font-family:Segoe UI; background:#f1f5f9; }
        .layout { display:flex; min-height:100vh; }

        .sidebar {
            width:250px;
            background:#020617;
            color:#fff;
            padding:20px;
        }

        .sidebar h2 { text-align:center; margin-bottom:30px; }

        .sidebar a {
            display:block;
            padding:10px;
            color:#cbd5e1;
            text-decoration:none;
            border-radius:8px;
            margin-bottom:8px;
        }

        .sidebar a:hover, .sidebar .active {
            background:#3b82f6;
            color:#fff;
        }

        .main { flex:1; padding:25px; }

        .topbar {
            background:#fff;
            padding:15px 20px;
            border-radius:14px;
            box-shadow:0 4px 10px rgba(0,0,0,0.05);
            display:flex;
            justify-content:space-between;
        }

        .cards {
            margin:25px 0;
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
            gap:15px;
        }

        .card {
            background:#fff;
            padding:20px;
            border-radius:16px;
            box-shadow:0 4px 10px rgba(0,0,0,0.05);
        }

        table {
            width:100%;
            border-collapse:collapse;
            background:#fff;
            border-radius:14px;
            overflow:hidden;
        }

        th, td {
            padding:12px;
            border-bottom:1px solid #e2e8f0;
            text-align:left;
        }

        th { background:#f8fafc; }
    </style>
</head>
<body>

<div class="layout">

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a class="active" href="dashboard.php">📊 Dashboard</a>
        <a href="users.php">👥 Manage Users</a>
        <a href="products.php">📦 Products</a>
        <a href="orders.php">🧾 Orders</a>
        <a href="../logout.php">🚪 Logout</a>
    </div>

    <!-- Main -->
    <div class="main">

        <div class="topbar">
            <h2>Welcome Admin, <?= htmlspecialchars($_SESSION['name']) ?></h2>
            <span><?= date("d M Y") ?></span>
        </div>

        <!-- Stat cards -->
        <div class="cards">
            <div class="card"><h3>👥 Total Users</h3><p style="font-size:28px"><?= $total_users ?></p></div>
            <div class="card"><h3>🧑 Customers</h3><p style="font-size:28px"><?= $total_customers ?></p></div>
            <div class="card"><h3>🧑‍🌾 Vendors</h3><p style="font-size:28px"><?= $total_vendors ?></p></div>
            <div class="card"><h3>📦 Products</h3><p style="font-size:28px"><?= $total_products ?></p></div>
            <div class="card"><h3>🧾 Orders</h3><p style="font-size:28px"><?= $total_orders ?></p></div>
            <div class="card"><h3>💰 Revenue</h3><p style="font-size:28px">₹<?= number_format($revenue,2) ?></p></div>
        </div>

        <!-- Recent users -->
        <h3>Recent Users</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
            </tr>

            <?php while($u = mysqli_fetch_assoc($recent_users)): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= ucfirst($u['role']) ?></td>
            </tr>
            <?php endwhile; ?>
        </table>

    </div>
</div>

</body>
</html>
