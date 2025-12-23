<?php
session_start();
include __DIR__ . '/../includes/e_db.php'; // include your DB connection

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

/* =========================
   FETCH DYNAMIC DATA
========================= */

// Total Users
$user_result = $conn->query("SELECT COUNT(*) AS total_users FROM users");
$total_users = $user_result->fetch_assoc()['total_users'] ?? 0;

// Total Vendors
$vendor_result = $conn->query("SELECT COUNT(*) AS total_vendors FROM users WHERE role='vendor'");
$total_vendors = $vendor_result->fetch_assoc()['total_vendors'] ?? 0;

// Total Orders
$order_result = $conn->query("SELECT COUNT(*) AS total_orders, SUM(total_amount) AS total_revenue FROM orders");
$order_data = $order_result->fetch_assoc();
$total_orders = $order_data['total_orders'] ?? 0;
$total_revenue = $order_data['total_revenue'] ?? 0;

// Pending Orders
$pending_result = $conn->query("SELECT COUNT(*) AS pending_orders FROM orders WHERE status='pending'");
$pending_orders = $pending_result->fetch_assoc()['pending_orders'] ?? 0;

// Total Products
$product_result = $conn->query("SELECT COUNT(*) AS total_products FROM products");
$total_products = $product_result->fetch_assoc()['total_products'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>

    <style>
        body {
            margin: 0;
            font-family: Arial;
            background: #f1f1f1;
        }

        /* CONTENT AREA */
        .content {
            margin-left: 250px; /* same width as sidebar */
            padding: 30px;
        }

        .content h1 {
            font-size: 30px;
            margin-bottom: 15px;
            color: #222;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: .3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }

        .card h3 {
            margin: 0;
            color: #333;
            font-size: 22px;
        }

        .card p {
            color: #666;
            margin-top: 10px;
            font-size: 18px;
        }
    </style>

</head>
<body>

<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<div class="content">

    <h1>Admin Dashboard</h1>

    <div class="cards">
        <div class="card">
            <h3>Total Users</h3>
            <p><?= $total_users ?></p>
        </div>

        <div class="card">
            <h3>Total Vendors</h3>
            <p><?= $total_vendors ?></p>
        </div>

        <div class="card">
            <h3>Total Orders</h3>
            <p><?= $total_orders ?></p>
        </div>

        <div class="card">
            <h3>Total Products</h3>
            <p><?= $total_products ?></p>
        </div>

        <div class="card">
            <h3>Pending Orders</h3>
            <p><?= $pending_orders ?></p>
        </div>

        <div class="card">
            <h3>Revenue</h3>
            <p>$<?= number_format($total_revenue, 2) ?></p>
        </div>
    </div>

</div>

</body>
</html>
