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
            margin-top: 90px;
            padding: 20px;
        }

        .navbar-brand {
            font-weight: bold;
            color: #fff !important;
            font-size: 20px;
        }

        /* Custom nav buttons */
        .nav-btn {
            text-decoration: none;
            padding: 8px 18px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 500;
            transition: 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }

        .nav-home {
            background: rgba(255,255,255,0.2);
            color: #fff;
        }
        .nav-home:hover {
            background: #fff;
            color: #198754;
        }

        .nav-login {
            background: linear-gradient(135deg, #0d6efd, #2563eb);
            color: #fff;
        }
        .nav-login:hover {
            transform: translateY(-1px);
            background: #084298;
        }

        .nav-register {
            background: linear-gradient(135deg, #facc15, #f59e0b);
            color: #000;
        }
        .nav-register:hover {
            transform: translateY(-1px);
            background: #fbbf24;
        }

        .nav-user {
            background: #20c997;
            color: #fff;
        }

        .nav-user:hover {
            background: #198754;
        }

        /* Space between navbar and hero */
.message-bar {
    background: #fff;
    min-height: 50px;
    border-bottom: 1px solid #e5e5e5;
}

/* Alert style */
.custom-alert {
    padding: 10px 14px;
    margin: 8px 0;
    border-radius: 6px;
    font-size: 14px;
}

.custom-alert.success {
    background: #e8f8f0;
    color: #146c43;
    border-left: 4px solid #198754;
}

.custom-alert.error {
    background: #fdecec;
    color: #842029;
    border-left: 4px solid #dc3545;
}



    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top shadow">
    <div class="container-fluid">

        <!-- Logo -->
        <a class="navbar-brand" href="/market_ecom/index.php">
            🌱 Farmers Market
        </a>

        <!-- Mobile toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar -->
        <div class="collapse navbar-collapse" id="mainNavbar">

        <ul class="navbar-nav ms-auto align-items-center gap-2">

        <li class="nav-item">
            <a href="/market_ecom/index.php" class="btn btn-outline-light px-3 rounded-pill">
                <i class="fa fa-home me-1"></i> Home
            </a>
        </li>

        <?php if (!isset($_SESSION['user_id'])): ?>

            <li class="nav-item">
                <button class="btn btn-light px-3 rounded-pill shadow-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#loginModal">
                    <i class="fa fa-sign-in-alt me-1 text-success"></i> Login
                </button>
            </li>

            <li class="nav-item">
                <button class="btn btn-warning px-3 rounded-pill shadow-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#registerModal">
                    <i class="fa fa-user-plus me-1"></i> Register
                </button>
            </li>

        <?php else: ?>

            <li class="nav-item">
                <a href="/market_ecom/logout.php" class="btn btn-danger px-3 rounded-pill">
                    <i class="fa fa-power-off me-1"></i> Logout
                </a>
            </li>

        <?php endif; ?>

    </ul>

</div>


            <!-- Left 
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link text-white" href="/market_ecom/index.php">Home</a>
                </li>
            </ul>-->

            <!-- Right -->
            <ul class="navbar-nav ms-auto align-items-center gap-2">

                <?php if (!isset($_SESSION['user_id'])): ?>

                    <li class="nav-item">
                        <a href="/market_ecom/index.php" class="nav-btn nav-home">
                            <i class="fa fa-house"></i> Home
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="#" class="nav-btn nav-login"
                           data-bs-toggle="modal"
                           data-bs-target="#loginModal">
                            <i class="fa fa-right-to-bracket"></i> Login
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="#" class="nav-btn nav-register"
                           data-bs-toggle="modal"
                           data-bs-target="#registerModal">
                            <i class="fa fa-user-plus"></i> Register
                        </a>
                    </li>

                <?php else: ?>

                    <li class="nav-item dropdown">
                        <a class="nav-btn nav-user dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fa fa-user"></i> <?= htmlspecialchars($_SESSION['name']) ?>
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










<!-- HERO SECTION -->
<section class="hero bg-success text-white py-5">
    <div class="container">

        <?php if (isset($_SESSION['success']) || isset($_SESSION['error'])): ?>
        <div class="d-flex justify-content-center align-items-center" style="min-height: 50vh;">
            <div class="alert <?php echo isset($_SESSION['success']) ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
                <?php echo isset($_SESSION['success']) ? $_SESSION['success'] : $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        <?php unset($_SESSION['success']); unset($_SESSION['error']); ?>
        <?php else: ?>
        <!-- Hero content -->
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-3">Fresh from Farm to Table</h1>
                <p class="lead mb-4">
                    Discover the freshest produce, meats, and dairy directly from local farmers.
                    Support sustainable agriculture and enjoy quality products delivered to your doorstep.
                </p>
                <a href="/market_ecom/customer/shop.php" class="btn btn-light btn-lg px-4 py-2">
                    <i class="fa fa-shopping-cart me-2"></i>Shop Now
                </a>
            </div>
            <div class="col-lg-6 text-center">
                <img src="https://via.placeholder.com/500x300/ffffff/28a745?text=Farmers+Market" 
                     alt="Farmers Market" class="img-fluid rounded shadow">
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Page content start -->
<div class="content-wrapper">
