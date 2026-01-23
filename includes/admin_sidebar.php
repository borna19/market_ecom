<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h3><i class="fa-solid fa-shield-halved"></i> Admin Panel</h3>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a class="<?= $current_page == 'admin_dashboard.php' ? 'active' : '' ?>" href="admin_dashboard.php">
                <i class="fa-solid fa-chart-pie"></i> Dashboard
            </a>
        </li>
        <li>
            <a class="<?= $current_page == 'users.php' ? 'active' : '' ?>" href="users.php">
                <i class="fa-solid fa-users"></i> Manage Users
            </a>
        </li>
        <li>
            <a class="<?= $current_page == 'products.php' ? 'active' : '' ?>" href="products.php">
                <i class="fa-solid fa-box"></i> Manage Products
            </a>
        </li>
        <li>
            <a class="<?= $current_page == 'manage_orders.php' ? 'active' : '' ?>" href="manage_orders.php">
                <i class="fa-solid fa-receipt"></i> Manage Orders
            </a>
        </li>
        <li style="margin-top: auto;">
            <a href="../logout.php" class="logout-link">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </li>
    </ul>
</div>