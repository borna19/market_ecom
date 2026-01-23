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

// Fetch Cart Items from Database
$cart_query = "
    SELECT ci.id as cart_id, ci.quantity, p.id as product_id, p.name, p.price, p.image
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    WHERE ci.user_id = $user_id
";
$cart_res = mysqli_query($conn, $cart_query);
$cart_items = [];
$total = 0;
if ($cart_res) {
    while ($row = mysqli_fetch_assoc($cart_res)) {
        $cart_items[] = $row;
        $total += $row['price'] * $row['quantity'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Cart - Customer</title>
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

        .content-card {
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }

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
            vertical-align: middle;
        }

        th {
            background: #f8fafc;
            font-weight: 600;
            color: #475569;
        }

        .img-thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.2s;
        }

        .btn-primary { background: #3b82f6; color: #fff; }
        .btn-primary:hover { background: #2563eb; }

        .btn-success { background: #10b981; color: #fff; }
        .btn-success:hover { background: #059669; }

        .btn-danger { background: #ef4444; color: #fff; padding: 6px 12px; }
        .btn-danger:hover { background: #dc2626; }

        .btn-secondary { background: #64748b; color: #fff; }
        .btn-secondary:hover { background: #475569; }

        .total-section {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .total-label { font-size: 18px; font-weight: 600; color: #475569; margin-right: 15px; }
        .total-amount { font-size: 24px; font-weight: 700; color: #1e293b; }

    </style>
</head>
<body>

<div class="layout">

    <!-- Sidebar -->
    <?php include __DIR__ . '/../includes/customer_sidebar.php'; ?>

    <!-- Main -->
    <div class="main">

        <div class="topbar">
            <h2><i class="fa-solid fa-cart-shopping"></i> My Cart</h2>
            <span><?= date("l, d M Y") ?></span>
        </div>

        <div class="content-card">
            <?php if (empty($cart_items)): ?>
                <div style="text-align:center; padding: 50px;">
                    <i class="fa-solid fa-cart-arrow-down fa-4x" style="color:#cbd5e1; margin-bottom: 20px;"></i>
                    <h3 style="color:#64748b;">Your cart is empty</h3>
                    <p style="color:#94a3b8; margin-bottom: 20px;">Looks like you haven't added anything yet.</p>
                    <a href="customer_dashboard.php" class="btn btn-primary">Start Shopping</a>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center; gap:15px;">
                                        <img src="/market_ecom/uploads/<?= htmlspecialchars($item['image']) ?>" class="img-thumbnail">
                                        <span style="font-weight:500;"><?= htmlspecialchars($item['name']) ?></span>
                                    </div>
                                </td>
                                <td>₹<?= number_format($item['price'], 2) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td>₹<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                <td>
                                    <a href="remove_cart.php?id=<?= $item['cart_id'] ?>" class="btn btn-danger" onclick="return confirm('Remove this item?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="total-section">
                    <span class="total-label">Total Amount:</span>
                    <span class="total-amount">₹<?= number_format($total, 2) ?></span>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:15px; margin-top:25px;">
                    <a href="customer_dashboard.php" class="btn btn-secondary">Continue Shopping</a>
                    <a href="checkout.php" class="btn btn-success">Proceed to Checkout <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

</body>
</html>
