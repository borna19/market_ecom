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

// Fetch categories
$cats = [];
$rc = mysqli_query($conn, "SELECT DISTINCT category FROM products ORDER BY category ASC");
if ($rc) {
    while ($r = mysqli_fetch_assoc($rc)) $cats[] = $r['category'];
}

// Selected category
$category = $_GET['category'] ?? '';

// Fetch products
if ($category) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE category = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, 's', $category);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
} else {
    $res = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
}

// Cart count
$cart_count = 0;
$q = mysqli_query($conn, "SELECT SUM(quantity) as cnt FROM cart_items WHERE user_id = $user_id");
if ($q && $r = mysqli_fetch_assoc($q)) {
    $cart_count = $r['cnt'] ?? 0;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f9;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 230px;
            background: #111827;
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
            border-radius: 6px;
            margin-bottom: 8px;
        }

        .sidebar a:hover, .sidebar a.active {
            background: #2563eb;
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
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit,minmax(200px,1fr));
            gap: 15px;
            margin: 20px 0;
        }

        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }

        .product {
            background: #fff;
            border-radius: 14px;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform .2s;
        }

        .product:hover {
            transform: translateY(-5px);
        }

        .product img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
        }

        .btn {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>

<div class="layout">

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>MarketEcom</h2>
        <a href="customer_dashboard.php" class="active">🏠 Dashboard</a>
        <a href="cart.php">🛒 Cart (<?= $cart_count ?>)</a>
        <a href="orders.php">📦 My Orders</a>
        <a href="../logout.php">🚪 Logout</a>

        <hr style="border-color:#334155">

        <h4>Categories</h4>
        <a href="customer_dashboard.php">All</a>
        <?php foreach($cats as $c): ?>
            <a href="?category=<?= urlencode($c) ?>"><?= htmlspecialchars($c) ?></a>
        <?php endforeach; ?>
    </div>

    <!-- Main -->
    <div class="main">

        <div class="topbar">
            <h2>Welcome, <?= htmlspecialchars($_SESSION['name'] ?? 'Customer') ?> 👋</h2>
            <span><?= date("l, d M Y") ?></span>
        </div>

        <!-- Stats -->
        <div class="stats">
            <div class="stat-card">
                <h3>🛒 Cart Items</h3>
                <p style="font-size:28px"><?= $cart_count ?></p>
            </div>
            <div class="stat-card">
                <h3>📦 Orders</h3>
                <p style="font-size:28px">Coming soon</p>
            </div>
            <div class="stat-card">
                <h3>💳 Wallet</h3>
                <p style="font-size:28px">₹0</p>
            </div>
        </div>

        <!-- Products -->
        <h2>🛍 Products</h2>
        <div class="products">
            <?php if ($res && mysqli_num_rows($res) > 0): while($p = mysqli_fetch_assoc($res)): ?>
                <div class="product">
                    <img src="/market_ecom/uploads/<?= htmlspecialchars($p['image']) ?>">
                    <h4><?= htmlspecialchars($p['name']) ?></h4>
                    <p>₹<?= htmlspecialchars($p['price']) ?></p>

                    <form method="POST" action="cart_action.php">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <button name="add" class="btn">Add to Cart</button>
                    </form>
                </div>
            <?php endwhile; else: ?>
                <p>No products found.</p>
            <?php endif; ?>
        </div>

    </div>
</div>

</body>
</html>
