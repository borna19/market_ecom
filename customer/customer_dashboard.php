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

// Fetch categories (moved to sidebar include)

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

// Cart count (moved to sidebar include)
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Dashboard</title>
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

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
        }

        .product-card {
            background: #fff;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            transition: transform .2s;
            text-align: center;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 15px;
        }

        .product-card h4 {
            font-size: 1.1rem;
            margin-bottom: 8px;
            color: #1e293b;
        }

        .product-card p {
            font-size: 1.2rem;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 15px;
        }

        .btn {
            background: #3b82f6;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s;
        }

        .btn:hover {
            background: #2563eb;
        }
    </style>
</head>
<body>

<div class="layout">

    <!-- Sidebar -->
    <?php include __DIR__ . '/../includes/customer_sidebar.php'; ?>

    <!-- Main -->
    <div class="main">

        <div class="topbar">
            <h2>Welcome, <?= htmlspecialchars($_SESSION['name'] ?? 'Customer') ?> 👋</h2>
            <span><?= date("l, d M Y") ?></span>
        </div>

        <!-- Stats -->
        <div class="stats">
            <div class="stat">
                <h3><i class="fa-solid fa-cart-shopping"></i> Cart Items</h3>
                <p><?= $cart_count ?></p>
            </div>
            <div class="stat">
                <h3><i class="fa-solid fa-box-open"></i> Orders</h3>
                <p>Coming soon</p>
            </div>
            <div class="stat">
                <h3><i class="fa-solid fa-wallet"></i> Wallet</h3>
                <p>₹0</p>
            </div>
        </div>

        <!-- Products -->
        <h2 style="margin-bottom: 20px; color: #1e293b;"><i class="fa-solid fa-store"></i> Products</h2>
        <div class="products-grid">
            <?php if ($res && mysqli_num_rows($res) > 0): while($p = mysqli_fetch_assoc($res)): ?>
                <div class="product-card">
                    <img src="/market_ecom/uploads/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                    <h4><?= htmlspecialchars($p['name']) ?></h4>
                    <p>₹<?= htmlspecialchars($p['price']) ?></p>

                    <form method="POST" action="cart_action.php">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <button name="add" class="btn">Add to Cart</button>
                    </form>
                </div>
            <?php endwhile; else: ?>
                <p style="text-align:center; padding: 30px; color:#64748b;">No products found.</p>
            <?php endif; ?>
        </div>

    </div>
</div>

</body>
</html>
