<?php
session_start();
include __DIR__ . '/../includes/e_db.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: /market_ecom/pages/login.php');
    exit;
}

$role = strtolower($_SESSION['role']);
$name = $_SESSION['name'];

// include sidebar (LEFT FIXED)
include __DIR__ . '/../includes/sidebar.php';
?>

<!-- CONTENT CSS (VERY IMPORTANT) -->
<style>
/* SHIFT CONTENT TO THE RIGHT OF SIDEBAR */
.content-area {
    margin-left: 260px;     /* sidebar width */
    padding: 30px;
}

/* CARD STYLE */
.card-box {
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.10);
}
</style>

<!-- DASHBOARD CONTENT AREA -->
<div class="content-area">
    <div class="card-box">
        <h2>Dashboard</h2>

        <p>Welcome back, <strong><?= htmlspecialchars($name) ?></strong></p>
        <p>Your role: <strong><?= htmlspecialchars($role) ?></strong></p>

        <hr>

        <?php if ($role === 'admin'): ?>
            <!-- ADMIN DASHBOARD CONTENT -->
            <h4>Admin Overview</h4>
            <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-top: 20px;">
                <!-- Total Users Card -->
                <div style="flex: 1; min-width: 200px; background: #e3f2fd; padding: 20px; border-radius: 8px;">
                    <h5 style="color: #1976d2;">Total Users</h5>
                    <?php
                    $users = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
                    $user_count = mysqli_fetch_assoc($users)['count'] ?? 0;
                    echo "<h3>$user_count</h3>";
                    ?>
                    <p><a href="/market_ecom/admin/users.php">Manage Users →</a></p>
                </div>

                <!-- Total Products Card -->
                <div style="flex: 1; min-width: 200px; background: #f3e5f5; padding: 20px; border-radius: 8px;">
                    <h5 style="color: #7b1fa2;">Total Products</h5>
                    <?php
                    $products = mysqli_query($conn, "SELECT COUNT(*) as count FROM products");
                    $product_count = mysqli_fetch_assoc($products)['count'] ?? 0;
                    echo "<h3>$product_count</h3>";
                    ?>
                    <p><a href="/market_ecom/admin/products.php">Manage Products →</a></p>
                </div>

                <!-- Total Orders Card -->
                <div style="flex: 1; min-width: 200px; background: #e8f5e9; padding: 20px; border-radius: 8px;">
                    <h5 style="color: #388e3c;">Total Orders</h5>
                    <?php
                    $orders = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders");
                    $order_count = mysqli_fetch_assoc($orders)['count'] ?? 0;
                    echo "<h3>$order_count</h3>";
                    ?>
                    <p><a href="/market_ecom/admin/orders.php">View Orders →</a></p>
                </div>
            </div>

        <?php elseif ($role === 'vendor' || $role === 'farmer'): ?>
            <!-- VENDOR DASHBOARD CONTENT -->
            <h4>Vendor Dashboard</h4>
            <p>Manage your products, orders, and earnings.</p>
            <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-top: 20px;">
                <div style="flex: 1; min-width: 200px; background: #fce4ec; padding: 20px; border-radius: 8px;">
                    <h5>My Products</h5>
                    <p><a href="/market_ecom/vendor/manage_products.php">View & Manage →</a></p>
                </div>
                <div style="flex: 1; min-width: 200px; background: #fff3e0; padding: 20px; border-radius: 8px;">
                    <h5>Pending Orders</h5>
                    <p><a href="/market_ecom/vendor/orders.php">Check Orders →</a></p>
                </div>
            </div>

        <?php else: ?>
            <!-- CUSTOMER DASHBOARD CONTENT -->
            <h4>Customer Dashboard</h4>
            <p>Browse products, manage your cart, and track orders.</p>
            <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-top: 20px;">
                <div style="flex: 1; min-width: 200px; background: #e0f2f1; padding: 20px; border-radius: 8px;">
                    <h5>My Cart</h5>
                    <p><a href="/market_ecom/customer/cart.php">View Cart →</a></p>
                </div>
                <div style="flex: 1; min-width: 200px; background: #fce4ec; padding: 20px; border-radius: 8px;">
                    <h5>My Orders</h5>
                    <p><a href="/market_ecom/customer/orders.php">View Orders →</a></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
