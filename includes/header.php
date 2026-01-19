<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmers Market</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f4f4f4;
        }

        .content-wrapper {
            margin-top: 80px;
            padding: 20px;
        }

        .navbar-brand {
            font-weight: bold;
            color: #198754 !important;
        }
    </style>
</head>

<body>

    <!-- TOP FIXED NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top shadow">
        <div class="container-fluid">

            <!-- Logo -->
            <a class="navbar-brand" href="/market_ecom/index.php">
                🌱 Farmers Market
            </a>

            <!-- Mobile Toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Content -->
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                    <li class="nav-item">
                        <a class="nav-link" href="/market_ecom/index.php">Home</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/market_ecom/customer/shop.php">Shop</a>
                    </li>

                    <?php if (isset($_SESSION['role'])): ?>

                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/market_ecom/admin/dashboard.php">Admin Dashboard</a>
                            </li>
                        <?php endif; ?>

                        <?php if ($_SESSION['role'] === 'vendor'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/market_ecom/vendor/dashboard.php">Vendor Dashboard</a>
                            </li>
                        <?php endif; ?>

                        <?php if ($_SESSION['role'] === 'customer'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/market_ecom/customer/dashboard.php">My Account</a>
                            </li>
                        <?php endif; ?>

                    <?php endif; ?>

                </ul>

                <!-- RIGHT SIDE -->
                <ul class="navbar-nav ms-auto">

                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <!-- Before Login -->
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#registerModal">
                                Register
                            </a>
                        </li>


                        <li class="nav-item">
                            <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">
                                Login
                            </a>

                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/market_ecom/login.php?role=customer">Customer Login</a></li>
                                <li><a class="dropdown-item" href="/market_ecom/login.php?role=vendor">Vendor Login</a></li>
                                <li><a class="dropdown-item" href="/market_ecom/login.php?role=admin">Admin Login</a></li>
                            </ul>
                        </li>

                    <?php else: ?>
                        <!-- After Login -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fa fa-user"></i> <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item text-danger" href="/market_ecom/logout.php">
                                        <i class="fa fa-power-off"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>

                </ul>
            </div>
        </div>
    </nav>

    <!-- PAGE CONTENT START -->
    <div class="content-wrapper">