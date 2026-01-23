<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h3><i class="fa-solid fa-store"></i> Vendor Panel</h3>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                <i class="fa-solid fa-chart-pie"></i> Dashboard
            </a>
        </li>
        <li>
            <a class="<?= $current_page == 'add_product.php' ? 'active' : '' ?>" href="add_product.php">
                <i class="fa-solid fa-plus"></i> Add Product
            </a>
        </li>
        <li>
            <a class="<?= $current_page == 'manage_products.php' ? 'active' : '' ?>" href="manage_products.php">
                <i class="fa-solid fa-list-check"></i> Manage Products
            </a>
        </li>
        <li>
            <a class="<?= $current_page == 'orders.php' ? 'active' : '' ?>" href="orders.php">
                <i class="fa-solid fa-box"></i> Orders
            </a>
        </li>
        <li style="margin-top: auto;">
            <a href="../logout.php" class="logout-link">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </li>
    </ul>
</div>