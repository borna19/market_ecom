<?php
$current_page = basename($_SERVER['PHP_SELF']);

// Fetch categories for sidebar
$cats = [];
$rc = mysqli_query($conn, "SELECT DISTINCT category FROM products ORDER BY category ASC");
if ($rc) {
    while ($r = mysqli_fetch_assoc($rc)) $cats[] = $r['category'];
}

// Cart count for sidebar
$user_id = (int)$_SESSION['user_id'];
$cart_count = 0;
$q = mysqli_query($conn, "SELECT SUM(quantity) as cnt FROM cart_items WHERE user_id = $user_id");
if ($q && $r = mysqli_fetch_assoc($q)) {
    $cart_count = $r['cnt'] ?? 0;
}
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h3><i class="fa-solid fa-basket-shopping"></i> MarketEcom</h3>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a class="<?= $current_page == 'customer_dashboard.php' ? 'active' : '' ?>" href="customer_dashboard.php">
                <i class="fa-solid fa-house"></i> Dashboard
            </a>
        </li>
        <li>
            <a class="<?= $current_page == 'cart.php' ? 'active' : '' ?>" href="cart.php">
                <i class="fa-solid fa-cart-shopping"></i> Cart <span class="badge badge-light" style="margin-left: auto; background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 4px; font-size: 12px;"><?= $cart_count ?></span>
            </a>
        </li>
        <li>
            <a class="<?= $current_page == 'orders.php' ? 'active' : '' ?>" href="orders.php">
                <i class="fa-solid fa-box-open"></i> My Orders
            </a>
        </li>

        <li style="border-top: 1px solid #334155; margin: 10px 0;"></li>
        <li style="padding: 0 25px; margin-bottom: 10px; color: #94a3b8; font-size: 12px; font-weight: 600; text-transform: uppercase;">Categories</li>

        <li>
            <a href="customer_dashboard.php">
                <i class="fa-solid fa-layer-group"></i> All Products
            </a>
        </li>
        <?php foreach($cats as $c): ?>
        <li>
            <a href="customer_dashboard.php?category=<?= urlencode($c) ?>">
                <i class="fa-solid fa-tag"></i> <?= htmlspecialchars($c) ?>
            </a>
        </li>
        <?php endforeach; ?>

        <li style="margin-top: auto;">
            <a href="../logout.php" class="logout-link">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </li>
    </ul>
</div>