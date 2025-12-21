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

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

// Fetch categories
$cats = [];
$rc = mysqli_query($conn, "SELECT DISTINCT category FROM products ORDER BY category ASC");
if ($rc) {
    while ($r = mysqli_fetch_assoc($rc)) $cats[] = $r['category'];
}

// Selected category
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// Fetch products (include vendor info)
if ($category) {
    $stmt = mysqli_prepare($conn, "SELECT p.id, p.name, p.price, p.image, p.stock, p.category, p.description, p.vendor_id, v.name AS vendor_name FROM products p LEFT JOIN vendors v ON p.vendor_id = v.id WHERE p.category = ? ORDER BY p.id DESC");
    mysqli_stmt_bind_param($stmt, 's', $category);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
} else {
    $res = mysqli_query($conn, "SELECT p.id, p.name, p.price, p.image, p.stock, p.category, p.description, p.vendor_id, v.name AS vendor_name FROM products p LEFT JOIN vendors v ON p.vendor_id = v.id ORDER BY p.id DESC");
} 

// Fetch cart count and total for this user
$cart_count = 0; $cart_total = 0;
$qc = mysqli_prepare($conn, "SELECT c.id FROM cart c WHERE c.user_id = ? ORDER BY c.created_at DESC LIMIT 1");
if ($qc) {
    mysqli_stmt_bind_param($qc, 'i', $user_id);
    mysqli_stmt_execute($qc);
    $rcart = mysqli_stmt_get_result($qc);
    if ($rcart && mysqli_num_rows($rcart)) {
        $cid = mysqli_fetch_assoc($rcart)['id'];
        $qi = mysqli_prepare($conn, "SELECT SUM(p.price * ci.quantity) as total, SUM(ci.quantity) as cnt FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.cart_id = ?");
        if ($qi) {
            mysqli_stmt_bind_param($qi, 'i', $cid);
            mysqli_stmt_execute($qi);
            $rqi = mysqli_stmt_get_result($qi);
            if ($rqi && mysqli_num_rows($rqi)) {
                $rr = mysqli_fetch_assoc($rqi);
                $cart_total = $rr['total'] ?? 0;
                $cart_count = $rr['cnt'] ?? 0;
            }
        }
    }
}

// Fetch orders count
$oq = mysqli_prepare($conn, "SELECT COUNT(*) as cnt FROM orders WHERE user_id = ?");
$order_count = 0;
if ($oq) {
    mysqli_stmt_bind_param($oq, 'i', $user_id);
    mysqli_stmt_execute($oq);
    $roq = mysqli_stmt_get_result($oq);
    if ($roq) $order_count = (int)(mysqli_fetch_assoc($roq)['cnt'] ?? 0);
}

?>
<style>
.content-area { margin-left: 260px; padding: 30px; }
.card-box { background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,0.08);} 
.products-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap:18px; }
.product-card img{ width:100%; height:160px; object-fit:cover; border-radius:8px }
</style>

<div class="content-area">
    <div class="card-box">
        <div class="d-flex justify-content-between align-items-center">
            <h3>Welcome, <?= htmlspecialchars($_SESSION['name'] ?? 'Customer') ?></h3>
            <div>
                <a href="/market_ecom/customer/cart.php" class="btn btn-outline-primary">Cart (<?= $cart_count ?>) - ₹<?= number_format($cart_total,2) ?></a>
                <a href="/market_ecom/customer/orders.php" class="btn btn-outline-secondary">My Orders (<?= $order_count ?>)</a>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-3">
                <h5>Categories</h5>
                <div class="list-group">
                    <a href="customer_dashboard.php" class="list-group-item list-group-item-action <?= $category==''? 'active':'' ?>">All</a>
                    <?php foreach($cats as $c): ?>
                        <a href="customer_dashboard.php?category=<?= urlencode($c) ?>" class="list-group-item list-group-item-action <?= $category==$c? 'active':'' ?>"><?= htmlspecialchars($c) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-md-9">
                <div class="products-grid">
                    <?php if ($res && mysqli_num_rows($res) > 0): while($p = mysqli_fetch_assoc($res)): ?>
                        <div class="product-card card p-2">
                            <img src="/market_ecom/uploads/<?= htmlspecialchars($p['image']) ?>" alt="">
                            <h5 class="mt-2"><?= htmlspecialchars($p['name']) ?></h5>
                            <p class="mb-1">₹<?= htmlspecialchars($p['price']) ?></p>
                            <p class="mb-1"><small class="text-muted">Stock: <?= (int)$p['stock'] ?></small></p>
                            <p><?= htmlspecialchars(substr($p['description'],0,90)) ?></p>
                            <p class="mb-1"><small>Vendor: <a href="/market_ecom/customer/vendor_products.php?vendor_id=<?= $p['vendor_id'] ?>"><?= htmlspecialchars($p['vendor_name'] ?? 'Vendor') ?></a></small></p>
                            <div class="d-flex">
                                <form method="POST" action="/market_ecom/customer/cart_action.php" class="d-flex">
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <input type="number" name="quantity" value="1" min="1" max="<?= max(1,(int)$p['stock']) ?>" class="form-control form-control-sm me-2" style="width:80px">
                                    <button type="submit" name="add" class="btn btn-primary btn-sm">Add to Cart</button>
                                </form>
                                <a href="/market_ecom/customer/vendor_products.php?vendor_id=<?= $p['vendor_id'] ?>" class="btn btn-outline-secondary btn-sm ms-2">Vendor Shop</a>
                            </div> 
                        </div>
                    <?php endwhile; else: ?>
                        <p>No products found in this category.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
