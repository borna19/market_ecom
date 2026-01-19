<?php
// Ensure session active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = strtolower($_SESSION['role'] ?? 'guest');
$name = $_SESSION['name'] ?? 'Guest';
?>

<!-- SIDEBAR CSS -->
<style>
    .sidebar {
        width: 260px;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        background: #1c1c1c;
        color: #fff;
        padding: 25px 20px;
        overflow-y: auto;
        box-shadow: 2px 0 8px rgba(0,0,0,0.3);
    }
    .sidebar h4 {
        text-align: center;
        margin-bottom: 25px;
        color: #f1c40f;
        font-weight: 600;
    }
    .sidebar a {
        display: block;
        padding: 10px 14px;
        border-radius: 6px;
        margin-bottom: 8px;
        color: #e0e0e0;
        background: #2c2c2c;
        text-decoration: none;
        transition: 0.25s;
        font-size: 15px;
    }
    .sidebar a:hover {
        background: #4a4a4a;
        color: #fff;
    }
    .sidebar-footer {
        margin-top: 40px;
        padding-top: 12px;
        border-top: 1px solid #444;
        text-align: center;
        color: #aaa;
        font-size: 13px;
    }
</style>

<!-- SIDEBAR HTML -->
<div class="sidebar">

    <h4><?= ucfirst($role) ?> Panel</h4>

    <!-- Admin Menu -->
    <?php if ($role === 'admin'): ?>
        <a href="/market_ecom/pages/dashboard.php">Dashboard</a>
        <a href="/market_ecom/admin/users.php">Manage Users</a>
        <a href="/market_ecom/admin/products.php">Manage Products</a>
        <a href="/market_ecom/admin/orders.php">All Orders</a>

    <!-- Vendor Menu -->
    <?php elseif ($role === 'vendor' || $role === 'farmer'): ?>
        <a href="/market_ecom/pages/dashboard.php">Dashboard</a>
        <a href="/market_ecom/vendor/add_product.php">Add Product</a>
        <a href="/market_ecom/vendor/manage_products.php">Manage Products</a>
        <a href="/market_ecom/vendor/my_orders.php">My Orders</a>
        <a href="/market_ecom/vendor/earnings.php">My Earnings</a>

    <!-- Customer Menu -->
    <?php elseif ($role === 'customer'): ?>
        <a href="/market_ecom/pages/dashboard.php">Dashboard</a>
        <a href="/market_ecom/customer/shop.php">Shop Products</a>
        <a href="/market_ecom/customer/cart.php">My Cart</a>
        <a href="/market_ecom/customer/orders.php">My Orders</a>
        <a href="/market_ecom/customer/profile.php">Profile</a>

    <!-- Guest Menu -->
    <?php else: ?>
        <a href="/market_ecom/pages/login.php">Login</a>
        <a href="/market_ecom/pages/register.php">Register</a>
    <?php endif; ?>

    <a href="/market_ecom/logout.php" style="background:#9b2c2c;">Logout</a>

    <div class="sidebar-footer">
        © 2025 Farmers Market
    </div>
</div>
