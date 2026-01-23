<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Calculate cart count if not already set
if (!isset($cart_count)) {
    $user_id = $_SESSION['user_id'] ?? 0;
    $cart_count = 0;
    if ($user_id) {
        $cq = mysqli_query($conn, "SELECT SUM(quantity) as cnt FROM cart_items WHERE user_id = $user_id");
        if ($cq && $r = mysqli_fetch_assoc($cq)) {
            $cart_count = $r['cnt'] ?? 0;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmers Market</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #22c55e;
            --primary-dark: #16a34a;
            --text-dark: #1f2937;
            --bg-light: #f9fafb;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            padding-top: 80px; /* Space for fixed navbar */
        }

        .navbar {
            background: #fff;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 15px 0;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--text-dark);
        }

        .nav-link {
            font-weight: 500;
            color: #4b5563;
            margin: 0 10px;
            transition: color 0.2s;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--primary-color);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .badge-cart {
            background: var(--primary-color);
            color: white;
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 50%;
            position: relative;
            top: -2px;
        }

        .dropdown-item:active {
            background-color: var(--primary-color);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand" href="/market_ecom/customer/customer_dashboard.php">
            <i class="fa-solid fa-leaf text-success"></i> Farmers Market
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#customerNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="customerNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="/market_ecom/customer/customer_dashboard.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/market_ecom/customer/shop.php">Shop</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/market_ecom/customer/orders.php">My Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/market_ecom/customer/cart.php">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span class="badge-cart"><?= $cart_count ?></span>
                    </a>
                </li>

                <li class="nav-item dropdown ms-3">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-user-circle fa-lg"></i> <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                        <li><a class="dropdown-item" href="/market_ecom/customer/profile.php">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/market_ecom/logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>