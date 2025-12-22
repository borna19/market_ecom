<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = strtolower($_SESSION['role'] ?? 'guest');
$name = $_SESSION['name'] ?? 'Guest';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Green Sidebar</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

/* SIDEBAR */
.sidebar {
    width: 260px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background: linear-gradient(180deg, #14532d, #166534);
    color: #ecfdf5;
    padding: 25px 20px;
    overflow-y: auto;
    box-shadow: 4px 0 12px rgba(0,0,0,0.25);
}

/* TITLE */
.sidebar h4 {
    text-align: center;
    margin-bottom: 30px;
    color: #facc15;
    font-weight: 700;
    letter-spacing: 0.5px;
}

/* LINKS */
.sidebar a {
    display: block;
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 10px;
    color: #ecfdf5;
    background: #166534;
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 15px;
}

/* HOVER */
.sidebar a:hover {
    background: #22c55e;
    color: #064e3b;
    transform: translateX(6px);
    font-weight: 600;
}

/* LOGOUT */
.sidebar a.logout {
    background: #dc2626;
    color: #fff;
}

.sidebar a.logout:hover {
    background: #b91c1c;
}

/* FOOTER */
.sidebar-footer {
    margin-top: 45px;
    padding-top: 15px;
    border-top: 1px solid #22c55e;
    text-align: center;
    color: #bbf7d0;
    font-size: 13px;
}
</style>
</head>

<body>

<div class="sidebar">

    <h4><?= ucfirst($role); ?> Panel</h4>

    <?php if ($role === 'admin'): ?>
        <a href="/market_ecom/pages/dashboard.php">Dashboard</a>
        <a href="/market_ecom/admin/users.php">Manage Users</a>
        <a href="/market_ecom/admin/products.php">Manage Products</a>
        <a href="/market_ecom/admin/orders.php">All Orders</a>

    <?php elseif ($role === 'vendor' || $role === 'farmer'): ?>
        <a href="/market_ecom/pages/dashboard.php">Dashboard</a>
        <a href="/market_ecom/vendor/add_product.php">Add Product</a>
        <a href="/market_ecom/vendor/manage_products.php">Manage Products</a>
        <a href="/market_ecom/vendor/my_orders.php">My Orders</a>
        <a href="/market_ecom/vendor/earnings.php">My Earnings</a>

    <?php elseif ($role === 'customer'): ?>
        <a href="/market_ecom/pages/dashboard.php">Dashboard</a>
        <a href="/market_ecom/customer/shop.php">Shop Products</a>
        <a href="/market_ecom/customer/cart.php">My Cart</a>
        <a href="/market_ecom/customer/orders.php">My Orders</a>
        <a href="/market_ecom/customer/profile.php">Profile</a>

    <?php else: ?>
        <a href="/market_ecom/pages/login.php">Login</a>
        <a href="/market_ecom/pages/register.php">Register</a>
    <?php endif; ?>

    <a href="/market_ecom/logout.php" class="logout">Logout</a>

    <div class="sidebar-footer">
        © 2025 Farmers Market
    </div>

</div>

</body>
</html>
